<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';

try {
    $period = isset($_GET['period']) ? $_GET['period'] : 'month'; // day, week, month, year
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    
    // Build date range
    $date_condition = "";
    $params = [];
    $param_types = "";
    
    if ($start_date && $end_date) {
        $date_condition = "AND o.order_date BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $param_types .= "ss";
    } else {
        // Default to current period
        switch ($period) {
            case 'day':
                $date_condition = "AND DATE(o.order_date) = CURDATE()";
                break;
            case 'week':
                $date_condition = "AND YEARWEEK(o.order_date) = YEARWEEK(CURDATE())";
                break;
            case 'month':
                $date_condition = "AND YEAR(o.order_date) = YEAR(CURDATE()) AND MONTH(o.order_date) = MONTH(CURDATE())";
                break;
            case 'year':
                $date_condition = "AND YEAR(o.order_date) = YEAR(CURDATE())";
                break;
        }
    }

    // Sales Overview (using existing columns)
    $sales_query = "
        SELECT 
            COUNT(DISTINCT o.id) as total_orders,
            COUNT(oi.id) as total_items_sold,
            SUM(o.total_price) as total_revenue,
            AVG(o.total_price) as average_order_value,
            SUM(o.tax) as total_tax,
            SUM(o.delivery_fee) as total_delivery_fees
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.status != 'cancelled' {$date_condition}
    ";
    
    // Add category condition only if category is specified
    if ($category) {
        $sales_query .= " AND p.category = ?";
    }
    
    $stmt = $conn->prepare($sales_query);
    if ($category) {
        $stmt->bind_param('s', $category);
    }
    $stmt->execute();
    $sales_result = $stmt->get_result();
    $sales_overview = $sales_result->fetch_assoc();

    // Top Selling Products
    $top_products_query = "
        SELECT 
            p.name,
            p.category,
            SUM(oi.quantity) as total_quantity_sold,
            SUM(oi.quantity * oi.price) as total_revenue,
            COUNT(DISTINCT o.id) as order_count
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status != 'cancelled' {$date_condition}
    ";
    
    // Add category condition only if category is specified
    if ($category) {
        $top_products_query .= " AND p.category = ?";
    }
    
    $top_products_query .= " GROUP BY p.id ORDER BY total_quantity_sold DESC LIMIT 10";
    
    $top_products_stmt = $conn->prepare($top_products_query);
    if ($category) {
        $top_products_stmt->bind_param('s', $category);
    }
    $top_products_stmt->execute();
    $top_products_result = $top_products_stmt->get_result();
    $top_products = [];
    
    while ($product = $top_products_result->fetch_assoc()) {
        $top_products[] = [
            'name' => $product['name'],
            'category' => $product['category'],
            'sku' => 'N/A', // SKU column doesn't exist in products table
            'total_quantity_sold' => intval($product['total_quantity_sold']),
            'total_revenue' => floatval($product['total_revenue']),
            'order_count' => intval($product['order_count'])
        ];
    }

    // Sales by Category
    $category_sales_query = "
        SELECT 
            p.category,
            COUNT(DISTINCT o.id) as order_count,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.quantity * oi.price) as total_revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status != 'cancelled' {$date_condition}
        GROUP BY p.category
        ORDER BY total_revenue DESC
    ";
    
    $category_sales_stmt = $conn->prepare($category_sales_query);
    $category_sales_stmt->execute();
    $category_sales_result = $category_sales_stmt->get_result();
    $category_sales = [];
    
    while ($category = $category_sales_result->fetch_assoc()) {
        $category_sales[] = [
            'category' => $category['category'],
            'order_count' => intval($category['order_count']),
            'total_quantity' => intval($category['total_quantity']),
            'total_revenue' => floatval($category['total_revenue'])
        ];
    }

    // Payment Method Analysis
    $payment_analysis_query = "
        SELECT 
            payment_method,
            COUNT(*) as order_count,
            SUM(total_price) as total_revenue,
            AVG(total_price) as average_order_value
        FROM orders o
        WHERE o.status != 'cancelled' {$date_condition}
        GROUP BY payment_method
        ORDER BY total_revenue DESC
    ";
    
    $payment_stmt = $conn->prepare($payment_analysis_query);
    $payment_stmt->execute();
    $payment_result = $payment_stmt->get_result();
    $payment_analysis = [];
    
    while ($payment = $payment_result->fetch_assoc()) {
        $payment_analysis[] = [
            'payment_method' => $payment['payment_method'] ?? 'Not specified',
            'order_count' => intval($payment['order_count']),
            'total_revenue' => floatval($payment['total_revenue']),
            'average_order_value' => floatval($payment['average_order_value'])
        ];
    }

    // Daily Sales Trend (last 30 days)
    $daily_trend_query = "
        SELECT 
            DATE(o.order_date) as date,
            COUNT(DISTINCT o.id) as order_count,
            SUM(o.total_price) as daily_revenue,
            SUM(oi.quantity) as items_sold
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.status != 'cancelled' 
        AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(o.order_date)
        ORDER BY date DESC
    ";
    
    $daily_trend_stmt = $conn->prepare($daily_trend_query);
    $daily_trend_stmt->execute();
    $daily_trend_result = $daily_trend_stmt->get_result();
    $daily_trend = [];
    
    while ($day = $daily_trend_result->fetch_assoc()) {
        $daily_trend[] = [
            'date' => $day['date'],
            'order_count' => intval($day['order_count']),
            'daily_revenue' => floatval($day['daily_revenue']),
            'items_sold' => intval($day['items_sold'])
        ];
    }

    // Customer Analysis (simplified since orders table doesn't have user_id)
    $customer_analysis_query = "
        SELECT 
            COUNT(DISTINCT o.id) as unique_orders,
            0 as new_customers_30d,
            0 as new_customers_7d
        FROM orders o
        WHERE o.status != 'cancelled' {$date_condition}
    ";
    
    $customer_analysis_stmt = $conn->prepare($customer_analysis_query);
    $customer_analysis_stmt->execute();
    $customer_analysis_result = $customer_analysis_stmt->get_result();
    $customer_analysis = $customer_analysis_result->fetch_assoc();

    $response = [
        'success' => true,
        'data' => [
            'period' => $period,
            'date_range' => [
                'start_date' => $start_date,
                'end_date' => $end_date
            ],
            'sales_overview' => [
                'total_orders' => intval($sales_overview['total_orders'] ?? 0),
                'total_items_sold' => intval($sales_overview['total_items_sold'] ?? 0),
                'total_revenue' => floatval($sales_overview['total_revenue'] ?? 0),
                'average_order_value' => floatval($sales_overview['average_order_value'] ?? 0),
                'total_tax' => floatval($sales_overview['total_tax'] ?? 0),
                'total_delivery_fees' => floatval($sales_overview['total_delivery_fees'] ?? 0)
            ],
            'top_products' => $top_products,
            'category_sales' => $category_sales,
            'payment_analysis' => $payment_analysis,
            'daily_trend' => $daily_trend,
            'customer_analysis' => [
                'unique_customers' => intval($customer_analysis['unique_orders'] ?? 0),
                'new_customers_30d' => intval($customer_analysis['new_customers_30d'] ?? 0),
                'new_customers_7d' => intval($customer_analysis['new_customers_7d'] ?? 0)
            ]
        ]
    ];

    $stmt->close();
    $top_products_stmt->close();
    $category_sales_stmt->close();
    $payment_stmt->close();
    $daily_trend_stmt->close();
    $customer_analysis_stmt->close();
    $conn->close();

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    $conn->close();
}
?> 