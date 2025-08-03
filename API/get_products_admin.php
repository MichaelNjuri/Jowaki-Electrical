<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/php-error.log'); // Adjust path as needed

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Optional: Admin authentication (uncomment if needed)
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'error' => 'Unauthorized access. Admin only.']);
//     exit();
// }

// Fetch all products with fields matching table structure
$sql = "SELECT id, name, description, price, discount_price, stock, is_active, 
               image_paths, category, specifications, brand, low_stock_threshold, 
               is_featured, weight_kg, warranty_months, created_at 
        FROM products 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

$products = [];

if ($result === false) {
    error_log("Query failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Query failed']);
    $conn->close();
    exit();
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        try {
            // Handle specifications field
            $specifications = [];
            if (!empty($row['specifications'])) {
                if (is_string($row['specifications'])) {
                    $decoded = json_decode($row['specifications'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $specifications = $decoded;
                    } else {
                        $specifications = array_map('trim', explode(',', $row['specifications']));
                    }
                } else {
                    $specifications = (array) $row['specifications'];
                }
            }

            // Ensure UTF-8 encoding for strings
            $product = [
                'id' => intval($row['id']),
                'name' => mb_convert_encoding($row['name'] ?? '', 'UTF-8', 'UTF-8'),
                'description' => mb_convert_encoding($row['description'] ?? '', 'UTF-8', 'UTF-8'),
                'price' => floatval($row['price']),
                'discount_price' => !empty($row['discount_price']) ? floatval($row['discount_price']) : null,
                'stock' => intval($row['stock']),
                'is_active' => intval($row['is_active']),
                'status' => intval($row['is_active']) ? 'active' : 'inactive', // Convert for frontend compatibility
                'category' => mb_convert_encoding($row['category'] ?? '', 'UTF-8', 'UTF-8'),
                'brand' => mb_convert_encoding($row['brand'] ?? '', 'UTF-8', 'UTF-8'),
                'specifications' => $specifications,
                'low_stock_threshold' => intval($row['low_stock_threshold'] ?? 10),
                'is_featured' => intval($row['is_featured'] ?? 0),
                'weight_kg' => !empty($row['weight_kg']) ? floatval($row['weight_kg']) : null,
                'warranty_months' => !empty($row['warranty_months']) ? intval($row['warranty_months']) : null,
                'image_paths' => mb_convert_encoding($row['image_paths'] ?? '', 'UTF-8', 'UTF-8'),
                'created_at' => $row['created_at'] ?? null
            ];

            $products[] = $product;
        } catch (Exception $e) {
            error_log("Error processing product ID {$row['id']}: " . $e->getMessage());
            continue;
        }
    }
} else {
    error_log("No products found in database.");
}

try {
    error_log("Fetched " . count($products) . " products for admin dashboard");
    echo json_encode($products, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    error_log("JSON encoding error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'JSON encoding failed']);
}

$conn->close();
?>