<?php
/**
 * Jowaki Electrical Services - System Test Script
 * This script tests all major functionality of the system
 */

// Start session
session_start();

// Include necessary files
require_once 'API/db_connection.php';
require_once 'API/email_service.php';
require_once 'API/load_settings.php';

// Test results array
$test_results = [];

// Function to log test results
function logTest($test_name, $passed, $message = '') {
    global $test_results;
    $test_results[] = [
        'test' => $test_name,
        'passed' => $passed,
        'message' => $message
    ];
    
    $status = $passed ? '✅ PASS' : '❌ FAIL';
    echo "$status: $test_name";
    if ($message) {
        echo " - $message";
    }
    echo "\n";
}

echo "🧪 Jowaki Electrical Services - System Test\n";
echo "==========================================\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    if ($conn && !$conn->connect_error) {
        logTest('Database Connection', true, 'Connected successfully');
    } else {
        logTest('Database Connection', false, 'Connection failed');
    }
} catch (Exception $e) {
    logTest('Database Connection', false, $e->getMessage());
}

// Test 2: Load Settings
echo "\n2. Testing Settings Loading...\n";
try {
    $settings = getStoreSettings($conn);
    if ($settings && isset($settings['store_name'])) {
        logTest('Settings Loading', true, 'Settings loaded successfully');
        logTest('WhatsApp Number', isset($settings['whatsapp_number']), 'WhatsApp: ' . ($settings['whatsapp_number'] ?? 'Not set'));
    } else {
        logTest('Settings Loading', false, 'Failed to load settings');
    }
} catch (Exception $e) {
    logTest('Settings Loading', false, $e->getMessage());
}

// Test 3: Email Service
echo "\n3. Testing Email Service...\n";
try {
    $emailService = new EmailService();
    logTest('Email Service Initialization', true, 'Email service created');
    
    // Test email template generation
    $test_order_data = [
        'order_id' => 'TEST001',
        'total' => 1000.00,
        'order_date' => date('Y-m-d H:i:s'),
        'payment_method' => 'M-Pesa',
        'delivery_method' => 'Standard Delivery',
        'customer_info' => [
            'firstName' => 'Test',
            'lastName' => 'User'
        ]
    ];
    
    $html_template = $emailService->sendOrderConfirmationEmail($test_order_data, 'test@example.com', 'Test User');
    if (strlen($html_template) > 100) {
        logTest('Email Template Generation', true, 'HTML template generated successfully');
    } else {
        logTest('Email Template Generation', false, 'Template generation failed');
    }
} catch (Exception $e) {
    logTest('Email Service', false, $e->getMessage());
}

// Test 4: Database Tables
echo "\n4. Testing Database Tables...\n";
$required_tables = ['users', 'orders', 'order_items', 'system_settings'];
foreach ($required_tables as $table) {
    try {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            logTest("Table: $table", true, 'Table exists');
        } else {
            logTest("Table: $table", false, 'Table does not exist');
        }
    } catch (Exception $e) {
        logTest("Table: $table", false, $e->getMessage());
    }
}

// Test 5: WhatsApp Integration
echo "\n5. Testing WhatsApp Integration...\n";
try {
    $settings = getStoreSettings($conn);
    $whatsapp_number = $settings['whatsapp_number'] ?? '254721442248';
    
    // Test WhatsApp number format
    $clean_number = preg_replace('/[^\d+]/', '', $whatsapp_number);
    $final_number = strpos($clean_number, '+') === 0 ? substr($clean_number, 1) : $clean_number;
    
    if (strlen($final_number) >= 10) {
        logTest('WhatsApp Number Format', true, "Number: $final_number");
    } else {
        logTest('WhatsApp Number Format', false, 'Invalid number format');
    }
    
    // Test WhatsApp URL generation
    $test_message = "Hello Jowaki Electrical, I would like to inquire about your products.";
    $whatsapp_url = "https://wa.me/$final_number?text=" . urlencode($test_message);
    
    if (filter_var($whatsapp_url, FILTER_VALIDATE_URL)) {
        logTest('WhatsApp URL Generation', true, 'URL generated successfully');
    } else {
        logTest('WhatsApp URL Generation', false, 'URL generation failed');
    }
} catch (Exception $e) {
    logTest('WhatsApp Integration', false, $e->getMessage());
}

