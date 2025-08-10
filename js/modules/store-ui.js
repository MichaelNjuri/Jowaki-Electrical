// UI Module - Handles all UI-related functionality

/**
 * Shows a notification message.
 * @param {string} message - The message to display.
 * @param {string} [type='success'] - The type of notification (success or error).
 */
export function showNotification(message, type = 'success') {
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
export function showModal(title, content, modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.warn(`Modal with ID '${modalId}' not found`);
        return;
    }

    // Set the title if there's a title element
    const titleElement = modal.querySelector('h2');
    if (titleElement) {
        titleElement.textContent = title;
    }

    // Set the content if there's a content element
    const contentElement = modal.querySelector('#productDetailContent');
    if (contentElement) {
        contentElement.innerHTML = content;
    }

    // Show the modal
    modal.classList.remove('hidden');
}

/**
 * Hides the modal with the given ID.
 * @param {string} modalId - The ID of the modal to hide.
 */
export function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
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

/**
 * Mobile filter functionality
 */
export function toggleMobileFilters() {
    const filterPanel = document.getElementById('categoryFilter');
    if (filterPanel) {
        filterPanel.classList.toggle('active');
    }
}

/**
 * Category scrolling functionality
 */
export function scrollCategories(direction) {
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

/**
 * Wishlist functionality
 */
export function toggleWishlist(productId) {
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

export function updateWishlistButton(productId, isInWishlist) {
    const wishlistBtn = document.querySelector(`[onclick*="toggleWishlist(${productId})"]`);
    if (wishlistBtn) {
        const icon = wishlistBtn.querySelector('i');
        if (icon) {
            icon.className = isInWishlist ? 'fas fa-heart' : 'far fa-heart';
            wishlistBtn.style.color = isInWishlist ? '#e74c3c' : '#666';
        }
    }
}

/**
 * Share product functionality
 */
export function shareProduct(productId, storeProducts) {
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

/**
 * WhatsApp Order Function
 */
export function orderByWhatsApp(productId = null, storeProducts) {
    let message = "Hello Jowaki Electrical, I would like to inquire about your products.";
    
    if (productId) {
        const product = storeProducts.find(p => String(p.id) === String(productId));
        
        if (product) {
            const price = parseFloat(product.discount_price) || parseFloat(product.price);
            message = `Hello Jowaki Electrical, I would like to order:\n\nProduct: ${product.name}\nPrice: KSh ${price.toLocaleString()}\n\nPlease provide more details about this product.`;
        }
    }
    
    // Get WhatsApp number from meta tag or use default
    const whatsappNumber = document.querySelector('meta[name="whatsapp-number"]')?.getAttribute('content') || '254721442248';
    
    // Clean the WhatsApp number (remove any non-numeric characters except +)
    let cleanNumber = whatsappNumber.replace(/[^\d+]/g, '');
    
    // Handle local format (07xxxxxxxx) - convert to international format
    if (cleanNumber.startsWith('07') && cleanNumber.length === 10) {
        cleanNumber = '254' + cleanNumber.substring(1);
    }
    
    // Ensure the number starts with the country code
    const finalNumber = cleanNumber.startsWith('+') ? cleanNumber.substring(1) : cleanNumber;
    
    const whatsappUrl = `https://wa.me/${finalNumber}?text=${encodeURIComponent(message)}`;
    
    // Open WhatsApp in a new tab
    const newWindow = window.open(whatsappUrl, '_blank');
    
    // Fallback if popup is blocked
    if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
        // Try to redirect in the same window
        window.location.href = whatsappUrl;
    }
}

/**
 * Load store categories dynamically from the API
 */
async function loadStoreCategories() {
    try {
        const response = await fetch('/jowaki_electrical_srvs/API/store_categories.php');
        if (!response.ok) {
            console.error('Failed to load store categories');
            return;
        }
        
        const data = await response.json();
        if (data.success && data.categories) {
            const categoryScroll = document.getElementById('categoryScroll');
            if (categoryScroll) {
                // Clear existing buttons
                categoryScroll.innerHTML = '';
                
                // Add "All Products" button first
                const allProductsBtn = document.createElement('button');
                allProductsBtn.className = 'category-btn active';
                allProductsBtn.onclick = () => filterProducts('all');
                allProductsBtn.innerHTML = `
                    <i class="fas fa-th-large"></i>
                    All Products
                `;
                categoryScroll.appendChild(allProductsBtn);
                
                // Add category buttons from API
                data.categories.forEach(category => {
                    const button = document.createElement('button');
                    button.className = 'category-btn';
                    button.onclick = () => filterProducts(category.filter_value);
                    button.innerHTML = `
                        <i class="${category.icon}"></i>
                        ${category.name}
                    `;
                    categoryScroll.appendChild(button);
                });
            }
        }
    } catch (error) {
        console.error('Error loading store categories:', error);
    }
}

/**
 * Initialize UI event listeners
 */
export function initializeUI() {
    // Load store categories
    loadStoreCategories();
    
    // Add event listeners for search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                // This will be handled by the products module
                if (window.searchProducts) {
                    window.searchProducts();
                }
            }
        });
    } else {
        console.warn('Search input element not found');
    }

    // Add event listeners for mobile filters
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    if (mobileFilterBtn) {
        mobileFilterBtn.addEventListener('click', toggleMobileFilters);
    }

    // Add event listeners for category scrolling
    const leftScrollBtn = document.getElementById('leftScrollBtn');
    const rightScrollBtn = document.getElementById('rightScrollBtn');
    
    if (leftScrollBtn) {
        leftScrollBtn.addEventListener('click', () => scrollCategories('left'));
    }
    
    if (rightScrollBtn) {
        rightScrollBtn.addEventListener('click', () => scrollCategories('right'));
    }
}

// Make functions globally available
window.showNotification = showNotification;
window.showModal = showModal;
window.hideModal = hideModal;
window.toggleCart = toggleCart;
window.toggleMobileFilters = toggleMobileFilters;
window.scrollCategories = scrollCategories;
window.toggleWishlist = toggleWishlist;
window.shareProduct = shareProduct;
window.orderByWhatsApp = orderByWhatsApp; 