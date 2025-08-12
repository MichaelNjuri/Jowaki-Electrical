<?php
// Check table structure
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Table Structure Check</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo ".info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo "h1 { color: #333; text-align: center; }";
echo "h2 { color: #555; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f2f2f2; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔍 Table Structure Check</h1>";

try {
    $pdo = getDbConnection();
    if (!$pdo) {
        echo "<div class='error'>❌ Database connection failed</div>";
        echo "</div></body></html>";
        exit;
    }

    echo "<div class='success'>✅ Database connection successful!</div>";

    // Check if store_categories table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'store_categories'");
    if ($stmt->rowCount() === 0) {
        echo "<div class='error'>❌ store_categories table does not exist</div>";
    } else {
        echo "<div class='success'>✅ store_categories table exists</div>";
        
        // Show table structure
        echo "<h2>📋 store_categories Table Structure</h2>";
        $stmt = $pdo->query("DESCRIBE store_categories");
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
        
        // Show sample data
        echo "<h2>📊 Sample Data from store_categories</h2>";
        $stmt = $pdo->query("SELECT * FROM store_categories LIMIT 5");
        if ($stmt->rowCount() > 0) {
            echo "<table>";
            $first = true;
            while ($row = $stmt->fetch()) {
                if ($first) {
                    echo "<tr>";
                    foreach ($row as $key => $value) {
                        echo "<th>" . htmlspecialchars($key) . "</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='info'>ℹ️ No data in store_categories table</div>";
        }
    }

    // Check if products table exists
    echo "<h2>📋 products Table Structure</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() === 0) {
        echo "<div class='error'>❌ products table does not exist</div>";
    } else {
        echo "<div class='success'>✅ products table exists</div>";
        
        // Show table structure
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
        
        // Show unique categories from products
        echo "<h2>🏷️ Categories from products table</h2>";
        $stmt = $pdo->query("SELECT DISTINCT category, COUNT(*) as count FROM products WHERE category IS NOT NULL AND category != '' GROUP BY category ORDER BY category");
        if ($stmt->rowCount() > 0) {
            echo "<table>";
            echo "<tr><th>Category</th><th>Count</th></tr>";
            while ($row = $stmt->fetch()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . htmlspecialchars($row['count']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='info'>ℹ️ No categories found in products table</div>";
        }
    }

} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div>";
echo "</body></html>";
?>
