<?php
// Direct test of admin login API
echo "Testing admin login API directly...\n";

// Simulate HTTP request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

// Test data
$test_data = [
    'username' => 'admin',
    'password' => 'admin123'
];

// Set input
file_put_contents('php://input', json_encode($test_data));

// Start output buffering
ob_start();

try {
    // Include the admin login file
    include 'API/admin_login.php';
    
    $output = ob_get_clean();
    echo "SUCCESS: Admin login API executed without fatal errors\n";
    echo "Output: $output\n";
    
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "ERROR: Exception in admin login API: " . $e->getMessage() . "\n";
    echo "Output: $output\n";
} catch (Error $e) {
    $output = ob_get_clean();
    echo "ERROR: Fatal error in admin login API: " . $e->getMessage() . "\n";
    echo "Output: $output\n";
}

// Test with different input method
echo "\nTesting with POST data...\n";
$_POST = $test_data;
unset($_SERVER['HTTP_CONTENT_TYPE']);

ob_start();
try {
    include 'API/admin_login.php';
    $output = ob_get_clean();
    echo "SUCCESS: Admin login API with POST data executed\n";
    echo "Output: $output\n";
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "ERROR: Exception with POST data: " . $e->getMessage() . "\n";
} catch (Error $e) {
    $output = ob_get_clean();
    echo "ERROR: Fatal error with POST data: " . $e->getMessage() . "\n";
}
?>

