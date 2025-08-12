<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
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

    // Check if store_categories table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'store_categories'");
    if ($tableExists->num_rows > 0) {
        // Fetch store categories with images
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
            WHERE is_active = 1
            ORDER BY sort_order ASC, name ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    } else {
        // Check if categories table exists as fallback
        $categoriesTableExists = $conn->query("SHOW TABLES LIKE 'categories'");
        if ($categoriesTableExists->num_rows > 0) {
            $stmt = $conn->prepare("
                SELECT DISTINCT 
                    category as name,
                    category as display_name,
                    category as filter_value
                FROM categories 
                WHERE category IS NOT NULL AND category != ''
                ORDER BY category ASC
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            $fallbackCategories = [];
            while ($row = $result->fetch_assoc()) {
                $fallbackCategories[] = $row;
            }
            
            // Convert to store_categories format with default values
            $categories = array_map(function($cat) {
                return [
                    'id' => null,
                    'name' => $cat['name'],
                    'display_name' => $cat['display_name'],
                    'image_url' => null,
                    'icon_class' => 'fas fa-box',
                    'filter_value' => $cat['filter_value'],
                    'sort_order' => 0,
                    'is_active' => 1
                ];
            }, $fallbackCategories);
        } else {
            $categories = [];
        }
    }
    
    $conn->close();
    
    // Log activity
    logAdminActivity('View Store Categories', 'Viewed store categories in admin dashboard');
    
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'total' => count($categories)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
