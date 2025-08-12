<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Save cart to session
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['cart'])) {
            $_SESSION['cart'] = $input['cart'];
        }
    }
    
    // Return cart items
    $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    
    echo json_encode([
        'success' => true,
        'cart_items' => $cart_items,
        'cart_count' => count($cart_items)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to sync cart: ' . $e->getMessage()
    ]);
}
?>