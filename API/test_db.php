<?php
// Test database connection and table structure
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    echo "<h2>✅ Database Connection Successful</h2>";
    echo "<p>Connected to database: $dbname</p>";

    // Check if users table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tableExists->num_rows === 0) {
        echo "<h2>❌ Users table does not exist</h2>";
    } else {
        echo "<h2>✅ Users table exists</h2>";
        
        // Show users table structure
        $result = $conn->query("DESCRIBE users");
        echo "<h3>Users table structure:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample users
        $users = $conn->query("SELECT * FROM users LIMIT 5");
        echo "<h3>Sample users:</h3>";
        echo "<table border='1'>";
        if ($users->num_rows > 0) {
            $first = true;
            while ($row = $users->fetch_assoc()) {
                if ($first) {
                    echo "<tr>";
                    foreach ($row as $key => $value) {
                        echo "<th>$key</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='10'>No users found</td></tr>";
        }
        echo "</table>";
    }

    // Check if orders table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($tableExists->num_rows === 0) {
        echo "<h2>❌ Orders table does not exist</h2>";
    } else {
        echo "<h2>✅ Orders table exists</h2>";
        
        // Show orders table structure
        $result = $conn->query("DESCRIBE orders");
        echo "<h3>Orders table structure:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample orders
        $orders = $conn->query("SELECT * FROM orders LIMIT 5");
        echo "<h3>Sample orders:</h3>";
        echo "<table border='1'>";
        if ($orders->num_rows > 0) {
            $first = true;
            while ($row = $orders->fetch_assoc()) {
                if ($first) {
                    echo "<tr>";
                    foreach ($row as $key => $value) {
                        echo "<th>$key</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='10'>No orders found</td></tr>";
        }
        echo "</table>";
    }

    $conn->close();
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>