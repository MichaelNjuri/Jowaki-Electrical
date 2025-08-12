<?php
// Test order insertion
header('Content-Type: text/html');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit();
}

echo "<h1>Test Order Insertion</h1>";

// Check orders table structure
$columns_query = "SHOW COLUMNS FROM orders";
$columns_result = $conn->query($columns_query);
$existing_columns = [];
while ($row = $columns_result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
    echo "<p>Column: " . $row['Field'] . " - Type: " . $row['Type'] . "</p>";
}

echo "<h2>Attempting to insert test order...</h2>";

// Test data
$user_id = 1; // Assuming user ID 1 exists
$subtotal = 100.00;
$tax = 16.00;
$delivery_fee = 0.00;
$total = 116.00;
$customer_info = json_encode([
    'firstName' => 'Test',
    'lastName' => 'User',
    'email' => 'test@example.com'
]);
$cart = json_encode([
    [
        'id' => 1,
        'name' => 'Test Product',
        'price' => 100.00,
        'quantity' => 1
    ]
]);

// Build dynamic INSERT query
$insert_fields = ['user_id', 'subtotal', 'tax', 'delivery_fee', 'total'];
$insert_values = [$user_id, $subtotal, $tax, $delivery_fee, $total];
$bind_types = 'idddd';

// Add optional columns if they exist
if (in_array('customer_info', $existing_columns)) {
    $insert_fields[] = 'customer_info';
    $insert_values[] = $customer_info;
    $bind_types .= 's';
    echo "<p style='color: green;'>✅ Adding customer_info column</p>";
}
if (in_array('cart', $existing_columns)) {
    $insert_fields[] = 'cart';
    $insert_values[] = $cart;
    $bind_types .= 's';
    echo "<p style='color: green;'>✅ Adding cart column</p>";
}
if (in_array('delivery_method', $existing_columns)) {
    $insert_fields[] = 'delivery_method';
    $insert_values[] = 'Standard Delivery';
    $bind_types .= 's';
    echo "<p style='color: green;'>✅ Adding delivery_method column</p>";
}
if (in_array('delivery_address', $existing_columns)) {
    $insert_fields[] = 'delivery_address';
    $insert_values[] = '123 Test St, Test City, 12345';
    $bind_types .= 's';
    echo "<p style='color: green;'>✅ Adding delivery_address column</p>";
}
if (in_array('payment_method', $existing_columns)) {
    $insert_fields[] = 'payment_method';
    $insert_values[] = 'Cash on Delivery';
    $bind_types .= 's';
    echo "<p style='color: green;'>✅ Adding payment_method column</p>";
}
if (in_array('order_date', $existing_columns)) {
    $insert_fields[] = 'order_date';
    $insert_values[] = date('Y-m-d H:i:s');
    $bind_types .= 's';
    echo "<p style='color: green;'>✅ Adding order_date column</p>";
}
if (in_array('status', $existing_columns)) {
    $insert_fields[] = 'status';
    $insert_values[] = 'pending';
    $bind_types .= 's';
    echo "<p style='color: green;'>✅ Adding status column</p>";
}

$order_query = "INSERT INTO orders (" . implode(', ', $insert_fields) . ") VALUES (" . str_repeat('?,', count($insert_values) - 1) . "?)";

echo "<h3>Query:</h3>";
echo "<pre>" . htmlspecialchars($order_query) . "</pre>";

echo "<h3>Fields:</h3>";
echo "<pre>" . implode(', ', $insert_fields) . "</pre>";

echo "<h3>Values:</h3>";
echo "<pre>" . json_encode($insert_values, JSON_PRETTY_PRINT) . "</pre>";

echo "<h3>Bind Types:</h3>";
echo "<pre>" . $bind_types . "</pre>";

// Try to execute the query
$stmt = $conn->prepare($order_query);
if (!$stmt) {
    echo "<p style='color: red;'>❌ Failed to prepare query: " . $conn->error . "</p>";
} else {
    $stmt->bind_param($bind_types, ...$insert_values);
    if (!$stmt->execute()) {
        echo "<p style='color: red;'>❌ Failed to execute query: " . $stmt->error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Order inserted successfully! Order ID: " . $conn->insert_id . "</p>";
    }
    $stmt->close();
}

$conn->close();
?> 