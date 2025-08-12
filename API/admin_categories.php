<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connection.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Get all categories
            $sql = "SELECT 
                        c.id,
                        c.name,
                        c.description,
                        COUNT(p.id) as product_count
                    FROM categories c
                    LEFT JOIN products p ON c.id = p.category_id
                    GROUP BY c.id, c.name, c.description
                    ORDER BY c.name ASC";
            
            $result = $conn->query($sql);
            $categories = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $categories[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'product_count' => $row['product_count']
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'categories' => $categories]);
            break;
            
        case 'POST':
            // Add new category
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['name'])) {
                throw new Exception('Category name is required');
            }
            
            $name = $conn->real_escape_string($data['name']);
            $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
            
            $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $name, $description);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Category added successfully']);
            } else {
                throw new Exception('Failed to add category');
            }
            break;
            
        case 'PUT':
            // Update category
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['id']) || !isset($data['name'])) {
                throw new Exception('Category ID and name are required');
            }
            
            $id = (int)$data['id'];
            $name = $conn->real_escape_string($data['name']);
            $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
            
            $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $description, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
            } else {
                throw new Exception('Failed to update category');
            }
            break;
            
        case 'DELETE':
            // Delete category
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if (!$id) {
                throw new Exception('Category ID is required');
            }
            
            // Check if category has products
            $checkSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("i", $id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                throw new Exception('Cannot delete category with existing products');
            }
            
            $sql = "DELETE FROM categories WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            } else {
                throw new Exception('Failed to delete category');
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