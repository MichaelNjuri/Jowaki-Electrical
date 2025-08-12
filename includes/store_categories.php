<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

try {
    // Create store_categories table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS store_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        icon VARCHAR(100) NOT NULL,
        filter_value VARCHAR(100) NOT NULL,
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTableSQL)) {
        throw new Exception('Failed to create store_categories table: ' . $conn->error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get active categories for store frontend
        $sql = "SELECT * FROM store_categories WHERE is_active = 1 ORDER BY sort_order ASC, created_at ASC";
        $result = $conn->query($sql);
        
        $categories = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'icon' => $row['icon'],
                    'filter_value' => $row['filter_value'],
                    'sort_order' => $row['sort_order']
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Add new category
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name']) || !isset($input['icon']) || !isset($input['filter_value'])) {
            throw new Exception('Missing required fields');
        }
        
        $stmt = $conn->prepare("INSERT INTO store_categories (name, icon, filter_value, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssii', 
            $input['name'],
            $input['icon'],
            $input['filter_value'],
            $input['sort_order'] ?? 0,
            $input['is_active'] ?? 1
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Category added successfully',
                'id' => $conn->insert_id
            ]);
        } else {
            throw new Exception('Failed to add category: ' . $stmt->error);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Update category
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            throw new Exception('Missing category ID');
        }
        
        $stmt = $conn->prepare("UPDATE store_categories SET name = ?, icon = ?, filter_value = ?, sort_order = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param('sssiii', 
            $input['name'],
            $input['icon'],
            $input['filter_value'],
            $input['sort_order'] ?? 0,
            $input['is_active'] ?? 1,
            $input['id']
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update category: ' . $stmt->error);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Delete category
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            throw new Exception('Missing category ID');
        }
        
        $stmt = $conn->prepare("DELETE FROM store_categories WHERE id = ?");
        $stmt->bind_param('i', $input['id']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } else {
            throw new Exception('Failed to delete category: ' . $stmt->error);
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 