import { sanitizeHTML } from './utils.js';
import { showNotification } from './notifications.js';

function processCustomerData(customer) {
    return {
        id: parseInt(customer.id) || 0,
        name: sanitizeHTML(customer.name || ''),
        email: sanitizeHTML(customer.email || ''),
        phone: sanitizeHTML(customer.phone || ''),
        address: sanitizeHTML(customer.address || ''),
        orders_count: parseInt(customer.orders_count) || 0,
        total_spent: parseFloat(customer.total_spent) || 0,
        loyalty_tier: customer.loyalty_tier || 'Bronze',
        last_order_date: customer.last_order_date || null
    };
}

export function renderCustomers(customersToRender, state) {
    const tbody = document.getElementById('customers-tbody');
    if (!tbody) {
        console.error('customers-tbody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(customersToRender) || customersToRender.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 20px; color: #666;">No customers found</td></tr>`;
        return;
    }
    
    customersToRender.forEach(customer => {
        const tr = document.createElement('tr');
        const loyaltyClass = getLoyaltyClass(customer.loyalty_tier);
        const lastOrderDate = customer.last_order_date ? new Date(customer.last_order_date).toLocaleDateString() : 'Never';
        
        tr.innerHTML = `
            <td>${customer.id}</td>
            <td>${customer.name}</td>
            <td>${customer.email}</td>
            <td>${customer.phone}</td>
            <td>${customer.orders_count}</td>
            <td>KSh ${customer.total_spent.toLocaleString()}</td>
            <td><span class="status-badge ${loyaltyClass}">${customer.loyalty_tier}</span></td>
            <td>${lastOrderDate}</td>
            <td>
                <button class="btn btn-secondary btn-sm" onclick="window.adminModules.viewCustomer(${customer.id})" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-warning btn-sm" onclick="window.adminModules.editCustomer(${customer.id})" title="Edit Customer">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="window.adminModules.deleteCustomer(${customer.id})" title="Delete Customer">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function getLoyaltyClass(tier) {
    switch(tier) {
        case 'Gold': return 'status-delivered';
        case 'Silver': return 'status-processing';
        case 'Bronze': return 'status-pending';
        default: return 'status-pending';
    }
}

export function fetchCustomers(state) {
    return fetch('../includes/get_customer_admin.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success !== false && Array.isArray(data)) {
                state.customers = data.map(customer => processCustomerData(customer));
                renderCustomers(state.customers, state);
            } else {
                throw new Error(data.error || 'Failed to fetch customers');
            }
        })
        .catch(error => {
            console.error('Customers fetch error:', error);
            showNotification(`Error fetching customers: ${error.message}`, 'error');
            renderCustomers([], state);
        });
}

// Enhanced CRUD operations
export function viewCustomer(customerId, state) {
    // Show loading state
    const modal = document.getElementById('customer-details-modal');
    const content = document.getElementById('customer-details-content');
    
    if (modal && content) {
        content.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                <p>Loading customer details...</p>
            </div>
        `;
        
        // Show modal
        modal.classList.add('show');
        
        // Fetch detailed customer information
        fetch(`../includes/customer_details.php?id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderCustomerDetails(data.data, content);
                } else {
                    throw new Error(data.error || 'Failed to load customer details');
                }
            })
            .catch(error => {
                console.error('Error loading customer details:', error);
                content.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem;"></i>
                        <p>Error loading customer details: ${error.message}</p>
                    </div>
                `;
            });
    }
}

function renderCustomerDetails(customer, content) {
    const orders = customer.orders || [];
    const preferences = customer.preferences || [];
    const metrics = customer.metrics || {};
    
    content.innerHTML = `
        <div class="customer-details-container">
            <!-- Customer Header -->
            <div class="customer-header" style="margin-bottom: 2rem;">
                <h4>${customer.name}</h4>
                <p><strong>Email:</strong> ${customer.email}</p>
                <p><strong>Phone:</strong> ${customer.phone}</p>
                <p><strong>Address:</strong> ${customer.address}, ${customer.city} ${customer.postal_code}</p>
            </div>
            
            <!-- Customer Metrics -->
            <div class="customer-metrics" style="margin-bottom: 2rem;">
                <h5>Customer Metrics</h5>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                        <h6>Total Orders</h6>
                        <p style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">${metrics.total_orders || 0}</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                        <h6>Total Spent</h6>
                        <p style="font-size: 1.5rem; font-weight: bold; color: var(--success-color);">KSh ${(metrics.total_spent || 0).toLocaleString()}</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                        <h6>Average Order Value</h6>
                        <p style="font-size: 1.5rem; font-weight: bold; color: var(--warning-color);">KSh ${(metrics.average_order_value || 0).toLocaleString()}</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                        <h6>Loyalty Tier</h6>
                        <p style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">${metrics.loyalty_tier || 'Bronze'}</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="recent-orders" style="margin-bottom: 2rem;">
                <h5>Recent Orders</h5>
                ${orders.length > 0 ? `
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${orders.slice(0, 5).map(order => `
                                <tr>
                                    <td>#${order.id}</td>
                                    <td>${new Date(order.order_date).toLocaleDateString()}</td>
                                    <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                                    <td>KSh ${order.total.toLocaleString()}</td>
                                    <td>${order.total_items} items</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                ` : '<p>No orders found</p>'}
            </div>
            
            <!-- Customer Preferences -->
            ${preferences.length > 0 ? `
                <div class="customer-preferences">
                    <h5>Top Product Preferences</h5>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Orders</th>
                                <th>Total Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${preferences.map(pref => `
                                <tr>
                                    <td>${pref.product_name}</td>
                                    <td>${pref.category}</td>
                                    <td>${pref.order_count}</td>
                                    <td>${pref.total_quantity}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : ''}
        </div>
    `;
}

export function editCustomer(customerId, state) {
    // For now, redirect to view customer with edit option
    viewCustomer(customerId, state);
}

export function deleteCustomer(customerId, state) {
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        // Implement delete functionality
        showNotification('Customer deletion not implemented yet', 'warning');
    }
}

export function initializeCustomers(state) {
    console.log('Initializing customers module...');
    
    // Load initial customers
    fetchCustomers(state);
    
    // Set up search and filter functionality
    const customerSearch = document.getElementById('customer-search');
    const customerLoyaltyFilter = document.getElementById('customer-loyalty-filter');
    
    if (customerSearch) {
        customerSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const filteredCustomers = state.customers.filter(customer => 
                customer.name.toLowerCase().includes(searchTerm) ||
                customer.email.toLowerCase().includes(searchTerm) ||
                customer.phone.toLowerCase().includes(searchTerm)
            );
            renderCustomers(filteredCustomers, state);
        });
    }
    
    if (customerLoyaltyFilter) {
        customerLoyaltyFilter.addEventListener('change', function() {
            const loyaltyFilter = this.value;
            const filteredCustomers = loyaltyFilter ? 
                state.customers.filter(customer => customer.loyalty_tier === loyaltyFilter) : 
                state.customers;
            renderCustomers(filteredCustomers, state);
        });
    }
    
    console.log('Customers module initialized');
}
