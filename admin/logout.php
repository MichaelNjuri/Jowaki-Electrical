<?php
/**
 * Admin Logout
 * Handles secure admin logout
 */

session_start();

// Include configuration
require_once dirname(__DIR__) . '/config/config.php';

// Log the admin logout attempt
if (isset($_SESSION['admin_id'])) {
    error_log("Admin logout: ID " . $_SESSION['admin_id'] . " (Username: " . ($_SESSION['admin_username'] ?? 'Unknown') . ")", 3, __DIR__ . DIRECTORY_SEPARATOR . 'admin_activity.log');
} else {
    error_log("Admin logout attempt with no active admin session", 3, __DIR__ . DIRECTORY_SEPARATOR . 'admin_activity.log');
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
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
header("Location: login.php?message=" . urlencode("You have been successfully logged out"));
exit();
?>
