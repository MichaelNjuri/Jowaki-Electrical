<?php
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
session_start();

require 'db_connection.php';

header('Content-Type: application/json');

function sendError($message, $httpCode = 400) {
    http_response_code($httpCode);
    echo json_encode(['error' => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendError("Invalid request method", 405);
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    sendError("Please fill in all fields");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError("Please enter a valid email address");
}

if (!$conn) {
    sendError("Database connection failed. Please try again later", 500);
}

try {
    $stmt = $conn->prepare("SELECT id, password, first_name, last_name FROM users WHERE email = ?");
    if (!$stmt) {
        sendError("Database error: " . $conn->error, 500);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['logged_in'] = true;
            
            session_regenerate_id(true);
            
            echo json_encode(['success' => true, 'redirect' => '/Profile.php']);
            exit;
        } else {
            sendError("Invalid email or password");
        }
    } else {
        sendError("Invalid email or password");
    }
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    sendError("An error occurred. Please try again later", 500);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>