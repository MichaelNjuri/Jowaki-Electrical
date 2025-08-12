<?php
session_start();
if (ob_get_level()) ob_end_clean(); // Clear any buffered output

require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = null;
    
    try {
        $conn = getConnection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        $admin_id = $_SESSION['user_id'];
        
        // Get admin profile with role information
        $stmt = $conn->prepare("
            SELECT 
                au.id,
                au.username,
                au.email,
                au.first_name,
                au.last_name,
                au.is_active,
                au.last_login,
                au.created_at,
                ar.role_name,
                ar.role_description
            FROM admin_users au
            LEFT JOIN admin_roles ar ON au.role_id = ar.id
            WHERE au.id = ?
        ");
        
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $admin_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute query: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Admin profile not found');
        }
        
        $admin = $result->fetch_assoc();
        
        // Format the response
        $profile = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'first_name' => $admin['first_name'],
            'last_name' => $admin['last_name'],
            'full_name' => $admin['first_name'] . ' ' . $admin['last_name'],
            'is_active' => (bool)$admin['is_active'],
            'last_login' => $admin['last_login'],
            'created_at' => $admin['created_at'],
            'role_name' => $admin['role_name'],
            'role_description' => $admin['role_description'],
            'permissions' => $_SESSION['permissions'] ?? []
        ];
        
        echo json_encode([
            'success' => true,
            'profile' => $profile
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching profile: ' . $e->getMessage()
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





