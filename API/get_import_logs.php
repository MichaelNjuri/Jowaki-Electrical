<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

try {
    // Get import logs
    $sql = "SELECT * FROM import_logs ORDER BY created_at DESC LIMIT 50";
    $result = $conn->query($sql);
    
    $logs = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = [
                'id' => $row['id'],
                'filename' => $row['filename'],
                'import_type' => $row['import_type'],
                'total_records' => $row['total_records'],
                'categories_created' => $row['categories_created'],
                'products_imported' => $row['products_imported'],
                'products_updated' => $row['products_updated'],
                'products_skipped' => $row['products_skipped'],
                'status' => $row['status'],
                'error_message' => $row['error_message'],
                'created_at' => $row['created_at']
            ];
        }
    }

    // Get import statistics
    $stats = [
        'total_imports' => 0,
        'total_categories_created' => 0,
        'total_products_imported' => 0,
        'last_import_date' => null
    ];

    $statsSql = "SELECT 
        COUNT(*) as total_imports,
        SUM(categories_created) as total_categories_created,
        SUM(products_imported) as total_products_imported,
        MAX(created_at) as last_import_date
        FROM import_logs";
    
    $statsResult = $conn->query($statsSql);
    if ($statsResult && $statsResult->num_rows > 0) {
        $statsRow = $statsResult->fetch_assoc();
        $stats['total_imports'] = $statsRow['total_imports'] ?? 0;
        $stats['total_categories_created'] = $statsRow['total_categories_created'] ?? 0;
        $stats['total_products_imported'] = $statsRow['total_products_imported'] ?? 0;
        $stats['last_import_date'] = $statsRow['last_import_date'];
    }

    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'statistics' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to retrieve import logs: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 