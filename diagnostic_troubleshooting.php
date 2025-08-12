<?php
// Comprehensive System Diagnostic Tool
// This file will help identify exactly what's failing in your system

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Jowaki Store - System Diagnostic</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo ".success { background: #d4edda; border-color: #c3e6cb; color: #155724; }";
echo ".error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }";
echo ".warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }";
echo ".info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }";
echo "h1 { color: #333; text-align: center; }";
echo "h2 { color: #555; border-bottom: 2px solid #007bff; padding-bottom: 5px; }";
echo "h3 { color: #666; }";
echo ".code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }";
echo ".step { margin: 10px 0; padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔍 Jowaki Store - System Diagnostic Report</h1>";
echo "<p><strong>Generated:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. PHP Environment Test
echo "<div class='test-section'>";
echo "<h2>1. PHP Environment</h2>";

$php_version = phpversion();
echo "<h3>PHP Version: $php_version</h3>";
if (version_compare($php_version, '7.4.0', '>=')) {
    echo "<div class='success'>✅ PHP version is compatible (7.4+ required)</div>";
} else {
    echo "<div class='error'>❌ PHP version too old. Need 7.4+</div>";
}

$extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
echo "<h3>Required Extensions:</h3>";
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='success'>✅ $ext extension loaded</div>";
    } else {
        echo "<div class='error'>❌ $ext extension missing</div>";
    }
}
echo "</div>";

// 2. File System Test
echo "<div class='test-section'>";
echo "<h2>2. File System & Permissions</h2>";

$critical_files = [
    'config/config.php',
    'api/db_connection.php',
    'includes/db_connection.php',
    'api/get_categories.php',
    'api/get_products.php'
];

foreach ($critical_files as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "<div class='success'>✅ $file exists and readable</div>";
        } else {
            echo "<div class='error'>❌ $file exists but not readable</div>";
        }
    } else {
        echo "<div class='error'>❌ $file missing</div>";
    }
}

// Check uploads directory
if (is_dir('uploads')) {
    if (is_writable('uploads')) {
        echo "<div class='success'>✅ uploads/ directory writable</div>";
    } else {
        echo "<div class='error'>❌ uploads/ directory not writable</div>";
    }
} else {
    echo "<div class='error'>❌ uploads/ directory missing</div>";
}
echo "</div>";

// 3. Configuration Test
echo "<div class='test-section'>";
echo "<h2>3. Configuration Test</h2>";

