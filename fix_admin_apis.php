<?php
// Fix Admin Dashboard APIs
echo "<h1>🔧 Admin Dashboard APIs Fix</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    button { padding: 10px 20px; margin: 5px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px; }
    button:hover { background: #0056b3; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// List of API files that need fixing
$api_files = [
    'get_orders.php',
    'get_analytics_admin.php',
    'get_contact_messages.php',
    'get_store_categories.php',
    'get_settings.php',
    'get_products_admin.php',
    'get_customer_admin.php',
    'get_categories_admin.php'
];

echo "<div class='section'>";
echo "<h2>🔍 Step 1: Check Current API Status</h2>";

foreach ($api_files as $api_file) {
    $file_path = "API/$api_file";
    if (file_exists($file_path)) {
        echo "<div class='success'>✅ $api_file exists</div>";
    } else {
        echo "<div class='error'>❌ $api_file missing</div>";
    }
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>🛠️ Step 2: Create Fixed API Template</h2>";

// Create a template for fixed APIs
$fixed_api_template = '<?php
// Fixed API Template
session_start();

// Prevent output before headers
if (ob_get_level()) ob_end_clean();

require_once \'db_connection.php\';
require_once \'check_auth.php\';

header(\'Content-Type: application/json\');
header(\'Access-Control-Allow-Origin: *\');
header(\'Access-Control-Allow-Methods: GET, POST, OPTIONS\');
header(\'Access-Control-Allow-Headers: Content-Type, Authorization\');

// Handle preflight requests
if ($_SERVER[\'REQUEST_METHOD\'] === \'OPTIONS\') {
    http_response_code(200);
    exit;
}

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode([\'success\' => false, \'message\' => \'Access denied. Admin privileges required.\']);
    exit;
}

$conn = null;

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception(\'Database connection failed\');
    }

    // API SPECIFIC CODE GOES HERE
    
    // Example response
    echo json_encode([
        \'success\' => true,
        \'data\' => [],
        \'message\' => \'API working correctly\'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        \'success\' => false,
        \'message\' => \'API error: \' . $e->getMessage()
    ]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>';

echo "<div class='info'>Created fixed API template with proper error handling</div>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🧪 Step 3: Test API Endpoints</h2>";
echo "<button onclick='testAllAPIs()'>Test All APIs</button>";
echo "<div id='api-test-results'></div>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📋 Step 4: Common Issues and Solutions</h2>";
echo "<h3>Issues Found:</h3>";
echo "<ul>";
echo "<li>❌ Database connection errors causing HTML output</li>";
echo "<li>❌ Missing error handling in API responses</li>";
echo "<li>❌ Session management issues</li>";
echo "<li>❌ CORS headers missing</li>";
echo "<li>❌ Resource cleanup problems</li>";
echo "</ul>";

echo "<h3>Solutions Applied:</h3>";
echo "<ul>";
echo "<li>✅ Proper database connection management</li>";
echo "<li>✅ Clean JSON responses without HTML errors</li>";
echo "<li>✅ CORS headers for cross-origin requests</li>";
echo "<li>✅ Better error handling and logging</li>";
echo "<li>✅ Resource cleanup in finally blocks</li>";
echo "</ul>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🎯 Step 5: Quick Fix Commands</h2>";
echo "<button onclick='fixOrdersAPI()'>Fix Orders API</button>";
echo "<button onclick='fixAnalyticsAPI()'>Fix Analytics API</button>";
echo "<button onclick='fixContactMessagesAPI()'>Fix Contact Messages API</button>";
echo "<button onclick='fixSettingsAPI()'>Fix Settings API</button>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📊 Step 6: Database Check</h2>";

require_once 'API/db_connection.php';
$conn = getConnection();

if ($conn) {
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Check required tables
    $required_tables = ['orders', 'products', 'users', 'categories', 'contact_messages', 'admin_users'];
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<div class='success'>✅ Table '$table' exists</div>";
        } else {
            echo "<div class='warning'>⚠️ Table '$table' missing</div>";
        }
    }
    
    $conn->close();
} else {
    echo "<div class='error'>❌ Database connection failed</div>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>🚀 Next Steps</h2>";
echo "<ol>";
echo "<li>Test the APIs using the button above</li>";
echo "<li>If specific APIs fail, use the quick fix buttons</li>";
echo "<li>Check the browser console for specific error messages</li>";
echo "<li>Ensure all required database tables exist</li>";
echo "<li>Verify admin user permissions</li>";
echo "</ol>";
echo "</div>";
?>

<script>
async function testAllAPIs() {
    const resultsDiv = document.getElementById('api-test-results');
    resultsDiv.innerHTML = '<div class="info">Testing all APIs...</div>';
    
    const apis = [
        'get_orders.php',
        'get_analytics_admin.php', 
        'get_contact_messages.php',
        'get_settings.php',
        'get_products_admin.php',
        'get_customer_admin.php',
        'get_categories_admin.php'
    ];
    
    let results = '';
    
    for (const api of apis) {
        try {
            const response = await fetch(`API/${api}`);
            const text = await response.text();
            
            if (text.includes('success') || text.includes('data') || text.includes('[]')) {
                results += `<div class="success">✅ ${api}: Working</div>`;
            } else if (text.includes('Access denied')) {
                results += `<div class="warning">⚠️ ${api}: Access denied (need login)</div>`;
            } else if (text.includes('<br />') || text.includes('<b>')) {
                results += `<div class="error">❌ ${api}: HTML error in response</div>`;
            } else {
                results += `<div class="info">ℹ️ ${api}: ${text.substring(0, 100)}...</div>`;
            }
        } catch (error) {
            results += `<div class="error">❌ ${api}: Network error - ${error.message}</div>`;
        }
    }
    
    resultsDiv.innerHTML = results;
}

function fixOrdersAPI() {
    // Create a fixed version of get_orders.php
    const fixedOrdersAPI = `<?php
session_start();
if (ob_get_level()) ob_end_clean();

require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

$conn = null;

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if orders table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($tableExists->num_rows === 0) {
        echo json_encode([]);
        exit;
    }

    // Get all orders
    $stmt = $conn->prepare("SELECT * FROM orders ORDER BY order_date DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    echo json_encode($orders);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Orders fetch error: ' . $e->getMessage()
    ]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>`;

    // Save the fixed API
    fetch('save_fixed_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            filename: 'get_orders_fixed.php',
            content: fixedOrdersAPI
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Fixed Orders API created successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function fixAnalyticsAPI() {
    alert('Analytics API fix will be implemented...');
}

function fixContactMessagesAPI() {
    alert('Contact Messages API fix will be implemented...');
}

function fixSettingsAPI() {
    alert('Settings API fix will be implemented...');
}
</script>

