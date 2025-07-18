<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!is_writable(session_save_path())) {
    die("Session save path is not writable: " . session_save_path());
}
session_start();
header('Content-Type: application/json');

require 'db_connection.php';

// Debug: check if session is available
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Session not found. Please log in again.']);
    exit();
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, created_at, address, postal_code, city FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'fullName' => $row['first_name'] . ' ' . $row['last_name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'memberSince' => $row['created_at'],
        'address' => $row['address'] ?? '',
        'postalCode' => $row['postal_code'] ?? '',
        'city' => $row['city'] ?? ''
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
}

$stmt->close();
$conn->close();
