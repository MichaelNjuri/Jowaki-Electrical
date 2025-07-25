<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

echo "Testing database connection...\n";

// Check if db_connection.php exists
$db_file = __DIR__ . DIRECTORY_SEPARATOR . 'db_connection.php';
echo "Looking for db_connection.php at: " . $db_file . "\n";

if (!file_exists($db_file)) {
    die("ERROR: db_connection.php not found!\n");
}

echo "db_connection.php found. Including file...\n";

try {
    require $db_file;
    echo "db_connection.php included successfully.\n";
} catch (Exception $e) {
    die("ERROR including db_connection.php: " . $e->getMessage() . "\n");
} catch (Error $e) {
    die("FATAL ERROR in db_connection.php: " . $e->getMessage() . "\n");
}

// Check if $conn variable exists
if (!isset($conn)) {
    die("ERROR: \$conn variable is not set after including db_connection.php\n");
}

if (!$conn) {
    die("ERROR: \$conn is false or null\n");
}

echo "Connection variable exists and is truthy.\n";

// Test connection
if (method_exists($conn, 'ping')) {
    if ($conn->ping()) {
        echo "Database connection is alive (ping successful).\n";
    } else {
        echo "WARNING: Database connection ping failed.\n";
    }
}

// Test a simple query
try {
    $result = $conn->query("SELECT 1 as test");
    if ($result) {
        echo "Simple query test successful.\n";
        $row = $result->fetch_assoc();
        echo "Query result: " . $row['test'] . "\n";
    } else {
        echo "ERROR: Simple query failed: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: Exception during query test: " . $e->getMessage() . "\n";
}

// Test users table structure
try {
    $result = $conn->query("DESCRIBE users");
    if ($result) {
        echo "\nUsers table structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "ERROR: Could not describe users table: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: Exception while checking users table: " . $e->getMessage() . "\n";
}

echo "\nDatabase test completed.\n";
?>