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
    // Check if notifications table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    
    if ($tableCheck && $tableCheck->num_rows > 0) {
        // Use existing notifications table
        $stmt = $conn->prepare("SELECT id, message, type, created_at FROM notifications ORDER BY created_at DESC LIMIT 20");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['id'],
                'message' => $row['message'],
                'type' => $row['type'],
                'created_at' => $row['created_at']
            ];
        }
        
        echo json_encode($notifications);
    } else {
        // Generate dynamic notifications based on system status
        $notifications = [];
        
        // Check for low stock products
        $lowStockResult = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock <= 5 AND stock > 0");
        if ($lowStockResult) {
            $lowStock = $lowStockResult->fetch_assoc()['count'];
            if ($lowStock > 0) {
                $notifications[] = [
                    'id' => 1,
                    'message' => "$lowStock products are running low on stock",
                    'type' => 'warning'
                ];
            }
        }
        
        // Check for pending orders
        $pendingResult = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending' OR status IS NULL OR status = ''");
        if ($pendingResult) {
            $pending = $pendingResult->fetch_assoc()['count'];
            if ($pending > 0) {
                $notifications[] = [
                    'id' => 2,
                    'message' => "$pending orders are pending confirmation",
                    'type' => 'info'
                ];
            }
        }
        
        // Check total orders today
        $todayResult = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()");
        if ($todayResult) {
            $today = $todayResult->fetch_assoc()['count'];
            if ($today > 0) {
                $notifications[] = [
                    'id' => 3,
                    'message' => "$today new orders received today",
                    'type' => 'success'
                ];
            }
        }
        
        // Default welcome message if no other notifications
        if (empty($notifications)) {
            $notifications[] = [
                'id' => 1,
                'message' => 'Welcome to your admin dashboard! All systems are running smoothly.',
                'type' => 'info'
            ];
        }
        
        echo json_encode($notifications);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to fetch notifications: ' . $e->getMessage()]);
}

$conn->close();
?>