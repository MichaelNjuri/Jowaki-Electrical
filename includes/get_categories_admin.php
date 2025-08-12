<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle DELETE request via GET parameter
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $categoryId = intval($_GET['id']);
            
            // Delete category
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Category not found or could not be deleted']);
            }
        } else {
            // Fetch categories for admin
            $stmt = $conn->prepare("
                SELECT 
                    id,
                    category as name,
                    subcategory as description
                FROM categories
                ORDER BY category, subcategory
            ");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert to expected format
            $formattedCategories = array_map(function($category) {
                return [
                    'id' => intval($category['id']),
                    'name' => $category['name'] ?? '',
                    'description' => $category['description'] ?? ''
                ];
            }, $categories);
            
            echo json_encode([
                'success' => true,
                'data' => $formattedCategories
            ]);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Handle proper DELETE request
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id']) || empty($input['id'])) {
            echo json_encode(['success' => false, 'error' => 'Category ID is required']);
            exit();
        }
        
        $categoryId = intval($input['id']);
        
        // Delete category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Category not found or could not be deleted']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Add a new category
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['name'])) {
            echo json_encode(['success' => false, 'error' => 'Category name is required']);
            exit();
        }

        $name = $input['name'];
        $description = $input['description'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO categories (category, subcategory) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        
        echo json_encode(['success' => true, 'message' => 'Category added successfully']);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Update an existing category
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['id']) || empty($input['name'])) {
            echo json_encode(['success' => false, 'error' => 'Category ID and name are required']);
            exit();
        }

        $id = $input['id'];
        $name = $input['name'];
        $description = $input['description'] ?? '';

        $stmt = $conn->prepare("UPDATE categories SET category = ?, subcategory = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);

        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>
