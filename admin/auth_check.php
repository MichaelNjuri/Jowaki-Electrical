<?php
/**
 * Admin Authentication Check
 * Include this at the top of every admin page
 */

session_start();

// Include configuration
require_once dirname(__DIR__) . '/config/config.php';

// Function to check if user is admin
function isAdmin() {
    // Check for admin session variables (new admin system)
    if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role'])) {
        return true;
    }
    
    // Check for legacy admin session
    if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return true;
    }
    
    return false;
}

// Function to log admin activity
function logAdminActivity($action, $details = '') {
    try {
        $pdo = getDbConnection();
        
        // Get admin ID from session (support both new and legacy systems)
        $admin_id = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
        
        if (!$admin_id) {
            return false;
        }
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$admin_id, $action, $details, $ip, $user_agent]);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log admin activity: " . $e->getMessage());
        return false;
    }
}

// Check if user is authenticated as admin
if (!isAdmin()) {
    // Log unauthorized access attempt
    error_log("Unauthorized admin access attempt from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    
    // Redirect to admin login
    header("Location: login.php?error=unauthorized");
    exit();
}

// Log successful admin access
logAdminActivity('Page Access', 'Accessed: ' . basename($_SERVER['PHP_SELF']));

// Set admin session timeout (optional)
$session_timeout = 3600; // 1 hour
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=timeout");
    exit();
}
$_SESSION['last_activity'] = time();
?>
