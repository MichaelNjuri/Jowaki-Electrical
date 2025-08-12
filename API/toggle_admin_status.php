<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
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
        $input = file_get_contents('php://input');
        error_log("Toggle admin status - Raw input: " . $input);
        
        $data = json_decode($input, true);
        
        if (!$data) {
            $data = $_POST;
        }

        error_log("Toggle admin status - Decoded data: " . print_r($data, true));

        $admin_id = $data['admin_id'] ?? null;
        $new_status = $data['status'] ?? null;

        if (!$admin_id) {
            echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
            exit;
        }

        if ($new_status === null) {
            echo json_encode(['success' => false, 'message' => 'Status is required']);
            exit;
        }

        // Check if trying to modify a Super Admin (role_id = 1)
        $check_stmt = $conn->prepare("SELECT role_id FROM admin_users WHERE id = ?");
        $check_stmt->bind_param("i", $admin_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Admin user not found']);
            exit;
        }

        $admin = $result->fetch_assoc();
        $check_stmt->close();

        // Prevent modifying Super Admin status
        if ($admin['role_id'] === 1) {
            echo json_encode(['success' => false, 'message' => 'Cannot modify Super Admin status']);
            exit;
        }

        // Update admin status
        $stmt = $conn->prepare("UPDATE admin_users SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $admin_id);
        
        if ($stmt->execute()) {
            // Log activity (with error handling)
            try {
                $action = $new_status ? 'Activate Admin' : 'Deactivate Admin';
                $details = "Admin ID: $admin_id status changed to " . ($new_status ? 'Active' : 'Inactive');
                logAdminActivity($action, $details);
            } catch (Exception $logError) {
                error_log("Failed to log admin activity: " . $logError->getMessage());
                // Continue execution even if logging fails
            }
            
            $stmt->close();
            $conn->close();

            echo json_encode([
                'success' => true,
                'message' => 'Admin status updated successfully',
                'new_status' => $new_status
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update admin status']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating admin status: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
