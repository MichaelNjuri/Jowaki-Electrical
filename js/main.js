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
import { storeCategoriesModule, initializeStoreCategories } from './modules/storeCategories.js';
import { contactMessagesModule, initializeContactMessages } from './modules/contactMessages.js';
import adminManagement from './modules/adminManagement.js';
import adminProfile from './modules/adminProfile.js';
import { SettingsManager } from './modules/settings.js';

// Shared state object
const state = {
    orders: [],
    products: [],
    customers: [],
    categories: [],
    notifications: [],
    lowStockData: {}
};

// Expose state globally for button onclick handlers
window.adminState = state;

// Expose managers globally
window.storeCategoriesModule = storeCategoriesModule;
window.contactMessagesModule = contactMessagesModule;
window.adminManagement = adminManagement;
window.settingsManager = new SettingsManager();

// Make functions available globally for onclick handlers
window.adminModules = {
    // Navigation
    showSection,
    
    // Dashboard navigation functions
    showOrdersSection: () => showSection('orders'),
    showProductsSection: () => showSection('products'),
    showCustomersSection: () => showSection('customers'),
    showCategoriesSection: () => showSection('categories'),
    showAnalyticsSection: () => showSection('analytics'),
    showContactMessagesSection: () => showSection('contact-messages'),
    
    // Orders CRUD
    viewOrder: (id) => viewOrder(id, state),
    editOrder: (id) => editOrder(id, state),
    deleteOrder: (id) => deleteOrder(id, state),
    
    // Order Status Management - Now uses modal interface
    updateOrderStatus: (orderId, newStatus, state) => {
        // Import the new modal-based function
        import('./modules/orders.js').then(module => {
            module.updateOrderStatus(orderId, newStatus, state);
        }).catch(error => {
            console.error('Error importing orders module:', error);
            showNotification('Error loading order status update interface', 'error');
        });
    },
    
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
    addCategory: () => promptAddCategory(state),
    
    // Notifications
    toggleNotifications: () => toggleNotifications(state),
    
    // Modals
    showModal: (modalId) => showModal(modalId),
    hideModal: (modalId) => hideModal(modalId),
    
    // Admin Management
    createAdmin: () => adminManagement.createAdmin(),
    loadAdmins: () => adminManagement.loadAdmins(),
    refreshAdmins: () => adminManagement.refreshAdmins(),
    loadActivity: () => adminManagement.loadActivity(),
    refreshActivity: () => adminManagement.refreshActivity(),
    viewAdmin: (adminId) => adminManagement.viewAdmin(adminId),
    editAdmin: (adminId) => adminManagement.editAdmin(adminId),
    toggleAdminStatus: (adminId, currentStatus) => adminManagement.toggleAdminStatus(adminId, currentStatus),
    
    // Admin Profile functions
    loadProfile: () => adminProfile.loadProfile(),
    refreshProfile: () => adminProfile.refreshProfile(),
    updateProfile: () => adminProfile.updateProfile(),
    
    // Settings
    loadSettings: async () => {
        try {
            const response = await fetch('API/get_settings.php');
            const data = await response.json();
            return data.success ? data.settings : null;
        } catch (error) {
            console.error('Error loading settings:', error);
            return null;
        }
    },
    saveSettings: async (settingsData) => {
        try {
            const response = await fetch('API/update_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(settingsData)
            });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || 'Failed to save settings');
            }
            return data;
        } catch (error) {
            console.error('Error saving settings:', error);
            throw error;
        }
    },
    backupDatabase: () => window.settingsManager.backupDatabase(),
    downloadLogs: () => window.settingsManager.downloadLogs(),
    
    // Profile Dropdown
    toggleProfileDropdown: () => {
        const dropdown = document.querySelector('.profile-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('active');
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function closeDropdown(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                    document.removeEventListener('click', closeDropdown);
                }
            });
        }
    },

    // Force check admin management visibility
    checkAdminManagementVisibility: () => {
        const adminUserStr = localStorage.getItem('adminUser');
        const adminManagementLink = document.getElementById('admin-management-link');
        
        if (adminManagementLink && adminUserStr) {
            try {
                const adminUser = JSON.parse(adminUserStr);
                console.log('Current admin user:', adminUser);
                
                if (adminUser.role_id === 1) {
                    adminManagementLink.style.display = 'block';
                    console.log('✅ Admin Management should be visible for Super Admin');
                } else {
                    adminManagementLink.style.display = 'none';
                    console.log('❌ Admin Management hidden for Regular Admin');
                }
            } catch (error) {
                console.error('Error checking admin management visibility:', error);
                adminManagementLink.style.display = 'none';
            }
        } else {
            console.log('Admin management link not found or no admin user data');
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
            const response = await fetch('./API/get_settings_fixed.php', {
                credentials: 'include'
            });
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
                credentials: 'include',
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
    },
    
    // Store Categories Management
    addCategory: (formData) => storeCategoriesModule.addStoreCategory(formData),
    editCategory: (id) => storeCategoriesModule.loadCategoryForEdit(id),
    deleteCategory: (id) => storeCategoriesModule.deleteStoreCategory(id),
    updateCategory: (formData) => storeCategoriesModule.editStoreCategory(formData),
    exportCategoriesCSV: () => storeCategoriesModule.exportStoreCategoriesCSV(),
    importCategoriesCSV: () => storeCategoriesModule.searchStoreCategories(),
    
    // Logout function
    logout: () => {
        if (confirm('Are you sure you want to logout?')) {
            // Clear any stored session data
            sessionStorage.clear();
            localStorage.removeItem('adminUser');
            
            // Call admin logout endpoint
            fetch('API/admin_logout.php')
                .then(() => {
                    // Redirect to admin login page
                    window.location.href = 'admin_login.html';
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    // Fallback: redirect to admin login page anyway
                    window.location.href = 'admin_login.html';
                });
        }
    }
};

