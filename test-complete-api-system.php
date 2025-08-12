<?php
// Complete API System Test
// This script tests all API endpoints for both frontend and admin systems

echo "<h1>🔧 Complete API System Test</h1>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Server:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'unknown') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Test 1: Frontend API Files
echo "<h2>1. Frontend API Files Test</h2>";
$frontend_apis = [
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

$missing_frontend_apis = [];
$existing_frontend_apis = [];

foreach ($frontend_apis as $api_file) {
    if (file_exists($api_file)) {
        echo "✅ <strong>$api_file</strong> exists<br>";
        $existing_frontend_apis[] = $api_file;
    } else {
        echo "❌ <strong>$api_file</strong> missing<br>";
        $missing_frontend_apis[] = $api_file;
    }
}

// Test 2: Admin API Files
echo "<h2>2. Admin API Files Test</h2>";
$admin_apis = [
    // API Directory (redirects to includes)
    'API/get_admins_fixed.php',
    'API/create_admin.php',
    'API/get_admin_activity_fixed.php',
    'API/get_admin_details.php',
    'API/get_admin_profile.php',
    'API/update_admin_profile.php',
    
    // Includes Directory (original files)
    'includes/admin_login.php',
    'includes/admin_login_fixed.php',
    'includes/admin_logout.php',
    'includes/toggle_admin_status.php',
    'includes/get_admin_profile.php',
    'includes/get_admin_details.php',
    'includes/get_admins_fixed.php',
    'includes/get_admin_activity_fixed.php',
    'includes/update_admin_profile.php',
    'includes/create_admin.php',
    'includes/check_auth.php'
];

$missing_admin_apis = [];
$existing_admin_apis = [];

foreach ($admin_apis as $api_file) {
    if (file_exists($api_file)) {
        echo "✅ <strong>$api_file</strong> exists<br>";
        $existing_admin_apis[] = $api_file;
    } else {
        echo "❌ <strong>$api_file</strong> missing<br>";
        $missing_admin_apis[] = $api_file;
    }
}

// Test 3: JavaScript Files and Their API Calls
echo "<h2>3. JavaScript API Usage Test</h2>";
$js_files = [
    // Frontend JavaScript
    'assets/js/store-products.js' => ['includes/get_categories.php', 'includes/get_products.php'],
    'assets/js/store-cart.js' => ['includes/get_cart_count.php', 'includes/add_to_cart.php', 'includes/remove_from_cart.php', 'includes/update_cart_quantity.php'],
    'assets/js/store-checkout.js' => ['includes/get_cart_count.php', 'includes/get_user_info.php', 'includes/place_order.php'],
    'assets/js/login.js' => ['includes/login.php', 'includes/signup.php', 'includes/reset_password.php'],
    'assets/js/profile.js' => ['includes/get_user_stats.php', 'includes/get_user_orders.php', 'includes/update_user_profile.php', 'includes/change_password.php', 'includes/get_order_details.php', 'includes/cancel_order.php', 'includes/Logout.php'],
    'assets/js/index.js' => ['includes/contact_form.php'],
    'assets/js/service.js' => ['includes/contact_form.php'],
    'assets/js/category_dropdown.js' => ['includes/get_categories.php'],
    
    // Admin JavaScript
    'admin/assets/js/adminManagement.js' => ['API/get_admins_fixed.php', 'API/create_admin.php', 'API/get_admin_activity_fixed.php', 'API/get_admin_details.php', 'API/toggle_admin_status.php'],
    'admin/assets/js/adminProfile.js' => ['API/get_admin_profile.php', 'API/update_admin_profile.php']
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

// Test 4: Database Connection Test
echo "<h2>4. Database Connection Test</h2>";
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

// Test 5: Configuration Test
echo "<h2>5. Configuration Test</h2>";
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

// Test 6: Session Test
echo "<h2>6. Session Test</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is active<br>";
    echo "   - Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Session is not active<br>";
}

echo "<h2>🎯 Summary</h2>";
echo "<p><strong>Frontend API files:</strong> " . count($frontend_apis) . "</p>";
echo "<p><strong>Existing Frontend APIs:</strong> " . count($existing_frontend_apis) . "</p>";
echo "<p><strong>Missing Frontend APIs:</strong> " . count($missing_frontend_apis) . "</p>";

echo "<p><strong>Admin API files:</strong> " . count($admin_apis) . "</p>";
echo "<p><strong>Existing Admin APIs:</strong> " . count($existing_admin_apis) . "</p>";
echo "<p><strong>Missing Admin APIs:</strong> " . count($missing_admin_apis) . "</p>";

if (count($missing_frontend_apis) > 0) {
    echo "<h3>🚨 Missing Frontend API Files:</h3>";
    echo "<ul>";
    foreach ($missing_frontend_apis as $missing_api) {
        echo "<li>$missing_api</li>";
    }
    echo "</ul>";
}

if (count($missing_admin_apis) > 0) {
    echo "<h3>🚨 Missing Admin API Files:</h3>";
    echo "<ul>";
    foreach ($missing_admin_apis as $missing_api) {
        echo "<li>$missing_api</li>";
    }
    echo "</ul>";
}

if (count($missing_frontend_apis) === 0 && count($missing_admin_apis) === 0) {
    echo "<p>🎉 <strong>All API files exist!</strong></p>";
} else {
    echo "<p>⚠️ <strong>Some API files are missing. Please create them.</strong></p>";
}

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='index.php'>← Test Homepage</a></p>";
echo "<p><a href='Store.php'>← Test Store Page</a></p>";
echo "<p><a href='admin/'>← Test Admin Panel</a></p>";
echo "<p><a href='test-all-paths.php'>← Test File Paths</a></p>";
echo "<p><a href='test-all-api-endpoints.php'>← Test API Endpoints</a></p>";

echo "<h2>📋 Next Steps</h2>";
if (count($missing_frontend_apis) > 0 || count($missing_admin_apis) > 0) {
    echo "<ol>";
    echo "<li>Create the missing API files listed above</li>";
    echo "<li>Upload all files to the server</li>";
    echo "<li>Test the website functionality</li>";
    echo "<li>Test the admin panel functionality</li>";
    echo "<li>Check browser console for any remaining errors</li>";
    echo "</ol>";
} else {
    echo "<ol>";
    echo "<li>Upload all files to the server</li>";
    echo "<li>Test the website functionality</li>";
    echo "<li>Test the admin panel functionality</li>";
    echo "<li>Check browser console for any remaining errors</li>";
    echo "<li>Verify all features work correctly</li>";
    echo "</ol>";
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>




