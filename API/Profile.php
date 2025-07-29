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
    // Check if session variables exist
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Check if user is marked as logged in
    if ($_SESSION['logged_in'] !== true) {
        return false;
    }
    
    // Check session timeout (30 minutes)
    $session_timeout = 30 * 60; // 30 minutes in seconds
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        error_log("Session expired for user ID: " . $_SESSION['user_id'], 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
        return false;
    }
    
    return true;
}

// Check if session save path is writable
if (!is_writable(session_save_path())) {
    error_log("Session save path not writable: " . session_save_path(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Server configuration error - session path not writable");
}

// Start session
try {
    session_start();
} catch (Exception $e) {
    error_log("Session start failed: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Session initialization failed");
}

error_log("Profile.php accessed - Session ID: " . session_id(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

// Validate session
if (!isValidSession()) {
    // Clear invalid session data
    $_SESSION = array();
    session_destroy();
    redirectToLogin("Your session has expired. Please log in again.");
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Include database connection
$db_file = __DIR__ . DIRECTORY_SEPARATOR . 'db_connection.php';
if (!file_exists($db_file)) {
    error_log("db_connection.php not found at: " . $db_file, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Database configuration file not found");
}

try {
    require $db_file;
} catch (Exception $e) {
    error_log("Failed to require db_connection.php: " . $e->getMessage(), 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Database configuration error");
}

// Verify database connection
if (!isset($conn) || !$conn) {
    error_log("Database connection not available", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Database connection failed");
}

$userId = $_SESSION['user_id'];
error_log("Loading profile for user ID: $userId", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');

// Prepare and execute query to get user data (only existing columns)
$stmt = $conn->prepare("SELECT first_name, last_name, email, created_at FROM users WHERE id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Database error - prepare failed");
}

if (!$stmt->bind_param("i", $userId)) {
    error_log("Bind param failed: " . $stmt->error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Database error - bind failed");
}

if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Database error - execute failed");
}

$result = $stmt->get_result();
if (!$result) {
    error_log("Get result failed: " . $stmt->error, 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    die("Database error - result failed");
}

if ($user = $result->fetch_assoc()) {
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    $userEmail = $user['email'];
    $userExtra = [
        'phone' => 'Not provided yet',
        'created_at' => $user['created_at'],
        'address' => 'Not provided yet',
        'postal_code' => 'Not provided yet',
        'city' => 'Not provided yet'
    ];
    
    error_log("Profile loaded successfully for: $userEmail", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
} else {
    error_log("User not found in database for ID: $userId", 3, __DIR__ . DIRECTORY_SEPARATOR . 'php_errors.log');
    // Clear session and redirect
    $_SESSION = array();
    session_destroy();
    redirectToLogin("User account not found. Please log in again.");
}

// Clean up database resources
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Jowaki Electrical Services Ltd</title>
    <link rel="stylesheet" href="/jowaki_electrical_srvs/profile.css">
</head>
<body>
    <?php 
    // Include header file - FIXED: Proper PHP syntax
    $header_file = __DIR__ . '/../header.php';
    if (file_exists($header_file)) {
        include($header_file);
    } else {
        echo "<!-- Header file not found at: " . htmlspecialchars($header_file) . " -->";
        // Fallback header
        echo '<header><div class="container"><h1>JOWAKI ELECTRICAL SERVICES LTD</h1></div></header>';
    }
    ?>

    <main>
        <section class="section">
            <div class="container">
                <div class="welcome-message">
                    <div class="welcome-header">
                        <div>
                            <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                            <p>Last login: <?php echo isset($_SESSION['login_time']) ? date('F j, Y g:i A', $_SESSION['login_time']) : 'Unknown'; ?></p>
                        </div>
                        <button onclick="logout()" class="logout-btn">🚪 Logout</button>
                    </div>
                </div>
                
                <h2 class="section-title">Profile Overview</h2>
                <div class="content-grid">
                    <div class="card">
                        <h2><span class="card-icon">👤</span> Personal Information</h2>
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
                            <span class="info-label">Location</span>
                            <span class="info-value" id="profile-location"><?php echo htmlspecialchars($userExtra['city'] !== 'Not provided yet' ? $userExtra['city'] . ', Kenya' : 'Kenya'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Member Since</span>
                            <span class="info-value" id="profile-member-since"><?php echo $userExtra['created_at'] ? date("F j, Y", strtotime($userExtra['created_at'])) : 'Unknown'; ?></span>
                        </div>
                        <a href="#" class="edit-btn" onclick="alert('Edit profile feature coming soon during checkout!')">Edit Profile</a>
                    </div>
                    
                    <div class="card">
                        <h2><span class="card-icon">📍</span> Delivery Address</h2>
                        <div class="info-row">
                            <span class="info-label">Address</span>
                            <span class="info-value" id="address-street"><?php echo htmlspecialchars($userExtra['address']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Postal Code</span>
                            <span class="info-value" id="address-postal"><?php echo htmlspecialchars($userExtra['postal_code']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">City</span>
                            <span class="info-value" id="address-city"><?php echo htmlspecialchars($userExtra['city']); ?></span>
                        </div>
                        <a href="/jowaki_electrical_srvs/Store.html" class="edit-btn">Add Address During Checkout</a>
                    </div>
                    
                    <div class="card">
                        <h2><span class="card-icon">📦</span> Order History</h2>
                        <div id="order-list">
                            <p class="no-orders">No orders yet. <a href="/jowaki_electrical_srvs/Store.html">Start shopping</a> to see your orders here!</p>
                        </div>
                        <a href="/jowaki_electrical_srvs/Store.html" class="edit-btn">Go Shopping</a>
                    </div>
                    
                    <div class="card">
                        <h2><span class="card-icon">🔒</span> Account Security</h2>
                        <div class="info-row">
                            <span class="info-label">Session Status</span>
                            <span class="info-value status-active">Active</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Last Activity</span>
                            <span class="info-value"><?php echo date('F j, Y g:i A', $_SESSION['last_activity']); ?></span>
                        </div>
                        <a href="#" class="edit-btn" onclick="alert('Password change feature will be added soon!')">Change Password</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Jowaki Electrical Services</h4>
                    <p>Quality • Integrity • Perfection</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/jowaki_electrical_srvs/Index.html">Home</a></li>
                        <li><a href="/jowaki_electrical_srvs/Service.html">Services</a></li>
                        <li><a href="/jowaki_electrical_srvs/Store.html">Shop</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p>📞 0721 442 248<br>✉️ kibukush@gmail.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Jowaki Electrical Services Ltd. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="/jowaki_electrical_srvs/api/profile.js"></script>
    <script>
    // Logout function
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            // Clear any client-side data if needed
            localStorage.clear();
            sessionStorage.clear();
            
            // Make logout request
            fetch('/jowaki_electrical_srvs/api/logout.php', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                // Redirect regardless of response
                window.location.href = '/jowaki_electrical_srvs/api/login_form.php?message=' + encodeURIComponent('You have been logged out successfully.');
            })
            .catch(error => {
                console.error('Logout error:', error);
                // Still redirect even if there's an error
                window.location.href = '/jowaki_electrical_srvs/api/login_form.php?message=' + encodeURIComponent('Logged out.');
            });
        }
    }

    // Add session timeout warning
    let sessionWarningShown = false;
    const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes in milliseconds
    const WARNING_TIME = 25 * 60 * 1000; // Show warning at 25 minutes

    setTimeout(() => {
        if (!sessionWarningShown) {
            sessionWarningShown = true;
            if (confirm('Your session will expire in 5 minutes. Would you like to extend it?')) {
                // Make a request to extend session
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
        window.location.href = '/jowaki_electrical_srvs/api/login_form.php?error=' + encodeURIComponent('Session expired. Please log in again.');
    }, SESSION_TIMEOUT);
    </script>
    
    <style>
    /* Logout button styles */
    .welcome-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .logout-btn {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        flex-shrink: 0;
    }
    
    .logout-btn:hover {
        background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
    }
    
    .logout-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(231, 76, 60, 0.3);
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .welcome-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .logout-btn {
            padding: 10px 20px;
            font-size: 14px;
        }
    }
    </style>
</body>
</html>