<?php
// Test the toggle admin status API
$url = 'http://localhost/jowaki_electrical_srvs/API/toggle_admin_status.php';

$data = [
    'admin_id' => 2,
    'status' => 0
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Response: " . $result . "\n";
echo "Response length: " . strlen($result) . "\n";
echo "First 100 characters: " . substr($result, 0, 100) . "\n";
?>
