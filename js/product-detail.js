// Product Detail Page JavaScript - Enhanced Version
console.log('Product detail JavaScript loaded');

// Global variables
let currentQuantity = 1;
let maxStock = 0;
let productId = 0;
let productName = '';
let productPrice = 0;
let wishlistItems = JSON.parse(localStorage.getItem('wishlist') || '[]');

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Product detail page initializing...');
    
    // Get product data from PHP variables (these will be set by the server)
    const quantityInput = document.querySelector('#quantity');
    const addToCartBtn = document.querySelector('.btn-primary');
    const productTitle = document.querySelector('.product-title');
    const currentPrice = document.querySelector('.current-price');
    
    maxStock = quantityInput ? parseInt(quantityInput.getAttribute('max')) || 0 : 0;
    productId = addToCartBtn ? parseInt(addToCartBtn.getAttribute('data-product-id')) || 0 : 0;
    productName = productTitle ? productTitle.textContent || '' : '';
    productPrice = currentPrice ? parseFloat(currentPrice.textContent.replace(/[^\d.]/g, '')) || 0 : 0;
    
    console.log('Product data loaded:', {
        maxStock,
        productId,
        productName,
        productPrice
    });
    
    // Update quantity display
    validateQuantity();
    
    // Initialize wishlist state
    initializeWishlistState();
    
    // Add smooth scrolling to related products
    const relatedSection = document.querySelector('.related-products-section');
    if (relatedSection) {
        relatedSection.style.opacity = '0';
        relatedSection.style.transform = 'translateY(50px)';
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.transition = 'all 0.8s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });
        
        observer.observe(relatedSection);
    }
});

// Image gallery functions
window.changeMainImage = function(imageSrc, thumbnail) {
    const mainImage = document.getElementById('mainProductImage');
    if (!mainImage) return;
    
    mainImage.style.opacity = '0.7';
    
    setTimeout(() => {
        mainImage.src = imageSrc;
        mainImage.style.opacity = '1';
    }, 200);

    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    thumbnail.classList.add('active');
}

// Quantity management
window.changeQuantity = function(change) {
    const quantityInput = document.getElementById('quantity');
    if (!quantityInput) return;
    
    let newValue = parseInt(quantityInput.value) + change;
    
    if (newValue >= 1 && newValue <= maxStock && maxStock > 0) {
        quantityInput.value = newValue;
        currentQuantity = newValue;
        updatePriceDisplay();
    }
}

window.updateQuantity = function(value) {
    const quantityInput = document.getElementById('quantity');
    if (!quantityInput) return;
    
    let newValue = parseInt(value);
    
    if (isNaN(newValue) || newValue < 1) {
        newValue = 1;
    } else if (maxStock > 0 && newValue > maxStock) {
        newValue = maxStock;
        showNotification(`Maximum quantity available is ${maxStock}`, 'error');
    } else if (maxStock <= 0) {
        newValue = 0;
        showNotification('Product is out of stock', 'error');
    }
    
    quantityInput.value = newValue;
    currentQuantity = newValue;
    updatePriceDisplay();
}

window.validateQuantity = function() {
    const quantityInput = document.getElementById('quantity');
    if (!quantityInput) return;
    
    let value = parseInt(quantityInput.value);
    
    if (isNaN(value) || value < 1) {
        value = 1;
    } else if (maxStock > 0 && value > maxStock) {
        value = maxStock;
        showNotification(`Maximum quantity available is ${maxStock}`, 'error');
    } else if (maxStock <= 0) {
        value = 0;
        showNotification('Product is out of stock', 'error');
    }
    
    quantityInput.value = value;
    currentQuantity = value;
    updatePriceDisplay();
}

window.updatePriceDisplay = function() {
    const totalPrice = productPrice * currentQuantity;
    const priceElements = document.querySelectorAll('.total-price');
    priceElements.forEach(el => {
        el.textContent = `KSh ${totalPrice.toLocaleString()}`;
    });
}

