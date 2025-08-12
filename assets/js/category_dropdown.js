document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNav = document.querySelector('.main-nav');

    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', function() {
            mainNav.classList.toggle('active');
        });
    }

    // Category dropdown functionality
    const categoryDropdown = document.querySelector('.category-dropdown');
    const categoryToggle = document.querySelector('.category-toggle');

    if (categoryDropdown && categoryToggle) {
        // Toggle dropdown on click
        categoryToggle.addEventListener('click', function(e) {
            e.preventDefault();
            categoryDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!categoryDropdown.contains(e.target)) {
                categoryDropdown.classList.remove('active');
            }
        });

        // Load categories dynamically (example using fetch)
        fetchCategories();
    }

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');

    if (searchInput && searchBtn) {
        // Search on button click
        searchBtn.addEventListener('click', function() {
            performSearch();
        });

        // Search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Clear search when input is empty
        searchInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                if (typeof filterProducts === 'function') {
                    filterProducts('all');
                }
            }
        });
    }

    // Close mobile menu when clicking nav links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (mainNav) {
                mainNav.classList.remove('active');
            }
        });
    });
});

// Function to fetch categories dynamically
function fetchCategories() {
            fetch('/api/get_categories.php') // Fixed API endpoint
        .then(response => response.json())
        .then(data => {
            const categoryMenu = document.querySelector('.category-menu');
            if (categoryMenu) {
                categoryMenu.innerHTML = data.map(category => `
                    <div class="category-item">
                        <span>${category.name}</span>
                        <div class="subcategory-menu">
                            ${category.subcategories.map(subcategory => `
                                <a href="Store.php?category=${category.id}&subcategory=${subcategory.id}">${subcategory.name}</a>
                            `).join('')}
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => console.error('Error fetching categories:', error));
}

// Search function
function performSearch() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    const searchTerm = searchInput.value.trim();
    if (searchTerm === '') {
        showNotification('Please enter a search term', 'error');
        return;
    }

    if (typeof searchProducts === 'function') {
        searchProducts();
    }
}

// Utility function to show notifications
function showNotification(message, type = 'success') {
    console.log(`Notification [${type}]: ${message}`);

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="margin-left: 1rem; background: none; border: none; cursor: pointer;">✕</button>
    `;

    // Position notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 2rem;
        border-radius: 8px;
        z-index: 10001;
        animation: slideInRight 0.3s ease-out;
        display: flex;
        align-items: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Add animation styles if not present
if (!document.getElementById('dropdown-animations')) {
    const styles = document.createElement('style');
    styles.id = 'dropdown-animations';
    styles.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        /* Smooth transitions for dropdown items */
        .category-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .subcategory-menu a {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
    `;
    document.head.appendChild(styles);
}
