<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No CSV file uploaded or upload error');
    }

    $file = $_FILES['csv_file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // Validate file
    if ($fileError !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $fileError);
    }

    if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
        throw new Exception('File too large. Maximum size is 10MB');
    }

    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($fileExtension !== 'csv') {
        throw new Exception('Only CSV files are allowed');
    }

    // Read CSV file
    if (!file_exists($fileTmpName)) {
        throw new Exception('Temporary file not found');
    }

    $csvData = array_map('str_getcsv', file($fileTmpName));
    
    if (empty($csvData) || count($csvData) < 2) {
        throw new Exception('CSV file is empty or has insufficient data');
    }

    // Validate CSV structure
    $headers = $csvData[0];
    $expectedHeaders = ['ITEM NAME', 'CATEGORY'];
    
    if (count($headers) < 2 || 
        strtoupper(trim($headers[0])) !== 'ITEM NAME' || 
        strtoupper(trim($headers[1])) !== 'CATEGORY') {
        throw new Exception('Invalid CSV format. Expected headers: ITEM NAME, CATEGORY');
    }

    // Get import options
    $createCategories = isset($_POST['create_categories']) && $_POST['create_categories'] === 'on';
    $updateExisting = isset($_POST['update_existing']) && $_POST['update_existing'] === 'on';
    $skipDuplicates = isset($_POST['skip_duplicates']) && $_POST['skip_duplicates'] === 'on';
    $defaultPrice = floatval($_POST['default_price'] ?? 0);
    $defaultStock = intval($_POST['default_stock'] ?? 10);

    // Remove header row
    array_shift($csvData);

    // Create import log table if it doesn't exist
    $createLogTableSQL = "CREATE TABLE IF NOT EXISTS import_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        import_type VARCHAR(50) NOT NULL,
        total_records INT DEFAULT 0,
        categories_created INT DEFAULT 0,
        products_imported INT DEFAULT 0,
        products_updated INT DEFAULT 0,
        products_skipped INT DEFAULT 0,
        status ENUM('success', 'failed', 'partial') DEFAULT 'success',
        error_message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createLogTableSQL)) {
        throw new Exception('Failed to create import logs table: ' . $conn->error);
    }

    // Start transaction
    $conn->begin_transaction();

    $categoriesCreated = 0;
    $productsImported = 0;
    $productsUpdated = 0;
    $productsSkipped = 0;
    $errors = [];

    // Process each row
    foreach ($csvData as $index => $row) {
        if (count($row) < 2) {
            $errors[] = "Row " . ($index + 2) . ": Insufficient data";
            continue;
        }

        $itemName = trim($row[0]);
        $categoryName = trim($row[1]);

        if (empty($itemName) || empty($categoryName)) {
            $errors[] = "Row " . ($index + 2) . ": Empty item name or category";
            continue;
        }

        try {
            // Check if category exists
            $categoryId = null;
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->bind_param('s', $categoryName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $categoryId = $result->fetch_assoc()['id'];
            } elseif ($createCategories) {
                // Create new category
                $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $description = "Auto-created from CSV import";
                $stmt->bind_param('ss', $categoryName, $description);
                
                if ($stmt->execute()) {
                    $categoryId = $conn->insert_id;
                    $categoriesCreated++;
                } else {
                    $errors[] = "Row " . ($index + 2) . ": Failed to create category '$categoryName'";
                    continue;
                }
            } else {
                $errors[] = "Row " . ($index + 2) . ": Category '$categoryName' not found and auto-creation disabled";
                continue;
            }

            // Check if product exists
            $stmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
            $stmt->bind_param('s', $itemName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                if ($updateExisting) {
                    // Update existing product
                    $productId = $result->fetch_assoc()['id'];
                    $stmt = $conn->prepare("UPDATE products SET category_id = ?, price = ?, stock_quantity = ? WHERE id = ?");
                    $stmt->bind_param('idii', $categoryId, $defaultPrice, $defaultStock, $productId);
                    
                    if ($stmt->execute()) {
                        $productsUpdated++;
                    } else {
                        $errors[] = "Row " . ($index + 2) . ": Failed to update product '$itemName'";
                    }
                } elseif ($skipDuplicates) {
                    $productsSkipped++;
                } else {
                    $errors[] = "Row " . ($index + 2) . ": Product '$itemName' already exists and update disabled";
                }
            } else {
                // Create new product
                $stmt = $conn->prepare("INSERT INTO products (name, category_id, price, stock_quantity, description, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $description = "Imported from CSV";
                $stmt->bind_param('sids', $itemName, $categoryId, $defaultPrice, $defaultStock, $description);
                
                if ($stmt->execute()) {
                    $productsImported++;
                } else {
                    $errors[] = "Row " . ($index + 2) . ": Failed to create product '$itemName'";
                }
            }

        } catch (Exception $e) {
            $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
        }
    }

    // Log the import
    $status = empty($errors) ? 'success' : (count($errors) < count($csvData) ? 'partial' : 'failed');
    $errorMessage = empty($errors) ? null : implode('; ', array_slice($errors, 0, 10)); // Limit error message length

    $stmt = $conn->prepare("INSERT INTO import_logs (filename, import_type, total_records, categories_created, products_imported, products_updated, products_skipped, status, error_message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $importType = 'stock_items';
    $totalRecords = count($csvData);
    $stmt->bind_param('ssiiiiiss', $fileName, $importType, $totalRecords, $categoriesCreated, $productsImported, $productsUpdated, $productsSkipped, $status, $errorMessage);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'CSV import completed',
        'summary' => [
            'total_records' => $totalRecords,
            'categories_created' => $categoriesCreated,
            'products_imported' => $productsImported,
            'products_updated' => $productsUpdated,
            'products_skipped' => $productsSkipped,
            'errors' => count($errors),
            'status' => $status
        ],
        'errors' => $errors
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 