import { showNotification } from './notifications.js';

export function fetchAnalyticsData(period = 'month', startDate = '', endDate = '', category = '') {
    let url = 'API/get_analytics_admin_fixed.php?period=' + period;
    if (startDate && endDate) {
        url += `&start_date=${startDate}&end_date=${endDate}`;
    }
    if (category) {
        url += `&category=${category}`;
    }

    return fetch(url, {
        credentials: 'include'
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderAnalytics(data.data);
            } else {
                throw new Error(data.error || 'Failed to fetch analytics data');
            }
        })
        .catch(error => {
            console.error('Analytics fetch error:', error);
            showNotification(`Error fetching analytics: ${error.message}`, 'error');
            renderAnalyticsError();
        });
}

function renderAnalyticsError() {
    const analyticsContainer = document.getElementById('analytics');
    if (analyticsContainer) {
        analyticsContainer.innerHTML = `
            <div class="analytics-error">
                <h2>Analytics Dashboard</h2>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Unable to load analytics data. Please check your connection and try again.
                </div>
            </div>
        `;
    }
}

function renderAnalytics(data) {
    console.log('Rendering analytics:', data);
    
    // Update sales overview cards
    updateSalesOverview(data.sales_overview);
    
    // Update top products table
    updateTopProductsTable(data.top_products);
    
    // Update category sales table
    updateCategorySalesTable(data.category_sales);
    
    // Update payment analysis table
    updatePaymentAnalysisTable(data.payment_analysis);
    
    // Update customer analysis
    updateCustomerAnalysis(data.customer_analysis);
}

function updateSalesOverview(salesOverview) {
    const elements = {
        'total-revenue': salesOverview.total_revenue,
        'total-orders': salesOverview.total_orders,
        'items-sold': salesOverview.total_items_sold,
        'avg-order-value': salesOverview.average_order_value,
        'total-tax': salesOverview.total_tax,
        'total-delivery': salesOverview.total_delivery_fees
    };

    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            if (id.includes('revenue') || id.includes('value') || id.includes('tax') || id.includes('delivery')) {
                element.textContent = `KSh ${parseFloat(value).toLocaleString()}`;
            } else {
                element.textContent = value.toLocaleString();
            }
        }
    });
}

function updateTopProductsTable(products) {
    const tbody = document.getElementById('top-products-tbody');
    if (!tbody) return;

    if (!products || products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">No products data available</td></tr>';
        return;
    }

    tbody.innerHTML = products.map(product => `
        <tr>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>${product.total_quantity_sold.toLocaleString()}</td>
            <td>KSh ${product.total_revenue.toLocaleString()}</td>
            <td>${product.order_count}</td>
        </tr>
    `).join('');
}

function updateCategorySalesTable(categories) {
    const tbody = document.getElementById('category-sales-tbody');
    if (!tbody) return;

    if (!categories || categories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #666;">No category data available</td></tr>';
        return;
    }

    tbody.innerHTML = categories.map(category => `
        <tr>
            <td>${category.category}</td>
            <td>${category.order_count}</td>
            <td>${category.total_quantity.toLocaleString()}</td>
            <td>KSh ${category.total_revenue.toLocaleString()}</td>
        </tr>
    `).join('');
}

function updatePaymentAnalysisTable(payments) {
    const tbody = document.getElementById('payment-analysis-tbody');
    if (!tbody) return;

    if (!payments || payments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #666;">No payment data available</td></tr>';
        return;
    }

    tbody.innerHTML = payments.map(payment => `
        <tr>
            <td>${payment.payment_method}</td>
            <td>${payment.order_count}</td>
            <td>KSh ${payment.total_revenue.toLocaleString()}</td>
            <td>KSh ${payment.average_order_value.toLocaleString()}</td>
        </tr>
    `).join('');
}

function updateCustomerAnalysis(customerAnalysis) {
    const elements = {
        'unique-customers': customerAnalysis.unique_customers,
        'new-customers-30d': customerAnalysis.new_customers_30d,
        'new-customers-7d': customerAnalysis.new_customers_7d
    };

    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value.toLocaleString();
        }
    });
}

export function generateReport() {
    const period = document.getElementById('report-period').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    const category = document.getElementById('report-category').value;

    // Show/hide custom date inputs
    const customDateInputs = document.querySelectorAll('#start-date, #end-date');
    customDateInputs.forEach(input => {
        input.style.display = period === 'custom' ? 'inline-block' : 'none';
    });

    fetchAnalyticsData(period, startDate, endDate, category);
}

export function initializeAnalytics() {
    // Set up event listeners for report controls
    const reportPeriod = document.getElementById('report-period');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');

    if (reportPeriod) {
        reportPeriod.addEventListener('change', function() {
            const customDateInputs = document.querySelectorAll('#start-date, #end-date');
            customDateInputs.forEach(input => {
                input.style.display = this.value === 'custom' ? 'inline-block' : 'none';
            });
        });
    }

    // Set default dates for custom range
    if (startDate && endDate) {
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
        
        startDate.value = thirtyDaysAgo.toISOString().split('T')[0];
        endDate.value = today.toISOString().split('T')[0];
    }

    // Load initial analytics data
    return fetchAnalyticsData();
}

// Make generateReport available globally
window.adminModules = window.adminModules || {};
window.adminModules.generateReport = generateReport;
