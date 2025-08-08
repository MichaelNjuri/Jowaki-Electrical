<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no categories in store_categories table, fall back to categories table
    if (empty($categories)) {
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
        $fallbackCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'total' => count($categories)
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn = null;
?>
