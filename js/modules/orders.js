import { sanitizeHTML, formatStatusText } from './utils.js';
import { showNotification } from './notifications.js';

// Initialize global functions immediately
if (typeof window !== 'undefined') {
    window.adminModules = window.adminModules || {};
}

// Placeholder functions that will be replaced when the actual functions are defined
window.adminModules.updateOrderStatus = function(orderId, state) {
    console.warn('updateOrderStatus not yet initialized');
};
window.adminModules.viewOrder = function(orderId, state) {
    console.warn('viewOrder not yet initialized');
};
window.adminModules.editOrder = function(orderId, state) {
    console.warn('editOrder not yet initialized');
};
window.adminModules.deleteOrder = function(orderId, state) {
    console.warn('deleteOrder not yet initialized');
};
window.adminModules.confirmOrder = function(orderId, state) {
    console.warn('confirmOrder not yet initialized');
};
window.adminModules.cancelOrder = function(orderId, state) {
    console.warn('cancelOrder not yet initialized');
};
window.adminModules.shipOrder = function(orderId, state) {
    console.warn('shipOrder not yet initialized');
};
window.adminModules.deliverOrder = function(orderId, state) {
    console.warn('deliverOrder not yet initialized');
};

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

export function renderOrders(ordersToRender, state) {
    const tbody = document.getElementById('orders-tbody');
    if (!tbody) {
        console.error('orders-tbody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(ordersToRender) || ordersToRender.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 20px; color: #666;">No orders found</td></tr>`;
        return;
    }
    
    ordersToRender.forEach(order => {
        const tr = document.createElement('tr');
        const statusClass = `status-${order.status}`;
        const statusText = formatStatusText(order.status);
        const orderDate = new Date(order.order_date).toLocaleDateString();
        const itemCount = order.items ? order.items.length : 0;
        
        // Generate action buttons based on order status
        const actionButtons = generateOrderActionButtons(order, state);
        
        tr.innerHTML = `
            <td>#${order.id}</td>
            <td>${order.customer_name}</td>
            <td>${orderDate}</td>
            <td>KSh ${order.total_amount.toLocaleString()}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>${order.payment_method}</td>
            <td>${itemCount} items</td>
            <td>
                ${actionButtons}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function generateOrderActionButtons(order, state) {
    const buttons = [];
    
    // Always show view button
    buttons.push(`
        <button class="btn btn-secondary btn-sm" onclick="window.adminModules.viewOrder(${order.id})" title="View Details">
            <i class="fas fa-eye"></i>
        </button>
    `);
    
    // Status-specific action buttons
    if (order.status === 'pending') {
        buttons.push(`
            <button class="btn btn-success btn-sm" onclick="window.adminModules.confirmOrder(${order.id}, window.adminState)" title="Confirm Order">
                <i class="fas fa-check"></i>
            </button>
            <button class="btn btn-danger btn-sm" onclick="window.adminModules.cancelOrder(${order.id}, window.adminState)" title="Cancel Order">
                <i class="fas fa-times"></i>
            </button>
        `);
    } else if (order.status === 'confirmed' || order.status === 'processing') {
        buttons.push(`
            <button class="btn btn-info btn-sm" onclick="window.adminModules.shipOrder(${order.id}, window.adminState)" title="Mark as Shipped">
                <i class="fas fa-shipping-fast"></i>
            </button>
        `);
    } else if (order.status === 'shipped') {
        buttons.push(`
            <button class="btn btn-success btn-sm" onclick="window.adminModules.deliverOrder(${order.id}, window.adminState)" title="Mark as Delivered">
                <i class="fas fa-box-check"></i>
            </button>
        `);
    }
    
    // Always show status update button
    buttons.push(`
        <button class="btn btn-warning btn-sm" onclick="window.adminModules.updateOrderStatus(${order.id}, window.adminState)" title="Update Status">
            <i class="fas fa-edit"></i>
        </button>
    `);
    
    return buttons.join('');
}

export function fetchOrders(state) {
    return fetch('./API/get_orders_fixed.php', {
        credentials: 'include'
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success !== false && Array.isArray(data.data)) {
                // Safely update state if it exists
                if (state && typeof state === 'object') {
                    state.orders = data.data.map(order => processOrderData(order));
                }
                renderOrders(data.data.map(order => processOrderData(order)), state);
            } else {
                throw new Error(data.error || 'Failed to fetch orders');
            }
        })
        .catch(error => {
            console.error('Orders fetch error:', error);
            showNotification(`Error fetching orders: ${error.message}`, 'error');
            renderOrders([], state);
        });
}

// Enhanced CRUD operations
export function viewOrder(orderId, state) {
    // Show loading state
    const modal = document.getElementById('order-details-modal');
    const content = document.getElementById('order-details-content');
    
    if (modal && content) {
        content.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                <p>Loading order details...</p>
            </div>
        `;
        
        // Show modal
        modal.classList.add('show');
        
        // Fetch detailed order information
        fetch(`./API/order_details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderOrderDetails(data.data, content, state);
                } else {
                    throw new Error(data.error || 'Failed to load order details');
                }
            })
            .catch(error => {
                console.error('Error loading order details:', error);
                content.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem;"></i>
                        <p>Error loading order details: ${error.message}</p>
                    </div>
                `;
            });
    }
}

function renderOrderDetails(order, content, state) {
    const statusHistory = order.status_history || [];
    const items = order.items || [];
    
    // Generate action buttons based on order status
    const actionButtons = generateOrderDetailActionButtons(order, state);
    
    content.innerHTML = `
        <div class="order-details-container">
            <!-- Order Header -->
            <div class="order-header" style="margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4>Order #${order.order_id}</h4>
                        <p><strong>Date:</strong> ${new Date(order.order_date).toLocaleDateString()}</p>
                        <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${formatStatusText(order.status)}</span></p>
                    </div>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        ${actionButtons}
                    </div>
                </div>
            </div>
            
            <!-- Customer Information -->
            <div class="customer-info" style="margin-bottom: 2rem;">
                <h5>Customer Information</h5>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <p><strong>Name:</strong> ${order.customer_info.name}</p>
                        <p><strong>Email:</strong> ${order.customer_info.email}</p>
                        <p><strong>Phone:</strong> ${order.customer_info.phone}</p>
                    </div>
                    <div>
                        <p><strong>Address:</strong> ${order.customer_info.address}</p>
                        <p><strong>City:</strong> ${order.customer_info.city}</p>
                        <p><strong>Postal Code:</strong> ${order.customer_info.postal_code}</p>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="order-items" style="margin-bottom: 2rem;">
                <h5>Order Items</h5>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(item => `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${item.sku}</td>
                                <td>${item.quantity}</td>
                                <td>KSh ${item.price.toLocaleString()}</td>
                                <td>KSh ${item.total.toLocaleString()}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary" style="margin-bottom: 2rem;">
                <h5>Order Summary</h5>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <p><strong>Subtotal:</strong> KSh ${order.subtotal.toLocaleString()}</p>
                        <p><strong>Tax (16%):</strong> KSh ${order.tax.toLocaleString()}</p>
                        <p><strong>Delivery Fee:</strong> KSh ${order.delivery_fee.toLocaleString()}</p>
                        <p><strong>Total:</strong> KSh ${order.total.toLocaleString()}</p>
                    </div>
                    <div>
                        <p><strong>Payment Method:</strong> ${order.payment_method}</p>
                        <p><strong>Delivery Method:</strong> ${order.delivery_method}</p>
                        <p><strong>Delivery Address:</strong> ${order.delivery_address}</p>
                    </div>
                </div>
            </div>
            
            <!-- Status History -->
            ${statusHistory.length > 0 ? `
                <div class="status-history">
                    <h5>Status History</h5>
                    <div style="max-height: 200px; overflow-y: auto;">
                        ${statusHistory.map(status => `
                            <div style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                                <p><strong>${new Date(status.created_at).toLocaleString()}</strong></p>
                                <p>Status: <span class="status-badge status-${status.status}">${formatStatusText(status.status)}</span></p>
                                ${status.notes ? `<p>Notes: ${status.notes}</p>` : ''}
                                <p>Updated by: ${status.updated_by}</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
}

function generateOrderDetailActionButtons(order, state) {
    const buttons = [];
    
    // Status-specific action buttons
    if (order.status === 'pending') {
        buttons.push(`
            <button class="btn btn-success btn-sm" onclick="window.adminModules.confirmOrder(${order.order_id}, window.adminState)" title="Confirm Order">
                <i class="fas fa-check"></i> Confirm
            </button>
            <button class="btn btn-danger btn-sm" onclick="window.adminModules.cancelOrder(${order.order_id}, window.adminState)" title="Cancel Order">
                <i class="fas fa-times"></i> Cancel
            </button>
        `);
    } else if (order.status === 'confirmed' || order.status === 'processing') {
        buttons.push(`
            <button class="btn btn-info btn-sm" onclick="window.adminModules.shipOrder(${order.order_id}, window.adminState)" title="Mark as Shipped">
                <i class="fas fa-shipping-fast"></i> Ship
            </button>
        `);
    } else if (order.status === 'shipped') {
        buttons.push(`
            <button class="btn btn-success btn-sm" onclick="window.adminModules.deliverOrder(${order.order_id}, window.adminState)" title="Mark as Delivered">
                <i class="fas fa-box-check"></i> Deliver
            </button>
        `);
    }
    
    // Always show status update button
    buttons.push(`
        <button class="btn btn-warning btn-sm" onclick="window.adminModules.updateOrderStatus(${order.order_id}, window.adminState)" title="Update Status">
            <i class="fas fa-edit"></i> Update Status
        </button>
    `);
    
    return buttons.join('');
}

export function updateOrderStatus(orderId, newStatus, state) {
    // Create a modal for status update
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Update Order Status</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Order ID: #${orderId}</label>
                </div>
                <div class="form-group">
                    <label for="status-select">New Status:</label>
                    <select id="status-select" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status-notes">Notes (Optional):</label>
                    <textarea id="status-notes" class="form-control" rows="3" placeholder="Enter any additional notes..."></textarea>
                </div>
                <div class="form-group">
                    <label for="tracking-number">Tracking Number (Optional):</label>
                    <input type="text" id="tracking-number" class="form-control" placeholder="Enter tracking number...">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                <button class="btn btn-primary" onclick="submitStatusUpdate(${orderId}, this.closest('.modal-overlay'))">Update Status</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Store state in global scope for the modal function
    window.currentOrderState = state;
    
    // Add global function for submission
    window.submitStatusUpdate = async (orderId, modal) => {
        const statusSelect = modal.querySelector('#status-select');
        const notesTextarea = modal.querySelector('#status-notes');
        const trackingInput = modal.querySelector('#tracking-number');
        
        const newStatus = statusSelect.value;
        const notes = notesTextarea.value;
        const tracking = trackingInput.value;
        
        // Combine notes and tracking
        let combinedNotes = notes;
        if (tracking) {
            combinedNotes = combinedNotes ? `${notes} | Tracking: ${tracking}` : `Tracking: ${tracking}`;
        }
        
        try {
            // Show loading state
            const submitBtn = modal.querySelector('.btn-primary');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
            
            const response = await fetch('./API/update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    order_id: parseInt(orderId),
                    status: newStatus,
                    notes: combinedNotes,
                    updated_by: 'admin'
                })
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const responseText = await response.text();
            console.log('Response text:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Server returned invalid JSON response');
            }
            
            if (data.success) {
                showNotification(`Order #${orderId} status updated to ${newStatus}!`, 'success');
                modal.remove();
                await fetchOrders(window.currentOrderState);
            } else {
                throw new Error(data.error || 'Failed to update order status');
            }
            
        } catch (error) {
            console.error('Error updating order status:', error);
            showNotification(`Error updating order status: ${error.message}`, 'error');
            
            // Reset button
            const submitBtn = modal.querySelector('.btn-primary');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update Status';
        }
    };
}

