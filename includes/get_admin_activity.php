<?php
session_start();
require_once 'db_connection.php';
require_once 'check_auth.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = getConnection();
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    try {
        // Get query parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $offset = ($page - 1) * $limit;

        // Build WHERE clause for filters
        $where_conditions = [];
        $params = [];
        $param_types = '';

        if (isset($_GET['admin_id']) && !empty($_GET['admin_id'])) {
            $where_conditions[] = 'aal.admin_id = ?';
            $params[] = (int)$_GET['admin_id'];
            $param_types .= 'i';
        }

        if (isset($_GET['action']) && !empty($_GET['action'])) {
            $where_conditions[] = 'aal.action LIKE ?';
            $params[] = '%' . $_GET['action'] . '%';
            $param_types .= 's';
        }

        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
            $where_conditions[] = 'DATE(aal.created_at) >= ?';
            $params[] = $_GET['date_from'];
            $param_types .= 's';
        }

        if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
            $where_conditions[] = 'DATE(aal.created_at) <= ?';
            $params[] = $_GET['date_to'];
            $param_types .= 's';
        }

        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }

        // Get total count
        $count_sql = "
            SELECT COUNT(*) as total 
            FROM admin_activity_log aal 
            $where_clause
        ";
        
        $count_stmt = $conn->prepare($count_sql);
        if (!empty($params)) {
            $count_stmt->bind_param($param_types, ...$params);
        }
        $count_stmt->execute();
        $total_result = $count_stmt->get_result();
        $total = $total_result->fetch_assoc()['total'];
        $count_stmt->close();

        // Get activity logs
        $sql = "
            SELECT 
                aal.id,
                aal.action,
                aal.details,
                aal.ip_address,
                aal.created_at,
                CONCAT(au.first_name, ' ', au.last_name) as admin_name,
                au.username
            FROM admin_activity_log aal
            LEFT JOIN admin_users au ON aal.admin_id = au.id
            $where_clause
            ORDER BY aal.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $conn->prepare($sql);
        
        // Add limit and offset to params
        $all_params = array_merge($params, [$limit, $offset]);
        $all_param_types = $param_types . 'ii';
        
        if (!empty($all_params)) {
            $stmt->bind_param($all_param_types, ...$all_params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $activities = [];
        
        while ($row = $result->fetch_assoc()) {
            $activities[] = [
                'id' => $row['id'],
                'action' => $row['action'],
                'details' => $row['details'],
                'ip_address' => $row['ip_address'],
                'created_at' => $row['created_at'],
                'admin_name' => $row['admin_name'] ?: 'System',
                'username' => $row['username']
            ];
        }

        $stmt->close();
        $conn->close();

        echo json_encode([
            'success' => true,
            'activities' => $activities,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving activity: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
