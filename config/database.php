<?php
// Database configuration
// Replace these with your actual database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'YOUR_DB_USERNAME');
define('DB_PASS', 'YOUR_DB_PASSWORD');
define('DB_NAME', 'YOUR_DB_NAME');

// Load global settings from JSON
$settings_file = __DIR__ . '/settings.json';
$global_settings = ['site_name' => 'Aryanispe', 'site_subtitle' => 'Multipanel', 'telegram_link' => '', 'show_telegram_icon' => '0', 'support_email' => 'admin@example.com'];
if (file_exists($settings_file)) {
    $loaded_settings = json_decode(file_get_contents($settings_file), true);
    if (is_array($loaded_settings)) {
        $global_settings = array_merge($global_settings, $loaded_settings);
    }
}

// Global Website Branding Configuration
define('SITE_NAME', $global_settings['site_name']);
define('SITE_SUBTITLE', $global_settings['site_subtitle']);
define('TELEGRAM_LINK', $global_settings['telegram_link']);
define('SHOW_TELEGRAM_ICON', $global_settings['show_telegram_icon']);
define('SUPPORT_EMAIL', $global_settings['support_email'] ?? 'admin@example.com');
// Create database connection
function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}
?>
