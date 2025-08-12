<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Clear existing categories
    $conn->exec("DELETE FROM store_categories");
    echo "Cleared existing categories\n";

    // Define category mappings with icons and images
    $categoryMappings = [
        'ACCESS' => [
            'display_name' => 'Access Control',
            'icon_class' => 'fas fa-key',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'ACCESS'
        ],
        'CAMERA' => [
            'display_name' => 'CCTV Cameras',
            'icon_class' => 'fas fa-video',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'CAMERA'
        ],
        'FENCE' => [
            'display_name' => 'Electric Fencing',
            'icon_class' => 'fas fa-shield-alt',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'FENCE'
        ],
        'FIRE' => [
            'display_name' => 'Fire Systems',
            'icon_class' => 'fas fa-fire-extinguisher',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'FIRE'
        ],
        'GATE' => [
            'display_name' => 'Automated Gates',
            'icon_class' => 'fas fa-door-open',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'GATE'
        ],
        'ALARM' => [
            'display_name' => 'Alarm Systems',
            'icon_class' => 'fas fa-bell',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'alarm'
        ],
        'BATTERY' => [
            'display_name' => 'Batteries',
            'icon_class' => 'fas fa-battery-full',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'BATTERY'
        ],
        'CABLE' => [
            'display_name' => 'Cables',
            'icon_class' => 'fas fa-plug',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'CABLE'
        ],
        'DETECTOR' => [
            'display_name' => 'Detectors',
            'icon_class' => 'fas fa-search',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'DETECTOR'
        ],
        'PSU' => [
            'display_name' => 'Power Supplies',
            'icon_class' => 'fas fa-bolt',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'PSU'
        ],
        'PANEL' => [
            'display_name' => 'Control Panels',
            'icon_class' => 'fas fa-cogs',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'PANEL'
        ],
        'SIREN' => [
            'display_name' => 'Sirens',
            'icon_class' => 'fas fa-volume-up',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'SIREN'
        ],
        'STROBE' => [
            'display_name' => 'Strobe Lights',
            'icon_class' => 'fas fa-lightbulb',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'STROBE'
        ],
        'BUTTON' => [
            'display_name' => 'Buttons & Switches',
            'icon_class' => 'fas fa-hand-pointer',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'BUTTON'
        ],
        'CARD' => [
            'display_name' => 'Cards & Readers',
            'icon_class' => 'fas fa-credit-card',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'CARD'
        ],
        'ADAPTER' => [
            'display_name' => 'Adapters',
            'icon_class' => 'fas fa-exchange-alt',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'ADAPTER'
        ],
        'NETWORK' => [
            'display_name' => 'Network Equipment',
            'icon_class' => 'fas fa-network-wired',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'NETWORK'
        ],
        'PHONE' => [
            'display_name' => 'Video Phones',
            'icon_class' => 'fas fa-phone',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'PHONE'
        ],
        'BREAKGLASS' => [
            'display_name' => 'Break Glass',
            'icon_class' => 'fas fa-exclamation-triangle',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'BREAKGLASS'
        ],
        'KEYSWITCH' => [
            'display_name' => 'Key Switches',
            'icon_class' => 'fas fa-key',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'KEYSWITCH'
        ],
        'LADDER' => [
            'display_name' => 'Ladders',
            'icon_class' => 'fas fa-climbing',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'LADDER'
        ],
        'MAGNET' => [
            'display_name' => 'Magnets',
            'icon_class' => 'fas fa-magnet',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'MAGNET'
        ],
        'SWITCHES' => [
            'display_name' => 'Switches',
            'icon_class' => 'fas fa-toggle-on',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'SWITCHES'
        ],
        'SENSOR' => [
            'display_name' => 'Sensors',
            'icon_class' => 'fas fa-radar',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'SENSOR'
        ],
        'GUARD' => [
            'display_name' => 'Guard Tour',
            'icon_class' => 'fas fa-user-shield',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'GUARD'
        ],
        'GENERALS' => [
            'display_name' => 'General Items',
            'icon_class' => 'fas fa-tools',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'GENERALS'
        ],
        'SERVICES' => [
            'display_name' => 'Services',
            'icon_class' => 'fas fa-concierge-bell',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'SERVICES'
        ],
        'Repairs' => [
            'display_name' => 'Repairs',
            'icon_class' => 'fas fa-wrench',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'Repairs'
        ],
        'TV' => [
            'display_name' => 'TVs & Monitors',
            'icon_class' => 'fas fa-tv',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'TV'
        ],
        'VIDEO PHONE' => [
            'display_name' => 'Video Phones',
            'icon_class' => 'fas fa-video',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'VIDEO PHONE'
        ],
        'HIK' => [
            'display_name' => 'Hikvision',
            'icon_class' => 'fas fa-camera',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'HIK'
        ],
        'TIANDY' => [
            'display_name' => 'Tiandy',
            'icon_class' => 'fas fa-camera',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'TIANDY'
        ],
        'SHERLOTRONICS' => [
            'display_name' => 'Sherlotronics',
            'icon_class' => 'fas fa-broadcast-tower',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'SHERLOTRONICS'
        ],
        'GARRET' => [
            'display_name' => 'Garrett',
            'icon_class' => 'fas fa-search',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'GARRET'
        ],
        'Readers' => [
            'display_name' => 'Readers',
            'icon_class' => 'fas fa-id-card',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'Readers'
        ],
        'ENERGIZER' => [
            'display_name' => 'Energizers',
            'icon_class' => 'fas fa-bolt',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'ENERGIZER'
        ],
        'BALANCES' => [
            'display_name' => 'Balances',
            'icon_class' => 'fas fa-balance-scale',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'BALANCES'
        ],
        'OTHERS' => [
            'display_name' => 'Other Items',
            'icon_class' => 'fas fa-box',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center',
            'filter_value' => 'OTHERS'
        ]
    ];

    // Insert categories
    $insertSql = "INSERT INTO store_categories (name, display_name, image_url, icon_class, filter_value, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);

    $sortOrder = 1;
    foreach ($categoryMappings as $categoryName => $categoryData) {
        try {
            $stmt->execute([
                $categoryName,
                $categoryData['display_name'],
                $categoryData['image_url'],
                $categoryData['icon_class'],
                $categoryData['filter_value'],
                $sortOrder,
                1
            ]);
            echo "Added category: {$categoryData['display_name']}\n";
            $sortOrder++;
        } catch (PDOException $e) {
            echo "Error adding category '{$categoryData['display_name']}': " . $e->getMessage() . "\n";
        }
    }

    echo "\nStore categories updated successfully!\n";
    echo "Total categories added: " . ($sortOrder - 1) . "\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn = null;
?>
