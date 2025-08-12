<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

try {
    // Default settings
    $settings = [
        'tax_rate' => 16.0,
        'standard_delivery_fee' => 0.0,
        'express_delivery_fee' => 500.0,
        'enable_mpesa' => true,
        'enable_card' => true,
        'enable_whatsapp' => true,
        'whatsapp_number' => '254721442248',
        'enable_standard_delivery' => true,
        'enable_express_delivery' => true,
        'enable_pickup' => true,
        'standard_delivery_time' => '3-5 business days',
        'express_delivery_time' => '1-2 business days',
        'pickup_location' => ''
    ];

    // Try to get settings from database if table exists
    $sql = "SHOW TABLES LIKE 'system_settings'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $sql = "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN (
            'tax_rate', 'standard_delivery_fee', 'express_delivery_fee', 'enable_mpesa', 
            'enable_card', 'enable_whatsapp', 'whatsapp_number', 'enable_standard_delivery',
            'enable_express_delivery', 'enable_pickup', 'standard_delivery_time',
            'express_delivery_time', 'pickup_location'
        )";
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $key = $row['setting_key'];
                $value = $row['setting_value'];
                
                // Convert string values to appropriate types
                if (in_array($key, ['tax_rate', 'standard_delivery_fee', 'express_delivery_fee'])) {
                    $settings[$key] = (float) $value;
                } elseif (in_array($key, ['enable_mpesa', 'enable_card', 'enable_whatsapp', 'enable_standard_delivery', 'enable_express_delivery', 'enable_pickup'])) {
                    $settings[$key] = (bool) $value;
                } else {
                    $settings[$key] = $value;
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'settings' => $settings
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to retrieve checkout settings: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 