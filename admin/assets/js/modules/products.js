import { sanitizeHTML } from './utils.js';
import { showNotification } from './notifications.js';

function processProductData(product) {
    return {
        id: parseInt(product.id) || 0,
        name: sanitizeHTML(product.name || ''),
        category: sanitizeHTML(product.category || ''),
        category_id: parseInt(product.category_id) || 0,
        price: parseFloat(product.price) || 0,
        stock: parseInt(product.stock) || 0,
        description: sanitizeHTML(product.description || ''),
        status: product.status || 'active'
    };
}

export function renderProducts(productsToRender, state) {
    const tbody = document.getElementById('products-tbody');
    if (!tbody) {
        console.error('products-tbody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(productsToRender) || productsToRender.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 20px; color: #666;">No products found</td></tr>`;
        return;
    }
    
    productsToRender.forEach(product => {
        const tr = document.createElement('tr');
        const statusClass = product.status === 'active' ? 'status-delivered' : 'status-cancelled';
        const statusText = product.status === 'active' ? 'Active' : 'Inactive';
        
        // Stock status indicators
        let stockClass = '';
        let stockText = product.stock.toString();
        if (product.stock <= 0) {
            stockClass = 'status-cancelled';
            stockText = 'Out of Stock';
        } else if (product.stock <= 10) {
            stockClass = 'status-pending';
            stockText = `${product.stock} (Low)`;
        }
        
        tr.innerHTML = `
            <td>${product.id}</td>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>KSh ${product.price.toLocaleString()}</td>
            <td><span class="${stockClass}">${stockText}</span></td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>
                <button class="btn btn-secondary btn-sm" onclick="window.adminModules.viewProduct(${product.id})" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-primary btn-sm" onclick="window.adminModules.editProduct(${product.id})" title="Edit Product">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-warning btn-sm" onclick="window.adminModules.manageStock(${product.id})" title="Manage Stock">
                    <i class="fas fa-boxes"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="window.adminModules.deleteProduct(${product.id})" title="Delete Product">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

export async function updateProductCategoryOptions(state) {
    const categoryFilter = document.getElementById('product-category-filter');
    const categorySelect = document.getElementById('product-category-select');
    
    try {
        // Fetch store categories
        const response = await fetch('../includes/store_categories_admin.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        if (data.success && Array.isArray(data.data)) {
            const storeCategories = data.data;
            
            if (categoryFilter) {
                categoryFilter.innerHTML = '<option value="">All Categories</option>';
                storeCategories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.filter_value || category.name;
                    option.textContent = category.display_name || category.name;
                    categoryFilter.appendChild(option);
                });
            }
            
            if (categorySelect) {
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                storeCategories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.filter_value || category.name;
                    option.textContent = category.display_name || category.name;
                    categorySelect.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error loading store categories for product options:', error);
        // Fallback to empty options
        if (categorySelect) {
            categorySelect.innerHTML = '<option value="">Select Category</option>';
        }
        if (categoryFilter) {
            categoryFilter.innerHTML = '<option value="">All Categories</option>';
        }
    }
}

export async function fetchProducts(state) {
    try {
        const response = await fetch('../includes/get_products_admin.php');
        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        
        const data = await response.json();
        if (data.success !== false && Array.isArray(data)) {
            state.products = data.map(product => processProductData(product));
            renderProducts(state.products, state);
            await updateProductCategoryOptions(state);
        } else {
            throw new Error(data.error || 'Failed to fetch products');
        }
    } catch (error) {
        console.error('Products fetch error:', error);
        showNotification(`Error fetching products: ${error.message}`, 'error');
        renderProducts([], state);
    }
}

// Enhanced CRUD operations
export function viewProduct(productId, state) {
    const product = state.products.find(p => p.id === productId);
    if (product) {
        alert(`Product Details:\nID: ${product.id}\nName: ${product.name}\nSKU: ${product.sku}\nCategory: ${product.category}\nPrice: KSh ${product.price.toLocaleString()}\nStock: ${product.stock}\nStatus: ${product.status}`);
    }
}

export function manageStock(productId, state) {
    const product = state.products.find(p => p.id === productId);
    if (!product) {
        showNotification('Product not found', 'error');
        return;
    }
    
    // Populate stock management modal
    const modal = document.getElementById('stock-management-modal');
    const productNameInput = document.getElementById('stock-product-name');
    const productIdInput = document.getElementById('stock-product-id');
    const currentStockInput = document.getElementById('current-stock');
    
    if (modal && productNameInput && productIdInput && currentStockInput) {
        productNameInput.value = product.name;
        productIdInput.value = product.id;
        currentStockInput.value = product.stock;
        
        // Show modal
        modal.classList.add('show');
        
        // Set up form submission
        const form = document.getElementById('stock-management-form');
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                updateStock(productId, state);
            };
        }
    }
}

function updateStock(productId, state) {
    const form = document.getElementById('stock-management-form');
    const operation = document.getElementById('stock-operation').value;
    const quantity = parseInt(document.getElementById('stock-quantity').value);
    const reason = document.getElementById('stock-reason').value;
    
    if (!quantity || quantity <= 0) {
        showNotification('Please enter a valid quantity', 'error');
        return;
    }
    
    fetch('../includes/update_stock.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity,
            operation: operation,
            reason: reason,
            updated_by: 'admin'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Stock updated successfully. New stock: ${data.data.new_stock}`, 'success');
            
            // Update local product data
            const product = state.products.find(p => p.id === productId);
            if (product) {
                product.stock = data.data.new_stock;
            }
            
            // Refresh products list
            renderProducts(state.products, state);
            
            // Hide modal
            const modal = document.getElementById('stock-management-modal');
            if (modal) {
                modal.classList.remove('show');
            }
            
            // Reset form
            if (form) {
                form.reset();
            }
        } else {
            throw new Error(data.error || 'Failed to update stock');
        }
    })
    .catch(error => {
        console.error('Error updating stock:', error);
        showNotification(`Error updating stock: ${error.message}`, 'error');
    });
}

