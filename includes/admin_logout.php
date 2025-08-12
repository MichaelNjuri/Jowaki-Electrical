<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

// Start session
session_start();

// Log the admin logout attempt
if (isset($_SESSION['admin_id'])) {
    error_log("Admin logout: ID " . $_SESSION['admin_id'] . " (Username: " . ($_SESSION['admin_username'] ?? 'Unknown') . ")", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
} else {
    error_log("Admin logout attempt with no active admin session", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
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

// Redirect to admin login page with success message
header("Location: ../admin_login.html?message=" . urlencode("You have been successfully logged out"));
exit;
?>





