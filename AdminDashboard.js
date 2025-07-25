// Ensure PapaParse is included via <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>
// Ensure Font Awesome is included via <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

document.addEventListener('DOMContentLoaded', () => {
    showSection('dashboard');
    fetchDashboardStats();
    fetchOrders();
    fetchProducts();
    fetchCustomers();
    fetchNotifications();
});

let orders = [];
let products = [];
let customers = [];
let notifications = [];

    function showNotification(message, type = 'info') {
        // Remove any existing notification to prevent stacking
        const existingNotification = document.querySelector('.notification-popup');
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement('div');
        notification.className = `notification-popup notification-${type}`; // Ensure this class is styled in CSS
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${sanitizeHTML(message)}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;

        document.body.appendChild(notification);

        // Automatically remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) { // Check if it still exists before trying to remove
                notification.remove();
            }
        }, 5000);

        console.log(`${type.toUpperCase()}: ${message}`);
    }
    

function toggleNotifications() {
    const notificationList = document.getElementById('notifications-list');
    if (notificationList) {
        notificationList.style.display = notificationList.style.display === 'none' ? 'block' : 'none';
    } else {
        console.error('Notifications list element not found!');
        showNotification('Notifications list not found', 'error');
    }
}

// Helper function to sanitize HTML to prevent XSS
function sanitizeHTML(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function showSection(sectionId) {
    const sections = document.querySelectorAll('.content-section');
    const navLinks = document.querySelectorAll('.nav-link');
    const pageTitle = document.getElementById('page-title');

    if (!pageTitle) {
        console.error('page-title element not found!');
        return;
    }

    sections.forEach(section => section.classList.remove('active'));
    const section = document.querySelector(`#${sectionId}`);
    if (section) {
        section.classList.add('active');
    } else {
        console.error(`Section #${sectionId} not found!`);
    }

    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-section') === sectionId) {
            link.classList.add('active');
        }
    });

    pageTitle.textContent = sectionId.charAt(0).toUpperCase() + sectionId.slice(1);
}

function fetchDashboardStats() {
    fetch('api/get_dashboard_stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success !== false) {
                updateDashboardElements({
                    'total-products': data.total_products || 0,
                    'pending-orders': data.pending_orders || 0,
                    'total-customers': data.total_customers || 0,
                    'monthly-revenue': data.monthly_revenue ? Number(data.monthly_revenue).toFixed(2) : '0.00',
                    'monthly-sales': data.monthly_sales || '0',
                    'orders-this-month': data.orders_this_month || 0,
                    'new-customers': data.new_customers || '0',
                    'service-completion': data.service_completion || '0%'
                });
            } else {
                updateDashboardStatsFromLocal();
            }
        })
        .catch(error => {
            console.error('Dashboard stats fetch error:', error);
            showNotification('Error fetching dashboard stats: ' + error.message, 'error');
            updateDashboardStatsFromLocal();
        });
}

function updateDashboardElements(data) {
    Object.keys(data).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = data[id];
        } else {
            console.warn(`Dashboard element #${id} not found!`);
        }
    });
}

function updateDashboardStatsFromLocal() {
    const pendingOrders = orders.filter(order => order.status === 'pending').length;
    const monthlyRevenue = orders.reduce((sum, order) => sum + (parseFloat(order.total_amount) || 0), 0);
    
    updateDashboardElements({
        'total-products': products.length,
        'pending-orders': pendingOrders,
        'total-customers': customers.length,
        'monthly-revenue': monthlyRevenue.toFixed(2),
        'monthly-sales': orders.length,
        'orders-this-month': orders.length,
        'new-customers': customers.length,
        'service-completion': '0%'
    });
}

function fetchOrders() {
    console.log('Fetching orders...');
    fetch('api/admin_orders.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response received');
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e, 'Raw text:', text.substring(0, 200));
                throw new Error('Invalid JSON response from server');
            }
            
            if (data.success !== false && Array.isArray(data.data)) {
                orders = data.data.map(order => processOrderData(order));
                console.log(`Processed ${orders.length} orders`);
                renderOrders(orders);
                updateDashboardStatsFromLocal();
            } else {
                const errorMsg = data.error || 'Failed to fetch orders';
                console.error('API Error:', errorMsg, data.debug);
                showNotification(errorMsg, 'error');
                renderOrders([]);
            }
        })
        .catch(error => {
            console.error('Orders fetch error:', error);
            showNotification('Network error fetching orders: ' + error.message, 'error');
            renderOrders([]);
        });
}

