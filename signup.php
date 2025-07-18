<?php
session_start();
require 'db_connection.php';

// Function to display error message
function displayError($message) {
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h2>Error</h2><p>" . htmlspecialchars($message) . "</p>";
    echo "<a href='login.html'>Back to Login</a></body></html>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect and sanitize inputs
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $terms = isset($_POST['terms']);

    // Initialize errors array
    $errors = [];

    // Validate required fields
    if (empty($firstName)) {
        $errors[] = "First name is required.";
    } elseif (!preg_match('/^[A-Za-z\s]{2,}$/', $firstName)) {
        $errors[] = "First name must be at least 2 characters long and contain only letters and spaces.";
    }

    if (empty($lastName)) {
        $errors[] = "Last name is required.";
    } elseif (!preg_match('/^[A-Za-z\s]{2,}$/', $lastName)) {
        $errors[] = "Last name must be at least 2 characters long and contain only letters and spaces.";
    }

    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    } else {
        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
        if (!preg_match('/^[\+]?[0-9]{10,15}$/', $cleanPhone)) {
            $errors[] = "Please enter a valid phone number (10-15 digits).";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/(?=.*[a-z])/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/(?=.*[A-Z])/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/(?=.*\d)/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }

    if (empty($confirmPassword)) {
        $errors[] = "Password confirmation is required.";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (!$terms) {
        $errors[] = "You must agree to the Terms of Service and Privacy Policy.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$check) {
            displayError("Database error: " . $conn->error);
        }
        $check->bind_param("s", $email);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $errors[] = "Email already registered.";
        }
        $check->close();
    }

    // If there are errors, redirect back with error message
    if (!empty($errors)) {
        $errorMessage = implode(" ", $errors);
        header("Location: login.html?error=" . urlencode($errorMessage));
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        displayError("Database error: " . $conn->error);
    }
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $phone, $hashedPassword);

    if ($stmt->execute()) {
        // Redirect to login page with success message
        header("Location: login.html?success=true&message=" . urlencode("Account created successfully! Please login."));
    } else {
        error_log("Database insertion error: " . $stmt->error);
        displayError("Something went wrong. Please try again.");
    }

    $stmt->close();
    $conn->close();
} else {
    displayError("Invalid request.");
}
?>