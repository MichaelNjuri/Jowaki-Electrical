<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
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
                
                echo json_encode(['success' => true, 'message' => 'Message marked as read']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Message ID required']);
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
