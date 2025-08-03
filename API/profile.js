// Profile Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
    console.log('Profile page loaded');
    
    // Initialize profile functionality
    initializeProfile();
    
    // Load order data
    loadOrderData();
});

function initializeProfile() {
    // Load profile data from server
    loadProfileData();
    
    // Initialize edit profile modal
    initializeEditModal();
}

function loadProfileData() {
    console.log('Loading profile data...');
    
    fetch('./get_profile.php', {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Profile data received:', data);
        updateProfileDisplay(data);
    })
    .catch(error => {
        console.error('Error loading profile:', error);
        showNotification('Failed to load profile data', 'error');
    });
}

function updateProfileDisplay(data) {
    // Update profile information with visual indicators
    const elements = {
        'profile-name': data.fullName || 'Not provided',
        'profile-email': data.email || 'Not provided',
        'profile-phone': data.phone || 'Not provided',
        'address-street': data.address || 'Not provided',
        'address-city': data.city || 'Not provided',
        'address-postal': data.postal_code || 'Not provided'
    };
    
    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            const value = elements[id];
            element.textContent = value;
            
            // Add visual indicators
            if (value === 'Not provided') {
                element.className = 'info-value incomplete';
                element.innerHTML += ' <i class="fas fa-exclamation-triangle" style="color: #dc2626; font-size: 0.8em;"></i>';
            } else {
                element.className = 'info-value complete';
                element.innerHTML += ' <i class="fas fa-check-circle" style="color: #059669; font-size: 0.8em;"></i>';
            }
        }
    });
    
    // Update completion message
    updateCompletionMessage(data);
}

function updateCompletionMessage(data) {
    const hasAddress = data.address && data.address !== 'Not provided';
    const hasCity = data.city && data.city !== 'Not provided';
    const hasPostal = data.postal_code && data.postal_code !== 'Not provided';
    const hasPhone = data.phone && data.phone !== 'Not provided';
    
    const completionDiv = document.getElementById('profile-completion-message');
    if (!completionDiv) return;
    
    const completeFields = [hasAddress, hasCity, hasPostal, hasPhone].filter(Boolean).length;
    const totalFields = 4;
    const completionPercentage = (completeFields / totalFields) * 100;
    
    if (completionPercentage === 100) {
        completionDiv.innerHTML = `
            <div class="completion-message success">
                <i class="fas fa-check-circle"></i>
                <strong>Profile Complete!</strong> All information is filled out.
            </div>
        `;
    } else {
        const missingFields = [];
        if (!hasAddress) missingFields.push('Address');
        if (!hasCity) missingFields.push('City');
        if (!hasPostal) missingFields.push('Postal Code');
        if (!hasPhone) missingFields.push('Phone');
        
        completionDiv.innerHTML = `
            <div class="completion-message warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Complete Your Profile:</strong> Missing ${missingFields.join(', ')}.
            </div>
        `;
    }
}

function loadOrderData() {
    console.log('Loading order data...');
    
    fetch('./get_orders.php', {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Order data received:', data);
        displayOrders(data);
    })
    .catch(error => {
        console.error('Error loading orders:', error);
        displayOrders([]);
    });
}

