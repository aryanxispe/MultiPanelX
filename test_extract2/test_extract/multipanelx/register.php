<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Redirect if already logged in
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $referralCode = trim($_POST['referral_code']);
    
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($referralCode)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $pdo = null;
        try {
            $pdo = getDBConnection();
            
            // First check if referral code is valid
            $referredBy = null;
            $referralType = null;
            
            // Check admin-generated referral codes
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
            
            if (!$referredBy) {
                $error = 'Invalid or expired referral code. You must have a valid referral code to register.';
            } else {
                // Check if username or email already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Username or email already exists';
                } else {
                    // Generate unique referral code for new user
                    $userReferralCode = strtoupper(substr(md5(uniqid()), 0, 8));
                    
                    $pdo->beginTransaction();
                    
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, referral_code, referred_by) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashedPassword, $userReferralCode, $referredBy]);
                    
                    // Deactivate the referral code after use
                    if ($referralType === 'admin') {
                        $stmt = $pdo->prepare("UPDATE referral_codes SET status = 'inactive' WHERE code = ?");
                        $stmt->execute([$referralCode]);
                    } else if ($referralType === 'user') {
                        $newUserReferralCode = strtoupper(substr(md5(uniqid()), 0, 8));
                        $stmt = $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
                        $stmt->execute([$newUserReferralCode, $referredBy]);
                    }
                    
                    // Give referral bonus to referrer
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance + 50 WHERE id = ?");
                    $stmt->execute([$referredBy]);
                    
                    // Record referral transaction for referrer
                    try {
                        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status, reference, created_at) VALUES (?, 'balance_add', 50, 'completed', 'Referral Bonus', NOW())");
                        $stmt->execute([$referredBy]);
                    } catch (Exception $e) {
                        // Ignore transaction log errors
                    }
                    
                    $pdo->commit();
                    $success = 'Registration successful! You can now log in.';
                    $username = $email = $referralCode = '';
                }
            }
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME . ' ' . SITE_SUBTITLE; ?></title>
    <!-- Theme state initializer (FOUC prevention) -->
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        }
    </script>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              brand: {
                primary: 'rgb(var(--brand-primary-rgb))',
                secondary: 'rgb(var(--brand-secondary-rgb))',
                bg: 'rgb(var(--brand-bg-rgb))',
                surface: 'rgb(var(--brand-surface-rgb))',
                border: 'rgb(var(--brand-border-rgb))',
                text: 'rgb(var(--brand-text-rgb))',
                muted: 'rgb(var(--brand-muted-rgb))',
                success: '#10b981',
                warning: '#f59e0b',
                error: '#ef4444'
              }
            },
            fontFamily: {
              sans: ['Inter', 'sans-serif'],
            },
            boxShadow: {
              glow: '0 0 15px rgba(99, 102, 241, 0.15)',
              'glow-cyan': '0 0 15px rgba(6, 182, 212, 0.15)'
            }
          }
        }
      }
    </script>
    <style type="text/tailwindcss">
      :root {
        --brand-primary-rgb: 99 102 241;
        --brand-secondary-rgb: 6 182 212;
        --brand-bg-rgb: 226 232 240;
        --brand-surface-rgb: 248 250 252;
        --brand-border-rgb: 148 163 184;
        --brand-text-rgb: 15 23 42;
        --brand-muted-rgb: 71 85 105;
      }

      .dark {
        --brand-bg-rgb: 11 15 25;
        --brand-surface-rgb: 17 24 39;
        --brand-border-rgb: 31 41 55;
        --brand-text-rgb: 249 250 251;
        --brand-muted-rgb: 156 163 175;
      }

      .glass-panel {
        background-color: var(--brand-surface);
        border: 1px solid var(--brand-border);
        backdrop-filter: blur(12px);
      }

      .glass-card {
        background-color: rgb(var(--brand-surface-rgb));
        border: 1px solid rgb(var(--brand-border-rgb));
        backdrop-filter: blur(8px);
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
      }

      .dark .glass-card {
        background-color: rgba(var(--brand-surface-rgb), 0.6);
        border: 1px solid rgba(var(--brand-border-rgb), 0.5);
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.15);
      }

      .text-glow-primary {
        text-shadow: 0 0 10px rgba(99, 102, 241, 0.4);
      }

      .text-glow-cyan {
        text-shadow: 0 0 10px rgba(6, 182, 212, 0.4);
      }

      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
      }

      .animate-fade-in {
        animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      }

      body {
        background-color: var(--brand-bg);
        color: var(--brand-text);
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
        background-image: 
          radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.03) 0%, transparent 40%),
          radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.03) 0%, transparent 40%);
      }

      .dark body {
        background-image: 
          radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 40%),
          radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.05) 0%, transparent 40%);
      }

      /* Dropdown option styles */
      select option {
        background-color: var(--brand-surface);
        color: var(--brand-text);
      }
    </style>
