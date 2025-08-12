<?php
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Simple API test successful',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION
]);
?>

