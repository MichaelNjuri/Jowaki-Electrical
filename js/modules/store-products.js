// Products Module - Handles all product-related functionality

// Global products state
let storeProducts = [];
let currentFilter = 'all';

/**
 * Checks if a product is in stock.
 * @param {number} stock - The stock quantity of the product.
 * @returns {boolean} - True if the product is in stock, false otherwise.
 */
export function isInStock(stock) {
    return stock > 0;
}

/**
 * Loads products into the products grid.
 * @param {Array} filteredProducts - The array of products to load.
 */
export function loadProducts(filteredProducts = storeProducts) {
    const grid = document.getElementById('productsGrid');
    const loadingState = document.getElementById('loadingState');
    
    if (!grid) {
        console.warn('Products grid element not found');
        return;
    }

    // Hide loading state
    if (loadingState) {
        loadingState.style.display = 'none';
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
            <div class="product-image" onclick="window.location.href='product-detail.php?id=${product.id}'" style="cursor: pointer;">
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
                    <button class="action-btn quick-view" onclick="event.stopPropagation(); window.location.href='product-detail.php?id=${product.id}'" title="Quick View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn share" onclick="event.stopPropagation(); shareProduct(${product.id})" title="Share">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
            </div>
            <div class="product-content">
                <div class="product-category">${product.category || 'General'}</div>
                <h3 class="product-title" onclick="window.location.href='product-detail.php?id=${product.id}'" style="cursor: pointer;">${product.name}</h3>
                <p class="product-description">${product.description}</p>

                <div class="product-meta">
                    ${priceHtml}
                </div>
                <div class="product-actions">
                    <button class="btn-primary" onclick="event.stopPropagation(); addToCart(${product.id})" ${!isInStock(product.stock) ? 'disabled' : ''}>
                        ${!isInStock(product.stock) ? 'Out of Stock' : 'Add to Cart'}
                    </button>
                    <button class="btn-secondary" onclick="event.stopPropagation(); window.location.href='product-detail.php?id=${product.id}'" title="View Details">
                        <i class="fas fa-eye"></i>
                        View Details
                    </button>
                    <button class="btn-secondary" onclick="event.stopPropagation(); orderByWhatsApp(${product.id})" title="Order via WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                        Order via WhatsApp
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
export function filterProducts(category) {
    currentFilter = category;
    
    // Update active state for category cards
    document.querySelectorAll('.category-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Find and activate the clicked category card
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        const cardLabel = card.querySelector('.category-card-label');
        if (cardLabel) {
            if (category === 'all' && cardLabel.textContent === 'All Products') {
                card.classList.add('active');
            } else if (cardLabel.textContent.toLowerCase().includes(category.toLowerCase())) {
                card.classList.add('active');
            }
        }
    });

    let filteredProducts;
    if (category === 'all') {
        filteredProducts = storeProducts;
    } else {
        // Filter by exact category match first, then by partial matches
        filteredProducts = storeProducts.filter(p => {
            const productCategory = p.category ? p.category.toUpperCase() : '';
            const categoryUpper = category.toUpperCase();
            
            // First try exact match
            if (productCategory === categoryUpper) {
                return true;
            }
            
            // Then try partial matches in category, name, and description
            const productName = p.name ? p.name.toUpperCase() : '';
            const productDescription = p.description ? p.description.toUpperCase() : '';
            
            return productCategory.includes(categoryUpper) ||
                   productName.includes(categoryUpper) ||
                   productDescription.includes(categoryUpper);
        });
    }
    
    // Update results count
    const resultsInfo = document.getElementById('productsCount');
    if (resultsInfo) {
        resultsInfo.textContent = filteredProducts.length;
    }
    
    loadProducts(filteredProducts);
    
    // Show notification for filtered results
    if (category !== 'all') {
        showNotification(`Showing ${filteredProducts.length} products in ${category}`, 'info');
    }
}

/**
 * Searches for products based on the search term.
 */
export function searchProducts() {
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
export function sortProducts() {
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
export function setView(viewMode) {
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
export function viewProduct(productId) {
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

/**
 * Loads categories dynamically from the API
 */
export async function loadCategories() {
    try {
        const response = await fetch('/jowaki_electrical_srvs/API/get_store_categories.php');
        if (!response.ok) {
            console.error('Failed to load categories');
            return;
        }
        
        const data = await response.json();
        if (data.success && data.categories) {
            const categoryScroll = document.getElementById('categoryScroll');
            if (categoryScroll) {
                // Clear existing category cards
                categoryScroll.innerHTML = '';
                
                // Add "All Products" card first
                const allProductsCard = document.createElement('div');
                allProductsCard.className = 'category-card active';
                allProductsCard.onclick = () => filterProducts('all');
                allProductsCard.innerHTML = `
                    <div class="category-card-image">
                        <i class="fas fa-th-large category-icon"></i>
                    </div>
                    <div class="category-card-label">All Products</div>
                `;
                categoryScroll.appendChild(allProductsCard);
                
                // Add category cards
                data.categories.forEach(category => {
                    const categoryCard = document.createElement('div');
                    categoryCard.className = 'category-card';
                    categoryCard.onclick = () => filterProducts(category.filter_value || category.name);
                    
                    // Determine image or icon
                    let imageContent = '';
                    if (category.image_url) {
                        imageContent = `<img src="${category.image_url}" alt="${category.display_name}" loading="lazy">`;
                    } else {
                        imageContent = `<i class="${category.icon_class || 'fas fa-box'} category-icon"></i>`;
                    }
                    
                    categoryCard.innerHTML = `
                        <div class="category-card-image">
                            ${imageContent}
                        </div>
                        <div class="category-card-label">${category.display_name || category.name}</div>
                    `;
                    categoryScroll.appendChild(categoryCard);
                });
            }
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

/**
 * Loads products from the API
 */
export async function loadProductsFromAPI() {
    try {
        console.log('Starting product fetch at', new Date().toISOString());
        
        // Show loading state
        const loadingState = document.getElementById('loadingState');
        if (loadingState) {
            loadingState.style.display = 'flex';
        }
        
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
        
        return storeProducts;
    } catch (error) {
        console.error('Error loading products:', error);
        storeProducts = [];
        loadProducts();
        showNotification(`Failed to load products: ${error.message}`, 'error');
        
        // Hide loading state on error
        const loadingState = document.getElementById('loadingState');
        if (loadingState) {
            loadingState.style.display = 'none';
        }
        
        return [];
    }
}

// Product Detail Functions
export function showProductDetail(productId) {
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
            <!-- Product Image Section -->
            <div class="product-detail-image-section">
                <div class="product-detail-image">
                    <img src="${imageSrc}" alt="${product.name}">
                    <div class="stock-badge ${stockStatus}">${stockText}</div>
                </div>
            </div>
            
            <!-- Product Information Section -->
            <div class="product-detail-info-section">
                <!-- Product Header -->
                <div class="product-detail-header">
                    <div class="product-category-badge">${product.category || 'General'}</div>
                    <h3 class="product-detail-title">${product.name}</h3>
                    <div class="product-detail-price">
                        ${actualPrice < originalPrice ? `<span class="original-price">KSh ${originalPrice.toLocaleString()}</span>` : ''}
                        <span class="current-price">KSh ${actualPrice.toLocaleString()}</span>
                    </div>
                </div>
                
                <!-- Product Description -->
                <div class="product-detail-description">
                    <h4>Description</h4>
                    <p>${product.description || 'No description available.'}</p>
                </div>
                
                <!-- Product Specifications -->
                <div class="product-detail-specs">
                    <h4>Specifications</h4>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Category</span>
                            <span class="spec-value">${product.category || 'N/A'}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Brand</span>
                            <span class="spec-value">${product.brand || 'N/A'}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Stock</span>
                            <span class="spec-value">${product.stock} units</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Warranty</span>
                            <span class="spec-value">${product.warranty_months || 0} months</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Weight</span>
                            <span class="spec-value">${product.weight_kg || 0} kg</span>
                        </div>
                    </div>
                </div>
                
                <!-- Product Actions -->
                <div class="product-detail-actions">
                    <button class="btn-primary" onclick="addToCart(${product.id}); hideProductDetail();" ${product.stock <= 0 ? 'disabled' : ''}>
                        <i class="fas fa-shopping-cart"></i>
                        ${product.stock <= 0 ? 'Out of Stock' : 'Add to Cart'}
                    </button>
                    <button class="btn-secondary" onclick="orderByWhatsApp(${product.id})">
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

export function hideProductDetail() {
    hideModal('productDetailModal');
}

// Export products state for other modules
export function getStoreProducts() {
    return storeProducts;
}

export function setStoreProducts(products) {
    storeProducts = products;
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

// Import modal function
function showModal(title, content, modalId) {
    if (window.showModal) {
        window.showModal(title, content, modalId);
    } else {
        console.log(`Modal: ${title} - ${content}`);
    }
}

function hideModal(modalId) {
    if (window.hideModal) {
        window.hideModal(modalId);
    }
} 