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

    $item_sql = "SELECT product_name, quantity, price FROM order_items WHERE order_id = ?";
    $item_stmt = $conn->prepare($item_sql);
    $item_stmt->bind_param('i', $order_id);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();

    $items = [];
    while ($item = $item_result->fetch_assoc()) {
        $items[] = [
            'name' => $item['product_name'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ];
    }

    $row['items'] = $items; // Structured array instead of string
    $orders[] = $row;
}

echo json_encode($orders);
$conn->close();
?>