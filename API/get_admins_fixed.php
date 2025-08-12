<?php
session_start();
if (ob_get_level()) ob_end_clean();

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

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

$conn = null;

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if admin_users table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($tableExists->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'admins' => [],
            'total' => 0,
            'message' => 'Admin users table does not exist'
        ]);
        exit;
    }

    // Get all admin users
    $sql = "
        SELECT 
            id,
            username,
            email,
            first_name,
            last_name,
            is_active,
            last_login,
            created_at
        FROM admin_users
        ORDER BY created_at DESC
    ";
    
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception('Failed to query admin users: ' . $conn->error);
    }
    
    $admins = [];
    
    while ($row = $result->fetch_assoc()) {
        $admins[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'full_name' => $row['first_name'] . ' ' . $row['last_name'],
            'is_active' => (bool)$row['is_active'],
            'last_login' => $row['last_login'],
            'created_at' => $row['created_at'],
            'role_name' => 'Admin', // Default role name
            'is_super_admin' => false // Default super admin status
        ];
    }

    echo json_encode([
        'success' => true,
        'admins' => $admins,
        'total' => count($admins)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving admins: ' . $e->getMessage()
    ]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>