export function confirmOrder(orderId, state) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Confirm Order</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to confirm Order #${orderId}?</p>
                <div class="form-group">
                    <label for="confirm-notes">Notes (Optional):</label>
                    <textarea id="confirm-notes" class="form-control" rows="3" placeholder="Enter any notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                <button class="btn btn-success" onclick="submitOrderConfirmation(${orderId}, this.closest('.modal-overlay'))">Confirm Order</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Store state in global scope for the modal function
    window.currentOrderState = state;
    
    window.submitOrderConfirmation = async (orderId, modal) => {
        const notes = modal.querySelector('#confirm-notes').value;
        
        try {
            const submitBtn = modal.querySelector('.btn-success');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Confirming...';
            
            const response = await fetch('./API/confirm_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    notes: notes
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(`Order #${orderId} confirmed successfully!`, 'success');
                modal.remove();
                await fetchOrders(window.currentOrderState);
            } else {
                throw new Error(data.error || 'Failed to confirm order');
            }
            
        } catch (error) {
            console.error('Error confirming order:', error);
            showNotification(`Error confirming order: ${error.message}`, 'error');
            
            const submitBtn = modal.querySelector('.btn-success');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirm Order';
        }
    };
}

export function cancelOrder(orderId, state) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Cancel Order</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel Order #${orderId}?</p>
                <div class="form-group">
                    <label for="cancel-reason">Reason for Cancellation:</label>
                    <textarea id="cancel-reason" class="form-control" rows="3" placeholder="Enter reason for cancellation..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Keep Order</button>
                <button class="btn btn-danger" onclick="submitOrderCancellation(${orderId}, this.closest('.modal-overlay'))">Cancel Order</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Store state in global scope for the modal function
    window.currentOrderState = state;
    
    window.submitOrderCancellation = async (orderId, modal) => {
        const reason = modal.querySelector('#cancel-reason').value;
        
        if (!reason.trim()) {
            showNotification('Please provide a reason for cancellation', 'error');
            return;
        }
        
        try {
            const submitBtn = modal.querySelector('.btn-danger');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Cancelling...';
            
            const response = await fetch('./API/update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: 'cancelled',
                    notes: reason,
                    updated_by: 'admin'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(`Order #${orderId} cancelled successfully!`, 'success');
                modal.remove();
                await fetchOrders(window.currentOrderState);
            } else {
                throw new Error(data.error || 'Failed to cancel order');
            }
            
        } catch (error) {
            console.error('Error cancelling order:', error);
            showNotification(`Error cancelling order: ${error.message}`, 'error');
            
            const submitBtn = modal.querySelector('.btn-danger');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Cancel Order';
        }
    };
}

