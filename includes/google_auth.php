<?php
/**
 * Google OAuth Authentication Handler
 * Jowaki Electrical Services
 */

// Start session
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS in production
session_start();

// Include configuration and database connection
require_once 'google_config.php';
require_once 'db_connection.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

// Function to redirect with error
function redirectWithError($error, $redirect = '') {
    $params = ['error=' . urlencode($error)];
    if (!empty($redirect)) {
        $params[] = 'redirect=' . urlencode($redirect);
    }
    header('Location: /jowaki_electrical_srvs/login.html?' . implode('&', $params));
    exit;
}

// Function to redirect with success
function redirectWithSuccess($message, $redirect = '') {
    $params = ['success=' . urlencode($message)];
    if (!empty($redirect)) {
        $params[] = 'redirect=' . urlencode($redirect);
    }
    header('Location: /jowaki_electrical_srvs/login.html?' . implode('&', $params));
    exit;
}

// Function to redirect to store
function redirectToStore($returnToCheckout = false) {
    $url = '/jowaki_electrical_srvs/Store.php';
    if ($returnToCheckout) {
        $url .= '?return_to_checkout=true';
    }
    header('Location: ' . $url);
    exit;
}

// Function to redirect to profile
function redirectToProfile() {
    header('Location: /jowaki_electrical_srvs/API/Profile.php');
    exit;
}

// Check if this is an initial OAuth request (no code parameter)
if (!isset($_GET['code'])) {
    // Get redirect parameters
    $redirect = $_GET['redirect'] ?? '';
    $returnToCheckout = $_GET['return_to_checkout'] ?? '';
    $signup = $_GET['signup'] ?? '';
    
    // Create state parameter to preserve redirect info
    $state = [];
    if (!empty($redirect)) $state['redirect'] = $redirect;
    if (!empty($returnToCheckout)) $state['return_to_checkout'] = $returnToCheckout;
    if (!empty($signup)) $state['signup'] = $signup;
    
    $stateParam = !empty($state) ? base64_encode(json_encode($state)) : '';
    
    // Redirect to Google OAuth
    $authUrl = getGoogleAuthUrl($stateParam);
    header('Location: ' . $authUrl);
    exit;
}

// Handle OAuth callback (code parameter present)
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

if (empty($code)) {
    redirectWithError('Authorization code not received from Google');
}

// Decode state parameter to get redirect info
$redirectInfo = [];
if (!empty($state)) {
    try {
        $redirectInfo = json_decode(base64_decode($state), true) ?? [];
    } catch (Exception $e) {
        error_log("Error decoding state parameter: " . $e->getMessage());
    }
}

$redirect = $redirectInfo['redirect'] ?? '';
$returnToCheckout = $redirectInfo['return_to_checkout'] ?? '';
$signup = $redirectInfo['signup'] ?? '';

// Exchange authorization code for access token
$tokenData = getGoogleAccessToken($code);
if (!$tokenData || !isset($tokenData['access_token'])) {
    error_log("Failed to get access token: " . json_encode($tokenData));
    redirectWithError('Failed to authenticate with Google', $redirect);
}

// Get user information from Google
$userInfo = getGoogleUserInfo($tokenData['access_token']);
if (!$userInfo) {
    error_log("Failed to get user info from Google");
    redirectWithError('Failed to get user information from Google', $redirect);
}

// Extract user data
$googleId = $userInfo['id'] ?? '';
$email = $userInfo['email'] ?? '';
$firstName = $userInfo['given_name'] ?? '';
$lastName = $userInfo['family_name'] ?? '';
$picture = $userInfo['picture'] ?? '';

if (empty($email)) {
    redirectWithError('Email address not provided by Google', $redirect);
}

// Check if user exists in database
$stmt = $conn->prepare("SELECT id, first_name, last_name, google_id FROM users WHERE email = ?");
if (!$stmt) {
    error_log("Database prepare error: " . $conn->error);
    redirectWithError('Database error occurred', $redirect);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // User exists - update Google ID if not set
    if (empty($user['google_id']) && !empty($googleId)) {
        $updateStmt = $conn->prepare("UPDATE users SET google_id = ? WHERE id = ?");
        if ($updateStmt) {
            $updateStmt->bind_param("si", $googleId, $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }
    
    // Log in existing user
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['auth_method'] = 'google';
    
    session_regenerate_id(true);
    
    // Redirect based on context - default to store
    if ($redirect === 'profile') {
        redirectToProfile();
    } else {
        redirectToStore($returnToCheckout === 'true');
    }
    
} else {
    // User doesn't exist - create new account if signup is allowed
    if ($signup === 'true') {
        // Create new user account
        $insertStmt = $conn->prepare("INSERT INTO users (email, first_name, last_name, google_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        if (!$insertStmt) {
            error_log("Database insert prepare error: " . $conn->error);
            redirectWithError('Failed to create account', $redirect);
        }
        
        $insertStmt->bind_param("ssss", $email, $firstName, $lastName, $googleId);
        
        if ($insertStmt->execute()) {
            $userId = $conn->insert_id;
            
            // Log in new user
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['auth_method'] = 'google';
            
            session_regenerate_id(true);
            
            // Redirect based on context
            if ($redirect === 'store') {
                redirectToStore($returnToCheckout === 'true');
            } else {
                redirectToProfile();
            }
            
        } else {
            error_log("Database insert error: " . $insertStmt->error);
            redirectWithError('Failed to create account', $redirect);
        }
        
        $insertStmt->close();
        
    } else {
        // Signup not allowed - redirect to login with error
        redirectWithError('No account found with this email. Please sign up first.', $redirect);
    }
}

$stmt->close();
$conn->close();
?>
