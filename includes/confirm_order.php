<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'db_connection.php';
require_once 'check_auth.php';

// Start session for admin authentication
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied. Admin privileges required.']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        exit;
    }
    
    $orderId = isset($input['orderId']) ? intval($input['orderId']) : 0;
    $confirmationNotes = isset($input['confirmationNotes']) ? trim($input['confirmationNotes']) : '';
    
    if ($orderId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Valid Order ID is required.']);
        exit;
    }
    
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Check if order exists and get current status
    $checkStmt = $conn->prepare("SELECT id, status, user_id FROM orders WHERE id = ?");
    $checkStmt->bind_param('i', $orderId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }
    
    // Check if status is empty, null, or not pending
    $currentStatus = $order['status'] ?? '';
    if (empty($currentStatus) || $currentStatus !== 'pending') {
        http_response_code(400);
        $statusDisplay = empty($currentStatus) ? 'empty/null' : $currentStatus;
        echo json_encode(['success' => false, 'error' => 'Order cannot be confirmed. Current status: ' . $statusDisplay]);
        exit;
    }
    
    // Update order status to confirmed
    $updateStmt = $conn->prepare("UPDATE orders SET status = 'confirmed', updated_at = NOW() WHERE id = ?");
    $updateStmt->bind_param('i', $orderId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update order status: ' . $updateStmt->error);
    }
    
    // Log admin activity
    $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
    $activityStmt = $conn->prepare("INSERT INTO admin_activity (admin_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
    $action = 'confirm_order';
    $details = json_encode([
        'order_id' => $orderId,
        'previous_status' => 'pending',
        'new_status' => 'confirmed',
        'notes' => $confirmationNotes
    ]);
    $activityStmt->bind_param('iss', $adminId, $action, $details);
    $activityStmt->execute();
    
    // Get order details for response
    $orderDetailsStmt = $conn->prepare("
        SELECT o.*, u.first_name, u.last_name, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $orderDetailsStmt->bind_param('i', $orderId);
    $orderDetailsStmt->execute();
    $orderDetails = $orderDetailsStmt->get_result()->fetch_assoc();
    
    $response = [
        'success' => true,
        'message' => 'Order confirmed successfully',
        'data' => [
            'order_id' => $orderId,
            'status' => 'confirmed',
            'customer_name' => $orderDetails['first_name'] . ' ' . $orderDetails['last_name'],
            'customer_email' => $orderDetails['email'],
            'total' => floatval($orderDetails['total']),
            'confirmation_notes' => $confirmationNotes
        ]
    ];
    
    $checkStmt->close();
    $updateStmt->close();
    $activityStmt->close();
    $orderDetailsStmt->close();
    $conn->close();
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    if (isset($conn)) $conn->close();
}
?>