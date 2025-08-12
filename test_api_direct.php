<?php
// Direct test of admin login API
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Testing Admin Login API</h1>";

// Test if the API file exists
$api_file = 'admin/admin_login_api.php';
if (file_exists($api_file)) {
    echo "<p>✅ API file exists: $api_file</p>";
} else {
    echo "<p>❌ API file not found: $api_file</p>";
}

// Test if we can access it via HTTP
$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/admin/admin_login_api.php';
echo "<p>Testing URL: $url</p>";

// Make a test request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => 'test', 'password' => 'test']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p>HTTP Response Code: $http_code</p>";
if ($error) {
    echo "<p>❌ cURL Error: $error</p>";
} else {
    echo "<p>✅ cURL request successful</p>";
    echo "<p>Response: " . htmlspecialchars($response) . "</p>";
}

// Test .htaccess file
$htaccess_file = 'admin/.htaccess';
if (file_exists($htaccess_file)) {
    echo "<p>✅ .htaccess file exists</p>";
    $htaccess_content = file_get_contents($htaccess_file);
    if (strpos($htaccess_content, 'admin_login_api.php') !== false) {
        echo "<p>✅ admin_login_api.php is allowed in .htaccess</p>";
    } else {
        echo "<p>❌ admin_login_api.php is NOT allowed in .htaccess</p>";
    }
} else {
    echo "<p>❌ .htaccess file not found</p>";
}
?>
