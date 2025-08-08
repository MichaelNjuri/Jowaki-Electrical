<?php
// Test the fixed settings loading
require_once 'API/load_settings.php';

echo "<h1>Settings Test - Fixed Version</h1>";

try {
    // Load current settings
    $settings = getStoreSettings($conn);
    
    echo "<h2>Current Settings:</h2>";
    echo "<pre>";
    print_r($settings);
    echo "</pre>";
    
    echo "<h2>Key Settings:</h2>";
    echo "<ul>";
    echo "<li><strong>Tax Rate:</strong> " . $settings['tax_rate'] . "%</li>";
    echo "<li><strong>Store Phone:</strong> " . $settings['store_phone'] . "</li>";
    echo "<li><strong>WhatsApp Number:</strong> " . $settings['whatsapp_number'] . "</li>";
    echo "<li><strong>Standard Delivery Fee:</strong> KSh " . $settings['standard_delivery_fee'] . "</li>";
    echo "<li><strong>Express Delivery Fee:</strong> KSh " . $settings['express_delivery_fee'] . "</li>";
    echo "</ul>";
    
    echo "<h2>Status:</h2>";
    echo "<p style='color: green;'>✅ Settings loaded successfully!</p>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>❌ Error loading settings: " . $e->getMessage() . "</p>";
}

if ($conn) {
    $conn->close();
}
?>


