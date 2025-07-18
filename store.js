let storeProducts = [];
let cart = [];
let currentFilter = 'all';
let currentCheckoutStep = 1;
let customerInfo = {};
let orderData = {};

document.addEventListener('DOMContentLoaded', async function () {
    let localProducts = [];
    try {
        console.log('Starting product fetch at', new Date().toISOString());
        const res = await fetch('/jowaki_electrical_srvs/api/get_products.php');
        if (!res.ok) {
            const errorText = await res.text();
            console.error(`Fetch error: Status ${res.status}, Response: ${errorText}`);
            throw new Error(`HTTP error ${res.status}: ${res.statusText}`);
        }

        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await res.text();
            console.error('Unexpected content type:', contentType, 'Response:', text);
            throw new Error('Response is not JSON');
        }

        const data = await res.json();
        console.log('Raw response data:', data);

        if (data.success && Array.isArray(data.products)) {
            localProducts = data.products;
        } else if (Array.isArray(data)) {
            localProducts = data;
        } else if (data.products && Array.isArray(data.products)) {
            localProducts = data.products;
        } else {
            console.error('Invalid response structure:', data);
            localProducts = [];
            showNotification('No products available at the moment.', 'error');
        }

        if (!Array.isArray(localProducts)) {
            console.error('localProducts is not an array:', localProducts);
            localProducts = [];
            showNotification('Failed to load products: Invalid data format.', 'error');
        }

        console.log('Products before processing:', localProducts);

        localProducts.forEach(p => {
            try {
                p.stock = parseInt(p.stock) || 0;
                p.features = Array.isArray(p.features) ? p.features : [];
                p.specifications = typeof p.specifications === 'object' ? p.specifications : {};
            } catch (e) {
                console.warn(`Invalid product JSON fields for product ID ${p.id || 'unknown'}:`, p, e);
                p.features = [];
                p.specifications = {};
                p.stock = 0;
            }
        });

        console.log('Processed products:', localProducts);
        window.storeProducts = localProducts;
        loadProducts();
        updateCartDisplay();

        const savedCart = JSON.parse(localStorage.getItem('cart') || '[]');
        if (savedCart.length > 0) {
            cart = savedCart;
            updateCartDisplay();
        }

        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    searchProducts();
                }
            });
        } else {
            console.warn('Search input element not found');
        }
    } catch (error) {
        console.error('Error loading products:', error);
        window.storeProducts = [];
        loadProducts();
        showNotification(`Failed to load products: ${error.message}`, 'error');
    }
});

function isInStock(stock) {
    return stock > 0;
}

function showHome() {
    const storeView = document.getElementById('storeView');
    const servicesView = document.getElementById('servicesView');
    if (storeView && servicesView) {
        storeView.classList.remove('hidden');
        servicesView.classList.add('hidden');
    }
    if (event) event.preventDefault();
}

function showServices() {
    const storeView = document.getElementById('storeView');
    const servicesView = document.getElementById('servicesView');
    if (storeView && servicesView) {
        storeView.classList.add('hidden');
        servicesView.classList.remove('hidden');
    }
    if (event) event.preventDefault();
}

function loadProducts(filteredProducts = window.storeProducts.filter(p => p.active)) {
    const grid = document.getElementById('productsGrid');
    if (!grid) {
        console.warn('Products grid element not found');
        return;
    }
    grid.innerHTML = '';

    if (!filteredProducts || !Array.isArray(filteredProducts) || filteredProducts.length === 0) {
        grid.innerHTML = '<p style="text-align: center; color: #2c3e50; grid-column: 1/-1;">No products found</p>';
        return;
    }

    filteredProducts.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';

        const originalPrice = parseFloat(product.price);
        const sellingPrice = parseFloat(product.discount_price) || originalPrice;
        const imageSrc = product.image || 'placeholder.jpg';

        const priceHtml = sellingPrice < originalPrice
            ? `<div class="product-price">
                   <span class="original-price">KSh ${originalPrice.toLocaleString()}</span>
                   KSh ${sellingPrice.toLocaleString()}
               </div>`
            : `<div class="product-price">KSh ${sellingPrice.toLocaleString()}</div>`;

        productCard.innerHTML = `
            <div class="product-image">
                <img src="${imageSrc}" alt="${product.name}" class="product-thumb" style="max-width: 100%; height: auto;">
            </div>
            <div class="product-info">
                <h3 class="product-title">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                ${priceHtml}
                <div class="product-actions">
                    <button class="btn btn-primary" onclick="addToCart(${product.id})" ${!isInStock(product.stock) ? 'disabled' : ''}>
                        ${!isInStock(product.stock) ? 'Out of Stock' : 'Add to Cart'}
                    </button>
                    <button class="btn btn-secondary" onclick="viewProduct(${product.id})">Details</button>
                </div>
            </div>
        `;
        grid.appendChild(productCard);
    });
}

