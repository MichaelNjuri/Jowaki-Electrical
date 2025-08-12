// Store UI Module
// Handles all UI-related functionality

class UIModule {
    constructor() {
        this.initialized = false;
    }

    // Initialize UI components
    initializeUI() {
        if (this.initialized) return;
        
        // Set up category scroll functionality
        this.setupCategoryScroll();
        
        // Set up header scroll effect
        this.setupHeaderScroll();
        
        // Set up notification system
        this.setupNotifications();
        
        this.initialized = true;
        console.log('UI Module initialized');
    }

    // Category scroll functionality
    scrollCategories(direction) {
        const scrollContainer = document.getElementById('categoryScroll');
        if (!scrollContainer) return;
        
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

    // Header scroll effect
    setupHeaderScroll() {
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            if (!header) return;
            
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Setup category scroll
    setupCategoryScroll() {
        // Make scrollCategories available globally for inline event handlers
        window.scrollCategories = (direction) => this.scrollCategories(direction);
    }

    // Setup notifications
    setupNotifications() {
        // Make showNotification available globally
        window.showNotification = (message, type = 'success') => this.showNotification(message, type);
    }

    // Show notification
    showNotification(message, type = 'success') {
        let notification = document.getElementById('notification');
        
        // Create notification element if it doesn't exist
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'notification';
            notification.className = 'notification';
            document.body.appendChild(notification);
        }
        
        notification.textContent = message;
        notification.className = `notification ${type}`;
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }

    // Show loading spinner
    showLoading(containerId = 'product-list') {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Loading products...</p>
                </div>
            `;
        }
    }

    // Hide loading spinner
    hideLoading(containerId = 'product-list') {
        const container = document.getElementById(containerId);
        if (container) {
            const spinner = container.querySelector('.loading-spinner');
            if (spinner) {
                spinner.remove();
            }
        }
    }

    // Show error message
    showError(message, containerId = 'product-list') {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `
                <div class="error-message">
                    <p>${message}</p>
                    <button onclick="location.reload()">Try Again</button>
                </div>
            `;
        }
    }
}

// Export the module
export default new UIModule(); 