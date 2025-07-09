<?php
header('Content-Type: application/json');

// Connect to the correct database
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db'; // ✅ using your correct DB name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

$name = $_POST['name'] ?? '';
$category = $_POST['category'] ?? '';
$brand = $_POST['brand'] ?? '';
$price = $_POST['price'] ?? 0;
$discount_price = $_POST['discount_price'] ?? null;
$stock = $_POST['stock'] ?? 0;
$low_stock_threshold = $_POST['low_stock_threshold'] ?? 10;
$description = $_POST['description'] ?? '';
$specifications = $_POST['specifications'] ?? '';
$weight_kg = $_POST['weight_kg'] ?? null;
$warranty_months = $_POST['warranty_months'] ?? null;
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

$image_paths = '';

if (!empty($_FILES['images']['name'][0])) {
    $uploadsDir = "../uploads/";
    $imageArray = [];

    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        $filename = basename($_FILES['images']['name'][$key]);
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
