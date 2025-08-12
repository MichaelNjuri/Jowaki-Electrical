<?php
session_start();
require_once 'includes/load_settings.php';

// Check if user is logged in (optional)
$user = null;
$user_id = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Get database connection
    $conn = getValidConnection();
    if ($conn) {
        $user_query = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    }
}

// Get cart items
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    header('Location: Store.php');
    exit();
}

// Load store settings
$settings = getStoreSettings(null);

// Calculate totals
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax_rate = floatval($settings['tax_rate'] ?? 16) / 100;
$tax = $subtotal * $tax_rate;
$standard_delivery_fee = floatval($settings['standard_delivery_fee'] ?? 500);
$express_delivery_fee = floatval($settings['express_delivery_fee'] ?? 1000);
$delivery_fee = $standard_delivery_fee;
$total = $subtotal + $tax + $delivery_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($settings['store_name'] ?? 'Jowaki Electrical Services'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="checkout-container">
        <!-- Checkout Form -->
        <div class="checkout-form">
            <a href="Store.php" class="back-to-cart">
                <i class="fas fa-arrow-left"></i> Back to Cart
            </a>

            <form id="checkout-form">
                <!-- Shipping Information -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-shipping-fast"></i>
                        Shipping Information
                    </h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-input" name="firstName" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-input" name="lastName" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-input" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-input" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label">Street Address *</label>
                            <input type="text" class="form-input" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <input type="text" class="form-input" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Postal Code</label>
                            <input type="text" class="form-input" name="postalCode" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Delivery Method -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-truck"></i>
                        Delivery Method
                    </h2>
                    
                    <div class="delivery-options">
                        <div class="delivery-option" onclick="selectDelivery('standard')">
                            <input type="radio" name="delivery_method" value="standard" id="standard-delivery" checked>
                            <label for="standard-delivery">
                                <strong>Standard Delivery</strong><br>
                                <small><?php echo htmlspecialchars($settings['standard_delivery_time'] ?? '3-5 business days'); ?></small>
                            </label>
                        </div>
                        
                        <div class="delivery-option" onclick="selectDelivery('express')">
                            <input type="radio" name="delivery_method" value="express" id="express-delivery">
                            <label for="express-delivery">
                                <strong>Express Delivery</strong><br>
                                <small><?php echo htmlspecialchars($settings['express_delivery_time'] ?? '1-2 business days'); ?></small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-credit-card"></i>
                        Payment Method
                    </h2>
                    
                    <div class="payment-options">
                        <div class="payment-option" onclick="selectPayment('mpesa')">
                            <input type="radio" name="payment_method" value="mpesa" id="mpesa-payment" checked>
                            <img src="mpesa-logo.png" alt="M-Pesa">
                            <label for="mpesa-payment">M-Pesa</label>
                        </div>
                        
                        <div class="payment-option" onclick="selectPayment('cash')">
                            <input type="radio" name="payment_method" value="cash" id="cash-payment">
                            <i class="fas fa-money-bill-wave" style="font-size: 30px; color: #16a34a; margin-bottom: 10px;"></i>
                            <label for="cash-payment">Cash on Delivery</label>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-sticky-note"></i>
                        Order Notes
                    </h2>
                    
                    <div class="form-group">
                        <label class="form-label">Special Instructions (Optional)</label>
                        <textarea class="form-input form-textarea" name="notes" placeholder="Any special delivery instructions or notes..."></textarea>
                    </div>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <h2 class="summary-title">
                <i class="fas fa-receipt"></i>
                Order Summary
            </h2>
            
            <div class="cart-items">
                <?php foreach ($cart as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image'] ?? 'Logo.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                    <div class="item-details">
                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="item-price">KSh <?php echo number_format($item['price'], 2); ?></div>
                    </div>
                    <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="summary-breakdown">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>KSh <?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>VAT (<?php echo floatval($settings['tax_rate'] ?? 16); ?>%)</span>
                    <span>KSh <?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span>KSh <?php echo number_format($delivery_fee, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>KSh <?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            
            <button type="button" class="place-order-btn" onclick="placeOrder()">
                <i class="fas fa-lock"></i> Place Order Securely
            </button>
            
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <span>100% Secure Payment</span>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
        <p>Processing your order...</p>
    </div>

    <script src="js/checkout.js"></script>
    <script>
        // Initialize checkout with PHP data
        window.checkoutData = {
            subtotal: <?php echo $subtotal; ?>,
            tax: <?php echo $tax; ?>,
            standardDeliveryFee: <?php echo $standard_delivery_fee; ?>,
            expressDeliveryFee: <?php echo $express_delivery_fee; ?>,
            cart: <?php echo json_encode($cart); ?>
        };
    </script>
</body>
</html>
