<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

require_once 'db_connection.php';

try {
    $user_id = $_SESSION['user_id'];
    
    // Get total orders
    $stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_orders = $result->fetch_assoc()['total_orders'];
    $stmt->close();
    
    // Get pending orders
    $stmt = $conn->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE user_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pending_orders = $result->fetch_assoc()['pending_orders'];
    $stmt->close();
    
    // Calculate profile completion percentage
    $stmt = $conn->prepare("SELECT first_name, last_name, email, phone, address FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    $profile_fields = 0;
    $completed_fields = 0;
    
    if ($user) {
        $fields = ['first_name', 'last_name', 'email', 'phone', 'address'];
        $profile_fields = count($fields);
        
        foreach ($fields as $field) {
            if (!empty($user[$field])) {
                $completed_fields++;
            }
        }
    }
    
    $profile_completion = $profile_fields > 0 ? round(($completed_fields / $profile_fields) * 100) : 0;
    
    $stats = [
        'total_orders' => (int)$total_orders,
        'pending_orders' => (int)$pending_orders,
        'profile_completion' => $profile_completion
    ];
    
    echo json_encode(['success' => true, 'stats' => $stats]);
    
} catch (Exception $e) {
    error_log("Error in get_user_stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$conn->close();
?>
