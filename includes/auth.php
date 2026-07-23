<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// CSRF Protection Functions
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf() {
    if (!isset($_POST['csrf_token']) || !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        die('CSRF token validation failed. Please go back and refresh the page.');
    }
}

// Check if user is logged in and session is valid
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect to admin panel if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Redirect to user panel if admin tries to access user pages
function requireUser() {
    requireLogin();
    if (isAdmin()) {
        header('Location: admin.php');
        exit();
    }
}

// Login function without one device restriction
function login(string $username, string $password, bool $forceLogout = false) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['balance'] = $user['balance'];
        return true;
    }
    return false;
}

// Register function
function register(string $username, string $email, string $password, ?string $referralCode = null) {
    $pdo = getDBConnection();
    
    // Check if referral code is valid (check both user codes and admin codes)
    $referredBy = null;
    $referralType = null;
    
    if ($referralCode) {
        // First check admin-generated referral codes
        $stmt = $pdo->prepare("SELECT created_by FROM referral_codes WHERE code = ? AND status = 'active' AND expires_at > NOW()");
        $stmt->execute([$referralCode]);
        $adminReferral = $stmt->fetchColumn();
        
        if ($adminReferral) {
            $referredBy = $adminReferral;
            $referralType = 'admin';
        } else {
            // Check user-generated referral codes
            $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ? AND role = 'user'");
            $stmt->execute([$referralCode]);
            $referredBy = $stmt->fetchColumn();
            if ($referredBy) {
                $referralType = 'user';
            }
        }
    }
    
    // Generate unique referral code for new user
    do {
        $userReferralCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referral_code = ?");
        $checkStmt->execute([$userReferralCode]);
        $exists = $checkStmt->fetchColumn() > 0;
    } while ($exists);
    
    try {
        $pdo->beginTransaction();
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, referral_code, referred_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword, $userReferralCode, $referredBy]);
        
        $userId = $pdo->lastInsertId();
        
        // Handle referral logic
        if ($referredBy) {
            // Deactivate the referral code after use (one-time use only)
            if ($referralType === 'admin') {
                // Deactivate admin-generated referral code
                $stmt = $pdo->prepare("UPDATE referral_codes SET status = 'inactive' WHERE code = ?");
                $stmt->execute([$referralCode]);
            } else if ($referralType === 'user') {
                // For user codes, we'll track usage in a separate table or mark as used
                // For now, we'll deactivate the user's referral code and generate a new one
                $newUserReferralCode = strtoupper(substr(md5(uniqid()), 0, 8));
                $stmt = $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
                $stmt->execute([$newUserReferralCode, $referredBy]);
            }
            
            // New user starts with ₹0 balance (no welcome bonus)
            // Only give bonus to referrer
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + 50 WHERE id = ?");
            $stmt->execute([$referredBy]);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status, reference, created_at) VALUES (?, 'balance_add', 50, 'completed', 'Referral Bonus', NOW())");
                $stmt->execute([$referredBy]);
            } catch (Exception $e) {
                // Ignore transaction errors
            }
        }
        
        $pdo->commit();
        return $userId; // Return user ID for auto-login
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Logout function
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Force logout from all devices
function forceLogoutAllDevices(int $userId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    $stmt->execute([$userId]);
}

// Clean up expired sessions (older than 24 hours)
function cleanupExpiredSessions() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute();
}

// Check if user has active session from another device
function hasActiveSession(int $userId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_sessions WHERE user_id = ? AND session_id != ?");
    $stmt->execute([$userId, session_id()]);
    return $stmt->fetchColumn() > 0;
}

// Reset device - logout from all devices using username/email
function resetDevice(string $username, string $password) {
    $pdo = getDBConnection();
    
    // Verify user credentials
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return 'user_not_found';
    }
    
    // Verify password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $hashedPassword = $stmt->fetchColumn();
    
    if (!password_verify($password, $hashedPassword)) {
        return 'invalid_password';
    }
    // Clear device lock for all keys belonging to this user
    $stmt = $pdo->prepare("UPDATE license_keys SET device_id = NULL WHERE sold_to = ?");
    $stmt->execute([$user['id']]);
    
    return 'success';
}

// Get user data
function getUserData($userId = null) {
    if (!$userId) {
        $userId = $_SESSION['user_id'];
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update user balance
function updateBalance(int $userId, float $amount, string $type = 'balance_add', ?string $reference = null) {
    $pdo = getDBConnection();
    
    try {
        $pdo->beginTransaction();
        
        // Update user balance
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        
        // Add transaction record
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, reference, status) VALUES (?, ?, ?, ?, 'completed')");
        $stmt->execute([$userId, $amount, $type, $reference]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}


?>