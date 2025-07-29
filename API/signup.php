<?php
session_start();
require 'db_connection.php';

// Function to display error message
function displayError($message, $redirect_params = '') {
    $redirect_url = "login.html?error=" . urlencode($message);
    if (!empty($redirect_params)) {
        $redirect_url .= "&" . $redirect_params;
    }
    header("Location: " . $redirect_url);
    exit;
}

// Function to display success message with redirect
function displaySuccess($message, $redirect_params = '') {
    $redirect_url = "login.html?success=true&message=" . urlencode($message);
    if (!empty($redirect_params)) {
        $redirect_url .= "&" . $redirect_params;
    }
    header("Location: " . $redirect_url);
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
    
    // Get redirect parameters
    $redirect = $_POST['redirect'] ?? '';
    $return_to_checkout = $_POST['return_to_checkout'] ?? '';
    
    // Build redirect parameters string
    $redirect_params = '';
    if (!empty($redirect)) {
        $redirect_params .= "redirect=" . urlencode($redirect);
    }
    if (!empty($return_to_checkout)) {
        $redirect_params .= ($redirect_params ? "&" : "") . "return_to_checkout=" . urlencode($return_to_checkout);
    }

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
            displayError("Database error: " . $conn->error, $redirect_params);
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
        displayError($errorMessage, $redirect_params);
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        displayError("Database error: " . $conn->error, $redirect_params);
    }
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $phone, $hashedPassword);

    if ($stmt->execute()) {
        // Account created successfully - now auto-login the user if returning to checkout
        if ($redirect === 'store' && $return_to_checkout === 'true') {
            // Get the new user ID
            $user_id = $conn->insert_id;
            
            // Set up session for auto-login
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            
            session_regenerate_id(true);
            
            // Redirect directly to store with checkout return
            header("Location: store.php?return_to_checkout=true&new_account=true");
            exit;
        } else {
            // Regular signup - redirect to login page with success message
            displaySuccess("Account created successfully! Please login.", $redirect_params);
        }
    } else {
        error_log("Database insertion error: " . $stmt->error);
        displayError("Something went wrong. Please try again.", $redirect_params);
    }

    $stmt->close();
    $conn->close();
} else {
    displayError("Invalid request.");
}
?>