</head>
<body class="bg-brand-bg text-brand-text font-sans antialiased min-h-screen flex flex-col items-center justify-between p-4">

    <div class="w-full max-w-lg my-auto animate-fade-in">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-primary to-brand-secondary items-center justify-center shadow-glow mb-4">
                <i class="fa-solid fa-cube text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-brand-primary to-brand-secondary bg-clip-text text-transparent"><?php echo SITE_NAME; ?></h1>
            <p class="text-xs uppercase font-bold tracking-widest text-brand-muted mt-1"><?php echo SITE_SUBTITLE; ?> Key Manager</p>
        </div>

        <!-- Register Card -->
        <div class="glass-card rounded-2xl p-8 border border-brand-border/60">
            <h2 class="text-xl font-bold mb-1 text-center">Create Account</h2>
            <p class="text-xs text-brand-muted text-center mb-6">Join Aryanispe Multipanel today</p>

            <?php if ($error): ?>
                <div class="bg-brand-error/10 border border-brand-error/20 text-brand-error text-xs rounded-xl p-3.5 mb-5 flex items-center space-x-2.5">
                    <i class="fa-solid fa-circle-exclamation text-base"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-brand-success/10 border border-brand-success/20 text-brand-success text-xs rounded-xl p-3.5 mb-5 flex items-center space-x-2.5">
                    <i class="fa-solid fa-circle-check text-base"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-brand-warning/10 border border-brand-warning/20 text-brand-warning text-[11px] rounded-xl p-3 mb-6 flex items-start space-x-2.5">
                <i class="fa-solid fa-circle-info text-sm mt-0.5"></i>
                <span><strong>Important:</strong> Registration requires a valid referral code. Each referral code can only be used once. New accounts start with ₹0.00 balance.</span>
            </div>

            <form method="POST" id="registerForm" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-xs font-semibold text-brand-muted mb-1.5">Username *</label>
                        <input type="text" id="username" name="username" required
                               value="<?php echo htmlspecialchars($username ?? ''); ?>"
                               class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-brand-muted/40"
                               placeholder="e.g. aryan123">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-brand-muted mb-1.5">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-brand-muted/40"
                               placeholder="e.g. name@example.com">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-brand-muted mb-1.5">Password *</label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-brand-muted/40"
                               placeholder="Min 6 characters">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-xs font-semibold text-brand-muted mb-1.5">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-brand-muted/40"
                               placeholder="Re-type password">
                    </div>
                </div>

                <div>
                    <label for="referral_code" class="block text-xs font-semibold text-brand-muted mb-1.5">Referral Code *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-brand-muted/80">
                            <i class="fa-solid fa-gift"></i>
                        </span>
                        <input type="text" id="referral_code" name="referral_code" required
                               value="<?php echo htmlspecialchars($referralCode ?? ''); ?>"
                               class="w-full pl-10 pr-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-brand-muted/40"
                               placeholder="Enter your referral code">
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="w-full py-3 bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-90 active:scale-[0.98] transition-all duration-300 rounded-xl text-sm font-bold text-white shadow-glow flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-user-plus" id="btnIcon"></i>
                    <span id="btnText">Register Account</span>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-brand-border/60 text-center">
                <p class="text-xs text-brand-muted">
                    Already have an account? 
                    <a href="login" class="text-brand-primary font-bold hover:underline transition-all">Login here</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Global Footer -->
    <footer class="w-full max-w-7xl mx-auto px-6 py-6 text-brand-muted border-t border-brand-border/20">
        <?php 
        $footer_html = base64_decode("PGRpdiBjbGFzcz0iZmxleCBmbGV4LWNvbCBtZDpmbGV4LXJvdyBpdGVtcy1jZW50ZXIganVzdGlmeS1iZXR3ZWVuIGdhcC00IHRleHQteHMgdGV4dC1icmFuZC1tdXRlZCB3LWZ1bGwiPjxwIGNsYXNzPSJ0ZXh0LWNlbnRlciBtZDp0ZXh0LWxlZnQiPiZjb3B5OyBfX1lFQVJfXyBfX1NJVEVfXy4gQWxsIHJpZ2h0cyByZXNlcnZlZC48L3A+PGRpdiBjbGFzcz0iZmxleCBmbGV4LWNvbCBzbTpmbGV4LXJvdyBpdGVtcy1jZW50ZXIgZ2FwLTMgc206Z2FwLTQgdGV4dC1jZW50ZXIgc206dGV4dC1yaWdodCI+PGEgaHJlZj0iaHR0cHM6Ly90Lm1lL0FSWUFOSVNQRSIgdGFyZ2V0PSJfYmxhbmsiIGNsYXNzPSJob3Zlcjp0ZXh0LWJyYW5kLXByaW1hcnkgdHJhbnNpdGlvbi1jb2xvcnMgZmxleCBpdGVtcy1jZW50ZXIgc3BhY2UteC0xLjUgZm9udC1tZWRpdW0iPjxpIGNsYXNzPSJmYS1icmFuZHMgZmEtdGVsZWdyYW0gdGV4dC14cyB0ZXh0LWJyYW5kLXByaW1hcnkiPjwvaT48c3Bhbj5EZXNpZ25lZCBhbmQgZGV2ZWxvcGVkIGJ5IEBhcnlhbmlzcGU8L3NwYW4+PC9hPjxzcGFuIGNsYXNzPSJoaWRkZW4gc206aW5saW5lIHRleHQtYnJhbmQtYm9yZGVyLzQwIj58PC9zcGFuPjxhIGhyZWY9Imh0dHBzOi8vYXJ5YW5pc3BlaG9zdC5pbi8iIHRhcmdldD0iX2JsYW5rIiBjbGFzcz0iaG92ZXI6dGV4dC1icmFuZC1wcmltYXJ5IHRyYW5zaXRpb24tY29sb3JzIGZsZXggaXRlbXMtY2VudGVyIHNwYWNlLXgtMS41IGZvbnQtbWVkaXVtIj48aSBjbGFzcz0iZmEtc29saWQgZmEtc2VydmVyIHRleHQteHMgdGV4dC1icmFuZC1zZWNvbmRhcnkiPjwvaT48c3Bhbj5Qb3dlcmVkIGJ5IEFyeWFuaXNwZSBIb3N0PC9zcGFuPjwvYT48L2Rpdj48L2Rpdj4=");
        echo str_replace(
            ['__YEAR__', '__SITE__'],
            [date('Y'), SITE_NAME . ' ' . SITE_SUBTITLE],
            $footer_html
        );
        ?>
    </footer>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = document.getElementById('submitBtn');
            const icon = document.getElementById('btnIcon');
            const text = document.getElementById('btnText');
            
            // Apply loading state
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            icon.className = 'fa-solid fa-spinner animate-spin';
            text.innerText = 'Creating Account...';
            
            // 700ms premium processing delay
            setTimeout(function() {
                form.submit();
            }, 700);
        });
    </script>
</body>
</html>