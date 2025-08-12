<?php
// Check orders table structure
header('Content-Type: text/html');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit();
}

echo "<h1>Orders Table Structure Analysis</h1>";
echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check if orders table exists
$result = $conn->query("SHOW TABLES LIKE 'orders'");
if ($result->num_rows > 0) {
    echo "<h2>Orders Table Structure</h2>";
    
    $structure = $conn->query("DESCRIBE orders");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
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
    
    // Check for constraints
    echo "<h2>Table Constraints</h2>";
    $constraints = $conn->query("SHOW CREATE TABLE orders");
    if ($constraints) {
        $create_table = $constraints->fetch_assoc();
        echo "<pre>" . htmlspecialchars($create_table['Create Table']) . "</pre>";
    }
    
    // Check if there's a customer_info column
    $columns_query = "SHOW COLUMNS FROM orders";
    $columns_result = $conn->query($columns_query);
    $has_customer_info = false;
    $has_cart = false;
    while ($row = $columns_result->fetch_assoc()) {
        if ($row['Field'] === 'customer_info') {
            $has_customer_info = true;
            echo "<h3>Customer Info Column Details:</h3>";
            echo "<p>Type: " . $row['Type'] . "</p>";
            echo "<p>Null: " . $row['Null'] . "</p>";
            echo "<p>Default: " . $row['Default'] . "</p>";
            echo "<p>Extra: " . $row['Extra'] . "</p>";
        }
        if ($row['Field'] === 'cart') {
            $has_cart = true;
            echo "<h3>Cart Column Details:</h3>";
            echo "<p>Type: " . $row['Type'] . "</p>";
            echo "<p>Null: " . $row['Null'] . "</p>";
            echo "<p>Default: " . $row['Default'] . "</p>";
            echo "<p>Extra: " . $row['Extra'] . "</p>";
        }
    }
    
    if (!$has_customer_info) {
        echo "<p style='color: orange;'>⚠️ No customer_info column found in orders table</p>";
    }
    if (!$has_cart) {
        echo "<p style='color: orange;'>⚠️ No cart column found in orders table</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Orders table does not exist</p>";
}

$conn->close();
?> 