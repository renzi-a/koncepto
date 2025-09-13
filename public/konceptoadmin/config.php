<?php
// config.php

// Database configuration
$dbHost = 'auth-db657.hstgr.io';
$dbUser = 'u874626598_teamvanguard';
$dbPass = 'KonceptoTeamVanguard2025!';
$dbName = 'u874626598_koncepto';

// Create connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Optional: Storage path
$STORAGE_UPLOAD_DIRECTORY = '/home/u874626598/domains/koncepto.me/koncepto/storage/app/public';
