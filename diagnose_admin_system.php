<?php
// Admin System Diagnostic Script
// This script will check all components of the admin system and report issues

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Admin System Diagnostic Report</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    .check { margin: 10px 0; }
</style>";

// Function to check if file exists
function checkFile($file, $description) {
    echo "<div class='check'>";
    if (file_exists($file)) {
        echo "✅ <strong>$description</strong>: File exists at <code>$file</code>";
    } else {
        echo "❌ <strong>$description</strong>: File NOT found at <code>$file</code>";
    }
    echo "</div>";
}

// Function to check database connection
function checkDatabase() {
    echo "<div class='section'>";
    echo "<h2>📊 Database Connection Test</h2>";
    
    try {
        // Check if db_connection.php exists
        if (!file_exists('API/db_connection.php')) {
            echo "<div class='error'>❌ db_connection.php not found in API directory</div>";
            return false;
        }
        
        require_once 'API/db_connection.php';
        $conn = getConnection();
        
        if ($conn) {
            echo "<div class='success'>✅ Database connection successful</div>";
            
            // Check if admin tables exist
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
            return true;
        } else {
            echo "<div class='error'>❌ Database connection failed</div>";
            return false;
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Database error: " . $e->getMessage() . "</div>";
        return false;
    }
    echo "</div>";
}

// Function to check PHP files
function checkPHPFiles() {
    echo "<div class='section'>";
    echo "<h2>📁 PHP Files Check</h2>";
    
    $files = [
        'API/db_connection.php' => 'Database Connection',
        'API/check_auth.php' => 'Authentication Helper',
        'API/create_admin_tables.php' => 'Admin Tables Creator',
        'API/admin_login.php' => 'Admin Login Handler',
        'API/get_admins.php' => 'Get Admins API',
        'API/get_admin_roles.php' => 'Get Roles API',
        'API/create_admin.php' => 'Create Admin API',
        'API/get_admin_activity.php' => 'Get Activity API',
        'admin_login.html' => 'Admin Login Page',
        'AdminDashboard.html' => 'Admin Dashboard',
        'js/modules/adminManagement.js' => 'Admin Management JS'
    ];
    
    foreach ($files as $file => $description) {
        checkFile($file, $description);
    }
    echo "</div>";
}

// Function to test API endpoints
function testAPIEndpoints() {
    echo "<div class='section'>";
    echo "<h2>🔗 API Endpoints Test</h2>";
    
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    
    $endpoints = [
        'API/check_auth.php' => 'Authentication Check',
        'API/create_admin_tables.php' => 'Create Admin Tables',
        'API/admin_login.php' => 'Admin Login'
    ];
    
    foreach ($endpoints as $endpoint => $description) {
        $url = $baseUrl . '/' . $endpoint;
        echo "<div class='check'>";
        echo "<strong>$description</strong>: <code>$url</code><br>";
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            echo "<span class='success'>✅ Endpoint accessible</span>";
            if (strpos($response, 'error') !== false || strpos($response, 'Error') !== false) {
                echo "<br><span class='warning'>⚠️ Response contains errors</span>";
            }
        } else {
            echo "<span class='error'>❌ Endpoint not accessible</span>";
        }
        echo "</div>";
    }
    echo "</div>";
}

// Function to check session handling
function checkSessionHandling() {
    echo "<div class='section'>";
    echo "<h2>🔐 Session Handling Check</h2>";
    
    // Check if session is already started
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<div class='warning'>⚠️ Session already active - this might cause conflicts</div>";
    } else {
        echo "<div class='success'>✅ Session not started - good for testing</div>";
    }
    
    // Check session configuration
    echo "<div class='info'>📋 Session save path: " . session_save_path() . "</div>";
    echo "<div class='info'>📋 Session name: " . session_name() . "</div>";
    echo "</div>";
}

// Function to provide solutions
function provideSolutions() {
    echo "<div class='section'>";
    echo "<h2>🛠️ Common Solutions</h2>";
    
    echo "<h3>If database tables don't exist:</h3>";
    echo "<ol>";
    echo "<li>Run the initialization script: <code>API/create_admin_tables.php</code></li>";
    echo "<li>Check database permissions</li>";
    echo "<li>Verify database connection settings in <code>API/db_connection.php</code></li>";
    echo "</ol>";
    
    echo "<h3>If API endpoints fail:</h3>";
    echo "<ol>";
    echo "<li>Check file permissions</li>";
    echo "<li>Verify PHP is running</li>";
    echo "<li>Check for syntax errors in PHP files</li>";
    echo "<li>Ensure all required files exist</li>";
    echo "</ol>";
    
    echo "<h3>If login doesn't work:</h3>";
    echo "<ol>";
    echo "<li>Initialize the admin system first</li>";
    echo "<li>Use default credentials: admin / admin123</li>";
    echo "<li>Check session configuration</li>";
    echo "<li>Verify database connection</li>";
    echo "</ol>";
    
    echo "</div>";
}

// Run all checks
echo "<div class='info'>🔍 Starting diagnostic checks...</div>";

checkPHPFiles();
checkDatabase();
testAPIEndpoints();
checkSessionHandling();
provideSolutions();

echo "<div class='section'>";
echo "<h2>🎯 Next Steps</h2>";
echo "<ol>";
echo "<li>If any files are missing, they need to be created</li>";
echo "<li>If database tables don't exist, run the initialization script</li>";
echo "<li>If API endpoints fail, check the specific error messages</li>";
echo "<li>Once all checks pass, try the test page: <a href='test_admin_system.html'>test_admin_system.html</a></li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>📅 Diagnostic completed at: " . date('Y-m-d H:i:s') . "</div>";
?>
