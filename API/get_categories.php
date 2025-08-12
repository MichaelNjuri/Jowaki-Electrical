<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $csvFile = 'C:\Users\USER\Jowaki-Electrical\JOWAKI STOCK ITEMS.csv';
    
    if (!file_exists($csvFile)) {
        echo json_encode(['success' => false, 'error' => 'CSV file not found']);
        exit;
    }
    
    $categories = [];
    $categoryCounts = [];
    
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        $isFirstLine = true;
        
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if ($isFirstLine) {
                $isFirstLine = false;
                continue; // Skip header
            }
            
            if (isset($data[1]) && !empty($data[1])) {
                $category = trim(strtoupper($data[1]));
                
                if (!isset($categoryCounts[$category])) {
                    $categoryCounts[$category] = 0;
                }
                $categoryCounts[$category]++;
            }
        }
        fclose($handle);
    }
    
    // Convert to array format and sort by name
    foreach ($categoryCounts as $category => $count) {
        $categories[] = [
            'name' => $category,
            'count' => $count,
            'display_name' => ucwords(strtolower($category))
        ];
    }
    
    // Sort categories alphabetically
    usort($categories, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'total' => count($categories)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error reading categories: ' . $e->getMessage()
    ]);
}
?>