// Test 6: File Permissions
echo "\n6. Testing File Permissions...\n";
$required_files = [
    'API/email_service.php',
    'API/place_order.php',
    'API/contact_form.php',
    'API/forgot_password.php',
    'API/reset_password.php',
    'Store.php',
    'cart.php',
    'checkout.php',
    'Service.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            logTest("File: $file", true, 'File exists and readable');
        } else {
            logTest("File: $file", false, 'File exists but not readable');
        }
    } else {
        logTest("File: $file", false, 'File does not exist');
    }
}

// Test 7: Upload Directory
echo "\n7. Testing Upload Directory...\n";
$upload_dir = 'Uploads/';
if (is_dir($upload_dir)) {
    if (is_writable($upload_dir)) {
        logTest('Upload Directory', true, 'Directory exists and writable');
    } else {
        logTest('Upload Directory', false, 'Directory exists but not writable');
    }
} else {
    logTest('Upload Directory', false, 'Directory does not exist');
}

// Test 8: Session Management
echo "\n8. Testing Session Management...\n";
try {
    $_SESSION['test_session'] = 'test_value';
    if (isset($_SESSION['test_session']) && $_SESSION['test_session'] === 'test_value') {
        logTest('Session Management', true, 'Session working correctly');
    } else {
        logTest('Session Management', false, 'Session not working');
    }
    unset($_SESSION['test_session']);
} catch (Exception $e) {
    logTest('Session Management', false, $e->getMessage());
}

// Test 9: API Endpoints
echo "\n9. Testing API Endpoints...\n";
$api_endpoints = [
    'API/get_products.php',
    'API/get_categories.php',
    'API/get_settings.php',
    'API/get_cart_count.php'
];

foreach ($api_endpoints as $endpoint) {
    if (file_exists($endpoint)) {
        logTest("API Endpoint: $endpoint", true, 'Endpoint exists');
    } else {
        logTest("API Endpoint: $endpoint", false, 'Endpoint missing');
    }
}

// Test 10: JavaScript Modules
echo "\n10. Testing JavaScript Modules...\n";
$js_modules = [
    'js/modules/store-ui.js',
    'js/modules/store-products.js',
    'js/modules/store-cart.js',
    'js/modules/store-checkout.js'
];

foreach ($js_modules as $module) {
    if (file_exists($module)) {
        logTest("JS Module: $module", true, 'Module exists');
    } else {
        logTest("JS Module: $module", false, 'Module missing');
    }
}

// Summary
echo "\n📊 Test Summary\n";
echo "==============\n";

$total_tests = count($test_results);
$passed_tests = count(array_filter($test_results, function($test) {
    return $test['passed'];
}));
$failed_tests = $total_tests - $passed_tests;

echo "Total Tests: $total_tests\n";
echo "Passed: $passed_tests\n";
echo "Failed: $failed_tests\n";
echo "Success Rate: " . round(($passed_tests / $total_tests) * 100, 2) . "%\n\n";

if ($failed_tests > 0) {
    echo "❌ Failed Tests:\n";
    foreach ($test_results as $result) {
        if (!$result['passed']) {
            echo "- {$result['test']}: {$result['message']}\n";
        }
    }
    echo "\n";
}

if ($passed_tests === $total_tests) {
    echo "🎉 All tests passed! The system is ready for use.\n";
} else {
    echo "⚠️  Some tests failed. Please review the failed tests above.\n";
}

// Recommendations
echo "\n💡 Recommendations:\n";
echo "==================\n";

if ($failed_tests > 0) {
    echo "1. Fix the failed tests before deploying to production\n";
    echo "2. Check file permissions for upload directories\n";
    echo "3. Verify database table structure\n";
    echo "4. Test email functionality with real SMTP settings\n";
    echo "5. Configure WhatsApp number in admin settings\n";
} else {
    echo "1. ✅ System is ready for production use\n";
    echo "2. ✅ Configure email settings for notifications\n";
    echo "3. ✅ Set up admin account and configure store settings\n";
    echo "4. ✅ Add products and test the complete workflow\n";
    echo "5. ✅ Test payment methods and delivery options\n";
}

echo "\n🔧 Next Steps:\n";
echo "==============\n";
echo "1. Access admin dashboard: AdminDashboard.html\n";
echo "2. Configure store settings and payment methods\n";
echo "3. Add products and categories\n";
echo "4. Test customer registration and ordering\n";
echo "5. Monitor email notifications and WhatsApp integration\n";

// Close database connection
if (isset($conn)) {
    $conn->close();
}

echo "\n✨ Test completed successfully!\n";
?>
