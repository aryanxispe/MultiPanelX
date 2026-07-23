<?php
require 'config/database.php';
try {
    $pdo = getDBConnection();
    $sql = "ALTER TABLE transactions ADD COLUMN plan_id INT NULL DEFAULT NULL AFTER reference;";
    $pdo->exec($sql);
    echo "Success: plan_id column added to transactions.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
