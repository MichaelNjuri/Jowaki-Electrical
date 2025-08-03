<?php
// Debug script for place_order.php
session_start();
header('Content-Type: text/html');

echo "<h1>Place Order Debug</h1>";

// Check database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit();
}
echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check if tables exist
$tables = ['users', 'orders', 'order_items'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        
        // Show table structure
        $structure = $conn->query("DESCRIBE $table");
        echo "<h3>Table '$table' structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
    }
}

// Test place_order.php with sample data
echo "<h2>Testing place_order.php</h2>";

// Simulate cart
$_SESSION['cart'] = [
    [
        'id' => 1,
        'name' => 'Test Product',
        'price' => 100.00,
        'quantity' => 2
    ]
];

// Test data
$testData = [
    'customer_info' => [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'test@example.com',
        'phone' => '1234567890',
        'address' => '123 Test Street',
        'city' => 'Test City',
        'postalCode' => '12345'
    ],
    'cart' => $_SESSION['cart'],
    'subtotal' => 200.00,
    'tax' => 32.00,
    'delivery_fee' => 0.00,
    'total' => 232.00,
    'delivery_method' => 'Standard Delivery',
    'delivery_address' => '123 Test Street, Test City, 12345',
    'payment_method' => 'Cash on Delivery',
    'order_date' => date('Y-m-d H:i:s')
];

echo "<h3>Test Data:</h3>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capture output
ob_start();
file_put_contents('php://input', json_encode($testData));
include 'place_order.php';
$output = ob_get_clean();

echo "<h3>Place Order Response:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

$conn->close();
?> 