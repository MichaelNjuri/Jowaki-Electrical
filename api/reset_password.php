<?php
// Password Reset Handler
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
    if (!isset($input['token']) || empty(trim($input['token']))) {
        sendErrorResponse('Reset token is required');
    }

    if (!isset($input['password']) || empty(trim($input['password']))) {
        sendErrorResponse('New password is required');
    }

    if (!isset($input['confirm_password']) || empty(trim($input['confirm_password']))) {
        sendErrorResponse('Password confirmation is required');
    }

    // Sanitize input
    $token = trim($input['token']);
    $password = trim($input['password']);
    $confirm_password = trim($input['confirm_password']);

    // Validate password
    if (strlen($password) < 8) {
        sendErrorResponse('Password must be at least 8 characters long');
    }

    if ($password !== $confirm_password) {
        sendErrorResponse('Passwords do not match');
    }

    // Check if password_resets table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'password_resets'");
    if ($table_check->num_rows === 0) {
        sendErrorResponse('Password reset table does not exist', 500);
    }

    // Validate reset token
    $stmt = $conn->prepare("SELECT pr.*, u.id as user_id, u.email FROM password_resets pr 
                           JOIN users u ON pr.user_id = u.id 
                           WHERE pr.token = ? AND pr.expires_at > NOW()");
    if (!$stmt) {
        sendErrorResponse('Database query preparation failed', 500);
    }
    
    $stmt->bind_param('s', $token);
    if (!$stmt->execute()) {
        sendErrorResponse('Database query execution failed', 500);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        sendErrorResponse('Invalid or expired reset token');
    }

    $reset_data = $result->fetch_assoc();
    $stmt->close();

    // Hash the new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Update user password
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if (!$update_stmt) {
        sendErrorResponse('Failed to prepare password update query', 500);
    }
    
    $update_stmt->bind_param('si', $hashed_password, $reset_data['user_id']);
    if (!$update_stmt->execute()) {
        sendErrorResponse('Failed to update password', 500);
    }
    $update_stmt->close();

    // Delete the used reset token
    $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
    $delete_stmt->bind_param('s', $token);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Log the password reset
    error_log("Password reset completed for user ID: " . $reset_data['user_id'] . ", email: " . $reset_data['email']);

    // Close database connection
    $conn->close();

    // Send success response
    sendJsonResponse([
        'success' => true,
        'message' => 'Password has been reset successfully. You can now log in with your new password.'
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("Error in reset_password.php: " . $e->getMessage());
    
    // Send error response
    sendErrorResponse('Internal server error: ' . $e->getMessage(), 500);
    
    // Close connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
    }
} catch (Error $e) {
    // Handle fatal errors
    error_log("Fatal error in reset_password.php: " . $e->getMessage());
    
    sendErrorResponse('Server error occurred', 500);
    
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>