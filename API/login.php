<?php
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('display_errors', 1); // Enable for debugging - set to 0 in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

// Start output buffering to prevent accidental output
ob_start();

try {
    session_start();
} catch (Exception $e) {
    error_log("Session start failed: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Session initialization failed']);
    exit;
}

error_log("Session ID: " . session_id(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
error_log("Starting api/login.php", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

header('Content-Type: application/json');

// Catch fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR)) {
        error_log("Fatal error: " . json_encode($error), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        
        // Clean any output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error occurred. Please try again later.', 'debug' => $error['message']]);
    }
});

function sendError($message, $httpCode = 400) {
    error_log("Error sent: $message (HTTP $httpCode)", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    
    // Clean any output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendError("Invalid request method", 405);
}

// Check if db_connection.php exists - FIXED PATH
$db_file = __DIR__ . DIRECTORY_SEPARATOR . 'db_connection.php';
if (!file_exists($db_file)) {
    error_log("db_connection.php not found at: " . $db_file, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    sendError("Database configuration file not found", 500);
}

error_log("Before requiring db_connection.php", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

try {
    require $db_file;
} catch (Exception $e) {
    error_log("Failed to require db_connection.php: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    sendError("Database configuration error", 500);
} catch (Error $e) {
    error_log("Fatal error in db_connection.php: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    sendError("Database configuration error", 500);
}

error_log("After requiring db_connection.php", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

// Parse input data
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // Fallback to FormData
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? '';
    $return_to_checkout = $_POST['return_to_checkout'] ?? '';
    error_log("Using FormData - Email: '$email', Password: " . (empty($password) ? 'empty' : 'non-empty'), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
} else {
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $input['password'] ?? '';
    $redirect = $input['redirect'] ?? '';
    $return_to_checkout = $input['return_to_checkout'] ?? '';
    error_log("Using JSON data - Email: '$email', Password: " . (empty($password) ? 'empty' : 'non-empty'), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
}

if (empty($email) || empty($password)) {
    sendError("Please fill in all fields");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError("Please enter a valid email address");
}

error_log("Checking database connection", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

// Check if $conn variable exists and is valid
if (!isset($conn)) {
    error_log("Connection variable \$conn is not set", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    sendError("Database connection not initialized", 500);
}

if (!$conn) {
    error_log("Connection variable \$conn is false/null", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    sendError("Database connection failed. Please try again later", 500);
}

// Check connection status for MySQLi
if (method_exists($conn, 'ping')) {
    if (!$conn->ping()) {
        error_log("Database connection ping failed", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        sendError("Database connection lost", 500);
    }
}

try {
    error_log("Preparing query for email: $email", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    
    $stmt = $conn->prepare("SELECT id, password, first_name, last_name FROM users WHERE email = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        sendError("Database error: " . $conn->error, 500);
    }
    
    if (!$stmt->bind_param("s", $email)) {
        error_log("Bind param failed: " . $stmt->error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        sendError("Database query error", 500);
    }
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        sendError("Database query execution failed", 500);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Get result failed: " . $stmt->error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        sendError("Database result error", 500);
    }
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        error_log("User found: " . json_encode(['id' => $user['id'], 'email' => $email]), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        
        if ($user['password'] === null) {
            error_log("User password is null", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
            sendError("Invalid email or password");
        }
        
        if (!password_verify($password, $user['password'])) {
            error_log("Password verification failed", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
            sendError("Invalid email or password");
        }
        
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        session_regenerate_id(true);
        error_log("Login successful for user: $email", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        
        // Determine redirect URL
        $redirectUrl = '/jowaki_electrical_srvs/api/Profile.php';
        
        if ($redirect === 'store') {
            $redirectUrl = '/jowaki_electrical_srvs/store.php';
            if ($return_to_checkout === 'true') {
                $redirectUrl .= '?return_to_checkout=true';
            }
        }
        
        // Clear output buffer and send response
        if (ob_get_level()) {
            ob_clean();
        }
        
        echo json_encode([
            'success' => true, 
            'redirect' => $redirectUrl,
            'message' => 'Login successful!'
        ]);
        exit;
        
    } else {
        error_log("User not found or multiple users found. Rows: " . $result->num_rows, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        sendError("Invalid email or password");
    }
    
} catch (mysqli_sql_exception $e) {
    error_log("MySQL error: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    sendError("Database error occurred", 500);
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    sendError("An error occurred. Please try again later", 500);
} finally {
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn && method_exists($conn, 'close')) {
        $conn->close();
    }
}
?>