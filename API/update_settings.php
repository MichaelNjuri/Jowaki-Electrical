<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Create system_settings table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTableSQL)) {
        throw new Exception('Failed to create settings table: ' . $conn->error);
    }

    // Prepare statement for upsert
    $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $updatedSettings = [];
    
    // Process each setting
    foreach ($input as $key => $value) {
        // Validate and sanitize the setting
        $allowedSettings = [
            'tax_rate', 'standard_delivery_fee', 'express_delivery_fee',
            'store_name', 'store_email', 'store_phone', 'store_address',
            'enable_mpesa', 'mpesa_business_number', 'enable_card',
            'enable_whatsapp', 'whatsapp_number', 'enable_standard_delivery',
            'standard_delivery_time', 'enable_express_delivery', 'express_delivery_time',
            'enable_pickup', 'pickup_location', 'enable_2fa',
            'enable_login_notifications', 'enable_audit_log'
        ];
        
        if (!in_array($key, $allowedSettings)) {
            continue; // Skip invalid settings
        }
        
        // Convert boolean values to string for storage
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } else {
            $value = (string) $value;
        }
        
        $stmt->bind_param('ss', $key, $value);
        
        if ($stmt->execute()) {
            $updatedSettings[$key] = $value;
        } else {
            error_log("Failed to update setting $key: " . $stmt->error);
        }
    }
    
    $stmt->close();

    // Get admin ID for logging
    $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;

    // Log the settings update (check if table exists first)
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_activity_log'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $logSQL = "INSERT INTO admin_activity_log (admin_id, action, details, ip_address) VALUES (?, 'update_settings', ?, ?)";
        $logStmt = $conn->prepare($logSQL);
        
        if ($logStmt) {
            $details = json_encode($updatedSettings);
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $logStmt->bind_param('iss', $admin_id, $details, $ip);
            $logStmt->execute();
            $logStmt->close();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Settings updated successfully',
        'updated_settings' => $updatedSettings
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update settings: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 