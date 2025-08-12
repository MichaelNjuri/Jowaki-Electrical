<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');

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

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            exit;
        }

        // Check if admin_users table exists
        $check_table = $conn->query("SHOW TABLES LIKE 'admin_users'");
        if ($check_table->num_rows === 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Admin system not initialized',
                'redirect' => 'initialize_admin_system.php'
            ]);
            exit;
        }

        // Get admin user
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = TRUE");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            $stmt->close();
            $conn->close();
            exit;
        }

        $admin = $result->fetch_assoc();
        $stmt->close();

        // Verify password
        if (!password_verify($password, $admin['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            $conn->close();
            exit;
        }

        // Set session variables
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['email'] = $admin['email'];
        $_SESSION['first_name'] = $admin['first_name'];
        $_SESSION['last_name'] = $admin['last_name'];
        $_SESSION['is_admin'] = true;
        $_SESSION['permissions'] = [
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
        ];

        // Update last login
        $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $update_stmt->bind_param("i", $admin['id']);
        $update_stmt->execute();
        $update_stmt->close();

        // Log login activity
        logAdminActivity('Login', 'Admin logged in successfully');

        $conn->close();

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'admin' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name'],
                'full_name' => $admin['first_name'] . ' ' . $admin['last_name'],
                'role_id' => $admin['role_id'],
                'permissions' => $_SESSION['permissions']
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Login error: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
