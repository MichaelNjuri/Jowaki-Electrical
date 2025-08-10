<?php
// Fix Admin Login System
echo "<h1>🔧 Admin Login System Fix</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    button { padding: 10px 20px; margin: 5px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px; }
    button:hover { background: #0056b3; }
</style>";

// Step 1: Check and fix database connection
echo "<div class='section'>";
echo "<h2>📊 Step 1: Database Connection Check</h2>";

require_once 'API/db_connection.php';
$conn = getConnection();

if ($conn) {
    echo "<div class='success'>✅ Database connection successful!</div>";
    
    // Check if admin tables exist
    $tables = ['admin_users', 'admin_activity_log', 'admin_roles'];
    $missing_tables = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows === 0) {
            $missing_tables[] = $table;
        }
    }
    
    if (empty($missing_tables)) {
        echo "<div class='success'>✅ All admin tables exist!</div>";
    } else {
        echo "<div class='warning'>⚠️ Missing tables: " . implode(', ', $missing_tables) . "</div>";
        echo "<button onclick='createTables()'>Create Missing Tables</button>";
    }
    
    // Check if admin user exists
    $result = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo "<div class='success'>✅ Admin users exist ({$row['count']} active)</div>";
    } else {
        echo "<div class='warning'>⚠️ No active admin users found</div>";
        echo "<button onclick='createAdminUser()'>Create Default Admin</button>";
    }
    
} else {
    echo "<div class='error'>❌ Database connection failed!</div>";
    echo "<div class='info'>Please check your XAMPP MySQL service and database configuration.</div>";
}
echo "</div>";

// Step 2: Fix admin login API
echo "<div class='section'>";
echo "<h2>🔐 Step 2: Admin Login API Fix</h2>";

// Create a fixed version of admin_login.php
$admin_login_fixed = '<?php
// Fixed Admin Login API
session_start();

// Prevent output before headers
if (ob_get_level()) ob_end_clean();

require_once \'db_connection.php\';
require_once \'check_auth.php\';