// Cart functionality
window.addToCart = async function(productId) {
    console.log('addToCart function called with productId:', productId);
    console.log('Current quantity:', currentQuantity);
    console.log('Max stock:', maxStock);
    
    const quantity = currentQuantity;
    
    // Check if product is in stock
    if (maxStock <= 0) {
        showNotification('Product is out of stock', 'error');
        return;
    }
    
    // Show loading state
    const addButton = document.querySelector('.btn-primary');
    const originalText = addButton.innerHTML;
    addButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    addButton.disabled = true;

    try {
        // Direct API call to add to cart
        const response = await fetch('API/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: productId,
                name: productName,
                price: productPrice,
                quantity: quantity,
                image: document.querySelector('#mainProductImage').src
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`${productName} added to cart successfully!`, 'success');
            
            // Update cart count if element exists
            const cartCount = document.querySelector('.cart-count');
            if (cartCount && data.cartCount) {
                cartCount.textContent = data.cartCount;
                cartCount.style.transform = 'scale(1.3)';
                setTimeout(() => {
                    cartCount.style.transform = 'scale(1)';
                }, 300);
            }
            
            // Add visual feedback
            addButton.style.background = 'var(--success-green)';
            addButton.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
            
            setTimeout(() => {
                addButton.style.background = '';
                addButton.innerHTML = originalText;
            }, 2000);
            
        } else {
            showNotification(data.message || 'Failed to add product to cart', 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showNotification('Error adding product to cart. Please try again.', 'error');
        
        // Fallback: Store in localStorage if API fails
        try {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: quantity,
                    image: document.querySelector('#mainProductImage').src
                });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            showNotification(`${productName} added to cart (offline mode)!`, 'success');
        } catch (localError) {
            console.error('Local storage error:', localError);
        }
    } finally {
        addButton.disabled = false;
        if (addButton.innerHTML.includes('Adding')) {
            addButton.innerHTML = originalText;
        }
    }
}

// Social sharing functions
window.shareOnFacebook = function() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(`Check out this amazing product: ${productName}`);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank', 'width=600,height=400');
}

window.shareOnTwitter = function() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(`Check out this amazing product: ${productName} - KSh ${productPrice.toLocaleString()}`);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank', 'width=600,height=400');
}

window.shareOnWhatsApp = function() {
    const text = encodeURIComponent(`Check out this amazing product: ${productName} - KSh ${productPrice.toLocaleString()} \n${window.location.href}`);
    // Get WhatsApp number from a data attribute or global variable
    let whatsappNumber = document.querySelector('meta[name="whatsapp-number"]')?.getAttribute('content') || '254721442248';
    
    // Clean the WhatsApp number (remove any non-numeric characters except +)
    let cleanNumber = whatsappNumber.replace(/[^\d+]/g, '');
    
    // Handle local format (07xxxxxxxx) - convert to international format
    if (cleanNumber.startsWith('07') && cleanNumber.length === 10) {
        cleanNumber = '254' + cleanNumber.substring(1);
    }
    
    // Ensure the number starts with the country code
    const finalNumber = cleanNumber.startsWith('+') ? cleanNumber.substring(1) : cleanNumber;
    
    window.open(`https://wa.me/${finalNumber}?text=${text}`, '_blank');
}

window.copyProductLink = async function() {
    try {
        await navigator.clipboard.writeText(window.location.href);
        showNotification('Product link copied to clipboard!', 'success');
        
        // Visual feedback
        const copyBtn = document.querySelector('.share-btn.copy');
        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            copyBtn.innerHTML = '<i class="fas fa-link"></i>';
        }, 2000);
    } catch (error) {
        showNotification('Failed to copy link', 'error');
    }
}

