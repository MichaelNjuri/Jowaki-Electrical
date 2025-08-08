<?php
// Contact Form Handler
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include email service and database connection
require_once 'email_service.php';
require_once 'db_connection.php';

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Function to send error response
function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse(['success' => false, 'error' => $message], $statusCode);
}

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendErrorResponse('Only POST method allowed', 405);
    }

    // Get and validate input
    $raw_input = file_get_contents('php://input');
    if (empty($raw_input)) {
        sendErrorResponse('No input data received');
    }

    $input = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse('Invalid JSON data');
    }

    // Validate required fields
    $required_fields = ['name', 'email', 'phone', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            sendErrorResponse("Missing required field: $field");
        }
    }

    // Sanitize and validate input
    $name = filter_var(trim($input['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
    $phone = filter_var(trim($input['phone']), FILTER_SANITIZE_STRING);
    $subject = filter_var(trim($input['subject']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($input['message']), FILTER_SANITIZE_STRING);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendErrorResponse('Invalid email address');
    }

    // Validate message length
    if (strlen($message) < 10) {
        sendErrorResponse('Message must be at least 10 characters long');
    }

    if (strlen($message) > 2000) {
        sendErrorResponse('Message must be less than 2000 characters');
    }

    // Prepare form data for email
    $form_data = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => $message,
        'submitted_at' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];

    // Send email notification to admin
    $admin_email = 'jowakielectricalsrvs@gmail.com'; // You can make this configurable
    $email_sent = sendContactFormNotification($form_data, $admin_email);

    // Store contact form message in database for admin dashboard
    try {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, submitted_at, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $phone, $subject, $message, $form_data['submitted_at'], $form_data['ip_address']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error storing contact message: " . $e->getMessage());
    }

    // Log the contact form submission
    error_log("Contact form submitted: " . json_encode($form_data));

    // Send success response
    sendJsonResponse([
        'success' => true,
        'message' => 'Thank you for your message! We will get back to you soon.',
        'email_sent' => $email_sent
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("Error in contact_form.php: " . $e->getMessage());
    
    // Send error response
    sendErrorResponse('Internal server error: ' . $e->getMessage(), 500);
} catch (Error $e) {
    // Handle fatal errors
    error_log("Fatal error in contact_form.php: " . $e->getMessage());
    
    sendErrorResponse('Server error occurred', 500);
}
?>
