<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

// Check if user is super admin
if (!isSuperAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Super admin privileges required.']);
    exit;
}

$conn = getConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Create admin_roles table
    $create_roles_table = "CREATE TABLE IF NOT EXISTS admin_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(50) UNIQUE NOT NULL,
        role_description TEXT,
        permissions JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($create_roles_table);

    // Create admin_users table
    $create_admin_users_table = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        role_id INT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        is_super_admin BOOLEAN DEFAULT FALSE,
        last_login TIMESTAMP NULL,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES admin_roles(id) ON DELETE RESTRICT,
        FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
    )";
    $conn->query($create_admin_users_table);

    // Create admin_activity_log table (if not exists)
    $create_activity_log_table = "CREATE TABLE IF NOT EXISTS admin_activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
    )";
    $conn->query($create_activity_log_table);

    // Insert default roles
    $default_roles = [
        [
            'role_name' => 'Super Admin',
            'role_description' => 'Full system access including admin management',
            'permissions' => json_encode([
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
            ])
        ],
        [
            'role_name' => 'Admin',
            'role_description' => 'Full access to all features except admin management',
            'permissions' => json_encode([
                'dashboard' => true,
                'products' => true,
                'orders' => true,
                'customers' => true,
                'categories' => true,
                'analytics' => true,
                'settings' => true,
                'admin_management' => false,
                'create_admins' => false,
                'delete_admins' => false,
                'view_logs' => true,
                'backup' => true
            ])
        ],
        [
            'role_name' => 'Manager',
            'role_description' => 'Limited access to core business functions',
            'permissions' => json_encode([
                'dashboard' => true,
                'products' => true,
                'orders' => true,
                'customers' => true,
                'categories' => false,
                'analytics' => true,
                'settings' => false,
                'admin_management' => false,
                'create_admins' => false,
                'delete_admins' => false,
                'view_logs' => false,
                'backup' => false
            ])
        ]
    ];

    foreach ($default_roles as $role) {
        $stmt = $conn->prepare("INSERT IGNORE INTO admin_roles (role_name, role_description, permissions) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $role['role_name'], $role['role_description'], $role['permissions']);
        $stmt->execute();
        $stmt->close();
    }

    // Update existing admin user to super admin if exists
    $update_super_admin = "UPDATE admin_users SET is_super_admin = TRUE, role_id = (SELECT id FROM admin_roles WHERE role_name = 'Super Admin') WHERE id = 1";
    $conn->query($update_super_admin);

    // Insert super admin if no admin exists
    $check_admin = $conn->query("SELECT COUNT(*) as count FROM admin_users");
    $admin_count = $check_admin->fetch_assoc()['count'];

    if ($admin_count == 0) {
        // Create default super admin
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password_hash, first_name, last_name, role_id, is_super_admin) VALUES (?, ?, ?, ?, ?, (SELECT id FROM admin_roles WHERE role_name = 'Super Admin'), TRUE)");
        $username = 'admin';
        $email = 'admin@jowaki.com';
        $first_name = 'Super';
        $last_name = 'Admin';
        $stmt->bind_param("sssss", $username, $email, $default_password, $first_name, $last_name);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Admin management tables created successfully',
        'default_password' => $admin_count == 0 ? 'admin123' : null
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error creating admin tables: ' . $e->getMessage()
    ]);
}
?>


