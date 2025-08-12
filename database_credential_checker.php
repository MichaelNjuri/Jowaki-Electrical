<?php
// Database Credential Checker for Hostinger
// This will help identify the exact database connection issue

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Database Credential Checker</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".test { margin: 15px 0; padding: 10px; border-radius: 5px; }";
echo ".success { background: #d4edda; color: #155724; }";
echo ".error { background: #f8d7da; color: #721c24; }";
echo ".info { background: #d1ecf1; color: #0c5460; }";
echo ".step { background: #fff3cd; color: #856404; margin: 10px 0; padding: 10px; border-left: 4px solid #ffc107; }";
echo "h1 { color: #333; text-align: center; }";
echo "h2 { color: #555; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔍 Database Credential Checker</h1>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Load current config
require_once 'config/config.php';

echo "<h2>Current Configuration</h2>";
echo "<div class='info'>";
echo "<strong>DB_HOST:</strong> " . DB_HOST . "<br>";
echo "<strong>DB_NAME:</strong> " . DB_NAME . "<br>";
echo "<strong>DB_USER:</strong> " . DB_USER . "<br>";
echo "<strong>DB_PASS:</strong> " . str_repeat('*', strlen(DB_PASS)) . "<br>";
echo "</div>";

echo "<h2>Connection Tests</h2>";

// Test 1: Basic PDO connection
echo "<h3>Test 1: Basic PDO Connection</h3>";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='success'>✅ Basic PDO connection successful!</div>";
} catch (PDOException $e) {
    echo "<div class='error'>❌ Basic PDO connection failed: " . $e->getMessage() . "</div>";
}

// Test 2: Try without database name (just to test credentials)
echo "<h3>Test 2: Test Credentials Without Database</h3>";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='success'>✅ Credentials work (can connect to MySQL server)</div>";
    
    // List available databases
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'>Available databases: " . implode(', ', $databases) . "</div>";
    
    // Check if our database exists
    if (in_array(DB_NAME, $databases)) {
        echo "<div class='success'>✅ Database '" . DB_NAME . "' exists</div>";
    } else {
        echo "<div class='error'>❌ Database '" . DB_NAME . "' does not exist</div>";
        echo "<div class='step'>";
        echo "<strong>Solution:</strong> Create the database in Hostinger hPanel or check the database name.";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Credential test failed: " . $e->getMessage() . "</div>";
}

// Test 3: Try different common Hostinger configurations
echo "<h3>Test 3: Common Hostinger Configurations</h3>";

$test_configs = [
    [
        'name' => 'Standard Hostinger',
        'host' => 'localhost',
        'user' => 'u383641303_jowaki',
        'pass' => 'jowaki@password',
        'db' => 'u383641303_jowaki_elec'
    ],
    [
        'name' => 'Alternative Hostinger',
        'host' => 'localhost',
        'user' => 'u383641303_jowaki_elec',
        'pass' => 'jowaki@password',
        'db' => 'u383641303_jowaki_elec'
    ],
    [
        'name' => 'Without Prefix',
        'host' => 'localhost',
        'user' => 'jowaki',
        'pass' => 'jowaki@password',
        'db' => 'u383641303_jowaki_elec'
    ]
];

foreach ($test_configs as $config) {
    echo "<h4>Testing: " . $config['name'] . "</h4>";
    try {
        $pdo = new PDO(
            "mysql:host=" . $config['host'] . ";dbname=" . $config['db'] . ";charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "<div class='success'>✅ " . $config['name'] . " configuration works!</div>";
        
        // Test if we can query the database
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM store_categories");
        $result = $stmt->fetch();
        echo "<div class='success'>✅ Can query store_categories: " . $result['count'] . " records</div>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ " . $config['name'] . " failed: " . $e->getMessage() . "</div>";
    }
}

echo "<h2>🔧 Troubleshooting Steps</h2>";

echo "<div class='step'>";
echo "<h3>Step 1: Verify in Hostinger hPanel</h3>";
echo "<ol>";
echo "<li>Login to Hostinger hPanel</li>";
echo "<li>Go to 'Databases' → 'MySQL Databases'</li>";
echo "<li>Check the exact database name (should start with u383641303_)</li>";
echo "<li>Check the exact username (should start with u383641303_)</li>";
echo "<li>Reset the password if needed</li>";
echo "</ol>";
echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 2: Common Issues</h3>";
echo "<ul>";
echo "<li><strong>Wrong username:</strong> Make sure it includes the u383641303_ prefix</li>";
echo "<li><strong>Wrong password:</strong> Use the MySQL password, not the database name</li>";
echo "<li><strong>Database doesn't exist:</strong> Create it in hPanel</li>";
echo "<li><strong>User permissions:</strong> Ensure user has access to the database</li>";
echo "</ul>";
echo "</div>";

echo "<div class='step'>";
echo "<h3>Step 3: Quick Fix</h3>";
echo "<p>If you need to reset the database password:</p>";
echo "<ol>";
echo "<li>In hPanel → Databases → MySQL Databases</li>";
echo "<li>Find your user (u383641303_jowaki)</li>";
echo "<li>Click 'Change Password'</li>";
echo "<li>Set a new password (e.g., 'jowaki2024')</li>";
echo "<li>Update config/config.php with the new password</li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>📞 Need Help?</h3>";
echo "<p>If none of the above configurations work:</p>";
echo "<ul>";
echo "<li>Contact Hostinger support</li>";
echo "<li>Check if your hosting plan includes MySQL databases</li>";
echo "<li>Verify your domain is properly configured</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
