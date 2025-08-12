// Store Products Module
// Handles all product-related functionality

class ProductsModule {
    constructor() {
        this.products = [];
        this.categories = [];
        this.currentCategory = 'all';
        this.currentView = 'grid';
        this.searchTerm = '';
    }

    // Load categories from API
    async loadCategories() {
        try {
            const response = await fetch('api/get_categories.php');
            const data = await response.json();
            
            if (data.success) {
                this.categories = data.categories;
                this.renderCategories();
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    // Load products from API
    async loadProductsFromAPI() {
        try {
            const response = await fetch('api/get_products.php');
            const data = await response.json();
            
            if (data.success) {
                this.products = data.products;
                this.renderProducts();
                return this.products;
            } else {
                console.error('Failed to load products:', data.message);
                return [];
            }
        } catch (error) {
            console.error('Error loading products:', error);
            return [];
        }
    }

    // Render categories
    renderCategories() {
        const categoryContainer = document.getElementById('categoryScroll');
        if (!categoryContainer) return;

        let categoryHTML = `
            <div class="category-card active" data-category="all">
                <div class="category-card-image">
                    <i class="fas fa-th-large category-icon"></i>
                </div>
                <div class="category-card-label">All Products</div>
            </div>
        `;

        this.categories.forEach(category => {
            categoryHTML += `
                <div class="category-card" data-category="${category.name}">
                    <div class="category-card-image">
                        <i class="fas fa-tag category-icon"></i>
                    </div>
                    <div class="category-card-label">${category.name}</div>
                </div>
            `;
        });

        categoryContainer.innerHTML = categoryHTML;

        // Add click event listeners
        const categoryItems = categoryContainer.querySelectorAll('.category-card');
        categoryItems.forEach(item => {
            item.addEventListener('click', () => {
                const category = item.dataset.category;
                this.filterProducts(category);
                
                // Update active state
                categoryItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
            });
        });
    }

    // Render products
    renderProducts() {
        const productsContainer = document.getElementById('productsGrid');
        if (!productsContainer) return;

        const filteredProducts = this.getFilteredProducts();
        
        // Update product count
        const productsCountElement = document.getElementById('productsCount');
        if (productsCountElement) {
            productsCountElement.textContent = filteredProducts.length;
        }
        
        if (filteredProducts.length === 0) {
            productsContainer.innerHTML = '<p class="no-products">No products found</p>';
            return;
        }

        let productsHTML = '';

        filteredProducts.forEach(product => {
            // Get the first image from the images array or use existing image as placeholder
            const imageUrl = product.images && product.images.length > 0 ? product.images[0] : 'assets/images/IMG_1.jpg';
            
            productsHTML += `
                <div class="product-card">
                    <div class="product-image">
                        <img src="${imageUrl}" alt="${product.name}" onerror="this.src='assets/images/IMG_1.jpg'">
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">${product.name}</h3>
                        <p class="product-description">${product.description}</p>
                        <div class="product-price">
                            <span class="current-price">KES ${product.price.toLocaleString()}</span>
                        </div>
                        <a href="product-detail.php?id=${product.id}" class="btn-primary">View Details</a>
                    </div>
                </div>
            `;
        });

        productsContainer.innerHTML = productsHTML;
    }

    // Filter products by category
    filterProducts(category) {
        this.currentCategory = category;
        this.renderProducts();
    }

    // Search products
    searchProducts() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            this.searchTerm = searchInput.value.toLowerCase();
            this.renderProducts();
        }
    }

    // Sort products
    sortProducts() {
        const sortSelect = document.getElementById('sortSelect');
        if (!sortSelect) return;

        const sortBy = sortSelect.value;
        
        switch (sortBy) {
            case 'price-low':
                this.products.sort((a, b) => a.price - b.price);
                break;
            case 'price-high':
                this.products.sort((a, b) => b.price - a.price);
                break;
            case 'name':
                this.products.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'newest':
                this.products.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                break;
            default:
                // Default sorting (by ID)
                this.products.sort((a, b) => a.id - b.id);
        }

        this.renderProducts();
    }

    // Set view mode (grid/list)
    setView(viewMode) {
        this.currentView = viewMode;
        this.renderProducts();
        
        // Update view buttons
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.onclick.toString().includes(viewMode)) {
                btn.classList.add('active');
            }
        });
    }

    // Show product detail
    showProductDetail(productId) {
        const product = this.products.find(p => p.id == productId);
        if (!product) return;

        const detailModal = document.getElementById('productDetailModal');
        if (!detailModal) return;

        const imageUrl = product.images && product.images.length > 0 ? product.images[0] : 'assets/images/IMG_1.jpg';
        
        const modalContent = detailModal.querySelector('#productDetailContent');
        modalContent.innerHTML = `
            <div class="product-detail-content">
                <div class="product-detail-image-section">
                    <div class="product-detail-image">
                                                 <img src="${imageUrl}" alt="${product.name}" onerror="this.src='assets/images/IMG_1.jpg'">
                        <div class="stock-badge ${product.stock > 0 ? 'in-stock' : 'out-of-stock'}">
                            ${product.stock > 0 ? 'In Stock' : 'Out of Stock'}
                        </div>
                    </div>
                </div>
                <div class="product-detail-info-section">
                    <div class="product-meta">
                        <div class="category-tag">
                            <i class="fas fa-tag"></i>
                            ${product.category}
                        </div>
                        ${product.brand ? `<div class="brand-tag">${product.brand}</div>` : ''}
                    </div>
                    <h2 id="productDetailTitle">${product.name}</h2>
                    <p class="product-description">${product.description}</p>
                    <div class="price-section">
                        <span class="current-price">KSh ${product.price.toLocaleString()}</span>
                        ${product.discount_price ? `<span class="original-price">KSh ${product.discount_price.toLocaleString()}</span>` : ''}
                    </div>
                    <div class="product-actions">
                        <button class="btn-primary" onclick="addToCart(${product.id})" ${product.stock <= 0 ? 'disabled' : ''}>
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                        <button class="btn-secondary" onclick="hideProductDetail()">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;

        detailModal.classList.remove('hidden');
    }

    // Hide product detail
    hideProductDetail() {
        const detailModal = document.getElementById('productDetailModal');
        if (detailModal) {
            detailModal.classList.add('hidden');
        }
    }

    // Get filtered products
    getFilteredProducts() {
        let filtered = this.products;

        // Filter by category
        if (this.currentCategory !== 'all') {
            filtered = filtered.filter(product => product.category === this.currentCategory);
        }

        // Filter by search term
        if (this.searchTerm) {
            filtered = filtered.filter(product => 
                product.name.toLowerCase().includes(this.searchTerm) ||
                product.description.toLowerCase().includes(this.searchTerm)
            );
        }

        return filtered;
    }

    // Initialize search functionality
    initializeSearch() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                this.searchProducts();
            });
        }

        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                this.sortProducts();
            });
        }
    }

    // Category scroll functions
    scrollCategories(direction) {
        const container = document.getElementById('categoryScroll');
        if (!container) return;

        const scrollAmount = 200;
        if (direction === 'left') {
            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    }
}

// Export the module
export default new ProductsModule();

