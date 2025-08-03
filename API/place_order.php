<?php
// Disable error display to prevent HTML output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Set JSON header immediately
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set up error handler to catch any unexpected errors
function handleError($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: $errstr in $errfile on line $errline");
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
    exit();
}
set_error_handler('handleError');

// Set up shutdown function to catch fatal errors
function handleShutdown() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Internal server error']);
    }
}
register_shutdown_function('handleShutdown');

// Log the incoming request
error_log("Place order request received: " . date('Y-m-d H:i:s'));
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content type: " . $_SERVER['CONTENT_TYPE'] ?? 'not set');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit();
}

try {
    $raw_input = file_get_contents('php://input');
    error_log("Raw input received: " . $raw_input);
    
    $input = json_decode($raw_input, true);
    error_log("Decoded input: " . json_encode($input));
    
    if (!$input || !isset($input['customer_info']) || !isset($input['cart']) || !is_array($input['cart'])) {
        error_log("Invalid order data received");
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid order data']);
        exit();
    }

    $customer_info = $input['customer_info'];
    $cart = $input['cart'];
    $subtotal = floatval($input['subtotal']);
    $tax = floatval($input['tax']);
    $delivery_fee = floatval($input['delivery_fee']);
    $total = floatval($input['total']);
    $delivery_method = $input['delivery_method'];
    $delivery_address = $input['delivery_address'];
    $payment_method = $input['payment_method'];
    $order_date = $input['order_date'];

    // Check if user exists or create new account
    $email = $conn->real_escape_string($customer_info['email']);
    $user_query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user_id = 0;

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user_id = $user['id'];
    } else {
        // Generate random password
        $password = bin2hex(random_bytes(8));
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $first_name = $conn->real_escape_string($customer_info['firstName']);
        $last_name = $conn->real_escape_string($customer_info['lastName']);
        $phone = $conn->real_escape_string($customer_info['phone']);
        $address = $conn->real_escape_string($customer_info['address']);
        $city = $conn->real_escape_string($customer_info['city']);
        $postal_code = $conn->real_escape_string($customer_info['postalCode']);

        // Check which columns exist in the users table
        $columns_query = "SHOW COLUMNS FROM users";
        $columns_result = $conn->query($columns_query);
        $existing_columns = [];
        while ($row = $columns_result->fetch_assoc()) {
            $existing_columns[] = $row['Field'];
        }
        
        // Build dynamic INSERT query based on existing columns
        $insert_fields = ['first_name', 'last_name', 'email', 'password'];
        $insert_values = [$first_name, $last_name, $email, $hashed_password];
        $bind_types = 'ssss';
        
        // Add optional columns if they exist
        if (in_array('phone', $existing_columns)) {
            $insert_fields[] = 'phone';
            $insert_values[] = $phone;
            $bind_types .= 's';
        }
        if (in_array('address', $existing_columns)) {
            $insert_fields[] = 'address';
            $insert_values[] = $address;
            $bind_types .= 's';
        }
        if (in_array('city', $existing_columns)) {
            $insert_fields[] = 'city';
            $insert_values[] = $city;
            $bind_types .= 's';
        }
        if (in_array('postal_code', $existing_columns)) {
            $insert_fields[] = 'postal_code';
            $insert_values[] = $postal_code;
            $bind_types .= 's';
        }
        
        $insert_user_query = "INSERT INTO users (" . implode(', ', $insert_fields) . ") VALUES (" . str_repeat('?,', count($insert_values) - 1) . "?)";
        $stmt = $conn->prepare($insert_user_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare user insert query: ' . $conn->error);
        }
        $stmt->bind_param($bind_types, ...$insert_values);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create user account: ' . $stmt->error);
        }
        $user_id = $conn->insert_id;

        // Send email with login credentials
        $to = $email;
        $subject = 'Your Jowaki Store Account';
        $message = "Thank you for your purchase!\n\nAn account has been created for you.\nEmail: $email\nPassword: $password\n\nYou can log in at /jowaki_electrical_srvs/login_form.php to view your order history.";
        $headers = 'From: no-reply@jowaki.com';
        mail($to, $subject, $message, $headers);
    }

    // Check which columns exist in the orders table
    $columns_query = "SHOW COLUMNS FROM orders";
    $columns_result = $conn->query($columns_query);
    $existing_columns = [];
    while ($row = $columns_result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Build dynamic INSERT query based on existing columns
    $insert_fields = ['user_id', 'subtotal', 'tax', 'delivery_fee', 'total'];
    $insert_values = [$user_id, $subtotal, $tax, $delivery_fee, $total];
    $bind_types = 'idddd';
    
    // Add optional columns if they exist
    if (in_array('customer_info', $existing_columns)) {
        $insert_fields[] = 'customer_info';
        $insert_values[] = json_encode($customer_info);
        $bind_types .= 's';
    }
    if (in_array('cart', $existing_columns)) {
        $insert_fields[] = 'cart';
        $insert_values[] = json_encode($cart);
        $bind_types .= 's';
    }
    if (in_array('delivery_method', $existing_columns)) {
        $insert_fields[] = 'delivery_method';
        $insert_values[] = $delivery_method;
        $bind_types .= 's';
    }
    if (in_array('delivery_address', $existing_columns)) {
        $insert_fields[] = 'delivery_address';
        $insert_values[] = $delivery_address;
        $bind_types .= 's';
    }
    if (in_array('payment_method', $existing_columns)) {
        $insert_fields[] = 'payment_method';
        $insert_values[] = $payment_method;
        $bind_types .= 's';
    }
    if (in_array('order_date', $existing_columns)) {
        $insert_fields[] = 'order_date';
        $insert_values[] = $order_date;
        $bind_types .= 's';
    }
    if (in_array('status', $existing_columns)) {
        $insert_fields[] = 'status';
        $insert_values[] = 'pending';
        $bind_types .= 's';
    }
    
    $order_query = "INSERT INTO orders (" . implode(', ', $insert_fields) . ") VALUES (" . str_repeat('?,', count($insert_values) - 1) . "?)";
    error_log("Order query: " . $order_query);
    error_log("Insert fields: " . implode(', ', $insert_fields));
    error_log("Bind types: " . $bind_types);
    error_log("Insert values: " . json_encode($insert_values));
    
    $stmt = $conn->prepare($order_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare order query: ' . $conn->error);
    }
    $stmt->bind_param($bind_types, ...$insert_values);
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }
    $order_id = $conn->insert_id;
    error_log("Order created with ID: " . $order_id);

    // Check which columns exist in the order_items table
    $columns_query = "SHOW COLUMNS FROM order_items";
    $columns_result = $conn->query($columns_query);
    $existing_columns = [];
    while ($row = $columns_result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Build dynamic INSERT query based on existing columns
    $insert_fields = ['order_id', 'product_id', 'quantity'];
    $insert_values = [$order_id, 0, 0]; // Placeholder values
    $bind_types = 'iii';
    
    // Add price column if it exists
    if (in_array('price', $existing_columns)) {
        $insert_fields[] = 'price';
        $bind_types .= 'd';
    }
    
    $order_item_query = "INSERT INTO order_items (" . implode(', ', $insert_fields) . ") VALUES (" . str_repeat('?,', count($insert_fields) - 1) . "?)";
    $stmt = $conn->prepare($order_item_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare order item query: ' . $conn->error);
    }
    
    foreach ($cart as $item) {
        $product_id = intval($item['id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        
        // Update values array
        $insert_values = [$order_id, $product_id, $quantity];
        if (in_array('price', $existing_columns)) {
            $insert_values[] = $price;
        }
        
        $stmt->bind_param($bind_types, ...$insert_values);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert order item: ' . $stmt->error);
        }
        error_log("Order item inserted: product_id=$product_id, quantity=$quantity, price=$price");
    }

    // Clear session cart
    $_SESSION['cart'] = [];
    $stmt->close();
    $conn->close();

    error_log("Order placement successful. Order ID: " . $order_id);
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    error_log("Order placement failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    if (isset($conn)) {
        $conn->close();
    }
}
?>