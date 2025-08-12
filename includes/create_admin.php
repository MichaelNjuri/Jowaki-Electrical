<?php
session_start();
if (ob_get_level()) ob_end_clean();

require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = null;
    
    try {
        $conn = getConnection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }

        // Validate required fields
        $required_fields = ['username', 'email', 'password', 'first_name', 'last_name'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }

        $username = trim($data['username']);
        $email = trim($data['email']);
        $password = $data['password'];
        $first_name = trim($data['first_name']);
        $last_name = trim($data['last_name']);
        $role_id = isset($data['role_id']) ? (int)$data['role_id'] : 2; // Default to Admin role (ID: 2)
        $is_active = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Check if username already exists
        $check_username = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
        if (!$check_username) {
            throw new Exception('Failed to prepare username check: ' . $conn->error);
        }
        $check_username->bind_param("s", $username);
        $check_username->execute();
        if ($check_username->get_result()->num_rows > 0) {
            $check_username->close();
            throw new Exception('Username already exists');
        }
        $check_username->close();

        // Check if email already exists
        $check_email = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
        if (!$check_email) {
            throw new Exception('Failed to prepare email check: ' . $conn->error);
        }
        $check_email->bind_param("s", $email);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            $check_email->close();
            throw new Exception('Email already exists');
        }
        $check_email->close();

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Validate role_id exists
        $check_role = $conn->prepare("SELECT id FROM admin_roles WHERE id = ?");
        if (!$check_role) {
            throw new Exception('Failed to prepare role check: ' . $conn->error);
        }
        $check_role->bind_param("i", $role_id);
        $check_role->execute();
        if ($check_role->get_result()->num_rows === 0) {
            $check_role->close();
            throw new Exception('Invalid role selected');
        }
        $check_role->close();

        // Create admin user
        $stmt = $conn->prepare("
            INSERT INTO admin_users (username, email, password_hash, first_name, last_name, role_id, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception('Failed to prepare insert statement: ' . $conn->error);
        }
        
        $is_active_int = $is_active ? 1 : 0;
        $stmt->bind_param("sssssii", $username, $email, $password_hash, $first_name, $last_name, $role_id, $is_active_int);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create admin user: ' . $stmt->error);
        }
        
        $new_admin_id = $conn->insert_id;
        
        // Log activity
        if (function_exists('logAdminActivity')) {
            logAdminActivity('Create Admin', "Created admin user: $username ($first_name $last_name)");
        }
        
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Admin user created successfully',
            'admin_id' => $new_admin_id
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error creating admin: ' . $e->getMessage()
        ]);
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
