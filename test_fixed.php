<?php
session_start();
require_once 'includes/load_settings.php';

// Load store settings
$store_settings = getStoreSettings(null);

// Test database connection
$conn = getValidConnection();
$db_status = $conn ? "✅ Database Connected" : "❌ Database Failed";

// Test file paths
$css_exists = file_exists('assets/css/index.css') ? "✅ CSS Found" : "❌ CSS Missing";
$js_exists = file_exists('assets/js/index.js') ? "✅ JS Found" : "❌ JS Missing";
$header_exists = file_exists('includes/header.php') ? "✅ Header Found" : "❌ Header Missing";
$logo_exists = file_exists('assets/images/Logo.jpg') ? "✅ Logo Found" : "❌ Logo Missing";
$img1_exists = file_exists('assets/images/IMG_1.jpg') ? "✅ IMG_1 Found" : "❌ IMG_1 Missing";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Jowaki Electrical Services</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 5px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Jowaki Website Test</h1>
    
    <div class="test-section">
        <h2>Database Status</h2>
        <div class="status <?php echo $conn ? 'success' : 'error'; ?>">
            <?php echo $db_status; ?>
        </div>
    </div>
    
    <div class="test-section">
        <h2>File Structure Test</h2>
        <div class="status <?php echo strpos($css_exists, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $css_exists; ?>
        </div>
        <div class="status <?php echo strpos($js_exists, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $js_exists; ?>
        </div>
        <div class="status <?php echo strpos($header_exists, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $header_exists; ?>
        </div>
        <div class="status <?php echo strpos($logo_exists, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $logo_exists; ?>
        </div>
        <div class="status <?php echo strpos($img1_exists, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $img1_exists; ?>
        </div>
    </div>
    
    <div class="test-section">
        <h2>Store Settings</h2>
        <pre><?php print_r($store_settings); ?></pre>
    </div>
    
    <div class="test-section">
        <h2>Quick Links</h2>
        <p><a href="index.php">🏠 Homepage</a></p>
        <p><a href="Store.php">🛍️ Store</a></p>
        <p><a href="Service.php">📞 Contact</a></p>
        <p><a href="login_form.php">🔐 Login</a></p>
        <p><a href="admin/">⚙️ Admin Panel</a></p>
    </div>
</body>
</html>

