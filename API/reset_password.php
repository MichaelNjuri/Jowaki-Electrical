<?php
session_start();
require 'db_connection.php';

// Function to display error message
function displayError($message) {
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h2>Error</h2><p>" . htmlspecialchars($message) . "</p>";
    echo "<a href='login.php'>Back to Login</a></body></html>";
    exit;
}

// Function to send JSON response (for AJAX requests)
function sendJsonResponse($success, $message, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Also check content type for JSON requests
$isJson = isset($_SERVER['CONTENT_TYPE']) && 
          strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Parse input data
    if ($isJson) {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
    } else {
        $email = trim($_POST['email'] ?? '');
    }

    // Validate email
    if (empty($email)) {
        if ($isAjax || $isJson) {
            sendJsonResponse(false, "Please enter your email address", 400);
        } else {
            header("Location: login.php?error=" . urlencode("Please enter your email address"));
            exit;
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($isAjax || $isJson) {
            sendJsonResponse(false, "Please enter a valid email address", 400);
        } else {
            header("Location: login.php?error=" . urlencode("Please enter a valid email address"));
            exit;
        }
    }

    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
    if (!$stmt) {
        if ($isAjax || $isJson) {
            sendJsonResponse(false, "Database error occurred", 500);
        } else {
            displayError("Database error: " . $conn->error);
        }
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        // For security, don't reveal if email exists or not
        error_log("Password reset requested for non-existent email: $email");
        
        if ($isAjax || $isJson) {
            sendJsonResponse(true, "If an account with that email exists, a password reset link has been sent to your email address.");
        } else {
            header("Location: login.php?success=true&message=" . urlencode("If an account with that email exists, a password reset link has been sent to your email address."));
            exit;
        }
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

    // Create password_resets table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        used TINYINT(1) DEFAULT 0,
        INDEX idx_email (email),
        INDEX idx_token (token)
    )";
    
    if (!$conn->query($createTable)) {
        error_log("Failed to create password_resets table: " . $conn->error);
        if ($isAjax || $isJson) {
            sendJsonResponse(false, "Database setup error", 500);
        } else {
            displayError("Database setup error");
        }
    }

    // Delete any existing reset tokens for this email
    $deleteOld = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    if ($deleteOld) {
        $deleteOld->bind_param("s", $email);
        $deleteOld->execute();
        $deleteOld->close();
    }

    // Store reset token in database
    $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    if (!$stmt) {
        if ($isAjax || $isJson) {
            sendJsonResponse(false, "Database error occurred", 500);
        } else {
            displayError("Database error: " . $conn->error);
        }
    }

    $stmt->bind_param("sss", $email, $resetToken, $resetExpiry);

    if (!$stmt->execute()) {
        error_log("Failed to insert password reset token: " . $stmt->error);
        if ($isAjax || $isJson) {
            sendJsonResponse(false, "Failed to process reset request", 500);
        } else {
            displayError("Failed to process reset request");
        }
    }

    $stmt->close();

    // Create reset link
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $resetLink = $protocol . "://" . $host . dirname($_SERVER['PHP_SELF']) . "/reset_password_form.php?token=" . $resetToken;

    // Log the reset link (in production, you would send this via email)
    error_log("Password reset link for $email: $resetLink");

    // Here you would send an email with the reset link
    // For demonstration, we'll create a simple email function
    $emailSent = sendResetEmail($email, $user['first_name'], $resetLink);

    if ($emailSent) {
        if ($isAjax || $isJson) {
            sendJsonResponse(true, "A password reset link has been sent to your email address. Please check your inbox.");
        } else {
            header("Location: login.php?success=true&message=" . urlencode("A password reset link has been sent to your email address. Please check your inbox."));
            exit;
        }
    } else {
        // Even if email fails, don't reveal it for security
        if ($isAjax || $isJson) {
            sendJsonResponse(true, "If an account with that email exists, a password reset link has been sent.");
        } else {
            header("Location: login.php?success=true&message=" . urlencode("If an account with that email exists, a password reset link has been sent."));
            exit;
        }
    }

} else {
    if ($isAjax || $isJson) {
        sendJsonResponse(false, "Invalid request method", 405);
    } else {
        displayError("Invalid request method");
    }
}

// Simple email function (replace with proper email service in production)
function sendResetEmail($email, $firstName, $resetLink) {
    $to = $email;
    $subject = "Password Reset - Jowaki Electrical Services";
    
    $message = "Hi " . htmlspecialchars($firstName) . ",\n\n";
    $message .= "You requested a password reset for your account at Jowaki Electrical Services.\n\n";
    $message .= "Click the link below to reset your password:\n";
    $message .= $resetLink . "\n\n";
    $message .= "This link will expire in 1 hour for security reasons.\n\n";
    $message .= "If you didn't request this password reset, please ignore this email. Your password will remain unchanged.\n\n";
    $message .= "Best regards,\n";
    $message .= "Jowaki Electrical Services Team";
    
    $headers = "From: noreply@jowakielectrical.com\r\n";
    $headers .= "Reply-To: support@jowakielectrical.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Use PHP's mail function (basic - consider using PHPMailer for production)
    return mail($to, $subject, $message, $headers);
}

$conn->close();
?>