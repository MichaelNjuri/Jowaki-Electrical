// Profile Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize profile page
    initializeProfile();
    
    // Load user data and statistics
    loadUserStats();
    
    // Load order history
    loadOrderHistory();
    
    // Initialize event listeners
    initializeEventListeners();
});

// Initialize profile page
function initializeProfile() {
    // Set active section based on URL hash
    const hash = window.location.hash.substring(1);
    if (hash) {
        switchSection(hash);
    }
    
    // Calculate profile completion
    calculateProfileCompletion();
}

// Switch between sections
function switchSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active class from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Show selected section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Add active class to corresponding nav item
    const navItem = document.querySelector(`[data-section="${sectionId}"]`);
    if (navItem) {
        navItem.classList.add('active');
    }
    
    // Update URL hash
    window.location.hash = sectionId;
}

// Initialize event listeners
function initializeEventListeners() {
    // Navigation click handlers
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('href').substring(1);
            switchSection(sectionId);
        });
    });
    
    // Modal event listeners
    const editProfileForm = document.getElementById('edit-profile-form');
    const changePasswordForm = document.getElementById('change-password-form');
    
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', handleProfileUpdate);
    }
    
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', handlePasswordChange);
    }
    
    // Settings toggles
    const settingToggles = document.querySelectorAll('.switch input');
    if (settingToggles.length > 0) {
        settingToggles.forEach(toggle => {
            toggle.addEventListener('change', handleSettingToggle);
        });
    }
}

// Load user statistics
function loadUserStats() {
    fetch('get_user_stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateStats(data.stats);
            } else {
                console.error('Error loading user stats:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading user stats:', error);
        });
}

// Update statistics display
function updateStats(stats) {
    const totalOrdersElement = document.getElementById('total-orders');
    if (totalOrdersElement) {
        totalOrdersElement.textContent = stats.total_orders || 0;
    }
}

// Load order history
function loadOrderHistory() {
    fetch('get_user_orders.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayOrders(data.orders);
            } else {
                console.error('Error loading orders:', data.error);
                displayNoOrders();
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
            displayNoOrders();
        });
}

