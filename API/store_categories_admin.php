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
            $stmt = $conn->prepare("DELETE FROM store_categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Category not found or could not be deleted']);
            }
        } else {
            // Fetch store categories for admin
            $stmt = $conn->prepare("
                SELECT 
                    id,
                    name,
                    display_name,
                    image_url,
                    icon_class,
                    filter_value,
                    sort_order,
                    is_active
                FROM store_categories
                ORDER BY sort_order ASC, name ASC
            ");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $categories
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
        $stmt = $conn->prepare("DELETE FROM store_categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Category not found or could not be deleted']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Add a new store category
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['name'])) {
            echo json_encode(['success' => false, 'error' => 'Category name is required']);
            exit();
        }

        $name = $input['name'];
        $displayName = $input['display_name'] ?? $name;
        $imageUrl = $input['image_url'] ?? null;
        $iconClass = $input['icon_class'] ?? 'fas fa-box';
        $filterValue = $input['filter_value'] ?? $name;
        $sortOrder = $input['sort_order'] ?? 0;
        $isActive = $input['is_active'] ?? 1;
        
        $stmt = $conn->prepare("
            INSERT INTO store_categories (name, display_name, image_url, icon_class, filter_value, sort_order, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $displayName, $imageUrl, $iconClass, $filterValue, $sortOrder, $isActive]);
        
        $categoryId = $conn->lastInsertId();
        
        echo json_encode(['success' => true, 'message' => 'Category added successfully', 'category_id' => $categoryId]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Update an existing store category
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['id']) || empty($input['name'])) {
            echo json_encode(['success' => false, 'error' => 'Category ID and name are required']);
            exit();
        }

        $id = $input['id'];
        $name = $input['name'];
        $displayName = $input['display_name'] ?? $name;
        $imageUrl = $input['image_url'] ?? null;
        $iconClass = $input['icon_class'] ?? 'fas fa-box';
        $filterValue = $input['filter_value'] ?? $name;
        $sortOrder = $input['sort_order'] ?? 0;
        $isActive = $input['is_active'] ?? 1;

        $stmt = $conn->prepare("
            UPDATE store_categories 
            SET name = ?, display_name = ?, image_url = ?, icon_class = ?, filter_value = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $displayName, $imageUrl, $iconClass, $filterValue, $sortOrder, $isActive, $id]);

        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>
