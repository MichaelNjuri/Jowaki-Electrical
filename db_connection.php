<?php
$host = "localhost";
$user = "root";
$pass = ""; // Replace with your password if set
$dbname = "jowaki_db";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error, 3, "/path/to/error.log"); // Log to a file
    http_response_code(500); // Internal server error
    die("An error occurred. Please try again later."); // User-friendly message
}

// Set charset to prevent encoding issues
$conn->set_charset("utf8mb4");

?>