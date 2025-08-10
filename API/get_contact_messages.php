<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Check if contact_messages table exists
            $tableExists = $conn->query("SHOW TABLES LIKE 'contact_messages'");
            if ($tableExists->num_rows === 0) {
                echo json_encode(['success' => true, 'messages' => []]);
                break;
            }

            // Get all contact messages for admin
            $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
            $result = $conn->query($sql);
            
            $messages = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $messages[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'subject' => $row['subject'],
                        'message' => $row['message'],
                        'submitted_at' => $row['submitted_at'],
                        'ip_address' => $row['ip_address'],
                        'is_read' => (bool)$row['is_read'],
                        'created_at' => $row['created_at']
                    ];
                }
            }
            
            // Log activity
            logAdminActivity('View Contact Messages', 'Viewed contact messages in admin dashboard');
            
            echo json_encode(['success' => true, 'messages' => $messages]);
            break;
            
        case 'POST':
            // Mark message as read
            $input = json_decode(file_get_contents('php://input'), true);
            $message_id = $input['message_id'] ?? null;
            
            if ($message_id) {
                $stmt = $conn->prepare("UPDATE contact_messages SET is_read = TRUE WHERE id = ?");
                $stmt->bind_param("i", $message_id);
                $stmt->execute();
                $stmt->close();
                
                // Log activity
                logAdminActivity('Mark Message Read', "Marked contact message ID $message_id as read");
                
                echo json_encode(['success' => true, 'message' => 'Message marked as read']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Message ID required']);
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>
