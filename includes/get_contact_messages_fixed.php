<?php
session_start();
if (ob_get_level()) ob_end_clean();

require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

$conn = null;

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if contact_messages table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'contact_messages'");
    if ($tableExists->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'messages' => []
        ]);
        exit;
    }

    // Handle POST request for marking messages as read
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $messageId = $data['message_id'] ?? null;
        
        if ($messageId) {
            $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
            $stmt->bind_param("i", $messageId);
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Message marked as read'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Message ID required'
            ]);
        }
        exit;
    }

    // GET request - fetch all contact messages
    $stmt = $conn->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Contact messages fetch error: ' . $e->getMessage()
    ]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>





