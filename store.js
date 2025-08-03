// Global variables
let storeProducts = []; // Array to store all products fetched from the API
let cart = []; // Array to store items in the cart
let currentFilter = 'all'; // Current filter applied to products
let currentCheckoutStep = 1; // Current step in the checkout process
let customerInfo = {}; // Object to store customer information during checkout
let orderData = {}; // Object to store order data during checkout
let cartCount = 0;  // To keep track of the cart item count

// Mobile filter functionality
function toggleMobileFilters() {
    const filterPanel = document.getElementById('categoryFilter');
    if (filterPanel) {
        filterPanel.classList.toggle('active');
    }
}

// Category scrolling functionality
function scrollCategories(direction) {
    const scrollContainer = document.getElementById('categoryScroll');
    const scrollAmount = 300;
    
    if (direction === 'left') {
        scrollContainer.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    } else {
        scrollContainer.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }
}



// Cart Functions
/**
 * Adds an item to the cart.
 * @param {number} productId - The ID of the product to add.
 * @param {number} [quantity=1] - The quantity of the product to add.
 */
async function addToCart(productId, quantity = 1) {
    const product = storeProducts.find(p => String(p.id) === String(productId));
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
    
    // Update cart count
    cartCount += quantity;
    const floatingCountElement = document.querySelector('#floatingCartCount');
    if (floatingCountElement) {
        floatingCountElement.innerText = cartCount;
        floatingCountElement.style.display = 'block';
    }
    toggleCartPulse();
    
    // Send to PHP session cart
    fetch('/jowaki_electrical_srvs/api/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: productId,
            name: product.name,
            price: actualPrice,
            quantity: quantity,
            image: imageSrc
        })
    })
    .then(response => response.json())
    .then(async data => {
        if (data.success) {
            // Update local cart for UI
            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: productId,
                    name: product.name,
                    price: actualPrice,
                    quantity: quantity,
                    image: imageSrc,
                    features: product.features
                });
            }

            // Display popup with item details
            displayCartPopup(product, quantity, imageSrc);
            
            updateCartDisplay();
            await saveCart();
            showNotification(`${product.name} added to cart!`);
        } else {
            showNotification(data.error || 'Failed to add item to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showNotification('Error adding item to cart', 'error');
    });
}

/**
 * Removes an item from the cart.
 * @param {number} productId - The ID of the product to remove.
 */
async function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCartDisplay();
    await saveCart();
    showNotification('Item removed from cart');
}

/**
 * Updates the quantity of an item in the cart.
 * @param {number} productId - The ID of the product to update.
 * @param {number} change - The change in quantity (positive or negative).
 */
async function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    const product = storeProducts.find(p => p.id === productId);
    if (!item || !product) return;

    const newQuantity = item.quantity + change;
    if (newQuantity <= 0) {
        await removeFromCart(productId);
        return;
    }

    if (newQuantity > product.stock) {
        showNotification(`Cannot add more ${product.name} - only ${product.stock} in stock!`, 'error');
        return;
    }

    item.quantity = newQuantity;
    updateCartDisplay();
    await saveCart();
}

/**
 * Clears the cart.
 */
async function clearCart() {
    if (cart.length === 0) {
        showNotification('Cart is already empty!', 'error');
        return;
    }

    if (confirm('Are you sure you want to clear your cart?')) {
        cart = [];
        updateCartDisplay();
        await saveCart();
        showNotification('Cart cleared successfully!');
    }
}

/**
 * Saves the cart to local storage.
 */
async function saveCart() {
    try {
        const response = await fetch('/jowaki_electrical_srvs/api/sync_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cart: cart })
        });
        
        const data = await response.json();
        if (!data.success) {
            console.error('Failed to save cart to server:', data.error);
        }
    } catch (error) {
        console.error('Error saving cart:', error);
    }
}

// Product Functions
/**
 * Checks if a product is in stock.
 * @param {number} stock - The stock quantity of the product.
 * @returns {boolean} - True if the product is in stock, false otherwise.
 */
function isInStock(stock) {
    return stock > 0;
}

/**
 * Loads products into the products grid.
 * @param {Array} filteredProducts - The array of products to load.
 */
