// Settings Management Module
export class SettingsManager {
    constructor() {
        this.settings = {};
        this.init();
    }

    async init() {
        await this.loadSettings();
        this.setupEventListeners();
    }

    async loadSettings() {
        try {
            const response = await fetch('API/get_settings_test.php');
            const data = await response.json();
            
            if (data.success) {
                this.settings = data.settings;
                this.populateSettingsForms();
            } else {
                console.error('Failed to load settings:', data.error);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    populateSettingsForms() {
        // General Settings Form
        const generalForm = document.getElementById('general-settings-form');
        if (generalForm) {
            generalForm.querySelector('[name="tax_rate"]').value = this.settings.tax_rate || 16;
            generalForm.querySelector('[name="standard_delivery_fee"]').value = this.settings.standard_delivery_fee || 0;
            generalForm.querySelector('[name="express_delivery_fee"]').value = this.settings.express_delivery_fee || 500;
            generalForm.querySelector('[name="store_name"]').value = this.settings.store_name || 'Jowaki Electrical Services';
            generalForm.querySelector('[name="store_email"]').value = this.settings.store_email || 'info@jowaki.com';
            generalForm.querySelector('[name="store_phone"]').value = this.settings.store_phone || '+254721442248';
            generalForm.querySelector('[name="store_address"]').value = this.settings.store_address || '';
        }

        // Payment Settings Form
        const paymentForm = document.getElementById('payment-settings-form');
        if (paymentForm) {
            paymentForm.querySelector('[name="enable_mpesa"]').checked = this.settings.enable_mpesa || false;
            paymentForm.querySelector('[name="mpesa_business_number"]').value = this.settings.mpesa_business_number || '254721442248';
            paymentForm.querySelector('[name="enable_card"]').checked = this.settings.enable_card || false;
            paymentForm.querySelector('[name="enable_whatsapp"]').checked = this.settings.enable_whatsapp || false;
            paymentForm.querySelector('[name="whatsapp_number"]').value = this.settings.whatsapp_number || '254721442248';
        }

        // Shipping Settings Form
        const shippingForm = document.getElementById('shipping-settings-form');
        if (shippingForm) {
            shippingForm.querySelector('[name="enable_standard_delivery"]').checked = this.settings.enable_standard_delivery || false;
            shippingForm.querySelector('[name="standard_delivery_time"]').value = this.settings.standard_delivery_time || '3-5 business days';
            shippingForm.querySelector('[name="enable_express_delivery"]').checked = this.settings.enable_express_delivery || false;
            shippingForm.querySelector('[name="express_delivery_time"]').value = this.settings.express_delivery_time || '1-2 business days';
            shippingForm.querySelector('[name="enable_pickup"]').checked = this.settings.enable_pickup || false;
            shippingForm.querySelector('[name="pickup_location"]').value = this.settings.pickup_location || '';
        }

        // Backup Settings Form
        const backupForm = document.getElementById('backup-settings-form');
        if (backupForm) {
            backupForm.querySelector('[name="enable_2fa"]').checked = this.settings.enable_2fa || false;
            backupForm.querySelector('[name="enable_login_notifications"]').checked = this.settings.enable_login_notifications || false;
            backupForm.querySelector('[name="enable_audit_log"]').checked = this.settings.enable_audit_log || false;
        }
    }

    setupEventListeners() {
        // General Settings Form
        const generalForm = document.getElementById('general-settings-form');
        if (generalForm) {
            generalForm.addEventListener('submit', (e) => this.handleGeneralSettings(e));
        }

        // Payment Settings Form
        const paymentForm = document.getElementById('payment-settings-form');
        if (paymentForm) {
            paymentForm.addEventListener('submit', (e) => this.handlePaymentSettings(e));
        }

        // Shipping Settings Form
        const shippingForm = document.getElementById('shipping-settings-form');
        if (shippingForm) {
            shippingForm.addEventListener('submit', (e) => this.handleShippingSettings(e));
        }

        // Backup Settings Form
        const backupForm = document.getElementById('backup-settings-form');
        if (backupForm) {
            backupForm.addEventListener('submit', (e) => this.handleBackupSettings(e));
        }
    }

    async handleGeneralSettings(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const settings = {
            tax_rate: parseFloat(formData.get('tax_rate')),
            standard_delivery_fee: parseFloat(formData.get('standard_delivery_fee')),
            express_delivery_fee: parseFloat(formData.get('express_delivery_fee')),
            store_name: formData.get('store_name'),
            store_email: formData.get('store_email'),
            store_phone: formData.get('store_phone'),
            store_address: formData.get('store_address')
        };

        await this.updateSettings(settings, 'General settings updated successfully');
    }

    async handlePaymentSettings(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const settings = {
            enable_mpesa: formData.get('enable_mpesa') === 'on',
            mpesa_business_number: formData.get('mpesa_business_number'),
            enable_card: formData.get('enable_card') === 'on',
            enable_whatsapp: formData.get('enable_whatsapp') === 'on',
            whatsapp_number: formData.get('whatsapp_number')
        };

        await this.updateSettings(settings, 'Payment settings updated successfully');
    }

    async handleShippingSettings(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const settings = {
            enable_standard_delivery: formData.get('enable_standard_delivery') === 'on',
            standard_delivery_time: formData.get('standard_delivery_time'),
            enable_express_delivery: formData.get('enable_express_delivery') === 'on',
            express_delivery_time: formData.get('express_delivery_time'),
            enable_pickup: formData.get('enable_pickup') === 'on',
            pickup_location: formData.get('pickup_location')
        };

        await this.updateSettings(settings, 'Shipping settings updated successfully');
    }

    async handleBackupSettings(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const settings = {
            enable_2fa: formData.get('enable_2fa') === 'on',
            enable_login_notifications: formData.get('enable_login_notifications') === 'on',
            enable_audit_log: formData.get('enable_audit_log') === 'on'
        };

        await this.updateSettings(settings, 'Security settings updated successfully');
    }

    async updateSettings(settings, successMessage) {
        try {
            const response = await fetch('API/update_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(settings)
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification(successMessage, 'success');
                // Update local settings
                Object.assign(this.settings, settings);
            } else {
                this.showNotification('Failed to update settings: ' + data.error, 'error');
            }
        } catch (error) {
            console.error('Error updating settings:', error);
            this.showNotification('Error updating settings', 'error');
        }
    }

    async backupDatabase() {
        try {
            const response = await fetch('API/backup_database.php');
            const blob = await response.blob();
            
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `jowaki_backup_${new Date().toISOString().split('T')[0]}.sql`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            this.showNotification('Database backup downloaded successfully', 'success');
        } catch (error) {
            console.error('Error downloading backup:', error);
            this.showNotification('Error downloading backup', 'error');
        }
    }

    async downloadLogs() {
        try {
            const response = await fetch('API/download_logs.php');
            const blob = await response.blob();
            
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `jowaki_logs_${new Date().toISOString().split('T')[0]}.txt`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            this.showNotification('Logs downloaded successfully', 'success');
        } catch (error) {
            console.error('Error downloading logs:', error);
            this.showNotification('Error downloading logs', 'error');
        }
    }

    showNotification(message, type = 'info') {
        // Use the existing notification system
        if (window.adminModules && window.adminModules.showNotification) {
            window.adminModules.showNotification(message, type);
        } else {
            alert(message);
        }
    }
}

// Initialize settings manager when module is loaded
if (typeof window !== 'undefined') {
    window.settingsManager = new SettingsManager();
} 