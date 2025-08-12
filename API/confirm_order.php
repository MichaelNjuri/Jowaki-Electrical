<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset("utf8");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// Parse and validate input
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON format.']);
    exit;
}

if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Valid Order ID is required.']);
    exit;
}

$orderId = (int)$data['id'];
if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid Order ID.']);
    exit;
}

$conn->begin_transaction();

try {
    // Verify order exists and is pending or NULL/empty
    $checkStmt = $conn->prepare("SELECT id, status, customer_info FROM orders WHERE id = ? AND (status = 'pending' OR status IS NULL OR status = '')");
    $checkStmt->bind_param("i", $orderId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found or already confirmed.']);
        exit;
    }

    $order = $result->fetch_assoc();
    $checkStmt->close();

    // Update order status
    $updateStmt = $conn->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ?");
    $updateStmt->bind_param("i", $orderId);

    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update order status.');
    }

    $updateStmt->close();
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Order #$orderId confirmed successfully.",
        'order_id' => $orderId,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to confirm order: ' . $e->getMessage()
    ]);
}

$conn->close();
?>