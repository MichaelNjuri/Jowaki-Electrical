<?php
// Turn off error display to prevent HTML errors from breaking JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include email service
require_once 'email_service.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to send JSON response and exit
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Function to send error response
function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse(['success' => false, 'error' => $message], $statusCode);
}

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendErrorResponse('Only POST method allowed', 405);
    }

    // Include database connection
    $db_file = __DIR__ . DIRECTORY_SEPARATOR . 'db_connection.php';
    if (!file_exists($db_file)) {
        error_log("Database connection file not found: " . $db_file);
        sendErrorResponse('Database configuration error', 500);
    }
    
    require_once $db_file;
    
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        error_log("Database connection failed: " . ($conn->connect_error ?? 'Connection object not found'));
        sendErrorResponse('Database connection failed', 500);
    }

    // Get and validate input
    $raw_input = file_get_contents('php://input');
    error_log("Raw input received: " . $raw_input);
    
    if (empty($raw_input)) {
        error_log("No input data received");
        sendErrorResponse('No input data received');
    }

    $input = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg() . " - Raw input: " . $raw_input);
        sendErrorResponse('Invalid JSON data: ' . json_last_error_msg());
    }
    
    error_log("Decoded input: " . json_encode($input));

    // Validate required fields
    if (!isset($input['order_id']) || !isset($input['status'])) {
        sendErrorResponse('Missing required fields: order_id and status are required');
    }

    $order_id = intval($input['order_id']);
    if ($order_id <= 0) {
        sendErrorResponse('Invalid order ID');
    }

    $status = trim($input['status']);
    $notes = isset($input['notes']) ? trim($input['notes']) : '';
    $updated_by = isset($input['updated_by']) ? trim($input['updated_by']) : 'admin';

    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    if (!in_array($status, $valid_statuses)) {
        sendErrorResponse('Invalid status. Valid statuses are: ' . implode(', ', $valid_statuses));
    }

    // Check if order exists
    $check_stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ?");
    if (!$check_stmt) {
        error_log("Prepare failed for order check: " . $conn->error);
        sendErrorResponse('Database query preparation failed', 500);
    }
    
    $check_stmt->bind_param('i', $order_id);
    if (!$check_stmt->execute()) {
        error_log("Execute failed for order check: " . $check_stmt->error);
        $check_stmt->close();
        sendErrorResponse('Database query execution failed', 500);
    }
    
    $result = $check_stmt->get_result();
    if ($result->num_rows === 0) {
        $check_stmt->close();
        sendErrorResponse('Order not found', 404);
    }
    
    $current_order = $result->fetch_assoc();
    $check_stmt->close();

    // Update order status
    $update_stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    if (!$update_stmt) {
        error_log("Prepare failed for order update: " . $conn->error);
        sendErrorResponse('Database update preparation failed', 500);
    }
    
    $update_stmt->bind_param('si', $status, $order_id);
    if (!$update_stmt->execute()) {
        error_log("Execute failed for order update: " . $update_stmt->error);
        $update_stmt->close();
        sendErrorResponse('Failed to update order status', 500);
    }

    $affected_rows = $update_stmt->affected_rows;
    $update_stmt->close();

    if ($affected_rows === 0) {
        sendErrorResponse('No changes made to order status', 400);
    }

    // Log status change if table exists
    try {
        $table_check = $conn->query("SHOW TABLES LIKE 'order_status_logs'");
        if ($table_check && $table_check->num_rows > 0) {
            $log_stmt = $conn->prepare("INSERT INTO order_status_logs (order_id, old_status, new_status, notes, updated_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($log_stmt) {
                $old_status = $current_order['status'];
                $log_stmt->bind_param('issss', $order_id, $old_status, $status, $notes, $updated_by);
                $log_stmt->execute();
                $log_stmt->close();
            }
        }
    } catch (Exception $log_error) {
        // Log the error but don't fail the main operation
        error_log("Failed to log status change: " . $log_error->getMessage());
    }

    // Get updated order details for notification
    try {
        $order_stmt = $conn->prepare("SELECT o.*, u.email, u.phone FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        if ($order_stmt) {
            $order_stmt->bind_param('i', $order_id);
            $order_stmt->execute();
            $order_result = $order_stmt->get_result();
            $order = $order_result->fetch_assoc();
            $order_stmt->close();

            // Send notifications if customer contact info exists
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
                    $customer_email = $order['email'];
                    $customer_name = $order['customer_info'] ? json_decode($order['customer_info'], true)['firstName'] . ' ' . json_decode($order['customer_info'], true)['lastName'] : 'Customer';
                    
                    $order_data_for_email = [
                        'order_id' => $order_id,
                        'total' => $order['total'],
                        'order_date' => $order['order_date'],
                        'payment_method' => $order['payment_method'] ?? 'N/A',
                        'delivery_method' => $order['delivery_method'] ?? 'N/A',
                        'customer_info' => json_decode($order['customer_info'], true) ?? []
                    ];
                    
                    // Send order status update email
                    sendOrderStatusUpdateEmail($order_data_for_email, $customer_email, $customer_name, $status);
                }

                    // Log SMS notification (SMS service would need to be implemented)
                    if ($order['phone']) {
                        error_log("SMS notification to {$order['phone']}: {$message}");
                    }
                }
            }
        }
    } catch (Exception $notification_error) {
        // Log notification errors but don't fail the main operation
        error_log("Failed to send notifications: " . $notification_error->getMessage());
    }

    // Close database connection
    $conn->close();

    // Send success response
    sendJsonResponse([
        'success' => true,
        'message' => 'Order status updated successfully',
        'order_id' => $order_id,
        'old_status' => $current_order['status'],
        'new_status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("Error in update_order_status.php: " . $e->getMessage() . " - Stack trace: " . $e->getTraceAsString());
    
    // Send error response
    sendErrorResponse('Internal server error: ' . $e->getMessage(), 500);
    
    // Close connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
    }
} catch (Error $e) {
    // Handle fatal errors
    error_log("Fatal error in update_order_status.php: " . $e->getMessage() . " - Stack trace: " . $e->getTraceAsString());
    
    sendErrorResponse('Server error occurred', 500);
    
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>