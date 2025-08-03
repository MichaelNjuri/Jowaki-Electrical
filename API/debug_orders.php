<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Orders Debug Information</h1>";

// Check session
echo "<h2>Session Information</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "</p>";
echo "<p>Logged in: " . (isset($_SESSION['logged_in']) ? ($_SESSION['logged_in'] ? 'Yes' : 'No') : 'Not set') . "</p>";

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
        
        // Show all orders
        $orders = $conn->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 10");
        echo "<h3>All Orders (Last 10):</h3>";
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
        
        // If user is logged in, show their orders
        if (isset($_SESSION['user_id']) && $_SESSION['logged_in']) {
            $userId = $_SESSION['user_id'];
            echo "<h3>Orders for User ID: $userId</h3>";
            
            $userOrders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
            $userOrders->bind_param("i", $userId);
            $userOrders->execute();
            $result = $userOrders->get_result();
            
            echo "<table border='1'>";
            if ($result->num_rows > 0) {
                $first = true;
                while ($row = $result->fetch_assoc()) {
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
                echo "<tr><td colspan='10'>No orders found for this user</td></tr>";
            }
            echo "</table>";
        }
    }

    $conn->close();
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?> 