try {
    if (file_exists('config/config.php')) {
        require_once 'config/config.php';
        echo "<div class='success'>✅ config/config.php loaded successfully</div>";
        
        // Test if constants are defined
        $required_constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
        foreach ($required_constants as $constant) {
            if (defined($constant)) {
                $value = constant($constant);
                $masked_value = $constant === 'DB_PASS' ? str_repeat('*', strlen($value)) : $value;
                echo "<div class='success'>✅ $constant defined: $masked_value</div>";
            } else {
                echo "<div class='error'>❌ $constant not defined</div>";
            }
        }
    } else {
        echo "<div class='error'>❌ config/config.php not found</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error loading config: " . $e->getMessage() . "</div>";
}
echo "</div>";

// 4. Database Connection Test
echo "<div class='test-section'>";
echo "<h2>4. Database Connection Test</h2>";

try {
    if (function_exists('getDbConnection')) {
        $pdo = getDbConnection();
        echo "<div class='success'>✅ Database connection successful!</div>";
        
        // Test basic queries
        $tables_to_test = [
            'store_categories' => 'SELECT COUNT(*) as count FROM store_categories',
            'products' => 'SELECT COUNT(*) as count FROM products',
            'users' => 'SELECT COUNT(*) as count FROM users',
            'orders' => 'SELECT COUNT(*) as count FROM orders'
        ];
        
        foreach ($tables_to_test as $table => $query) {
            try {
                $stmt = $pdo->query($query);
                $result = $stmt->fetch();
                echo "<div class='success'>✅ $table table accessible: " . $result['count'] . " records</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ $table table error: " . $e->getMessage() . "</div>";
            }
        }
        
    } else {
        echo "<div class='error'>❌ getDbConnection function not found</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    
    // Provide specific troubleshooting steps
    echo "<div class='step'>";
    echo "<h3>🔧 Database Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Check your Hostinger database credentials in config/config.php</li>";
    echo "<li>Verify the database exists in Hostinger hPanel</li>";
    echo "<li>Ensure the database user has proper permissions</li>";
    echo "<li>Check if the database server is accessible</li>";
    echo "</ol>";
    echo "</div>";
}
echo "</div>";

// 5. API Endpoints Test
echo "<div class='test-section'>";
echo "<h2>5. API Endpoints Test</h2>";

$api_endpoints = [
    'api/get_categories.php' => 'Categories API',
    'api/get_products.php' => 'Products API',
    'api/get_featured_products.php' => 'Featured Products API'
];

foreach ($api_endpoints as $endpoint => $name) {
    if (file_exists($endpoint)) {
        echo "<div class='success'>✅ $name file exists</div>";
        
        // Test if the file can be included without errors
        try {
            ob_start();
            include $endpoint;
            $output = ob_get_clean();
            
            // Check if it returns JSON
            if (strpos($output, '{') !== false || strpos($output, '[') !== false) {
                echo "<div class='success'>✅ $name returns data</div>";
            } else {
                echo "<div class='warning'>⚠️ $name may not be returning proper JSON</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ $name error: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='error'>❌ $name file missing</div>";
    }
}
echo "</div>";

// 6. Session Test
echo "<div class='test-section'>";
echo "<h2>6. Session Test</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='success'>✅ Sessions working</div>";
    
    // Test session variables
    $_SESSION['test_var'] = 'test_value';
    if (isset($_SESSION['test_var']) && $_SESSION['test_var'] === 'test_value') {
        echo "<div class='success'>✅ Session variables working</div>";
    } else {
        echo "<div class='error'>❌ Session variables not working</div>";
    }
} else {
    echo "<div class='error'>❌ Sessions not working</div>";
}
echo "</div>";

// 7. Error Logging Test
echo "<div class='test-section'>";
echo "<h2>7. Error Logging Test</h2>";

$error_log = ini_get('error_log');
if ($error_log) {
    echo "<div class='success'>✅ Error log configured: $error_log</div>";
} else {
    echo "<div class='warning'>⚠️ Error log not configured</div>";
}

$display_errors = ini_get('display_errors');
echo "<div class='info'>ℹ️ Display errors: " . ($display_errors ? 'ON' : 'OFF') . "</div>";

$log_errors = ini_get('log_errors');
echo "<div class='info'>ℹ️ Log errors: " . ($log_errors ? 'ON' : 'OFF') . "</div>";
echo "</div>";

// 8. Common Issues & Solutions
echo "<div class='test-section'>";
echo "<h2>8. Common Issues & Solutions</h2>";

echo "<div class='step'>";
echo "<h3>🔧 If Database Connection Fails:</h3>";
echo "<ol>";
echo "<li>Check config/config.php has correct Hostinger credentials</li>";
echo "<li>Username should start with 'u383641303_' prefix</li>";
echo "<li>Password should be the actual MySQL password, not database name</li>";
echo "<li>Verify database exists in Hostinger hPanel</li>";
echo "</ol>";
echo "</div>";

echo "<div class='step'>";
echo "<h3>🔧 If APIs Return Empty Data:</h3>";
echo "<ol>";
echo "<li>Check if tables exist in database</li>";
echo "<li>Verify tables have data</li>";
echo "<li>Check API file permissions</li>";
echo "<li>Review error logs for specific errors</li>";
echo "</ol>";
echo "</div>";

echo "<div class='step'>";
echo "<h3>🔧 If Files Are Missing:</h3>";
echo "<ol>";
echo "<li>Upload missing files via FTP or file manager</li>";
echo "<li>Check file permissions (should be 644 for .php files)</li>";
echo "<li>Ensure proper file paths in includes</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

// 9. Quick Fix Commands
echo "<div class='test-section'>";
echo "<h2>9. Quick Fix Commands</h2>";

echo "<div class='code'>";
echo "# Set proper file permissions:<br>";
echo "chmod 644 *.php<br>";
echo "chmod 755 uploads/<br><br>";
echo "# Check PHP error log:<br>";
echo "tail -f /path/to/error.log<br><br>";
echo "# Test database connection manually:<br>";
echo "php -r \"require 'config/config.php'; \$pdo = getDbConnection(); echo 'Connected!';\"<br>";
echo "</div>";
echo "</div>";

echo "<div class='test-section info'>";
echo "<h2>📞 Need Help?</h2>";
echo "<p>If you're still having issues after reviewing this diagnostic:</p>";
echo "<ul>";
echo "<li>Check your Hostinger error logs in hPanel</li>";
echo "<li>Verify all files are uploaded correctly</li>";
echo "<li>Ensure database credentials are correct</li>";
echo "<li>Test with the database connection script first</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
