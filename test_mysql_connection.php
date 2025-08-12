<?php
// Simple MySQL Connection Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 MySQL Connection Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
</style>";

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "jowaki_db";

echo "<div class='section'>";
echo "<h2>📊 Testing MySQL Connection</h2>";

// Test 1: Check if MySQL server is running
echo "<h3>1. MySQL Server Status</h3>";
try {
    $test_conn = new mysqli($host, $user, $pass);
    
    if ($test_conn->connect_error) {
        echo "<div class='error'>❌ MySQL server is not running or not accessible</div>";
        echo "<div class='error'>Error: " . $test_conn->connect_error . "</div>";
        echo "<div class='info'>💡 Please start MySQL in XAMPP Control Panel</div>";
        exit;
    } else {
        echo "<div class='success'>✅ MySQL server is running</div>";
        echo "<div class='info'>Server version: " . $test_conn->server_info . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Cannot connect to MySQL server</div>";
    echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>💡 Please start MySQL in XAMPP Control Panel</div>";
    exit;
}

// Test 2: Check if database exists
echo "<h3>2. Database Check</h3>";
$result = $test_conn->query("SHOW DATABASES LIKE '$dbname'");
if ($result->num_rows > 0) {
    echo "<div class='success'>✅ Database '$dbname' exists</div>";
} else {
    echo "<div class='warning'>⚠️ Database '$dbname' does not exist</div>";
    echo "<div class='info'>💡 Creating database '$dbname'...</div>";
    
    if ($test_conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
        echo "<div class='success'>✅ Database '$dbname' created successfully</div>";
    } else {
        echo "<div class='error'>❌ Failed to create database: " . $test_conn->error . "</div>";
        $test_conn->close();
        exit;
    }
}

// Test 3: Connect to specific database
echo "<h3>3. Database Connection Test</h3>";
$test_conn->select_db($dbname);
if ($test_conn->error) {
    echo "<div class='error'>❌ Cannot access database '$dbname'</div>";
    echo "<div class='error'>Error: " . $test_conn->error . "</div>";
} else {
    echo "<div class='success'>✅ Successfully connected to database '$dbname'</div>";
}

// Test 4: Check admin tables
echo "<h3>4. Admin Tables Check</h3>";
$tables = ['admin_users', 'admin_activity_log'];
foreach ($tables as $table) {
    $result = $test_conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<div class='success'>✅ Table '$table' exists</div>";
    } else {
        echo "<div class='warning'>⚠️ Table '$table' does not exist</div>";
    }
}

$test_conn->close();

echo "<div class='section'>";
echo "<h2>🎯 Next Steps</h2>";
echo "<ol>";
echo "<li>If MySQL server is not running, start it in XAMPP Control Panel</li>";
echo "<li>If database doesn't exist, it will be created automatically</li>";
echo "<li>If admin tables don't exist, run: <a href='initialize_admin_system.php'>initialize_admin_system.php</a></li>";
echo "<li>Once all tests pass, try: <a href='admin_login.html'>admin_login.html</a></li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>📅 Test completed at: " . date('Y-m-d H:i:s') . "</div>";
?>


