<?php
// Test script for place_order.php
session_start();

// Simulate a cart session
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

// Include the place_order.php script
ob_start();
$_POST['data'] = json_encode($testData);
include 'place_order.php';
$output = ob_get_clean();

echo "Test completed. Output:\n";
echo $output;
?> 