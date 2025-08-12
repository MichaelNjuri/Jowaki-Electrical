<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/config.php';

try {
    $pdo = getDbConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Get featured products from database
    $sql = "SELECT id, name, description, price, discount_price, category, 
                   image_paths, specifications, brand, warranty_months, stock, is_featured
            FROM products 
            WHERE is_featured = 1 AND is_active = 1
            ORDER BY name ASC
            LIMIT 6";

    $stmt = $pdo->query($sql);
    $products = [];

    while ($row = $stmt->fetch()) {
        $product = [
            'id' => intval($row['id']),
            'name' => $row['name'],
            'description' => $row['description'] ?? '',
            'price' => floatval($row['price']),
            'category' => $row['category'],
            'brand' => $row['brand'] ?? '',
            'stock' => intval($row['stock'] ?? 0),
            'is_featured' => (bool)$row['is_featured']
        ];

        if ($row['discount_price'] && floatval($row['discount_price']) > 0) {
            $product['discount_price'] = floatval($row['discount_price']);
        }

        if ($row['warranty_months']) {
            $product['warranty_months'] = intval($row['warranty_months']);
        }

        if ($row['image_paths']) {
            $images = json_decode($row['image_paths'], true);
            if ($images && count($images) > 0) {
                $product['image_url'] = $images[0]; // Use first image
            }
        }

        if ($row['specifications']) {
            $product['specifications'] = json_decode($row['specifications'], true) ?: [];
        }

        $products[] = $product;
    }

    // If no featured products, get some regular products as fallback
    if (empty($products)) {
        $sql = "SELECT id, name, description, price, category, image_paths
                FROM products 
                WHERE is_active = 1
                ORDER BY name ASC
                LIMIT 3";
        
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch()) {
            $product = [
                'id' => intval($row['id']),
                'name' => $row['name'],
                'description' => $row['description'] ?? '',
                'price' => floatval($row['price']),
                'category' => $row['category']
            ];

            if ($row['image_paths']) {
                $images = json_decode($row['image_paths'], true);
                if ($images && count($images) > 0) {
                    $product['image_url'] = $images[0];
                }
            }

            $products[] = $product;
        }
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading featured products',
        'error' => $e->getMessage()
    ]);
}
?>

