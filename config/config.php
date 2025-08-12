<?php
// Database Configuration for Hostinger
define("DB_HOST", "localhost"); // Hostinger shared hosting uses localhost
define("DB_NAME", "u383641303_jowaki_db"); // Your database name
define("DB_USER", "u383641303_jowaki"); // Your database username
define("DB_PASS", "Db_password1"); // Your database password

// Site Configuration
define("SITE_NAME", "Jowaki Electrical Services Ltd");
define("SITE_URL", "https://jowakielectrical.com"); // Your actual domain
define("SITE_EMAIL", "info@jowaki.com");

// File Upload Configuration
define("UPLOAD_DIR", "uploads/");
define("MAX_FILE_SIZE", 5 * 1024 * 1024); // 5MB
define("ALLOWED_EXTENSIONS", ["jpg", "jpeg", "png", "gif"]);

// Session Configuration
define("SESSION_LIFETIME", 3600); // 1 hour
define("SESSION_NAME", "jowaki_session");

// Security Configuration
define("CSRF_TOKEN_NAME", "jowaki_csrf");
define("PASSWORD_SALT", "jowaki_salt_2024_production"); // Change this for production

// Email Configuration (if using SMTP)
define("SMTP_HOST", "smtp.gmail.com");
define("SMTP_PORT", 587);
define("SMTP_USER", "your-email@gmail.com");
define("SMTP_PASS", "your-app-password");

// WhatsApp Configuration
define("WHATSAPP_NUMBER", "+254721442248");
define("WHATSAPP_MESSAGE", "Hello Jowaki Electrical, I would like to inquire about your products.");

// Google OAuth Configuration (if using)
define("GOOGLE_CLIENT_ID", "your-google-client-id");
define("GOOGLE_CLIENT_SECRET", "your-google-client-secret");
define("GOOGLE_REDIRECT_URI", SITE_URL . "/auth/google-callback.php");

// Error Reporting (set to false in production)
define("DEBUG_MODE", true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set("display_errors", 0); // Keep errors logged but don't display them
} else {
    error_reporting(0);
    ini_set("display_errors", 0);
}

// Timezone
date_default_timezone_set("Africa/Nairobi");

// Database connection function
function getDbConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            throw new Exception("Connection failed: " . $e->getMessage());
        } else {
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
}

// CSRF Token functions
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Utility functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, "UTF-8");
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isAjaxRequest() {
    return !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && 
           strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest";
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit();
}
?>
