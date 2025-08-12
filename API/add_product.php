<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Connect to the correct database
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// Validate required fields
if (empty($_POST['name']) || empty($_POST['price'])) {
    die(json_encode(['success' => false, 'error' => 'Name and price are required']));
}

$name = $_POST['name'] ?? '';
$category_id = $_POST['category_id'] ?? null;
$category = $_POST['category'] ?? ''; // Keep for backward compatibility
$brand = $_POST['brand'] ?? '';
$price = floatval($_POST['price']);
$discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
$stock = intval($_POST['stock_quantity'] ?? $_POST['stock'] ?? 0);
$low_stock_threshold = intval($_POST['low_stock_threshold'] ?? 10);
$description = $_POST['description'] ?? '';
$specifications = $_POST['specifications'] ?? '';
$weight_kg = !empty($_POST['weight_kg']) ? floatval($_POST['weight_kg']) : null;
$warranty_months = !empty($_POST['warranty_months']) ? intval($_POST['warranty_months']) : null;
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$is_active = isset($_POST['is_active']) ? 1 : 1; // Default to active

$image_paths = '';

if (!empty($_FILES['images']['name'][0])) {
    $uploadsDir = "../uploads/";
    $imageArray = [];
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        if (empty($tmpName)) continue;
        
        $originalName = $_FILES['images']['name'][$key];
        $fileSize = $_FILES['images']['size'][$key];
        $fileError = $_FILES['images']['error'][$key];
        
        // Check for upload errors
        if ($fileError !== UPLOAD_ERR_OK) {
            continue;
        }
        
        // Check file size (limit to 5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            continue;
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($tmpName);
        if (!in_array($fileType, $allowedTypes)) {
            continue;
        }
        
        // Generate unique filename
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = uniqid('product_', true) . '.' . $extension;
        $targetFile = $uploadsDir . $filename;

        if (move_uploaded_file($tmpName, $targetFile)) {
            $imageArray[] = 'uploads/' . $filename;
        }
    }

    $image_paths = implode(',', $imageArray);
}

$sql = "INSERT INTO products (
    name, category, brand, price, discount_price, stock, low_stock_threshold,
    description, specifications, weight_kg, warranty_months, image_paths,
    is_featured, is_active
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssddiissddssi", $name, $category, $brand, $price, $discount_price,
    $stock, $low_stock_threshold, $description, $specifications, $weight_kg,
    $warranty_months, $image_paths, $is_featured, $is_active);

$response = [];

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Product added successfully.';
} else {
    $response['success'] = false;
    $response['error'] = $stmt->error;
}

echo json_encode($response);
