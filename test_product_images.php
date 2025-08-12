<?php
// Test product images
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Product Images Test</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }";
echo ".product { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".product img { max-width: 200px; max-height: 150px; border: 1px solid #ccc; }";
echo ".error { color: red; }";
echo ".success { color: green; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>Product Images Test</h1>";

try {
    $pdo = getDbConnection();
    if (!$pdo) {
        echo "<p class='error'>Database connection failed</p>";
        echo "</div></body></html>";
        exit;
    }

    echo "<p class='success'>Database connected successfully</p>";

    // Get products with images
    $stmt = $pdo->query("SELECT id, name, image_paths FROM products WHERE is_active = 1 LIMIT 5");
    
    while ($row = $stmt->fetch()) {
        echo "<div class='product'>";
        echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
        echo "<p><strong>Raw image_paths:</strong> " . htmlspecialchars($row['image_paths']) . "</p>";
        
        $image_paths = json_decode($row['image_paths'], true);
        if ($image_paths && is_array($image_paths)) {
            echo "<p><strong>Decoded images:</strong> " . implode(', ', $image_paths) . "</p>";
            
            if (count($image_paths) > 0) {
                $first_image = $image_paths[0];
                echo "<p><strong>First image:</strong> " . htmlspecialchars($first_image) . "</p>";
                echo "<img src='" . htmlspecialchars($first_image) . "' alt='Product Image' onerror=\"this.style.border='2px solid red'; this.alt='Image failed to load';\" onload=\"this.style.border='2px solid green'; this.alt='Image loaded successfully';\">";
            } else {
                echo "<p class='error'>No images in array</p>";
            }
        } else {
            echo "<p class='error'>Invalid JSON or no images</p>";
        }
        echo "</div>";
    }

    // Test API response
    echo "<h2>API Response Test</h2>";
    $api_url = 'api/get_products.php';
    if (file_exists($api_url)) {
        ob_start();
        include $api_url;
        $api_response = ob_get_clean();
        
        $decoded = json_decode($api_response, true);
        if ($decoded && isset($decoded['products']) && count($decoded['products']) > 0) {
            $sample_product = $decoded['products'][0];
            echo "<div class='product'>";
            echo "<h3>Sample API Product: " . htmlspecialchars($sample_product['name']) . "</h3>";
            echo "<p><strong>Images array:</strong> " . json_encode($sample_product['images']) . "</p>";
            
            if (isset($sample_product['images']) && is_array($sample_product['images']) && count($sample_product['images']) > 0) {
                $first_image = $sample_product['images'][0];
                echo "<p><strong>First image from API:</strong> " . htmlspecialchars($first_image) . "</p>";
                echo "<img src='" . htmlspecialchars($first_image) . "' alt='API Product Image' onerror=\"this.style.border='2px solid red'; this.alt='API Image failed to load';\" onload=\"this.style.border='2px solid green'; this.alt='API Image loaded successfully';\">";
            } else {
                echo "<p class='error'>No images in API response</p>";
            }
            echo "</div>";
        } else {
            echo "<p class='error'>API response is invalid or empty</p>";
        }
    } else {
        echo "<p class='error'>API file not found</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
echo "</body></html>";
?>