function processOrderData(order) {
    return {
        id: parseInt(order.id) || 0,
        customer_name: sanitizeHTML(order.customer_name || 'Unknown Customer'),
        customer_email: sanitizeHTML(order.customer_email || ''),
        customer_phone: sanitizeHTML(order.customer_phone || ''),
        customer_address: sanitizeHTML(order.customer_address || ''),
        order_date: order.order_date || new Date().toISOString(),
        items: Array.isArray(order.items) ? order.items.map(item => ({
            name: sanitizeHTML(item.name || ''),
            sku: sanitizeHTML(item.sku || ''),
            quantity: parseInt(item.quantity) || 0,
            price: parseFloat(item.price) || 0
        })) : [],
        subtotal: parseFloat(order.subtotal) || 0,
        shipping: parseFloat(order.shipping) || 0,
        tax: parseFloat(order.tax) || 0,
        total_amount: parseFloat(order.total_amount) || 0,
        payment_method: sanitizeHTML(order.payment_method || 'N/A'),
        shipping_method: sanitizeHTML(order.shipping_method || 'N/A'),
        status: order.status || 'pending'
    };
}

function renderOrders(ordersToRender) {
    const tbody = document.getElementById('orders-tbody');
    if (!tbody) {
        console.error('orders-tbody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(ordersToRender) || ordersToRender.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #666;">No orders found</td></tr>';
        return;
    }
    
    ordersToRender.forEach(order => {
        const tr = document.createElement('tr');
        const statusClass = `status-${order.status}`;
        const statusText = formatStatusText(order.status);
        
        tr.innerHTML = `
            <td>${order.id}</td>
            <td>${order.customer_name}</td>
            <td>${formatDate(order.order_date)}</td>
            <td>${order.items.length}</td>
            <td>KSh ${order.total_amount.toFixed(2)}</td>
            <td><span class="status-badge status-badge-large ${statusClass}">${statusText}</span></td>
            <td class="action-buttons">
                <button class="btn btn-primary" onclick="viewOrderDetails(${order.id})">
                    <i class="fas fa-eye"></i> View
                </button>
                ${renderOrderActionButtons(order)}
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    console.log(`Rendered ${ordersToRender.length} orders`);
}

function formatStatusText(status) {
    if (!status) return 'Unknown';
    return status === 'pending' ? 'Pending' : status.charAt(0).toUpperCase() + status.slice(1);
}

function formatDate(dateString) {
    try {
        return new Date(dateString).toLocaleDateString();
    } catch (e) {
        return 'Invalid Date';
    }
}

function renderOrderActionButtons(order) {
    if (order.status === 'pending') {
        return `
            <button class="btn btn-success" onclick="confirmOrder(${order.id}, this)">
                <i class="fas fa-check"></i> Confirm
            </button>
            <button class="btn btn-danger" onclick="cancelOrder(${order.id}, this)">
                <i class="fas fa-times"></i> Cancel
            </button>
        `;
    } else if (order.status !== 'cancelled') {
        const isDisabled = order.status === 'delivered' ? 'disabled' : '';
        return `
            <select class="status-select" onchange="updateOrderStatus(${order.id}, this.value, this)" ${isDisabled}>
                <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
            </select>
        `;
    }
    return '';
}

function viewOrderDetails(orderId) {
    const order = orders.find(o => o.id === orderId);
    if (!order) {
        showNotification('Order not found', 'error');
        return;
    }
    
    const modal = document.getElementById('orderModal');
    if (!modal) {
        console.error('orderModal element not found!');
        return;
    }
    
    const modalBody = modal.querySelector('.modal-body');
    if (!modalBody) {
        console.error('modal-body element not found in orderModal!');
        return;
    }
    
    modalBody.innerHTML = generateOrderModalContent(order);
    modal.style.display = 'block';
}

function generateOrderModalContent(order) {
    return `
        <div class="order-status-section">
            <h3><i class="fas fa-info-circle"></i> Order Status</h3>
            <div class="status-controls">
                <span class="status-badge-large status-${order.status}">
                    ${formatStatusText(order.status)}
                </span>
                ${generateModalActionButtons(order)}
            </div>
        </div>
        <div class="order-info-grid">
            <div class="info-card">
                <h4><i class="fas fa-user"></i> Customer Information</h4>
                <div class="info-content">
                    <p><strong>Name:</strong> ${order.customer_name}</p>
                    <p><strong>Email:</strong> ${order.customer_email}</p>
                    <p><strong>Phone:</strong> ${order.customer_phone}</p>
                    <p><strong>Address:</strong> ${order.customer_address}</p>
                </div>
            </div>
            <div class="info-card">
                <h4><i class="fas fa-shopping-cart"></i> Order Information</h4>
                <div class="info-content">
                    <p><strong>Order ID:</strong> ${order.id}</p>
                    <p><strong>Date:</strong> ${formatDate(order.order_date)}</p>
                    <p><strong>Payment Method:</strong> ${order.payment_method}</p>
                    <p><strong>Shipping Method:</strong> ${order.shipping_method}</p>
                </div>
            </div>
        </div>
        <div class="order-items-section">
            <h4><i class="fas fa-box"></i> Order Items</h4>
            <div class="items-container">
                ${generateOrderItemsHtml(order.items)}
            </div>
        </div>
        <div class="order-summary">
            <h4><i class="fas fa-dollar-sign"></i> Order Summary</h4>
            <div class="summary-table">
                <div class="summary-row"><span>Subtotal</span><span>KSh ${order.subtotal.toFixed(2)}</span></div>
                <div class="summary-row"><span>Shipping</span><span>KSh ${order.shipping.toFixed(2)}</span></div>
                <div class="summary-row"><span>Tax</span><span>KSh ${order.tax.toFixed(2)}</span></div>
                <div class="summary-row total-row"><span>Total</span><span>KSh ${order.total_amount.toFixed(2)}</span></div>
            </div>
        </div>
    `;
}

function generateModalActionButtons(order) {
    if (order.status === 'pending') {
        return `
            <button class="btn btn-success" onclick="confirmOrder(${order.id}, this, true)">
                <i class="fas fa-check"></i> Confirm
            </button>
            <button class="btn btn-danger" onclick="cancelOrder(${order.id}, this, true)">
                <i class="fas fa-times"></i> Cancel
            </button>
        `;
    } else if (order.status !== 'cancelled') {
        const isDisabled = order.status === 'delivered' ? 'disabled' : '';
        return `
            <select id="order-status-${order.id}" class="form-control status-select-large" 
                    onchange="updateOrderStatus(${order.id}, this.value, this)" ${isDisabled}>
                <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
            </select>
        `;
    }
    return '';
}

function generateOrderItemsHtml(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return '<div class="no-items">No items found</div>';
    }
    
    return items.map(item => `
        <div class="order-item">
            <div class="item-info">
                <h5>${item.name}</h5>
                <p>SKU: ${item.sku}</p>
                <p>Quantity: ${item.quantity}</p>
                <p>Price: KSh ${item.price.toFixed(2)}</p>
            </div>
            <div class="item-total">KSh ${(item.quantity * item.price).toFixed(2)}</div>
        </div>
    `).join('');
}

function confirmOrder(orderId, button, fromModal = false) {
    if (!button) return;
    
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Confirming...';
    
    fetch('api/confirm_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateOrderStatus(orderId, 'processing');
            showNotification(`Order #${orderId} confirmed successfully`, 'success');
            if (fromModal) {
                viewOrderDetails(orderId);
            }
        } else {
            throw new Error(data.error || 'Failed to confirm order');
        }
    })
    .catch(error => {
        console.error('Confirm order error:', error);
        showNotification('Error confirming order: ' + error.message, 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

function cancelOrder(orderId, button, fromModal = false) {
    if (!button || !confirm('Are you sure you want to cancel this order?')) return;
    
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
    
    fetch('api/cancel_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateOrderStatus(orderId, 'cancelled');
            showNotification(`Order #${orderId} cancelled successfully`, 'success');
            if (fromModal) {
                viewOrderDetails(orderId);
            }
        } else {
            throw new Error(data.error || 'Failed to cancel order');
        }
    })
    .catch(error => {
        console.error('Cancel order error:', error);
        showNotification('Error cancelling order: ' + error.message, 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

function updateOrderStatus(orderId, status, select = null) {
    if (select) {
        select.disabled = true;
    }
    
    fetch('api/update_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId, status })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update local order status
            const order = orders.find(o => o.id === orderId);
            if (order) {
                order.status = status;
                renderOrders(orders);
                updateDashboardStatsFromLocal();
            }
            
            showNotification(`Order #${orderId} status updated to ${status}`, 'success');
            
            if (select && select.classList.contains('status-select-large')) {
                viewOrderDetails(orderId);
            }
        } else {
            throw new Error(data.error || 'Failed to update order status');
        }
    })
    .catch(error => {
        console.error('Update order status error:', error);
        showNotification('Error updating order status: ' + error.message, 'error');
    })
    .finally(() => {
        if (select) {
            select.disabled = false;
        }
    });
}



function importProductsFromExcel() {
    const fileInput = createFileInput();
    document.body.appendChild(fileInput);
    fileInput.click();
    document.body.removeChild(fileInput);
}


function processCSVData(csvData) {
    console.log('Processing CSV data...');
    
    if (typeof Papa === 'undefined') {
        const error = 'PapaParse library not loaded! Please include the PapaParse script tag.';
        console.error(error);
        showNotification(error, 'error');
        return;
    }
    
    Papa.parse(csvData, {
        header: true,
        skipEmptyLines: true,
        transformHeader: header => header.trim().replace(/^"|"$/g, ''),
        transform: (value) => value.trim().replace(/^"|"$/g, ''),
        complete: async (results) => {
            console.log('CSV parsing completed');
            
            if (results.errors && results.errors.length > 0) {
                console.warn('CSV parsing errors:', results.errors);
            }
            
            const validProducts = results.data.filter((item, index) => {
                if (!item["ITEM NAME"] || !item["CATEGORY"]) {
                    console.warn(`Skipping invalid item at row ${index + 1}:`, item);
                    return false;
                }
                return true;
            });
            
            if (validProducts.length === 0) {
                showNotification('No valid products found in CSV file', 'error');
                return;
            }
            
            const processedProducts = validProducts.map(item => processProductFromCSV(item));
            
            let successCount = 0;
            const totalProducts = processedProducts.length;
            
            for (const product of processedProducts) {
                try {
                    const formData = createProductFormData(product);
                    const response = await fetch('api/add_product.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    const data = await response.json();
                    if (data.success) {
                        successCount++;
                    } else {
                        console.warn(`Failed to add product "${product.name}": ${data.error || 'Unknown error'}`);
                    }
                } catch (error) {
                    console.error(`Error adding product "${product.name}":`, error);
                }
            }
            
            showNotification(`Imported ${successCount} of ${totalProducts} products successfully`, 'success');
            fetchProducts();
            updateCategoryDropdownFromExcel(csvData);
        },
        error: (err) => {
            console.error('CSV parsing error:', err);
            showNotification('Failed to import products: Invalid CSV format.', 'error');
        }
    });
}



function createProductFormData(product) {
    const formData = new FormData();
    Object.keys(product).forEach(key => {
        if (key === 'featured' || key === 'active') {
            const formKey = key === 'featured' ? 'is_featured' : 'is_active';
            formData.append(formKey, product[key] ? '1' : '0');
        } else {
            formData.append(key, product[key]);
        }
    });
    return formData;
}

function updateCategoryDropdownFromExcel(csvData = null) {
    if (!csvData) {
        const fileInput = createFileInput();
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.name.toLowerCase().endsWith('.csv')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    processCategoryData(e.target.result);
                };
                reader.readAsText(file);
            } else {
                showNotification('Please select a CSV file for category update', 'error');
            }
        });
        document.body.appendChild(fileInput);
        fileInput.click();
        document.body.removeChild(fileInput);
        return;
    }
    processCategoryData(csvData);
}

function processCategoryData(csvData) {
    if (typeof Papa === 'undefined') {
        const error = 'PapaParse library not loaded!';
        console.error(error);
        showNotification(error, 'error');
        return;
    }
    
    Papa.parse(csvData, {
        header: true,
        skipEmptyLines: true,
        transformHeader: header => header.trim().replace(/^"|"$/g, ''),
        transform: (value) => value.trim().replace(/^"|"$/g, ''),
        complete: (results) => {
            const categories = results.data
                .map(item => item["CATEGORY"]?.toUpperCase())
                .filter(Boolean);
            
            const uniqueCategories = [...new Set(categories)].sort();
            
            const categorySelect = document.getElementById('product-category');
            if (!categorySelect) {
                console.warn('Category select element not found');
                showNotification('Category select element not found', 'error');
                return;
            }
            
            categorySelect.innerHTML = `<option value="">Select Category</option>` +
                uniqueCategories.map(category => 
                    `<option value="${sanitizeHTML(category)}">${sanitizeHTML(category)}</option>`
                ).join('');
            
            showNotification('Categories updated successfully', 'success');
        },
        error: (err) => {
            console.error('Category processing error:', err);
            showNotification('Failed to load categories from CSV file.', 'error');
        }
    });
}

function triggerCategoryUpdate() {
    updateCategoryDropdownFromExcel();
}

function fetchProducts() {
    fetch('api/get_products_admin.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success !== false && Array.isArray(data)) {
                products = data.map(product => processProductData(product));
                renderProducts(products);
                updateDashboardStatsFromLocal();
            } else {
                throw new Error(data.error || 'Failed to fetch products');
            }
        })
        .catch(error => {
            console.error('Products fetch error:', error);
            showNotification('Error fetching products: ' + error.message, 'error');
            renderProducts([]);
        });
}

