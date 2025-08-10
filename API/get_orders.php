<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // First, check if the orders table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($tableExists->num_rows === 0) {
        // Orders table doesn't exist, return empty array
        echo json_encode([]);
        exit;
    }

    // Check what columns exist in the orders table
    $checkColumns = $conn->query("DESCRIBE orders");
    $existingColumns = [];
    while ($row = $checkColumns->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
    }

    // Build query based on existing columns
    $requiredColumns = ['id', 'user_id', 'cart', 'total', 'status', 'order_date'];
    $optionalColumns = ['customer_info', 'subtotal', 'tax', 'delivery_fee', 'delivery_method', 'delivery_address', 'payment_method', 'confirmed_at'];
    
    $availableColumns = [];
    
    foreach ($requiredColumns as $column) {
        if (in_array($column, $existingColumns)) {
            $availableColumns[] = $column;
        }
    }
    
    foreach ($optionalColumns as $column) {
        if (in_array($column, $existingColumns)) {
            $availableColumns[] = $column;
        }
    }

    $columnsString = implode(', ', $availableColumns);
    
    // Get all orders for admin view
    $stmt = $conn->prepare("SELECT $columnsString FROM orders ORDER BY order_date DESC");
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Database execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $orders = [];
    
    while ($row = $result->fetch_assoc()) {
        // Parse cart items
        $cart_items = json_decode($row['cart'], true);
        $items = [];
        $item_count = 0;
        
        if (is_array($cart_items)) {
            foreach ($cart_items as $item) {
                if (isset($item['name'])) {
                    $items[] = $item['name'];
                    $item_count += isset($item['quantity']) ? (int)$item['quantity'] : 1;
                }
            }
        }
        
        // Format order data with safe defaults
        $order = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'items' => $items,
            'item_count' => $item_count,
            'total' => isset($row['total']) ? (float)$row['total'] : 0,
            'status' => $row['status'] ?? 'pending',
            'order_date' => $row['order_date'],
            'cart' => $cart_items
        ];
        
        // Add optional fields if they exist
        if (isset($row['customer_info'])) {
            $order['customer_info'] = json_decode($row['customer_info'], true);
        }
        if (isset($row['subtotal'])) {
            $order['subtotal'] = (float)$row['subtotal'];
        }
        if (isset($row['tax'])) {
            $order['tax'] = (float)$row['tax'];
        }
        if (isset($row['delivery_fee'])) {
            $order['delivery_fee'] = (float)$row['delivery_fee'];
        }
        if (isset($row['delivery_method'])) {
            $order['delivery_method'] = $row['delivery_method'];
        }
        if (isset($row['delivery_address'])) {
            $order['delivery_address'] = $row['delivery_address'];
        }
        if (isset($row['payment_method'])) {
            $order['payment_method'] = $row['payment_method'];
        }
        if (isset($row['confirmed_at'])) {
            $order['confirmed_at'] = $row['confirmed_at'];
        }
        
        $orders[] = $order;
    }
    
    $stmt->close();
    $conn->close();
    
    // Log activity
    logAdminActivity('View Orders', 'Viewed all orders in admin dashboard');
    
    echo json_encode($orders);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving orders: ' . $e->getMessage()
    ]);
}
?>