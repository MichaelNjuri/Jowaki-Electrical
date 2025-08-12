<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db_connection.php';

// Category mapping for JOWAKI STOCK ITEMS.csv format
$categoryMapping = [
    'ACCESS' => ['name' => 'Access Control', 'icon' => 'fas fa-key', 'filter_value' => 'access'],
    'ADAPTER' => ['name' => 'Adapters', 'icon' => 'fas fa-plug', 'filter_value' => 'adapter'],
    'alarm' => ['name' => 'Alarm Systems', 'icon' => 'fas fa-bell', 'filter_value' => 'alarm'],
    'BALANCES' => ['name' => 'Balances', 'icon' => 'fas fa-balance-scale', 'filter_value' => 'balances'],
    'BATTERY' => ['name' => 'Batteries', 'icon' => 'fas fa-battery-full', 'filter_value' => 'battery'],
    'BREAKGLASS' => ['name' => 'Break Glass', 'icon' => 'fas fa-exclamation-triangle', 'filter_value' => 'breakglass'],
    'BUTTON' => ['name' => 'Buttons', 'icon' => 'fas fa-circle', 'filter_value' => 'button'],
    'CABLE' => ['name' => 'Cables', 'icon' => 'fas fa-cable', 'filter_value' => 'cable'],
    'CAMERA' => ['name' => 'Cameras', 'icon' => 'fas fa-video', 'filter_value' => 'camera'],
    'CARD' => ['name' => 'Cards', 'icon' => 'fas fa-credit-card', 'filter_value' => 'card'],
    'DETECTOR' => ['name' => 'Detectors', 'icon' => 'fas fa-search', 'filter_value' => 'detector'],
    'ENERGIZER' => ['name' => 'Energizers', 'icon' => 'fas fa-bolt', 'filter_value' => 'energizer'],
    'FENCE' => ['name' => 'Electric Fencing', 'icon' => 'fas fa-shield-alt', 'filter_value' => 'fence'],
    'FIRE' => ['name' => 'Fire Systems', 'icon' => 'fas fa-fire', 'filter_value' => 'fire'],
    'fire' => ['name' => 'Fire Systems', 'icon' => 'fas fa-fire', 'filter_value' => 'fire'],
    'GARRET' => ['name' => 'Garret Systems', 'icon' => 'fas fa-building', 'filter_value' => 'garret'],
    'GATE' => ['name' => 'Gates', 'icon' => 'fas fa-door-open', 'filter_value' => 'gate'],
    'gates' => ['name' => 'Gates', 'icon' => 'fas fa-door-open', 'filter_value' => 'gate'],
    'GENERALS' => ['name' => 'General Items', 'icon' => 'fas fa-tools', 'filter_value' => 'general'],
    'GUARD' => ['name' => 'Guard Systems', 'icon' => 'fas fa-user-shield', 'filter_value' => 'guard'],
    'HIK' => ['name' => 'Hikvision', 'icon' => 'fas fa-camera', 'filter_value' => 'hik'],
    'KEYSWITCH' => ['name' => 'Key Switches', 'icon' => 'fas fa-toggle-on', 'filter_value' => 'keyswitch'],
    'LADDER' => ['name' => 'Ladders', 'icon' => 'fas fa-level-up-alt', 'filter_value' => 'ladder'],
    'MAGNET' => ['name' => 'Magnets', 'icon' => 'fas fa-magnet', 'filter_value' => 'magnet'],
    'NETWORK' => ['name' => 'Network', 'icon' => 'fas fa-network-wired', 'filter_value' => 'network'],
    'OTHERS' => ['name' => 'Others', 'icon' => 'fas fa-ellipsis-h', 'filter_value' => 'others'],
    'PANEL' => ['name' => 'Panels', 'icon' => 'fas fa-th-large', 'filter_value' => 'panel'],
    'PHONE' => ['name' => 'Phones', 'icon' => 'fas fa-phone', 'filter_value' => 'phone'],
    'PSU' => ['name' => 'Power Supplies', 'icon' => 'fas fa-power-off', 'filter_value' => 'psu'],
    'psu' => ['name' => 'Power Supplies', 'icon' => 'fas fa-power-off', 'filter_value' => 'psu'],
    'Readers' => ['name' => 'Readers', 'icon' => 'fas fa-fingerprint', 'filter_value' => 'readers'],
    'Repairs' => ['name' => 'Repairs', 'icon' => 'fas fa-wrench', 'filter_value' => 'repairs'],
    'SENSOR' => ['name' => 'Sensors', 'icon' => 'fas fa-microchip', 'filter_value' => 'sensor'],
    'SERVICES' => ['name' => 'Services', 'icon' => 'fas fa-cogs', 'filter_value' => 'services'],
    'SHERLOTRONICS' => ['name' => 'Sherlotronics', 'icon' => 'fas fa-industry', 'filter_value' => 'sherlotronics'],
    'SIREN' => ['name' => 'Sirens', 'icon' => 'fas fa-volume-up', 'filter_value' => 'siren'],
    'STROBE' => ['name' => 'Strobes', 'icon' => 'fas fa-lightbulb', 'filter_value' => 'strobe'],
    'SWITCHES' => ['name' => 'Switches', 'icon' => 'fas fa-toggle-on', 'filter_value' => 'switches'],
    'TIANDY' => ['name' => 'Tiandy', 'icon' => 'fas fa-camera', 'filter_value' => 'tiandy'],
    'TV' => ['name' => 'TV Systems', 'icon' => 'fas fa-tv', 'filter_value' => 'tv'],
    'VIDEO PHONE' => ['name' => 'Video Phones', 'icon' => 'fas fa-video', 'filter_value' => 'videophone']
];

