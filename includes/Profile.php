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
        'address' => $user['address'] ?? 'Not provided',
        'city' => $user['city'] ?? 'Not provided',
        'postal_code' => $user['postal_code'] ?? 'Not provided',
        'created_at' => $user['created_at'] ?? 'Unknown'
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/jowaki_electrical_srvs/profile.css">
</head>
<body>
    <!-- Header -->
    <?php include 'header_include.php'; ?>

    <div class="profile-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($userName); ?></h3>
                    <p><?php echo htmlspecialchars($userEmail); ?></p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item active" data-section="overview">
                        <a href="#overview" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>Overview</span>
                        </a>
                    </li>
                    <li class="nav-item" data-section="personal">
                        <a href="#personal" class="nav-link">
                            <i class="fas fa-user"></i>
                            <span>Personal Info</span>
                        </a>
                    </li>
                    <li class="nav-item" data-section="orders">
                        <a href="#orders" class="nav-link">
                            <i class="fas fa-shopping-bag"></i>
                            <span>Order History</span>
                        </a>
                    </li>
                    <li class="nav-item" data-section="address">
                        <a href="#address" class="nav-link">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Delivery Address</span>
                        </a>
                    </li>
                    <li class="nav-item" data-section="security">
                        <a href="#security" class="nav-link">
                            <i class="fas fa-shield-alt"></i>
                            <span>Security</span>
                        </a>
                    </li>
                    <li class="nav-item" data-section="settings">
                        <a href="#settings" class="nav-link">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Overview Section -->
            <section id="overview" class="content-section active">
                <div class="section-header">
                    <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                    <p>Here's your account overview</p>
                </div>
                
                <!-- Account Summary -->
                <div class="account-summary">
                    <div class="summary-card">
                        <div class="summary-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="summary-content">
                            <h3><?php echo htmlspecialchars($userName); ?></h3>
                            <p><?php echo htmlspecialchars($userEmail); ?></p>
                            <span class="member-since">Member since <?php echo $userExtra['created_at'] ? date("F Y", strtotime($userExtra['created_at'])) : 'Unknown'; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="total-orders">0</h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $userExtra['created_at'] ? date("M Y", strtotime($userExtra['created_at'])) : 'N/A'; ?></h3>
                            <p>Member Since</p>
                        </div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="actions-grid">
                        <a href="/jowaki_electrical_srvs/Store.php" class="action-card">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Start Shopping</span>
                        </a>
                        <button class="action-card" onclick="editProfile()">
                            <i class="fas fa-edit"></i>
                            <span>Edit Profile</span>
                        </button>
                        <button class="action-card" onclick="changePassword()">
                            <i class="fas fa-key"></i>
                            <span>Change Password</span>
                        </button>
                        <a href="#orders" class="action-card" onclick="switchSection('orders')">
                            <i class="fas fa-history"></i>
                            <span>View Orders</span>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Personal Information Section -->
            <section id="personal" class="content-section">
                <div class="section-header">
                    <h1>Personal Information</h1>
                    <p>Manage your personal details</p>
                </div>
                
                <div class="info-card">
                    <div class="card-header">
                        <h2>Basic Information</h2>
                        <button class="edit-btn" onclick="editProfile()">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Full Name</label>
                            <span><?php echo htmlspecialchars($userName); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email Address</label>
                            <span><?php echo htmlspecialchars($userEmail); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Phone Number</label>
                            <span><?php echo htmlspecialchars($userExtra['phone']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Member Since</label>
                            <span><?php echo $userExtra['created_at'] ? date("F j, Y", strtotime($userExtra['created_at'])) : 'Unknown'; ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Order History Section -->
            <section id="orders" class="content-section">
                <div class="section-header">
                    <h1>Order History</h1>
                    <p>Track your past and current orders</p>
                </div>
                
                <div class="orders-container">
                    <div id="orders-list" class="orders-list">
                        <div class="loading-state">
                            <div class="spinner"></div>
                            <p>Loading orders...</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Delivery Address Section -->
            <section id="address" class="content-section">
                <div class="section-header">
                    <h1>Delivery Address</h1>
                    <p>Manage your delivery information</p>
                </div>
                
                <div class="info-card">
                    <div class="card-header">
                        <h2>Address Details</h2>
                        <button class="edit-btn" onclick="editAddress()">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Street Address</label>
                            <span><?php echo htmlspecialchars($userExtra['address']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>City</label>
                            <span><?php echo htmlspecialchars($userExtra['city']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Postal Code</label>
                            <span><?php echo htmlspecialchars($userExtra['postal_code']); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Security Section -->
            <section id="security" class="content-section">
                <div class="section-header">
                    <h1>Account Security</h1>
                    <p>Manage your account security settings</p>
                </div>
                
                <div class="security-grid">
                    <div class="security-card">
                        <div class="security-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="security-content">
                            <h3>Session Status</h3>
                            <p class="status-active">Active</p>
                            <small>Last activity: <?php echo date('F j, Y g:i A', $_SESSION['last_activity']); ?></small>
                        </div>
                    </div>
                    
                    <div class="security-card">
                        <div class="security-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="security-content">
                            <h3>Password</h3>
                            <p>Last changed: Unknown</p>
                            <button class="btn btn-primary" onclick="changePassword()">
                                Change Password
                            </button>
                        </div>
                    </div>
                    
                    <div class="security-card">
                        <div class="security-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="security-content">
                            <h3>Notifications</h3>
                            <p>Email notifications enabled</p>
                            <button class="btn btn-secondary" onclick="manageNotifications()">
                                Manage
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Settings Section -->
            <section id="settings" class="content-section">
                <div class="section-header">
                    <h1>Account Settings</h1>
                    <p>Customize your account preferences</p>
                </div>
                
                <div class="settings-grid">
                    <div class="setting-card">
                        <div class="setting-header">
                            <h3>Email Preferences</h3>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p>Receive email notifications for orders and updates</p>
                    </div>
                    
                    <div class="setting-card">
                        <div class="setting-header">
                            <h3>SMS Notifications</h3>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p>Receive SMS notifications for order updates</p>
                    </div>
                    
                    <div class="setting-card">
                        <div class="setting-header">
                            <h3>Two-Factor Authentication</h3>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p>Add an extra layer of security to your account</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Edit Profile Modal -->
    <div id="edit-profile-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Profile</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="edit-profile-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($userExtra['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="Enter your street address">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" placeholder="Enter your city">
                    </div>
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>" placeholder="Enter postal code">
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="change-password-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change Password</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="change-password-form">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="order-details-modal" class="modal">
        <div class="modal-content order-details-content">
            <div class="modal-header">
                <h3>Order Details</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <div id="order-details-content">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <script src="/jowaki_electrical_srvs/js/profile.js"></script>
</body>
</html>