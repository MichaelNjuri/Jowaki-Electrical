<?php
session_start();
header('Content-Type: application/json');

// Calculate cart count from session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_count = 0;

foreach ($cart as $item) {
    $cart_count += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'cart_count' => $cart_count
]);
?>
