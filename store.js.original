// Main Store Application - Modular Version
// This file imports and coordinates all the store modules

// Import all modules
import * as CartModule from './js/modules/store-cart.js';
import * as ProductsModule from './js/modules/store-products.js';
import * as UIModule from './js/modules/store-ui.js';
import * as CheckoutModule from './js/modules/store-checkout.js';

// Global state variables
let storeProducts = [];
let cart = [];

// Initialize the application
document.addEventListener('DOMContentLoaded', async function() {
    try {
        console.log('Starting modular store application...');
        
        // Initialize UI first
        UIModule.initializeUI();
        
        // Load cart count from PHP session first
        await CartModule.loadCartCount();
        
        // Load categories first
        await ProductsModule.loadCategories();
        
        // Load products from API
        storeProducts = await ProductsModule.loadProductsFromAPI();
        
        // Load cart from server
        await CartModule.loadCartItems();
        
        // Update cart display
        CartModule.updateCartDisplay();
        
        // Set up global function references for inline event handlers
        setupGlobalFunctions();
        
        console.log('Store application initialized successfully');
        
    } catch (error) {
        console.error('Error initializing store application:', error);
        UIModule.showNotification(`Failed to initialize store: ${error.message}`, 'error');
    }
});

/**
 * Sets up global function references for inline event handlers
 */
function setupGlobalFunctions() {
    // Cart functions
    window.addToCart = (productId, quantity = 1) => {
        CartModule.addToCart(productId, quantity, storeProducts);
    };
    
    window.removeFromCart = (productId) => {
        CartModule.removeFromCart(productId);
    };
    
    window.updateQuantity = (productId, change) => {
        CartModule.updateQuantity(productId, change, storeProducts);
    };
    
    window.clearCart = () => {
        CartModule.clearCart();
    };
    
    window.toggleCart = () => {
        CartModule.toggleCart();
    };
    
    window.getCart = () => {
        return CartModule.getCart();
    };
    
    // Product functions
    window.filterProducts = (category) => {
        ProductsModule.filterProducts(category);
    };
    
    window.searchProducts = () => {
        ProductsModule.searchProducts();
    };
    
    window.sortProducts = () => {
        ProductsModule.sortProducts();
    };
    
    window.setView = (viewMode) => {
        ProductsModule.setView(viewMode);
    };
    
    window.showProductDetail = (productId) => {
        ProductsModule.showProductDetail(productId);
    };
    
    window.hideProductDetail = () => {
        ProductsModule.hideProductDetail();
    };
    
    window.viewProduct = (productId) => {
        ProductsModule.viewProduct(productId);
    };
    
    // UI functions
    window.toggleMobileFilters = () => {
        UIModule.toggleMobileFilters();
    };
    
    window.scrollCategories = (direction) => {
        UIModule.scrollCategories(direction);
    };
    
    window.toggleWishlist = (productId) => {
        UIModule.toggleWishlist(productId);
    };
    
    window.shareProduct = (productId) => {
        UIModule.shareProduct(productId, storeProducts);
    };
    
    window.orderByWhatsApp = (productId = null) => {
        UIModule.orderByWhatsApp(productId, storeProducts);
    };
    
    // Checkout functions
    window.startCheckout = () => {
        const currentCart = CartModule.getCart();
        CheckoutModule.startCheckout(currentCart);
    };
    
    window.hideCheckout = () => {
        CheckoutModule.hideCheckout();
    };
    
    window.showCheckoutStep = (step) => {
        CheckoutModule.showCheckoutStep(step);
    };
    
    window.saveCustomerInfo = (event) => {
        CheckoutModule.saveCustomerInfo(event);
    };
    
    window.saveDeliveryInfo = (event) => {
        CheckoutModule.saveDeliveryInfo(event);
    };
    
    window.processPayment = () => {
        const currentCart = CartModule.getCart();
        CheckoutModule.processPayment(currentCart);
    };
    
    window.placeOrder = () => {
        CheckoutModule.placeOrder();
    };
}

// Export functions for use in other modules
window.getStoreProducts = () => {
    return storeProducts;
};

window.setStoreProducts = (products) => {
    storeProducts = products;
};

// Make notification function available globally
window.showNotification = UIModule.showNotification;
window.showModal = UIModule.showModal;
window.hideModal = UIModule.hideModal;

console.log('Store modules loaded successfully');