// Display orders
function displayOrders(orders) {
    const ordersList = document.getElementById('orders-list');
    
    // Store order data globally for modal access
    window.orderData = orders;
    
    if (orders.length === 0) {
        displayNoOrders();
        return;
    }
    
    ordersList.innerHTML = orders.map(order => {
        // For cancelled orders, show minimal content
        if (order.status.toLowerCase() === 'cancelled') {
            return `
                <div class="order-item cancelled-order">
                    <div class="order-header">
                        <div class="order-header-left">
                            <span class="order-id">Order #${order.order_id}</span>
                            <span class="order-date">${formatDate(order.order_date)}</span>
                        </div>
                        <span class="order-status ${order.status.toLowerCase()}">${order.status}</span>
                    </div>
                    
                    <div class="order-actions-preview">
                        <button class="view-order-btn" onclick="viewOrder('${order.order_id}')">
                            <i class="fas fa-eye"></i>
                            View Details
                        </button>
                    </div>
                </div>
            `;
        }
        
        // For active orders, show full content
        return `
            <div class="order-item">
                <div class="order-header">
                    <div class="order-header-left">
                        <span class="order-id">Order #${order.order_id}</span>
                        <span class="order-date">${formatDate(order.order_date)}</span>
                    </div>
                    <span class="order-status ${order.status.toLowerCase()}">${order.status}</span>
                </div>
                
                <div class="order-progress">
                    <div class="progress-step ${getProgressClass(order.status, 'ordered')}">
                        <div class="step-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="step-label">Ordered</div>
                    </div>
                    <div class="progress-line ${getProgressClass(order.status, 'processing')}"></div>
                    <div class="progress-step ${getProgressClass(order.status, 'processing')}">
                        <div class="step-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="step-label">Processing</div>
                    </div>
                    <div class="progress-line ${getProgressClass(order.status, 'shipped')}"></div>
                    <div class="progress-step ${getProgressClass(order.status, 'shipped')}">
                        <div class="step-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="step-label">Shipped</div>
                    </div>
                    <div class="progress-line ${getProgressClass(order.status, 'delivered')}"></div>
                    <div class="progress-step ${getProgressClass(order.status, 'delivered')}">
                        <div class="step-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="step-label">Delivered</div>
                    </div>
                </div>
                
                <div class="order-items-preview">
                    ${order.items.slice(0, 3).map(item => `
                        <div class="order-item-preview">
                            <div class="item-image">
                                <img src="${item.image || '../images/placeholder-product.jpg'}" alt="${item.name}" onerror="this.src='../images/placeholder-product.jpg'">
                            </div>
                            <div class="item-details">
                                <div class="item-name">${item.name}</div>
                                <div class="item-quantity">Qty: ${item.quantity}</div>
                            </div>
                        </div>
                    `).join('')}
                    ${order.items.length > 3 ? `<div class="more-items">+${order.items.length - 3} more items</div>` : ''}
                </div>
                
                <div class="order-summary-preview">
                    <div class="summary-item">
                        <span class="summary-label">Total Items</span>
                        <span class="summary-value">${order.item_count}</span>
                    </div>
                </div>
                
                <div class="order-actions-preview">
                    <button class="view-order-btn" onclick="viewOrder('${order.order_id}')">
                        <i class="fas fa-eye"></i>
                        View Details
                    </button>
                    ${order.status === 'pending' ? `
                        <button class="cancel-order-btn" onclick="cancelOrder('${order.order_id}')">
                            <i class="fas fa-times"></i>
                            Cancel Order
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');
}

// Get progress class based on order status
function getProgressClass(orderStatus, stepStatus) {
    const statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
    const orderIndex = statusOrder.indexOf(orderStatus.toLowerCase());
    const stepIndex = statusOrder.indexOf(stepStatus);
    
    if (stepIndex <= orderIndex) {
        return 'completed';
    } else {
        return 'pending';
    }
}

// Display no orders message
function displayNoOrders() {
    const ordersList = document.getElementById('orders-list');
    ordersList.innerHTML = `
        <div class="no-orders">
            <i class="fas fa-shopping-bag"></i>
            <h3>No Orders Yet</h3>
            <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
            <a href="Store.php" class="btn btn-primary">Start Shopping</a>
        </div>
    `;
}

// Calculate profile completion
function calculateProfileCompletion() {
    // This function is no longer needed since we removed the profile completion stat card
    // Keeping the function for future use if needed
    console.log('Profile completion calculation removed');
}

// Edit profile modal
function editProfile() {
    const modal = document.getElementById('edit-profile-modal');
    modal.classList.add('active');
}

// Change password modal
function changePassword() {
    const modal = document.getElementById('change-password-modal');
    modal.classList.add('active');
}

// Close modal
function closeModal() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('active');
    });
}

// Handle profile update
function handleProfileUpdate(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    fetch('update_user_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
            closeModal();
            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.error || 'Failed to update profile', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        showNotification('An error occurred while updating profile', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Save Changes';
    });
}

// Handle password change
function handlePasswordChange(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // Validate passwords match
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');
    
    if (newPassword !== confirmPassword) {
        showNotification('New passwords do not match', 'error');
        return;
    }
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';
    
    fetch('change_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Password changed successfully!', 'success');
            closeModal();
            e.target.reset();
        } else {
            showNotification(data.error || 'Failed to change password', 'error');
        }
    })
    .catch(error => {
        console.error('Error changing password:', error);
        showNotification('An error occurred while changing password', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Change Password';
    });
}

// Handle setting toggles
function handleSettingToggle(e) {
    const setting = e.target.closest('.setting-card').querySelector('h3').textContent;
    const enabled = e.target.checked;
    
    // Here you would typically save the setting to the database
    console.log(`${setting}: ${enabled ? 'enabled' : 'disabled'}`);
    
    showNotification(`${setting} ${enabled ? 'enabled' : 'disabled'}`, 'success');
}

// Edit address
function editAddress() {
    // For now, just open the edit profile modal
    editProfile();
}

// Manage notifications
function manageNotifications() {
    showNotification('Notification settings will be implemented soon!', 'info');
}

