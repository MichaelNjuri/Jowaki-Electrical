<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['order_id']) || !isset($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit();
    }

    $order_id = intval($input['order_id']);
    $status = $conn->real_escape_string($input['status']);
    $notes = isset($input['notes']) ? $conn->real_escape_string($input['notes']) : '';
    $updated_by = isset($input['updated_by']) ? $conn->real_escape_string($input['updated_by']) : 'admin';

    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    if (!in_array($status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        exit();
    }

    // Update order status (using existing columns)
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update order status');
    }

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit();
    }

    // Log status change (check if table exists first)
    $table_check = $conn->query("SHOW TABLES LIKE 'order_status_logs'");
    if ($table_check && $table_check->num_rows > 0) {
        $log_stmt = $conn->prepare("INSERT INTO order_status_logs (order_id, status, notes, updated_by, created_at) VALUES (?, ?, ?, ?, NOW())");
        $log_stmt->bind_param('isss', $order_id, $status, $notes, $updated_by);
        $log_stmt->execute();
        $log_stmt->close();
    }

    // Get order details for notification
    $order_stmt = $conn->prepare("SELECT o.*, u.email, u.phone FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $order_stmt->bind_param('i', $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    $order = $order_result->fetch_assoc();

    // Send notification if customer contact info exists
    if ($order && ($order['email'] || $order['phone'])) {
        $status_messages = [
            'processing' => 'Your order is now being processed',
            'shipped' => 'Your order has been shipped',
            'delivered' => 'Your order has been delivered',
            'cancelled' => 'Your order has been cancelled',
            'refunded' => 'Your order has been refunded'
        ];

        if (isset($status_messages[$status])) {
            $message = "Order #{$order_id}: {$status_messages[$status]}";
            
            // Send email notification
            if ($order['email']) {
                $to = $order['email'];
                $subject = "Order Status Update - #{$order_id}";
                $email_message = "Dear Customer,\n\n{$message}.\n\nThank you for choosing Jowaki Electrical Services.\n\nBest regards,\nJowaki Team";
                $headers = 'From: no-reply@jowaki.com';
                mail($to, $subject, $email_message, $headers);
            }

            // Send SMS notification (if SMS service is configured)
            if ($order['phone']) {
                // SMS integration would go here
                // For now, just log the SMS notification
                error_log("SMS notification to {$order['phone']}: {$message}");
            }
        }
    }

    $stmt->close();
    $order_stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'order_id' => $order_id,
        'status' => $status
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    $conn->close();
}
?>