function processProductData(product) {
    return {
        id: parseInt(product.id) || 0,
        name: sanitizeHTML(product.name || ''),
        category: sanitizeHTML(product.category || ''),
        price: parseFloat(product.price) || 0,
        stock: parseInt(product.stock) || 0,
        low_stock_alert: parseInt(product.low_stock_alert) || 0,
        image: sanitizeHTML(product.image || ''),
        brand: sanitizeHTML(product.brand || ''),
        discount_price: parseFloat(product.discount_price) || 0,
        description: sanitizeHTML(product.description || ''),
        specs: sanitizeHTML(product.specifications || ''),
        weight: parseFloat(product.weight_kg) || 0,
        warranty: parseInt(product.warranty_months) || 0,
        featured: product.is_featured === '1' || product.is_featured === 1,
        active: product.is_active === '1' || product.is_active === 1
    };
}

function renderProducts(productsToRender) {
    const tbody = document.getElementById('products-tbody');
    if (!tbody) {
        console.error('products-tbody element not found!');
        showNotification('Products table not found', 'error');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(productsToRender) || productsToRender.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #666;">No products found</td></tr>';
        return;
    }
    
    productsToRender.forEach(product => {
        const tr = document.createElement('tr');
        const stockStatus = product.stock <= product.low_stock_alert ? 'Low Stock' : 'In Stock';
        const stockClass = product.stock <= product.low_stock_alert ? 'status-out-of-stock' : 'status-completed';
        
        tr.innerHTML = `
            <td>${product.id}</td>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>KSh ${product.price.toFixed(2)}</td>
            <td>${product.stock}</td>
            <td><span class="status-badge ${stockClass}">${stockStatus}</span></td>
            <td class="action-buttons">
                <button class="btn btn-primary" onclick="editProduct(${product.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger" onclick="deleteProduct(${product.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

    function fetchCustomers() {
       fetch('api/get_customer_admin.php') // Corrected path
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success !== false && Array.isArray(data)) {
                    customers = data.map(customer => processCustomerData(customer));
                    renderCustomers(customers);
                    updateDashboardStatsFromLocal();
                } else {
                    throw new Error(data.error || 'Failed to fetch customers');
                }
            })
            .catch(error => {
                console.error('Customers fetch error:', error);
                showNotification('Error fetching customers: ' + error.message, 'error');
                renderCustomers([]);
            });
    }
    

function processCustomerData(customer) {
    return {
        id: parseInt(customer.id) || 0,
        name: sanitizeHTML(customer.name || ''),
        email: sanitizeHTML(customer.email || ''),
        phone: sanitizeHTML(customer.phone || ''),
        location: sanitizeHTML(customer.location || ''),
        orders: parseInt(customer.orders) || 0,
        total_spent: parseFloat(customer.total_spent) || 0
    };
}

function renderCustomers(customersToRender) {
    const tbody = document.getElementById('customers-tbody');
    if (!tbody) {
        console.error('customers-tbody element not found!');
        showNotification('Customers table not found', 'error');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(customersToRender) || customersToRender.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #666;">No customers found</td></tr>';
        return;
    }
    
    customersToRender.forEach(customer => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${customer.id}</td>
            <td>${customer.name}</td>
            <td>${customer.email}</td>
            <td>${customer.phone}</td>
            <td>${customer.location}</td>
            <td>${customer.orders}</td>
            <td>KSh ${customer.total_spent.toFixed(2)}</td>
            <td class="action-buttons">
                <button class="btn btn-primary" onclick="viewCustomerDetails(${customer.id})">
                    <i class="fas fa-eye"></i> View
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function fetchNotifications() {
    fetch('api/get_notifications_admin.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success !== false && Array.isArray(data)) {
                notifications = data.map(notification => processNotificationData(notification));
                renderNotifications();
            } else {
                throw new Error(data.error || 'Failed to fetch notifications');
            }
        })
        .catch(error => {
            console.error('Notifications fetch error:', error);
            showNotification('Error fetching notifications: ' + error.message, 'error');
            renderNotifications();
        });
}

function processNotificationData(notification) {
    return {
        id: parseInt(notification.id) || 0,
        message: sanitizeHTML(notification.message || ''),
        type: notification.type || 'info'
    };
}

function renderNotifications() {
    const notificationList = document.getElementById('notifications-list');
    const notificationCount = document.getElementById('notification-count');
    
    if (!notificationList || !notificationCount) {
        console.error('Notification elements not found!');
        return;
    }
    
    notificationList.innerHTML = '';
    notificationCount.textContent = notifications.length;
    
    if (notifications.length === 0) {
        notificationList.innerHTML = '<p>No notifications</p>';
        return;
    }
    
    notifications.forEach(notification => {
        const div = document.createElement('div');
        div.className = `notification ${notification.type}`;
        div.textContent = notification.message;
        notificationList.appendChild(div);
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with ID ${modalId} not found!`);
        showNotification(`Modal ${modalId} not found`, 'error');
        return;
    }
    modal.style.display = 'block';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with ID ${modalId} not found!`);
        showNotification(`Modal ${modalId} not found`, 'error');
        return;
    }
    
    modal.style.display = 'none';
    
    if (modalId === 'productModal') {
        resetProductModal();
    }
}

function resetProductModal() {
    const productForm = document.getElementById('productForm');
    const imagePreview = document.getElementById('image-preview');
    const modalTitle = document.getElementById('product-modal-title');
    
    if (productForm) {
        productForm.reset();
        const productIdField = document.getElementById('product-id');
        if (productIdField) {
            productIdField.value = '';
        }
    }
    
    if (imagePreview) {
        imagePreview.innerHTML = '';
    }
    
    if (modalTitle) {
        modalTitle.textContent = 'Add Product';
    }
}

function saveProduct(event) {
    event.preventDefault();
    
    const productForm = document.getElementById('productForm');
    if (!productForm) {
        console.error('productForm element not found!');
        showNotification('Product form not found', 'error');
        return;
    }
    
    const formData = new FormData(productForm);
    const productIdField = document.getElementById('product-id');
    const productId = productIdField ? productIdField.value : '';
    
    const url = productId ? 'api/update_product.php' : 'api/add_product.php';
    const action = productId ? 'updated' : 'added';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification(`Product ${action} successfully`, 'success');
            fetchProducts();
            closeModal('productModal');
        } else {
            throw new Error(data.error || `Failed to ${action.slice(0, -1)} product`);
        }
    })
    .catch(error => {
        console.error(`Product ${action.slice(0, -1)} error:`, error);
        showNotification(`Error ${action.slice(0, -1)}ing product: ${error.message}`, 'error');
    });
}

function editProduct(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) {
        showNotification('Product not found', 'error');
        return;
    }
    
    const fieldMappings = {
        'product-id': product.id,
        'product-name': product.name,
        'product-category': product.category,
        'product-brand': product.brand,
        'product-price': product.price,
        'product-discount': product.discount_price,
        'product-stock': product.stock,
        'product-low-stock': product.low_stock_alert,
        'product-description': product.description,
        'product-specs': product.specs,
        'product-weight': product.weight,
        'product-warranty': product.warranty,
        'product-featured': product.featured,
        'product-active': product.active
    };
    
    Object.keys(fieldMappings).forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            if (fieldId === 'product-featured' || fieldId === 'product-active') {
                element.checked = fieldMappings[fieldId];
            } else {
                element.value = fieldMappings[fieldId];
            }
        } else {
            console.warn(`Form field #${fieldId} not found!`);
        }
    });
    
    const modalTitle = document.getElementById('product-modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Edit Product';
    }
    
    openModal('productModal');
}

