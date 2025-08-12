<?php
// Debug script to check product images
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Product Images Debug</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo ".info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo "h1 { color: #333; text-align: center; }";
echo "h2 { color: #555; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f2f2f2; }";
echo ".product-image { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔍 Product Images Debug</h1>";

try {
    $pdo = getDbConnection();
    if (!$pdo) {
        echo "<div class='error'>❌ Database connection failed</div>";
        echo "</div></body></html>";
        exit;
    }

    echo "<div class='success'>✅ Database connection successful!</div>";

    // Check products table structure
    echo "<h2>📋 Products Table Structure</h2>";
    $stmt = $pdo->query("DESCRIBE products");
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
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

    // Check products with images
    echo "<h2>📊 Products with Image Data</h2>";
    $stmt = $pdo->query("SELECT id, name, image_paths, is_active FROM products WHERE is_active = 1 LIMIT 10");
    
    if ($stmt->rowCount() > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Image Paths (Raw)</th><th>Image Paths (Decoded)</th><th>First Image</th><th>Image Preview</th></tr>";
        
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['image_paths']) . "</td>";
            
            $image_paths = json_decode($row['image_paths'], true);
            if ($image_paths) {
                echo "<td>" . htmlspecialchars(implode(', ', $image_paths)) . "</td>";
                $first_image = $image_paths[0] ?? 'No image';
                echo "<td>" . htmlspecialchars($first_image) . "</td>";
                echo "<td>";
                if ($first_image && $first_image !== 'No image') {
                    echo "<img src='" . htmlspecialchars($first_image) . "' class='product-image' onerror=\"this.style.display='none'; this.nextSibling.style.display='block';\" alt='Product Image'>";
                    echo "<span style='display:none; color:red;'>❌ Image failed to load</span>";
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
        echo "<div class='info'>ℹ️ No active products found</div>";
    }

    // Test API response
    echo "<h2>🧪 API Response Test</h2>";
    echo "<div class='info'>Testing get_products.php API response:</div>";
    
    // Simulate API call
    $api_url = 'api/get_products.php';
    if (file_exists($api_url)) {
        ob_start();
        include $api_url;
        $api_response = ob_get_clean();
        
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>API Response:</strong><br>";
        echo "<pre>" . htmlspecialchars($api_response) . "</pre>";
        echo "</div>";
        
        $decoded = json_decode($api_response, true);
        if ($decoded && isset($decoded['products'])) {
            echo "<div class='success'>✅ API returned " . count($decoded['products']) . " products</div>";
            
            if (count($decoded['products']) > 0) {
                $sample_product = $decoded['products'][0];
                echo "<div class='info'>Sample product structure:</div>";
                echo "<pre>" . htmlspecialchars(json_encode($sample_product, JSON_PRETTY_PRINT)) . "</pre>";
            }
        } else {
            echo "<div class='error'>❌ API response is not valid JSON</div>";
        }
    } else {
        echo "<div class='error'>❌ API file not found: $api_url</div>";
    }

} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div>";
echo "</body></html>";
?>
