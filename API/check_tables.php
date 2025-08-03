<?php
// Check database tables
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

echo "<h1>Database Table Check</h1>";
echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check required tables
$required_tables = [
    'users' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'first_name' => 'VARCHAR(255)',
        'last_name' => 'VARCHAR(255)',
        'email' => 'VARCHAR(255) UNIQUE',
        'password' => 'VARCHAR(255)',
        'phone' => 'VARCHAR(20)',
        'address' => 'TEXT',
        'city' => 'VARCHAR(100)',
        'postal_code' => 'VARCHAR(20)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'orders' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT',
        'subtotal' => 'DECIMAL(10,2)',
        'tax' => 'DECIMAL(10,2)',
        'delivery_fee' => 'DECIMAL(10,2)',
        'total' => 'DECIMAL(10,2)',
        'delivery_method' => 'VARCHAR(100)',
        'delivery_address' => 'TEXT',
        'payment_method' => 'VARCHAR(100)',
        'order_date' => 'DATETIME',
        'status' => 'VARCHAR(50) DEFAULT "pending"',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'order_items' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'order_id' => 'INT',
        'product_id' => 'INT',
        'quantity' => 'INT',
        'price' => 'DECIMAL(10,2)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ]
];

foreach ($required_tables as $table_name => $columns) {
    $result = $conn->query("SHOW TABLES LIKE '$table_name'");
    if ($result->num_rows > 0) {
        echo "<h2 style='color: green;'>✅ Table '$table_name' exists</h2>";
        
        // Check table structure
        $structure = $conn->query("DESCRIBE $table_name");
        $existing_columns = [];
        while ($row = $structure->fetch_assoc()) {
            $existing_columns[$row['Field']] = $row;
        }
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($existing_columns as $field => $details) {
            echo "<tr>";
            echo "<td>" . $details['Field'] . "</td>";
            echo "<td>" . $details['Type'] . "</td>";
            echo "<td>" . $details['Null'] . "</td>";
            echo "<td>" . $details['Key'] . "</td>";
            echo "<td>" . $details['Default'] . "</td>";
            echo "<td>" . $details['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check if required columns exist
        $missing_columns = [];
        foreach ($columns as $column => $type) {
            if (!isset($existing_columns[$column])) {
                $missing_columns[] = $column;
            }
        }
        
        if (!empty($missing_columns)) {
            echo "<p style='color: orange;'>⚠️ Missing columns: " . implode(', ', $missing_columns) . "</p>";
        } else {
            echo "<p style='color: green;'>✅ All required columns present</p>";
        }
        
    } else {
        echo "<h2 style='color: red;'>❌ Table '$table_name' does not exist</h2>";
        echo "<p>Required columns:</p>";
        echo "<ul>";
        foreach ($columns as $column => $type) {
            echo "<li><strong>$column</strong>: $type</li>";
        }
        echo "</ul>";
    }
}

$conn->close();
?> 