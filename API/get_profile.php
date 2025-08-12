<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // First, check what columns exist in the users table
    $checkColumns = $conn->query("DESCRIBE users");
    $existingColumns = [];
    while ($row = $checkColumns->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
    }

    // Build query based on existing columns
    $selectColumns = ['first_name', 'last_name', 'email', 'created_at'];
    $availableColumns = [];
    
    foreach ($selectColumns as $column) {
        if (in_array($column, $existingColumns)) {
            $availableColumns[] = $column;
        }
    }

    // Add optional columns if they exist
    $optionalColumns = ['phone', 'address', 'city', 'postal_code'];
    foreach ($optionalColumns as $column) {
        if (in_array($column, $existingColumns)) {
            $availableColumns[] = $column;
        }
    }

    $columnsString = implode(', ', $availableColumns);
    
    // Get user information
    $stmt = $conn->prepare("SELECT $columnsString FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Database execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Format the response with safe defaults
        $response = [
            'success' => true,
            'fullName' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
            'address' => $user['address'] ?? '',
            'city' => $user['city'] ?? '',
            'postal_code' => $user['postal_code'] ?? '',
            'memberSince' => $user['created_at'] ?? null
        ];
        
        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log('Get profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch profile information: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>