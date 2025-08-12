<?php
session_start();
header('Content-Type: application/json');

// Simulate a logged-in user for testing
$_SESSION['user_id'] = 1;
$_SESSION['logged_in'] = true;

echo "Testing order data fetch...\n";

// Test the get_orders.php endpoint
$test_url = 'http://localhost/jowaki_electrical_srvs/API/get_orders.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response: $response\n";

// Test database connection and orders table
require_once 'db_connection.php';

echo "\nTesting database connection...\n";

try {
    // Check if orders table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($tableExists->num_rows === 0) {
        echo "❌ Orders table does not exist!\n";
    } else {
        echo "✅ Orders table exists\n";
        
        // Check table structure
        $describe = $conn->query("DESCRIBE orders");
        echo "Orders table structure:\n";
        while ($row = $describe->fetch_assoc()) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
        
        // Check for orders
        $orders = $conn->query("SELECT COUNT(*) as count FROM orders");
        $orderCount = $orders->fetch_assoc()['count'];
        echo "Total orders in database: $orderCount\n";
        
        // Show sample orders
        if ($orderCount > 0) {
            $sampleOrders = $conn->query("SELECT id, user_id, total, status, order_date FROM orders LIMIT 3");
            echo "Sample orders:\n";
            while ($order = $sampleOrders->fetch_assoc()) {
                echo "- Order #{$order['id']}: KSh {$order['total']} ({$order['status']}) - {$order['order_date']}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 