<?php
header('Content-Type: application/json');
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// only return active products for the store
$sql = "SELECT * FROM products WHERE active = 1";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    // decode features and specifications if they exist
    $row['features'] = isset($row['features']) && $row['features'] ? json_decode($row['features'], true) : [];
$row['specifications'] = isset($row['specifications']) && $row['specifications'] ? json_decode($row['specifications'], true) : [];

    $products[] = $row;
}

echo json_encode($products);
?>