function filterProducts(category) {
    currentFilter = category;
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    if (event && event.target) {
        event.target.classList.add('active');
    }

    const filteredProducts = category === 'all'
        ? window.storeProducts.filter(p => p.active)
        : window.storeProducts.filter(p => p.category === category && p.active);
    loadProducts(filteredProducts);
}

function searchProducts() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) {
        console.warn('Search input element not found');
        return;
    }
    const searchTerm = searchInput.value.toLowerCase().trim();
    if (!searchTerm) {
        filterProducts(currentFilter);
        return;
    }

    const filteredProducts = window.storeProducts.filter(product =>
        (product.name.toLowerCase().includes(searchTerm) ||
         product.description.toLowerCase().includes(searchTerm) ||
         product.category.toLowerCase().includes(searchTerm)) &&
        product.active
    );
    loadProducts(filteredProducts);
    showNotification(`Found ${filteredProducts.length} products matching "${searchTerm}"`);
}

function viewProduct(productId) {
    const product = window.storeProducts.find(p => String(p.id) === String(productId));
    if (!product) {
        showNotification('Product not found!', 'error');
        return;
    }

    const actualPrice = parseFloat(product.discount_price) || parseFloat(product.price);
    const originalPrice = parseFloat(product.price);
    const imageSrc = product.image || 'placeholder.jpg';

    const featuresHtml = Array.isArray(product.features) && product.features.length > 0
        ? `<h4>Features:</h4><ul>${product.features.map(f => `<li>${f}</li>`).join('')}</ul>`
        : '';

    const specsHtml = product.specifications && typeof product.specifications === 'object'
        ? `<h4>Specifications:</h4><ul>${Object.entries(product.specifications).map(([key, value]) => `<li><strong>${key}:</strong> ${value}</li>`).join('')}</ul>`
        : '';

    const modalContent = `
        <div style="text-align: center;">
            <div style="margin-bottom: 1rem;">
                <img src="${imageSrc}" alt="${product.name}" style="max-width: 300px; height: auto; border-radius: 8px;">
            </div>
            <h2>${product.name}</h2>
            <p style="color: #7f8c8d; margin-bottom: 1rem;">${product.description}</p>
            <div style="font-size: 1.5rem; color: #e74c3c; font-weight: bold; margin-bottom: 1rem;">
                ${actualPrice < originalPrice ? `<span style="text-decoration: line-through; color: #95a5a6; font-size: 1rem;">KSh ${originalPrice.toLocaleString()}</span> ` : ''}
                KSh ${actualPrice.toLocaleString()}
            </div>
            ${featuresHtml}
            ${specsHtml}
            <div style="margin-top: 2rem;">
                <button class="btn btn-primary" onclick="addToCart(${product.id}); hideModal('productModal');" ${!isInStock(product.stock) ? 'disabled' : ''} style="margin-right: 1rem;">
                    ${!isInStock(product.stock) ? 'Out of Stock' : 'Add to Cart'}
                </button>
                <button class="btn btn-secondary" onclick="hideModal('productModal')">Close</button>
            </div>
        </div>
    `;

    showModal('Product Details', modalContent, 'productModal');
}

function addToCart(productId, quantity = 1) {
    const product = window.storeProducts.find(p => String(p.id) === String(productId));
    if (!product) {
        showNotification('Product not found!', 'error');
        return;
    }

    if (!isInStock(product.stock)) {
        showNotification(`${product.name} is out of stock!`, 'error');
        return;
    }

    const actualPrice = parseFloat(product.discount_price) || parseFloat(product.price);
    const imageSrc = product.image || 'placeholder.jpg';

    const existingItem = cart.find(item => item.id === productId);

    if (existingItem) {
        if (existingItem.quantity + quantity > product.stock) {
            showNotification(`Cannot add more ${product.name} - only ${product.stock} in stock!`, 'error');
            return;
        }
        existingItem.quantity += quantity;
    } else {
        if (quantity > product.stock) {
            showNotification(`Cannot add ${product.name} - only ${product.stock} in stock!`, 'error');
            return;
        }
        cart.push({
            id: productId,
            name: product.name,
            price: actualPrice,
            quantity: quantity,
            image: imageSrc
        });
    }

    updateCartDisplay();
    saveCart();
    showNotification(`${product.name} added to cart!`);
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCartDisplay();
    saveCart();
    showNotification('Item removed from cart');
}

