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

function initializeAddProductForm(state) {
    const addProductForm = document.getElementById('add-product-form');
    if (addProductForm) {
        addProductForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(addProductForm);
            
            // Send FormData directly for file uploads
            fetch('api/add_product.php', {
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
            
            fetch('api/admin_customers.php', {
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
            
            fetch('api/admin_categories.php', {
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