// Notification system
window.showNotification = function(message, type = 'success') {
    // Remove existing notifications
    document.querySelectorAll('.notification').forEach(notification => {
        notification.remove();
    });

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    notification.innerHTML = `
        <i class="${icon}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Hide notification
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

// Enhanced user interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.btn, .share-btn, .quantity-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('div');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255,255,255,0.5)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.left = (e.clientX - button.offsetLeft) + 'px';
            ripple.style.top = (e.clientY - button.offsetTop) + 'px';
            
            button.style.position = 'relative';
            button.style.overflow = 'hidden';
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Smooth scroll for internal links
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

    // Add loading states for images
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('load', function() {
            this.style.opacity = '1';
        });
        
        if (!img.complete) {
            img.style.opacity = '0.7';
        }
    });
});

// Performance optimization
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        }
    });
});

// Observe lazy-loaded images
document.querySelectorAll('img[data-src]').forEach(img => {
    observer.observe(img);
});

// Image Modal Functions
window.openImageModal = function() {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const mainImage = document.getElementById('mainProductImage');
    
    if (modal && modalImage && mainImage) {
        modalImage.src = mainImage.src;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Add fade-in animation
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
    }
}

window.closeImageModal = function() {
    const modal = document.getElementById('imageModal');
    
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeImageModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
});

// Wishlist functionality
window.toggleWishlist = function(productId) {
    const index = wishlistItems.findIndex(item => item.id === productId);
    const wishlistBtn = document.querySelector(`[onclick="toggleWishlist(${productId})"]`);
    
    if (index > -1) {
        // Remove from wishlist
        wishlistItems.splice(index, 1);
        localStorage.setItem('wishlist', JSON.stringify(wishlistItems));
        
        if (wishlistBtn) {
            wishlistBtn.innerHTML = '<i class="fas fa-heart"></i>';
            wishlistBtn.style.color = '#6b7280';
        }
        
        showNotification('Product removed from wishlist', 'success');
    } else {
        // Add to wishlist
        const productData = {
            id: productId,
            name: productName,
            price: productPrice,
            image: document.querySelector('#mainProductImage').src,
            addedAt: new Date().toISOString()
        };
        
        wishlistItems.push(productData);
        localStorage.setItem('wishlist', JSON.stringify(wishlistItems));
        
        if (wishlistBtn) {
            wishlistBtn.innerHTML = '<i class="fas fa-heart"></i>';
            wishlistBtn.style.color = '#ef4444';
        }
        
        showNotification('Product added to wishlist!', 'success');
    }
}

// Initialize wishlist button state
function initializeWishlistState() {
    const wishlistBtn = document.querySelector(`[onclick="toggleWishlist(${productId})"]`);
    if (wishlistBtn && wishlistItems.find(item => item.id === productId)) {
        wishlistBtn.innerHTML = '<i class="fas fa-heart"></i>';
        wishlistBtn.style.color = '#ef4444';
    }
}

// Quick view functionality (if needed)
function quickView(productId) {
    // Implementation for quick view modal
    console.log('Quick view for product:', productId);
}

// Carousel functionality for recommendations
window.scrollCarousel = function(direction) {
    const track = document.querySelector('.carousel-track');
    if (!track) return;
    
    const cardWidth = 280 + 24; // card width + gap
    const currentScroll = track.scrollLeft || 0;
    const maxScroll = track.scrollWidth - track.clientWidth;
    
    let newScroll;
    if (direction === 'left') {
        newScroll = Math.max(0, currentScroll - cardWidth * 2);
    } else {
        newScroll = Math.min(maxScroll, currentScroll + cardWidth * 2);
    }
    
    track.scrollTo({
        left: newScroll,
        behavior: 'smooth'
    });
};

// Initialize tooltips for better UX
function initializeTooltips() {
    const elementsWithTooltips = document.querySelectorAll('[title]');
    elementsWithTooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('title');
            tooltip.style.cssText = `
                position: absolute;
                background: var(--dark-gray);
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                white-space: nowrap;
                z-index: 1000;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.3s;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
            
            setTimeout(() => tooltip.style.opacity = '1', 100);
            
            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            }, { once: true });
        });
    });
}

// Initialize all enhancements
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
});
