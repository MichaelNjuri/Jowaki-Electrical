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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    try {
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }

        // Social media settings to update
        $social_settings = [
            'facebook_url' => $data['facebook_url'] ?? '',
            'twitter_url' => $data['twitter_url'] ?? '',
            'instagram_url' => $data['instagram_url'] ?? '',
            'linkedin_url' => $data['linkedin_url'] ?? '',
            'youtube_url' => $data['youtube_url'] ?? '',
            'tiktok_url' => $data['tiktok_url'] ?? '',
            'enable_facebook' => isset($data['enable_facebook']) ? 1 : 0,
            'enable_twitter' => isset($data['enable_twitter']) ? 1 : 0,
            'enable_instagram' => isset($data['enable_instagram']) ? 1 : 0,
            'enable_linkedin' => isset($data['enable_linkedin']) ? 1 : 0,
            'enable_youtube' => isset($data['enable_youtube']) ? 1 : 0,
            'enable_tiktok' => isset($data['enable_tiktok']) ? 1 : 0
        ];

        // Check if system_settings table exists
        $check_table = $conn->query("SHOW TABLES LIKE 'system_settings'");
        if ($check_table->num_rows === 0) {
            // Create the table if it doesn't exist
            $create_table = "CREATE TABLE system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $conn->query($create_table);
        }

        // Update each setting
        foreach ($social_settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) 
                                   VALUES (?, ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("sss", $key, $value, $value);
            $stmt->execute();
            $stmt->close();
        }

        // Log the update
        $admin_id = $_SESSION['user_id'] ?? 0;
        $log_sql = "INSERT INTO admin_activity_log (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $action = "Updated social media settings";
        $details = "Updated social media links and visibility settings";
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log_stmt->bind_param("isss", $admin_id, $action, $details, $ip);
        $log_stmt->execute();
        $log_stmt->close();

        $conn->close();

        echo json_encode([
            'success' => true, 
            'message' => 'Social media settings updated successfully',
            'settings' => $social_settings
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error updating settings: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
