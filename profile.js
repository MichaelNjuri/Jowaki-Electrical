document.addEventListener('DOMContentLoaded', () => {
  fetch('get_profile.php', {
    method: 'GET',
    credentials: 'include' // This line ensures session cookie is sent
})


        .then(response => {
            if (!response.ok) throw new Error('Not logged in');
            return response.json();
        })
        .then(data => {
            // Update contact info in header
            document.getElementById('contact-email').textContent = data.email;
            document.getElementById('contact-phone').textContent = data.phone;

            // Personal Information section
            document.getElementById('profile-name').textContent = data.fullName;
            document.getElementById('profile-email').textContent = data.email;
            document.getElementById('profile-phone').textContent = data.phone;
            document.getElementById('profile-location').textContent = ''; // blank since location is not returned
            document.getElementById('profile-member-since').textContent = new Date(data.memberSince).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        })
        .catch(error => {
            console.error('Error loading profile:', error);
            window.location.href = 'login.html?error=' + encodeURIComponent('Please log in to access your profile.');
        });

    // Fetch order history
    fetch('get_orders.php')
        .then(response => {
            if (!response.ok) throw new Error('Failed to load orders');
            return response.json();
        })
        .then(orders => {
            const orderList = document.getElementById('order-list');
            if (!Array.isArray(orders) || orders.length === 0) {
                orderList.innerHTML = '<div class="no-orders">No orders found</div>';
                return;
            }

            orders.forEach(order => {
                const orderElement = document.createElement('div');
                orderElement.className = 'order-item';
                orderElement.innerHTML = `
                    <div class="order-header">
                        <span class="order-id">Order #${order.orderId}</span>
                        <span class="order-status ${order.status.toLowerCase()}">${order.status}</span>
                    </div>
                    <div class="order-details">
                        <div class="order-detail">
                            <div class="order-detail-label">Date</div>
                            <div class="order-detail-value">${new Date(order.date).toLocaleDateString('en-US')}</div>
                        </div>
                        <div class="order-detail">
                            <div class="order-detail-label">Total</div>
                            <div class="order-detail-value">KSh ${parseFloat(order.total).toLocaleString()}</div>
                        </div>
                        <div class="order-detail">
                            <div class="order-detail-label">Items</div>
                            <div class="order-detail-value">${order.items.map(item => item.name).join(', ')}</div>
                        </div>
                    </div>
                    <button class="view-order-btn">View Details</button>
                `;
                orderList.appendChild(orderElement);
            });
        })
        .catch(error => {
            console.error('Error loading orders:', error);
        });

    // Ripple effect for buttons
    document.addEventListener('click', (e) => {
        const button = e.target.closest('.edit-btn, .view-order-btn');
        if (!button) return;

        e.preventDefault();

        const ripple = document.createElement('span');
        ripple.style.position = 'absolute';
        ripple.style.borderRadius = '50%';
        ripple.style.background = 'rgba(255, 255, 255, 0.6)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple 0.6s linear';
        ripple.style.left = (e.clientX - button.offsetLeft) + 'px';
        ripple.style.top = (e.clientY - button.offsetTop) + 'px';

        button.style.position = 'relative';
        button.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);

        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);
    });

    // Add ripple animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Order status pulsing effect for "pending"
    setInterval(() => {
        const processingOrders = document.querySelectorAll('.order-status.pending');
        processingOrders.forEach(status => {
            status.style.opacity = status.style.opacity === '0.5' ? '1' : '0.5';
        });
    }, 1000);
});
