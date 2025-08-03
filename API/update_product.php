<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    $required_fields = ['id', 'name', 'category', 'price', 'stock', 'status'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $product_id = intval($input['id']);
    $name = trim($input['name']);
    $category = trim($input['category']);
    $price = floatval($input['price']);
    $stock = intval($input['stock']);
    $description = isset($input['description']) ? trim($input['description']) : '';
    $status = $input['status'];

    // Validate data
    if ($price < 0) {
        throw new Exception('Price cannot be negative');
    }
    
    if ($stock < 0) {
        throw new Exception('Stock cannot be negative');
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        throw new Exception('Invalid status value');
    }

    // Convert status to is_active (1 for active, 0 for inactive)
    $is_active = ($status === 'active') ? 1 : 0;

    // Check if product exists
    $check_stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $check_stmt->bind_param('i', $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Product not found');
    }
    $check_stmt->close();

    // Update the product
    $update_query = "UPDATE products SET 
        name = ?, 
        category = ?, 
        price = ?, 
        stock = ?, 
        description = ?, 
        is_active = ?
        WHERE id = ?";
    
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare update statement: ' . $conn->error);
    }
    
    $stmt->bind_param('ssdsssi', $name, $category, $price, $stock, $description, $is_active, $product_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update product: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('No changes made to product');
    }
    
    $stmt->close();

    // Log the update (check if table exists first)
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_activity_log'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;
        $logSQL = "INSERT INTO admin_activity_log (admin_id, action, details, ip_address) VALUES (?, 'update_product', ?, ?)";
        $logStmt = $conn->prepare($logSQL);
        
        if ($logStmt) {
            $details = json_encode([
                'product_id' => $product_id,
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'stock' => $stock,
                'status' => $status
            ]);
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $logStmt->bind_param('iss', $admin_id, $details, $ip);
            $logStmt->execute();
            $logStmt->close();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Product updated successfully',
        'product' => [
            'id' => $product_id,
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'stock' => $stock,
            'description' => $description,
            'status' => $status // Keep the original status value for frontend compatibility
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update product: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
