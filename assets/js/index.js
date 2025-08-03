// Jowaki Electrical Services - Main JavaScript

// Sample products data
const featuredProducts = [
    {
        id: 1,
        name: "LED Smart Bulb 12W",
        description: "Energy-efficient smart LED bulb with app control and color changing features",
        price: 1500,
        originalPrice: 2000,
        category: "LIGHTING",
        rating: 4.5,
        reviewCount: 24,
        inStock: true,
        isNew: true,
        isFeatured: true,
        image: "https://images.unsplash.com/photo-1524484485831-a92ffc0de03f?w=300&h=200&fit=crop"
    },
    {
        id: 2,
        name: "Premium Wall Socket",
        description: "High-quality 13A wall socket with USB charging ports",
        price: 800,
        category: "SOCKETS & SWITCHES",
        rating: 4.8,
        reviewCount: 16,
        inStock: true,
        isFeatured: true,
        image: "https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=300&h=200&fit=crop"
    },
    {
        id: 3,
        name: "Circuit Breaker 20A",
        description: "Reliable circuit breaker for home electrical protection",
        price: 1200,
        category: "CIRCUIT PROTECTION",
        rating: 4.7,
        reviewCount: 12,
        inStock: true,
        isFeatured: true,
        image: "https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=300&h=200&fit=crop"
    },
    {
        id: 4,
        name: "Electrical Wire 2.5mm",
        description: "High-grade copper electrical wire for household wiring",
        price: 300,
        originalPrice: 400,
        category: "WIRING & CABLES",
        rating: 4.3,
        reviewCount: 8,
        inStock: true,
        isFeatured: true,
        image: "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=300&h=200&fit=crop"
    }
];

// Shopping cart functionality
let cart = JSON.parse(localStorage.getItem('jowaki_cart')) || [];

/**
 * Add product to cart
 * @param {number} productId - ID of the product to add
 */
function addToCart(productId) {
    const product = featuredProducts.find(p => p.id === productId);
    if (product && product.inStock) {
        const existingItem = cart.find(item => item.id === productId);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        updateCartUI();
        saveCart();
        showNotification(`${product.name} added to cart!`, 'success');
    } else {
        showNotification('Product not available', 'error');
    }
}

/**
 * Remove product from cart
 * @param {number} productId - ID of the product to remove
 */
function removeFromCart(productId) {
    const product = cart.find(item => item.id === productId);
    cart = cart.filter(item => item.id !== productId);
    updateCartUI();
    saveCart();
    if (product) {
        showNotification(`${product.name} removed from cart`, 'info');
    }
}

/**
 * Update quantity of item in cart
 * @param {number} productId - ID of the product
 * @param {number} newQuantity - New quantity
 */
function updateCartQuantity(productId, newQuantity) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        if (newQuantity <= 0) {
            removeFromCart(productId);
        } else {
            item.quantity = newQuantity;
            updateCartUI();
            saveCart();
        }
    }
}

/**
 * Update cart UI elements
 */
function updateCartUI() {
    const cartCount = document.getElementById('cart-count');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');

    if (!cartCount || !cartItems || !cartTotal) return;

    // Update cart count
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;

    // Update cart items
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="text-center py-8">
                <i data-lucide="shopping-cart" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <p class="text-gray-500">Your cart is empty</p>
                <p class="text-sm text-gray-400">Add some products to get started</p>
            </div>
        `;
        cartTotal.textContent = 'KSh 0';
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="flex items-center flex-1">
                    <img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded mr-3">
                    <div class="flex-1">
                        <h4 class="font-medium text-sm">${item.name}</h4>
                        <p class="text-xs text-gray-600">KSh ${item.price.toLocaleString()}</p>
                        <div class="flex items-center mt-1">
                            <button onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})" 
                                    class="w-6 h-6 flex items-center justify-center bg-gray-200 rounded-l text-sm hover:bg-gray-300">
                                -
                            </button>
                            <span class="w-8 h-6 flex items-center justify-center bg-gray-100 text-sm">${item.quantity}</span>
                            <button onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})" 
                                    class="w-6 h-6 flex items-center justify-center bg-gray-200 rounded-r text-sm hover:bg-gray-300">
                                +
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <button onclick="removeFromCart(${item.id})" 
                            class="text-red-500 hover:text-red-700 mb-2" title="Remove item">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                    <span class="font-semibold text-sm">KSh ${(item.price * item.quantity).toLocaleString()}</span>
                </div>
            </div>
        `).join('');

        // Calculate total
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotal.textContent = `KSh ${total.toLocaleString()}`;
    }

    // Reinitialize icons for new elements
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Save cart to localStorage
 */
function saveCart() {
    localStorage.setItem('jowaki_cart', JSON.stringify(cart));
}

