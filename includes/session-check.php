<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

header('Content-Type: application/json');

// Start session
session_start();

// Function to send JSON response
function sendResponse($valid, $message = '', $data = []) {
    echo json_encode([
        'valid' => $valid,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Function to check session validity
function isValidSession() {
    // Check if session variables exist
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Check if user is marked as logged in
    if ($_SESSION['logged_in'] !== true) {
        return false;
    }
    
    // Check session timeout (30 minutes)
    $session_timeout = 30 * 60; // 30 minutes in seconds
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        return false;
    }
    
    return true;
}

if (isValidSession()) {
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    $time_remaining = 30 * 60 - (time() - $_SESSION['last_activity']);
    
    sendResponse(true, "Session is valid", [
        'user_id' => $_SESSION['user_id'],
        'user_email' => $_SESSION['user_email'] ?? '',
        'time_remaining' => $time_remaining,
        'last_activity' => $_SESSION['last_activity']
    ]);
} else {
    // Clear invalid session
    $_SESSION = array();
    session_destroy();
    
    sendResponse(false, "Session is invalid or expired");
}
?>