async function loadStoreCategoriesForEditProduct(currentCategory) {
    try {
        const response = await fetch('../includes/get_store_categories_fixed.php');
        if (!response.ok) {
            console.error('Failed to load store categories for edit product form');
            return;
        }
        
        const data = await response.json();
        if (data.success && data.categories) {
            const categorySelect = document.getElementById('edit-product-category');
            if (categorySelect) {
                // Clear existing options
                categorySelect.innerHTML = '<option value="">Select a category...</option>';
                
                // Add store categories
                data.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.name;
                    option.textContent = category.name;
                    if (category.name === currentCategory) {
                        option.selected = true;
                    }
                    categorySelect.appendChild(option);
                });
                
                // Add a separator option
                const separatorOption = document.createElement('option');
                separatorOption.value = '';
                separatorOption.textContent = '─────────── Custom Categories ───────────';
                separatorOption.disabled = true;
                categorySelect.appendChild(separatorOption);
                
                // Add common custom categories
                const customCategories = [
                    'Electrical Components',
                    'Security Equipment',
                    'CCTV Systems',
                    'Access Control',
                    'Fire Safety',
                    'Lighting',
                    'Wiring',
                    'Tools',
                    'Spare Parts',
                    'Installation Services'
                ];
                
                customCategories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    if (category === currentCategory) {
                        option.selected = true;
                    }
                    categorySelect.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error loading store categories for edit product form:', error);
    }
}

export async function editProduct(productId, state) {
    // Find the product in the state
    const product = state.products.find(p => p.id === productId);
    if (!product) {
        showNotification('Product not found', 'error');
        return;
    }

    // Populate the edit modal with product data
    document.getElementById('edit-product-id').value = product.id;
    document.getElementById('edit-product-name').value = product.name;
    document.getElementById('edit-product-price').value = product.price;
    document.getElementById('edit-product-stock').value = product.stock;
    document.getElementById('edit-product-description').value = product.description || '';
    document.getElementById('edit-product-status').value = product.status;
    document.getElementById('edit-product-sku').value = product.sku || '';

    // Set up current image display
    const currentImageElement = document.getElementById('edit-product-current-image');
    if (currentImageElement) {
        const imageSrc = product.image_paths ? 
            (Array.isArray(JSON.parse(product.image_paths)) ? JSON.parse(product.image_paths)[0] : product.image_paths) 
            : 'placeholder.jpg';
        currentImageElement.src = imageSrc;
        currentImageElement.style.display = imageSrc !== 'placeholder.jpg' ? 'block' : 'none';
    }

    // Load store categories for edit product form
    await loadStoreCategoriesForEditProduct(product.category);

    // Set up image upload functionality
    setupImageUpload();

    // Show the modal
    const modal = document.getElementById('edit-product-modal');
    if (modal) {
        modal.classList.add('show');
    }

    // Set up form submission
    const form = document.getElementById('edit-product-form');
    if (form) {
        form.onsubmit = (e) => {
            e.preventDefault();
            updateProduct(productId, state);
        };
    }
}

