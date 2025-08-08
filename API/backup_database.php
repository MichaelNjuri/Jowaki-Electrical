<?php
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="jowaki_backup_' . date('Y-m-d_H-i-s') . '.sql"');

require_once 'db_connection.php';

try {
    // Get database configuration
    $host = $conn->host_info;
    $database = $conn->database;
    
    // Create backup using mysqldump
    $backup_file = tempnam(sys_get_temp_dir(), 'backup_');
    $command = "mysqldump --host=localhost --user=root --password= --database=jowaki_electrical_srvs > $backup_file";
    
    exec($command, $output, $return_var);
    
    if ($return_var === 0 && file_exists($backup_file)) {
        // Read and output the backup file
        $backup_content = file_get_contents($backup_file);
        echo $backup_content;
        
        // Clean up temporary file
        unlink($backup_file);
    } else {
        // Fallback: create a simple backup with table structures
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        
        while ($row = $result->fetch_array()) {
            $table_name = $row[0];
            
            // Get table structure
            $create_result = $conn->query("SHOW CREATE TABLE `$table_name`");
            $create_row = $create_result->fetch_array();
            $tables[] = $create_row[1] . ";\n";
            
            // Get table data
            $data_result = $conn->query("SELECT * FROM `$table_name`");
            while ($data_row = $data_result->fetch_assoc()) {
                $columns = array_keys($data_row);
                $values = array_map(function($value) use ($conn) {
                    if ($value === null) return 'NULL';
                    return "'" . $conn->real_escape_string($value) . "'";
                }, array_values($data_row));
                
                $tables[] = "INSERT INTO `$table_name` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
        }
        
        echo "-- Jowaki Electrical Services Database Backup\n";
        echo "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        echo implode("\n", $tables);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo "-- Error creating backup: " . $e->getMessage() . "\n";
}

$conn->close();
?> 