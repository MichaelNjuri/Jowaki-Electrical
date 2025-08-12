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
    
    if (!$input || !isset($input['product_id']) || !isset($input['change'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Product ID and change are required']);
        exit();
    }

    $product_id = intval($input['product_id']);
    $change = intval($input['change']);
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    
    // Find the item in cart
    $item_index = -1;
    foreach ($cart as $index => $item) {
        if ($item['id'] === $product_id) {
            $item_index = $index;
            break;
        }
    }
    
    if ($item_index === -1) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Item not found in cart']);
        exit();
    }
    
    // Update quantity
    $new_quantity = $cart[$item_index]['quantity'] + $change;
    
    if ($new_quantity <= 0) {
        // Remove item if quantity becomes 0 or negative
        unset($cart[$item_index]);
        $cart = array_values($cart); // Re-index array
    } else {
        $cart[$item_index]['quantity'] = $new_quantity;
    }
    
    $_SESSION['cart'] = $cart;
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully',
        'cart_count' => count($cart),
        'new_quantity' => $new_quantity > 0 ? $new_quantity : 0
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 