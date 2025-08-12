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

// Check if user is admin (simplified check)
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

    // Check if admin_roles table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'admin_roles'");
    if ($tableExists->num_rows === 0) {
        // Return default roles if table doesn't exist
        echo json_encode([
            'success' => true,
            'roles' => [
                [
                    'id' => 1,
                    'role_name' => 'Super Admin',
                    'role_description' => 'Full system access',
                    'permissions' => [
                        'dashboard' => true,
                        'products' => true,
                        'orders' => true,
                        'customers' => true,
                        'categories' => true,
                        'analytics' => true,
                        'settings' => true,
                        'admin_management' => true,
                        'create_admins' => true,
                        'delete_admins' => true,
                        'view_logs' => true,
                        'backup' => true
                    ]
                ],
                [
                    'id' => 2,
                    'role_name' => 'Admin',
                    'role_description' => 'Standard admin access',
                    'permissions' => [
                        'dashboard' => true,
                        'products' => true,
                        'orders' => true,
                        'customers' => true,
                        'categories' => true,
                        'analytics' => true,
                        'settings' => false,
                        'admin_management' => false,
                        'create_admins' => false,
                        'delete_admins' => false,
                        'view_logs' => true,
                        'backup' => false
                    ]
                ]
            ]
        ]);
        exit;
    }

    // Get all admin roles
    $sql = "SELECT id, role_name, role_description, permissions FROM admin_roles ORDER BY role_name";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Failed to query admin roles: ' . $conn->error);
    }
    
    $roles = [];
    
    while ($row = $result->fetch_assoc()) {
        $roles[] = [
            'id' => $row['id'],
            'role_name' => $row['role_name'],
            'role_description' => $row['role_description'],
            'permissions' => json_decode($row['permissions'], true) ?: []
        ];
    }

    echo json_encode([
        'success' => true,
        'roles' => $roles
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving roles: ' . $e->getMessage()
    ]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>





