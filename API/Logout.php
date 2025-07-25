<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

// Start session
session_start();

// Log the logout attempt
if (isset($_SESSION['user_email'])) {
    error_log("User logout: " . $_SESSION['user_email'], 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
} else {
    error_log("Logout attempt with no active session", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with success message
header("Location: /jowaki_electrical_srvs/login.html?message=" . urlencode("You have been successfully logged out"));
exit;
?>