header(\'Content-Type: application/json\');
header(\'Access-Control-Allow-Origin: *\');
header(\'Access-Control-Allow-Methods: POST, OPTIONS\');
header(\'Access-Control-Allow-Headers: Content-Type\');

// Handle preflight requests
if ($_SERVER[\'REQUEST_METHOD\'] === \'OPTIONS\') {
    http_response_code(200);
    exit;
}

if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {
    $conn = getConnection();
    if (!$conn) {
        http_response_code(500);
        echo json_encode([\'success\' => false, \'message\' => \'Database connection failed\']);
        exit;
    }

    try {
        $data = json_decode(file_get_contents(\'php://input\'), true);
        
        if (!$data) {
            $data = $_POST;
        }

        $username = $data[\'username\'] ?? \'\';
        $password = $data[\'password\'] ?? \'\';

        if (empty($username) || empty($password)) {
            echo json_encode([\'success\' => false, \'message\' => \'Username and password are required\']);
            exit;
        }

        // Check if admin_users table exists
        $check_table = $conn->query("SHOW TABLES LIKE \'admin_users\'");
        if ($check_table->num_rows === 0) {
            echo json_encode([
                \'success\' => false, 
                \'message\' => \'Admin system not initialized\',
                \'redirect\' => \'../initialize_admin_system.php\'
            ]);
            exit;
        }

        // Get admin user
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = TRUE");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode([\'success\' => false, \'message\' => \'Invalid username or password\']);
            $stmt->close();
            $conn->close();
            exit;
        }

        $admin = $result->fetch_assoc();
        $stmt->close();

        // Verify password
        if (!password_verify($password, $admin[\'password_hash\'])) {
            echo json_encode([\'success\' => false, \'message\' => \'Invalid username or password\']);
            $conn->close();
            exit;
        }

        // Set session variables
        $_SESSION[\'user_id\'] = $admin[\'id\'];
        $_SESSION[\'username\'] = $admin[\'username\'];
        $_SESSION[\'email\'] = $admin[\'email\'];
        $_SESSION[\'first_name\'] = $admin[\'first_name\'];
        $_SESSION[\'last_name\'] = $admin[\'last_name\'];
        $_SESSION[\'is_admin\'] = true;
        $_SESSION[\'permissions\'] = [
            \'dashboard\' => true,
            \'products\' => true,
            \'orders\' => true,
            \'customers\' => true,
            \'categories\' => true,
            \'analytics\' => true,
            \'settings\' => true,
            \'admin_management\' => true,
            \'create_admins\' => true,
            \'delete_admins\' => true,
            \'view_logs\' => true,
            \'backup\' => true
        ];

        // Update last login
        $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $update_stmt->bind_param("i", $admin[\'id\']);
        $update_stmt->execute();
        $update_stmt->close();

        // Log login activity (only if table exists)
        try {
            logAdminActivity(\'Login\', \'Admin logged in successfully\');
        } catch (Exception $e) {
            // Ignore logging errors
        }

        $conn->close();

        echo json_encode([
            \'success\' => true,
            \'message\' => \'Login successful\',
            \'admin\' => [
                \'id\' => $admin[\'id\'],
                \'username\' => $admin[\'username\'],
                \'first_name\' => $admin[\'first_name\'],
                \'last_name\' => $admin[\'last_name\'],
                \'full_name\' => $admin[\'first_name\'] . \' \' . $admin[\'last_name\'],
                \'permissions\' => $_SESSION[\'permissions\']
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            \'success\' => false,
            \'message\' => \'Login error: \' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([\'success\' => false, \'message\' => \'Method not allowed\']);
}
?>';

// Write the fixed version
if (file_put_contents('API/admin_login_fixed.php', $admin_login_fixed)) {
    echo "<div class='success'>✅ Created fixed admin login API (admin_login_fixed.php)</div>";
    echo "<div class='info'>The fixed version includes:</div>";
    echo "<ul>";
    echo "<li>Proper header handling</li>";
    echo "<li>CORS headers for cross-origin requests</li>";
    echo "<li>Better error handling</li>";
    echo "<li>Preflight request support</li>";
    echo "</ul>";
} else {
    echo "<div class='error'>❌ Failed to create fixed admin login API</div>";
}

echo "</div>";

// Step 3: Create test admin user
echo "<div class='section'>";
echo "<h2>👤 Step 3: Create Test Admin User</h2>";

if ($conn) {
    // Check if admin user exists
    $result = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo "<div class='success'>✅ Admin user 'admin' already exists</div>";
    } else {
        // Create admin user
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password_hash, first_name, last_name, is_active, is_super_admin, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $username = 'admin';
        $email = 'admin@jowaki.com';
        $first_name = 'Admin';
        $last_name = 'User';
        $is_active = 1;
        $is_super_admin = 1;
        
        $stmt->bind_param("sssssii", $username, $email, $password_hash, $first_name, $last_name, $is_active, $is_super_admin);
        
        if ($stmt->execute()) {
            echo "<div class='success'>✅ Created admin user successfully!</div>";
            echo "<div class='info'>Username: admin</div>";
            echo "<div class='info'>Password: admin123</div>";
        } else {
            echo "<div class='error'>❌ Failed to create admin user: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
echo "</div>";

// Step 4: Test the fixed API
echo "<div class='section'>";
echo "<h2>🧪 Step 4: Test Fixed API</h2>";
echo "<button onclick='testFixedAPI()'>Test Fixed Admin Login API</button>";
echo "<div id='test-results'></div>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🎯 Next Steps</h2>";
echo "<ol>";
echo "<li>If the database connection failed, start MySQL in XAMPP Control Panel</li>";
echo "<li>If tables are missing, click 'Create Missing Tables'</li>";
echo "<li>If no admin users exist, click 'Create Default Admin'</li>";
echo "<li>Test the fixed API using the button above</li>";
echo "<li>Update your admin login form to use the fixed API</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>

<script>
function createTables() {
    window.location.href = 'API/create_admin_tables.php';
}

function createAdminUser() {
    // Create admin user via AJAX
    fetch('API/create_admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: 'admin',
            email: 'admin@jowaki.com',
            password: 'admin123',
            first_name: 'Admin',
            last_name: 'User',
            is_super_admin: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Admin user created successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function testFixedAPI() {
    const resultsDiv = document.getElementById('test-results');
    resultsDiv.innerHTML = '<div class="info">Testing fixed admin login API...</div>';
    
    fetch('API/admin_login_fixed.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: 'admin',
            password: 'admin123'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultsDiv.innerHTML = '<div class="success">✅ Fixed API works! Login successful.</div>';
        } else {
            resultsDiv.innerHTML = '<div class="error">❌ Fixed API error: ' + data.message + '</div>';
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = '<div class="error">❌ Network error: ' + error.message + '</div>';
    });
}
</script>

