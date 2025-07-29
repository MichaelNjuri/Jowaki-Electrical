<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Method 1: If user_id column exists and is populated
$sql1 = "SELECT id, order_date, status, total, customer_info, cart FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt1 = $conn->prepare($sql1);

if ($stmt1) {
    $stmt1->bind_param("i", $userId);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    
    if ($result1->num_rows > 0) {
        // Found orders using user_id column
        $orders = [];
        while ($row = $result1->fetch_assoc()) {
            $cart_data = json_decode($row['cart'], true) ?? [];
            $customer_data = json_decode($row['customer_info'], true) ?? [];
            
            $items = [];
            if (is_array($cart_data)) {
                foreach ($cart_data as $item) {
                    $items[] = [
                        'name' => $item['name'] ?? $item['product_name'] ?? 'Unknown Product',
                        'quantity' => $item['quantity'] ?? 1,
                        'price' => $item['price'] ?? 0
                    ];
                }
            }
            
            $orders[] = [
                'orderId' => $row['id'],
                'status' => $row['status'] ?? 'pending',
                'date' => $row['order_date'],
                'total' => $row['total'],
                'items' => $items,
                'customer_info' => $customer_data
            ];
        }
        
        echo json_encode($orders);
        $stmt1->close();
        $conn->close();
        exit;
    }
    $stmt1->close();
}

// Method 2: Search in customer_info JSON for user ID
// This assumes customer_info contains user_id in JSON format
$sql2 = "SELECT id, order_date, status, total, customer_info, cart FROM orders ORDER BY order_date DESC";
$stmt2 = $conn->prepare($sql2);

if ($stmt2) {
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    $orders = [];
    while ($row = $result2->fetch_assoc()) {
        $customer_data = json_decode($row['customer_info'], true);
        
        // Check if this order belongs to the current user
        $order_user_id = null;
        if (is_array($customer_data)) {
            $order_user_id = $customer_data['user_id'] ?? $customer_data['id'] ?? null;
        }
        
        if ($order_user_id == $userId) {
            $cart_data = json_decode($row['cart'], true) ?? [];
            
            $items = [];
            if (is_array($cart_data)) {
                foreach ($cart_data as $item) {
                    $items[] = [
                        'name' => $item['name'] ?? $item['product_name'] ?? 'Unknown Product',
                        'quantity' => $item['quantity'] ?? 1,
                        'price' => $item['price'] ?? 0
                    ];
                }
            }
            
            $orders[] = [
                'orderId' => $row['id'],
                'status' => $row['status'] ?? 'pending',
                'date' => $row['order_date'],
                'total' => $row['total'],
                'items' => $items,
                'customer_info' => $customer_data
            ];
        }
    }
    
    echo json_encode($orders);
    $stmt2->close();
} else {
    echo json_encode(['error' => 'Failed to prepare SQL statement']);
}

$conn->close();
?>