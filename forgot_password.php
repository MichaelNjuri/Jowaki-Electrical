<?php
session_start();
require 'includes/db_connection.php';

// Function to display error message
function displayError($message) {
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h2>Error</h2><p>" . htmlspecialchars($message) . "</p>";
             echo "<a href='login_form.php'>Back to Login</a></body></html>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                 header("Location: login_form.php?error=" . urlencode("Please enter a valid email address."));
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        displayError("Database error: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Generate reset token (simplified example)
        $token = bin2hex(random_bytes(32));
        // Store token in database or send email (implement email sending here)
        error_log("Reset token for $email: $token"); // Placeholder for email sending
                 header("Location: login_form.php?success=true&message=" . urlencode("Password reset link sent to your email."));
    } else {
                 header("Location: login_form.php?error=" . urlencode("Email not found."));
    }

    $stmt->close();
    $conn->close();
} else {
    displayError("Invalid request.");
}
?>