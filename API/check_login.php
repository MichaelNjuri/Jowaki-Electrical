<?php
session_start();
header('Content-Type: application/json');

// Check if session is valid and user is logged in
$logged_in = false;
$user_info = null;

if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Check if session hasn't expired (optional - 24 hours)
    $session_timeout = 24 * 60 * 60; // 24 hours in seconds
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) < $session_timeout) {
        $logged_in = true;
        $_SESSION['last_activity'] = time(); // Update last activity time
        
        $user_info = [
            'user_id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null
        ];
    } else {
        // Session expired, destroy it
        session_destroy();
    }
}

echo json_encode([
    'logged_in' => $logged_in,
    'user' => $user_info,
    'session_id' => session_id()
]);
?>