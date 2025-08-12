<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Uploads/categories directory if it doesn't exist
    $uploadDir = 'Uploads/categories/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Define image mappings for each category
    $imageMappings = [
        'ACCESS' => [
            'search_term' => 'security+access+control',
            'alt_search' => 'key+card+reader'
        ],
        'CAMERA' => [
            'search_term' => 'security+camera',
            'alt_search' => 'cctv+camera'
        ],
        'FENCE' => [
            'search_term' => 'electric+fence',
            'alt_search' => 'security+fence'
        ],
        'FIRE' => [
            'search_term' => 'fire+alarm+system',
            'alt_search' => 'fire+extinguisher'
        ],
        'GATE' => [
            'search_term' => 'automatic+gate',
            'alt_search' => 'security+gate'
        ],
        'ALARM' => [
            'search_term' => 'security+alarm',
            'alt_search' => 'burglar+alarm'
        ],
        'BATTERY' => [
            'search_term' => 'battery+power',
            'alt_search' => 'rechargeable+battery'
        ],
        'CABLE' => [
            'search_term' => 'electrical+cable',
            'alt_search' => 'network+cable'
        ],
        'DETECTOR' => [
            'search_term' => 'motion+detector',
            'alt_search' => 'security+sensor'
        ],
        'PSU' => [
            'search_term' => 'power+supply+unit',
            'alt_search' => 'electrical+transformer'
        ],
        'PANEL' => [
            'search_term' => 'control+panel',
            'alt_search' => 'electrical+panel'
        ],
        'SIREN' => [
            'search_term' => 'emergency+siren',
            'alt_search' => 'alarm+siren'
        ],
        'STROBE' => [
            'search_term' => 'strobe+light',
            'alt_search' => 'emergency+light'
        ],
        'BUTTON' => [
            'search_term' => 'emergency+button',
            'alt_search' => 'push+button'
        ],
        'CARD' => [
            'search_term' => 'access+card',
            'alt_search' => 'rfid+card'
        ],
        'ADAPTER' => [
            'search_term' => 'electrical+adapter',
            'alt_search' => 'power+adapter'
        ],
        'NETWORK' => [
            'search_term' => 'network+equipment',
            'alt_search' => 'ethernet+cable'
        ],
        'PHONE' => [
            'search_term' => 'video+phone',
            'alt_search' => 'intercom+system'
        ],
        'BREAKGLASS' => [
            'search_term' => 'break+glass+emergency',
            'alt_search' => 'emergency+button'
        ],
        'KEYSWITCH' => [
            'search_term' => 'key+switch',
            'alt_search' => 'security+switch'
        ],
        'LADDER' => [
            'search_term' => 'aluminum+ladder',
            'alt_search' => 'extension+ladder'
        ],
        'MAGNET' => [
            'search_term' => 'electromagnetic+lock',
            'alt_search' => 'magnetic+lock'
        ],
        'SWITCHES' => [
            'search_term' => 'electrical+switch',
            'alt_search' => 'toggle+switch'
        ],
        'SENSOR' => [
            'search_term' => 'security+sensor',
            'alt_search' => 'motion+sensor'
        ],
        'GUARD' => [
            'search_term' => 'security+guard',
            'alt_search' => 'guard+tour+system'
        ],
        'GENERALS' => [
            'search_term' => 'electrical+tools',
            'alt_search' => 'maintenance+tools'
        ],
        'SERVICES' => [
            'search_term' => 'security+service',
            'alt_search' => 'maintenance+service'
        ],
        'Repairs' => [
            'search_term' => 'repair+tools',
            'alt_search' => 'maintenance+repair'
        ],
        'TV' => [
            'search_term' => 'security+monitor',
            'alt_search' => 'cctv+monitor'
        ],
        'VIDEO PHONE' => [
            'search_term' => 'video+intercom',
            'alt_search' => 'door+phone'
        ],
        'HIK' => [
            'search_term' => 'hikvision+camera',
            'alt_search' => 'security+camera'
        ],
        'TIANDY' => [
            'search_term' => 'security+camera',
            'alt_search' => 'cctv+camera'
        ],
        'SHERLOTRONICS' => [
            'search_term' => 'wireless+transmitter',
            'alt_search' => 'radio+transmitter'
        ],
        'GARRET' => [
            'search_term' => 'metal+detector',
            'alt_search' => 'security+scanner'
        ],
        'Readers' => [
            'search_term' => 'card+reader',
            'alt_search' => 'access+reader'
        ],
        'ENERGIZER' => [
            'search_term' => 'electric+fence+energizer',
            'alt_search' => 'fence+controller'
        ],
        'BALANCES' => [
            'search_term' => 'digital+scale',
            'alt_search' => 'weighing+scale'
        ],
        'OTHERS' => [
            'search_term' => 'electrical+equipment',
            'alt_search' => 'security+equipment'
        ]
    ];

    // Function to download image from Unsplash
    function downloadImage($searchTerm, $filename) {
        global $uploadDir;
        
        // Unsplash API endpoint (using a free image service)
        $url = "https://source.unsplash.com/200x200/?{$searchTerm}";
        
        $imagePath = $uploadDir . $filename;
        
        // Download image
        $imageContent = file_get_contents($url);
        if ($imageContent !== false) {
            file_put_contents($imagePath, $imageContent);
            return $imagePath;
        }
        
        return false;
    }

    // Update categories with local images
    $stmt = $conn->prepare("SELECT id, name FROM store_categories WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updateStmt = $conn->prepare("UPDATE store_categories SET image_url = ? WHERE id = ?");

    foreach ($categories as $category) {
        $categoryName = $category['name'];
        
        if (isset($imageMappings[$categoryName])) {
            $searchTerm = $imageMappings[$categoryName]['search_term'];
            $filename = strtolower(str_replace(' ', '_', $categoryName)) . '.jpg';
            
            echo "Downloading image for {$categoryName}...\n";
            
            $imagePath = downloadImage($searchTerm, $filename);
            
            if ($imagePath) {
                $relativePath = 'Uploads/categories/' . $filename;
                $updateStmt->execute([$relativePath, $category['id']]);
                echo "✓ Image saved for {$categoryName}: {$relativePath}\n";
            } else {
                echo "✗ Failed to download image for {$categoryName}\n";
            }
            
            // Small delay to avoid overwhelming the server
            sleep(1);
        }
    }

    echo "\nCategory images update completed!\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn = null;
?>
