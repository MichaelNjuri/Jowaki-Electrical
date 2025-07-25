<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!is_writable(session_save_path())) {
    error_log("Session save path not writable: " . session_save_path());
    die(json_encode(['error' => 'Server configuration error']));
}
session_start();
header('Content-Type: application/json');

require 'db_connection.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    error_log("Session not found for user_id: " . ($_SESSION['user_id'] ?? 'none'));
    http_response_code(401);
    echo json_encode(['error' => 'Session not found. Please log in again.']);
    exit;
}

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, created_at, address, postal_code, city FROM users WHERE id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'fullName' => $row['first_name'] . ' ' . $row['last_name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'memberSince' => $row['created_at'],
        'address' => $row['address'] ?? 'Not provided yet',
        'postal_code' => $row['postal_code'] ?? 'Not provided yet',
        'city' => $row['city'] ?? 'Not provided yet'
    ]);
} else {
    error_log("User not found for ID: $id");
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
}

$stmt->close();
$conn->close();
?>