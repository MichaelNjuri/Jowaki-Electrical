<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Connect to the database
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// Query to find products with low stock
$sql = "SELECT id, name, stock, low_stock_threshold, category 
        FROM products 
        WHERE stock <= low_stock_threshold 
        AND is_active = 1 
        ORDER BY stock ASC";

$result = $conn->query($sql);

$lowStockProducts = [];
$totalLowStock = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lowStockProducts[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'stock' => (int)$row['stock'],
            'threshold' => (int)$row['low_stock_threshold'],
            'category' => $row['category']
        ];
        $totalLowStock++;
    }
}

// Generate notifications array
$notifications = [];
foreach ($lowStockProducts as $product) {
    $status = $product['stock'] == 0 ? 'out of stock' : 'low stock';
    $urgency = $product['stock'] == 0 ? 'critical' : 'warning';
    
    $notifications[] = [
        'id' => 'stock_' . $product['id'],
        'type' => 'stock_alert',
        'urgency' => $urgency,
        'title' => ucfirst($status) . ' Alert',
        'message' => $product['name'] . ' has ' . ($product['stock'] == 0 ? 'no stock remaining' : 'only ' . $product['stock'] . ' items left'),
        'details' => [
            'product_id' => $product['id'],
            'product_name' => $product['name'],
            'current_stock' => $product['stock'],
            'threshold' => $product['threshold'],
            'category' => $product['category']
        ],
        'timestamp' => date('Y-m-d H:i:s'),
        'action_url' => 'products',
        'is_read' => false
    ];
}

$response = [
    'success' => true,
    'total_low_stock' => $totalLowStock,
    'low_stock_products' => $lowStockProducts,
    'notifications' => $notifications,
    'summary' => [
        'total_notifications' => count($notifications),
        'critical_count' => count(array_filter($notifications, function($n) { return $n['urgency'] === 'critical'; })),
        'warning_count' => count(array_filter($notifications, function($n) { return $n['urgency'] === 'warning'; }))
    ]
];

echo json_encode($response);

$conn->close();
?>
