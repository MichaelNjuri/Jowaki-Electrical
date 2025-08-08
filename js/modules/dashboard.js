import { showNotification } from './notifications.js';

export function fetchDashboardStats(state) {
    return fetch('API/get_dashboard_stats.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
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
                    'contact-messages-count': data.contact_messages_count || 0
                }, state);
            } else {
                updateDashboardStatsFromLocal(state);
            }
        })
        .catch(error => {
            console.error('Dashboard stats fetch error:', error);
            showNotification(`Error fetching dashboard stats: ${error.message}`, 'error');
            updateDashboardStatsFromLocal(state);
        });
}

function updateDashboardElements(data, state) {
    Object.keys(data).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = data[id];
        } else {
            console.warn(`Dashboard element #${id} not found!`);
        }
    });
}

function updateDashboardStatsFromLocal(state) {
    const pendingOrders = state.orders.filter(order => order.status === 'pending').length;
    const monthlyRevenue = state.orders.reduce((sum, order) => sum + (parseFloat(order.total_amount) || 0), 0);
    
    updateDashboardElements({
        'total-products': state.products.length,
        'pending-orders': pendingOrders,
        'total-customers': state.customers.length,
        'monthly-revenue': monthlyRevenue.toFixed(2),
        'monthly-sales': state.orders.length,
        'orders-this-month': state.orders.length,
        'new-customers': state.customers.length,
        'contact-messages-count': 0
    }, state);
}

export function initializeDashboard(state) {
    return fetchDashboardStats(state);
}
