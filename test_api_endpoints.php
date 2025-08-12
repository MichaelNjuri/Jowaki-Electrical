<?php
// API Endpoints Test Script
echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>API Endpoints Test - Jowaki Store</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo ".warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo ".info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 5px 0; }";
echo "h1 { color: #333; text-align: center; }";
echo "h2 { color: #555; }";
echo ".endpoint { background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 5px; border-left: 4px solid #007bff; }";
echo ".response { background: #f1f3f4; padding: 10px; margin: 5px 0; border-radius: 3px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔍 API Endpoints Test - Jowaki Store</h1>";
echo "<p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Critical API endpoints to test
$endpoints = [
    'get_categories.php' => [
        'name' => 'Get Categories',
        'method' => 'GET',
        'description' => 'Retrieves product categories'
    ],
    'get_products.php' => [
        'name' => 'Get Products',
        'method' => 'GET',
        'description' => 'Retrieves product listings'
    ],
    'get_featured_products.php' => [
        'name' => 'Get Featured Products',
        'method' => 'GET',
        'description' => 'Retrieves featured products'
    ],
    'db_connection.php' => [
        'name' => 'Database Connection',
        'method' => 'GET',
        'description' => 'Tests database connectivity'
    ]
];

echo "<h2>📋 Testing API Endpoints</h2>";

foreach ($endpoints as $endpoint => $info) {
    echo "<div class='endpoint'>";
    echo "<h3>🔗 {$info['name']}</h3>";
    echo "<p><strong>File:</strong> api/{$endpoint}</p>";
    echo "<p><strong>Method:</strong> {$info['method']}</p>";
    echo "<p><strong>Description:</strong> {$info['description']}</p>";
    
    // Check if file exists
    $file_path = "api/{$endpoint}";
    if (file_exists($file_path)) {
        echo "<div class='success'>✅ File exists: {$file_path}</div>";
        
        // Test the endpoint
        try {
            // Capture output
            ob_start();
            include $file_path;
            $output = ob_get_clean();
            
            // Check if it's valid JSON
            $json_data = json_decode($output, true);
            if ($json_data !== null) {
                echo "<div class='success'>✅ Valid JSON response</div>";
                echo "<div class='response'>";
                echo "<strong>Response:</strong><br>";
                echo htmlspecialchars(substr($output, 0, 500));
                if (strlen($output) > 500) {
                    echo "... (truncated)";
                }
                echo "</div>";
            } else {
                echo "<div class='warning'>⚠️ Response is not valid JSON</div>";
                echo "<div class='response'>";
                echo "<strong>Response:</strong><br>";
                echo htmlspecialchars(substr($output, 0, 500));
                if (strlen($output) > 500) {
                    echo "... (truncated)";
                }
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        echo "<div class='error'>❌ File missing: {$file_path}</div>";
    }
    echo "</div>";
}

// Test database connection specifically
echo "<h2>🗄️ Database Connection Test</h2>";
try {
    require_once 'config/config.php';
    $pdo = getDbConnection();
    
    if ($pdo) {
        echo "<div class='success'>✅ Database connection successful!</div>";
        
        // Test basic queries
        $tables_to_test = [
            'store_categories' => 'SELECT COUNT(*) as count FROM store_categories',
            'products' => 'SELECT COUNT(*) as count FROM products',
            'admin_users' => 'SELECT COUNT(*) as count FROM admin_users'
        ];
        
        foreach ($tables_to_test as $table => $query) {
            try {
                $stmt = $pdo->query($query);
                $result = $stmt->fetch();
                echo "<div class='success'>✅ {$table} table: " . $result['count'] . " records</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ {$table} table error: " . $e->getMessage() . "</div>";
            }
        }
    } else {
        echo "<div class='error'>❌ Database connection failed</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Database test error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test URL accessibility
echo "<h2>🌐 URL Accessibility Test</h2>";
$base_url = 'https://jowakielectrical.com';
$urls_to_test = [
    '/api/get_categories.php',
    '/api/get_products.php',
    '/api/get_featured_products.php'
];

foreach ($urls_to_test as $url) {
    $full_url = $base_url . $url;
    echo "<div class='endpoint'>";
    echo "<h3>🔗 Testing: {$url}</h3>";
    echo "<p><strong>Full URL:</strong> <a href='{$full_url}' target='_blank'>{$full_url}</a></p>";
    
    // Test with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $full_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<div class='error'>❌ cURL Error: " . htmlspecialchars($error) . "</div>";
    } elseif ($http_code == 200) {
        echo "<div class='success'>✅ HTTP 200 - URL accessible</div>";
        
        // Check if response is JSON
        $json_data = json_decode($response, true);
        if ($json_data !== null) {
            echo "<div class='success'>✅ Valid JSON response</div>";
        } else {
            echo "<div class='warning'>⚠️ Response is not valid JSON</div>";
        }
        
        echo "<div class='response'>";
        echo "<strong>Response Preview:</strong><br>";
        echo htmlspecialchars(substr($response, 0, 300));
        if (strlen($response) > 300) {
            echo "... (truncated)";
        }
        echo "</div>";
    } elseif ($http_code == 404) {
        echo "<div class='error'>❌ HTTP 404 - File not found</div>";
    } else {
        echo "<div class='error'>❌ HTTP {$http_code} - Error</div>";
    }
    echo "</div>";
}

echo "<h2>🔧 Troubleshooting</h2>";
echo "<div class='info'>";
echo "<h3>If APIs return 404:</h3>";
echo "<ol>";
echo "<li>Check if files are uploaded to Hostinger</li>";
echo "<li>Verify file permissions (should be 644)</li>";
echo "<li>Check if .htaccess is blocking access</li>";
echo "<li>Ensure database connection is working</li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>If APIs return 500:</h3>";
echo "<ol>";
echo "<li>Check PHP error logs</li>";
echo "<li>Verify database credentials</li>";
echo "<li>Check if required tables exist</li>";
echo "<li>Test database connection manually</li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>Quick Links:</h3>";
echo "<p><a href='/test_db_connection.php' target='_blank'>🧪 Database Connection Test</a></p>";
echo "<p><a href='/diagnostic_troubleshooting.php' target='_blank'>🔍 System Diagnostic</a></p>";
echo "<p><a href='/admin/setup_admin.php' target='_blank'>🔧 Admin Setup</a></p>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
