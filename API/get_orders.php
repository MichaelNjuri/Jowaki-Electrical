<?php
session_start();
header('Content-Type: application/json');
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$sql = "SELECT id, order_date, status, total FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['id'];

        // Fetch order items
        $item_sql = "SELECT product_name, quantity, price FROM order_items WHERE order_id = ?";
        $item_stmt = $conn->prepare($item_sql);

        if ($item_stmt) {
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

            $item_stmt->close();
        }

        $orders[] = [
            'orderId' => $row['id'],
            'status' => $row['status'] ?? 'Pending',
            'date' => $row['order_date'],
            'total' => $row['total'] ?? array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $items)),
            'items' => $items
        ];
    }

    echo json_encode($orders);
    $stmt->close();
} else {
    echo json_encode(['error' => 'Failed to prepare the SQL statement']);
}

$conn->close();
?>