function loadProducts(filteredProducts = storeProducts) {
    const grid = document.getElementById('productsGrid');
    if (!grid) {
        console.warn('Products grid element not found');
        return;
    }

    // Clear grid
    grid.innerHTML = '';

    // Load products with lazy loading and transition effects
    if (!filteredProducts || filteredProducts.length === 0) {
        grid.innerHTML = '<p style="text-align: center; color: #2c3e50; grid-column: 1/-1;">No products found</p>';
        return;
    }

    filteredProducts.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        const originalPrice = parseFloat(product.price);
        const sellingPrice = parseFloat(product.discount_price) || originalPrice;
        const imageSrc = product.image || 'placeholder.jpg';
        const discountPercentage = sellingPrice < originalPrice ? Math.round(((originalPrice - sellingPrice) / originalPrice) * 100) : 0;
        
        // Generate stock status
        const stockStatus = product.stock > 0 ? 'In Stock' : 'Out of Stock';
        
        // Generate badges
        const badges = [];
        if (discountPercentage > 0) {
            badges.push(`<div class="product-badge sale">-${discountPercentage}%</div>`);
        }
        if (product.stock > 0 && product.stock <= 10) {
            badges.push('<div class="product-badge hot">Hot</div>');
        }
        if (product.category && product.category.toLowerCase().includes('new')) {
            badges.push('<div class="product-badge new">New</div>');
        }
        

        
        const priceHtml = sellingPrice < originalPrice
            ? `<div class="product-price">
                <span class="current-price">KSh ${sellingPrice.toLocaleString()}</span>
                <span class="original-price">KSh ${originalPrice.toLocaleString()}</span>
                <span class="discount-percentage">-${discountPercentage}%</span>
            </div>`
            : `<div class="product-price">
                <span class="current-price">KSh ${sellingPrice.toLocaleString()}</span>
            </div>`;

        productCard.innerHTML = `
            <div class="product-image">
                <img src="${imageSrc}" alt="${product.name}" class="product-thumb" loading="lazy">
                <div class="product-badges">
                    ${badges.join('')}
                </div>
                <div class="stock-badge ${product.stock > 0 ? 'in-stock' : 'out-of-stock'}">
                    ${stockStatus}
                </div>
                <div class="product-actions-overlay">
                    <button class="action-btn wishlist" onclick="event.stopPropagation(); toggleWishlist(${product.id})" title="Add to Wishlist">
                        <i class="fas fa-heart"></i>
                    </button>
                    <button class="action-btn quick-view" onclick="event.stopPropagation(); showProductDetail(${product.id})" title="Quick View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn share" onclick="event.stopPropagation(); shareProduct(${product.id})" title="Share">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
            </div>
            <div class="product-content">
                <div class="product-category">${product.category || 'General'}</div>
                <h3 class="product-title">${product.name}</h3>
                <p class="product-description">${product.description}</p>

                <div class="product-meta">
                    ${priceHtml}
                </div>
                <div class="product-actions">
                    <button class="btn btn-primary add-to-cart-btn" onclick="event.stopPropagation(); addToCart(${product.id})" ${!isInStock(product.stock) ? 'disabled' : ''}>
                        ${!isInStock(product.stock) ? 'Out of Stock' : 'Add to Cart'}
                    </button>
                    <button class="btn btn-quick-view" onclick="event.stopPropagation(); showProductDetail(${product.id})" title="Quick View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-whatsapp" onclick="event.stopPropagation(); orderByWhatsApp(${product.id})" title="Order via WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                        Order Now
                    </button>
                </div>
            </div>
        `;
        grid.appendChild(productCard);
    });
}

/**
 * Filters products based on the selected category.
 * @param {string} category - The category to filter by.
 */
