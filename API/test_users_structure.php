<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connection.php';

try {
    // Check users table structure
    $result = $conn->query("DESCRIBE users");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Check if table exists and has data
    $countResult = $conn->query("SELECT COUNT(*) as count FROM users");
    $count = $countResult->fetch_assoc()['count'];
    
    // Get sample data
    $sampleResult = $conn->query("SELECT * FROM users LIMIT 1");
    $sample = null;
    if ($sampleResult && $sampleResult->num_rows > 0) {
        $sample = $sampleResult->fetch_assoc();
    }
    
    echo json_encode([
        'success' => true,
        'columns' => $columns,
        'total_users' => $count,
        'sample_user' => $sample
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 