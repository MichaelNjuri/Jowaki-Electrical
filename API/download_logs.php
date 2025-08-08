<?php
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="jowaki_logs_' . date('Y-m-d_H-i-s') . '.txt"');

require_once 'db_connection.php';

try {
    $logs = [];
    
    // Add header
    $logs[] = "Jowaki Electrical Services - System Logs";
    $logs[] = "Generated on: " . date('Y-m-d H:i:s');
    $logs[] = "==========================================";
    $logs[] = "";
    
    // Get admin activity logs
    $logs[] = "ADMIN ACTIVITY LOGS:";
    $logs[] = "====================";
    
    $result = $conn->query("SELECT * FROM admin_activity_log ORDER BY created_at DESC LIMIT 100");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = sprintf(
                "[%s] Admin ID: %s | Action: %s | Details: %s | IP: %s",
                $row['created_at'],
                $row['admin_id'],
                $row['action'],
                $row['details'],
                $row['ip_address']
            );
        }
    } else {
        $logs[] = "No admin activity logs found.";
    }
    
    $logs[] = "";
    $logs[] = "ERROR LOGS:";
    $logs[] = "===========";
    
    // Check for PHP error log
    $error_log_file = 'php_errors.log';
    if (file_exists($error_log_file)) {
        $error_logs = file_get_contents($error_log_file);
        $logs[] = $error_logs;
    } else {
        $logs[] = "No error logs found.";
    }
    
    $logs[] = "";
    $logs[] = "SYSTEM INFORMATION:";
    $logs[] = "===================";
    $logs[] = "PHP Version: " . phpversion();
    $logs[] = "Server Software: " . $_SERVER['SERVER_SOFTWARE'];
    $logs[] = "Database: MySQL";
    $logs[] = "Current Time: " . date('Y-m-d H:i:s');
    
    // Get database statistics
    $logs[] = "";
    $logs[] = "DATABASE STATISTICS:";
    $logs[] = "====================";
    
    $tables = ['users', 'products', 'orders', 'categories', 'system_settings'];
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        if ($result) {
            $row = $result->fetch_assoc();
            $logs[] = "$table: " . $row['count'] . " records";
        }
    }
    
    echo implode("\n", $logs);
    
} catch (Exception $e) {
    echo "Error generating logs: " . $e->getMessage();
}

$conn->close();
?> 