<?php
// Test database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection</h2>";

// Test 1: Include db_connection.php
echo "<h3>Test 1: Including db_connection.php</h3>";
try {
    require_once 'API/db_connection.php';
    echo "✅ db_connection.php included successfully<br>";
    
    if (isset($conn)) {
        echo "✅ \$conn variable is set<br>";
        
        if ($conn instanceof mysqli) {
            echo "✅ \$conn is a valid mysqli object<br>";
            
            if ($conn->ping()) {
                echo "✅ Database connection is active and responding<br>";
                
                // Test a simple query
                $result = $conn->query("SELECT 1 as test");
                if ($result) {
                    echo "✅ Database query test successful<br>";
                    $row = $result->fetch_assoc();
                    echo "Query result: " . $row['test'] . "<br>";
                } else {
                    echo "❌ Database query test failed: " . $conn->error . "<br>";
                }
            } else {
                echo "❌ Database connection ping failed<br>";
            }
        } else {
            echo "❌ \$conn is not a valid mysqli object<br>";
        }
    } else {
        echo "❌ \$conn variable is not set<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error including db_connection.php: " . $e->getMessage() . "<br>";
}

// Test 2: Test login API directly
echo "<h3>Test 2: Testing Login API</h3>";
try {
    // Simulate a POST request to login API
    $_POST['email'] = 'test@example.com';
    $_POST['password'] = 'testpassword';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Capture output
    ob_start();
    include 'API/login.php';
    $output = ob_get_clean();
    
    echo "✅ Login API executed without fatal errors<br>";
    echo "Output length: " . strlen($output) . " characters<br>";
    
    // Try to decode JSON response
    $json = json_decode($output, true);
    if ($json !== null) {
        echo "✅ API returned valid JSON<br>";
        echo "Response: " . json_encode($json, JSON_PRETTY_PRINT) . "<br>";
    } else {
        echo "⚠️ API did not return valid JSON<br>";
        echo "Raw output: " . htmlspecialchars(substr($output, 0, 500)) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing login API: " . $e->getMessage() . "<br>";
}

echo "<h3>Test Complete</h3>";
?>

