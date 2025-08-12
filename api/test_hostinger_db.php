<?php
// Suppress error output to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require_once '../config/config.php';
    
    // Test different possible Hostinger database hosts
    $possible_hosts = [
        'localhost',
        '127.0.0.1',
        'mysql.hostinger.com',
        'sql.hostinger.com'
    ];
    
    $results = [];
    
    foreach ($possible_hosts as $host) {
        try {
            $test_conn = new mysqli($host, DB_USER, DB_PASS, DB_NAME);
            
            if ($test_conn->connect_error) {
                $results[$host] = [
                    'success' => false,
                    'error' => $test_conn->connect_error
                ];
            } else {
                $results[$host] = [
                    'success' => true,
                    'server_info' => $test_conn->server_info,
                    'host_info' => $test_conn->host_info
                ];
                $test_conn->close();
            }
        } catch (Exception $e) {
            $results[$host] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Hostinger database connection test',
        'current_config' => [
            'host' => DB_HOST,
            'database' => DB_NAME,
            'user' => DB_USER
        ],
        'test_results' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

