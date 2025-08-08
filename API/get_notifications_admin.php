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
            // Get all notifications for admin
            $notifications = [];
            
            // Check for low stock notifications
            $lowStockSql = "SELECT 
                                p.id,
                                p.name as product_name,
                                p.stock,
                                p.low_stock_threshold,
                                'low_stock' as type,
                                CONCAT('Low stock alert: ', p.name, ' (', p.stock, ' remaining)') as message,
                                NOW() as created_at
                            FROM products p 
                            WHERE p.stock <= p.low_stock_threshold 
                            AND p.stock > 0
                            ORDER BY p.stock ASC";
            
            $result = $conn->query($lowStockSql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $notifications[] = [
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'message' => $row['message'],
                        'product_name' => $row['product_name'],
                        'stock_quantity' => $row['stock'],
                        'threshold' => $row['low_stock_threshold'],
                        'created_at' => $row['created_at'],
                        'urgency' => $row['stock'] == 0 ? 'critical' : 'warning'
                    ];
                }
            }
            
            // Check for out of stock notifications
            $outOfStockSql = "SELECT 
                                p.id,
                                p.name as product_name,
                                p.stock,
                                'out_of_stock' as type,
                                CONCAT('Out of stock: ', p.name) as message,
                                NOW() as created_at
                            FROM products p 
                            WHERE p.stock = 0
                            ORDER BY p.name ASC";
            
            $result = $conn->query($outOfStockSql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $notifications[] = [
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'message' => $row['message'],
                        'product_name' => $row['product_name'],
                        'stock_quantity' => $row['stock'],
                        'created_at' => $row['created_at'],
                        'urgency' => 'critical'
                    ];
                }
            }
            
            // Check for recent orders (last 24 hours)
            $recentOrdersSql = "SELECT 
                                o.id,
                                o.customer_info,
                                o.total,
                                o.status,
                                'new_order' as type,
                                CONCAT('New order #', o.id, ' from ', o.customer_info) as message,
                                o.order_date as created_at
                            FROM orders o 
                            WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                            ORDER BY o.order_date DESC
                            LIMIT 10";
            
            // Check for recent contact messages (last 24 hours)
            $recentContactSql = "SELECT 
                                cm.id,
                                cm.name,
                                cm.email,
                                cm.subject,
                                'contact_message' as type,
                                CONCAT('New contact message from ', cm.name, ': ', cm.subject) as message,
                                cm.created_at
                            FROM contact_messages cm 
                            WHERE cm.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                            AND cm.is_read = FALSE
                            ORDER BY cm.created_at DESC
                            LIMIT 10";
            
            $result = $conn->query($recentOrdersSql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $notifications[] = [
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'message' => $row['message'],
                        'customer_name' => $row['customer_info'],
                        'order_total' => $row['total'],
                        'order_status' => $row['status'],
                        'created_at' => $row['created_at'],
                        'urgency' => 'info'
                    ];
                }
            }
            
            // Add contact messages to notifications
            $result = $conn->query($recentContactSql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $notifications[] = [
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'message' => $row['message'],
                        'contact_name' => $row['name'],
                        'contact_email' => $row['email'],
                        'contact_subject' => $row['subject'],
                        'created_at' => $row['created_at'],
                        'urgency' => 'info'
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'notifications' => $notifications]);
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>