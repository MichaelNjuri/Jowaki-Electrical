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

export function updateProductCategoryOptions(state) {
    const categoryFilter = document.getElementById('product-category-filter');
    const categorySelect = document.getElementById('product-category-select');
    
    if (categoryFilter) {
        categoryFilter.innerHTML = '<option value="">All Categories</option>';
        state.categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categoryFilter.appendChild(option);
        });
    }
    
    if (categorySelect) {
        categorySelect.innerHTML = '<option value="">Select Category</option>';
        state.categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
    }
}

export function fetchProducts(state) {
    return fetch('api/get_products_admin.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success !== false && Array.isArray(data)) {
                state.products = data.map(product => processProductData(product));
                renderProducts(state.products, state);
                updateProductCategoryOptions(state);
            } else {
                throw new Error(data.error || 'Failed to fetch products');
            }
        })
        .catch(error => {
            console.error('Products fetch error:', error);
            showNotification(`Error fetching products: ${error.message}`, 'error');
            renderProducts([], state);
        });
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
    
    fetch('api/update_stock.php', {
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

export function editProduct(productId, state) {
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

    // Populate category dropdown
    const categorySelect = document.getElementById('edit-product-category');
    categorySelect.innerHTML = '<option value="">Select Category</option>';
    state.categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.name;
        option.textContent = category.name;
        if (category.name === product.category) {
            option.selected = true;
        }
        categorySelect.appendChild(option);
    });

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

async function updateProduct(productId, state) {
    const form = document.getElementById('edit-product-form');
    if (!form) return;

    const formData = new FormData(form);
    const productData = {
        id: productId,
        name: formData.get('name'),
        category: formData.get('category'),
        price: parseFloat(formData.get('price')),
        stock: parseInt(formData.get('stock')),
        description: formData.get('description'),
        status: formData.get('status')
    };

    try {
        const response = await fetch('api/update_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(productData)
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Product updated successfully', 'success');
            
            // Update the product in the state
            const productIndex = state.products.findIndex(p => p.id === productId);
            if (productIndex !== -1) {
                state.products[productIndex] = { ...state.products[productIndex], ...productData };
            }
            
            // Refresh products list
            renderProducts(state.products, state);
            
            // Hide modal
            const modal = document.getElementById('edit-product-modal');
            if (modal) {
                modal.classList.remove('show');
            }
            
            // Reset form
            if (form) {
                form.reset();
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

export function initializeProducts(state) {
    console.log('Initializing products module...');
    
    // Load initial products
    fetchProducts(state);
    
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
