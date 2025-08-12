// Checkout Module - Handles all checkout-related functionality

// Global checkout state
let currentCheckoutStep = 1;
let customerInfo = {};
let orderData = {};

/**
 * Starts the checkout process.
 */
export function startCheckout(cart) {
    if (cart.length === 0) {
        showNotification('Your cart is empty!', 'error');
        return;
    }

    currentCheckoutStep = 1;
    customerInfo = {};
    const checkoutModal = document.getElementById('checkoutModal');
    if (checkoutModal) {
        checkoutModal.classList.remove('hidden');
        showCheckoutStep(1);
    } else {
        console.warn('Checkout modal element not found');
    }
}

/**
 * Hides the checkout modal.
 */
export function hideCheckout() {
    const checkoutModal = document.getElementById('checkoutModal');
    if (checkoutModal) {
        checkoutModal.classList.add('hidden');
    }
}

/**
 * Shows the specified step in the checkout process.
 * @param {number} step - The step to show.
 */
export function showCheckoutStep(step) {
    currentCheckoutStep = step;
    const steps = document.querySelectorAll('.step');
    steps.forEach((stepEl, index) => {
        stepEl.classList.toggle('active', index + 1 === step);
    });

    let content = '';
    switch(step) {
        case 1:
            content = getCustomerInfoStep();
            break;
        case 2:
            content = getDeliveryStep();
            break;
        case 3:
            content = getPaymentStep();
            break;
        case 4:
            content = getConfirmationStep();
            break;
    }

    const checkoutContent = document.getElementById('checkoutContent');
    if (checkoutContent) {
        checkoutContent.innerHTML = content;
    } else {
        console.warn('Checkout content element not found');
        return;
    }

    if (step === 3) {
        const paymentMethods = document.querySelectorAll('.payment-method');
        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                paymentMethods.forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                const paymentBtn = document.getElementById('paymentBtn');
                if (paymentBtn) {
                    paymentBtn.disabled = false;
                }
            });
        });
    }
}

/**
 * Gets the HTML content for the customer information step.
 * @returns {string} - The HTML content for the customer information step.
 */
function getCustomerInfoStep() {
    return `
        <div class="checkout-section">
            <h3>Customer Information</h3>
            <form onsubmit="saveCustomerInfo(event)">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>First Name*</label>
                        <input type="text" name="firstName" value="${customerInfo.firstName || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name*</label>
                        <input type="text" name="lastName" value="${customerInfo.lastName || ''}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email Address*</label>
                    <input type="email" name="email" value="${customerInfo.email || ''}" required>
                </div>
                <div class="form-group">
                    <label>Phone Number*</label>
                    <input type="tel" name="phone" value="${customerInfo.phone || ''}" pattern="[0-9]{10,12}" required>
                </div>
                <div class="form-group">
                    <label>Address*</label>
                    <textarea name="address" rows="3" required>${customerInfo.address || ''}</textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>City*</label>
                        <input type="text" name="city" value="${customerInfo.city || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" name="postalCode" value="${customerInfo.postalCode || ''}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Continue to Delivery</button>
            </form>
        </div>
    `;
}

/**
 * Saves the customer information and proceeds to the next step.
 * @param {Event} event - The form submission event.
 */
export function saveCustomerInfo(event) {
    event.preventDefault();
    const form = event.target;
    customerInfo = {
        firstName: form.firstName.value,
        lastName: form.lastName.value,
        email: form.email.value,
        phone: form.phone.value,
        address: form.address.value,
        city: form.city.value,
        postalCode: form.postalCode.value
    };
    showCheckoutStep(2);
}

/**
 * Gets the HTML content for the delivery step.
 * @returns {string} - The HTML content for the delivery step.
 */
function getDeliveryStep() {
    return `
        <div class="checkout-section">
            <h3>Delivery Options</h3>
            <form onsubmit="saveDeliveryInfo(event)">
                <div class="form-group">
                    <label>Delivery Method*</label>
                    <div style="margin-top: 0.5rem;">
                        <label style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                            <input type="radio" name="deliveryMethod" value="standard" ${customerInfo.deliveryMethod === 'standard' ? 'checked' : ''} required style="margin-right: 0.5rem;">
                            Standard Delivery (3-5 business days) - FREE
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                            <input type="radio" name="deliveryMethod" value="express" ${customerInfo.deliveryMethod === 'express' ? 'checked' : ''} style="margin-right: 0.5rem;">
                            Express Delivery (1-2 business days) - KSh 500
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                            <input type="radio" name="deliveryMethod" value="pickup" ${customerInfo.deliveryMethod === 'pickup' ? 'checked' : ''} style="margin-right: 0.5rem;">
                            Store Pickup - FREE
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Delivery Address*</label>
                    <textarea name="deliveryAddress" rows="3" required>${customerInfo.deliveryAddress || customerInfo.address || ''}</textarea>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="showCheckoutStep(1)">Back</button>
                    <button type="submit" class="btn btn-primary">Continue to Payment</button>
                </div>
            </form>
        </div>
    `;
}

/**
 * Saves the delivery information and proceeds to the next step.
 * @param {Event} event - The form submission event.
 */
export function saveDeliveryInfo(event) {
    event.preventDefault();
    const form = event.target;
    customerInfo.deliveryMethod = form.deliveryMethod.value;
    customerInfo.deliveryAddress = form.deliveryAddress.value;
    showCheckoutStep(3);
}

/**
 * Gets the HTML content for the payment step.
 * @returns {string} - The HTML content for the payment step.
 */
