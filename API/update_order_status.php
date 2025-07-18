<?php
header('Content-Type: application/json');

// DB connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

// Read raw JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing order ID or status.']);
    exit;
}

$orderId = (int)$data['id'];
$status = $conn->real_escape_string($data['status']);

// Update status
$query = "UPDATE orders SET status = '$status' WHERE id = $orderId";
if ($conn->query($query)) {
    echo json_encode(['success' => true, 'message' => 'Order status updated.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update order status.']);
}
$conn->close();
