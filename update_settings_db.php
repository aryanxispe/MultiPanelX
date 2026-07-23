<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Create settings table
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Settings table created successfully.<br>";
    
    // Default settings
    $defaults = [
        'site_name' => 'Aryanispe',
        'site_subtitle' => 'Multipanel',
        'telegram_link' => 'https://t.me/ARYANISPE',
        'show_telegram_icon' => '0' // 0 for false by default
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaults as $key => $val) {
        $stmt->execute([$key, $val]);
    }
    echo "Default settings inserted successfully.<br>";
    
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
