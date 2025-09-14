<?php
// KonceptoAdmin/api/login.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

require __DIR__ . '/config.php';

$data = json_decode(file_get_contents("php://input"), true);
error_log("Received login request data: " . print_r($data, true));

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if (!$email || !$password) {
    http_response_code(400);
    $errorMessage = "Missing email or password";
    error_log($errorMessage);
    echo json_encode(["success" => false, "message" => $errorMessage]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role, password, created_at FROM users WHERE email = :email AND role = 'admin'");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $token = base64_encode(bin2hex(random_bytes(32)));

            echo json_encode([
                "success" => true,
                "token" => $token,
                "user" => [
                    "id" => $user["id"],
                    "first_name" => $user["first_name"],
                    "last_name" => $user["last_name"],
                    "email" => $user["email"],
                    "role" => $user["role"],
                    "date_joined" => $user["created_at"]
                ]
            ]);
            error_log("Login successful for email: " . $email);
        } else {
            http_response_code(401);
            error_log("Incorrect password for email: " . $email);
            echo json_encode(["success" => false, "message" => "Incorrect password"]);
        }
    } else {
        http_response_code(401);
        error_log("Unauthorized or email not found: " . $email);
        echo json_encode(["success" => false, "message" => "Unauthorized or email not found"]);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Server error during login: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "An internal server error occurred."]);
}
?>