function getPaymentStep() {
    return `
        <div class="checkout-section">
            <h3>Payment Method</h3>
            <div style="display: grid; gap: 1rem; margin-bottom: 1rem;">
                <div class="payment-method ${customerInfo.paymentMethod === 'mpesa' ? 'selected' : ''}" data-method="mpesa">
                    <img src="/jowaki_electrical_srvs/mpesa-logo.png" alt="M-Pesa" style="width: 100px; height: auto;">
                    <p>Pay with M-Pesa</p>
                </div>
                <div class="payment-method ${customerInfo.paymentMethod === 'card' ? 'selected' : ''}" data-method="card">
                    <img src="/jowaki_electrical_srvs/card-logo.png" alt="Card" style="width: 100px; height: auto;">
                    <p>Pay with Credit/Debit Card</p>
                </div>
                <div class="payment-method ${customerInfo.paymentMethod === 'paypal' ? 'selected' : ''}" data-method="paypal">
                    <img src="/jowaki_electrical_srvs/paypal-logo.png" alt="PayPal" style="width: 100px; height: auto;">
                    <p>Pay with PayPal</p>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="showCheckoutStep(2)">Back</button>
                <button type="button" id="paymentBtn" class="btn btn-primary" disabled onclick="processPayment()">Continue to Confirmation</button>
            </div>
        </div>
    `;
}

/**
 * Processes the payment and proceeds to the confirmation step.
 */
export function processPayment(cart) {
    const selectedMethod = document.querySelector('.payment-method.selected');
    if (!selectedMethod) {
        showNotification('Please select a payment method', 'error');
        return;
    }

    customerInfo.paymentMethod = selectedMethod.dataset.method;
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.16;
    const deliveryFee = customerInfo.deliveryMethod === 'express' ? 500 : 0;
    const total = subtotal + tax + deliveryFee;

    orderData = {
        customer_info: customerInfo,
        cart: cart,
        subtotal: subtotal,
        tax: tax,
        delivery_fee: deliveryFee,
        total: total,
        delivery_method: customerInfo.deliveryMethod,
        delivery_address: customerInfo.deliveryAddress,
        payment_method: customerInfo.paymentMethod,
        order_date: new Date().toISOString()
    };

    showCheckoutStep(4);
}

/**
 * Gets the HTML content for the confirmation step.
 * @returns {string} - The HTML content for the confirmation step.
 */
function getConfirmationStep() {
    const cart = getCart();
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.16;
    const deliveryFee = customerInfo.deliveryMethod === 'express' ? 500 : 0;
    const total = subtotal + tax + deliveryFee;

    const cartItemsHtml = cart.map(item => `
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span>${item.name} x${item.quantity}</span>
            <span>KSh ${(item.price * item.quantity).toLocaleString()}</span>
        </div>
    `).join('');

    return `
        <div class="checkout-section">
            <h3>Order Confirmation</h3>
            <div style="margin-bottom: 1rem;">
                <h4>Customer Details</h4>
                <p>${customerInfo.firstName} ${customerInfo.lastName}</p>
                <p>${customerInfo.email}</p>
                <p>${customerInfo.phone}</p>
                <p>${customerInfo.address}, ${customerInfo.city}${customerInfo.postalCode ? ', ' + customerInfo.postalCode : ''}</p>
            </div>
            <div style="margin-bottom: 1rem;">
                <h4>Delivery Details</h4>
                <p>Delivery Method: ${customerInfo.deliveryMethod.charAt(0).toUpperCase() + customerInfo.deliveryMethod.slice(1)}</p>
                <p>Delivery Address: ${customerInfo.deliveryAddress}</p>
            </div>
            <div style="margin-bottom: 1rem;">
                <h4>Order Summary</h4>
                ${cartItemsHtml}
                <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                    <span>Subtotal:</span>
                    <span>KSh ${subtotal.toLocaleString()}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Tax (16%):</span>
                    <span>KSh ${Math.round(tax).toLocaleString()}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Delivery Fee:</span>
                    <span>KSh ${deliveryFee.toLocaleString()}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 0.5rem;">
                    <span>Total:</span>
                    <span>KSh ${Math.round(total).toLocaleString()}</span>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="showCheckoutStep(3)">Back</button>
                <button type="button" class="btn btn-primary" onclick="placeOrder()">Place Order</button>
            </div>
        </div>
    `;
}

/**
 * Places the order by sending the order data to the server.
 */
export function placeOrder() {
    fetch('/jowaki_electrical_srvs/api/place_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Order placed successfully! You will receive a confirmation email soon.', 'success');
            // Clear cart and reset checkout
            clearCart();
            hideCheckout();
            customerInfo = {};
            orderData = {};
        } else {
            showNotification(data.error || 'Failed to place order. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error placing order:', error);
        showNotification(`An error occurred while placing your order: ${error.message}`, 'error');
    });
}

// Import functions from other modules
function showNotification(message, type = 'success') {
    if (window.showNotification) {
        window.showNotification(message, type);
    } else {
        console.log(`${type}: ${message}`);
    }
}

function getCart() {
    // This will be imported from store-cart.js
    if (window.getCart) {
        return window.getCart();
    }
    return [];
}

function clearCart() {
    // This will be imported from store-cart.js
    if (window.clearCart) {
        window.clearCart();
    }
}

// Make functions globally available
window.startCheckout = startCheckout;
window.hideCheckout = hideCheckout;
window.showCheckoutStep = showCheckoutStep;
window.saveCustomerInfo = saveCustomerInfo;
window.saveDeliveryInfo = saveDeliveryInfo;
window.processPayment = processPayment;
window.placeOrder = placeOrder; 