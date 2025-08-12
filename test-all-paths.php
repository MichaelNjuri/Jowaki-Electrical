<?php
// Comprehensive File Path and Functionality Test
// This script tests all critical file paths and basic functionality

echo "<h1>🔧 Complete File Path and Functionality Test</h1>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Server:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'unknown') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Test 1: Check if config.php exists and is readable
echo "<h2>1. Configuration File Test</h2>";
$config_path = 'config/config.php';
if (file_exists($config_path)) {
    echo "✅ <strong>config/config.php</strong> exists<br>";
    if (is_readable($config_path)) {
        echo "✅ <strong>config/config.php</strong> is readable<br>";
        
        // Test if we can include it
        try {
            require_once $config_path;
            echo "✅ <strong>config/config.php</strong> can be included successfully<br>";
            
            // Test if constants are defined
            if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
                echo "✅ Database constants are defined<br>";
                echo "   - DB_HOST: " . DB_HOST . "<br>";
                echo "   - DB_NAME: " . DB_NAME . "<br>";
                echo "   - DB_USER: " . DB_USER . "<br>";
            } else {
                echo "❌ Database constants are NOT defined<br>";
            }
        } catch (Exception $e) {
            echo "❌ Error including config.php: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ <strong>config/config.php</strong> is NOT readable<br>";
    }
} else {
    echo "❌ <strong>config/config.php</strong> does NOT exist<br>";
}

// Test 2: Check includes/load_settings.php
echo "<h2>2. Load Settings Test</h2>";
$load_settings_path = 'includes/load_settings.php';
if (file_exists($load_settings_path)) {
    echo "✅ <strong>includes/load_settings.php</strong> exists<br>";
    if (is_readable($load_settings_path)) {
        echo "✅ <strong>includes/load_settings.php</strong> is readable<br>";
        
        // Test if we can include it
        try {
            require_once $load_settings_path;
            echo "✅ <strong>includes/load_settings.php</strong> can be included successfully<br>";
            
            // Test if functions are available
            if (function_exists('getValidConnection')) {
                echo "✅ getValidConnection() function is available<br>";
            } else {
                echo "❌ getValidConnection() function is NOT available<br>";
            }
            
            if (function_exists('getStoreSettings')) {
                echo "✅ getStoreSettings() function is available<br>";
            } else {
                echo "❌ getStoreSettings() function is NOT available<br>";
            }
        } catch (Exception $e) {
            echo "❌ Error including load_settings.php: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ <strong>includes/load_settings.php</strong> is NOT readable<br>";
    }
} else {
    echo "❌ <strong>includes/load_settings.php</strong> does NOT exist<br>";
}

// Test 3: Check database connection
echo "<h2>3. Database Connection Test</h2>";
try {
    if (function_exists('getValidConnection')) {
        $conn = getValidConnection();
        if ($conn) {
            echo "✅ Database connection successful<br>";
            
            // Test a simple query
            $result = $conn->query("SELECT 1 as test");
            if ($result) {
                echo "✅ Database query test successful<br>";
            } else {
                echo "❌ Database query test failed<br>";
            }
        } else {
            echo "❌ Database connection failed<br>";
        }
    } else {
        echo "❌ getValidConnection() function not available<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
}

// Test 4: Check JavaScript files
echo "<h2>4. JavaScript Files Test</h2>";
$js_files = [
    'assets/js/store.js',
    'assets/js/store-cart.js',
    'assets/js/store-products.js',
    'assets/js/store-checkout.js',
    'assets/js/store-ui.js'
];

foreach ($js_files as $js_file) {
    if (file_exists($js_file)) {
        echo "✅ <strong>$js_file</strong> exists<br>";
        if (is_readable($js_file)) {
            echo "✅ <strong>$js_file</strong> is readable<br>";
        } else {
            echo "❌ <strong>$js_file</strong> is NOT readable<br>";
        }
    } else {
        echo "❌ <strong>$js_file</strong> does NOT exist<br>";
    }
}

// Test 5: Check CSS files
echo "<h2>5. CSS Files Test</h2>";
$css_files = [
    'assets/css/index.css',
    'assets/css/store.css',
    'assets/css/login.css',
    'assets/css/checkout.css'
];

foreach ($css_files as $css_file) {
    if (file_exists($css_file)) {
        echo "✅ <strong>$css_file</strong> exists<br>";
    } else {
        echo "❌ <strong>$css_file</strong> does NOT exist<br>";
    }
}

// Test 6: Check main PHP files
echo "<h2>6. Main PHP Files Test</h2>";
$php_files = [
    'index.php',
    'Store.php',
    'cart.php',
    'checkout.php',
    'login_form.php',
    'profile.php'
];

foreach ($php_files as $php_file) {
    if (file_exists($php_file)) {
        echo "✅ <strong>$php_file</strong> exists<br>";
    } else {
        echo "❌ <strong>$php_file</strong> does NOT exist<br>";
    }
}

// Test 7: Check includes directory
echo "<h2>7. Includes Directory Test</h2>";
$includes_files = [
    'includes/header.php',
    'includes/footer.php',
    'includes/db_connection.php',
    'includes/auth-helper.php'
];

foreach ($includes_files as $include_file) {
    if (file_exists($include_file)) {
        echo "✅ <strong>$include_file</strong> exists<br>";
    } else {
        echo "❌ <strong>$include_file</strong> does NOT exist<br>";
    }
}

// Test 8: Check permissions
echo "<h2>8. File Permissions Test</h2>";
$test_files = [
    'config/config.php',
    'includes/load_settings.php',
    'index.php',
    'assets/js/store.js'
];

foreach ($test_files as $test_file) {
    if (file_exists($test_file)) {
        $perms = fileperms($test_file);
        $perms_octal = substr(sprintf('%o', $perms), -4);
        echo "✅ <strong>$test_file</strong> permissions: $perms_octal<br>";
    }
}

// Test 9: Check if .htaccess exists
echo "<h2>9. .htaccess Test</h2>";
if (file_exists('.htaccess')) {
    echo "✅ <strong>.htaccess</strong> exists<br>";
    if (is_readable('.htaccess')) {
        echo "✅ <strong>.htaccess</strong> is readable<br>";
    } else {
        echo "❌ <strong>.htaccess</strong> is NOT readable<br>";
    }
} else {
    echo "❌ <strong>.htaccess</strong> does NOT exist<br>";
}

echo "<h2>🎯 Summary</h2>";
echo "<p>If you see mostly ✅ marks above, your file paths are correct!</p>";
echo "<p>If you see ❌ marks, those files need to be uploaded or fixed.</p>";

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='index.php'>← Test Homepage</a></p>";
echo "<p><a href='Store.php'>← Test Store Page</a></p>";
echo "<p><a href='test-js-modules.html'>← Test JavaScript Modules</a></p>";
?>




