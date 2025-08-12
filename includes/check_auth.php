<?php
require_once 'db_connection.php';

// Authentication and authorization functions
function isAdmin() {
    // Check for admin session variables (new admin system)
    if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role'])) {
        return true;
    }
    
    // Check for legacy session variables
    if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return true;
    }
    
    return false;
}

function hasPermission($permission) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['permissions'])) {
        return false;
    }
    
    $permissions = $_SESSION['permissions'];
    return isset($permissions[$permission]) && $permissions[$permission] === true;
}

function logAdminActivity($action, $details = '') {
    // Get admin ID from session (support both new and legacy systems)
    $admin_id = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
    
    if (!$admin_id) {
        return false;
    }
    
    try {
        $conn = getConnection();
        if (!$conn) {
            return false;
        }
        
        $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bind_param("issss", $admin_id, $action, $details, $ip, $user_agent);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// For backward compatibility - simple auth check endpoint
if (basename($_SERVER['PHP_SELF']) === 'check_auth.php') {
    header('Content-Type: application/json');
    session_start();
    
    if (isAdmin()) {
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'message' => 'Authentication check passed'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'message' => 'Authentication required'
        ]);
    }
}
?>