<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
        exit();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    
    // Prepare update query with all possible fields
    $updateFields = [];
    $types = '';
    $values = [];
    
    // Map input fields to database columns
    $fieldMappings = [
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email',
        'phone' => 'phone',
        'address' => 'address',
        'city' => 'city',
        'postal_code' => 'postal_code'
    ];
    
    foreach ($fieldMappings as $inputField => $dbField) {
        if (isset($input[$inputField]) && !empty($input[$inputField])) {
            $updateFields[] = "$dbField = ?";
            $types .= 's';
            $values[] = $input[$inputField];
        }
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No valid fields to update']);
        exit();
    }
    
    // Add user_id to values array
    $values[] = $user_id;
    $types .= 'i';
    
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception('Database execute failed: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'updated_fields' => array_keys(array_filter($input, function($value) {
                return !empty($value);
            }))
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Profile data unchanged',
            'updated_fields' => []
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log('Update profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?> 