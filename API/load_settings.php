<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to format WhatsApp number to international format
function formatWhatsAppNumber($number) {
    // Remove any non-numeric characters except +
    $cleanNumber = preg_replace('/[^\d+]/', '', $number);
    
    // Handle local format (07xxxxxxxx) - convert to international format
    if (preg_match('/^07\d{8}$/', $cleanNumber)) {
        $cleanNumber = '254' . substr($cleanNumber, 1);
    }
    
    // Remove + if present and ensure it starts with country code
    $cleanNumber = ltrim($cleanNumber, '+');
    
    return $cleanNumber;
}

// Function to get a valid database connection
function getValidConnection($existingConn = null) {
    // If we have a valid existing connection, check if it's still alive
    if ($existingConn && !$existingConn->connect_error) {
        try {
            // Try to ping the connection to see if it's still valid
            if ($existingConn->ping()) {
                return $existingConn;
            }
        } catch (Exception $e) {
            // Connection is dead, will create new one
        } catch (Error $e) {
            // Connection is closed or invalid, will create new one
        }
    }
    
    // Otherwise, create a new connection
    try {
        $newConn = new mysqli('localhost', 'root', '', 'jowaki_db');
        if ($newConn->connect_error) {
            return null; // Return null if connection fails
        }
        $newConn->set_charset("utf8mb4");
        return $newConn;
    } catch (Exception $e) {
        return null; // Return null if connection fails
    }
}

// Function to get settings with defaults
function getStoreSettings($conn) {
    $settings = [
        'tax_rate' => 16.0,
        'standard_delivery_fee' => 0.0,
        'express_delivery_fee' => 500.0,
        'store_name' => 'Jowaki Electrical Services',
        'store_email' => 'info@jowaki.com',
        'store_phone' => '+254721442248',
        'whatsapp_number' => '254721442248',
        'whatsapp_message' => 'Hello Jowaki Electrical, I would like to inquire about your products.',
        'store_address' => '',
        'enable_mpesa' => true,
        'mpesa_business_number' => '254721442248',
        'enable_card' => true,
        'enable_whatsapp' => true,
        'enable_standard_delivery' => true,
        'standard_delivery_time' => '3-5 business days',
        'enable_express_delivery' => true,
        'express_delivery_time' => '1-2 business days',
        'enable_pickup' => true,
        'pickup_location' => '',
        'enable_2fa' => false,
        'enable_login_notifications' => false,
        'enable_audit_log' => true,
        // Social Media Settings
        'facebook_url' => 'https://www.facebook.com/JowakiElectricalServicesLTD',
        'twitter_url' => '',
        'instagram_url' => '',
        'linkedin_url' => '',
        'youtube_url' => '',
        'tiktok_url' => '',
        'enable_facebook' => true,
        'enable_twitter' => false,
        'enable_instagram' => false,
        'enable_linkedin' => false,
        'enable_youtube' => false,
        'enable_tiktok' => false
    ];

    // Get a valid connection
    $useConnection = getValidConnection($conn);
    if (!$useConnection) {
        return $settings; // Return defaults if we can't get a connection
    }

    $shouldCloseConnection = ($useConnection !== $conn); // Only close if we created a new one

    try {
        // Try to get settings from database if table exists
        $sql = "SHOW TABLES LIKE 'system_settings'";
        $result = $useConnection->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $sql = "SELECT setting_key, setting_value FROM system_settings";
            $result = $useConnection->query($sql);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $key = $row['setting_key'];
                    $value = $row['setting_value'];
                    
                    // Convert string values to appropriate types
                    if (in_array($key, ['tax_rate', 'standard_delivery_fee', 'express_delivery_fee'])) {
                        $settings[$key] = (float) $value;
                    } elseif (in_array($key, ['enable_mpesa', 'enable_card', 'enable_whatsapp', 'enable_standard_delivery', 'enable_express_delivery', 'enable_pickup', 'enable_2fa', 'enable_login_notifications', 'enable_audit_log'])) {
                        $settings[$key] = (bool) $value;
                    } else {
                        // Format WhatsApp number if it's the whatsapp_number setting
                        if ($key === 'whatsapp_number') {
                            $settings[$key] = formatWhatsAppNumber($value);
                        } else {
                            $settings[$key] = $value;
                        }
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Error in getStoreSettings: " . $e->getMessage());
        // If any database error occurs, return default settings
    } finally {
        // Close the connection if we created a new one
        if ($shouldCloseConnection && $useConnection) {
            try {
                $useConnection->close();
            } catch (Exception $e) {
                // Ignore errors when closing connection
            }
        }
    }

    return $settings;
}

// If called directly as API
if (basename($_SERVER['PHP_SELF']) == 'load_settings.php') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    try {
        $settings = getStoreSettings($conn);
        echo json_encode([
            'success' => true,
            'settings' => $settings
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to retrieve settings: ' . $e->getMessage()
        ]);
    }
    
    if ($conn) {
        $conn->close();
    }
}
?>