function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }
    
    fetch('api/delete_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: productId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            products = products.filter(p => p.id !== productId);
            renderProducts(products);
            showNotification('Product deleted successfully', 'success');
            updateDashboardStatsFromLocal();
        } else {
            throw new Error(data.error || 'Failed to delete product');
        }
    })
    .catch(error => {
        console.error('Delete product error:', error);
        showNotification('Error deleting product: ' + error.message, 'error');
    });
}

function searchProducts(query) {
    if (!query || typeof query !== 'string') {
        renderProducts(products);
        return;
    }
    
    const searchTerm = query.toLowerCase().trim();
    const filteredProducts = products.filter(product =>
        product.name.toLowerCase().includes(searchTerm) ||
        product.category.toLowerCase().includes(searchTerm) ||
        product.brand.toLowerCase().includes(searchTerm)
    );
    
    renderProducts(filteredProducts);
}

function filterProducts(category) {
    if (!category) {
        renderProducts(products);
        return;
    }
    
    const filteredProducts = products.filter(product => 
        product.category.toLowerCase() === category.toLowerCase()
    );
    
    renderProducts(filteredProducts);
}

function searchOrders(query) {
    if (!query || typeof query !== 'string') {
        renderOrders(orders);
        return;
    }
    
    const searchTerm = query.toLowerCase().trim();
    const filteredOrders = orders.filter(order =>
        order.id.toString().includes(searchTerm) ||
        order.customer_name.toLowerCase().includes(searchTerm) ||
        order.customer_email.toLowerCase().includes(searchTerm)
    );
    
    renderOrders(filteredOrders);
}

