<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart)) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = array_reduce($cart, function($sum, $item) {
    return $sum + ($item['price'] * $item['quantity']);
}, 0);
$tax = $subtotal * 0.16;
$delivery_fee = 0; // Will be calculated based on delivery method
$total = $subtotal + $tax + $delivery_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jowaki Store - Checkout</title>
    <link rel="stylesheet" href="store.css">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Checkout Specific Styles - Clean White Design */
        :root {
            --checkout-bg: #ffffff;
            --checkout-surface: #f8fafc;
            --checkout-border: #e2e8f0;
            --checkout-text: #1e293b;
            --checkout-text-light: #64748b;
            --checkout-primary: #2563eb;
            --checkout-success: #059669;
            --checkout-error: #dc2626;
        }

        body {
            background-color: var(--checkout-bg);
            color: var(--checkout-text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--checkout-bg);
            min-height: 100vh;
        }

        .checkout-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--checkout-border);
        }

        .checkout-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--checkout-text);
            margin-bottom: 0.5rem;
        }

        .checkout-header p {
            color: var(--checkout-text-light);
            font-size: 1.1rem;
        }

        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            position: relative;
            background: var(--checkout-surface);
            border-radius: 1rem;
            padding: 1.5rem;
        }

        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 2rem;
            right: 2rem;
            height: 2px;
            background: var(--checkout-border);
            z-index: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            background: var(--checkout-bg);
            padding: 0 1rem;
            flex: 1;
        }

        .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--checkout-border);
            color: var(--checkout-text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            border: 3px solid var(--checkout-bg);
        }

        .step.active .step-number {
            background: var(--checkout-primary);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .step.completed .step-number {
            background: var(--checkout-success);
            color: white;
        }

        .step-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--checkout-text);
            text-align: center;
        }

        .step.active .step-title {
            color: var(--checkout-primary);
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .checkout-form {
            background: var(--checkout-bg);
            border: 1px solid var(--checkout-border);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--checkout-text);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--checkout-border);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--checkout-text);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--checkout-border);
            border-radius: 0.5rem;
            font-size: 1rem;
            background: var(--checkout-bg);
            color: var(--checkout-text);
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--checkout-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .checkout-summary {
            background: var(--checkout-surface);
            border: 1px solid var(--checkout-border);
            border-radius: 1rem;
            padding: 2rem;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .summary-header {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--checkout-text);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--checkout-border);
        }

        .cart-items {
            margin-bottom: 1.5rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--checkout-border);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 60px;
            height: 60px;
            border-radius: 0.5rem;
            overflow: hidden;
            flex-shrink: 0;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            color: var(--checkout-text);
            margin-bottom: 0.25rem;
        }

        .cart-item-price {
            color: var(--checkout-text-light);
            font-size: 0.9rem;
        }

        .cart-item-quantity {
            color: var(--checkout-text-light);
            font-size: 0.9rem;
        }

        .summary-totals {
            border-top: 1px solid var(--checkout-border);
            padding-top: 1.5rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .total-row.final {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--checkout-text);
            border-top: 1px solid var(--checkout-border);
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .payment-methods {
            margin-top: 2rem;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid var(--checkout-border);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: var(--checkout-primary);
            background: rgba(37, 99, 235, 0.05);
        }

        .payment-method.selected {
            border-color: var(--checkout-primary);
            background: rgba(37, 99, 235, 0.1);
        }

        .payment-method input[type="radio"] {
            margin: 0;
        }

        .payment-method-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--checkout-bg);
            border-radius: 0.5rem;
            font-size: 1.2rem;
        }

        .payment-method-details {
            flex: 1;
        }

        .payment-method-name {
            font-weight: 600;
            color: var(--checkout-text);
            margin-bottom: 0.25rem;
        }

        .payment-method-description {
            color: var(--checkout-text-light);
            font-size: 0.9rem;
        }

        .checkout-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--checkout-primary);
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-secondary {
            background: var(--checkout-bg);
            color: var(--checkout-text);
            border: 1px solid var(--checkout-border);
        }

        .btn-secondary:hover {
            background: var(--checkout-surface);
            border-color: var(--checkout-primary);
            color: var(--checkout-primary);
        }

        .btn-back {
            background: var(--checkout-bg);
            color: var(--checkout-text-light);
            border: 1px solid var(--checkout-border);
        }

        .btn-back:hover {
            background: var(--checkout-surface);
            color: var(--checkout-text);
        }

        .checkout-footer {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid var(--checkout-border);
            color: var(--checkout-text-light);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .checkout-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .checkout-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .checkout-container {
                padding: 1rem;
            }

            .checkout-header h1 {
                font-size: 2rem;
            }

            .checkout-steps {
                flex-direction: column;
                gap: 1rem;
            }

            .checkout-steps::before {
                display: none;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .checkout-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid var(--checkout-border);
            border-top: 2px solid var(--checkout-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success/Error States */
        .success-message {
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid var(--checkout-success);
            color: var(--checkout-success);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .error-message {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid var(--checkout-error);
            color: var(--checkout-error);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        /* Progress Bar */
        .progress-bar {
            width: 100%;
            height: 4px;
            background: var(--checkout-border);
            border-radius: 2px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-fill {
            height: 100%;
            background: var(--checkout-primary);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <?php include 'store_header.php'; ?>

    <div class="checkout-container">
        <!-- Checkout Header -->
        <div class="checkout-header">
            <h1>Secure Checkout</h1>
            <p>Complete your order with confidence</p>
        </div>

        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-title">Customer Info</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-title">Delivery</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-title">Payment</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-title">Confirmation</div>
            </div>
        </div>

        <!-- Checkout Content -->
        <div class="checkout-content">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <form id="checkoutForm">
                    <!-- Customer Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Customer Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name *</label>
                                <input type="text" id="firstName" name="firstName" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name *</label>
                                <input type="text" id="lastName" name="lastName" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-truck"></i> Delivery Information</h3>
                        <div class="form-group">
                            <label for="address">Street Address *</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            <div class="form-group">
                                <label for="postalCode">Postal Code</label>
                                <input type="text" id="postalCode" name="postalCode">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="deliveryNotes">Delivery Notes</label>
                            <textarea id="deliveryNotes" name="deliveryNotes" rows="3" placeholder="Any special delivery instructions..."></textarea>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                        <div class="payment-methods">
                            <div class="payment-method selected">
                                <input type="radio" name="paymentMethod" value="mpesa" checked>
                                <div class="payment-method-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div class="payment-method-details">
                                    <div class="payment-method-name">M-Pesa</div>
                                    <div class="payment-method-description">Pay with M-Pesa mobile money</div>
                                </div>
                            </div>
                            <div class="payment-method">
                                <input type="radio" name="paymentMethod" value="cash">
                                <div class="payment-method-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="payment-method-details">
                                    <div class="payment-method-name">Cash on Delivery</div>
                                    <div class="payment-method-description">Pay when you receive your order</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout Actions -->
                    <div class="checkout-actions">
                        <a href="cart.php" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i>
                            Back to Cart
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-lock"></i>
                            Complete Order
                        </button>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="checkout-summary">
                <div class="summary-header">
                    <i class="fas fa-shopping-cart"></i>
                    Order Summary
                </div>

                <!-- Cart Items -->
                <div class="cart-items">
                    <?php foreach ($cart as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="<?php echo htmlspecialchars($item['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="cart-item-details">
                            <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="cart-item-price">KSh <?php echo number_format($item['price'], 2); ?></div>
                            <div class="cart-item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Totals -->
                <div class="summary-totals">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span>KSh <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Tax (16%)</span>
                        <span>KSh <?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Delivery Fee</span>
                        <span>KSh <?php echo number_format($delivery_fee, 2); ?></span>
                    </div>
                    <div class="total-row final">
                        <span>Total</span>
                        <span>KSh <?php echo number_format($total, 2); ?></span>
                    </div>
                </div>

                <!-- Security Notice -->
                <div style="margin-top: 2rem; padding: 1rem; background: rgba(37, 99, 235, 0.05); border-radius: 0.5rem; border: 1px solid rgba(37, 99, 235, 0.2);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-shield-alt" style="color: var(--checkout-primary);"></i>
                        <span style="font-weight: 600; color: var(--checkout-text);">Secure Checkout</span>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--checkout-text-light); margin: 0;">
                        Your payment information is encrypted and secure. We never store your payment details.
                    </p>
                </div>
            </div>
        </div>

        <!-- Checkout Footer -->
        <div class="checkout-footer">
            <p>Need help? Contact us at <a href="tel:+254721442248" style="color: var(--checkout-primary);">+254 721 442 248</a></p>
        </div>
    </div>

    <script>
        // Pre-fill form with user data if logged in
        function prefillUserData() {
            fetch('API/get_user_data.php', {
                method: 'GET',
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const userData = data.data;
                    
                    // Pre-fill customer information
                    if (userData.firstName) document.getElementById('firstName').value = userData.firstName;
                    if (userData.lastName) document.getElementById('lastName').value = userData.lastName;
                    if (userData.email) document.getElementById('email').value = userData.email;
                    if (userData.phone) document.getElementById('phone').value = userData.phone;
                    
                    // Pre-fill delivery information
                    if (userData.address) document.getElementById('address').value = userData.address;
                    if (userData.city) document.getElementById('city').value = userData.city;
                    if (userData.postalCode) document.getElementById('postalCode').value = userData.postalCode;
                    
                    // Show a subtle notification that form was pre-filled
                    const notification = document.createElement('div');
                    notification.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: rgba(5, 150, 105, 0.9);
                        color: white;
                        padding: 1rem;
                        border-radius: 0.5rem;
                        z-index: 1000;
                        font-size: 0.9rem;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    `;
                    notification.innerHTML = '✅ Form pre-filled with your profile data';
                    document.body.appendChild(notification);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                }
            })
            .catch(error => {
                console.log('No user data available or user not logged in');
            });
        }

        // Call pre-fill function when page loads
        document.addEventListener('DOMContentLoaded', prefillUserData);

        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                // Add selected class to clicked method
                this.classList.add('selected');
                // Check the radio button
                this.querySelector('input[type="radio"]').checked = true;
            });
        });

        // Form submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="spinner"></div> Please wait for confirmation...';
            submitBtn.disabled = true;
            
            // Collect form data
            const customerInfo = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
                city: document.getElementById('city').value,
                postalCode: document.getElementById('postalCode').value
            };
            
            // Get cart data from session
            const cartData = <?php echo json_encode($cart); ?>;
            
            // Get delivery and payment method
            const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked')?.value || 'Standard Delivery';
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'Cash on Delivery';
            const deliveryAddress = document.getElementById('address').value + ', ' + document.getElementById('city').value + ', ' + document.getElementById('postalCode').value;
            
            // Calculate totals
            const subtotal = <?php echo $subtotal; ?>;
            const tax = <?php echo $tax; ?>;
            const deliveryFee = 0; // Will be calculated based on delivery method
            const total = <?php echo $total; ?>;
            
            // Prepare order data
            const orderData = {
                customer_info: customerInfo,
                cart: cartData,
                subtotal: subtotal,
                tax: tax,
                delivery_fee: deliveryFee,
                total: total,
                delivery_method: deliveryMethod,
                delivery_address: deliveryAddress,
                payment_method: paymentMethod,
                order_date: new Date().toISOString()
            };
            
            // First update user profile if logged in
            fetch('API/update_user_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(customerInfo)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Profile updated successfully');
                } else {
                    console.log('Profile update failed or user not logged in');
                }
            })
            .catch(error => {
                console.log('Profile update error:', error);
            })
            .then(() => {
                            // Now place the order
            console.log('Sending order data:', orderData);
            return fetch('API/place_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            });
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // If not JSON, get text and try to parse it
                    return response.text().then(text => {
                        console.log('Non-JSON response:', text);
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    console.log('Order placed successfully:', data);
                    // Show success message and redirect
                    alert('Order placed successfully! You will receive a confirmation email shortly.');
                    window.location.href = 'thankyou.php';
                } else {
                    console.error('Order placement failed:', data.error);
                    alert('Order placement failed: ' + data.error);
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Order placement error:', error);
                alert('Order placement failed: ' + error.message);
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Form validation
        document.querySelectorAll('input[required]').forEach(input => {
            input.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.borderColor = 'var(--checkout-error)';
                } else {
                    this.style.borderColor = 'var(--checkout-border)';
                }
            });
        });
    </script>
</body>
</html>