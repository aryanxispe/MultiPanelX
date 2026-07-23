<?php
require 'config/database.php';
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("DESCRIBE transactions;");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
