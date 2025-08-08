<?php
require_once 'API/db_connection.php';
require_once 'API/load_settings.php';

// Load current settings
$settings = getStoreSettings($conn);

echo "<h1>Settings Test</h1>";
echo "<h2>Current Settings:</h2>";
echo "<pre>";
print_r($settings);
echo "</pre>";

// Test updating a setting
echo "<h2>Testing Settings Update...</h2>";

$test_settings = [
    'whatsapp_number' => '254721442248',
    'store_phone' => '+254721442248',
    'tax_rate' => 16.0,
    'store_name' => 'Jowaki Electrical Services'
];

// Make a POST request to update settings
$post_data = json_encode($test_settings);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $post_data
    ]
]);

$response = file_get_contents('http://localhost/jowaki_electrical_srvs/API/update_settings.php', false, $context);

echo "<h3>Update Response:</h3>";
echo "<pre>";
print_r($response);
echo "</pre>";

// Reload settings to see if they were updated
$updated_settings = getStoreSettings($conn);

echo "<h2>Updated Settings:</h2>";
echo "<pre>";
print_r($updated_settings);
echo "</pre>";

$conn->close();
?>


