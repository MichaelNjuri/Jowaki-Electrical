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
    $stats = [];
    
    // Get products count
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE 1");
    $stats['total_products'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Get orders stats
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending' OR status IS NULL OR status = ''");
    $stats['pending_orders'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE())");
    $stats['orders_this_month'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    $result = $conn->query("SELECT SUM(total) as revenue FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE())");
    $revenue = $result ? $result->fetch_assoc()['revenue'] : 0;
    $stats['monthly_revenue'] = $revenue ? number_format($revenue, 2) : '0.00';
    
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE())");
    $stats['monthly_sales'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Get customers count - estimate from orders since you may not have a customers table
    $result = $conn->query("SELECT COUNT(DISTINCT customer_info) as count FROM orders WHERE customer_info IS NOT NULL AND customer_info != ''");
    $stats['total_customers'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // New customers this month (estimated)
    $result = $conn->query("SELECT COUNT(DISTINCT customer_info) as count FROM orders WHERE customer_info IS NOT NULL AND customer_info != '' AND MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE())");
    $stats['new_customers'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    $stats['service_completion'] = '95%'; // Placeholder
    $stats['success'] = true;
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to fetch dashboard stats: ' . $e->getMessage()]);
}

$conn->close();
?>