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
function getDBConnection()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("[MultiPanelX DB Error] " . $e->getMessage());
            http_response_code(503);
            $err = htmlspecialchars($e->getMessage());
            die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Database Error</title>'
            . '<style>*{margin:0;padding:0;box-sizing:border-box}body{background:#0f0f0f;color:#fff;font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}'
            . '.box{background:#1a1a1a;border:1px solid #ef444450;border-radius:16px;padding:36px;max-width:600px;width:100%}'
            . 'h1{font-size:18px;font-weight:700;color:#ef4444;margin-bottom:16px}'
            . '.err{background:#0f0f0f;border:1px solid #2a2a2a;border-radius:8px;padding:14px 16px;font-family:monospace;font-size:13px;color:#f87171;word-break:break-all}'
            . '</style></head><body><div class="box">'
            . '<h1>&#9888; Database Connection Error</h1>'
            . '<div class="err">' . $err . '</div>'
            . '</div></body></html>');
        }
    }
    return $pdo;
}
