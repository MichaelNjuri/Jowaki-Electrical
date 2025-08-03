<?php
// Simple test for place_order.php
header('Content-Type: text/html');

echo "<h1>Testing place_order.php</h1>";

// Simulate a POST request to place_order.php
$testData = [
    'customer_info' => [
        'firstName' => 'Test',
        'lastName' => 'User',
        'email' => 'test@example.com',
        'phone' => '1234567890',
        'address' => '123 Test St',
        'city' => 'Test City',
        'postalCode' => '12345'
    ],
    'cart' => [
        [
            'id' => 1,
            'name' => 'Test Product',
            'price' => 100.00,
            'quantity' => 1
        ]
    ],
    'subtotal' => 100.00,
    'tax' => 16.00,
    'delivery_fee' => 0.00,
    'total' => 116.00,
    'delivery_method' => 'Standard Delivery',
    'delivery_address' => '123 Test St, Test City, 12345',
    'payment_method' => 'Cash on Delivery',
    'order_date' => date('Y-m-d H:i:s')
];

echo "<h2>Test Data:</h2>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Start session
session_start();
$_SESSION['cart'] = $testData['cart'];

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capture output
ob_start();

// Write test data to php://input
$input = json_encode($testData);
file_put_contents('php://input', $input);

// Include place_order.php
include 'place_order.php';

$output = ob_get_clean();

echo "<h2>Response:</h2>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Try to decode as JSON
$decoded = json_decode($output, true);
if ($decoded !== null) {
    echo "<h2>Parsed JSON:</h2>";
    echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<h2>JSON Parse Error:</h2>";
    echo "<p style='color: red;'>Failed to parse response as JSON</p>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
}
?> 