// Store Cart Module
// Handles all cart-related functionality

class CartModule {
    constructor() {
        this.cart = [];
        this.cartCount = 0;
    }

    // Load cart count from PHP session
    async loadCartCount() {
        try {
            const response = await fetch('api/get_cart_count.php');
            const data = await response.json();
            
            if (data.success) {
                this.cartCount = data.count;
                this.updateCartCountDisplay();
            }
        } catch (error) {
            console.error('Error loading cart count:', error);
        }
    }

    // Load cart items from server
    async loadCartItems() {
        try {
            const response = await fetch('api/get_cart_count.php');
            const data = await response.json();
            
            if (data.success && data.cart) {
                this.cart = data.cart;
                this.updateCartDisplay();
            }
        } catch (error) {
            console.error('Error loading cart items:', error);
        }
    }

    // Add item to cart
    async addToCart(productId, quantity = 1, products = []) {
        try {
            const response = await fetch('api/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.cartCount = data.cart_count;
                this.updateCartCountDisplay();
                this.showNotification('Item added to cart!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to add item to cart', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('Error adding item to cart', 'error');
        }
    }

    // Remove item from cart
    async removeFromCart(productId) {
        try {
            const response = await fetch('api/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.cartCount = data.cart_count;
                this.updateCartCountDisplay();
                this.updateCartDisplay();
                this.showNotification('Item removed from cart!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to remove item from cart', 'error');
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            this.showNotification('Error removing item from cart', 'error');
        }
    }

    // Update quantity
    async updateQuantity(productId, change, products = []) {
        try {
            const response = await fetch('api/update_cart_quantity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    change: change
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.cartCount = data.cart_count;
                this.updateCartCountDisplay();
                this.updateCartDisplay();
            } else {
                this.showNotification(data.message || 'Failed to update quantity', 'error');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            this.showNotification('Error updating quantity', 'error');
        }
    }

    // Clear cart
    async clearCart() {
        try {
            const response = await fetch('api/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    clear_all: true
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.cart = [];
                this.cartCount = 0;
                this.updateCartCountDisplay();
                this.updateCartDisplay();
                this.showNotification('Cart cleared!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to clear cart', 'error');
            }
        } catch (error) {
            console.error('Error clearing cart:', error);
            this.showNotification('Error clearing cart', 'error');
        }
    }

    // Toggle cart visibility
    toggleCart() {
        const cartContainer = document.getElementById('cart-container');
        if (cartContainer) {
            cartContainer.classList.toggle('show');
        }
    }

    // Get cart data
    getCart() {
        return this.cart;
    }

    // Update cart count display
    updateCartCountDisplay() {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = this.cartCount;
            element.style.display = this.cartCount > 0 ? 'block' : 'none';
        });
    }

    // Update cart display
    updateCartDisplay() {
        const cartContainer = document.getElementById('cart-items');
        if (!cartContainer) return;

        if (this.cart.length === 0) {
            cartContainer.innerHTML = '<p>Your cart is empty</p>';
            return;
        }

        let cartHTML = '';
        let total = 0;

        this.cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            cartHTML += `
                <div class="cart-item">
                    <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-details">
                        <h4>${item.name}</h4>
                        <p>KSh ${item.price}</p>
                        <div class="quantity-controls">
                            <button onclick="updateQuantity(${item.id}, -1)">-</button>
                            <span>${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, 1)">+</button>
                        </div>
                    </div>
                    <button onclick="removeFromCart(${item.id})" class="remove-btn">×</button>
                </div>
            `;
        });

        cartHTML += `
            <div class="cart-total">
                <h3>Total: KSh ${total.toFixed(2)}</h3>
                <button onclick="window.location.href='checkout.php'" class="checkout-btn">Checkout</button>
            </div>
        `;

        cartContainer.innerHTML = cartHTML;
    }

    // Show notification
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Export the module
export default new CartModule();

