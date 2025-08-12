<?php
require_once 'API/db_connection.php';

$conn = getConnection();
if (!$conn) {
    echo "Database connection failed\n";
    exit;
}

// Check if admin_activity_log table exists
$result = $conn->query("SHOW TABLES LIKE 'admin_activity_log'");
if ($result && $result->num_rows > 0) {
    echo "admin_activity_log table exists\n";
    
    // Check table structure
    $structure = $conn->query("DESCRIBE admin_activity_log");
    if ($structure) {
        echo "Table structure:\n";
        while ($row = $structure->fetch_assoc()) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
    }
} else {
    echo "admin_activity_log table does not exist\n";
}

// Check if admin_users table exists
$result = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($result && $result->num_rows > 0) {
    echo "admin_users table exists\n";
    
    // Check table structure
    $structure = $conn->query("DESCRIBE admin_users");
    if ($structure) {
        echo "admin_users table structure:\n";
        while ($row = $structure->fetch_assoc()) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
    }
} else {
    echo "admin_users table does not exist\n";
}

$conn->close();
?>
