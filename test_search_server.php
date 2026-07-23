<?php
require_once 'config/database.php';
$pdo = getDBConnection();

$query = 'manage';
$search_term = "%{$query}%";
$results = [];

// 1. Search Pages (Navigation)
$pages = [
    ['title' => 'Dashboard', 'url' => 'index', 'icon' => 'fa-solid fa-chart-pie', 'type' => 'Page'],
    ['title' => 'Manage Mods', 'url' => 'mods', 'icon' => 'fa-solid fa-gamepad', 'type' => 'Page'],
    ['title' => 'License Keys', 'url' => 'keys', 'icon' => 'fa-solid fa-key', 'type' => 'Page'],
    ['title' => 'Orders', 'url' => 'approvals', 'icon' => 'fa-solid fa-shopping-cart', 'type' => 'Page'],
    ['title' => 'Manage Clients', 'url' => 'users', 'icon' => 'fa-solid fa-users', 'type' => 'Page'],
    ['title' => 'Site Settings', 'url' => 'settings', 'icon' => 'fa-solid fa-cog', 'type' => 'Page'],
    ['title' => 'API Docs', 'url' => 'docs', 'icon' => 'fa-solid fa-code', 'type' => 'Page'],
];

foreach ($pages as $page) {
    if (stripos($page['title'], $query) !== false) {
        $results[] = $page;
    }
}

// 2. Search Mods
$stmt = $pdo->prepare("SELECT id, name FROM mods WHERE name LIKE ? LIMIT 3");
$stmt->execute([$search_term]);
while ($row = $stmt->fetch()) {
    $results[] = [
        'title' => $row['name'],
        'url' => 'mods', 
        'icon' => 'fa-solid fa-box',
        'type' => 'Mod'
    ];
}

// 3. Search Users
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE username LIKE ? LIMIT 3");
$stmt->execute([$search_term]);
while ($row = $stmt->fetch()) {
    $results[] = [
        'title' => $row['username'],
        'url' => 'users',
        'icon' => 'fa-solid fa-user',
        'type' => 'User'
    ];
}

// 4. Search License Keys
$stmt = $pdo->prepare("SELECT id, license_key FROM license_keys WHERE license_key LIKE ? LIMIT 3");
$stmt->execute([$search_term]);
while ($row = $stmt->fetch()) {
    $results[] = [
        'title' => $row['license_key'],
        'url' => 'keys',
        'icon' => 'fa-solid fa-key',
        'type' => 'License Key'
    ];
}

echo json_encode(['results' => $results]);
