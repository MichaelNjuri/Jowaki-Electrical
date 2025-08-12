<?php
// Quick database connection test
require_once 'config/config.php';

echo "<h2>Database Connection Test</h2>";

try {
    $pdo = getDbConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM store_categories");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Store categories table accessible: " . $result['count'] . " categories found</p>";
    
    // Test products table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Products table accessible: " . $result['count'] . " products found</p>";
    
    echo "<p style='color: blue;'>🎉 All database connections working! Your store should now function properly.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials in config/config.php</p>";
}
?>
