<?php
// Test debug version
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up the GET parameters
$_GET['period'] = 'day';

// Capture the output without any previous output
ob_start();
include 'API/sales_reports_debug.php';
$output = ob_get_clean();

echo "<h1>Testing sales_reports_debug.php</h1>";
echo "<h2>Output:</h2>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";
?> 