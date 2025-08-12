<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

header('Content-Type: application/json');

// Start session
session_start();

// Function to send JSON response
function sendResponse($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Invalid request method");
}

// Check if session is valid
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    sendResponse(false, "No active session found");
}

// Check if user ID exists
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, "Invalid session data");
}

// Update session activity time
$_SESSION['last_activity'] = time();

// Regenerate session ID for security
session_regenerate_id(true);

// Log the session extension
$user_email = $_SESSION['user_email'] ?? 'Unknown';
error_log("Session extended for user: $user_email", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

sendResponse(true, "Session extended successfully", [
    'new_session_id' => session_id(),
    'last_activity' => $_SESSION['last_activity']
]);
?>