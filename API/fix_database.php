<?php
// Fix database structure
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

echo "<h1>Database Structure Fix</h1>";
echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check users table structure
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "<h2>Users Table Structure</h2>";
    
    $structure = $conn->query("DESCRIBE users");
    $existing_columns = [];
    while ($row = $structure->fetch_assoc()) {
        $existing_columns[$row['Field']] = $row;
        echo "<p>Column: " . $row['Field'] . " - Type: " . $row['Type'] . "</p>";
    }
    
    // Check for missing columns
    $required_columns = [
        'address' => 'TEXT',
        'city' => 'VARCHAR(100)',
        'postal_code' => 'VARCHAR(20)'
    ];
    
    foreach ($required_columns as $column => $type) {
        if (!isset($existing_columns[$column])) {
            echo "<p style='color: orange;'>⚠️ Missing column: $column</p>";
            
            // Add the missing column
            $alter_query = "ALTER TABLE users ADD COLUMN $column $type";
            if ($conn->query($alter_query)) {
                echo "<p style='color: green;'>✅ Added column: $column</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to add column: $column - " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ Column exists: $column</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Users table does not exist</p>";
}

// Check orders table structure
$result = $conn->query("SHOW TABLES LIKE 'orders'");
if ($result->num_rows > 0) {
    echo "<h2>Orders Table Structure</h2>";
    
    $structure = $conn->query("DESCRIBE orders");
    $existing_columns = [];
    while ($row = $structure->fetch_assoc()) {
        $existing_columns[$row['Field']] = $row;
        echo "<p>Column: " . $row['Field'] . " - Type: " . $row['Type'] . "</p>";
    }
    
    // Check for missing columns
    $required_columns = [
        'delivery_method' => 'VARCHAR(100)',
        'delivery_address' => 'TEXT',
        'payment_method' => 'VARCHAR(100)',
        'order_date' => 'DATETIME',
        'status' => 'VARCHAR(50) DEFAULT "pending"'
    ];
    
    foreach ($required_columns as $column => $type) {
        if (!isset($existing_columns[$column])) {
            echo "<p style='color: orange;'>⚠️ Missing column: $column</p>";
            
            // Add the missing column
            $alter_query = "ALTER TABLE orders ADD COLUMN $column $type";
            if ($conn->query($alter_query)) {
                echo "<p style='color: green;'>✅ Added column: $column</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to add column: $column - " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ Column exists: $column</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Orders table does not exist</p>";
}

// Check order_items table structure
$result = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($result->num_rows > 0) {
    echo "<h2>Order Items Table Structure</h2>";
    
    $structure = $conn->query("DESCRIBE order_items");
    $existing_columns = [];
    while ($row = $structure->fetch_assoc()) {
        $existing_columns[$row['Field']] = $row;
        echo "<p>Column: " . $row['Field'] . " - Type: " . $row['Type'] . "</p>";
    }
    
    // Check for missing columns
    $required_columns = [
        'price' => 'DECIMAL(10,2)'
    ];
    
    foreach ($required_columns as $column => $type) {
        if (!isset($existing_columns[$column])) {
            echo "<p style='color: orange;'>⚠️ Missing column: $column</p>";
            
            // Add the missing column
            $alter_query = "ALTER TABLE order_items ADD COLUMN $column $type";
            if ($conn->query($alter_query)) {
                echo "<p style='color: green;'>✅ Added column: $column</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to add column: $column - " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ Column exists: $column</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Order items table does not exist</p>";
}

$conn->close();
echo "<h2>Database structure check completed!</h2>";
?> 