export function shipOrder(orderId, state) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Ship Order</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Mark Order #${orderId} as shipped</p>
                <div class="form-group">
                    <label for="tracking-number">Tracking Number:</label>
                    <input type="text" id="tracking-number" class="form-control" placeholder="Enter tracking number...">
                </div>
                <div class="form-group">
                    <label for="shipping-notes">Shipping Notes:</label>
                    <textarea id="shipping-notes" class="form-control" rows="3" placeholder="Enter shipping notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                <button class="btn btn-primary" onclick="submitOrderShipment(${orderId}, this.closest('.modal-overlay'))">Mark as Shipped</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Store state in global scope for the modal function
    window.currentOrderState = state;
    
    window.submitOrderShipment = async (orderId, modal) => {
        const tracking = modal.querySelector('#tracking-number').value;
        const notes = modal.querySelector('#shipping-notes').value;
        
        const statusNotes = [];
        if (tracking) statusNotes.push(`Tracking: ${tracking}`);
        if (notes) statusNotes.push(notes);
        
        try {
            const submitBtn = modal.querySelector('.btn-primary');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Shipping...';
            
            const response = await fetch('./API/update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: 'shipped',
                    notes: statusNotes.join(' | '),
                    updated_by: 'admin'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(`Order #${orderId} marked as shipped!`, 'success');
                modal.remove();
                await fetchOrders(window.currentOrderState);
            } else {
                throw new Error(data.error || 'Failed to update order status');
            }
            
        } catch (error) {
            console.error('Error updating order status:', error);
            showNotification(`Error updating order status: ${error.message}`, 'error');
            
            const submitBtn = modal.querySelector('.btn-primary');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Mark as Shipped';
        }
    };
}

