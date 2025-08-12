<?php
// Fix product images by assigning proper image paths
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Fix Product Images</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }";
echo ".success { color: green; }";
echo ".error { color: red; }";
echo ".info { color: blue; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>Fix Product Images</h1>";

try {
    $pdo = getDbConnection();
    if (!$pdo) {
        echo "<p class='error'>Database connection failed</p>";
        echo "</div></body></html>";
        exit;
    }

    echo "<p class='success'>Database connected successfully</p>";

    // Available images
    $available_images = [
        'assets/images/IMG_1.jpg',
        'assets/images/IMG_2.jpg', 
        'assets/images/IMG_3.jpg',
        'assets/images/IMG_4.jpg',
        'assets/images/IMG_5.jpg',
        'assets/images/IMG_6.jpg',
        'assets/images/IMG_7.jpg',
        'assets/images/IMG_8.jpg',
        'assets/images/IMG_9.jpg',
        'assets/images/IMG_10.jpg',
        'assets/images/IMG_11.jpg'
    ];

    echo "<h2>Available Images:</h2>";
    echo "<ul>";
    foreach ($available_images as $image) {
        echo "<li>$image</li>";
    }
    echo "</ul>";

    // Get all active products
    $stmt = $pdo->query("SELECT id, name, category FROM products WHERE is_active = 1 ORDER BY id");
    $products = $stmt->fetchAll();

    echo "<h2>Updating Product Images</h2>";

    $update_count = 0;
    foreach ($products as $index => $product) {
        // Assign different images based on product category or index
        $image_index = $index % count($available_images);
        $image_path = $available_images[$image_index];
        
        // Create image paths array
        $image_paths = json_encode([$image_path]);
        
        // Update the product
        $update_stmt = $pdo->prepare("UPDATE products SET image_paths = ? WHERE id = ?");
        $result = $update_stmt->execute([$image_paths, $product['id']]);
        
        if ($result) {
            echo "<p class='success'>✅ Updated product '{$product['name']}' (ID: {$product['id']}) with image: $image_path</p>";
            $update_count++;
        } else {
            echo "<p class='error'>❌ Failed to update product '{$product['name']}' (ID: {$product['id']})</p>";
        }
    }

    echo "<h2>Summary</h2>";
    echo "<p class='success'>Successfully updated $update_count products with proper image paths.</p>";

    // Verify the updates
    echo "<h2>Verification</h2>";
    $stmt = $pdo->query("SELECT id, name, image_paths FROM products WHERE is_active = 1 ORDER BY id");
    while ($row = $stmt->fetch()) {
        $image_paths = json_decode($row['image_paths'], true);
        if ($image_paths && count($image_paths) > 0) {
            echo "<p class='success'>✅ Product '{$row['name']}' (ID: {$row['id']}) has image: {$image_paths[0]}</p>";
        } else {
            echo "<p class='error'>❌ Product '{$row['name']}' (ID: {$row['id']}) has no images</p>";
        }
    }

    echo "<h2>Next Steps</h2>";
    echo "<p class='info'>1. Visit your store page to see the updated product images</p>";
    echo "<p class='info'>2. Check <a href='check_database_images.php'>check_database_images.php</a> to verify the changes</p>";
    echo "<p class='info'>3. If you want to assign specific images to specific products, you can manually update them in the admin panel</p>";

} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
echo "</body></html>";
?>
