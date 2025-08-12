<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Product ID is required']);
        exit();
    }

    $product_id = intval($input['product_id']);
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    
    // Remove item from cart
    $cart = array_filter($cart, function($item) use ($product_id) {
        return $item['id'] !== $product_id;
    });
    
    $_SESSION['cart'] = array_values($cart); // Re-index array
    
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart',
        'cart_count' => count($cart)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 