<?php
// Simple Admin Setup Script
session_start();

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Admin Setup - Jowaki Store</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "h1 { color: #333; text-align: center; }";
echo "h2 { color: #555; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔧 Admin Setup - Jowaki Store</h1>";

// Database connection function
function getDbConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=u383641303_jowaki_db;charset=utf8mb4",
            "u383641303_jowaki",
            "Db_password1",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

try {
    $pdo = getDbConnection();
    if (!$pdo) {
        echo "<div class='error'>❌ Database connection failed. Please check your database credentials.</div>";
        echo "<div class='info'>";
        echo "<h3>Database Configuration:</h3>";
        echo "<p><strong>Host:</strong> localhost</p>";
        echo "<p><strong>Database:</strong> u383641303_jowaki_db</p>";
        echo "<p><strong>Username:</strong> u383641303_jowaki</p>";
        echo "<p><strong>Password:</strong> Db_password1</p>";
        echo "</div>";
        echo "</div></body></html>";
        exit;
    }

    echo "<div class='success'>✅ Database connection successful!</div>";

    // Step 1: Create admin_users table
    echo "<h2>Step 1: Creating Admin Users Table</h2>";
    
    $create_admin_users = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        is_active BOOLEAN DEFAULT TRUE,
        is_super_admin BOOLEAN DEFAULT FALSE,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_admin_users);
    echo "<div class='success'>✅ Admin users table created/verified</div>";

    // Step 2: Create admin_activity_log table
    echo "<h2>Step 2: Creating Activity Log Table</h2>";
    
    $create_activity_log = "CREATE TABLE IF NOT EXISTS admin_activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($create_activity_log);
    echo "<div class='success'>✅ Activity log table created/verified</div>";

    // Step 3: Check if admin user exists
    echo "<h2>Step 3: Checking Admin User</h2>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM admin_users WHERE username = ?");
    $stmt->execute(['admin']);
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Create default admin user
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password_hash, first_name, last_name, is_super_admin) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@jowaki.com', $password_hash, 'Super', 'Admin', true]);
        
        echo "<div class='success'>✅ Default admin user created!</div>";
        echo "<div class='info'>";
        echo "<h3>Default Admin Credentials:</h3>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Email:</strong> admin@jowaki.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "</div>";
    } else {
        echo "<div class='warning'>⚠️ Admin user already exists</div>";
    }

    // Step 4: Test admin login
    echo "<h2>Step 4: Testing Admin System</h2>";
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = TRUE");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify('admin123', $admin['password_hash'])) {
        echo "<div class='success'>✅ Admin login test successful!</div>";
    } else {
        echo "<div class='error'>❌ Admin login test failed</div>";
    }

    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<div class='success'>";
    echo "<h3>✅ Admin System Ready!</h3>";
    echo "<p>Your admin system has been set up successfully.</p>";
    echo "</div>";

    echo "<div class='info'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='login.php' class='btn'>🔐 Go to Admin Login</a></li>";
    echo "<li>Login with the default credentials</li>";
    echo "<li>Change the default password immediately</li>";
    echo "<li>Delete this setup file for security</li>";
    echo "</ol>";
    echo "</div>";

    echo "<div class='warning'>";
    echo "<h3>⚠️ Security Reminder:</h3>";
    echo "<p>Delete this file (setup_admin.php) after successful setup!</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>❌ Setup Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div>";
echo "</body></html>";
?>
