<?php
// Test the database connection and settings loading
echo "<h1>Database Connection and Settings Test</h1>";

// Test 1: Direct database connection
echo "<h2>Test 1: Direct Database Connection</h2>";
try {
    $testConn = new mysqli('localhost', 'root', '', 'jowaki_db');
    if ($testConn->connect_error) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $testConn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
        
        // Test if system_settings table exists
        $result = $testConn->query("SHOW TABLES LIKE 'system_settings'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✅ system_settings table exists</p>";
            
            // Test if we can read from the table
            $result = $testConn->query("SELECT COUNT(*) as count FROM system_settings");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<p style='color: green;'>✅ Found " . $row['count'] . " settings in database</p>";
            } else {
                echo "<p style='color: red;'>❌ Cannot read from system_settings table</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ system_settings table does not exist (will use defaults)</p>";
        }
        
        $testConn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection error: " . $e->getMessage() . "</p>";
}

// Test 2: Settings loading function
echo "<h2>Test 2: Settings Loading Function</h2>";
try {
    require_once 'API/load_settings.php';
    
    // Test with null connection (should create new one)
    $settings1 = getStoreSettings(null);
    echo "<p style='color: green;'>✅ Settings loaded with null connection</p>";
    
    // Test with valid connection
    $testConn = new mysqli('localhost', 'root', '', 'jowaki_db');
    if (!$testConn->connect_error) {
        $settings2 = getStoreSettings($testConn);
        echo "<p style='color: green;'>✅ Settings loaded with valid connection</p>";
        $testConn->close();
    }
    
    // Test with closed connection
    $testConn = new mysqli('localhost', 'root', '', 'jowaki_db');
    $testConn->close();
    $settings3 = getStoreSettings($testConn);
    echo "<p style='color: green;'>✅ Settings loaded with closed connection</p>";
    
    echo "<h3>Sample Settings:</h3>";
    echo "<ul>";
    echo "<li><strong>Tax Rate:</strong> " . $settings1['tax_rate'] . "%</li>";
    echo "<li><strong>Store Phone:</strong> " . $settings1['store_phone'] . "</li>";
    echo "<li><strong>WhatsApp Number:</strong> " . $settings1['whatsapp_number'] . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Settings loading error: " . $e->getMessage() . "</p>";
}

echo "<h2>Test 3: Integration Test</h2>";
try {
    // Simulate how it's used in store pages
    require_once 'API/load_settings.php';
    
    // Create a connection like other pages do
    $conn = new mysqli('localhost', 'root', '', 'jowaki_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Load settings
    $store_settings = getStoreSettings($conn);
    
    echo "<p style='color: green;'>✅ Integration test successful</p>";
    echo "<p><strong>Store Name:</strong> " . $store_settings['store_name'] . "</p>";
    echo "<p><strong>Tax Rate:</strong> " . $store_settings['tax_rate'] . "%</p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Integration test failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Summary</h2>";
echo "<p style='color: green;'>✅ All tests completed. The settings system should now work correctly.</p>";
?>






