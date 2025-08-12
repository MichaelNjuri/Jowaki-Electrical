<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create store_categories table
    $sql = "CREATE TABLE IF NOT EXISTS store_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        display_name VARCHAR(255) NOT NULL,
        image_url TEXT,
        icon_class VARCHAR(100) DEFAULT 'fas fa-box',
        filter_value VARCHAR(255) NOT NULL,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $conn->exec($sql);
    echo "Store categories table created successfully!\n";

    // Insert some sample categories based on the design image
    $sampleCategories = [
        [
            'name' => '4G_WIFI_DC_CAMERA',
            'display_name' => '4G/WIFI DC Camera',
            'icon_class' => 'fas fa-video',
            'filter_value' => '4G_WIFI_DC_CAMERA'
        ],
        [
            'name' => 'A9_HD_HIDDEN_CAMERA',
            'display_name' => 'A9 HD Hidden CAMERA',
            'icon_class' => 'fas fa-eye',
            'filter_value' => 'A9_HD_HIDDEN_CAMERA'
        ],
        [
            'name' => 'ACCESSORIES',
            'display_name' => 'Accessories',
            'icon_class' => 'fas fa-tools',
            'filter_value' => 'ACCESSORIES'
        ],
        [
            'name' => 'CABLES',
            'display_name' => 'Cables',
            'icon_class' => 'fas fa-plug',
            'filter_value' => 'CABLES'
        ],
        [
            'name' => 'CCTV',
            'display_name' => 'CCTV',
            'icon_class' => 'fas fa-shield-alt',
            'filter_value' => 'CCTV'
        ],
        [
            'name' => 'COMPUTER_KEYBOARDS',
            'display_name' => 'Computer Keyboards',
            'icon_class' => 'fas fa-keyboard',
            'filter_value' => 'COMPUTER_KEYBOARDS'
        ],
        [
            'name' => 'DAHUA',
            'display_name' => 'Dahua',
            'icon_class' => 'fas fa-camera',
            'filter_value' => 'DAHUA'
        ]
    ];

    $insertSql = "INSERT INTO store_categories (name, display_name, icon_class, filter_value, sort_order) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);

    foreach ($sampleCategories as $index => $category) {
        try {
            $stmt->execute([
                $category['name'],
                $category['display_name'],
                $category['icon_class'],
                $category['filter_value'],
                $index + 1
            ]);
            echo "Added category: {$category['display_name']}\n";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                echo "Category '{$category['display_name']}' already exists\n";
            } else {
                echo "Error adding category '{$category['display_name']}': " . $e->getMessage() . "\n";
            }
        }
    }

    echo "Sample categories inserted successfully!\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn = null;
?>

