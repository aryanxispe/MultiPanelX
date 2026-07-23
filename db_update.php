<?php
require 'config/database.php';
try {
    $pdo = getDBConnection();
    
    // Update the ENUM status field
    $sql = "ALTER TABLE `license_keys` MODIFY COLUMN `status` ENUM('available', 'sold', 'blocked', 'expired') DEFAULT 'available'";
    $pdo->exec($sql);
    
    echo "Success: ENUM updated.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
