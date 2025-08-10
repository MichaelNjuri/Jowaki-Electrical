<?php
// Simple Admin System Initialization Script
// This script will create a simplified admin system without roles

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚀 Simple Admin System Initialization</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    .warning { color: orange; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
</style>";

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "jowaki_db";

echo "<div class='section'>";
echo "<h2>📊 Initializing Simple Admin System</h2>";

try {
    // Connect to database
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        echo "<div class='error'>❌ Database connection failed: " . $conn->connect_error . "</div>";
        exit;
    }
    
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Create simple admin_users table (no roles)
    echo "<h3>👥 Creating admin_users table...</h3>";
    $create_admin_users_table = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_admin_users_table)) {
        echo "<div class='success'>✅ admin_users table created successfully</div>";
    } else {
        echo "<div class='error'>❌ Error creating admin_users table: " . $conn->error . "</div>";
    }
    
    // Create admin_activity_log table
    echo "<h3>📝 Creating admin_activity_log table...</h3>";
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
    
    if ($conn->query($create_activity_log_table)) {
        echo "<div class='success'>✅ admin_activity_log table created successfully</div>";
    } else {
        echo "<div class='error'>❌ Error creating admin_activity_log table: " . $conn->error . "</div>";
    }
    
    // Create default admin user
    echo "<h3>👑 Creating default admin...</h3>";
    
    // Check if admin user already exists
    $check_admin = $conn->query("SELECT COUNT(*) as count FROM admin_users");
    $admin_count = $check_admin->fetch_assoc()['count'];
    
    if ($admin_count == 0) {
        // Create default admin
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        $username = 'admin';
        $email = 'admin@jowaki.com';
        $first_name = 'Admin';
        $last_name = 'User';
        $stmt->bind_param("sssss", $username, $email, $default_password, $first_name, $last_name);
        
        if ($stmt->execute()) {
            echo "<div class='success'>✅ Default admin created successfully</div>";
            echo "<div class='info'>📋 Default credentials:</div>";
            echo "<div class='info'>👤 Username: <strong>admin</strong></div>";
            echo "<div class='info'>🔑 Password: <strong>admin123</strong></div>";
        } else {
            echo "<div class='error'>❌ Error creating default admin: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='warning'>⚠️ Admin users already exist, skipping default admin creation</div>";
    }
    
    // Verify tables were created
    echo "<h3>✅ Verification</h3>";
    $tables = ['admin_users', 'admin_activity_log'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<div class='success'>✅ Table '$table' exists</div>";
        } else {
            echo "<div class='error'>❌ Table '$table' does not exist</div>";
        }
    }
    
    $conn->close();
    
    echo "<div class='section'>";
    echo "<h2>🎉 Simple Admin System Ready!</h2>";
    echo "<p>The simplified admin system has been successfully initialized.</p>";
    echo "<p><strong>Features:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Simple admin login (no roles)</li>";
    echo "<li>✅ All admins have full permissions</li>";
    echo "<li>✅ Activity logging</li>";
    echo "<li>✅ Clean and simple interface</li>";
    echo "</ul>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Test the admin login: <a href='admin_login.html'>admin_login.html</a></li>";
    echo "<li>Use credentials: <strong>admin</strong> / <strong>admin123</strong></li>";
    echo "<li>Access the admin dashboard: <a href='AdminDashboard.html'>AdminDashboard.html</a></li>";
    echo "<li>Run the diagnostic: <a href='diagnose_admin_system.php'>diagnose_admin_system.php</a></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Exception: " . $e->getMessage() . "</div>";
}

echo "<div class='info'>📅 Initialization completed at: " . date('Y-m-d H:i:s') . "</div>";
?>
