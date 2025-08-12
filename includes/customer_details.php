<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';
require_once 'check_auth.php';

// Start session for admin authentication
session_start();

try {
    $customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($customer_id === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Customer ID is required']);
        exit();
    }

    // Check admin authentication
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied. Admin privileges required.']);
        exit();
    }

    // Get customer details
    $customer_query = "
        SELECT id, first_name, last_name, email, phone, address, city, postal_code, created_at, 
               google_id, is_google_user
        FROM users 
        WHERE id = ?
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

    // Get customer's orders
    $orders_query = "
        SELECT id, order_date, total, status, payment_method, delivery_method
        FROM orders 
        WHERE user_id = ?
        ORDER BY order_date DESC
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
            'total' => floatval($order['total']),
            'status' => $order['status'],
            'payment_method' => $order['payment_method'],
            'delivery_method' => $order['delivery_method']
        ];
    }

    // Calculate customer statistics
    $total_orders = count($orders);
    $total_spent = array_reduce($orders, function($sum, $order) {
        return $sum + $order['total'];
    }, 0);
    
    $pending_orders = array_filter($orders, function($order) {
        return $order['status'] === 'pending';
    });
    
    $completed_orders = array_filter($orders, function($order) {
        return in_array($order['status'], ['delivered', 'completed']);
    });

    $response = [
        'success' => true,
        'data' => [
            'id' => $customer['id'],
            'first_name' => $customer['first_name'],
            'last_name' => $customer['last_name'],
            'full_name' => trim($customer['first_name'] . ' ' . $customer['last_name']),
            'email' => $customer['email'],
            'phone' => $customer['phone'] ?? 'Not provided',
            'address' => $customer['address'] ?? 'Not provided',
            'city' => $customer['city'] ?? 'Not provided',
            'postal_code' => $customer['postal_code'] ?? 'Not provided',
            'created_at' => $customer['created_at'],
            'is_google_user' => !empty($customer['google_id']),
            'google_id' => $customer['google_id'] ?? null,
            'orders' => $orders,
            'statistics' => [
                'total_orders' => $total_orders,
                'total_spent' => $total_spent,
                'pending_orders' => count($pending_orders),
                'completed_orders' => count($completed_orders),
                'average_order_value' => $total_orders > 0 ? $total_spent / $total_orders : 0
            ]
        ]
    ];

    $stmt->close();
    $orders_stmt->close();
    $conn->close();

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    if (isset($conn)) $conn->close();
}
?> 