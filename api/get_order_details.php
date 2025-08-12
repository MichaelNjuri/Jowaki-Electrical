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
    if (!isset($_GET['order_id'])) {
        echo json_encode(['success' => false, 'error' => 'Order ID is required']);
        exit;
    }
    
    $order_id = $_GET['order_id'];
    $user_id = $_SESSION['user_id'];
    
    // Get order details with cart data
    $stmt = $conn->prepare("
        SELECT 
            id as order_id,
            order_date,
            total,
            status,
            cart,
            delivery_address,
            payment_method,
            delivery_method
        FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Order not found or access denied']);
        exit;
    }
    
    $row = $result->fetch_assoc();
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
    
    $order = [
        'order_id' => $row['order_id'],
        'order_date' => $row['order_date'],
        'total' => $row['total'],
        'status' => $row['status'],
        'items' => $items,
        'item_count' => $item_count,
        'delivery_address' => $row['delivery_address'] ?? '',
        'payment_method' => $row['payment_method'] ?? '',
        'delivery_method' => $row['delivery_method'] ?? ''
    ];
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'order' => $order]);
    
} catch (Exception $e) {
    error_log("Error in get_order_details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$conn->close();
?>
