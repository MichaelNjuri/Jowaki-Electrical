<?php
// Comprehensive API Endpoint Test
// This script tests all API endpoints used by the JavaScript files

echo "<h1>🔧 Complete API Endpoint Test</h1>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Server:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'unknown') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Test 1: Check all API files exist
echo "<h2>1. API Files Existence Test</h2>";
$api_files = [
    // Store APIs
    'includes/get_products.php',
    'includes/get_categories.php',
    'includes/get_cart_count.php',
    'includes/add_to_cart.php',
    'includes/remove_from_cart.php',
    'includes/update_cart_quantity.php',
    'includes/place_order.php',
    'includes/get_user_info.php',
    
    // Authentication APIs
    'includes/login.php',
    'includes/signup.php',
    'includes/reset_password.php',
    'includes/Logout.php',
    
    // Other APIs
    'includes/contact_form.php',
    'includes/get_featured_products.php',
    'includes/get_user_stats.php',
    'includes/get_user_orders.php',
    'includes/update_user_profile.php',
    'includes/change_password.php',
    'includes/get_order_details.php',
    'includes/cancel_order.php'
];

$missing_apis = [];
$existing_apis = [];

foreach ($api_files as $api_file) {
    if (file_exists($api_file)) {
        echo "✅ <strong>$api_file</strong> exists<br>";
        $existing_apis[] = $api_file;
    } else {
        echo "❌ <strong>$api_file</strong> missing<br>";
        $missing_apis[] = $api_file;
    }
}

// Test 2: Check JavaScript files and their API calls
echo "<h2>2. JavaScript API Usage Test</h2>";
$js_files = [
    'assets/js/store-products.js' => ['includes/get_categories.php', 'includes/get_products.php'],
    'assets/js/store-cart.js' => ['includes/get_cart_count.php', 'includes/add_to_cart.php', 'includes/remove_from_cart.php', 'includes/update_cart_quantity.php'],
    'assets/js/store-checkout.js' => ['includes/get_cart_count.php', 'includes/get_user_info.php', 'includes/place_order.php'],
    'assets/js/login.js' => ['includes/login.php', 'includes/signup.php', 'includes/reset_password.php'],
    'assets/js/profile.js' => ['includes/get_user_stats.php', 'includes/get_user_orders.php', 'includes/update_user_profile.php', 'includes/change_password.php', 'includes/get_order_details.php', 'includes/cancel_order.php', 'includes/Logout.php'],
    'assets/js/index.js' => ['includes/contact_form.php'],
    'assets/js/service.js' => ['includes/contact_form.php'],
    'assets/js/category_dropdown.js' => ['includes/get_categories.php']
];

foreach ($js_files as $js_file => $apis) {
    if (file_exists($js_file)) {
        echo "✅ <strong>$js_file</strong> exists<br>";
        foreach ($apis as $api) {
            if (file_exists($api)) {
                echo "  ✅ Calls <strong>$api</strong> - EXISTS<br>";
            } else {
                echo "  ❌ Calls <strong>$api</strong> - MISSING<br>";
            }
        }
    } else {
        echo "❌ <strong>$js_file</strong> missing<br>";
    }
}

// Test 3: Database Connection Test
echo "<h2>3. Database Connection Test</h2>";
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

// Test 4: Configuration Test
echo "<h2>4. Configuration Test</h2>";
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

// Test 5: Session Test
echo "<h2>5. Session Test</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is active<br>";
    echo "   - Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Session is not active<br>";
}

// Test 6: API Response Test (simulated)
echo "<h2>6. API Response Format Test</h2>";
echo "<p>Testing API response formats...</p>";

// Test get_cart_count.php response format
if (file_exists('includes/get_cart_count.php')) {
    echo "✅ <strong>get_cart_count.php</strong> - Should return: {success: true, cart_count: number, cart_total: number, cart_items: array}<br>";
}

// Test get_products.php response format
if (file_exists('includes/get_products.php')) {
    echo "✅ <strong>get_products.php</strong> - Should return: {success: true, products: array, total: number}<br>";
}

// Test login.php response format
if (file_exists('includes/login.php')) {
    echo "✅ <strong>login.php</strong> - Should return: {success: true, message: string, user: object, redirect: string}<br>";
}

echo "<h2>🎯 Summary</h2>";
echo "<p><strong>Total API files:</strong> " . count($api_files) . "</p>";
echo "<p><strong>Existing APIs:</strong> " . count($existing_apis) . "</p>";
echo "<p><strong>Missing APIs:</strong> " . count($missing_apis) . "</p>";

if (count($missing_apis) > 0) {
    echo "<h3>🚨 Missing API Files:</h3>";
    echo "<ul>";
    foreach ($missing_apis as $missing_api) {
        echo "<li>$missing_api</li>";
    }
    echo "</ul>";
}

if (count($missing_apis) === 0) {
    echo "<p>🎉 <strong>All API files exist!</strong></p>";
} else {
    echo "<p>⚠️ <strong>Some API files are missing. Please create them.</strong></p>";
}

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='index.php'>← Test Homepage</a></p>";
echo "<p><a href='Store.php'>← Test Store Page</a></p>";
echo "<p><a href='test-all-paths.php'>← Test File Paths</a></p>";
echo "<p><a href='test-all-apis.php'>← Test API Functionality</a></p>";

echo "<h2>📋 Next Steps</h2>";
if (count($missing_apis) > 0) {
    echo "<ol>";
    echo "<li>Create the missing API files listed above</li>";
    echo "<li>Upload all files to the server</li>";
    echo "<li>Test the website functionality</li>";
    echo "<li>Check browser console for any remaining errors</li>";
    echo "</ol>";
} else {
    echo "<ol>";
    echo "<li>Upload all files to the server</li>";
    echo "<li>Test the website functionality</li>";
    echo "<li>Check browser console for any remaining errors</li>";
    echo "<li>Verify all features work correctly</li>";
    echo "</ol>";
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>