function setupImageUpload() {
    const fileInput = document.getElementById('edit-product-image-upload');
    const imagePreview = document.getElementById('edit-product-new-image-preview');
    const previewWrapper = document.querySelector('.image-preview-wrapper');
    const removeButton = document.getElementById('edit-product-remove-image');

    if (fileInput) {
        fileInput.onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    showNotification('Please select a valid image file', 'error');
                    return;
                }

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showNotification('Image file size must be less than 5MB', 'error');
                    return;
                }

                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewWrapper.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        };
    }

    if (removeButton) {
        removeButton.onclick = function() {
            fileInput.value = '';
            imagePreview.src = '';
            previewWrapper.style.display = 'none';
        };
    }
}

async function updateProduct(productId, state) {
    const form = document.getElementById('edit-product-form');
    if (!form) return;

    const formData = new FormData(form);
    const fileInput = document.getElementById('edit-product-image-upload');
    
    // Check if a new image was selected
    const hasNewImage = fileInput && fileInput.files.length > 0;
    
    const productData = {
        id: productId,
        name: formData.get('name'),
        category: formData.get('category'),
        price: parseFloat(formData.get('price')),
        stock: parseInt(formData.get('stock')),
        description: formData.get('description'),
        status: formData.get('status')
    };

    // If there's a new image, append it to formData
    if (hasNewImage) {
        formData.append('image', fileInput.files[0]);
    }

    try {
        const response = await fetch('../includes/update_product.php', {
            method: 'POST',
            body: hasNewImage ? formData : JSON.stringify(productData),
            headers: hasNewImage ? {} : {
                'Content-Type': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Product updated successfully', 'success');
            
            // Update the product in the state
            const productIndex = state.products.findIndex(p => p.id === productId);
            if (productIndex !== -1) {
                state.products[productIndex] = { ...state.products[productIndex], ...productData };
                // Update image path if new image was uploaded
                if (data.image_path) {
                    state.products[productIndex].image_paths = data.image_path;
                }
            }
            
            // Refresh products list
            renderProducts(state.products, state);
            
            // Hide modal
            const modal = document.getElementById('edit-product-modal');
            if (modal) {
                modal.classList.remove('show');
            }
            
            // Reset form and image preview
            if (form) {
                form.reset();
                const previewWrapper = document.querySelector('.image-preview-wrapper');
                if (previewWrapper) {
                    previewWrapper.style.display = 'none';
                }
            }
        } else {
            throw new Error(data.error || 'Failed to update product');
        }
    } catch (error) {
        console.error('Error updating product:', error);
        showNotification(`Error updating product: ${error.message}`, 'error');
    }
}

export function deleteProduct(productId, state) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        // Implement delete functionality
        showNotification('Product deletion not implemented yet', 'warning');
    }
}

export async function initializeProducts(state) {
    console.log('Initializing products module...');
    
    // Load initial products
    await fetchProducts(state);
    
    // Set up search and filter functionality
    const productSearch = document.getElementById('product-search');
    const productCategoryFilter = document.getElementById('product-category-filter');
    const productStockFilter = document.getElementById('product-stock-filter');
    
    if (productSearch) {
        productSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const filteredProducts = state.products.filter(product => 
                product.name.toLowerCase().includes(searchTerm) ||
                product.category.toLowerCase().includes(searchTerm)
            );
            renderProducts(filteredProducts, state);
        });
    }
    
    if (productCategoryFilter) {
        productCategoryFilter.addEventListener('change', function() {
            const categoryFilter = this.value;
            const filteredProducts = categoryFilter ? 
                state.products.filter(product => product.category_id.toString() === categoryFilter) : 
                state.products;
            renderProducts(filteredProducts, state);
        });
    }
    
    if (productStockFilter) {
        productStockFilter.addEventListener('change', function() {
            const stockFilter = this.value;
            let filteredProducts = state.products;
            
            switch(stockFilter) {
                case 'in-stock':
                    filteredProducts = state.products.filter(product => product.stock > 10);
                    break;
                case 'low-stock':
                    filteredProducts = state.products.filter(product => product.stock > 0 && product.stock <= 10);
                    break;
                case 'out-of-stock':
                    filteredProducts = state.products.filter(product => product.stock <= 0);
                    break;
            }
            
            renderProducts(filteredProducts, state);
        });
    }
    
    console.log('Products module initialized');
}

// Make manageStock available globally
window.adminModules = window.adminModules || {};
window.adminModules.manageStock = manageStock;
window.adminModules.editProduct = editProduct;
