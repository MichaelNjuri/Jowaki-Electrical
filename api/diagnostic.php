<?php
// Diagnostic script to identify API issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>API Diagnostic Report</h1>";

// Test 1: Check if config file can be loaded
echo "<h2>Test 1: Config File</h2>";
try {
    require_once '../config/config.php';
    echo "✅ Config file loaded successfully<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
    echo "DEBUG_MODE: " . (DEBUG_MODE ? 'true' : 'false') . "<br>";
} catch (Exception $e) {
    echo "❌ Config file error: " . $e->getMessage() . "<br>";
}

// Test 2: Check database connection
echo "<h2>Test 2: Database Connection</h2>";
try {
    require_once '../includes/db_connection_fixed.php';
    $conn = getConnection();
    if ($conn) {
        echo "✅ Database connection successful<br>";
        
        // Test if products table exists
        $result = $conn->query("SHOW TABLES LIKE 'products'");
        if ($result->num_rows > 0) {
            echo "✅ Products table exists<br>";
            
            // Count products
            $count_result = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
            $count = $count_result->fetch_assoc()['count'];
            echo "📊 Active products: " . $count . "<br>";
        } else {
            echo "❌ Products table does not exist<br>";
        }
        
        // Test if store_categories table exists
        $result = $conn->query("SHOW TABLES LIKE 'store_categories'");
        if ($result->num_rows > 0) {
            echo "✅ Store categories table exists<br>";
            
            // Count categories
            $count_result = $conn->query("SELECT COUNT(*) as count FROM store_categories WHERE is_active = 1");
            $count = $count_result->fetch_assoc()['count'];
            echo "📊 Active categories: " . $count . "<br>";
        } else {
            echo "⚠️ Store categories table does not exist (will use products table fallback)<br>";
        }
        
        $conn->close();
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Test categories API
echo "<h2>Test 3: Categories API</h2>";
try {
    ob_start();
    include 'get_categories.php';
    $output = ob_get_clean();
    
    $json = json_decode($output, true);
    if ($json) {
        echo "✅ Categories API returned valid JSON<br>";
        echo "Success: " . ($json['success'] ? 'true' : 'false') . "<br>";
        if (isset($json['categories'])) {
            echo "Categories count: " . count($json['categories']) . "<br>";
        }
        if (isset($json['error'])) {
            echo "Error: " . $json['error'] . "<br>";
        }
    } else {
        echo "❌ Categories API returned invalid JSON<br>";
        echo "Raw output: <pre>" . htmlspecialchars($output) . "</pre>";
    }
} catch (Exception $e) {
    echo "❌ Categories API error: " . $e->getMessage() . "<br>";
}

// Test 4: Test products API
echo "<h2>Test 4: Products API</h2>";
try {
    ob_start();
    include 'get_products.php';
    $output = ob_get_clean();
    
    $json = json_decode($output, true);
    if ($json) {
        echo "✅ Products API returned valid JSON<br>";
        echo "Success: " . ($json['success'] ? 'true' : 'false') . "<br>";
        if (isset($json['products'])) {
            echo "Products count: " . count($json['products']) . "<br>";
        }
        if (isset($json['error'])) {
            echo "Error: " . $json['error'] . "<br>";
        }
    } else {
        echo "❌ Products API returned invalid JSON<br>";
        echo "Raw output: <pre>" . htmlspecialchars($output) . "</pre>";
    }
} catch (Exception $e) {
    echo "❌ Products API error: " . $e->getMessage() . "<br>";
}

echo "<h2>Summary</h2>";
echo "If you see any ❌ errors above, those need to be fixed.<br>";
echo "If all tests show ✅, the issue might be with server deployment or caching.<br>";
?>
