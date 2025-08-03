import { showSection } from './modules/utils.js';
import { initializeDashboard } from './modules/dashboard.js';
import { initializeOrders, viewOrder, editOrder, deleteOrder } from './modules/orders.js';
import { initializeProducts, viewProduct, editProduct, deleteProduct } from './modules/products.js';
import { initializeCustomers, viewCustomer, editCustomer, deleteCustomer } from './modules/customers.js';
import { initializeCategories, editCategory, deleteCategory, promptAddCategory } from './modules/categories.js';
import { initializeNotifications, toggleNotifications } from './modules/notifications.js';
import { initializeModals, showModal, hideModal } from './modules/modals.js';
import { initializeSearchFilters } from './modules/searchFilters.js';
import { initializeAnalytics } from './modules/analytics.js';

// Shared state object
const state = {
    orders: [],
    products: [],
    customers: [],
    categories: [],
    notifications: [],
    lowStockData: {}
};

// Make functions available globally for onclick handlers
window.adminModules = {
    // Navigation
    showSection,
    
    // Orders CRUD
    viewOrder: (id) => viewOrder(id, state),
    editOrder: (id) => editOrder(id, state),
    deleteOrder: (id) => deleteOrder(id, state),
    
    // Products CRUD
    viewProduct: (id) => viewProduct(id, state),
    editProduct: (id) => editProduct(id, state),
    deleteProduct: (id) => deleteProduct(id, state),
    
    // Customers CRUD
    viewCustomer: (id) => viewCustomer(id, state),
    editCustomer: (id) => editCustomer(id, state),
    deleteCustomer: (id) => deleteCustomer(id, state),
    
    // Categories CRUD
    editCategory: (id) => editCategory(id, state),
    deleteCategory: (id) => deleteCategory(id, state),
    promptAddCategory: () => promptAddCategory(state),
    
    // Modals
    showModal,
    hideModal,
    
    // Notifications
    toggleNotifications,
    
    // Order Status Management
    updateOrderStatus: async (orderId, newStatus, notes = '') => {
        try {
            const response = await fetch('./API/update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: newStatus,
                    notes: notes
                })
            });
            
            const data = await response.json();
            if (data.success) {
                // Refresh orders list
                await initializeOrders(state);
                return data;
            } else {
                throw new Error(data.error || 'Failed to update order status');
            }
        } catch (error) {
            console.error('Error updating order status:', error);
            throw error;
        }
    },
    
    // Stock Management
    manageStock: async (productId, action, quantity = 0) => {
        try {
            const response = await fetch('./API/update_stock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productId,
                    action: action, // 'set', 'add', 'subtract'
                    quantity: quantity
                })
            });
            
            const data = await response.json();
            if (data.success) {
                // Refresh products list
                await initializeProducts(state);
                return data;
            } else {
                throw new Error(data.error || 'Failed to update stock');
            }
        } catch (error) {
            console.error('Error updating stock:', error);
            throw error;
        }
    },
    
    // Generate Reports
    generateReport: () => {
        // This function is already defined in analytics.js
        if (window.adminModules.generateReport) {
            return window.adminModules.generateReport();
        }
    },
    
    // Settings
    loadSettings: async () => {
        try {
            const response = await fetch('./API/get_settings.php');
            const data = await response.json();
            if (data.success) {
                return data.settings;
            } else {
                throw new Error(data.error || 'Failed to load settings');
            }
        } catch (error) {
            console.error('Error loading settings:', error);
            return null;
        }
    },
    
    saveSettings: async (settings) => {
        try {
            const response = await fetch('./API/update_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(settings)
            });
            const data = await response.json();
            if (data.success) {
                return data;
            } else {
                throw new Error(data.error || 'Failed to save settings');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            throw error;
        }
    },
    
    backupDatabase: () => {
        // Placeholder for database backup functionality
        alert('Database backup functionality will be implemented here');
    },
    
    downloadLogs: () => {
        // Placeholder for logs download functionality
        alert('Logs download functionality will be implemented here');
    }
};

// Initialize the application
document.addEventListener('DOMContentLoaded', async () => {
    console.log('Initializing Modular Admin Dashboard...');
    
    try {
        // Show current section or dashboard by default
        const currentSection = window.location.hash.slice(1) || 'dashboard';
        showSection(currentSection);
        
        // Initialize all modules
        await Promise.allSettled([
            initializeDashboard(state),
            initializeOrders(state),
            initializeProducts(state),
            initializeCustomers(state),
            initializeCategories(state),
            initializeNotifications(state),
            initializeAnalytics()
        ]);
        
        // Initialize UI components
        initializeModals(state);
        initializeSearchFilters(state);
        
        // Initialize settings forms
        initializeSettingsForms();
        
        // Add event listeners for sidebar navigation
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const sectionId = link.getAttribute('data-section');
                console.log(`Switching to section: ${sectionId}`);
                showSection(sectionId);
                // Update URL hash to maintain state
                window.location.hash = sectionId;
            });
        });
        
        console.log('Admin Dashboard initialized successfully!');
        
        // Set up periodic low stock checking (every 5 minutes)
        setInterval(() => {
            initializeNotifications(state);
        }, 5 * 60 * 1000);
        
    } catch (error) {
        console.error('Error initializing admin dashboard:', error);
    }
});

