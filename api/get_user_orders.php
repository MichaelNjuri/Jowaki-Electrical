<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

require_once 'db_connection.php';

try {
    $user_id = $_SESSION['user_id'];
    
    // Get user orders with cart data
    $stmt = $conn->prepare("
        SELECT 
            id as order_id,
            order_date,
            total,
            status,
            cart
        FROM orders 
        WHERE user_id = ?
        ORDER BY order_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    
    while ($row = $result->fetch_assoc()) {
        $cart_data = json_decode($row['cart'], true);
        $items = [];
        $item_count = 0;
        
        if (is_array($cart_data)) {
            foreach ($cart_data as $item) {
                // Get product details from products table if product_id exists
                $image = '../images/placeholder-product.jpg';
                $product_name = $item['name'];
                
                if (isset($item['id'])) {
                    $product_stmt = $conn->prepare("
                        SELECT name, image_paths 
                        FROM products 
                        WHERE id = ?
                    ");
                    $product_stmt->bind_param("i", $item['id']);
                    $product_stmt->execute();
                    $product_result = $product_stmt->get_result();
                    
                    if ($product_row = $product_result->fetch_assoc()) {
                        $product_name = $product_row['name'];
                        if ($product_row['image_paths']) {
                            $imagePaths = json_decode($product_row['image_paths'], true);
                            if (is_array($imagePaths) && !empty($imagePaths)) {
                                $image = '../' . $imagePaths[0];
                            } else {
                                $image = '../' . $product_row['image_paths'];
                            }
                        }
                    }
                    $product_stmt->close();
                }
                
                $items[] = [
                    'name' => $product_name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'image' => $image
                ];
                $item_count += $item['quantity'];
            }
        }
        
        $orders[] = [
            'order_id' => $row['order_id'],
            'order_date' => $row['order_date'],
            'total' => $row['total'],
            'status' => $row['status'],
            'items' => $items,
            'item_count' => $item_count
        ];
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'orders' => $orders]);
    
} catch (Exception $e) {
    error_log("Error in get_user_orders.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$conn->close();
?>
