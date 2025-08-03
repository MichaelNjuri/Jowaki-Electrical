<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';

try {
    $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($order_id === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Order ID is required']);
        exit();
    }

    // Get order details with customer information (using only existing columns)
    $order_query = "
        SELECT o.*, u.first_name, u.last_name, u.email, u.phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ";
    
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $order = $order_result->fetch_assoc();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit();
    }

    // Get order items
    $items_query = "
        SELECT oi.*, p.name as product_name, p.image_paths as product_image
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ";
    
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param('i', $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    $items = [];
    
    while ($item = $items_result->fetch_assoc()) {
        // Handle image_paths - it might be JSON or comma-separated
        $product_image = 'placeholder.jpg';
        if (!empty($item['product_image'])) {
            $image_paths = json_decode($item['product_image'], true);
            if (is_array($image_paths) && !empty($image_paths)) {
                $product_image = $image_paths[0]; // Use first image
            } else {
                $product_image = $item['product_image']; // Use as is if not JSON
            }
        }
        
        $items[] = [
            'id' => $item['id'],
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'] ?? 'Unknown Product',
            'product_image' => $product_image,
            'quantity' => $item['quantity'],
            'price' => floatval($item['price']),
            'total' => floatval($item['quantity'] * $item['price'])
        ];
    }

    // Get order status history (check if table exists first)
    $status_history = [];
    $status_query = "
        SELECT status, notes, updated_by, created_at
        FROM order_status_logs
        WHERE order_id = ?
        ORDER BY created_at DESC
    ";
    
    // Check if order_status_logs table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'order_status_logs'");
    if ($table_check && $table_check->num_rows > 0) {
        $status_stmt = $conn->prepare($status_query);
        $status_stmt->bind_param('i', $order_id);
        $status_stmt->execute();
        $status_result = $status_stmt->get_result();
        
        while ($status = $status_result->fetch_assoc()) {
            $status_history[] = [
                'status' => $status['status'],
                'notes' => $status['notes'],
                'updated_by' => $status['updated_by'],
                'created_at' => $status['created_at']
            ];
        }
        $status_stmt->close();
    }

    // Build customer information (using only existing columns)
    $customer_info = [
        'name' => trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')),
        'email' => $order['email'] ?? '',
        'phone' => $order['phone'] ?? '',
        'address' => 'Not available', // This column doesn't exist in users table
        'city' => 'Not available',    // This column doesn't exist in users table
        'postal_code' => 'Not available' // This column doesn't exist in users table
    ];

    // Calculate totals
    $subtotal = array_reduce($items, function($sum, $item) {
        return $sum + $item['total'];
    }, 0);

    $response = [
        'success' => true,
        'data' => [
            'order_id' => $order['id'],
            'order_date' => $order['order_date'],
            'status' => $order['status'],
            'customer_info' => $customer_info,
            'items' => $items,
            'subtotal' => floatval($order['total_price'] ?? $subtotal), // Use total_price from orders table
            'tax' => 0.0, // Tax column doesn't exist in orders table
            'delivery_fee' => 0.0, // Delivery fee column doesn't exist in orders table
            'total' => floatval($order['total_price'] ?? $subtotal),
            'payment_method' => $order['payment_method'] ?? 'Not specified',
            'delivery_method' => $order['delivery_method'] ?? 'Not specified',
            'delivery_address' => 'Not available', // This column doesn't exist in orders table
            'status_history' => $status_history,
            'created_at' => $order['order_date'],
            'updated_at' => $order['confirmed_at'] ?? $order['order_date']
        ]
    ];

    $stmt->close();
    $items_stmt->close();
    $conn->close();

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    $conn->close();
}
?> 