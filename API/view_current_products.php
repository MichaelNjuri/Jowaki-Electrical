<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$sql = "SELECT id, name, description, price, discount_price, category, stock, is_active
        FROM products 
        ORDER BY name ASC";

$result = $conn->query($sql);

$products = [];

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Query failed: ' . $conn->error]);
    $conn->close();
    exit();
}

while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => intval($row['id']),
        'name' => $row['name'],
        'description' => $row['description'] ?? '',
        'price' => floatval($row['price']),
        'category' => $row['category'] ?? '',
        'stock' => intval($row['stock'] ?? 0),
        'is_active' => intval($row['is_active'] ?? 1)
    ];
}

echo json_encode([
    'success' => true, 
    'products' => $products,
    'total' => count($products)
]);
$conn->close();
?>
