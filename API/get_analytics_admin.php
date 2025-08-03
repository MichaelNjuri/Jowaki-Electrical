<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get current date and calculate periods
    $currentDate = date('Y-m-d');
    $currentMonth = date('Y-m-01');
    $lastMonth = date('Y-m-01', strtotime('-1 month'));
    $currentYear = date('Y-01-01');
    $last30Days = date('Y-m-d', strtotime('-30 days'));
    $last7Days = date('Y-m-d', strtotime('-7 days'));

    $analytics = [];

    // 1. SALES ANALYTICS
    // Total sales this month vs last month
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(total), 0) as total_revenue
        FROM orders 
        WHERE DATE(order_date) >= ?
    ");
    $stmt->execute([$currentMonth]);
    $currentMonthSales = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt->execute([$lastMonth]);
    $lastMonthSales = $stmt->fetch(PDO::FETCH_ASSOC);

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
    $stmt->execute([$last7Days]);
    $dailySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    $stmt->execute([$currentMonth]);
    $orderStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $analytics['order_status'] = array_map(function($status) {
        return [
            'status' => $status['status'],
            'count' => intval($status['count'])
        ];
    }, $orderStatus);

    // 4. TOP PRODUCTS (simplified - just from products table)
    $stmt = $conn->prepare("
        SELECT 
            name,
            category,
            stock,
            price
        FROM products 
        ORDER BY stock DESC
        LIMIT 10
    ");
    $stmt->execute();
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $analytics['top_products'] = array_map(function($product) {
        return [
            'name' => $product['name'],
            'category' => $product['category'],
            'stock' => intval($product['stock']),
            'price' => floatval($product['price'])
        ];
    }, $topProducts);

    // 5. CATEGORY PERFORMANCE (simplified)
    $stmt = $conn->prepare("
        SELECT 
            category,
            COUNT(*) as total_products,
            COALESCE(SUM(stock), 0) as total_stock,
            COALESCE(AVG(price), 0) as avg_price
        FROM products 
        GROUP BY category
        ORDER BY total_products DESC
    ");
    $stmt->execute();
    $categoryPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $analytics['category_performance'] = array_map(function($category) {
        return [
            'category' => $category['category'],
            'total_products' => intval($category['total_products']),
            'total_stock' => intval($category['total_stock']),
            'avg_price' => floatval($category['avg_price'])
        ];
    }, $categoryPerformance);

    // 6. CUSTOMER ANALYTICS (simplified - just count orders)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_orders 
        FROM orders
    ");
    $stmt->execute();
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("
        SELECT COUNT(*) as monthly_orders 
        FROM orders 
        WHERE DATE(order_date) >= ?
    ");
    $stmt->execute([$currentMonth]);
    $monthlyOrders = $stmt->fetch(PDO::FETCH_ASSOC);

    $analytics['customers'] = [
        'total_orders' => intval($totalOrders['total_orders']),
        'monthly_orders' => intval($monthlyOrders['monthly_orders'])
    ];

    // 7. INVENTORY ALERTS
    $stmt = $conn->prepare("
        SELECT 
            name,
            stock,
            low_stock_threshold
        FROM products 
        WHERE stock <= low_stock_threshold
        ORDER BY stock ASC
        LIMIT 10
    ");
    $stmt->execute();
    $lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $analytics['inventory_alerts'] = array_map(function($product) {
        return [
            'name' => $product['name'],
            'current_stock' => intval($product['stock']),
            'threshold' => intval($product['low_stock_threshold'])
        ];
    }, $lowStockProducts);

    echo json_encode([
        'success' => true,
        'data' => $analytics
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn = null;
?>
