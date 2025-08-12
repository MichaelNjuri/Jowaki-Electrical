<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    $userId = $_SESSION['user_id'];
    
    // Get user data with all possible fields
    $stmt = $conn->prepare("SELECT first_name, last_name, email, phone, address, city, postal_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Format the data for form pre-filling
        $userData = [
            'success' => true,
            'data' => [
                'firstName' => $user['first_name'] ?? '',
                'lastName' => $user['last_name'] ?? '',
                'fullName' => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
                'email' => $user['email'] ?? '',
                'phone' => $user['phone'] ?? '',
                'address' => $user['address'] ?? '',
                'city' => $user['city'] ?? '',
                'postalCode' => $user['postal_code'] ?? ''
            ]
        ];
        
        echo json_encode($userData);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 