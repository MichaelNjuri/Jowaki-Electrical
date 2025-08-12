<?php
// Suppress error output to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

// Global connection variable
$conn = null;

/**
 * Get database connection
 * @return mysqli|false Returns mysqli connection object or false on failure
 */
function getConnection() {
    global $conn;
    
    // If connection already exists and is valid, return it
    if ($conn && $conn->connect_errno === 0) {
        return $conn;
    }
    
    try {
        // Load config only when needed
        if (!defined('DB_HOST')) {
            require_once dirname(__DIR__) . '/config/config.php';
        }
        
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            return false;
        }
        
        // Set charset to prevent encoding issues
        $conn->set_charset("utf8mb4");
        
        return $conn;
        
    } catch (Exception $e) {
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
?>
