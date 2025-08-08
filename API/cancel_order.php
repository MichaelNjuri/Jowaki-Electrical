<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

require_once 'db_connection.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['order_id'])) {
        echo json_encode(['success' => false, 'error' => 'Order ID is required']);
        exit;
    }
    
    $order_id = $input['order_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if order belongs to the logged-in user
    $check_stmt = $conn->prepare("
        SELECT id, status 
        FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Order not found or access denied']);
        exit;
    }
    
    $order = $result->fetch_assoc();
    
    // Check if order can be cancelled (only pending orders can be cancelled)
    if ($order['status'] !== 'pending') {
        echo json_encode(['success' => false, 'error' => 'Only pending orders can be cancelled']);
        exit;
    }
    
    // Update order status to cancelled
    $update_stmt = $conn->prepare("
        UPDATE orders 
        SET status = 'cancelled', updated_at = NOW() 
        WHERE id = ? AND user_id = ?
    ");
    $update_stmt->bind_param("ii", $order_id, $user_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to cancel order']);
    }
    
    $update_stmt->close();
    $check_stmt->close();
    
} catch (Exception $e) {
    error_log("Error in cancel_order.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$conn->close();
?>
