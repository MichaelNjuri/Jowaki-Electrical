<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Get current date and calculate periods
    $currentDate = date('Y-m-d');
    $currentMonth = date('Y-m-01');
    $lastMonth = date('Y-m-01', strtotime('-1 month'));
    $currentYear = date('Y-01-01');
    $last30Days = date('Y-m-d', strtotime('-30 days'));
    $last7Days = date('Y-m-d', strtotime('-7 days'));

    $analytics = [];

    // Check if orders table exists
    $ordersTableExists = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($ordersTableExists->num_rows > 0) {
        // 1. SALES ANALYTICS
        // Total sales this month vs last month
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total), 0) as total_revenue
            FROM orders 
            WHERE DATE(order_date) >= ?
        ");
        $stmt->bind_param("s", $currentMonth);
        $stmt->execute();
        $currentMonthSales = $stmt->get_result()->fetch_assoc();

        $stmt->bind_param("s", $lastMonth);
        $stmt->execute();
        $lastMonthSales = $stmt->get_result()->fetch_assoc();

        $analytics['sales'] = [
            'current_month' => [
                'orders' => intval($currentMonthSales['total_orders']),
                'revenue' => floatval($currentMonthSales['total_revenue'])
            ],
            'last_month' => [
                'orders' => intval($lastMonthSales['total_orders']),
                'revenue' => floatval($lastMonthSales['total_revenue'])
            ]
        ];

        // 2. DAILY SALES (Last 7 days)
        $stmt = $conn->prepare("
            SELECT 
                DATE(order_date) as date,
                COUNT(*) as orders,
                COALESCE(SUM(total), 0) as revenue
            FROM orders 
            WHERE DATE(order_date) >= ?
            GROUP BY DATE(order_date)
            ORDER BY DATE(order_date)
        ");
        $stmt->bind_param("s", $last7Days);
        $stmt->execute();
        $dailySalesResult = $stmt->get_result();
        $dailySales = [];
        while ($row = $dailySalesResult->fetch_assoc()) {
            $dailySales[] = $row;
        }

        $analytics['daily_sales'] = array_map(function($day) {
            return [
                'date' => $day['date'],
                'orders' => intval($day['orders']),
                'revenue' => floatval($day['revenue'])
            ];
        }, $dailySales);

        // 3. ORDER STATUS BREAKDOWN
        $stmt = $conn->prepare("
            SELECT 
                status,
                COUNT(*) as count
            FROM orders 
            WHERE DATE(order_date) >= ?
            GROUP BY status
        ");
        $stmt->bind_param("s", $currentMonth);
        $stmt->execute();
        $orderStatusResult = $stmt->get_result();
        $orderStatus = [];
        while ($row = $orderStatusResult->fetch_assoc()) {
            $orderStatus[] = $row;
        }

        $analytics['order_status'] = array_map(function($status) {
            return [
                'status' => $status['status'],
                'count' => intval($status['count'])
            ];
        }, $orderStatus);
    } else {
        // No orders table, provide empty analytics
        $analytics['sales'] = [
            'current_month' => ['orders' => 0, 'revenue' => 0],
            'last_month' => ['orders' => 0, 'revenue' => 0]
        ];
        $analytics['daily_sales'] = [];
        $analytics['order_status'] = [];
    }

    // 4. PRODUCT ANALYTICS
    $productsTableExists = $conn->query("SHOW TABLES LIKE 'products'");
    if ($productsTableExists->num_rows > 0) {
        // Total products
        $totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
        
        // Low stock products (assuming stock < 10 is low)
        $lowStockProducts = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock < 10")->fetch_assoc()['count'];
        
        // Out of stock products
        $outOfStockProducts = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock = 0")->fetch_assoc()['count'];

        $analytics['products'] = [
            'total' => intval($totalProducts),
            'low_stock' => intval($lowStockProducts),
            'out_of_stock' => intval($outOfStockProducts)
        ];
    } else {
        $analytics['products'] = [
            'total' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0
        ];
    }

    // 5. CUSTOMER ANALYTICS
    $usersTableExists = $conn->query("SHOW TABLES LIKE 'users'");
    if ($usersTableExists->num_rows > 0) {
        // Total customers
        $totalCustomers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        
        // New customers this month
        $newCustomers = $conn->query("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE DATE(created_at) >= '$currentMonth'
        ")->fetch_assoc()['count'];

        $analytics['customers'] = [
            'total' => intval($totalCustomers),
            'new_this_month' => intval($newCustomers)
        ];
    } else {
        $analytics['customers'] = [
            'total' => 0,
            'new_this_month' => 0
        ];
    }

    $conn->close();
    
    // Log activity
    logAdminActivity('View Analytics', 'Viewed analytics dashboard');
    
    echo json_encode($analytics);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving analytics: ' . $e->getMessage()
    ]);
}
?>
