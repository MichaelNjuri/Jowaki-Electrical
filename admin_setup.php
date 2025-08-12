<?php
/**
 * Admin Setup and Database Fix Script for Hostinger
 * This script will:
 * 1. Test database connection
 * 2. Fix database structure
 * 3. Create admin user
 * 4. Initialize admin system
 * DELETE THIS FILE AFTER SUCCESSFUL SETUP FOR SECURITY.
 */

require_once 'config/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Setup - Jowaki Electrical</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #cce5ff; color: #004085; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        .section { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>";

echo "<h1>🔧 Admin Setup and Database Fix</h1>";
echo "<p>Setting up admin system for Jowaki Electrical Services</p>";

// Step 1: Test Database Connection
echo "<div class='section'>";
echo "<h2>📊 Step 1: Database Connection Test</h2>";

try {
    $pdo = getDbConnection();
    if ($pdo) {
        echo "<div class='success'>✅ Database connection successful!</div>";
        echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
        echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
        echo "<p><strong>User:</strong> " . DB_USER . "</p>";
    } else {
        echo "<div class='error'>❌ Database connection failed!</div>";
        echo "<p>Please check your database credentials in config/config.php</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
echo "</div>";

// Step 2: Check Database Tables
echo "<div class='section'>";
echo "<h2>🗄️ Step 2: Database Tables Check</h2>";

$required_tables = [
    'admin_users', 'admin_roles', 'admin_permissions', 'admin_activity',
    'users', 'products', 'categories', 'orders', 'order_items',
    'contact_messages', 'system_settings', 'store_categories'
];

$existing_tables = [];
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch()) {
    $existing_tables[] = $row[0];
}

echo "<h3>Required Tables:</h3>";
foreach ($required_tables as $table) {
    if (in_array($table, $existing_tables)) {
        echo "<div class='success'>✅ $table</div>";
    } else {
        echo "<div class='error'>❌ $table (missing)</div>";
    }
}
echo "</div>";

// Step 3: Fix Database Structure
echo "<div class='section'>";
echo "<h2>🔧 Step 3: Database Structure Fix</h2>";

// Fix users table
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    $required_columns = ['address', 'city', 'postal_code'];
    foreach ($required_columns as $column) {
        if (!in_array($column, $columns)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN $column VARCHAR(255)");
            echo "<div class='success'>✅ Added column: users.$column</div>";
        } else {
            echo "<div class='info'>ℹ️ Column exists: users.$column</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='warning'>⚠️ Could not check/fix users table: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Fix orders table
try {
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    
    $required_columns = ['delivery_method', 'delivery_address', 'payment_method', 'order_date', 'status'];
    foreach ($required_columns as $column) {
        if (!in_array($column, $columns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN $column VARCHAR(255)");
            echo "<div class='success'>✅ Added column: orders.$column</div>";
        } else {
            echo "<div class='info'>ℹ️ Column exists: orders.$column</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='warning'>⚠️ Could not check/fix orders table: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Step 4: Create Admin User
echo "<div class='section'>";
echo "<h2>👤 Step 4: Admin User Setup</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $admin_username = $_POST['admin_username'] ?? 'admin';
    $admin_email = $_POST['admin_email'] ?? 'admin@jowaki.com';
    $admin_password = $_POST['admin_password'] ?? 'admin123';
    
    try {
        // Check if admin_users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
        if ($stmt->rowCount() === 0) {
            // Create admin_users table
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                first_name VARCHAR(50),
                last_name VARCHAR(50),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            echo "<div class='success'>✅ Created admin_users table</div>";
        }
        
        // Check if admin already exists
        $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
        $stmt->execute([$admin_username]);
        
        if ($stmt->rowCount() === 0) {
            // Create admin user
            $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$admin_username, $admin_email, $password_hash, 'Admin', 'User']);
            
            echo "<div class='success'>✅ Admin user created successfully!</div>";
            echo "<div class='info'>";
            echo "<h4>Admin Login Details:</h4>";
            echo "<p><strong>Username:</strong> $admin_username</p>";
            echo "<p><strong>Email:</strong> $admin_email</p>";
            echo "<p><strong>Password:</strong> $admin_password</p>";
            echo "</div>";
        } else {
            echo "<div class='warning'>⚠️ Admin user already exists</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error creating admin: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    echo "<form method='POST'>";
    echo "<h3>Create Admin User:</h3>";
    echo "<p><label>Username: <input type='text' name='admin_username' value='admin' required></label></p>";
    echo "<p><label>Email: <input type='email' name='admin_email' value='admin@jowaki.com' required></label></p>";
    echo "<p><label>Password: <input type='password' name='admin_password' value='admin123' required></label></p>";
    echo "<button type='submit' name='create_admin' class='btn btn-success'>Create Admin User</button>";
    echo "</form>";
}
echo "</div>";

// Step 5: System Settings
echo "<div class='section'>";
echo "<h2>⚙️ Step 5: System Settings</h2>";

try {
    // Check if system_settings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_settings'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<div class='success'>✅ Created system_settings table</div>";
    }
    
    // Insert default settings
    $default_settings = [
        'site_name' => 'Jowaki Electrical Services Ltd',
        'site_url' => 'https://jowakielectrical.com',
        'site_email' => 'info@jowaki.com',
        'whatsapp_number' => '+254721442248',
        'currency' => 'KES',
        'tax_rate' => '16',
        'shipping_cost' => '500'
    ];
    
    foreach ($default_settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }
    echo "<div class='success'>✅ System settings initialized</div>";
    
} catch (Exception $e) {
    echo "<div class='warning'>⚠️ Could not initialize system settings: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// Step 6: Final Status
echo "<div class='section'>";
echo "<h2>🎯 Step 6: Setup Complete</h2>";

echo "<div class='success'>";
echo "<h3>✅ Admin System Ready!</h3>";
echo "<p>Your admin system has been set up successfully.</p>";
echo "</div>";

echo "<div class='info'>";
echo "<h4>Next Steps:</h4>";
echo "<ol>";
echo "<li>Delete this setup file for security</li>";
echo "<li>Access your admin panel</li>";
echo "<li>Change the default admin password</li>";
echo "<li>Configure your website settings</li>";
echo "</ol>";
echo "</div>";

echo "<div class='warning'>";
echo "<h4>⚠️ Security Reminder:</h4>";
echo "<p>Delete this file (admin_setup.php) after successful setup!</p>";
echo "</div>";
echo "</div>";

echo "</body></html>";
?>
