<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';

try {
    $customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($customer_id === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Customer ID is required']);
        exit();
    }

    // Get customer details (using only existing columns)
    $customer_query = "
        SELECT u.*, 
               COUNT(DISTINCT o.id) as total_orders,
               SUM(o.total_price) as total_spent,
               MAX(o.order_date) as last_order_date,
               MIN(o.order_date) as first_order_date
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE u.id = ?
        GROUP BY u.id
    ";
    
    $stmt = $conn->prepare($customer_query);
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $customer_result = $stmt->get_result();
    $customer = $customer_result->fetch_assoc();

    if (!$customer) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Customer not found']);
        exit();
    }

    // Get customer orders
    $orders_query = "
        SELECT o.*, 
               COUNT(oi.id) as total_items,
               GROUP_CONCAT(p.name SEPARATOR ', ') as products
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.order_date DESC
    ";
    
    $orders_stmt = $conn->prepare($orders_query);
    $orders_stmt->bind_param('i', $customer_id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    $orders = [];
    
    while ($order = $orders_result->fetch_assoc()) {
        $orders[] = [
            'id' => $order['id'],
            'order_date' => $order['order_date'],
            'status' => $order['status'],
            'total' => floatval($order['total_price'] ?? 0), // Use total_price instead of total
            'payment_method' => $order['payment_method'] ?? 'Not specified',
            'delivery_method' => $order['delivery_method'] ?? 'Not specified',
            'total_items' => $order['total_items'],
            'products' => $order['products'] ?? 'No products'
        ];
    }

    // Get customer preferences (most ordered products)
    $preferences_query = "
        SELECT p.name, p.category, COUNT(oi.id) as order_count, SUM(oi.quantity) as total_quantity
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id = ?
        GROUP BY p.id
        ORDER BY order_count DESC
        LIMIT 5
    ";
    
    $preferences_stmt = $conn->prepare($preferences_query);
    $preferences_stmt->bind_param('i', $customer_id);
    $preferences_stmt->execute();
    $preferences_result = $preferences_stmt->get_result();
    $preferences = [];
    
    while ($pref = $preferences_result->fetch_assoc()) {
        $preferences[] = [
            'product_name' => $pref['name'],
            'category' => $pref['category'],
            'order_count' => $pref['order_count'],
            'total_quantity' => $pref['total_quantity']
        ];
    }

    // Calculate customer metrics
    $total_spent = floatval($customer['total_spent'] ?? 0);
    $total_orders = intval($customer['total_orders'] ?? 0);
    $average_order_value = $total_orders > 0 ? $total_spent / $total_orders : 0;
    
    // Customer loyalty tier (based on total spent)
    $loyalty_tier = 'Bronze';
    if ($total_spent >= 50000) {
        $loyalty_tier = 'Gold';
    } elseif ($total_spent >= 25000) {
        $loyalty_tier = 'Silver';
    }

    // Days since last order
    $days_since_last_order = 0;
    if ($customer['last_order_date']) {
        $last_order = new DateTime($customer['last_order_date']);
        $now = new DateTime();
        $days_since_last_order = $now->diff($last_order)->days;
    }

    $response = [
        'success' => true,
        'data' => [
            'customer_id' => $customer['id'],
            'name' => trim($customer['first_name'] . ' ' . $customer['last_name']),
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'address' => 'Not available', // This column doesn't exist in users table
            'city' => 'Not available',    // This column doesn't exist in users table
            'postal_code' => 'Not available', // This column doesn't exist in users table
            'created_at' => $customer['created_at'],
            'metrics' => [
                'total_orders' => $total_orders,
                'total_spent' => $total_spent,
                'average_order_value' => $average_order_value,
                'loyalty_tier' => $loyalty_tier,
                'first_order_date' => $customer['first_order_date'],
                'last_order_date' => $customer['last_order_date'],
                'days_since_last_order' => $days_since_last_order
            ],
            'orders' => $orders,
            'preferences' => $preferences
        ]
    ];

    $stmt->close();
    $orders_stmt->close();
    $preferences_stmt->close();
    $conn->close();

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    $conn->close();
}
?> 