function filterOrders(status) {
    if (!status) {
        renderOrders(orders);
        return;
    }
    
    const filteredOrders = orders.filter(order => 
        order.status.toLowerCase() === status.toLowerCase()
    );
    
    renderOrders(filteredOrders);
}

function searchCustomers(query) {
    if (!query || typeof query !== 'string') {
        renderCustomers(customers);
        return;
    }
    
    const searchTerm = query.toLowerCase().trim();
    const filteredCustomers = customers.filter(customer =>
        customer.name.toLowerCase().includes(searchTerm) ||
        customer.email.toLowerCase().includes(searchTerm) ||
        customer.phone.includes(searchTerm)
    );
    
    renderCustomers(filteredCustomers);
}

function viewCustomerDetails(customerId) {
    const customer = customers.find(c => c.id === customerId);
    if (!customer) {
        showNotification('Customer not found', 'error');
        return;
    }
    
    const modal = document.getElementById('customerModal');
    if (!modal) {
        console.error('customerModal element not found!');
        showNotification('Customer modal not found', 'error');
        return;
    }
    
    const modalBody = modal.querySelector('.modal-body');
    if (!modalBody) {
        console.error('modal-body element not found in customerModal!');
        return;
    }
    
    modalBody.innerHTML = `
        <div class="customer-info">
            <h3><i class="fas fa-user"></i> Customer Details</h3>
            <div class="info-content">
                <p><strong>Name:</strong> ${customer.name}</p>
                <p><strong>Email:</strong> ${customer.email}</p>
                <p><strong>Phone:</strong> ${customer.phone}</p>
                <p><strong>Location:</strong> ${customer.location}</p>
                <p><strong>Orders:</strong> ${customer.orders}</p>
                <p><strong>Total Spent:</strong> KSh ${customer.total_spent.toFixed(2)}</p>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
}

// Event listeners for modal close buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('close') || e.target.classList.contains('modal-close')) {
        const modal = e.target.closest('.modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // Close modal when clicking outside
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

// Keyboard event listener for ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal[style*="block"]');
        openModals.forEach(modal => {
            modal.style.display = 'none';
        });
    }
});