<?php
// Fixed Admin Login API
session_start();

// Prevent output before headers
if (ob_get_level()) ob_end_clean();

require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = null;
    $stmt = null;
    $update_stmt = null;
    
    try {
        $conn = getConnection();
        if (!$conn) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit;
        }

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
                'redirect' => '../initialize_admin_system.php'
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
            exit;
        }

        $admin = $result->fetch_assoc();

        // Verify password
        if (!password_verify($password, $admin['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
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

        // Log login activity using the current connection
        try {
            $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $admin_id = $admin['id'];
            $action = 'Login';
            $details = 'Admin logged in successfully';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $log_stmt->bind_param("issss", $admin_id, $action, $details, $ip, $user_agent);
            $log_stmt->execute();
            $log_stmt->close();
        } catch (Exception $e) {
            // Ignore logging errors
        }

        // Clean up resources
        if ($update_stmt) {
            $update_stmt->close();
        }
        if ($stmt) {
            $stmt->close();
        }
        if ($conn) {
            $conn->close();
        }

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
        // Clean up resources in case of error
        if ($update_stmt) {
            $update_stmt->close();
        }
        if ($stmt) {
            $stmt->close();
        }
        if ($conn) {
            $conn->close();
        }
        
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