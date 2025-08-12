<?php
// Save Fixed API Helper
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['filename']) || !isset($data['content'])) {
        throw new Exception('Missing filename or content');
    }
    
    $filename = $data['filename'];
    $content = $data['content'];
    
    // Validate filename
    if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
        throw new Exception('Invalid filename');
    }
    
    $filepath = 'API/' . $filename;
    
    if (file_put_contents($filepath, $content)) {
        echo json_encode([
            'success' => true,
            'message' => "Fixed API saved as $filename"
        ]);
    } else {
        throw new Exception('Failed to write file');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

