<?php
if (($handle = fopen('JOWAKI STOCK ITEMS.csv', 'r')) !== FALSE) {
    $categories = [];
    while (($data = fgetcsv($handle)) !== FALSE) {
        if (count($data) >= 2 && !empty($data[1])) {
            $categories[] = trim($data[1]);
        }
    }
    fclose($handle);
    
    $unique_categories = array_unique($categories);
    sort($unique_categories);
    
    echo "Unique categories found:\n";
    foreach($unique_categories as $cat) {
        echo "- " . $cat . "\n";
    }
    
    echo "\nTotal unique categories: " . count($unique_categories) . "\n";
}
?>
