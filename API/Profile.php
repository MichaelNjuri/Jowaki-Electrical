<?php
// Session and error configuration
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
error_reporting(E_ALL);

// Function to redirect with error message
function redirectToLogin($error = "Please log in to access your profile.") {
    error_log("Redirecting to login: $error", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    header("Location: /jowaki_electrical_srvs/login.html?error=" . urlencode($error));
    exit;
}

// Function to check session validity
function isValidSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !isset($_SESSION['last_activity'])) {
        return false;
    }
    
    if ($_SESSION['logged_in'] !== true) {
        return false;
    }
    
    $session_timeout = 30 * 60; // 30 minutes
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        error_log("Session expired for user ID: " . $_SESSION['user_id'], 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        return false;
    }
    
    return true;
}

// Start session
try {
    session_start();
} catch (Exception $e) {
    error_log("Session start failed: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Session initialization failed");
}

// Validate session
if (!isValidSession()) {
    $_SESSION = array();
    session_destroy();
    redirectToLogin("Your session has expired. Please log in again.");
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Include database connection
require_once 'db_connection.php';

$userId = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, address, city, postal_code, created_at FROM users WHERE id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Database error - prepare failed");
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    $userEmail = $user['email'];
    $userExtra = [
        'phone' => $user['phone'] ?? 'Not provided',
        'created_at' => $user['created_at'] ?? 'Unknown',
        'address' => $user['address'] ?? 'Not provided',
        'postal_code' => $user['postal_code'] ?? 'Not provided',
        'city' => $user['city'] ?? 'Not provided'
    ];
} else {
    $_SESSION = array();
    session_destroy();
    redirectToLogin("User account not found. Please log in again.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Jowaki Electrical Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            padding-top: 100px; /* Account for fixed header */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Welcome Section */
        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .welcome-text h1 {
            color: #2d3748;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-text p {
            color: #718096;
            font-size: 1.1rem;
        }

        .logout-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
        }

        /* Info Rows */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 0.95rem;
        }

        .info-value {
            color: #2d3748;
            font-weight: 500;
            text-align: right;
            max-width: 60%;
            word-wrap: break-word;
        }

        .info-value.complete {
            color: #059669;
        }

        .info-value.incomplete {
            color: #dc2626;
            font-style: italic;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            margin-top: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #edf2f7;
            transform: translateY(-2px);
        }

        /* Order List */
        .order-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .order-item {
            background: #f7fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .order-item:hover {
            background: #edf2f7;
            transform: translateX(5px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .order-id {
            font-weight: 700;
            color: #2d3748;
        }

        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-processing {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-delivered {
            background: #d1fae5;
            color: #059669;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

        .order-details {
            color: #718096;
            font-size: 0.9rem;
        }

        .order-details p {
            margin: 5px 0;
        }

        .order-actions {
            margin-top: 15px;
            display: flex;
            justify-content: flex-end;
        }

        .view-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Loading State */
        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #718096;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #718096;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Profile Completion Message */
        .completion-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .completion-message.success {
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid #059669;
            color: #059669;
        }

        .completion-message.warning {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid #dc2626;
            color: #dc2626;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }

        .modal.active .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
            padding: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a5568;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header {
                padding: 20px;
                text-align: center;
            }

            .welcome-text h1 {
                font-size: 2rem;
            }

            .main-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .card {
                padding: 20px;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .info-value {
                text-align: left;
                max-width: 100%;
            }

            .modal-content {
                width: 95%;
                padding: 20px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        
        /* CSS Variables for consistent theming */
        :root {
            --primary-color: hsl(207, 90%, 54%);
            --secondary-color: hsl(45, 93%, 47%);
            --accent-color: hsl(151, 55%, 42%);
            --primary-dark: hsl(207, 90%, 40%);
            --secondary-dark: hsl(45, 93%, 35%);
            --accent-dark: hsl(151, 55%, 30%);
            --text-dark: hsl(216, 12%, 20%);
            --text-light: hsl(216, 12%, 50%);
            --background-light: hsl(0, 0%, 98%);
            --background-white: hsl(0, 0%, 100%);
            --border-light: hsl(216, 12%, 90%);
            --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 8px 24px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 16px 48px rgba(0, 0, 0, 0.15);
            --border-radius: 1rem;
            --transition: all 0.3s ease;
        }

        /* Modern Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Header scroll effect */
        header.scrolled {
            min-height: 60px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        header.scrolled .header-content {
            padding: 0.5rem 0;
            min-height: 60px;
        }

        header.scrolled .logo-img {
            width: 45px;
            height: 45px;
        }

        header.scrolled .logo-text {
            font-size: 1.2rem;
        }

        .header-content {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 2rem;
            padding: 1rem 0;
            min-height: 80px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
        }

        /* Logo Section */
        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            transition: var(--transition);
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo-img {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: var(--shadow-light);
            transition: var(--transition);
        }

        .logo:hover .logo-img {
            box-shadow: var(--shadow-medium);
            transform: rotate(5deg);
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.025em;
            line-height: 1.2;
            max-width: 200px;
        }

        /* Navigation Section */
        .main-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            white-space: nowrap;
            background: transparent;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50px;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
        }

        .nav-link:hover::before {
            opacity: 0.1;
            transform: scale(1);
        }

        .nav-link:hover {
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        /* Special Navigation Buttons */
        .shop-link {
            background: linear-gradient(135deg, var(--secondary-color), var(--secondary-dark));
            color: white;
            font-weight: 600;
            box-shadow: var(--shadow-light);
        }

        .shop-link::before {
            display: none;
        }

        .shop-link:hover {
            background: linear-gradient(135deg, var(--secondary-dark), var(--secondary-color));
            color: white;
            box-shadow: var(--shadow-medium);
            transform: translateY(-3px);
        }

        .login-link, .profile-link {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            font-weight: 600;
            box-shadow: var(--shadow-light);
        }

        .login-link::before, .profile-link::before {
            display: none;
        }

        .login-link:hover, .profile-link:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            color: white;
            box-shadow: var(--shadow-medium);
            transform: translateY(-3px);
        }

        /* User Actions */
        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logout-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            color: white;
            box-shadow: var(--shadow-medium);
            transform: translateY(-3px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                grid-template-columns: auto 1fr auto;
                gap: 1rem;
            }

            .main-nav {
                display: none;
            }

            .logo-text {
                font-size: 0.9rem;
                max-width: 140px;
            }

            .logo-img {
                width: 40px;
                height: 40px;
            }

            body {
                padding-top: 80px;
            }
        }

        @media (max-width: 480px) {
            .header-content {
                padding: 0.75rem 0;
            }

            .logo-text {
                display: none;
            }

            .logo-img {
                width: 35px;
                height: 35px;
            }
        }
    </style>
</head>
<body>
    <!-- Main Header -->
    <header id="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo and Name -->
                <div class="logo">
                    <img src="/jowaki_electrical_srvs/Logo.jpg" alt="Jowaki Logo" class="logo-img" />
                    <span class="logo-text">JOWAKI ELECTRICAL SERVICES LTD</span>
                </div>
                
                <!-- Navigation -->
                <nav class="main-nav">
                    <a href="/jowaki_electrical_srvs/Index.php" class="nav-link">Home</a>
                    <a href="/jowaki_electrical_srvs/Service.php" class="nav-link">Services</a>
                    <a href="/jowaki_electrical_srvs/Store.php" class="nav-link shop-link">🛒 Shop</a>
                    <a href="/jowaki_electrical_srvs/API/Profile.php" class="nav-link profile-link">👤 Profile</a>
                </nav>
                
                <!-- User Actions -->
                <div class="user-actions">
                    <button onclick="logout()" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                <p>Last login: <?php echo isset($_SESSION['login_time']) ? date('F j, Y g:i A', $_SESSION['login_time']) : 'Unknown'; ?></p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Personal Information Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2 class="card-title">Personal Information</h2>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Full Name</span>
                    <span class="info-value" id="profile-name"><?php echo htmlspecialchars($userName); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value" id="profile-email"><?php echo htmlspecialchars($userEmail); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone</span>
                    <span class="info-value" id="profile-phone"><?php echo htmlspecialchars($userExtra['phone']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Member Since</span>
                    <span class="info-value" id="profile-member-since">
                        <?php echo $userExtra['created_at'] ? date("F j, Y", strtotime($userExtra['created_at'])) : 'Unknown'; ?>
                    </span>
                </div>
                
                <button class="btn btn-primary" onclick="editProfile()">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                
                <div id="profile-completion-message"></div>
            </div>

            <!-- Delivery Address Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h2 class="card-title">Delivery Address</h2>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Address</span>
                    <span class="info-value" id="address-street"><?php echo htmlspecialchars($userExtra['address']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">City</span>
                    <span class="info-value" id="address-city"><?php echo htmlspecialchars($userExtra['city']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Postal Code</span>
                    <span class="info-value" id="address-postal"><?php echo htmlspecialchars($userExtra['postal_code']); ?></span>
                </div>
                
                <a href="/jowaki_electrical_srvs/Store.html" class="btn btn-secondary">
                    <i class="fas fa-shopping-cart"></i> Go Shopping
                </a>
            </div>

            <!-- Order History Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h2 class="card-title">Order History</h2>
                </div>
                
                <div id="order-list">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p>Loading orders...</p>
                    </div>
                </div>
                
                <a href="/jowaki_electrical_srvs/Store.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Order
                </a>
            </div>

            <!-- Account Security Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h2 class="card-title">Account Security</h2>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Session Status</span>
                    <span class="info-value status-active">
                        <i class="fas fa-circle" style="color: #059669;"></i> Active
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Last Activity</span>
                    <span class="info-value"><?php echo date('F j, Y g:i A', $_SESSION['last_activity']); ?></span>
                </div>
                
                <button class="btn btn-secondary" onclick="changePassword()">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="edit-profile-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Profile</h3>
                <button class="close-btn" onclick="closeEditForm()">&times;</button>
            </div>
            
            <form id="edit-profile-form">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-input form-textarea"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="postal_code" class="form-input">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditForm()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="profile.js"></script>
    <script>
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                localStorage.clear();
                sessionStorage.clear();
                
                fetch('./Logout.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => {
                    window.location.href = '/jowaki_electrical_srvs/login_form.php?message=' + encodeURIComponent('You have been logged out successfully.');
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = '/jowaki_electrical_srvs/login_form.php?message=' + encodeURIComponent('Logged out.');
                });
            }
        }

        // Change password function
        function changePassword() {
            alert('Password change feature will be implemented soon!');
        }

        // Session timeout warning
        let sessionWarningShown = false;
        const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes
        const WARNING_TIME = 25 * 60 * 1000; // Show warning at 25 minutes

        setTimeout(() => {
            if (!sessionWarningShown) {
                sessionWarningShown = true;
                if (confirm('Your session will expire in 5 minutes. Would you like to extend it?')) {
                    fetch('extend-session.php', {
                        method: 'POST',
                        credentials: 'include'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            sessionWarningShown = false;
                            console.log('Session extended');
                        } else {
                            alert('Failed to extend session. Please save your work and refresh the page.');
                        }
                    })
                    .catch(error => {
                        console.error('Error extending session:', error);
                    });
                }
            }
        }, WARNING_TIME);

        // Auto-logout after session timeout
        setTimeout(() => {
            alert('Your session has expired. You will be redirected to the login page.');
            window.location.href = '/jowaki_electrical_srvs/login_form.php?error=' + encodeURIComponent('Session expired. Please log in again.');
        }, SESSION_TIMEOUT);

        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>