// Utility function to show notifications
function showNotification(message, type = 'info') {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.notification-popup');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-popup notification-${type}`;
    
    // Notification content
    notification.innerHTML = `
        <div class="notification-content">
            <div>
                <strong>${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <p>${message}</p>
            </div>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show with animation
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Check authentication on page load
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Check if admin is logged in
        const adminUser = localStorage.getItem('adminUser');
        if (!adminUser) {
            // Show message instead of redirecting
            document.body.innerHTML = `
                <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
                    <h2>Not Logged In</h2>
                    <p>You need to log in to access the admin dashboard.</p>
                    <a href="admin_login.html" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Go to Login</a>
                </div>
            `;
            return;
        }

        // Verify session is still valid
        try {
            const response = await fetch('./API/check_auth.php');
            const authData = await response.json();
            
            if (!authData.success || !authData.authenticated) {
                // Session expired, show message instead of redirecting
                localStorage.removeItem('adminUser');
                document.body.innerHTML = `
                    <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
                        <h2>Session Expired</h2>
                        <p>Your session has expired. Please log in again.</p>
                        <a href="admin_login.html" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Go to Login</a>
                    </div>
                `;
                return;
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            // If auth check fails, show message instead of redirecting
            localStorage.removeItem('adminUser');
            document.body.innerHTML = `
                <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
                    <h2>Authentication Error</h2>
                    <p>Unable to verify your session. Please log in again.</p>
                    <a href="admin_login.html" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Go to Login</a>
                </div>
            `;
            return;
        }

        // Initialize admin dashboard
        await initializeAdminDashboard();
        
    } catch (error) {
        console.error('Error during initialization:', error);
        // If initialization fails, show message instead of redirecting
        localStorage.removeItem('adminUser');
        document.body.innerHTML = `
            <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
                <h2>Initialization Error</h2>
                <p>Unable to initialize the admin dashboard. Please try refreshing the page or log in again.</p>
                <a href="admin_login.html" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Go to Login</a>
                <button onclick="location.reload()" style="display: inline-block; margin-left: 10px; padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">Refresh Page</button>
            </div>
        `;
    }
});

// Main initialization function
async function initializeAdminDashboard() {
    try {
        // Initialize all modules
        await initializeDashboard(state);
        await initializeOrders(state);
        await initializeProducts(state);
        await initializeCustomers(state);
        await initializeCategories(state);
        await initializeAnalytics(state);
        await initializeNotifications(state);
        await adminManagement.init();
        
        // Initialize admin profile
        await adminProfile.init();
        
        // Initialize contact messages
        initializeContactMessages();
        
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
}

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
                    showNotification('General settings saved successfully!', 'success');
                    window.adminModules.hideModal('general-settings-modal');
                } catch (error) {
                    showNotification('Error saving settings: ' + error.message, 'error');
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
                    showNotification('Payment settings saved successfully!', 'success');
                    window.adminModules.hideModal('payment-settings-modal');
                } catch (error) {
                    showNotification('Error saving settings: ' + error.message, 'error');
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
                    showNotification('Shipping settings saved successfully!', 'success');
                    window.adminModules.hideModal('shipping-settings-modal');
                } catch (error) {
                    showNotification('Error saving settings: ' + error.message, 'error');
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
                    showNotification('Security settings saved successfully!', 'success');
                    window.adminModules.hideModal('backup-settings-modal');
                } catch (error) {
                    showNotification('Error saving settings: ' + error.message, 'error');
                }
            });
        }
        
    } catch (error) {
        console.error('Error initializing settings forms:', error);
    }
}