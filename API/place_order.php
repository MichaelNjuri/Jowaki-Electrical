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
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    error_log('Database connection failed: ' . $conn->connect_error);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input: ' . json_last_error_msg()]);
    error_log('Invalid JSON input: ' . json_last_error_msg());
    exit;
}

$required_fields = ['customer_info', 'cart', 'subtotal', 'tax', 'delivery_fee', 'total', 'delivery_method', 'delivery_address', 'payment_method'];
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        error_log("Missing required field: $field");
        exit;
    }
}

$required_customer_fields = ['firstName', 'lastName', 'email', 'phone', 'address', 'city'];
foreach ($required_customer_fields as $field) {
    if (!isset($input['customer_info'][$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required customer field: $field"]);
        error_log("Missing required customer field: $field");
        exit;
    }
}

if (!is_array($input['cart']) || empty($input['cart'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cart is empty or invalid']);
    error_log('Cart is empty or invalid');
    exit;
}

$user_id = isset($_SESSION['user_id']) && $_SESSION['logged_in'] ? $_SESSION['user_id'] : null;

// Check if user exists by email for guest checkout
if (!$user_id) {
    $email = $input['customer_info']['email'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_id = $row['id'];
    } else {
        // Create new user for guest checkout (without password)
        $stmt = $conn->prepare("
            INSERT INTO users (first_name, last_name, email, phone, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param(
            "ssss",
            $input['customer_info']['firstName'],
            $input['customer_info']['lastName'],
            $input['customer_info']['email'],
            $input['customer_info']['phone']
        );
        $stmt->execute();
        $user_id = $conn->insert_id;
    }
    $stmt->close();
}

$conn->begin_transaction();

try {
    $customer_info = json_encode($input['customer_info']);
    $cart = json_encode($input['cart']);
    $status = 'pending';

    $stmt = $conn->prepare("
        INSERT INTO orders (
            customer_info, cart, subtotal, tax, delivery_fee, total, delivery_method,
            delivery_address, payment_method, user_id, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssddddsssis",
        $customer_info,
        $cart,
        $input['subtotal'],
        $input['tax'],
        $input['delivery_fee'],
        $input['total'],
        $input['delivery_method'],
        $input['delivery_address'],
        $input['payment_method'],
        $user_id,
        $status
    );

    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();

    $item_stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, quantity, price)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($input['cart'] as $item) {
        if (!isset($item['id'], $item['name'], $item['quantity'], $item['price'])) {
            throw new Exception('Invalid cart item format');
        }
        $item_stmt->bind_param(
            "iisid",
            $order_id,
            $item['id'],
            $item['name'],
            $item['quantity'],
            $item['price']
        );
        $item_stmt->execute();
    }

    $item_stmt->close();
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Order placed successfully']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save order: ' . $e->getMessage()]);
    error_log('Order placement error: ' . $e->getMessage() . ' | Input: ' . json_encode($input));

} finally {
    $conn->close();
}
?>
