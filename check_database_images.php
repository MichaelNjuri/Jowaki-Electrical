<?php
// Check database images
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Database Images Check</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }";
echo ".product { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".product img { max-width: 200px; max-height: 150px; border: 1px solid #ccc; }";
echo ".error { color: red; }";
echo ".success { color: green; }";
echo ".info { color: blue; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>Database Images Check</h1>";

try {
    $pdo = getDbConnection();
    if (!$pdo) {
        echo "<p class='error'>Database connection failed</p>";
        echo "</div></body></html>";
        exit;
    }

    echo "<p class='success'>Database connected successfully</p>";

    // Check products table structure
    echo "<h2>Products Table Structure</h2>";
    $stmt = $pdo->query("DESCRIBE products");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Check all products with their image data
    echo "<h2>All Products with Image Data</h2>";
    $stmt = $pdo->query("SELECT id, name, image_paths, is_active FROM products ORDER BY id");
    
    if ($stmt->rowCount() > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Active</th><th>Raw image_paths</th><th>Decoded Images</th><th>First Image</th><th>Preview</th></tr>";
        
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td style='max-width: 200px; word-wrap: break-word;'>" . htmlspecialchars($row['image_paths']) . "</td>";
            
            $image_paths = json_decode($row['image_paths'], true);
            if ($image_paths && is_array($image_paths)) {
                echo "<td>" . htmlspecialchars(implode(', ', $image_paths)) . "</td>";
                $first_image = $image_paths[0] ?? 'No image';
                echo "<td>" . htmlspecialchars($first_image) . "</td>";
                echo "<td>";
                if ($first_image && $first_image !== 'No image') {
                    echo "<img src='" . htmlspecialchars($first_image) . "' style='max-width: 100px; max-height: 75px;' onerror=\"this.style.border='2px solid red'; this.alt='Failed to load';\" onload=\"this.style.border='2px solid green'; this.alt='Loaded successfully';\">";
                } else {
                    echo "<span style='color:orange;'>⚠️ No image</span>";
                }
                echo "</td>";
            } else {
                echo "<td colspan='3' style='color:red;'>❌ Invalid JSON or no images</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No products found in database</p>";
    }

    // Check if there are any products with actual images
    echo "<h2>Products with Valid Images</h2>";
    $stmt = $pdo->query("SELECT id, name, image_paths FROM products WHERE image_paths IS NOT NULL AND image_paths != '' AND image_paths != '[]' AND is_active = 1");
    
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>Found " . $stmt->rowCount() . " products with image data</p>";
        while ($row = $stmt->fetch()) {
            echo "<div class='product'>";
            echo "<h3>" . htmlspecialchars($row['name']) . " (ID: " . $row['id'] . ")</h3>";
            $image_paths = json_decode($row['image_paths'], true);
            if ($image_paths && is_array($image_paths)) {
                foreach ($image_paths as $index => $image_path) {
                    echo "<p><strong>Image " . ($index + 1) . ":</strong> " . htmlspecialchars($image_path) . "</p>";
                    echo "<img src='" . htmlspecialchars($image_path) . "' alt='Product Image' onerror=\"this.style.border='2px solid red'; this.alt='Failed to load';\" onload=\"this.style.border='2px solid green'; this.alt='Loaded successfully';\">";
                }
            }
            echo "</div>";
        }
    } else {
        echo "<p class='error'>No products with valid image data found</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
echo "</body></html>";
?>
