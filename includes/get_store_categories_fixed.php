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

    // Check if store_categories table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'store_categories'");
    if ($tableExists->num_rows === 0) {
        // Return default categories if table doesn't exist
        echo json_encode([
            'success' => true,
            'categories' => [
                ['id' => 1, 'name' => 'Electrical Components', 'description' => 'Basic electrical components'],
                ['id' => 2, 'name' => 'Security Equipment', 'description' => 'Security and surveillance equipment'],
                ['id' => 3, 'name' => 'CCTV Systems', 'description' => 'Closed-circuit television systems'],
                ['id' => 4, 'name' => 'Access Control', 'description' => 'Access control systems'],
                ['id' => 5, 'name' => 'Fire Safety', 'description' => 'Fire safety equipment'],
                ['id' => 6, 'name' => 'Lighting', 'description' => 'Lighting solutions'],
                ['id' => 7, 'name' => 'Wiring', 'description' => 'Electrical wiring and cables'],
                ['id' => 8, 'name' => 'Tools', 'description' => 'Electrical tools and equipment'],
                ['id' => 9, 'name' => 'Spare Parts', 'description' => 'Spare parts and accessories'],
                ['id' => 10, 'name' => 'Installation Services', 'description' => 'Professional installation services']
            ]
        ]);
        exit;
    }

    // Fetch store categories from database
    $stmt = $conn->prepare("SELECT * FROM store_categories ORDER BY name ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Store categories fetch error: ' . $e->getMessage()
    ]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>





