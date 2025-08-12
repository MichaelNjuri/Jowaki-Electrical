<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if display_name column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM store_categories LIKE 'display_name'");
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;

    if (!$columnExists) {
        // Add display_name column
        $conn->exec("ALTER TABLE store_categories ADD COLUMN display_name VARCHAR(255) NOT NULL AFTER name");
        echo "Added display_name column\n";
        
        // Update existing records to use name as display_name
        $conn->exec("UPDATE store_categories SET display_name = name WHERE display_name = '' OR display_name IS NULL");
        echo "Updated existing records with display_name\n";
    }

    // Check if image_url column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM store_categories LIKE 'image_url'");
    $stmt->execute();
    $imageUrlExists = $stmt->rowCount() > 0;

    if (!$imageUrlExists) {
        // Add image_url column
        $conn->exec("ALTER TABLE store_categories ADD COLUMN image_url TEXT AFTER display_name");
        echo "Added image_url column\n";
    }

    // Check if icon_class column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM store_categories LIKE 'icon_class'");
    $stmt->execute();
    $iconClassExists = $stmt->rowCount() > 0;

    if (!$iconClassExists) {
        // Add icon_class column
        $conn->exec("ALTER TABLE store_categories ADD COLUMN icon_class VARCHAR(100) DEFAULT 'fas fa-box' AFTER image_url");
        echo "Added icon_class column\n";
    }

    // Check if filter_value column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM store_categories LIKE 'filter_value'");
    $stmt->execute();
    $filterValueExists = $stmt->rowCount() > 0;

    if (!$filterValueExists) {
        // Add filter_value column
        $conn->exec("ALTER TABLE store_categories ADD COLUMN filter_value VARCHAR(255) NOT NULL AFTER icon_class");
        echo "Added filter_value column\n";
        
        // Update existing records to use name as filter_value
        $conn->exec("UPDATE store_categories SET filter_value = name WHERE filter_value = '' OR filter_value IS NULL");
        echo "Updated existing records with filter_value\n";
    }

    // Check if sort_order column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM store_categories LIKE 'sort_order'");
    $stmt->execute();
    $sortOrderExists = $stmt->rowCount() > 0;

    if (!$sortOrderExists) {
        // Add sort_order column
        $conn->exec("ALTER TABLE store_categories ADD COLUMN sort_order INT DEFAULT 0 AFTER filter_value");
        echo "Added sort_order column\n";
    }

    // Check if is_active column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM store_categories LIKE 'is_active'");
    $stmt->execute();
    $isActiveExists = $stmt->rowCount() > 0;

    if (!$isActiveExists) {
        // Add is_active column
        $conn->exec("ALTER TABLE store_categories ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER sort_order");
        echo "Added is_active column\n";
    }

    echo "Store categories table structure fixed successfully!\n";

    // Show current table structure
    $stmt = $conn->prepare("DESCRIBE store_categories");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn = null;
?>
