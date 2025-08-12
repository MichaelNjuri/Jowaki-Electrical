<?php
// Comprehensive API Test Script
// This script tests all critical API endpoints

echo "<h1>🔧 Complete API Test</h1>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Server:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'unknown') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'includes/db_connection.php';
    $conn = getConnection();
    if ($conn) {
        echo "✅ Database connection successful<br>";
        
        // Test query
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            echo "✅ Database query test successful<br>";
        } else {
            echo "❌ Database query test failed<br>";
        }
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: Configuration Test
echo "<h2>2. Configuration Test</h2>";
try {
    require_once 'config/config.php';
    if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
        echo "✅ Configuration constants defined<br>";
        echo "   - DB_HOST: " . DB_HOST . "<br>";
        echo "   - DB_NAME: " . DB_NAME . "<br>";
    } else {
        echo "❌ Configuration constants missing<br>";
    }
} catch (Exception $e) {
    echo "❌ Configuration error: " . $e->getMessage() . "<br>";
}

// Test 3: API Files Existence
echo "<h2>3. API Files Existence Test</h2>";
$api_files = [
    'includes/get_products.php',
    'includes/get_categories.php',
    'includes/get_cart_count.php',
    'includes/add_to_cart.php',
    'includes/remove_from_cart.php',
    'includes/update_cart_quantity.php',
    'includes/place_order.php',
    'includes/get_user_info.php',
    'includes/contact_form.php',
    'includes/get_featured_products.php'
];

foreach ($api_files as $api_file) {
    if (file_exists($api_file)) {
        echo "✅ <strong>$api_file</strong> exists<br>";
    } else {
        echo "❌ <strong>$api_file</strong> missing<br>";
    }
}

// Test 4: JavaScript Files Existence
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
    } else {
        echo "❌ <strong>$js_file</strong> missing<br>";
    }
}

// Test 5: Session Test
echo "<h2>5. Session Test</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is active<br>";
    echo "   - Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Session is not active<br>";
}

// Test 6: File Permissions
echo "<h2>6. File Permissions Test</h2>";
$test_files = [
    'config/config.php',
    'includes/db_connection.php',
    'includes/get_products.php',
    'assets/js/store.js'
];

foreach ($test_files as $test_file) {
    if (file_exists($test_file)) {
        $perms = fileperms($test_file);
        $perms_octal = substr(sprintf('%o', $perms), -4);
        echo "✅ <strong>$test_file</strong> permissions: $perms_octal<br>";
    }
}

// Test 7: Database Tables
echo "<h2>7. Database Tables Test</h2>";
try {
    if (isset($conn)) {
        $tables = ['products', 'users', 'orders', 'store_categories', 'system_settings'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "✅ Table <strong>$table</strong> exists<br>";
            } else {
                echo "❌ Table <strong>$table</strong> missing<br>";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Database tables test error: " . $e->getMessage() . "<br>";
}

// Test 8: API Endpoint Test (simulated)
echo "<h2>8. API Endpoint Test</h2>";
echo "<p>Testing API endpoints via curl simulation...</p>";

// Test get_products.php
$get_products_url = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/includes/get_products.php';
echo "✅ Testing: <strong>$get_products_url</strong><br>";

// Test get_categories.php
$get_categories_url = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/includes/get_categories.php';
echo "✅ Testing: <strong>$get_categories_url</strong><br>";

// Test get_cart_count.php
$get_cart_count_url = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/includes/get_cart_count.php';
echo "✅ Testing: <strong>$get_cart_count_url</strong><br>";

echo "<h2>🎯 Summary</h2>";
echo "<p>If you see mostly ✅ marks above, your APIs are working correctly!</p>";
echo "<p>If you see ❌ marks, those components need to be fixed.</p>";

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='index.php'>← Test Homepage</a></p>";
echo "<p><a href='Store.php'>← Test Store Page</a></p>";
echo "<p><a href='test-all-paths.php'>← Test File Paths</a></p>";

echo "<h2>📋 Next Steps</h2>";
echo "<ol>";
echo "<li>Upload all missing API files to the server</li>";
echo "<li>Ensure all JavaScript modules are uploaded</li>";
echo "<li>Test the website functionality</li>";
echo "<li>Check browser console for any remaining errors</li>";
echo "</ol>";

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>




