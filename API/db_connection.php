<?php
$host = "localhost";
$user = "root";
$pass = ""; // Replace with your password if set
$dbname = "jowaki_db";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        http_response_code(500);
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set charset to prevent encoding issues
    $conn->set_charset("utf8mb4");
    
    error_log("Database connection successful", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    http_response_code(500);
    die("Database connection failed. Please try again later.");
}
?>