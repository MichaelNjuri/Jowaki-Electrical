<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

try {
    // First, let's check if the orders table exists and has data
    $tableCheck = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($tableCheck->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Orders table does not exist']);
        exit;
    }

    // Check table structure
    $structureResult = $conn->query("DESCRIBE orders");
    $columns = [];
    while ($row = $structureResult->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    // Count total orders
    $countResult = $conn->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $countResult->fetch_assoc()['total'];

    // If no orders, return early with debug info
    if ($totalOrders == 0) {
        echo json_encode([
            'success' => true, 
            'data' => [],
            'message' => 'No orders found in database',
            'debug' => [
                'table_exists' => true,
                'total_orders' => $totalOrders,
                'columns' => $columns
            ]
        ]);
        exit;
    }

    // Fetch orders with all available columns
    $stmt = $conn->prepare("SELECT * FROM orders ORDER BY order_date DESC");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];

    while ($order = $result->fetch_assoc()) {
        // Try to parse customer_info
        $customer_info = [];
        if (isset($order['customer_info']) && !empty($order['customer_info'])) {
            $customer_info = json_decode($order['customer_info'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON parsing fails, create default structure
                $customer_info = [
                    'firstName' => 'Unknown',
                    'lastName' => 'Customer', 
                    'email' => '',
                    'phone' => '',
                    'address' => '',
                    'city' => '',
                    'postalCode' => ''
                ];
            }
        } else {
            // Try alternative column names or create defaults
            $customer_info = [
                'firstName' => $order['customer_name'] ?? $order['name'] ?? 'Unknown',
                'lastName' => '',
                'email' => $order['customer_email'] ?? $order['email'] ?? '',
                'phone' => $order['customer_phone'] ?? $order['phone'] ?? '',
                'address' => $order['customer_address'] ?? $order['address'] ?? '',
                'city' => $order['customer_city'] ?? $order['city'] ?? '',
                'postalCode' => $order['postal_code'] ?? ''
            ];
        }

        // Try to parse cart/items
        $cart = [];
        if (isset($order['cart']) && !empty($order['cart'])) {
            $cart = json_decode($order['cart'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $cart = [];
            }
        } else if (isset($order['items']) && !empty($order['items'])) {
            $cart = json_decode($order['items'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $cart = [];
            }
        }

        // Build the customer address
        $addressParts = array_filter([
            $customer_info['address'] ?? '',
            $customer_info['city'] ?? '',
            $customer_info['postalCode'] ?? ''
        ]);
        $customerAddress = implode(', ', $addressParts);

        // Build order array with fallbacks for different column naming conventions
        $orders[] = [
            'id' => $order['id'],
            'customer_name' => trim(($customer_info['firstName'] ?? '') . ' ' . ($customer_info['lastName'] ?? '')),
            'customer_email' => $customer_info['email'] ?? '',
            'customer_phone' => $customer_info['phone'] ?? '',
            'customer_address' => $customerAddress,
            'order_date' => $order['order_date'] ?? $order['created_at'] ?? date('Y-m-d H:i:s'),
            'items' => $cart,
            'subtotal' => floatval($order['subtotal'] ?? $order['sub_total'] ?? 0),
            'tax' => floatval($order['tax'] ?? 0),
            'shipping' => floatval($order['delivery_fee'] ?? $order['shipping_fee'] ?? $order['shipping'] ?? 0),
            'total_amount' => floatval($order['total'] ?? $order['total_amount'] ?? 0),
            'payment_method' => $order['payment_method'] ?? 'Not specified',
            'shipping_method' => $order['delivery_method'] ?? $order['shipping_method'] ?? 'Not specified',
            'status' => $order['status'] ?? 'pending'
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $orders,
        'debug' => [
            'total_orders_in_db' => $totalOrders,
            'orders_returned' => count($orders),
            'table_columns' => $columns,
            'sample_raw_order' => isset($orders[0]) ? 'Available' : 'None'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to fetch orders: ' . $e->getMessage(),
        'debug' => [
            'columns' => $columns ?? [],
            'total_orders' => $totalOrders ?? 0
        ]
    ]);
}

$stmt->close();
$conn->close();
?>