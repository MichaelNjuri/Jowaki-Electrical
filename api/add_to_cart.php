<?php
session_start();
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
if (!isset($input['id']) || !isset($input['name']) || !isset($input['price'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields: id, name, price']);
    exit;
}

$product_id = (int)$input['id'];
$product_name = $input['name'];
$product_price = (float)$input['price'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
$product_image = isset($input['image']) ? $input['image'] : 'placeholder.jpg';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if item already exists in cart
$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['id'] == $product_id) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}

// If not found, add new item
if (!$found) {
    $_SESSION['cart'][] = [
        'id' => $product_id,
        'name' => $product_name,
        'price' => $product_price,
        'quantity' => $quantity,
        'image' => $product_image
    ];
}

// Calculate new cart count (unique items)
$cart_count = count($_SESSION['cart']);

echo json_encode([
    'success' => true,
    'message' => 'Item added to cart successfully',
    'cart_count' => $cart_count
]);
?>
