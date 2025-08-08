<?php
// Forgot Password Handler
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include email service
require_once 'email_service.php';

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Function to send error response
function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse(['success' => false, 'error' => $message], $statusCode);
}

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendErrorResponse('Only POST method allowed', 405);
    }

    // Include database connection
    require_once 'db_connection.php';
    
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        sendErrorResponse('Database connection failed', 500);
    }

    // Get and validate input
    $raw_input = file_get_contents('php://input');
    if (empty($raw_input)) {
        sendErrorResponse('No input data received');
    }

    $input = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse('Invalid JSON data');
    }

    // Validate required fields
    if (!isset($input['email']) || empty(trim($input['email']))) {
        sendErrorResponse('Email address is required');
    }

    // Sanitize and validate email
    $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendErrorResponse('Invalid email address');
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, first_name, last_name FROM users WHERE email = ?");
    if (!$stmt) {
        sendErrorResponse('Database query preparation failed', 500);
    }
    
    $stmt->bind_param('s', $email);
    if (!$stmt->execute()) {
        sendErrorResponse('Database query execution failed', 500);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        sendErrorResponse('Email address not found in our system');
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Generate reset token
    $reset_token = bin2hex(random_bytes(32));
    $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Check if password_resets table exists, create if not
    $table_check = $conn->query("SHOW TABLES LIKE 'password_resets'");
    if ($table_check->num_rows === 0) {
        $create_table_sql = "CREATE TABLE password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_email (email),
            INDEX idx_expires (expires_at)
        )";
        
        if (!$conn->query($create_table_sql)) {
            sendErrorResponse('Failed to create password reset table', 500);
        }
    }

    // Delete any existing reset tokens for this user
    $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $delete_stmt->bind_param('s', $email);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Insert new reset token
    $insert_stmt = $conn->prepare("INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)");
    if (!$insert_stmt) {
        sendErrorResponse('Failed to prepare password reset insert query', 500);
    }
    
    $insert_stmt->bind_param('isss', $user['id'], $email, $reset_token, $token_expiry);
    if (!$insert_stmt->execute()) {
        sendErrorResponse('Failed to create password reset token', 500);
    }
    $insert_stmt->close();

    // Send password reset email
    $customer_name = $user['first_name'] . ' ' . $user['last_name'];
    $email_sent = sendPasswordResetEmail($email, $reset_token);

    // Log the password reset request
    error_log("Password reset requested for email: $email, token: $reset_token");

    // Close database connection
    $conn->close();

    // Send success response
    sendJsonResponse([
        'success' => true,
        'message' => 'Password reset link has been sent to your email address.',
        'email_sent' => $email_sent
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("Error in forgot_password.php: " . $e->getMessage());
    
    // Send error response
    sendErrorResponse('Internal server error: ' . $e->getMessage(), 500);
    
    // Close connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
    }
} catch (Error $e) {
    // Handle fatal errors
    error_log("Fatal error in forgot_password.php: " . $e->getMessage());
    
    sendErrorResponse('Server error occurred', 500);
    
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>
