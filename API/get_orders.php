<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Error reporting for debugging
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

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

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
    
    // Get user orders
    $stmt = $conn->prepare("SELECT $columnsString FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    
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
            'orderId' => 'JES-' . str_pad($row['id'], 6, '0', STR_PAD_LEFT),
            'total' => (float)($row['total'] ?? 0),
            'subtotal' => (float)($row['subtotal'] ?? 0),
            'tax' => (float)($row['tax'] ?? 0),
            'delivery_fee' => (float)($row['delivery_fee'] ?? 0),
            'status' => ucfirst($row['status'] ?? 'pending'),
            'date' => $row['order_date'] ?? date('Y-m-d H:i:s'),
            'created_at' => $row['order_date'] ?? date('Y-m-d H:i:s'),
            'confirmed_at' => $row['confirmed_at'] ?? null,
            'delivery_method' => $row['delivery_method'] ?? 'Standard',
            'delivery_address' => $row['delivery_address'] ?? '',
            'payment_method' => $row['payment_method'] ?? 'Cash on Delivery',
            'items' => $items,
            'item_count' => $item_count
        ];
        
        $orders[] = $order;
    }
    
    echo json_encode($orders);
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log('Get orders error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch orders: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>