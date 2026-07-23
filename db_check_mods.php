<?php
require 'config/database.php';
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM mods");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