try {
    // Create store_categories table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS store_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        icon VARCHAR(100) NOT NULL,
        filter_value VARCHAR(100) NOT NULL,
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (!$conn->query($createTableSQL)) {
        throw new Exception('Failed to create store_categories table: ' . $conn->error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Export categories to CSV
        $action = $_GET['action'] ?? 'export';

        if ($action === 'export') {
            // Get all categories for export
            $sql = "SELECT * FROM store_categories ORDER BY sort_order ASC, created_at ASC";
            $result = $conn->query($sql);

            $categories = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $categories[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'icon' => $row['icon'],
                        'filter_value' => $row['filter_value'],
                        'sort_order' => $row['sort_order'],
                        'is_active' => $row['is_active']
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'categories' => $categories,
                'message' => 'Categories exported successfully'
            ]);
        } else {
            throw new Exception('Invalid action');
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Import categories from CSV
        $action = $_POST['action'] ?? 'import';

        if ($action === 'import') {
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No CSV file uploaded or upload error');
            }

            $csvFile = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($csvFile, 'r');

            if (!$handle) {
                throw new Exception('Could not open CSV file');
            }

            // Start transaction
            $conn->begin_transaction();

            try {
                // Clear existing categories if requested
                $clearExisting = isset($_POST['clear_existing']) && $_POST['clear_existing'] === '1';
                if ($clearExisting) {
                    $conn->query("DELETE FROM store_categories");
                }

                $imported = 0;
                $skipped = 0;
                $errors = [];

                $isFirstLine = true;
                $csvFormat = 'standard'; // Default format

                // Detect CSV format by reading first few lines
                $firstLines = [];
                for ($i = 0; $i < 5; $i++) {
                    $line = fgets($handle);
                    if ($line !== false) {
                        $firstLines[] = trim($line);
                    }
                }

                // Reset file pointer
                rewind($handle);

                // Check if it's the JOWAKI format (ITEM NAME,CATEGORY)
                if (count($firstLines) > 0 && strpos($firstLines[0], 'ITEM NAME') !== false) {
                    $csvFormat = 'jowaki';
                }

                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    if ($isFirstLine) {
                        $isFirstLine = false;
                        continue; // Skip header
                    }

                    if ($csvFormat === 'jowaki') {
                        // Handle JOWAKI STOCK ITEMS.csv format
                        if (count($data) >= 2) {
                            $itemName = trim($data[0] ?? '');
                            $category = trim($data[1] ?? '');

                            // Skip empty rows
                            if (empty($itemName) || empty($category)) {
                                $skipped++;
                                continue;
                            }

                            // Use category mapping
                            if (isset($categoryMapping[$category])) {
                                $mapping = $categoryMapping[$category];
                                $name = $mapping['name'];
                                $icon = $mapping['icon'];
                                $filter_value = $mapping['filter_value'];
                                $sort_order = 0;
                                $is_active = 1;

                                // Check if category already exists (by name)
                                $checkStmt = $conn->prepare("SELECT id FROM store_categories WHERE name = ?");
                                $checkStmt->bind_param('s', $name);
                                $checkStmt->execute();
                                $existing = $checkStmt->get_result();

                                if ($existing->num_rows === 0) {
                                    // Insert new category
                                    $stmt = $conn->prepare("INSERT INTO store_categories (name, icon, filter_value, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
                                    $stmt->bind_param('sssii', $name, $icon, $filter_value, $sort_order, $is_active);

                                    if ($stmt->execute()) {
                                        $imported++;
                                    } else {
                                        $skipped++;
                                        $errors[] = "Row " . ($imported + $skipped) . ": Database error - " . $stmt->error;
                                    }
                                } else {
                                    $skipped++;
                                    // Don't add to errors since this is expected behavior
                                }
                            } else {
                                $skipped++;
                                $errors[] = "Row " . ($imported + $skipped) . ": Unknown category '$category'";
                            }
                        } else {
                            $skipped++;
                            $errors[] = "Row " . ($imported + $skipped) . ": Insufficient columns";
                        }
                    } else {
                        // Handle standard format (name,icon,filter_value,sort_order,is_active)
                        if (count($data) >= 3) {
                            $name = trim($data[0] ?? '');
                            $icon = trim($data[1] ?? '');
                            $filter_value = trim($data[2] ?? '');
                            $sort_order = isset($data[3]) ? intval(trim($data[3])) : 0;
                            $is_active = isset($data[4]) ? (trim($data[4]) === '1' ? 1 : 0) : 1;

                            // Validate required fields
                            if (empty($name) || empty($icon) || empty($filter_value)) {
                                $skipped++;
                                $errors[] = "Row " . ($imported + $skipped) . ": Missing required fields";
                                continue;
                            }

                            // Check if category already exists (by name)
                            $checkStmt = $conn->prepare("SELECT id FROM store_categories WHERE name = ?");
                            $checkStmt->bind_param('s', $name);
                            $checkStmt->execute();
                            $existing = $checkStmt->get_result();

                            if ($existing->num_rows > 0 && !$clearExisting) {
                                $skipped++;
                                $errors[] = "Row " . ($imported + $skipped) . ": Category '$name' already exists";
                                continue;
                            }

                            // Insert or update category
                            if ($existing->num_rows > 0) {
                                // Update existing
                                $stmt = $conn->prepare("UPDATE store_categories SET icon = ?, filter_value = ?, sort_order = ?, is_active = ? WHERE name = ?");
                                $stmt->bind_param('ssiis', $icon, $filter_value, $sort_order, $is_active, $name);
                            } else {
                                // Insert new
                                $stmt = $conn->prepare("INSERT INTO store_categories (name, icon, filter_value, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
                                $stmt->bind_param('sssii', $name, $icon, $filter_value, $sort_order, $is_active);
                            }

                            if ($stmt->execute()) {
                                $imported++;
                            } else {
                                $skipped++;
                                $errors[] = "Row " . ($imported + $skipped) . ": Database error - " . $stmt->error;
                            }
                        } else {
                            $skipped++;
                            $errors[] = "Row " . ($imported + $skipped) . ": Insufficient columns";
                        }
                    }
                }

                fclose($handle);

                // Commit transaction
                $conn->commit();

                echo json_encode([
                    'success' => true,
                    'message' => "Import completed: $imported imported, $skipped skipped",
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors,
                    'format_detected' => $csvFormat
                ]);

            } catch (Exception $e) {
                // Rollback transaction
                $conn->rollback();
                throw $e;
            }
        } else {
            throw new Exception('Invalid action');
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 