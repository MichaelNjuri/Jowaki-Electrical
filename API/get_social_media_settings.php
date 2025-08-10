<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = getConnection();
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    try {
        // Check if system_settings table exists
        $check_table = $conn->query("SHOW TABLES LIKE 'system_settings'");
        if ($check_table->num_rows === 0) {
            // Return default settings if table doesn't exist
            $default_settings = [
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
            
            echo json_encode([
                'success' => true,
                'settings' => $default_settings
            ]);
            exit;
        }

        // Get social media settings from database
        $social_keys = [
            'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url', 
            'youtube_url', 'tiktok_url', 'enable_facebook', 'enable_twitter', 
            'enable_instagram', 'enable_linkedin', 'enable_youtube', 'enable_tiktok'
        ];
        
        $placeholders = str_repeat('?,', count($social_keys) - 1) . '?';
        $sql = "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ($placeholders)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($social_keys)), ...$social_keys);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $settings = [
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
        
        while ($row = $result->fetch_assoc()) {
            $key = $row['setting_key'];
            $value = $row['setting_value'];
            
            // Convert boolean values
            if (strpos($key, 'enable_') === 0) {
                $settings[$key] = (bool)$value;
            } else {
                $settings[$key] = $value;
            }
        }
        
        $stmt->close();
        $conn->close();

        echo json_encode([
            'success' => true,
            'settings' => $settings
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error retrieving settings: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
