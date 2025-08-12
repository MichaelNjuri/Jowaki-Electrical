<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
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
        // Get all admin users
        $sql = "
            SELECT 
                id,
                username,
                email,
                first_name,
                last_name,
                role_id,
                is_active,
                last_login,
                created_at
            FROM admin_users
            ORDER BY created_at DESC
        ";
        
        $result = $conn->query($sql);
        $admins = [];
        
        while ($row = $result->fetch_assoc()) {
            $admins[] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'email' => $row['email'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'full_name' => $row['first_name'] . ' ' . $row['last_name'],
                'role_id' => $row['role_id'],
                'is_active' => (bool)$row['is_active'],
                'last_login' => $row['last_login'],
                'created_at' => $row['created_at']
            ];
        }

        // Log activity
        logAdminActivity('View Admins', 'Viewed admin management page');

        $conn->close();

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
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