// View order details
function viewOrder(orderId) {
    // Find the order data
    const order = window.orderData ? window.orderData.find(o => o.order_id == orderId) : null;
    
    if (order) {
        displayOrderDetails(order);
    } else {
        // If order data not available, fetch it
        fetch(`get_order_details.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayOrderDetails(data.order);
                } else {
                    showNotification('Failed to load order details', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading order details:', error);
                showNotification('Error loading order details', 'error');
            });
    }
}

// Display order details in modal
function displayOrderDetails(order) {
    const modal = document.getElementById('order-details-modal');
    const content = document.getElementById('order-details-content');
    
    const orderDate = formatDate(order.order_date);
    const statusClass = order.status.toLowerCase();
    const totalItems = order.item_count || order.items.length;
    const totalAmount = parseFloat(order.total).toFixed(2);
    
    content.innerHTML = `
        <div class="order-details-header">
            <div class="order-header-info">
                <h4>Order #${order.order_id}</h4>
                <p class="order-date">Placed on ${orderDate}</p>
            </div>
            <span class="order-status-badge ${statusClass}">${order.status}</span>
        </div>
        
        <div class="order-progress-modal">
            <div class="progress-step ${getProgressClass(order.status, 'ordered')}">
                <div class="step-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="step-label">Ordered</div>
            </div>
            <div class="progress-line ${getProgressClass(order.status, 'processing')}"></div>
            <div class="progress-step ${getProgressClass(order.status, 'processing')}">
                <div class="step-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="step-label">Processing</div>
            </div>
            <div class="progress-line ${getProgressClass(order.status, 'shipped')}"></div>
            <div class="progress-step ${getProgressClass(order.status, 'shipped')}">
                <div class="step-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="step-label">Shipped</div>
            </div>
            <div class="progress-line ${getProgressClass(order.status, 'delivered')}"></div>
            <div class="progress-step ${getProgressClass(order.status, 'delivered')}">
                <div class="step-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="step-label">Delivered</div>
            </div>
        </div>
        
        <div class="order-summary">
            <div class="summary-item">
                <span class="summary-label">Order Total</span>
                <span class="summary-value">KSh ${totalAmount}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Items</span>
                <span class="summary-value">${totalItems}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Status</span>
                <span class="summary-value status-${statusClass}">${order.status}</span>
            </div>
        </div>
        
        <div class="order-items-section">
            <h4>Order Items</h4>
            <div class="order-items-list">
                ${order.items.map(item => `
                    <div class="order-item-row">
                        <div class="order-item-image">
                            <img src="${item.image || '../images/placeholder-product.jpg'}" alt="${item.name}" onerror="this.src='../images/placeholder-product.jpg'">
                        </div>
                        <div class="order-item-info">
                            <div class="order-item-name">${item.name}</div>
                            <div class="order-item-details">
                                <span class="order-item-price">KSh ${parseFloat(item.price).toFixed(2)} each</span>
                                <span class="order-item-subtotal">Subtotal: KSh ${(parseFloat(item.price) * item.quantity).toFixed(2)}</span>
                            </div>
                        </div>
                        <div class="order-item-quantity">
                            <span class="quantity-badge">Qty: ${item.quantity}</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
        
        <div class="order-total-section">
            <div class="order-total">
                <h4>Total Amount</h4>
                <div class="total-amount">KSh ${totalAmount}</div>
            </div>
        </div>
        
        <div class="order-actions">
            <button class="order-action-btn secondary" onclick="closeModal()">Close</button>
            ${order.status === 'pending' ? '<button class="order-action-btn primary" onclick="cancelOrder(' + order.order_id + ')">Cancel Order</button>' : ''}
        </div>
    `;
    
    modal.classList.add('active');
}

// Cancel order function
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        fetch('cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Order cancelled successfully', 'success');
                closeModal();
                // Reload order history
                loadOrderHistory();
            } else {
                showNotification(data.error || 'Failed to cancel order', 'error');
            }
        })
        .catch(error => {
            console.error('Error cancelling order:', error);
            showNotification('Error cancelling order', 'error');
        });
    }
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        fetch('Logout.php', {
            method: 'POST',
            credentials: 'include'
        })
        .then(() => {
            window.location.href = '../login_form.php?message=' + encodeURIComponent('You have been logged out successfully.');
        })
        .catch(error => {
            console.error('Logout error:', error);
            window.location.href = '../login_form.php?message=' + encodeURIComponent('Logged out.');
        });
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Get notification icon
function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Get notification color
function getNotificationColor(type) {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    return colors[type] || '#3b82f6';
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
