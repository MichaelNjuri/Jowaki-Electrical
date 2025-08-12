<?php
/**
 * API Test Endpoint
 * Check database connection and basic system functionality
 */

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Test database connection
    require_once 'db_connection.php';
    
    $db_status = 'disconnected';
    $db_error = '';
    $tables = [];
    
    if ($conn && !$conn->connect_error) {
        $db_status = 'connected';
        
        // Test a simple query
        try {
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                while ($row = $result->fetch_array()) {
                    $tables[] = $row[0];
                }
                $result->free();
            }
        } catch (Exception $e) {
            $db_error = $e->getMessage();
        }
    } else {
        $db_error = $conn ? $conn->connect_error : 'Connection failed';
    }
    
    // Test session
    $session_status = session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive';
    $session_id = session_id();
    
    // System info
    $system_info = [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'current_time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get()
    ];
    
    // User info (if logged in)
    $user_info = null;
    if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        $user_info = [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? 'Unknown',
            'name' => $_SESSION['user_name'] ?? 'Unknown',
            'login_time' => $_SESSION['login_time'] ?? 'Unknown',
            'last_activity' => $_SESSION['last_activity'] ?? 'Unknown'
        ];
    }
    
    // Response
    $response = [
        'success' => true,
        'message' => 'API test successful',
        'timestamp' => time(),
        'database' => [
            'status' => $db_status,
            'error' => $db_error,
            'table_count' => count($tables),
            'tables' => $tables
        ],
        'session' => [
            'status' => $session_status,
            'id' => $session_id
        ],
        'system' => $system_info,
        'user' => $user_info,
        'config' => [
            'db_host' => defined('DB_HOST') ? DB_HOST : 'Not defined',
            'db_name' => defined('DB_NAME') ? DB_NAME : 'Not defined',
            'site_url' => defined('SITE_URL') ? SITE_URL : 'Not defined'
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'API test failed: ' . $e->getMessage(),
        'timestamp' => time()
    ], JSON_PRETTY_PRINT);
}
?>
