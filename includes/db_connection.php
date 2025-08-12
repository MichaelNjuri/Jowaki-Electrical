<?php
// Suppress error output to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

// Database configuration
require_once dirname(__DIR__) . '/config/config.php';
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$dbname = DB_NAME;

// Global connection variable
$conn = null;

/**
 * Get database connection
 * @return mysqli|false Returns mysqli connection object or false on failure
 */
function getConnection() {
    global $host, $user, $pass, $dbname, $conn;
    
    // If connection already exists and is valid, return it
    if ($conn && $conn->connect_errno === 0) {
        return $conn;
    }
    
    try {
        $conn = new mysqli($host, $user, $pass, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
            return false;
        }
        
        // Set charset to prevent encoding issues
        $conn->set_charset("utf8mb4");
        
        error_log("Database connection successful", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        
        return $conn;
        
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        return false;
    }
}

/**
 * Close database connection
 */
function closeConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
        $conn = null;
    }
}

// Initialize connection when this file is included
$conn = getConnection();
if (!$conn) {
    error_log("Failed to initialize database connection in db_connection.php", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    // Don't die here, let the calling script handle the error
}
?>