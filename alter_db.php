<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$pdo = getDBConnection();
$pdo->exec("ALTER TABLE license_keys MODIFY COLUMN status ENUM('available','pending','sold') DEFAULT 'available'");
echo "Database altered successfully!";
