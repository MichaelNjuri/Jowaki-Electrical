// Cart Module - Handles all cart-related functionality

// Global cart state
let cart = [];
let cartCount = 0;

/**
 * Adds an item to the cart.
 * @param {number} productId - The ID of the product to add.
 * @param {number} [quantity=1] - The quantity of the product to add.
 */
export async function addToCart(productId, quantity = 1, storeProducts) {
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
export async function removeFromCart(productId) {
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
export async function updateQuantity(productId, change, storeProducts) {
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
export async function clearCart() {
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
export async function saveCart() {
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

/**
 * Loads the current cart count from PHP session
 */
export async function loadCartCount() {
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

export async function loadCartItems() {
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

/**
 * Updates the display of the cart in both header and floating button.
 */
export function updateCartDisplay() {
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
export function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    if (cartSidebar) {
        cartSidebar.classList.toggle('open');
    } else {
        console.warn('Cart sidebar element not found');
    }
}

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

// Helper function
function isInStock(stock) {
    return stock > 0;
}

// Import notification function
function showNotification(message, type = 'success') {
    // This will be imported from store-ui.js
    if (window.showNotification) {
        window.showNotification(message, type);
    } else {
        console.log(`${type}: ${message}`);
    }
}

// Export cart state for other modules
export function getCart() {
    return cart;
}

export function setCart(newCart) {
    cart = newCart;
} 