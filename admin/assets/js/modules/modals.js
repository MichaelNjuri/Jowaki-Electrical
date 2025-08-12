import { showNotification } from './notifications.js';
import { fetchOrders } from './orders.js';
import { fetchProducts } from './products.js';
import { fetchCustomers } from './customers.js';
import { fetchCategories } from './categories.js';

export function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

export function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        
        // Reset form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

function initializeAddOrderForm(state) {
    const addOrderForm = document.getElementById('add-order-form');
    if (addOrderForm) {
        addOrderForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(addOrderForm);
            const orderData = Object.fromEntries(formData.entries());
            
            fetch('api/admin_orders.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Order added successfully!', 'success');
                    hideModal('add-order-modal');
                    fetchOrders(state);
                } else {
                    throw new Error(data.error || 'Failed to add order');
                }
            })
            .catch(error => {
                console.error('Add order error:', error);
                showNotification(`Error adding order: ${error.message}`, 'error');
            });
        });
    }
}

async function loadStoreCategoriesForProduct() {
    try {
        const response = await fetch('./API/get_store_categories_fixed.php', {
            credentials: 'include'
        });
        if (!response.ok) {
            console.error('Failed to load store categories for product form');
            return;
        }
        
        const data = await response.json();
        if (data.success && data.categories) {
            // Load categories for both add and edit product forms
            const categorySelects = [
                document.getElementById('product-category-select'),
                document.getElementById('edit-product-category')
            ];
            
            categorySelects.forEach(categorySelect => {
                if (categorySelect) {
                    // Clear existing options except the first one
                    categorySelect.innerHTML = '<option value="">Select a category...</option>';
                    
                    // Add store categories
                    data.categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.name; // Use the display name as the value
                        option.textContent = category.name;
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
                        categorySelect.appendChild(option);
                    });
                }
            });
        }
    } catch (error) {
        console.error('Error loading store categories for product form:', error);
    }
}

function initializeAddProductForm(state) {
    const addProductForm = document.getElementById('add-product-form');
    if (addProductForm) {
        // Load store categories when the form is initialized
        loadStoreCategoriesForProduct();
        
        addProductForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(addProductForm);
            
            // Send FormData directly for file uploads
            fetch('../includes/add_product.php', {
                method: 'POST',
                body: formData // Don't set Content-Type header, let browser set it for multipart/form-data
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Product added successfully!', 'success');
                    hideModal('add-product-modal');
                    fetchProducts(state);
                } else {
                    throw new Error(data.error || 'Failed to add product');
                }
            })
            .catch(error => {
                console.error('Add product error:', error);
                showNotification(`Error adding product: ${error.message}`, 'error');
            });
        });
    }
}

function initializeAddCustomerForm(state) {
    const addCustomerForm = document.getElementById('add-customer-form');
    if (addCustomerForm) {
        addCustomerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(addCustomerForm);
            const customerData = Object.fromEntries(formData.entries());
            
            fetch('../includes/admin_customers.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(customerData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Customer added successfully!', 'success');
                    hideModal('add-customer-modal');
                    fetchCustomers(state);
                } else {
                    throw new Error(data.error || 'Failed to add customer');
                }
            })
            .catch(error => {
                console.error('Add customer error:', error);
                showNotification(`Error adding customer: ${error.message}`, 'error');
            });
        });
    }
}

function initializeAddCategoryForm(state) {
    const addCategoryForm = document.getElementById('add-category-form');
    if (addCategoryForm) {
        addCategoryForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(addCategoryForm);
            const categoryData = Object.fromEntries(formData.entries());
            
            fetch('../includes/admin_categories.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(categoryData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Category added successfully!', 'success');
                    hideModal('add-category-modal');
                    fetchCategories(state);
                } else {
                    throw new Error(data.error || 'Failed to add category');
                }
            })
            .catch(error => {
                console.error('Add category error:', error);
                showNotification(`Error adding category: ${error.message}`, 'error');
            });
        });
    }
}

export function initializeModals(state) {
    // Close modals when clicking outside
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    
    // Initialize all form handlers
    initializeAddOrderForm(state);
    initializeAddProductForm(state);
    initializeAddCustomerForm(state);
    initializeAddCategoryForm(state);
}
