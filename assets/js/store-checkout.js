// Store Checkout Module
// Handles all checkout-related functionality

class CheckoutModule {
    constructor() {
        this.cart = [];
        this.shippingInfo = {};
        this.paymentInfo = {};
        this.orderSummary = {};
    }

    // Initialize checkout
    async initializeCheckout() {
        try {
            // Load cart items
            await this.loadCartItems();
            
            // Calculate order summary
            this.calculateOrderSummary();
            
            // Render checkout form
            this.renderCheckoutForm();
            
            // Set up event listeners
            this.setupEventListeners();
            
        } catch (error) {
            console.error('Error initializing checkout:', error);
            this.showNotification('Error initializing checkout', 'error');
        }
    }

    // Load cart items
    async loadCartItems() {
        try {
            const response = await fetch('/api/get_cart_count.php');
            const data = await response.json();
            
            if (data.success && data.cart) {
                this.cart = data.cart;
            }
        } catch (error) {
            console.error('Error loading cart items:', error);
        }
    }

    // Calculate order summary
    calculateOrderSummary() {
        let subtotal = 0;
        let tax = 0;
        let shipping = 0;
        let total = 0;

        // Calculate subtotal
        this.cart.forEach(item => {
            subtotal += item.price * item.quantity;
        });

        // Calculate tax (16% VAT)
        tax = subtotal * 0.16;

        // Calculate shipping (free for orders over KSh 5000)
        shipping = subtotal >= 5000 ? 0 : 500;

        // Calculate total
        total = subtotal + tax + shipping;

        this.orderSummary = {
            subtotal: subtotal,
            tax: tax,
            shipping: shipping,
            total: total
        };
    }

    // Render checkout form
    renderCheckoutForm() {
        const checkoutContainer = document.getElementById('checkout-container');
        if (!checkoutContainer) return;

        checkoutContainer.innerHTML = `
            <div class="checkout-form">
                <div class="checkout-sections">
                    <!-- Shipping Information -->
                    <div class="checkout-section">
                        <h3>Shipping Information</h3>
                        <div class="form-group">
                            <label for="full-name">Full Name</label>
                            <input type="text" id="full-name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Delivery Address</label>
                            <textarea id="address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="postal-code">Postal Code</label>
                            <input type="text" id="postal-code" name="postal_code" required>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="checkout-section">
                        <h3>Payment Method</h3>
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="mpesa" name="payment_method" value="mpesa" checked>
                                <label for="mpesa">
                                    <img src="assets/images/mpesa-logo.png" alt="M-Pesa">
                                    M-Pesa
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="card" name="payment_method" value="card">
                                <label for="card">
                                    <img src="assets/images/visa-logo.jpeg" alt="Card">
                                    Credit/Debit Card
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="paypal" name="payment_method" value="paypal">
                                <label for="paypal">
                                    <img src="assets/images/paypal-logo.png" alt="PayPal">
                                    PayPal
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div class="order-items">
                        ${this.renderOrderItems()}
                    </div>
                    <div class="order-totals">
                        <div class="total-line">
                            <span>Subtotal:</span>
                            <span>KSh ${this.orderSummary.subtotal.toFixed(2)}</span>
                        </div>
                        <div class="total-line">
                            <span>Tax (16% VAT):</span>
                            <span>KSh ${this.orderSummary.tax.toFixed(2)}</span>
                        </div>
                        <div class="total-line">
                            <span>Shipping:</span>
                            <span>${this.orderSummary.shipping === 0 ? 'Free' : `KSh ${this.orderSummary.shipping.toFixed(2)}`}</span>
                        </div>
                        <div class="total-line total">
                            <span>Total:</span>
                            <span>KSh ${this.orderSummary.total.toFixed(2)}</span>
                        </div>
                    </div>
                    <button id="place-order-btn" class="place-order-btn">Place Order</button>
                </div>
            </div>
        `;
    }

