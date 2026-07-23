<?php
// clean.php - Clean up old unused files from the server

$oldFiles = [
    'admin_dashboard.php',
    'admin_dashboard_simple.php',
    'licence_key_list.php',
    'register_simple.php',
    'reset_device.php',
    'user_applications.php',
    'user_applications_simple.php',
    'user_balance.php',
    'user_balance_simple.php',
    'user_dashboard.php',
    'user_dashboard_simple.php',
    'user_generate.php',
    'user_generate_simple.php',
    'user_manage_keys.php',
    'user_manage_keys_simple.php',
    'user_settings.php',
    'user_settings_simple.php',
    'user_transactions.php',
    'user_transactions_simple.php',
    'manage_users.php',
    'manage_mods.php',
    'referral_codes.php',
    'add_license.php',
    'add_balance.php',
    'add_mod.php',
    'available_keys.php',
    'upload_mod.php',
    'mod_list.php'
];

echo "<h3>Cleaning up old files...</h3><ul>";

foreach ($oldFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<li style='color: green;'>Deleted: $file</li>";
        } else {
            echo "<li style='color: red;'>Failed to delete: $file</li>";
        }
    } else {
        echo "<li style='color: gray;'>Not found (already clean): $file</li>";
    }
}

echo "</ul>";

// Self-delete clean.php for security
unlink(__FILE__);
echo "<p style='color: blue;'>Cleanup complete. This script has self-deleted.</p>";
?>
