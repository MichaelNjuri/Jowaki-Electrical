<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connection.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Get all orders with customer information
            $sql = "SELECT 
                        o.id,
                        o.user_id,
                        o.subtotal,
                        o.tax,
                        o.delivery_fee,
                        o.total,
                        o.delivery_method,
                        o.delivery_address,
                        o.payment_method,
                        o.order_date,
                        o.status,
                        u.first_name,
                        u.last_name,
                        u.email,
                        u.phone,
                        u.address,
                        u.city,
                        u.postal_code
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.id
                    ORDER BY o.order_date DESC";
            
            $result = $conn->query($sql);
            $orders = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Get order items
                    $items = [];
                    $itemsSql = "SELECT oi.*, p.name as product_name, p.price as product_price 
                                FROM order_items oi 
                                LEFT JOIN products p ON oi.product_id = p.id 
                                WHERE oi.order_id = ?";
                    $itemsStmt = $conn->prepare($itemsSql);
                    $itemsStmt->bind_param("i", $row['id']);
                    $itemsStmt->execute();
                    $itemsResult = $itemsStmt->get_result();
                    
                    while ($item = $itemsResult->fetch_assoc()) {
                        $items[] = [
                            'id' => $item['id'],
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product_name'],
                            'quantity' => $item['quantity'],
                            'price' => $item['price']
                        ];
                    }
                    
                    $orders[] = [
                        'id' => $row['id'],
                        'user_id' => $row['user_id'],
                        'order_date' => $row['order_date'],
                        'customer_name' => ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''),
                        'customer_email' => $row['email'] ?? '',
                        'customer_phone' => $row['phone'] ?? '',
                        'customer_address' => ($row['address'] ?? '') . ', ' . ($row['city'] ?? '') . ' ' . ($row['postal_code'] ?? ''),
                        'items' => $items,
                        'subtotal' => $row['subtotal'],
                        'shipping' => $row['delivery_fee'],
                        'tax' => $row['tax'],
                        'total_amount' => $row['total'],
                        'payment_method' => $row['payment_method'],
                        'shipping_method' => $row['delivery_method'],
                        'status' => $row['status'] ?: 'pending'
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'data' => $orders]);
            break;
            
        case 'POST':
            // Add new order
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['user_id']) || !isset($data['total_amount'])) {
                throw new Exception('Missing required fields: user_id and total_amount');
            }
            
            $userId = (int)$data['user_id'];
            $subtotal = isset($data['subtotal']) ? (float)$data['subtotal'] : 0;
            $tax = isset($data['tax']) ? (float)$data['tax'] : 0;
            $deliveryFee = isset($data['delivery_fee']) ? (float)$data['delivery_fee'] : 0;
            $totalAmount = (float)$data['total_amount'];
            $deliveryMethod = isset($data['delivery_method']) ? $conn->real_escape_string($data['delivery_method']) : 'standard';
            $deliveryAddress = isset($data['delivery_address']) ? $conn->real_escape_string($data['delivery_address']) : '';
            $paymentMethod = isset($data['payment_method']) ? $conn->real_escape_string($data['payment_method']) : 'mpesa';
            $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'pending';
            
            $sql = "INSERT INTO orders (user_id, subtotal, tax, delivery_fee, total, delivery_method, delivery_address, payment_method, order_date, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iddddsss", $userId, $subtotal, $tax, $deliveryFee, $totalAmount, $deliveryMethod, $deliveryAddress, $paymentMethod, $status);
            
            if ($stmt->execute()) {
                $orderId = $conn->insert_id;
                
                // Add order items if provided
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                        $itemStmt = $conn->prepare($itemSql);
                        $itemStmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
                        $itemStmt->execute();
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Order added successfully', 'id' => $orderId]);
            } else {
                throw new Exception('Failed to add order');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>