<?php
// Suppress error output to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Test 1: Check if config can be loaded
    require_once '../config/config.php';
    
    // Test 2: Try direct mysqli connection
    $test_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($test_conn->connect_error) {
        throw new Exception('Direct mysqli connection failed: ' . $test_conn->connect_error);
    }
    
    // Test 3: Try the getConnection function
    require_once '../includes/db_connection_fixed.php';
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception('getConnection() function returned false');
    }
    
    // Test 4: Test a simple query
    $result = $conn->query("SELECT 1 as test");
    if (!$result) {
        throw new Exception('Simple query failed: ' . $conn->error);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'connection_type' => 'mysqli',
        'server_info' => $conn->server_info,
        'host_info' => $conn->host_info
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'db_host' => defined('DB_HOST') ? DB_HOST : 'not defined',
        'db_name' => defined('DB_NAME') ? DB_NAME : 'not defined',
        'db_user' => defined('DB_USER') ? DB_USER : 'not defined'
    ]);
} finally {
    if (isset($test_conn)) {
        $test_conn->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
