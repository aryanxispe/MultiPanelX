<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdmin();

$success = '';
$error = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'site_updated') {
    $success = 'Site details updated successfully!';
}

try {
    $pdo = getDBConnection();
    
    // Get current user data
    $user = getUserData();
    
    if ($_POST) {
        if (isset($_POST['update_profile'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            
            if (empty($username) || empty($email)) {
                $error = 'Please fill in all fields';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address';
            } else {
                // Check if username or email already exists
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
        } elseif (isset($_POST['update_site'])) {
            $siteSettings = [
                'site_name' => trim($_POST['site_name'] ?? 'Aryanispe'),
                'site_subtitle' => trim($_POST['site_subtitle'] ?? 'Multipanel'),
                'telegram_link' => trim($_POST['telegram_link'] ?? ''),
                'show_telegram_icon' => isset($_POST['show_telegram_icon']) ? "1" : "0",
                'support_email' => trim($_POST['support_email'] ?? '')
            ];
            
            if (!is_dir('config')) {
                mkdir('config', 0755, true);
            }
            if (file_put_contents('config/settings.json', json_encode($siteSettings, JSON_PRETTY_PRINT))) {
                header("Location: settings?msg=site_updated");
                exit;
            } else {
                $error = 'Failed to update site details. Please check folder permissions.';
            }
        } elseif (isset($_POST['update_upi'])) {
            $upiId = trim($_POST['upi_id']);
            if (empty($upiId)) {
                $error = 'Please enter a UPI ID';
            } else {
                $upiData = ['upi_id' => $upiId];
                if (!is_dir('config')) {
                    mkdir('config', 0755, true);
                }
                if (file_put_contents('config/upi.json', json_encode($upiData, JSON_PRETTY_PRINT))) {
                    $success = 'UPI ID updated successfully!';
                } else {
                    $error = 'Failed to update UPI ID. Please check folder permissions.';
                }
            }
        }
    }
} catch (Exception $e) {
    $error = 'Error processing settings: ' . $e->getMessage();
}

$page_title = "Admin Settings - " . SITE_NAME . " " . SITE_SUBTITLE;
require_once 'includes/header.php';
?>

<!-- Header -->
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-glow-primary">System Settings</h2>
        <p class="text-sm text-brand-muted mt-1 font-medium">Configure administrator credentials, passwords, and profile details.</p>
    </div>
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Profile Card -->
    <div class="glass-card rounded-2xl p-6 border border-brand-border/60">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-user-gear text-brand-primary"></i>
            <span>Update Profile</span>
        </h3>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-xs font-semibold text-brand-muted mb-1.5">Username *</label>
                <input type="text" id="username" name="username" required
                       value="<?php echo htmlspecialchars($user['username']); ?>"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary">
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-brand-muted mb-1.5">Email Address *</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($user['email']); ?>"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary">
            </div>

            <button type="submit" name="update_profile" class="w-full py-3 bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-90 active:scale-[0.98] transition-all duration-300 rounded-xl text-sm font-bold text-white shadow-glow flex items-center justify-center space-x-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Profile</span>
            </button>
        </form>
    </div>

    <!-- Password Card -->
    <div class="glass-card rounded-2xl p-6 border border-brand-border/60">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-lock text-brand-secondary"></i>
            <span>Change Password</span>
        </h3>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="current_password" class="block text-xs font-semibold text-brand-muted mb-1.5">Current Password *</label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary">
            </div>

            <div>
                <label for="new_password" class="block text-xs font-semibold text-brand-muted mb-1.5">New Password *</label>
                <input type="password" id="new_password" name="new_password" required
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary"
                       placeholder="Min 6 characters">
            </div>

            <div>
                <label for="confirm_password" class="block text-xs font-semibold text-brand-muted mb-1.5">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary">
            </div>

            <button type="submit" name="change_password" class="w-full py-3 bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-90 active:scale-[0.98] transition-all duration-300 rounded-xl text-sm font-bold text-white shadow-glow flex items-center justify-center space-x-2">
                <i class="fa-solid fa-key"></i>
                <span>Update Password</span>
            </button>
        </form>
    </div>
    <!-- Site Details Card -->
    <div class="glass-card rounded-2xl p-6 border border-brand-border/60">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-globe text-brand-primary"></i>
            <span>Site Details</span>
        </h3>
        
        <?php
        $currentSettings = [
            'site_name' => 'Aryanispe',
            'site_subtitle' => 'Multipanel',
            'telegram_link' => 'https://t.me/ARYANISPE',
            'show_telegram_icon' => '0',
            'support_email' => 'admin@example.com'
        ];
        if (file_exists('config/settings.json')) {
            $settingsData = json_decode(file_get_contents('config/settings.json'), true);
            if ($settingsData) {
                $currentSettings = array_merge($currentSettings, $settingsData);
            }
        }
        ?>
        
        <form method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label for="site_name" class="block text-xs font-semibold text-brand-muted mb-1.5">Site Name *</label>
                <input type="text" id="site_name" name="site_name" required
                       value="<?php echo htmlspecialchars($currentSettings['site_name']); ?>"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary">
            </div>

            <div>
                <label for="site_subtitle" class="block text-xs font-semibold text-brand-muted mb-1.5">Site Subtitle *</label>
                <input type="text" id="site_subtitle" name="site_subtitle" required
                       value="<?php echo htmlspecialchars($currentSettings['site_subtitle']); ?>"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary">
            </div>
            
            <div>
                <label for="support_email" class="block text-xs font-semibold text-brand-muted mb-1.5">Support Email</label>
                <input type="email" id="support_email" name="support_email"
                       value="<?php echo htmlspecialchars($currentSettings['support_email']); ?>"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary">
            </div>

            <div>
                <label class="flex items-center space-x-3 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="show_telegram_icon" value="1" class="sr-only peer" <?php echo $currentSettings['show_telegram_icon'] === '1' ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-primary"></div>
                    </div>
                    <span class="text-sm font-semibold text-brand-muted">Show Telegram Icon</span>
                </label>
            </div>
            
            <div>
                <label for="telegram_link" class="block text-xs font-semibold text-brand-muted mb-1.5">Telegram Link</label>
                <input type="url" id="telegram_link" name="telegram_link"
                       value="<?php echo htmlspecialchars($currentSettings['telegram_link']); ?>"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary">
            </div>

            <button type="submit" name="update_site" class="w-full py-3 bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-90 active:scale-[0.98] transition-all duration-300 rounded-xl text-sm font-bold text-white shadow-glow flex items-center justify-center space-x-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Site Details</span>
            </button>
        </form>
    </div>
    <!-- UPI Settings Card -->
    <div class="glass-card rounded-2xl p-6 border border-brand-border/60">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-qrcode text-brand-primary"></i>
            <span>Payment Settings (UPI)</span>
        </h3>
        
        <?php
        $currentUpi = '';
        if (file_exists('config/upi.json')) {
            $upiData = json_decode(file_get_contents('config/upi.json'), true);
            if ($upiData && isset($upiData['upi_id'])) {
                $currentUpi = $upiData['upi_id'];
            }
        }
        ?>
        
        <form method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label for="upi_id" class="block text-xs font-semibold text-brand-muted mb-1.5">UPI ID *</label>
                <input type="text" id="upi_id" name="upi_id" required
                       value="<?php echo htmlspecialchars($currentUpi); ?>"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary"
                       placeholder="e.g. yourname@ybl">
                <p class="text-[10px] text-brand-muted mt-2">This UPI ID will be used to generate QR codes for users to pay.</p>
            </div>

            <button type="submit" name="update_upi" class="w-full py-3 bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-90 active:scale-[0.98] transition-all duration-300 rounded-xl text-sm font-bold text-white shadow-glow flex items-center justify-center space-x-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save UPI ID</span>
            </button>
        </form>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>


