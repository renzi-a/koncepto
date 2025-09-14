<?php
// config.php
$dbHost = 'auth-db657.hstgr.io';
$dbUser = 'u874626598_teamvanguard';
$dbPass = 'KonceptoTeamVanguard2025!';
$dbName = 'u874626598_koncepto';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit();
}

// Optional: Storage path
$STORAGE_UPLOAD_DIRECTORY = '/home/u874626598/domains/koncepto.me/koncepto/storage/app/public';
?>
