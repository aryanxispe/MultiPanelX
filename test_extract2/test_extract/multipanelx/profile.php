<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireUser();

$success = '';
$error = '';

$pdo = getDBConnection();

// Get current user data
$user = getUserData();

if ($_POST) {

    if (isset($_POST['reset_device'])) {
        $password = $_POST['password'];
        
        if (empty($password)) {
            $error = 'Please enter your password to reset device';
        } else {
            $resetResult = resetDevice($user['username'], $password);
            if ($resetResult === 'success') {
                $success = 'Successfully logged out from all devices. You will be redirected to login page.';
                header('refresh:3;url=logout.php');
            } else if ($resetResult === 'invalid_password') {
                $error = 'Invalid password';
            } else {
                $error = 'Failed to reset device';
            }
        }
    } elseif (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        
        if (empty($username) || empty($email)) {
            $error = 'Please fill in all fields';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            // Check if username or email already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user['id']]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username or email already exists';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$username, $email, $user['id']])) {
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $success = 'Profile updated successfully!';
                    $user = getUserData(); // Refresh user data
                } else {
                    $error = 'Failed to update profile';
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Please fill in all password fields';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters long';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $user['id']])) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password';
            }
        }
    }
}

$page_title = "User Settings - " . SITE_NAME . " " . SITE_SUBTITLE;
require_once 'includes/header.php';
?>

<!-- Header -->
<div class="mb-8">
    <h2 class="text-2xl font-bold tracking-tight text-glow-primary">My Settings</h2>
    <p class="text-sm text-brand-muted mt-1 font-medium">Configure profile settings, change security passwords, or reset device logs.</p>
</div>

<!-- Alerts -->
<?php if ($success): ?>
    <div class="bg-brand-success/10 border border-brand-success/20 text-brand-success text-xs rounded-xl p-3.5 mb-6 flex items-center space-x-2.5">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span><?php echo htmlspecialchars($success); ?></span>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-brand-error/10 border border-brand-error/20 text-brand-error text-xs rounded-xl p-3.5 mb-6 flex items-center space-x-2.5">
        <i class="fa-solid fa-circle-exclamation text-base"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Profile Card -->
    <div class="glass-card rounded-2xl p-6 border border-brand-border/60 h-fit">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-user-gear text-brand-primary"></i>
            <span>Update Profile</span>
        </h3>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-xs font-semibold text-brand-muted mb-1.5">Username *</label>
                <input type="text" id="username" name="username" required
                       value="<?php echo htmlspecialchars($user['username']); ?>"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary text-brand-text">
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-brand-muted mb-1.5">Email Address *</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($user['email']); ?>"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary text-brand-text">
            </div>

            <button type="submit" name="update_profile" class="w-full py-3 bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-90 active:scale-[0.98] transition-all duration-300 rounded-xl text-sm font-bold text-white shadow-glow flex items-center justify-center space-x-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Profile</span>
            </button>
        </form>
    </div>

    <!-- Password Card -->
    <div class="glass-card rounded-2xl p-6 border border-brand-border/60 h-fit">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-lock text-brand-secondary"></i>
            <span>Change Password</span>
        </h3>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="current_password" class="block text-xs font-semibold text-brand-muted mb-1.5">Current Password *</label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary text-brand-text">
            </div>

            <div>
                <label for="new_password" class="block text-xs font-semibold text-brand-muted mb-1.5">New Password *</label>
                <input type="password" id="new_password" name="new_password" required
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary text-brand-text"
                       placeholder="Min 6 characters">
            </div>

            <div>
                <label for="confirm_password" class="block text-xs font-semibold text-brand-muted mb-1.5">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary text-brand-text">
            </div>

            <button type="submit" name="change_password" class="w-full py-3 bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-90 active:scale-[0.98] transition-all duration-300 rounded-xl text-sm font-bold text-white shadow-glow flex items-center justify-center space-x-2">
                <i class="fa-solid fa-key"></i>
                <span>Update Password</span>
            </button>
        </form>
    </div>

    <!-- Device Reset Card -->
    <div class="glass-card rounded-2xl p-6 border border-brand-border/60 h-fit">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2 text-brand-error">
            <i class="fa-solid fa-rotate"></i>
            <span>Device Authorization</span>
        </h3>
        <p class="text-xs text-brand-muted leading-relaxed mb-4">Reset device logs if you are unable to login due to device locked restrictions.</p>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="password" class="block text-xs font-semibold text-brand-muted mb-1.5">Confirm Password *</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary text-brand-text"
                       placeholder="Enter account password">
            </div>

            <button type="submit" name="reset_device" class="w-full py-3 bg-brand-error/10 border border-brand-error/30 text-brand-error hover:bg-brand-error/20 active:scale-[0.98] transition-all duration-300 rounded-xl text-sm font-bold flex items-center justify-center space-x-2">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span>Reset Authorized Device</span>
            </button>
        </form>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>