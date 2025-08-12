<?php
echo "Testing update_order_status.php API endpoint...\n";

// Test data
$test_data = [
    'order_id' => 1,
    'status' => 'processing',
    'notes' => 'Test status update',
    'updated_by' => 'admin'
];

echo "Test data: " . json_encode($test_data) . "\n";

// Make a request to the API
$url = 'http://localhost/jowaki_electrical_srvs/API/update_order_status.php';
$data = json_encode($test_data);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ],
        'content' => $data
    ]
]);

echo "Making request to: $url\n";

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "Request failed\n";
    print_r($http_response_header);
} else {
    echo "Response received:\n";
    echo $response . "\n";
    
    // Try to parse as JSON
    $json_response = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Valid JSON response\n";
        print_r($json_response);
    } else {
        echo "Invalid JSON response: " . json_last_error_msg() . "\n";
    }
}

echo "Test completed.\n";
?> 