<?php
header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'DB connection failed']));
}

$sql = "SELECT * FROM orders ORDER BY order_date DESC";
$result = $conn->query($sql);

$orders = [];

while ($row = $result->fetch_assoc()) {
    $order_id = $row['id'];

    $item_sql = "SELECT product_name, quantity FROM order_items WHERE order_id = $order_id";
    $item_result = $conn->query($item_sql);

    $items = [];
    while ($item = $item_result->fetch_assoc()) {
        $items[] = $item['product_name'] . ' (x' . $item['quantity'] . ')';
    }

    $row['items'] = implode(', ', $items);
    $orders[] = $row;
}

echo json_encode($orders);
