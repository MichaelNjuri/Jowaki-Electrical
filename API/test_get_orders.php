<?php
// Test the get_orders.php endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing get_orders.php Endpoint</h1>";

// Simulate a session
session_start();

// Set test user ID (you can change this to test with different users)
$_SESSION['user_id'] = 1; // Change this to test with different user IDs
$_SESSION['logged_in'] = true;

echo "<h2>Test Session</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>Logged in: " . ($_SESSION['logged_in'] ? 'Yes' : 'No') . "</p>";

// Test the get_orders.php endpoint
echo "<h2>Testing get_orders.php</h2>";

// Include the get_orders.php file
ob_start();
include 'get_orders.php';
$output = ob_get_clean();

echo "<h3>Raw Output:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Try to decode JSON
$jsonData = json_decode($output, true);
if ($jsonData === null) {
    echo "<h3>❌ Invalid JSON Response</h3>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
} else {
    echo "<h3>✅ Valid JSON Response</h3>";
    echo "<h4>Response Data:</h4>";
    echo "<pre>" . print_r($jsonData, true) . "</pre>";
    
    if (is_array($jsonData)) {
        echo "<h4>Orders Found: " . count($jsonData) . "</h4>";
        
        if (count($jsonData) > 0) {
            echo "<table border='1'>";
            echo "<tr><th>Order ID</th><th>Total</th><th>Status</th><th>Date</th><th>Items</th></tr>";
            
            foreach ($jsonData as $order) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($order['orderId'] ?? 'N/A') . "</td>";
                echo "<td>KSh " . number_format($order['total'] ?? 0, 2) . "</td>";
                echo "<td>" . htmlspecialchars($order['status'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($order['date'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars(implode(', ', $order['items'] ?? [])) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No orders found for this user.</p>";
        }
    }
}

echo "<h2>Testing with different user IDs</h2>";

// Test with different user IDs
$testUserIds = [1, 2, 3, 4, 5];

foreach ($testUserIds as $userId) {
    echo "<h3>Testing User ID: $userId</h3>";
    
    $_SESSION['user_id'] = $userId;
    
    ob_start();
    include 'get_orders.php';
    $output = ob_get_clean();
    
    $jsonData = json_decode($output, true);
    if ($jsonData !== null && is_array($jsonData)) {
        echo "<p>Orders found: " . count($jsonData) . "</p>";
    } else {
        echo "<p>No orders or error for user ID: $userId</p>";
    }
}
?> 