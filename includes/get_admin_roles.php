<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');

// Check if user has admin management permission
if (!hasPermission('admin_management')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin management permission required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = getConnection();
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    try {
        // Get all admin roles
        $sql = "SELECT id, role_name, role_description, permissions FROM admin_roles ORDER BY role_name";
        $result = $conn->query($sql);
        $roles = [];
        
        while ($row = $result->fetch_assoc()) {
            $roles[] = [
                'id' => $row['id'],
                'role_name' => $row['role_name'],
                'role_description' => $row['role_description'],
                'permissions' => json_decode($row['permissions'], true)
            ];
        }

        $conn->close();

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
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>






