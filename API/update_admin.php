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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }

        $admin_id = $data['admin_id'] ?? null;
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $first_name = $data['first_name'] ?? '';
        $last_name = $data['last_name'] ?? '';
        $role_id = $data['role_id'] ?? 2;
        $new_password = $data['new_password'] ?? '';

        // Validate required fields
        if (!$admin_id) {
            echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
            exit;
        }

        if (empty($username) || empty($email) || empty($first_name) || empty($last_name)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }

        // Check if username already exists (excluding current admin)
        $check_username = $conn->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
        $check_username->bind_param("si", $username, $admin_id);
        $check_username->execute();
        if ($check_username->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }
        $check_username->close();

        // Check if email already exists (excluding current admin)
        $check_email = $conn->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $admin_id);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        $check_email->close();

        // Check if trying to modify a Super Admin (role_id = 1) to regular admin
        $current_admin = $conn->prepare("SELECT role_id FROM admin_users WHERE id = ?");
        $current_admin->bind_param("i", $admin_id);
        $current_admin->execute();
        $current_result = $current_admin->get_result();
        
        if ($current_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Admin user not found']);
            exit;
        }

        $current_data = $current_result->fetch_assoc();
        $current_admin->close();

        // Prevent downgrading Super Admin to regular admin
        if ($current_data['role_id'] === 1 && $role_id !== 1) {
            echo json_encode(['success' => false, 'message' => 'Cannot downgrade Super Admin to regular admin']);
            exit;
        }

        // Build update query
        if (!empty($new_password)) {
            // Update with password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, first_name = ?, last_name = ?, role_id = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssisi", $username, $email, $first_name, $last_name, $role_id, $hashed_password, $admin_id);
        } else {
            // Update without password
            $stmt = $conn->prepare("UPDATE admin_users SET username = ?, email = ?, first_name = ?, last_name = ?, role_id = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $username, $email, $first_name, $last_name, $role_id, $admin_id);
        }
        
        if ($stmt->execute()) {
            // Log activity
            $action = 'Update Admin';
            $details = "Updated admin ID: $admin_id - Username: $username, Email: $email, Role: " . ($role_id === 1 ? 'Super Admin' : 'Admin');
            if (!empty($new_password)) {
                $details .= " (Password changed)";
            }
            logAdminActivity($action, $details);
            
            $stmt->close();
            $conn->close();

            echo json_encode([
                'success' => true,
                'message' => 'Admin updated successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update admin']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating admin: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
