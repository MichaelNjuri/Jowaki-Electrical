<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

try {
    // Check if customers table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'customers'");
    
    if ($tableCheck && $tableCheck->num_rows > 0) {
        // Use existing customers table
        $stmt = $conn->prepare("SELECT id, name, email, phone, location, COALESCE(orders, 0) as orders, COALESCE(total_spent, 0) as total_spent FROM customers ORDER BY id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'location' => $row['location'],
                'orders' => intval($row['orders']),
                'total_spent' => floatval($row['total_spent'])
            ];
        }
        
        echo json_encode($customers);
    } else {
        // Extract customers from orders table
        $stmt = $conn->prepare("SELECT DISTINCT customer_info, COUNT(*) as order_count, SUM(total) as total_spent FROM orders WHERE customer_info IS NOT NULL AND customer_info != '' GROUP BY customer_info ORDER BY order_count DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $customers = [];
        $id = 1;
        
        while ($row = $result->fetch_assoc()) {
            $customerInfo = json_decode($row['customer_info'], true);
            if ($customerInfo && is_array($customerInfo)) {
                $firstName = $customerInfo['firstName'] ?? $customerInfo['first_name'] ?? '';
                $lastName = $customerInfo['lastName'] ?? $customerInfo['last_name'] ?? '';
                $email = $customerInfo['email'] ?? '';
                $phone = $customerInfo['phone'] ?? '';
                $address = $customerInfo['address'] ?? '';
                $city = $customerInfo['city'] ?? '';
                
                if ($firstName || $lastName || $email) {
                    $customers[] = [
                        'id' => $id++,
                        'name' => trim($firstName . ' ' . $lastName),
                        'email' => $email,
                        'phone' => $phone,
                        'location' => trim($city . ' ' . $address),
                        'orders' => intval($row['order_count']),
                        'total_spent' => floatval($row['total_spent'])
                    ];
                }
            }
        }
        
        echo json_encode($customers);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to fetch customers: ' . $e->getMessage()]);
}

$conn->close();
?>