export function deliverOrder(orderId, state) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Deliver Order</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Mark Order #${orderId} as delivered</p>
                <div class="form-group">
                    <label for="delivery-notes">Delivery Notes:</label>
                    <textarea id="delivery-notes" class="form-control" rows="3" placeholder="Enter delivery notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                <button class="btn btn-success" onclick="submitOrderDelivery(${orderId}, this.closest('.modal-overlay'))">Mark as Delivered</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Store state in global scope for the modal function
    window.currentOrderState = state;
    
    window.submitOrderDelivery = async (orderId, modal) => {
        const notes = modal.querySelector('#delivery-notes').value;
        
        try {
            const submitBtn = modal.querySelector('.btn-success');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Delivering...';
            
            const response = await fetch('./API/update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: 'delivered',
                    notes: notes || 'Order delivered successfully',
                    updated_by: 'admin'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(`Order #${orderId} marked as delivered!`, 'success');
                modal.remove();
                await fetchOrders(window.currentOrderState);
            } else {
                throw new Error(data.error || 'Failed to update order status');
            }
            
        } catch (error) {
            console.error('Error updating order status:', error);
            showNotification(`Error updating order status: ${error.message}`, 'error');
            
            const submitBtn = modal.querySelector('.btn-success');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Mark as Delivered';
        }
    };
}

