<?php
/**
 * Authentication Helper Functions
 * Include this file in pages that need authentication
 */

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    session_start();
}

/**
 * Check if user is logged in and session is valid
 */
function requireLogin($redirect_url = '/jowaki_electrical_srvs/login.html') {
    if (!isLoggedIn()) {
        $error_message = "Please log in to access this page";
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            $error_message = "Your session has expired. Please log in again";
        }
        
        // Clear session
        $_SESSION = array();
        session_destroy();
        
        header("Location: $redirect_url?error=" . urlencode($error_message));
        exit;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    // Check if session variables exist
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Check if user is marked as logged in
    if ($_SESSION['logged_in'] !== true) {
        return false;
    }
    
    // Check session timeout (30 minutes)
    $session_timeout = 30 * 60;
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        return false;
    }
    
    return true;
}

/**
 * Get current user data from session
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'last_activity' => $_SESSION['last_activity'] ?? null
    ];
}

/**
 * Get user data from database
 */
function getUserFromDatabase($user_id, $conn = null) {
    $close_connection = false;
    
    if ($conn === null) {
        $db_file = __DIR__ . DIRECTORY_SEPARATOR . 'db_connection.php';
        if (!file_exists($db_file)) {
            error_log("db_connection.php not found", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
            return null;
        }
        
        try {
            require $db_file;
            $close_connection = true;
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
            return null;
        }
    }
    
    try {
        $stmt = $conn->prepare("SELECT id, email, first_name, last_name, phone, address, postal_code, city, created_at FROM users WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $user_data = null;
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
        }
        
        $stmt->close();
        if ($close_connection) {
            $conn->close();
        }
        
        return $user_data;
        
    } catch (Exception $e) {
        error_log("Error fetching user data: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        return null;
    }
}

/**
 * Check if user has specific role or permission
 */
function hasPermission($permission) {
    // You can extend this function based on your role system
    if (!isLoggedIn()) {
        return false;
    }
    
    // For now, all logged-in users have basic permissions
    $basic_permissions = ['view_profile', 'edit_profile', 'place_order', 'view_orders'];
    
    return in_array($permission, $basic_permissions);
}

/**
 * Log security events
 */
function logSecurityEvent($event, $user_id = null, $details = '') {
    $user_id = $user_id ?? ($_SESSION['user_id'] ?? 'anonymous');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_message = "SECURITY: $event | User: $user_id | IP: $ip_address | Details: $details | UA: $user_agent";
    error_log($log_message, 3, __DIR__ . DIRECTORY_SEPARATOR . 'security.log');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting helper
 */
function checkRateLimit($action, $max_attempts = 5, $time_window = 300) {
    $key = $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    $now = time();
    
    // Clean old entries
    if (isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = array_filter(
            $_SESSION['rate_limits'][$key],
            function($timestamp) use ($now, $time_window) {
                return ($now - $timestamp) < $time_window;
            }
        );
    } else {
        $_SESSION['rate_limits'][$key] = [];
    }
    
    // Check if limit exceeded
    if (count($_SESSION['rate_limits'][$key]) >= $max_attempts) {
        return false;
    }
    
    // Add current attempt
    $_SESSION['rate_limits'][$key][] = $now;
    
    return true;
}
?>