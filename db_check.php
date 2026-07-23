<?php
require 'config/database_live.php';
$pdo = getDBConnection();
$stmt = $pdo->query('DESCRIBE license_keys');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->query('DESCRIBE transactions');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