// updateOrderStatus function is handled by main.js



export function editOrder(orderId, state) {
    // For now, redirect to view order with edit option
    viewOrder(orderId, state);
}

export function deleteOrder(orderId, state) {
    if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
        // Implement delete functionality
        showNotification('Order deletion not implemented yet', 'warning');
    }
}

export function initializeOrders(state) {
    console.log('Initializing orders module...');
    
    // Load initial orders
    fetchOrders(state);
    
    // Set up search and filter functionality
    const orderSearch = document.getElementById('order-search');
    const orderStatusFilter = document.getElementById('order-status-filter');
    
    if (orderSearch) {
        orderSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const filteredOrders = state.orders.filter(order => 
                order.customer_name.toLowerCase().includes(searchTerm) ||
                order.customer_email.toLowerCase().includes(searchTerm) ||
                order.id.toString().includes(searchTerm)
            );
            renderOrders(filteredOrders, state);
        });
    }
    
    if (orderStatusFilter) {
        orderStatusFilter.addEventListener('change', function() {
            const statusFilter = this.value;
            const filteredOrders = statusFilter ? 
                state.orders.filter(order => order.status === statusFilter) : 
                state.orders;
            renderOrders(filteredOrders, state);
        });
    }
    
    console.log('Orders module initialized');
}

// Replace placeholder functions with actual implementations
if (typeof window !== 'undefined') {
    window.adminModules.viewOrder = viewOrder;
    window.adminModules.editOrder = editOrder;
    window.adminModules.deleteOrder = deleteOrder;
    window.adminModules.confirmOrder = confirmOrder;
    window.adminModules.cancelOrder = cancelOrder;
    window.adminModules.shipOrder = shipOrder;
    window.adminModules.deliverOrder = deliverOrder;
}
