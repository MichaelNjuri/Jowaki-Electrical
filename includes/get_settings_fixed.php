<?php
session_start();
if (ob_get_level()) ob_end_clean();

require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

$conn = null;

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if settings table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'settings'");
    if ($tableExists->num_rows === 0) {
        // Return default settings if table doesn't exist
        echo json_encode([
            'success' => true,
            'settings' => [
                'store_name' => 'Jowaki Electrical Services',
                'store_email' => 'info@jowaki.com',
                'store_phone' => '+254 700 000 000',
                'store_address' => 'Nairobi, Kenya',
                'currency' => 'KSh',
                'tax_rate' => '16',
                'delivery_fee' => '500',
                'free_delivery_threshold' => '5000',
                'order_notification_email' => 'orders@jowaki.com',
                'support_email' => 'support@jowaki.com',
                'business_hours' => 'Monday - Friday: 8:00 AM - 6:00 PM',
                'payment_methods' => 'M-Pesa, Bank Transfer, Cash',
                'return_policy' => '7 days return policy',
                'privacy_policy' => 'Your privacy is important to us',
                'terms_of_service' => 'Terms and conditions apply'
            ]
        ]);
        exit;
    }

    // Fetch settings from database
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
    $stmt->execute();
    $result = $stmt->get_result();
    $settings = [];
    
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Merge with defaults for missing settings
    $defaultSettings = [
        'store_name' => 'Jowaki Electrical Services',
        'store_email' => 'info@jowaki.com',
        'store_phone' => '+254 700 000 000',
        'store_address' => 'Nairobi, Kenya',
        'currency' => 'KSh',
        'tax_rate' => '16',
        'delivery_fee' => '500',
        'free_delivery_threshold' => '5000',
        'order_notification_email' => 'orders@jowaki.com',
        'support_email' => 'support@jowaki.com',
        'business_hours' => 'Monday - Friday: 8:00 AM - 6:00 PM',
        'payment_methods' => 'M-Pesa, Bank Transfer, Cash',
        'return_policy' => '7 days return policy',
        'privacy_policy' => 'Your privacy is important to us',
        'terms_of_service' => 'Terms and conditions apply'
    ];
    
    $finalSettings = array_merge($defaultSettings, $settings);
    
    echo json_encode([
        'success' => true,
        'settings' => $finalSettings
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Settings fetch error: ' . $e->getMessage()
    ]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>





