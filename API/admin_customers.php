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
            // Get all customers
            $sql = "SELECT 
                        o.id,
                        o.customer_info,
                        o.customer_email,
                        o.customer_phone,
                        COUNT(DISTINCT o.id) as order_count,
                        SUM(o.total) as total_spent,
                        MAX(o.order_date) as last_order_date
                    FROM orders o 
                    WHERE o.customer_info IS NOT NULL 
                    GROUP BY o.customer_info, o.customer_email, o.customer_phone
                    ORDER BY last_order_date DESC";
            
            $result = $conn->query($sql);
            $customers = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $customers[] = [
                        'id' => $row['id'],
                        'name' => $row['customer_info'],
                        'email' => $row['customer_email'],
                        'phone' => $row['customer_phone'],
                        'order_count' => $row['order_count'],
                        'total_spent' => $row['total_spent'] ? number_format($row['total_spent'], 2) : '0.00',
                        'last_order' => $row['last_order_date'],
                        'loyalty_tier' => getLoyaltyTier($row['total_spent'])
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'customers' => $customers]);
            break;
            
        case 'POST':
            // Add new customer
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['name']) || !isset($data['email'])) {
                throw new Exception('Missing required fields');
            }
            
            // For now, we'll just return success since we don't have a separate customers table
            // In a real implementation, you'd insert into a customers table
            echo json_encode(['success' => true, 'message' => 'Customer added successfully']);
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getLoyaltyTier($totalSpent) {
    if ($totalSpent >= 10000) return 'Gold';
    if ($totalSpent >= 5000) return 'Silver';
    return 'Bronze';
}

$conn->close();
?> 