function filterProducts(category) {
    currentFilter = category;
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    // Find and activate the clicked filter button
    const activeButton = document.querySelector(`[onclick*="filterProducts('${category}')"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }

    const filteredProducts = category === 'all'
        ? storeProducts
        : storeProducts.filter(p => p.category.toUpperCase() === category.toUpperCase());
    loadProducts(filteredProducts);
}

/**
 * Searches for products based on the search term.
 */
function searchProducts() {
    const searchInput = document.getElementById('productSearch') || document.getElementById('searchInput');
    if (!searchInput) {
        console.warn('Search input element not found');
        return;
    }

    const searchTerm = searchInput.value.toLowerCase().trim();
    if (!searchTerm) {
        filterProducts(currentFilter);
        return;
    }

    const filteredProducts = storeProducts.filter(product =>
        (product.name.toLowerCase().includes(searchTerm) ||
        product.description.toLowerCase().includes(searchTerm) ||
        product.category.toLowerCase().includes(searchTerm))
    );

    loadProducts(filteredProducts);
    showNotification(`Found ${filteredProducts.length} products matching "${searchTerm}"`);
}

/**
 * Sorts products based on the selected option.
 */
function sortProducts() {
    const sortSelect = document.getElementById('sortSelect');
    if (!sortSelect) {
        console.warn('Sort select element not found');
        return;
    }

    const sortValue = sortSelect.value;
    let sortedProducts = [...storeProducts];

    switch(sortValue) {
        case 'name':
            sortedProducts.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'price-low':
            sortedProducts.sort((a, b) => {
                const priceA = parseFloat(a.discount_price) || parseFloat(a.price);
                const priceB = parseFloat(b.discount_price) || parseFloat(b.price);
                return priceA - priceB;
            });
            break;
        case 'price-high':
            sortedProducts.sort((a, b) => {
                const priceA = parseFloat(a.discount_price) || parseFloat(a.price);
                const priceB = parseFloat(b.discount_price) || parseFloat(b.price);
                return priceB - priceA;
            });
            break;
        case 'featured':
            // Assuming featured products have a 'featured' property or use discount_price
            sortedProducts.sort((a, b) => {
                const featuredA = a.featured || (a.discount_price && parseFloat(a.discount_price) < parseFloat(a.price));
                const featuredB = b.featured || (b.discount_price && parseFloat(b.discount_price) < parseFloat(b.price));
                return featuredB - featuredA;
            });
            break;
        default:
            break;
    }

    loadProducts(sortedProducts);
}

/**
 * Sets the view mode (grid or list).
 * @param {string} viewMode - The view mode to set ('grid' or 'list').
 */
function setView(viewMode) {
    const productsGrid = document.getElementById('productsGrid');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    if (!productsGrid) {
        console.warn('Products grid element not found');
        return;
    }

    // Update button states
    viewButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('onclick').includes(viewMode)) {
            btn.classList.add('active');
        }
    });

    // Update grid class
    if (viewMode === 'list') {
        productsGrid.classList.add('list-view');
    } else {
        productsGrid.classList.remove('list-view');
    }
}

/**
 * Displays the details of a product in a modal.
 * @param {number} productId - The ID of the product to view.
 */
function viewProduct(productId) {
    const product = storeProducts.find(p => String(p.id) === String(productId));
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

// UI Functions
/**
 * Updates the display of the cart in both header and floating button.
 */
function updateCartDisplay() {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    // Update header cart count
    const headerCartCounts = document.querySelectorAll('.cart-count');
    headerCartCounts.forEach(cartCount => {
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
    });
    
    // Update floating cart button
    const floatingCartCount = document.getElementById('floatingCartCount');
    const floatingCartButton = document.getElementById('floatingCartButton');
    
    if (floatingCartCount) {
        floatingCartCount.textContent = totalItems;
        floatingCartCount.style.display = totalItems > 0 ? 'flex' : 'none';
    }
    
    if (floatingCartButton) {
        floatingCartButton.style.display = totalItems > 0 ? 'flex' : 'none';
        
        // Add pulse animation when item is added
        floatingCartButton.classList.add('cart-pulse');
        setTimeout(() => {
            floatingCartButton.classList.remove('cart-pulse');
        }, 600);
    }
    
    // Update cart modal/sidebar if exists
    const cartCount = document.getElementById('cartCount');
    const cartItems = document.getElementById('cartItems');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartTax = document.getElementById('cartTax');
    const cartTotalAmount = document.getElementById('cartTotalAmount');

    if (cartCount) {
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
    }

    if (cartItems) {
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
    }

    if (cartSubtotal && cartTax && cartTotalAmount) {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * 0.16;
        const total = subtotal + tax;

        cartSubtotal.textContent = `KSh ${subtotal.toLocaleString()}`;
        cartTax.textContent = `KSh ${Math.round(tax).toLocaleString()}`;
        cartTotalAmount.textContent = `KSh ${Math.round(total).toLocaleString()}`;
    }
}

/**
 * Toggles the cart sidebar.
 */
function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    if (cartSidebar) {
        cartSidebar.classList.toggle('open');
    } else {
        console.warn('Cart sidebar element not found');
    }
}

/**
 * Shows a notification message.
 * @param {string} message - The message to display.
 * @param {string} [type='success'] - The type of notification (success or error).
 */
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

/**
 * Shows a modal with the given content.
 * @param {string} title - The title of the modal.
 * @param {string} content - The content of the modal.
 * @param {string} modalId - The ID of the modal.
 */
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

/**
 * Hides the modal with the given ID.
 * @param {string} modalId - The ID of the modal to hide.
 */
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.remove();
}

// Checkout Functions
/**
 * Starts the checkout process.
 */
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

/**
 * Hides the checkout modal.
 */
function hideCheckout() {
    const checkoutModal = document.getElementById('checkoutModal');
    if (checkoutModal) {
        checkoutModal.classList.add('hidden');
    }
}

/**
 * Shows the specified step in the checkout process.
 * @param {number} step - The step to show.
 */
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
function saveDeliveryInfo(event) {
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

/**
 * Gets the HTML content for the confirmation step.
 * @returns {string} - The HTML content for the confirmation step.
 */
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

/**
 * Places the order by sending the order data to the server.
 */
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

/**
 * Loads categories dynamically from the API
 */
async function loadCategories() {
    try {
        const response = await fetch('/jowaki_electrical_srvs/api/get_categories.php');
        if (!response.ok) {
            console.error('Failed to load categories');
            return;
        }
        
        const data = await response.json();
        if (data.success && data.categories) {
            const filterButtons = document.querySelector('.filter-buttons');
            if (filterButtons) {
                // Clear existing buttons except "All Products"
                filterButtons.innerHTML = '<button class="filter-btn active" onclick="filterProducts(\'all\')">All Products</button>';
                
                // Add category buttons
                data.categories.forEach(category => {
                    const button = document.createElement('button');
                    button.className = 'filter-btn';
                    button.onclick = () => filterProducts(category.name);
                    button.textContent = category.display_name;
                    filterButtons.appendChild(button);
                });
            }
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

/**
 * Loads the current cart count from PHP session
 */
async function loadCartCount() {
    try {
        const response = await fetch('/jowaki_electrical_srvs/api/get_cart_count.php');
        const data = await response.json();
        
        if (data.success) {
            // Update all cart count displays
            const headerCartCounts = document.querySelectorAll('.cart-count');
            headerCartCounts.forEach(cartCount => {
                cartCount.textContent = data.cart_count;
                cartCount.style.display = data.cart_count > 0 ? 'flex' : 'none';
            });
            
            // Update floating cart button
            const floatingCartCount = document.getElementById('floatingCartCount');
            const floatingCartButton = document.getElementById('floatingCartButton');
            
            if (floatingCartCount) {
                floatingCartCount.textContent = data.cart_count;
                floatingCartCount.style.display = data.cart_count > 0 ? 'flex' : 'none';
            }
            
            if (floatingCartButton) {
                floatingCartButton.style.display = data.cart_count > 0 ? 'flex' : 'none';
            }
            
            // Load cart items from server to sync local cart
            if (data.cart_count > 0) {
                await loadCartItems();
            }
        }
    } catch (error) {
        console.error('Error loading cart count:', error);
    }
}

async function loadCartItems() {
    try {
        const response = await fetch('/jowaki_electrical_srvs/api/sync_cart.php');
        const data = await response.json();
        
        if (data.success && data.cart_items) {
            cart = data.cart_items;
            updateCartDisplay();
        }
    } catch (error) {
        console.error('Error loading cart items:', error);
    }
}

// Missing functions to add
function toggleCartPulse() {
    const cartButton = document.getElementById('floatingCartButton');
    if (cartButton) {
        cartButton.classList.add('cart-pulse');
        setTimeout(() => {
            cartButton.classList.remove('cart-pulse');
        }, 600);
    }
}

function displayCartPopup(product, quantity, imageSrc) {
    // Create a temporary popup showing the added item
    const popup = document.createElement('div');
    popup.className = 'cart-popup';
    popup.innerHTML = `
        <div class="popup-content">
            <img src="${imageSrc}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
            <div>
                <h4>${product.name}</h4>
                <p>Added ${quantity} to cart</p>
            </div>
        </div>
    `;
    
    // Add popup styles if not already added
    if (!document.getElementById('popup-styles')) {
        const styles = document.createElement('style');
        styles.id = 'popup-styles';
        styles.textContent = `
            .cart-popup {
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--primary-color);
                color: white;
                padding: 1rem;
                border-radius: 8px;
                box-shadow: var(--shadow-lg);
                z-index: 10000;
                animation: slideInRight 0.3s ease-out;
            }
            .popup-content {
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            .popup-content h4 {
                margin: 0;
                font-size: 0.9rem;
            }
            .popup-content p {
                margin: 0;
                font-size: 0.8rem;
                opacity: 0.9;
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(popup);
    
    // Remove popup after 3 seconds
    setTimeout(() => {
        popup.remove();
    }, 3000);
}

// WhatsApp Order Function
function orderByWhatsApp(productId = null) {
    let message = "Hello Jowaki Electrical, I would like to inquire about your products.";
    
    if (productId) {
        const product = storeProducts.find(p => String(p.id) === String(productId));
        
        if (product) {
            const price = parseFloat(product.discount_price) || parseFloat(product.price);
            message = `Hello Jowaki Electrical, I would like to order:\n\nProduct: ${product.name}\nPrice: KSh ${price.toLocaleString()}\n\nPlease provide more details about this product.`;
        }
    }
    
    const whatsappUrl = `https://wa.me/0721442248?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

// Make function globally available
window.orderByWhatsApp = orderByWhatsApp;

// Wishlist functionality
function toggleWishlist(productId) {
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const index = wishlist.indexOf(productId);
    
    if (index > -1) {
        wishlist.splice(index, 1);
        showNotification('Removed from wishlist', 'info');
    } else {
        wishlist.push(productId);
        showNotification('Added to wishlist', 'success');
    }
    
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    updateWishlistButton(productId, index === -1);
}

function updateWishlistButton(productId, isInWishlist) {
    const wishlistBtn = document.querySelector(`[onclick*="toggleWishlist(${productId})"]`);
    if (wishlistBtn) {
        const icon = wishlistBtn.querySelector('i');
        if (icon) {
            icon.className = isInWishlist ? 'fas fa-heart' : 'far fa-heart';
            wishlistBtn.style.color = isInWishlist ? '#e74c3c' : '#666';
        }
    }
}

// Share product functionality
function shareProduct(productId) {
    const product = storeProducts.find(p => String(p.id) === String(productId));
    if (!product) {
        showNotification('Product not found!', 'error');
        return;
    }
    
    const shareData = {
        title: product.name,
        text: `Check out this amazing product: ${product.name}`,
        url: window.location.href
    };
    
    if (navigator.share) {
        navigator.share(shareData)
            .then(() => showNotification('Shared successfully!', 'success'))
            .catch(err => {
                console.log('Error sharing:', err);
                fallbackShare(shareData);
            });
    } else {
        fallbackShare(shareData);
    }
}

function fallbackShare(shareData) {
    const text = `${shareData.title}\n${shareData.text}\n${shareData.url}`;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text)
            .then(() => showNotification('Link copied to clipboard!', 'success'))
            .catch(() => showNotification('Failed to copy link', 'error'));
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Link copied to clipboard!', 'success');
    }
}

// Make functions globally available
window.toggleWishlist = toggleWishlist;
window.shareProduct = shareProduct;
window.showProductDetail = showProductDetail;
window.hideProductDetail = hideProductDetail;

// Product Detail Functions
function showProductDetail(productId) {
    const product = storeProducts.find(p => String(p.id) === String(productId));
    if (!product) {
        showNotification('Product not found!', 'error');
        return;
    }

    const actualPrice = parseFloat(product.discount_price) || parseFloat(product.price);
    const originalPrice = parseFloat(product.price);
    const imageSrc = product.image_paths ? 
        (Array.isArray(JSON.parse(product.image_paths)) ? JSON.parse(product.image_paths)[0] : product.image_paths) 
        : 'placeholder.jpg';
    
    const stockStatus = product.stock > 0 ? 'in-stock' : 'out-of-stock';
    const stockText = product.stock > 0 ? 'In Stock' : 'Out of Stock';

    const modalContent = `
        <div class="product-detail-content">
            <div class="product-detail-image">
                <img src="${imageSrc}" alt="${product.name}">
                <div class="stock-badge ${stockStatus}">${stockText}</div>
            </div>
            <div class="product-detail-info">
                <h3>${product.name}</h3>
                <div class="product-detail-price">
                    ${actualPrice < originalPrice ? `<span style="text-decoration: line-through; color: #95a5a6; font-size: 1rem;">KSh ${originalPrice.toLocaleString()}</span> ` : ''}
                    KSh ${actualPrice.toLocaleString()}
                </div>
                <div class="product-detail-description">
                    ${product.description || 'No description available.'}
                </div>
                <div class="product-detail-specs">
                    <h4>Product Specifications</h4>
                    <div class="spec-item">
                        <span class="spec-label">Category:</span>
                        <span class="spec-value">${product.category || 'N/A'}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Brand:</span>
                        <span class="spec-value">${product.brand || 'N/A'}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Stock:</span>
                        <span class="spec-value">${product.stock} units</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Warranty:</span>
                        <span class="spec-value">${product.warranty_months || 0} months</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Weight:</span>
                        <span class="spec-value">${product.weight_kg || 0} kg</span>
                    </div>
                </div>
                <div class="product-detail-actions">
                    <button class="btn btn-primary" onclick="addToCart(${product.id}); hideProductDetail();" ${product.stock <= 0 ? 'disabled' : ''}>
                        ${product.stock <= 0 ? 'Out of Stock' : 'Add to Cart'}
                    </button>
                    <button class="btn btn-whatsapp" onclick="orderByWhatsApp(${product.id})">
                        <i class="fab fa-whatsapp"></i>
                        Order via WhatsApp
                    </button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('productDetailTitle').textContent = product.name;
    document.getElementById('productDetailContent').innerHTML = modalContent;
    showModal('Product Details', '', 'productDetailModal');
}

function hideProductDetail() {
    hideModal('productDetailModal');
}



// Initialize the application
document.addEventListener('DOMContentLoaded', async function() {
    try {
        console.log('Starting product fetch at', new Date().toISOString());
        
        // Load cart count from PHP session first
        await loadCartCount();
        
        // Load categories first
        await loadCategories();
        
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
            storeProducts = data.products;
        } else if (Array.isArray(data)) {
            storeProducts = data;
        } else if (data.products && Array.isArray(data.products)) {
            storeProducts = data.products;
        } else {
            console.error('Invalid response structure:', data);
            storeProducts = [];
            showNotification('No products available at the moment.', 'error');
        }

        if (!Array.isArray(storeProducts)) {
            console.error('storeProducts is not an array:', storeProducts);
            storeProducts = [];
            showNotification('Failed to load products: Invalid data format.', 'error');
        }

        console.log('Products before processing:', storeProducts);
        storeProducts.forEach(p => {
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

        console.log('Processed products:', storeProducts);
        loadProducts();
        updateCartDisplay();

        // Load cart from server instead of localStorage
        await loadCartItems();

        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchProducts();
                }
            });
        } else {
            console.warn('Search input element not found');
        }
    } catch (error) {
        console.error('Error loading products:', error);
        storeProducts = [];
        loadProducts();
        showNotification(`Failed to load products: ${error.message}`, 'error');
    }
});