function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    const product = window.storeProducts.find(p => p.id === productId);

    if (!item || !product) return;

    const newQuantity = item.quantity + change;

    if (newQuantity <= 0) {
        removeFromCart(productId);
        return;
    }

    if (newQuantity > product.stock) {
        showNotification(`Cannot add more ${product.name} - only ${product.stock} in stock!`, 'error');
        return;
    }

    item.quantity = newQuantity;
    updateCartDisplay();
    saveCart();
}

function clearCart() {
    if (cart.length === 0) {
        showNotification('Cart is already empty!', 'error');
        return;
    }

    if (confirm('Are you sure you want to clear your cart?')) {
        cart = [];
        updateCartDisplay();
        saveCart();
        showNotification('Cart cleared successfully!');
    }
}

function updateCartDisplay() {
    const cartCount = document.getElementById('cartCount');
    const cartItems = document.getElementById('cartItems');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartTax = document.getElementById('cartTax');
    const cartTotalAmount = document.getElementById('cartTotalAmount');

    if (!cartCount || !cartItems || !cartSubtotal || !cartTax || !cartTotalAmount) {
        console.warn('Cart display elements not found');
        return;
    }

    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;
    cartCount.style.display = totalItems > 0 ? 'flex' : 'none';

    if (cart.length === 0) {
        cartItems.innerHTML = '<p style="text-align: center; padding: 20px; color: #7f8c8d;">Your cart is empty</p>';
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-image">
                    <img src="${item.image}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                </div>
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">KSh ${(item.price * item.quantity).toLocaleString()}</div>
                </div>
                <div class="cart-item-controls">
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">−</button>
                    <span>${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                    <button class="btn btn-secondary" onclick="removeFromCart(${item.id})" style="margin-left: 0.5rem; padding: 0.25rem 0.5rem;">×</button>
                </div>
            </div>
        `).join('');
    }

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.16;
    const total = subtotal + tax;

    cartSubtotal.textContent = `KSh ${subtotal.toLocaleString()}`;
    cartTax.textContent = `KSh ${Math.round(tax).toLocaleString()}`;
    cartTotalAmount.textContent = `KSh ${Math.round(total).toLocaleString()}`;
}

function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    if (cartSidebar) {
        cartSidebar.classList.toggle('open');
    } else {
        console.warn('Cart sidebar element not found');
    }
}

function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function showModal(title, content, modalId) {
    const existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }

    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.id = modalId;
    modal.innerHTML = `
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>${title}</h2>
                <button onclick="hideModal('${modalId}')" class="btn btn-secondary">✕</button>
            </div>
            ${content}
        </div>
    `;
    document.body.appendChild(modal);
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.remove();
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    if (!document.getElementById('notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 2rem;
                border-radius: 5px;
                color: white;
                z-index: 10001;
                animation: slideIn 0.5s ease-out, slideOut 0.5s ease-out 2.5s forwards;
            }
            .notification.success { background: #2ecc71; }
            .notification.error { background: #e74c3c; }
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(styles);
    }

    setTimeout(() => notification.remove(), 3000);
}

function startCheckout() {
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

function hideCheckout() {
    const checkoutModal = document.getElementById('checkoutModal');
    if (checkoutModal) {
        checkoutModal.classList.add('hidden');
    }
}

function showCheckoutStep(step) {
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

function saveCustomerInfo(event) {
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

function saveDeliveryInfo(event) {
    event.preventDefault();
    const form = event.target;
    customerInfo.deliveryMethod = form.deliveryMethod.value;
    customerInfo.deliveryAddress = form.deliveryAddress.value;
    showCheckoutStep(3);
}

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

function processPayment() {
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

function getConfirmationStep() {
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

function placeOrder() {
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
            cart = [];
            saveCart();
            updateCartDisplay();
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