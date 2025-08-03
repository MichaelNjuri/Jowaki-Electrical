import { renderOrders } from './orders.js';
import { renderProducts } from './products.js';
import { renderCustomers } from './customers.js';
import { renderCategories } from './categories.js';

export function filterOrders(searchTerm, statusFilter, state) {
    let filteredOrders = state.orders;
    
    if (searchTerm) {
        filteredOrders = filteredOrders.filter(order => 
            order.customer_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            order.customer_email.toLowerCase().includes(searchTerm.toLowerCase()) ||
            order.id.toString().includes(searchTerm)
        );
    }
    
    if (statusFilter) {
        filteredOrders = filteredOrders.filter(order => order.status === statusFilter);
    }
    
    renderOrders(filteredOrders, state);
}

export function filterProducts(searchTerm, categoryFilter, state) {
    let filteredProducts = state.products;
    
    if (searchTerm) {
        filteredProducts = filteredProducts.filter(product => 
            product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            product.sku.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }
    
    if (categoryFilter) {
        filteredProducts = filteredProducts.filter(product => 
            product.category_id.toString() === categoryFilter
        );
    }
    
    renderProducts(filteredProducts, state);
}

export function filterCustomers(searchTerm, state) {
    let filteredCustomers = state.customers;
    
    if (searchTerm) {
        filteredCustomers = filteredCustomers.filter(customer => 
            customer.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            customer.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
            customer.phone.includes(searchTerm)
        );
    }
    
    renderCustomers(filteredCustomers, state);
}

export function filterCategories(searchTerm, state) {
    let filteredCategories = state.categories;
    
    if (searchTerm) {
        filteredCategories = filteredCategories.filter(category => 
            category.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            category.description.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }
    
    renderCategories(filteredCategories, state);
}

export function initializeSearchFilters(state) {
    // Order search and filter
    const orderSearch = document.getElementById('order-search');
    const orderStatusFilter = document.getElementById('order-status-filter');
    
    if (orderSearch) {
        orderSearch.addEventListener('input', (e) => {
            filterOrders(e.target.value, orderStatusFilter?.value || '', state);
        });
    }
    
    if (orderStatusFilter) {
        orderStatusFilter.addEventListener('change', (e) => {
            filterOrders(orderSearch?.value || '', e.target.value, state);
        });
    }
    
    // Product search and filter
    const productSearch = document.getElementById('product-search');
    const productCategoryFilter = document.getElementById('product-category-filter');
    
    if (productSearch) {
        productSearch.addEventListener('input', (e) => {
            filterProducts(e.target.value, productCategoryFilter?.value || '', state);
        });
    }
    
    if (productCategoryFilter) {
        productCategoryFilter.addEventListener('change', (e) => {
            filterProducts(productSearch?.value || '', e.target.value, state);
        });
    }
    
    // Customer search
    const customerSearch = document.getElementById('customer-search');
    if (customerSearch) {
        customerSearch.addEventListener('input', (e) => {
            filterCustomers(e.target.value, state);
        });
    }
    
    // Category search
    const categorySearch = document.getElementById('category-search');
    if (categorySearch) {
        categorySearch.addEventListener('input', (e) => {
            filterCategories(e.target.value, state);
        });
    }
}
