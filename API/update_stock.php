<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit();
    }

    $product_id = intval($input['product_id']);
    $action = isset($input['action']) ? $input['action'] : 'set'; // set, add, subtract
    $quantity = isset($input['quantity']) ? intval($input['quantity']) : 0;
    $reason = isset($input['reason']) ? $conn->real_escape_string($input['reason']) : '';
    $updated_by = isset($input['updated_by']) ? $conn->real_escape_string($input['updated_by']) : 'admin';

    // Get current stock
    $current_stock_query = "SELECT stock, name FROM products WHERE id = ?";
    $stmt = $conn->prepare($current_stock_query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit();
    }

    $current_stock = intval($product['stock']);
    $new_stock = $current_stock;

    // Calculate new stock based on action
    switch ($action) {
        case 'set':
            $new_stock = $quantity;
            break;
        case 'add':
            $new_stock = $current_stock + $quantity;
            break;
        case 'subtract':
            $new_stock = $current_stock - $quantity;
            if ($new_stock < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Insufficient stock']);
                exit();
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            exit();
    }

    // Update stock (using existing columns)
    $update_query = "UPDATE products SET stock = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('ii', $new_stock, $product_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update stock');
    }

    // Log stock movement (check if table exists first)
    $table_check = $conn->query("SHOW TABLES LIKE 'stock_movements'");
    if ($table_check && $table_check->num_rows > 0) {
        $log_query = "INSERT INTO stock_movements (product_id, old_stock, new_stock, quantity_changed, operation, reason, updated_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $log_stmt = $conn->prepare($log_query);
        $quantity_changed = abs($new_stock - $current_stock);
        $log_stmt->bind_param('iiissis', $product_id, $current_stock, $new_stock, $quantity_changed, $action, $reason, $updated_by);
        $log_stmt->execute();
        $log_stmt->close();
    }

    // Check for low stock alert (check if table exists first)
    $low_stock_threshold = 10; // Configurable threshold
    $alert_table_check = $conn->query("SHOW TABLES LIKE 'stock_alerts'");
    if ($alert_table_check && $alert_table_check->num_rows > 0) {
        if ($new_stock <= $low_stock_threshold && $new_stock > 0) {
            // Log low stock alert
            $alert_query = "INSERT INTO stock_alerts (product_id, alert_type, message, created_at) VALUES (?, 'low_stock', ?, NOW())";
            $alert_stmt = $conn->prepare($alert_query);
            $alert_message = "Product {$product['name']} is running low on stock. Current stock: {$new_stock}";
            $alert_stmt->bind_param('is', $product_id, $alert_message);
            $alert_stmt->execute();
            $alert_stmt->close();
        }

        // Check for out of stock alert
        if ($new_stock <= 0) {
            $alert_query = "INSERT INTO stock_alerts (product_id, alert_type, message, created_at) VALUES (?, 'out_of_stock', ?, NOW())";
            $alert_stmt = $conn->prepare($alert_query);
            $alert_message = "Product {$product['name']} is out of stock";
            $alert_stmt->bind_param('is', $product_id, $alert_message);
            $alert_stmt->execute();
            $alert_stmt->close();
        }
    }

    $stmt->close();
    $update_stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Stock updated successfully',
        'data' => [
            'product_id' => $product_id,
            'product_name' => $product['name'],
            'old_stock' => $current_stock,
            'new_stock' => $new_stock,
            'quantity_changed' => abs($new_stock - $current_stock),
            'action' => $action,
            'low_stock_alert' => $new_stock <= $low_stock_threshold,
            'out_of_stock' => $new_stock <= 0
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    $conn->close();
}
?> 