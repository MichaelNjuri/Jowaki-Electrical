<?php
// Create contact_messages table for storing contact form submissions
require_once 'db_connection.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        subject VARCHAR(500) NOT NULL,
        message TEXT NOT NULL,
        submitted_at DATETIME NOT NULL,
        ip_address VARCHAR(45),
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Contact messages table created successfully\n";
    } else {
        echo "❌ Error creating contact messages table: " . $conn->error . "\n";
    }
    
    // Add phone column if it doesn't exist (for existing tables)
    $checkPhoneColumn = "SHOW COLUMNS FROM contact_messages LIKE 'phone'";
    $result = $conn->query($checkPhoneColumn);
    
    if ($result->num_rows == 0) {
        $addPhoneColumn = "ALTER TABLE contact_messages ADD COLUMN phone VARCHAR(50) NOT NULL AFTER email";
        if ($conn->query($addPhoneColumn) === TRUE) {
            echo "✅ Phone column added to existing contact_messages table\n";
        } else {
            echo "❌ Error adding phone column: " . $conn->error . "\n";
        }
    } else {
        echo "✅ Phone column already exists in contact_messages table\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
