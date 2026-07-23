<?php
require 'config/database.php';
try {
    $pdo = getDBConnection();
    $sql = "CREATE TABLE IF NOT EXISTS mod_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mod_id INT NOT NULL,
    plan_name VARCHAR(255) NOT NULL,
    duration INT NOT NULL,
    duration_type ENUM('minutes', 'hours', 'days', 'lifetime') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mod_id) REFERENCES mods(id) ON DELETE CASCADE
);";
    $pdo->exec($sql);
    echo "Success: mod_plans table created.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
