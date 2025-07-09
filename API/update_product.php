<?php
header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Missing product ID']);
    exit;
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

$image_paths = $_POST['existing_images'] ?? '';

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

    if (!empty($imageArray)) {
        $image_paths = implode(',', $imageArray);
    }
}

$sql = "UPDATE products SET
    name=?, category=?, brand=?, price=?, discount_price=?, stock=?,
    low_stock_threshold=?, description=?, specifications=?, weight_kg=?,
    warranty_months=?, image_paths=?, is_featured=?, is_active=?
    WHERE id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssddiissddssii", $name, $category, $brand, $price, $discount_price, $stock,
    $low_stock_threshold, $description, $specifications, $weight_kg,
    $warranty_months, $image_paths, $is_featured, $is_active, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
