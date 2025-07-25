document.addEventListener('DOMContentLoaded', () => {
    // Load profile data
    loadProfileData();
    
    // Load order statistics and history
    loadOrderData();
    
    // Initialize navigation
    initializeNavigation();
    
    // Initialize mobile functionality
    initializeMobile();
});

function loadProfileData() {
    fetch('api/get_profile.php', {
        method: 'GET',
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Update profile information
        updateProfileInfo(data);
        
        // Update address status in stats
        updateAddressStatus(data);
        
    })
    .catch(error => {
        console.error('Error loading profile:', error.message);
        showErrorMessage('Failed to load profile information');
    });
}

function loadOrderData() {
    fetch('api/get_orders.php', {
        method: 'GET',
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: Failed to load orders`);
        }
        return response.json();
    })
    .then(orders => {
        updateOrderStatistics(orders);
        displayRecentOrders(orders);
        displayAllOrders(orders);
    })
    .catch(error => {
        console.error('Error loading orders:', error.message);
        showErrorMessage('Failed to load order information');
    });
}

function updateProfileInfo(data) {
    // Personal Information section
    const profileName = document.getElementById('profile-name');
    const profileEmail = document.getElementById('profile-email');
    const profilePhone = document.getElementById('profile-phone');
    const memberSince = document.getElementById('profile-member-since');
    
    if (profileName) profileName.textContent = data.fullName || 'Unknown';
    if (profileEmail) profileEmail.textContent = data.email || 'Not provided';
    if (profilePhone) profilePhone.textContent = data.phone || 'Not provided yet';
    if (memberSince && data.memberSince) {
        memberSince.textContent = new Date(data.memberSince).toLocaleDateString('en-US', { 
            month: 'long', 
            day: 'numeric',
            year: 'numeric' 
        });
    }

    // Delivery Address section
    const addressStreet = document.getElementById('address-street');
    const addressCity = document.getElementById('address-city');
    const addressPostal = document.getElementById('address-postal');
    
    if (addressStreet) addressStreet.textContent = data.address || 'Not provided yet';
    if (addressCity) addressCity.textContent = data.city || 'Not provided yet';
    if (addressPostal) addressPostal.textContent = data.postal_code || 'Not provided yet';
}

function updateAddressStatus(data) {
    const addressStatus = document.getElementById('address-status');
    if (addressStatus) {
        if (data.address && data.city) {
            addressStatus.textContent = 'Complete';
            addressStatus.style.color = '#10b981';
        } else if (data.address || data.city) {
            addressStatus.textContent = 'Partial';
            addressStatus.style.color = '#f59e0b';
        } else {
            addressStatus.textContent = 'Not Set';
            addressStatus.style.color = '#64748b';
        }
    }
}

function updateOrderStatistics(orders) {
    const totalOrdersEl = document.getElementById('total-orders');
    const totalSpentEl = document.getElementById('total-spent');
    
    if (!Array.isArray(orders)) {
        if (totalOrdersEl) totalOrdersEl.textContent = '0';
        if (totalSpentEl) totalSpentEl.textContent = 'KSh 0.00';
        return;
    }
    
    const totalOrders = orders.length;
    const totalSpent = orders.reduce((sum, order) => {
        return sum + (parseFloat(order.total) || 0);
    }, 0);
    
    if (totalOrdersEl) {
        animateCounter(totalOrdersEl, totalOrders);
    }
    
    if (totalSpentEl) {
        totalSpentEl.textContent = `KSh ${totalSpent.toLocaleString('en-KE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`;
    }
}

function displayRecentOrders(orders) {
    const recentOrdersList = document.getElementById('recent-orders-list');
    if (!recentOrdersList) return;
    
    if (!Array.isArray(orders) || orders.length === 0) {
        recentOrdersList.innerHTML = `
            <div class="no-orders">
                <p>No orders found.</p>
                <a href="/jowaki_electrical_srvs/Store.html" class="start-shopping-btn">Start Shopping</a>
            </div>
        `;
        return;
    }
    
    // Show only the 3 most recent orders
    const recentOrders = orders.slice(0, 3);
    recentOrdersList.innerHTML = '';
    
    recentOrders.forEach(order => {
        const orderElement = createOrderElement(order);
        recentOrdersList.appendChild(orderElement);
    });
}

function displayAllOrders(orders) {
    const allOrdersList = document.getElementById('all-orders-list');
    if (!allOrdersList) return;
    
    if (!Array.isArray(orders) || orders.length === 0) {
        allOrdersList.innerHTML = `
            <div class="no-orders">
                <p>No orders found.</p>
                <a href="/jowaki_electrical_srvs/Store.html" class="start-shopping-btn">Start Shopping</a>
            </div>
        `;
        return;
    }
    
    allOrdersList.innerHTML = '';
    
    orders.forEach(order => {
        const orderElement = createOrderElement(order);
        allOrdersList.appendChild(orderElement);
    });
}

function createOrderElement(order) {
    const orderElement = document.createElement('div');
    orderElement.className = 'order-item';
    
    const itemsList = order.items && Array.isArray(order.items) 
        ? order.items.map(item => item.name || item).join(', ')
        : 'No items listed';
    
    orderElement.innerHTML = `
        <div class="order-header">
            <span class="order-id">Order #${order.orderId || order.id || 'N/A'}</span>
            <span class="order-status ${(order.status || 'pending').toLowerCase()}">${order.status || 'Pending'}</span>
        </div>
        <div class="order-details">
            <div class="order-detail">
                <div class="order-detail-label">Date</div>
                <div class="order-detail-value">${formatOrderDate(order.date || order.created_at)}</div>
            </div>
            <div class="order-detail">
                <div class="order-detail-label">Total</div>
                <div class="order-detail-value">KSh ${parseFloat(order.total || 0).toLocaleString('en-KE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}</div>
            </div>
            <div class="order-detail">
                <div class="order-detail-label">Items</div>
                <div class="order-detail-value">${order.item_count || (order.items ? order.items.length : 0)} item(s)</div>
            </div>
        </div>
        <div class="order-items">${itemsList}</div>
        <button class="view-order-btn" onclick="viewOrderDetails('${order.orderId || order.id}')">View Details</button>
    `;
    
    return orderElement;
}

function formatOrderDate(dateString) {
    if (!dateString) return 'Unknown';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    } catch (error) {
        return 'Invalid Date';
    }
}

function viewOrderDetails(orderId) {
    alert(`Order details for #${orderId} will be implemented soon!`);
}

function animateCounter(element, targetValue) {
    const duration = 1000; // 1 second
    const steps = 30;
    const increment = targetValue / steps;
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= targetValue) {
            element.textContent = targetValue;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, duration / steps);
}

function initializeNavigation() {
    const navItems = document.querySelectorAll('.nav-item[data-section]');
    const contentSections = document.querySelectorAll('.content-section');

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all nav items
            navItems.forEach(nav => nav.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Hide all content sections
            contentSections.forEach(section => section.classList.remove('active'));
            
            // Show the selected section
            const targetSection = document.getElementById(this.dataset.section + '-section');
            if (targetSection) {
                targetSection.classList.add('active');
                
                // Close mobile sidebar after navigation
                if (window.innerWidth <= 1024) {
                    const sidebar = document.querySelector('.sidebar');
                    sidebar.classList.remove('open');
                }
            }
        });
    });
}

function initializeMobile() {
    // Mobile sidebar toggle
    window.toggleSidebar = function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
    };

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        
        if (window.innerWidth <= 1024 && 
            sidebar && mobileMenuBtn &&
            !sidebar.contains(e.target) && 
            !mobileMenuBtn.contains(e.target) && 
            sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
        }
    });
}

function showErrorMessage(message) {
    // Create a toast notification for errors
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ef4444;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        font-weight: 500;
        max-width: 300px;
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Remove toast after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}

// Enhanced button interactions
document.addEventListener('click', (e) => {
    const button = e.target.closest('.edit-btn, .view-order-btn, .start-shopping-btn');
    if (!button) return;

    // Add ripple effect
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;
    
    ripple.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        left: ${x}px;
        top: ${y}px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    `;
    
    button.style.position = 'relative';
    button.style.overflow = 'hidden';
    button.appendChild(ripple);
    
    setTimeout(() => {
        if (ripple.parentNode) {
            ripple.parentNode.removeChild(ripple);
        }
    }, 600);
});

// Add ripple animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    .order-status.pending {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
`;
document.head.appendChild(style);