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
        $admin_id = $_GET['id'] ?? null;
        
        if (!$admin_id) {
            echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
            exit;
        }

        // Get admin details
        $stmt = $conn->prepare("
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
            WHERE id = ?
        ");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Admin user not found']);
            exit;
        }

        $admin = $result->fetch_assoc();
        $stmt->close();

        // Get recent activity for this admin
        $activity_stmt = $conn->prepare("
            SELECT 
                action,
                details,
                ip_address,
                created_at
            FROM admin_activity_log 
            WHERE admin_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $activity_stmt->bind_param("i", $admin_id);
        $activity_stmt->execute();
        $activity_result = $activity_stmt->get_result();
        
        $activities = [];
        while ($activity = $activity_result->fetch_assoc()) {
            $activities[] = $activity;
        }
        $activity_stmt->close();

        $conn->close();

        echo json_encode([
            'success' => true,
            'admin' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'email' => $admin['email'],
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name'],
                'full_name' => $admin['first_name'] . ' ' . $admin['last_name'],
                'role_id' => $admin['role_id'],
                'role_name' => $admin['role_id'] === 1 ? 'Super Admin' : 'Admin',
                'is_active' => (bool)$admin['is_active'],
                'last_login' => $admin['last_login'],
                'created_at' => $admin['created_at']
            ],
            'activities' => $activities
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving admin details: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
