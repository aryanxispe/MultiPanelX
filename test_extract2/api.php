<?php
// api.php - API Validation Endpoint for Mod Menus
header("Content-Type: application/json; charset=UTF-8");
require_once 'config/database.php';

// Initialize response array
$response = [
    "status" => "error",
    "message" => "Invalid Request"
];

// Verify if the license key is provided
if (!isset($_GET['key']) || empty(trim($_GET['key']))) {
    $response["message"] = "License key is required";
    echo json_encode($response);
    exit;
}

$key = trim($_GET['key']);
$deviceId = isset($_GET['device_id']) ? trim($_GET['device_id']) : null;

try {
    $pdo = getDBConnection();

    // Check if the key exists and retrieve mod details
    $stmt = $pdo->prepare("
        SELECT lk.*, m.name as mod_name, m.status as mod_status 
        FROM license_keys lk
        JOIN mods m ON lk.mod_id = m.id
        WHERE lk.license_key = ? LIMIT 1
    ");
    $stmt->execute([$key]);
    $license = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$license) {
        $response["message"] = "Invalid license key";
        echo json_encode($response);
        exit;
    }

    // Verify if the mod is active
    if ($license['mod_status'] !== 'active') {
        $response["message"] = "This mod is currently disabled";
        echo json_encode($response);
        exit;
    }

    // Verify if the key has been purchased/sold
    if ($license['status'] !== 'sold') {
        $response["message"] = "License key has not been activated or sold yet";
        echo json_encode($response);
        exit;
    }

    // Verify key expiration
    if (!empty($license['sold_at'])) {
        $soldTimestamp = strtotime($license['sold_at']);
        $duration = (int)$license['duration'];
        $durationType = strtolower($license['duration_type']);
        
        $expirationTime = strtotime("+$duration $durationType", $soldTimestamp);
        $currentTime = time();
        
        if ($currentTime > $expirationTime) {
            $response["message"] = "License key has expired";
            echo json_encode($response);
            exit;
        }
    }

    // Device ID Lock Verification
    if ($deviceId !== null) {
        if (empty($license['device_id'])) {
            // First time use, bind the device ID to the license key
            $updateStmt = $pdo->prepare("UPDATE license_keys SET device_id = ? WHERE id = ?");
            $updateStmt->execute([$deviceId, $license['id']]);
            $license['device_id'] = $deviceId;
        } elseif ($license['device_id'] !== $deviceId) {
            // Locked to another device
            $response["message"] = "License key is locked to another device";
            echo json_encode($response);
            exit;
        }
    } else {
        // If device ID is not supplied but the key is already locked to a device
        if (!empty($license['device_id'])) {
            $response["message"] = "Device ID is required for this locked license key";
            echo json_encode($response);
            exit;
        }
    }
    
    $response = [
        "status" => "success",
        "message" => "License key validated successfully",
        "data" => [
            "mod_name" => $license['mod_name'],
            "duration" => $license['duration'] . ' ' . $license['duration_type'],
            "sold_at" => $license['sold_at'],
            "device_id" => $license['device_id']
        ]
    ];

} catch (Exception $e) {
    $response["message"] = "Server database connection error";
}

echo json_encode($response);
exit;
?>
