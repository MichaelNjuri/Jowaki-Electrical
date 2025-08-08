import { sanitizeHTML } from './utils.js';

// Notification system
export function showNotification(message, type = 'info') {
    const existingNotification = document.querySelector('.notification-popup');
    if (existingNotification) {
        existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification-popup notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${sanitizeHTML(message)}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;

    document.body.appendChild(notification);
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 15000);
}

export function toggleNotifications() {
    const dropdown = document.getElementById('notification-dropdown');
    if (!dropdown) {
        console.error('Notifications dropdown not found');
        return;
    }
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';
}

function processNotificationData(notification) {
    return {
        id: parseInt(notification.id) || 0,
        message: sanitizeHTML(notification.message || 'No message'),
        type: sanitizeHTML(notification.type || 'info')
    };
}

// Fetch low stock notifications
export function fetchLowStockNotifications(state) {
    return fetch('API/check_low_stock.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Process low stock notifications
                const processedNotifications = data.notifications.map(notification => ({
                    id: notification.id,
                    message: notification.message,
                    type: notification.urgency === 'critical' ? 'error' : 'warning',
                    title: notification.title,
                    timestamp: notification.timestamp,
                    details: notification.details,
                    action_url: notification.action_url
                }));
                
                state.notifications = processedNotifications;
                state.lowStockData = {
                    total: data.total_low_stock,
                    products: data.low_stock_products,
                    summary: data.summary
                };
                
                renderNotifications(state);
                
                // Update dashboard with low stock info if available
                updateLowStockDashboard(data);
                
            } else {
                throw new Error(data.error || 'Failed to fetch low stock notifications');
            }
        })
        .catch(error => {
            console.error('Low stock notifications fetch error:', error);
            showNotification(`Error fetching low stock notifications: ${error.message}`, 'error');
            renderNotifications(state);
        });
}

export function fetchNotifications(state) {
    return fetch('API/get_notifications_admin.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success && Array.isArray(data.notifications)) {
                state.notifications = data.notifications.map(notification => processNotificationData(notification));
                renderNotifications(state);
            } else {
                throw new Error(data.error || 'Failed to fetch notifications');
            }
        })
        .catch(error => {
            console.error('Notifications fetch error:', error);
            showNotification(`Error fetching notifications: ${error.message}`, 'error');
            renderNotifications(state);
        });
}

// Update dashboard with low stock information
function updateLowStockDashboard(data) {
    // Add low stock count to dashboard if element exists
    const lowStockElement = document.getElementById('low-stock-products');
    if (lowStockElement) {
        lowStockElement.textContent = data.total_low_stock;
    }
    
    // Show critical stock alerts as popup notifications
    if (data.summary.critical_count > 0) {
        const criticalProducts = data.notifications.filter(n => n.urgency === 'critical');
        criticalProducts.forEach(product => {
            showNotification(
                `Critical: ${product.details.product_name} is out of stock!`,
                'error'
            );
        });
    }
}

// Enhanced notification rendering with better styling
function renderNotifications(state) {
    const notificationList = document.getElementById('notification-dropdown');
    const notificationCount = document.getElementById('notification-count');
    
    if (!notificationList || !notificationCount) {
        console.error('Notification elements not found!');
        return;
    }
    
    // Clear existing content
    notificationList.innerHTML = '';
    notificationCount.textContent = state.notifications.length;
    
    // Hide count badge if no notifications
    notificationCount.style.display = state.notifications.length > 0 ? 'block' : 'none';
    
    // Add header
    const header = document.createElement('div');
    header.className = 'notification-header';
    header.textContent = `Notifications (${state.notifications.length})`;
    notificationList.appendChild(header);
    
    if (state.notifications.length === 0) {
        const emptyMessage = document.createElement('div');
        emptyMessage.className = 'notification';
        emptyMessage.style.textAlign = 'center';
        emptyMessage.style.color = '#666';
        emptyMessage.innerHTML = `
            <div style="padding: 20px;">
                <i class="fas fa-bell-slash" style="font-size: 24px; margin-bottom: 10px; opacity: 0.5;"></i>
                <div>No notifications</div>
            </div>
        `;
        notificationList.appendChild(emptyMessage);
        return;
    }
    
    // Add notifications
    state.notifications.forEach(notification => {
        const div = document.createElement('div');
        div.className = `notification notification-${notification.type}`;
        
        const icon = notification.type === 'error' ? 'fas fa-exclamation-triangle' : 
                    notification.type === 'warning' ? 'fas fa-exclamation-circle' : 
                    'fas fa-info-circle';
        
        div.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <i class="${icon}" style="margin-top: 2px; color: ${notification.type === 'error' ? '#ef4444' : notification.type === 'warning' ? '#f59e0b' : '#2563eb'};"></i>
                <div style="flex: 1;">
                    <div style="font-weight: 500; margin-bottom: 4px;">${sanitizeHTML(notification.title || 'Notification')}</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">${sanitizeHTML(notification.message)}</div>
                    ${notification.details && notification.details.product_name ? 
                        `<div style="font-size: 0.75rem; color: #9ca3af; margin-top: 4px;">Stock: ${notification.details.current_stock} / Threshold: ${notification.details.threshold}</div>` : 
                        ''}
                </div>
            </div>
        `;
        
        // Add click handler to navigate to products section
        if (notification.action_url) {
            div.style.cursor = 'pointer';
            div.addEventListener('click', () => {
                // Navigate to products section
                const productLink = document.querySelector(`[data-section="${notification.action_url}"]`);
                if (productLink) {
                    productLink.click();
                }
                // Hide dropdown
                notificationList.style.display = 'none';
            });
        }
        
        notificationList.appendChild(div);
    });
}

export function initializeNotifications(state) {
    // Fetch both regular notifications and low stock notifications
    return Promise.all([
        fetchLowStockNotifications(state),
        // fetchNotifications(state) // Uncomment when you have regular notifications API
    ]).catch(error => {
        console.error('Failed to initialize notifications:', error);
    });
}