    // Render order items
    renderOrderItems() {
        let itemsHTML = '';
        
        this.cart.forEach(item => {
            itemsHTML += `
                <div class="order-item">
                    <img src="${item.image}" alt="${item.name}">
                    <div class="item-details">
                        <h4>${item.name}</h4>
                        <p>Quantity: ${item.quantity}</p>
                        <p>KSh ${item.price}</p>
                    </div>
                    <div class="item-total">
                        KSh ${(item.price * item.quantity).toFixed(2)}
                    </div>
                </div>
            `;
        });

        return itemsHTML;
    }

    // Set up event listeners
    setupEventListeners() {
        const placeOrderBtn = document.getElementById('place-order-btn');
        if (placeOrderBtn) {
            placeOrderBtn.addEventListener('click', () => {
                this.placeOrder();
            });
        }

        // Auto-fill form if user is logged in
        this.autoFillUserInfo();
    }

    // Auto-fill user information
    async autoFillUserInfo() {
        try {
            const response = await fetch('/api/get_user_info.php');
            const data = await response.json();
            
            if (data.success && data.user) {
                const user = data.user;
                
                // Fill in form fields
                const fullNameInput = document.getElementById('full-name');
                const emailInput = document.getElementById('email');
                const phoneInput = document.getElementById('phone');
                
                if (fullNameInput) fullNameInput.value = user.full_name || '';
                if (emailInput) emailInput.value = user.email || '';
                if (phoneInput) phoneInput.value = user.phone || '';
            }
        } catch (error) {
            console.error('Error loading user info:', error);
        }
    }

    // Place order
    async placeOrder() {
        try {
            // Validate form
            if (!this.validateForm()) {
                return;
            }

            // Collect form data
            const formData = this.collectFormData();
            
            // Show loading state
            const placeOrderBtn = document.getElementById('place-order-btn');
            if (placeOrderBtn) {
                placeOrderBtn.textContent = 'Processing...';
                placeOrderBtn.disabled = true;
            }

            // Submit order
            const response = await fetch('/api/place_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Order placed successfully!', 'success');
                
                // Redirect to thank you page
                setTimeout(() => {
                    window.location.href = 'thankyou.php?order_id=' + data.order_id;
                }, 2000);
            } else {
                this.showNotification(data.message || 'Failed to place order', 'error');
            }

        } catch (error) {
            console.error('Error placing order:', error);
            this.showNotification('Error placing order', 'error');
        } finally {
            // Reset button state
            const placeOrderBtn = document.getElementById('place-order-btn');
            if (placeOrderBtn) {
                placeOrderBtn.textContent = 'Place Order';
                placeOrderBtn.disabled = false;
            }
        }
    }

    // Validate form
    validateForm() {
        const requiredFields = [
            'full-name', 'email', 'phone', 'address', 'city', 'postal-code'
        ];

        for (const fieldId of requiredFields) {
            const field = document.getElementById(fieldId);
            if (!field || !field.value.trim()) {
                this.showNotification(`Please fill in ${fieldId.replace('-', ' ')}`, 'error');
                field?.focus();
                return false;
            }
        }

        // Validate email
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            this.showNotification('Please enter a valid email address', 'error');
            return false;
        }

        // Validate phone
        const phone = document.getElementById('phone').value;
        const phoneRegex = /^(\+254|0)[17]\d{8}$/;
        if (!phoneRegex.test(phone)) {
            this.showNotification('Please enter a valid phone number', 'error');
            return false;
        }

        return true;
    }

    // Collect form data
    collectFormData() {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        return {
            shipping_info: {
                full_name: document.getElementById('full-name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
                city: document.getElementById('city').value,
                postal_code: document.getElementById('postal-code').value
            },
            payment_method: paymentMethod,
            order_summary: this.orderSummary,
            cart_items: this.cart
        };
    }

    // Show notification
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Export the module
export default new CheckoutModule();