function displayOrders(orders) {
    const orderList = document.getElementById('order-list');
    if (!orderList) return;
    
    if (!Array.isArray(orders) || orders.length === 0) {
        orderList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <h3>No Orders Yet</h3>
                <p>Start shopping to see your orders here!</p>
            </div>
        `;
        return;
    }
    
    // Display recent orders (limit to 5)
    const recentOrders = orders.slice(0, 5);
    orderList.innerHTML = '';
    
    recentOrders.forEach(order => {
        const orderElement = createOrderElement(order);
        orderList.appendChild(orderElement);
    });
    
    if (orders.length > 5) {
        const moreOrdersDiv = document.createElement('div');
        moreOrdersDiv.innerHTML = `
            <div class="empty-state">
                <p>And ${orders.length - 5} more orders...</p>
            </div>
        `;
        orderList.appendChild(moreOrdersDiv);
    }
}

function createOrderElement(order) {
    const orderDiv = document.createElement('div');
    orderDiv.className = 'order-item';
    
    const statusClass = getStatusClass(order.status);
    const formattedDate = formatOrderDate(order.date || order.created_at);
    const formattedTotal = new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(order.total);
    
    // Get order items for display
    const items = order.items || [];
    const itemNames = items.slice(0, 3).join(', ');
    const hasMoreItems = items.length > 3;
    
    orderDiv.innerHTML = `
        <div class="order-header">
            <span class="order-id">${order.orderId || 'Order #' + order.id}</span>
            <span class="order-status ${statusClass}">${order.status}</span>
        </div>
        <div class="order-details">
            <p><strong>Date:</strong> ${formattedDate}</p>
            <p><strong>Total:</strong> ${formattedTotal}</p>
            <p><strong>Items:</strong> ${order.item_count || 0} item(s)</p>
            ${items.length > 0 ? `<p><strong>Products:</strong> ${itemNames}${hasMoreItems ? '...' : ''}</p>` : ''}
            ${order.delivery_method ? `<p><strong>Delivery:</strong> ${order.delivery_method}</p>` : ''}
            ${order.payment_method ? `<p><strong>Payment:</strong> ${order.payment_method}</p>` : ''}
        </div>
        <div class="order-actions">
            <button onclick="viewOrderDetails('${order.id}')" class="view-btn">View Details</button>
        </div>
    `;
    
    return orderDiv;
}

function getStatusClass(status) {
    const statusMap = {
        'pending': 'status-pending',
        'processing': 'status-processing',
        'delivered': 'status-delivered',
        'cancelled': 'status-cancelled'
    };
    return statusMap[status.toLowerCase()] || 'status-pending';
}

function formatOrderDate(dateString) {
    if (!dateString) return 'Unknown';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return dateString;
    }
}

function viewOrderDetails(orderId) {
    // For now, show a simple alert. This can be expanded to show a modal with full order details
    alert(`Order details for ${orderId} will be implemented soon!`);
}

function initializeEditModal() {
    const modal = document.getElementById('edit-profile-modal');
    const form = document.getElementById('edit-profile-form');
    
    if (!modal || !form) return;
    
    // Handle form submission
    form.addEventListener('submit', handleProfileUpdate);
    
    // Handle modal close
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeEditForm();
        }
    });
}

function editProfile() {
    const modal = document.getElementById('edit-profile-modal');
    const form = document.getElementById('edit-profile-form');
    
    if (!modal || !form) return;
    
    // Populate form with current data
    populateEditForm();
    
    // Show modal
    modal.classList.add('active');
}

function populateEditForm() {
    const form = document.getElementById('edit-profile-form');
    if (!form) return;
    
    // Get current profile data
    const currentData = {
        first_name: document.getElementById('profile-name')?.textContent.split(' ')[0] || '',
        last_name: document.getElementById('profile-name')?.textContent.split(' ').slice(1).join(' ') || '',
        email: document.getElementById('profile-email')?.textContent.replace('✅', '').replace('⚠️', '').trim() || '',
        phone: document.getElementById('profile-phone')?.textContent.replace('✅', '').replace('⚠️', '').trim() || '',
        address: document.getElementById('address-street')?.textContent.replace('✅', '').replace('⚠️', '').trim() || '',
        city: document.getElementById('address-city')?.textContent.replace('✅', '').replace('⚠️', '').trim() || '',
        postal_code: document.getElementById('address-postal')?.textContent.replace('✅', '').replace('⚠️', '').trim() || ''
    };
    
    // Populate form fields
    Object.keys(currentData).forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
            input.value = currentData[field];
        }
    });
}

function handleProfileUpdate(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const profileData = {};
    
    for (let [key, value] of formData.entries()) {
        profileData[key] = value.trim();
    }
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    fetch('./update_user_profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(profileData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
            closeEditForm();
            loadProfileData(); // Reload profile data
        } else {
            throw new Error(data.error || 'Failed to update profile');
        }
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        showNotification('Error updating profile: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function closeEditForm() {
    const modal = document.getElementById('edit-profile-modal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#059669' : type === 'error' ? '#dc2626' : '#667eea'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Export functions for global access
window.editProfile = editProfile;
window.closeEditForm = closeEditForm;
window.viewOrderDetails = viewOrderDetails;