// Initialize settings forms
async function initializeSettingsForms() {
    try {
        // Load current settings
        const settings = await window.adminModules.loadSettings();
        if (!settings) return;
        
        // Populate general settings form
        const generalForm = document.getElementById('general-settings-form');
        if (generalForm) {
            generalForm.querySelector('[name="tax_rate"]').value = settings.tax_rate || 16;
            generalForm.querySelector('[name="standard_delivery_fee"]').value = settings.standard_delivery_fee || 0;
            generalForm.querySelector('[name="express_delivery_fee"]').value = settings.express_delivery_fee || 500;
            generalForm.querySelector('[name="store_name"]').value = settings.store_name || 'Jowaki Electrical Services';
            generalForm.querySelector('[name="store_email"]').value = settings.store_email || 'info@jowaki.com';
            generalForm.querySelector('[name="store_phone"]').value = settings.store_phone || '+254721442248';
            generalForm.querySelector('[name="store_address"]').value = settings.store_address || '';
            
            // Add form submit handler
            generalForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(generalForm);
                const settingsData = {};
                
                for (let [key, value] of formData.entries()) {
                    if (key === 'tax_rate' || key === 'standard_delivery_fee' || key === 'express_delivery_fee') {
                        settingsData[key] = parseFloat(value);
                    } else {
                        settingsData[key] = value;
                    }
                }
                
                try {
                    await window.adminModules.saveSettings(settingsData);
                    alert('General settings saved successfully!');
                    window.adminModules.hideModal('general-settings-modal');
                } catch (error) {
                    alert('Error saving settings: ' + error.message);
                }
            });
        }
        
        // Populate payment settings form
        const paymentForm = document.getElementById('payment-settings-form');
        if (paymentForm) {
            paymentForm.querySelector('[name="enable_mpesa"]').checked = settings.enable_mpesa || false;
            paymentForm.querySelector('[name="mpesa_business_number"]').value = settings.mpesa_business_number || '254721442248';
            paymentForm.querySelector('[name="enable_card"]').checked = settings.enable_card || false;
            paymentForm.querySelector('[name="enable_whatsapp"]').checked = settings.enable_whatsapp || false;
            paymentForm.querySelector('[name="whatsapp_number"]').value = settings.whatsapp_number || '254721442248';
            
            // Add form submit handler
            paymentForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(paymentForm);
                const settingsData = {};
                
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('enable_')) {
                        settingsData[key] = value === 'on';
                    } else {
                        settingsData[key] = value;
                    }
                }
                
                try {
                    await window.adminModules.saveSettings(settingsData);
                    alert('Payment settings saved successfully!');
                    window.adminModules.hideModal('payment-settings-modal');
                } catch (error) {
                    alert('Error saving settings: ' + error.message);
                }
            });
        }
        
        // Populate shipping settings form
        const shippingForm = document.getElementById('shipping-settings-form');
        if (shippingForm) {
            shippingForm.querySelector('[name="enable_standard_delivery"]').checked = settings.enable_standard_delivery || false;
            shippingForm.querySelector('[name="standard_delivery_time"]').value = settings.standard_delivery_time || '3-5 business days';
            shippingForm.querySelector('[name="enable_express_delivery"]').checked = settings.enable_express_delivery || false;
            shippingForm.querySelector('[name="express_delivery_time"]').value = settings.express_delivery_time || '1-2 business days';
            shippingForm.querySelector('[name="enable_pickup"]').checked = settings.enable_pickup || false;
            shippingForm.querySelector('[name="pickup_location"]').value = settings.pickup_location || '';
            
            // Add form submit handler
            shippingForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(shippingForm);
                const settingsData = {};
                
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('enable_')) {
                        settingsData[key] = value === 'on';
                    } else {
                        settingsData[key] = value;
                    }
                }
                
                try {
                    await window.adminModules.saveSettings(settingsData);
                    alert('Shipping settings saved successfully!');
                    window.adminModules.hideModal('shipping-settings-modal');
                } catch (error) {
                    alert('Error saving settings: ' + error.message);
                }
            });
        }
        
        // Populate backup settings form
        const backupForm = document.getElementById('backup-settings-form');
        if (backupForm) {
            backupForm.querySelector('[name="enable_2fa"]').checked = settings.enable_2fa || false;
            backupForm.querySelector('[name="enable_login_notifications"]').checked = settings.enable_login_notifications || false;
            backupForm.querySelector('[name="enable_audit_log"]').checked = settings.enable_audit_log || false;
            
            // Add form submit handler
            backupForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(backupForm);
                const settingsData = {};
                
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('enable_')) {
                        settingsData[key] = value === 'on';
                    } else {
                        settingsData[key] = value;
                    }
                }
                
                try {
                    await window.adminModules.saveSettings(settingsData);
                    alert('Security settings saved successfully!');
                    window.adminModules.hideModal('backup-settings-modal');
                } catch (error) {
                    alert('Error saving settings: ' + error.message);
                }
            });
        }
        
    } catch (error) {
        console.error('Error initializing settings forms:', error);
    }
}
