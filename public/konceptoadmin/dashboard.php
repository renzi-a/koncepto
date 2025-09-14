<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET"); 
header("Access-Control-Allow-Headers: Content-Type"); 

// Include the config file to get the $pdo object
require __DIR__ . '/config.php';

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

try {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    // Validate the year to prevent unexpected behavior
    if ($year < 1900 || $year > 2100) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid year parameter."]);
        exit();
    }
    
    $startDate = date('Y-01-01 00:00:00', strtotime("{$year}-01-01"));
    $endDate = date('Y-12-31 23:59:59', strtotime("{$year}-12-31"));

    // Combine all order-related data into a single query
    $stmt = $pdo->prepare("
        SELECT
            SUM(CASE WHEN o.status = 'new' THEN 1 ELSE 0 END) AS pendingOrders,
            SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) AS completedOrders,
            SUM(CASE WHEN o.status = 'delivered' THEN (od.price * od.quantity) ELSE 0 END) AS orderRevenue
        FROM
            orders o
        LEFT JOIN
            order_details od ON o.id = od.order_id
        WHERE
            o.created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Combine all custom order-related data into a single query
    $stmt = $pdo->prepare("
        SELECT
            SUM(CASE WHEN co.status IN ('to_be_quoted', 'quoted', 'approved', 'gathering') THEN 1 ELSE 0 END) AS customPending,
            SUM(CASE WHEN co.status = 'delivered' THEN 1 ELSE 0 END) AS customCompleted,
            SUM(CASE WHEN co.status = 'delivered' THEN coi.total_price ELSE 0 END) AS customRevenue
        FROM
            custom_orders co
        LEFT JOIN
            custom_order_items coi ON co.id = coi.custom_order_id
        WHERE
            co.created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $customData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate total revenue
    $totalRevenue = (float)($orderData['orderRevenue'] ?? 0.0) + (float)($customData['customRevenue'] ?? 0.0);

    echo json_encode([
        'pendingOrders' => (int)($orderData['pendingOrders'] ?? 0),
        'completedOrders' => (int)($orderData['completedOrders'] ?? 0),
        'customPending' => (int)($customData['customPending'] ?? 0),
        'customCompleted' => (int)($customData['customCompleted'] ?? 0),
        'totalRevenue' => $totalRevenue
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    $error_message = "Database error: " . $e->getMessage();
    error_log($error_message);
    echo json_encode(["success" => false, "message" => $error_message]);
}