/**
 * Toggle cart sidebar
 */
function toggleCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    
    if (!cartSidebar || !cartOverlay) return;
    
    if (cartSidebar.classList.contains('translate-x-full')) {
        cartSidebar.classList.remove('translate-x-full');
        cartOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        cartSidebar.classList.add('translate-x-full');
        cartOverlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

/**
 * Create star rating HTML
 * @param {number} rating - Rating value (0-5)
 * @returns {string} HTML string for star rating
 */
function createStarRating(rating) {
    const stars = [];
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(rating)) {
            stars.push('<i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-current"></i>');
        } else if (i === Math.ceil(rating) && rating % 1 !== 0) {
            stars.push('<i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-current opacity-50"></i>');
        } else {
            stars.push('<i data-lucide="star" class="w-4 h-4 text-gray-300"></i>');
        }
    }
    return stars.join('');
}

/**
 * Create product card HTML
 * @param {Object} product - Product object
 * @returns {string} HTML string for product card
 */
function createProductCard(product) {
    const discountPercentage = product.originalPrice 
        ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
        : null;

    return `
        <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
            <div class="relative h-48 bg-gray-100 overflow-hidden">
                <img
                    src="${product.image}"
                    alt="${product.name}"
                    class="product-image w-full h-full object-cover"
                    loading="lazy"
                    onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDMwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xNTAgMTAwTDE3NSAxMjVIMTI1TDE1MCAx"'"
                />
                
                <!-- Badges -->
                <div class="absolute top-2 left-2 flex flex-col gap-1">
                    ${discountPercentage ? `<span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">-${discountPercentage}%</span>` : ''}
                    ${product.isNew ? '<span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-semibold">New</span>' : ''}
                    ${product.isFeatured ? '<span class="bg-blue-500 text-white px-2 py-1 rounded text-xs font-semibold">Featured</span>' : ''}
                </div>

                <!-- Quick Actions -->
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="flex flex-col gap-2">
                        <button onclick="addToWishlist(${product.id})" 
                                class="bg-white p-2 rounded-full shadow-md hover:bg-gray-50 transition-colors" 
                                title="Add to wishlist">
                            <i data-lucide="heart" class="w-4 h-4 text-gray-600 hover:text-red-500"></i>
                        </button>
                        <button onclick="quickView(${product.id})" 
                                class="bg-white p-2 rounded-full shadow-md hover:bg-gray-50 transition-colors" 
                                title="Quick view">
                            <i data-lucide="eye" class="w-4 h-4 text-gray-600 hover:text-green-600"></i>
                        </button>
                    </div>
                </div>

                ${!product.inStock ? `
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <span class="bg-red-500 text-white px-4 py-2 rounded font-semibold">
                            Out of Stock
                        </span>
                    </div>
                ` : ''}
            </div>

            <div class="p-4">
                <!-- Category -->
                <p class="text-xs text-green-600 font-medium mb-1 uppercase tracking-wide">
                    ${product.category}
                </p>

                <!-- Product Name -->
                <h3 class="font-semibold text-gray-900 mb-2 hover:text-green-600 transition-colors line-clamp-2">
                    ${product.name}
                </h3>

                <!-- Description -->
                <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                    ${product.description}
                </p>

                <!-- Rating -->
                <div class="flex items-center mb-3">
                    <div class="flex items-center">
                        ${createStarRating(product.rating)}
                    </div>
                    <span class="text-sm text-gray-600 ml-2">
                        ${product.rating} (${product.reviewCount})
                    </span>
                </div>

                <!-- Price and Actions -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg font-bold text-green-600">
                            KSh ${product.price.toLocaleString()}
                        </span>
                        ${product.originalPrice ? `<span class="text-sm text-gray-500 line-through">KSh ${product.originalPrice.toLocaleString()}</span>` : ''}
                    </div>
                    
                    <button
                        onclick="addToCart(${product.id})"
                        ${!product.inStock ? 'disabled' : ''}
                        class="${product.inStock 
                            ? 'bg-green-600 text-white hover:bg-green-700' 
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        } px-4 py-2 rounded text-sm font-semibold transition-colors duration-200 flex items-center gap-2"
                        title="${product.inStock ? 'Add to cart' : 'Out of stock'}"
                    >
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        ${product.inStock ? 'Add to Cart' : 'Unavailable'}
                    </button>
                </div>
            </div>
        </div>
    `;
}

/**
 * Load featured products into the page
 */
function loadFeaturedProducts() {
    const productsContainer = document.getElementById('featured-products');
    if (!productsContainer) return;

    productsContainer.innerHTML = featuredProducts.map(product => createProductCard(product)).join('');
    
    // Initialize icons for new elements
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Handle search functionality
 * @param {Event} event - Keyboard event
 */
function handleSearch(event) {
    if (event.key === 'Enter') {
        const query = event.target.value.trim();
        if (query) {
            // In a real app, this would redirect to search results
            showNotification(`Search functionality coming soon! You searched for: "${query}"`, 'info');
            console.log('Search query:', query);
        }
    }
}

/**
 * Show login modal/page
 */
function showLogin() {
    showNotification('Login page coming soon!', 'info');
    console.log('Show login');
}

/**
 * Show signup modal/page
 */
function showSignup() {
    showNotification('Signup page coming soon!', 'info');
    console.log('Show signup');
}

/**
 * Add product to wishlist
 * @param {number} productId - ID of the product
 */
function addToWishlist(productId) {
    const product = featuredProducts.find(p => p.id === productId);
    if (product) {
        showNotification(`${product.name} added to wishlist!`, 'success');
        console.log('Added to wishlist:', productId);
    }
}

/**
 * Show quick view modal
 * @param {number} productId - ID of the product
 */
function quickView(productId) {
    const product = featuredProducts.find(p => p.id === productId);
    if (product) {
        showNotification('Quick view coming soon!', 'info');
        console.log('Quick view:', product);
    }
}

/**
 * Show notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, error, info)
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : 
                   type === 'error' ? 'bg-red-600' : 'bg-blue-600';
    
    notification.className = `notification ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    notification.innerHTML = `
        <div class="flex items-center gap-2">
            <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="w-5 h-5"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Initialize icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Show notification
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
        notification.classList.add('show');
    }, 100);
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Handle checkout process
 */
function checkout() {
    if (cart.length === 0) {
        showNotification('Your cart is empty', 'error');
        return;
    }
    
    // In a real app, this would redirect to checkout page
    showNotification('Checkout functionality coming soon!', 'info');
    console.log('Checkout with items:', cart);
}

/**
 * Clear entire cart
 */
function clearCart() {
    if (cart.length === 0) {
        showNotification('Cart is already empty', 'info');
        return;
    }
    
    if (confirm('Are you sure you want to clear your cart?')) {
        cart = [];
        updateCartUI();
        saveCart();
        showNotification('Cart cleared', 'info');
    }
}

/**
 * Initialize the page
 */
function loadDynamicProducts() {
    const productsContainer = document.getElementById('product-list');
    const loadingIndicator = document.getElementById('products-loading');

    if (!productsContainer) return;

    fetch('API/get_products.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                productsContainer.innerHTML = data.products.slice(0, 4).map(product => `
                    <div class="product-card">
                        ${product.discount_price ? '<div class="product-badge">Sale</div>' : ''}
                        <img src="${product.image}" alt="${product.name}" class="product-image" onerror="this.src='placeholder.jpg'">
                        <div class="product-content">
                            <div class="product-category">${product.category}</div>
                            <h3>${product.name}</h3>
                            <p class="product-description">${product.description || ''}</p>
                            <div class="product-footer">
                                <span class="product-price">KSh ${product.price.toLocaleString()}</span>
                                <button onclick="addToCart(${product.id})" class="btn-primary" ${product.stock <= 0 ? 'disabled' : ''}>
                                    ${product.stock <= 0 ? 'Out of Stock' : 'Add to Cart'}
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                productsContainer.innerHTML = '<p class="text-center text-gray-500">No products available at the moment.</p>';
            }

            loadingIndicator.style.display = 'none';
        })
        .catch(err => {
            console.error('Failed to load products:', err);
            loadingIndicator.innerHTML = '<p class="text-center text-red-500">Failed to load products. Please try again later.</p>';
        });
}


function initializePage() {
    // Load featured products
    loadFeaturedProducts();
    
    // Load dynamic products from API
    loadDynamicProducts();
    
    // Update cart UI
    updateCartUI();
    
    // Initialize smooth scrolling
    initSmoothScrolling();
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Add checkout button event listener
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', checkout);
    }
    
    // Add keyboard shortcut for cart (Ctrl+Shift+C)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'C') {
            e.preventDefault();
            toggleCart();
        }
    });
    
    console.log('Jowaki Electrical Services website initialized');
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializePage);

// Handle page visibility changes to update cart if changed in another tab
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        const savedCart = JSON.parse(localStorage.getItem('jowaki_cart')) || [];
        if (JSON.stringify(cart) !== JSON.stringify(savedCart)) {
            cart = savedCart;
            updateCartUI();
        }
    }
});

// Export functions for potential use in other scripts
window.JowakiStore = {
    addToCart,
    removeFromCart,
    updateCartQuantity,
    toggleCart,
    clearCart,
    checkout,
    showNotification,
    getFeaturedProducts: () => featuredProducts,
    getCart: () => cart
};