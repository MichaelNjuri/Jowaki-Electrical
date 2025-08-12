<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$sql = "SELECT id, name, description, price, discount_price, category, 
               image_paths, specifications, brand, warranty_months, stock
        FROM products 
        WHERE is_active = 1 
        ORDER BY is_featured DESC, name ASC";

$result = $conn->query($sql);

$products = [];

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Query failed: ' . $conn->error]);
    $conn->close();
    exit();
}

while ($row = $result->fetch_assoc()) {
    try {
        $product = [
            'id' => intval($row['id']),
            'name' => $row['name'],
            'description' => $row['description'] ?? '',
            'price' => floatval($row['price']),
            'category' => $row['category'],
            'brand' => $row['brand'] ?? '',
            'stock' => intval($row['stock'] ?? 0)
        ];

        if ($row['discount_price'] && floatval($row['discount_price']) > 0) {
            $product['discount_price'] = floatval($row['discount_price']);
        }

        if ($row['warranty_months']) {
            $product['warranty_months'] = intval($row['warranty_months']);
        }

        if ($row['image_paths']) {
            $imagePaths = json_decode($row['image_paths'], true);
            if (is_array($imagePaths) && !empty($imagePaths)) {
                $product['image'] = $imagePaths[0];
                $product['images'] = $imagePaths;
            } else {
                $product['image'] = $row['image_paths'];
                $product['images'] = [$row['image_paths']];
            }
        } else {
            $product['image'] = 'placeholder.jpg';
            $product['images'] = ['placeholder.jpg'];
        }

        if ($row['specifications']) {
            $specs = json_decode($row['specifications'], true);
            $product['specifications'] = is_array($specs) ? $specs : [];
        } else {
            $product['specifications'] = [];
        }

        $product['features'] = [];
        $product['active'] = true;

        $products[] = $product;
    } catch (Exception $e) {
        error_log("Error processing product ID {$row['id']}: " . $e->getMessage());
        continue;
    }
}

echo json_encode(['success' => true, 'products' => $products]);
$conn->close();
?>