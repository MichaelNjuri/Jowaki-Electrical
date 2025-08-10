<?php
// Test admin login API
echo "Testing admin login API...\n";

// Check if admin_login.php exists
if (!file_exists('API/admin_login.php')) {
    echo "ERROR: admin_login.php not found!\n";
    exit;
}

// Test syntax
$php_code = file_get_contents('API/admin_login.php');
if ($php_code === false) {
    echo "ERROR: Cannot read admin_login.php!\n";
    exit;
}

// Check for syntax errors
$syntax_check = shell_exec('C:\xampp\php\php.exe -l API/admin_login.php 2>&1');
echo "Syntax check result:\n$syntax_check\n";

// Test if required files exist
$required_files = [
    'API/db_connection.php',
    'API/check_auth.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "SUCCESS: $file exists\n";
    } else {
        echo "ERROR: $file not found!\n";
    }
}

// Test if we can include the file
try {
    // Start output buffering to capture any output
    ob_start();
    
    // Simulate a POST request
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = ['username' => 'test', 'password' => 'test'];
    
    // Include the file
    include 'API/admin_login.php';
    
    $output = ob_get_clean();
    echo "SUCCESS: admin_login.php can be included without fatal errors\n";
    
} catch (Exception $e) {
    echo "ERROR: Exception when including admin_login.php: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "ERROR: Fatal error when including admin_login.php: " . $e->getMessage() . "\n";
}
?>

