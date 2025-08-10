<?php
session_start();
require_once 'API/db_connection.php';
require_once 'API/check_auth.php';

echo "Session ID: " . session_id() . "\n";
echo "Session data: " . print_r($_SESSION, true) . "\n";

// Check if admin session exists
if (isset($_SESSION['admin_id'])) {
    echo "Admin ID: " . $_SESSION['admin_id'] . "\n";
    echo "Admin username: " . ($_SESSION['admin_username'] ?? 'Not set') . "\n";
    
    // Test isAdmin function
    try {
        $isAdmin = isAdmin();
        echo "isAdmin() result: " . ($isAdmin ? 'true' : 'false') . "\n";
    } catch (Exception $e) {
        echo "isAdmin() error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No admin session found\n";
}

// Test database connection
try {
    $conn = getConnection();
    if ($conn) {
        echo "Database connection: OK\n";
        $conn->close();
    } else {
        echo "Database connection: FAILED\n";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
