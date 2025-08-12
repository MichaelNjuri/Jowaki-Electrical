<?php
// Comprehensive Admin System Diagnostic
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin System Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Admin System Diagnostic</h1>
    
    <div class="section">
        <h2>📁 File System Check</h2>
        <?php
        $files_to_check = [
            'admin/admin_login_api.php' => 'Admin Login API',
            'admin/login.php' => 'Admin Login Page',
            'admin/.htaccess' => 'Admin .htaccess',
            'admin/index.php' => 'Admin Dashboard',
            'favicon.ico' => 'Favicon',
            'favicon.php' => 'Favicon Handler'
        ];
        
        foreach ($files_to_check as $file => $description) {
            if (file_exists($file)) {
                echo "<p class='success'>✅ $description exists: $file</p>";
            } else {
                echo "<p class='error'>❌ $description missing: $file</p>";
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🔐 .htaccess Configuration</h2>
        <?php
        if (file_exists('admin/.htaccess')) {
            $htaccess_content = file_get_contents('admin/.htaccess');
            echo "<p class='info'>Admin .htaccess content:</p>";
            echo "<pre>" . htmlspecialchars($htaccess_content) . "</pre>";
            
            if (strpos($htaccess_content, 'admin_login_api.php') !== false) {
                echo "<p class='success'>✅ admin_login_api.php is allowed in .htaccess</p>";
            } else {
                echo "<p class='error'>❌ admin_login_api.php is NOT allowed in .htaccess</p>";
            }
        } else {
            echo "<p class='error'>❌ Admin .htaccess file not found</p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🌐 HTTP Access Test</h2>
        <?php
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        $api_url = $base_url . '/admin/admin_login_api.php';
        
        echo "<p class='info'>Testing API URL: <code>$api_url</code></p>";
        
        // Test with cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => 'test', 'password' => 'test']));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "<p class='info'>HTTP Response Code: <strong>$http_code</strong></p>";
        
        if ($error) {
            echo "<p class='error'>❌ cURL Error: $error</p>";
        } else {
            echo "<p class='success'>✅ cURL request completed</p>";
            echo "<p class='info'>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🔧 Server Configuration</h2>
        <?php
        echo "<p class='info'>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
        echo "<p class='info'>PHP Version: " . phpversion() . "</p>";
        echo "<p class='info'>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
        echo "<p class='info'>Current Directory: " . getcwd() . "</p>";
        
        // Check if mod_rewrite is available
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            if (in_array('mod_rewrite', $modules)) {
                echo "<p class='success'>✅ mod_rewrite is enabled</p>";
            } else {
                echo "<p class='warning'>⚠️ mod_rewrite is not enabled</p>";
            }
        } else {
            echo "<p class='info'>ℹ️ Cannot check Apache modules (function not available)</p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🧪 Manual Test</h2>
        <p class='info'>Click the button below to test the admin login API directly:</p>
        <button onclick="testAPI()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Test Admin Login API
        </button>
        <div id="testResult" style="margin-top: 10px;"></div>
    </div>
    
    <script>
        async function testAPI() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<p class="info">Testing...</p>';
            
            try {
                const response = await fetch('admin/admin_login_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        username: 'test', 
                        password: 'test' 
                    })
                });
                
                const data = await response.json();
                
                resultDiv.innerHTML = `
                    <p class="success">✅ API Response Received</p>
                    <p class="info">Status: ${response.status}</p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                resultDiv.innerHTML = `
                    <p class="error">❌ API Test Failed</p>
                    <p class="error">Error: ${error.message}</p>
                `;
            }
        }
    </script>
</body>
</html>
