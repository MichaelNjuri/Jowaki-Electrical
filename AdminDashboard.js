let products = [];
let orders = [];
let services = [];
let notifications = [];

function generateNotifications() {
    notifications = [];
    const now = new Date();

    products.forEach(product => {
        if (product.stock <= product.lowStock && product.active) {
            notifications.push({
                id: `prod-${product.id}`,
                message: `Low stock alert: ${product.name} has ${product.stock} units left.`,
                time: now.toISOString(),
                action: `openModal('productModal', ${product.id})`,
                section: "products"
            });
        }
    });

    orders.forEach(order => {
        if (order.status === "pending") {
            notifications.push({
                id: `ord-${order.id}`,
                message: `Pending order #${order.id} from ${order.customer_info.firstName} ${order.customer_info.lastName}.`,
                time: order.order_date,
                action: `openModal('orderModal', '${order.id}')`,
                section: "orders"
            });
        }
    });

    services.forEach(service => {
        if (service.status === "pending") {
            notifications.push({
                id: `srv-${service.id}`,
                message: `Pending service request ${service.id}: ${service.type}.`,
                time: service.date,
                action: `openModal('serviceModal', '${service.id}')`,
                section: "services"
            });
        }
    });

    updateNotificationDisplay();
}

function updateNotificationDisplay() {
    const notificationList = document.getElementById("notifications-list");
    const notificationCount = document.getElementById("notification-count");
    if (notificationList && notificationCount) {
        notificationList.innerHTML = notifications.length === 0 
            ? '<div class="notification-item">No new notifications</div>'
            : notifications.map(notif => `
                <div class="notification-item" id="notif-${notif.id}">
                    <span class="message">${notif.message}</span>
                    <span class="time">${new Date(notif.time).toLocaleString()}</span>
                    <a href="#" class="action" onclick="${notif.action}; showSection('${notif.section}'); return false;">View</a>
                </div>
            `).join("");
        notificationCount.textContent = notifications.length || "";
        notificationCount.style.display = notifications.length ? "inline" : "none";
    }
}

function toggleNotifications() {
    const dropdown = document.getElementById("notification-dropdown");
    if (dropdown) dropdown.classList.toggle("show");
}

function showSection(sectionId) {
    document.querySelectorAll(".content-section").forEach(section => section.classList.remove("active"));
    const section = document.getElementById(sectionId);
    if (section) section.classList.add("active");
    document.querySelectorAll(".nav-link").forEach(link => link.classList.remove("active"));
    const navLink = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
    if (navLink) navLink.classList.add("active");
    const pageTitle = document.getElementById("page-title");
    if (pageTitle) pageTitle.textContent = sectionId.charAt(0).toUpperCase() + sectionId.slice(1);
    const notificationDropdown = document.getElementById("notification-dropdown");
    if (notificationDropdown) notificationDropdown.classList.remove("show");
    if (sectionId === "products") {
        renderProducts(products);
    } else if (sectionId === "orders") {
        renderOrders(orders);
    } else if (sectionId === "services") {
        renderServices(services);
    }
}

function openModal(modalId, id = null) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = "block";
    if (modalId === "productModal" && id) {
        const product = products.find(p => p.id === id);
        if (product) {
            document.getElementById("product-id").value = product.id;
            document.getElementById("product-name").value = product.name;
            document.getElementById("product-category").value = product.category;
            document.getElementById("product-brand").value = product.brand || "";
            document.getElementById("product-price").value = product.price;
            document.getElementById("product-discount").value = product.discount || "";
            document.getElementById("product-stock").value = product.stock;
            document.getElementById("product-low-stock").value = product.lowStock;
            document.getElementById("product-description").value = product.description || "";
            document.getElementById("product-specs").value = product.specs ? product.specs.join("\n") : "";
            document.getElementById("product-weight").value = product.weight || "";
            document.getElementById("product-warranty").value = product.warranty || "";
            document.getElementById("product-featured").checked = product.featured;
            document.getElementById("product-active").checked = product.active;
            document.getElementById("product-modal-title").textContent = "Edit Product";
            const preview = document.getElementById("image-preview");
            if (preview) preview.innerHTML = product.images.map(url => `<img src="${url}" alt="Product Image">`).join("");
        }
    } else if (modalId === "productModal") {
        const form = document.getElementById("productForm");
        if (form) form.reset();
        document.getElementById("product-id").value = "";
        document.getElementById("product-active").checked = true;
        document.getElementById("product-low-stock").value = 10;
        const preview = document.getElementById("image-preview");
        if (preview) preview.innerHTML = "";
        document.getElementById("product-modal-title").textContent = "Add New Product";
    } else if (modalId === "orderModal" && id) {
        viewOrderDetails(id);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = "none";
}

function handleImageUpload(input) {
    const preview = document.getElementById("image-preview");
    if (!preview) return;
    preview.innerHTML = "";
    const files = Array.from(input.files);
    files.forEach(file => {
        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.style.maxWidth = "100px";
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
}

function saveProduct(event) {
    event.preventDefault();
    const form = document.getElementById("productForm");
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const id = document.getElementById("product-id").value;
    const isEditing = !!id;
    const apiUrl = isEditing ? 'api/update_product.php' : 'api/add_product.php';

    const formData = new FormData();
    const imageInput = document.getElementById("product-images");
    for (let i = 0; i < imageInput.files.length; i++) {
        formData.append('images[]', imageInput.files[i]);
    }

    if (isEditing) {
        formData.append('id', id);
        const existingImages = Array.from(document.getElementById("image-preview").querySelectorAll("img"))
            .map(img => img.src)
            .filter(src => !src.startsWith("data:"));
        formData.append('existing_images', existingImages.join(','));
    }

    formData.append('name', document.getElementById("product-name").value);
    formData.append('category', document.getElementById("product-category").value);
    formData.append('brand', document.getElementById("product-brand").value);
    formData.append('price', document.getElementById("product-price").value);
    formData.append('discount_price', document.getElementById("product-discount").value);
    formData.append('stock', document.getElementById("product-stock").value);
    formData.append('low_stock_threshold', document.getElementById("product-low-stock").value);
    formData.append('description', document.getElementById("product-description").value);
    formData.append('specifications', document.getElementById("product-specs").value);
    formData.append('weight_kg', document.getElementById("product-weight").value);
    formData.append('warranty_months', document.getElementById("product-warranty").value);
    formData.append('is_featured', document.getElementById("product-featured").checked ? 1 : 0);
    formData.append('is_active', document.getElementById("product-active").checked ? 1 : 0);

    fetch(apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification(`Product ${isEditing ? "updated" : "added"} successfully!`, "success");
            closeModal("productModal");
            fetchProducts();
        } else {
            showNotification(`Failed to ${isEditing ? "update" : "add"} product: ${data.error}`, "error");
        }
    })
    .catch(err => {
        console.error('Error saving product:', err);
        showNotification(`An error occurred while ${isEditing ? "updating" : "adding"} the product.`, "error");
    });
}

function fetchProducts() {
    fetch('api/get_products_admin.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            let productsArray = Array.isArray(data) ? data : data.products || [];
            products = productsArray.map(product => {
                let specsArray = [];
                if (product.specifications) {
                    if (typeof product.specifications === 'string') {
                        try {
                            specsArray = JSON.parse(product.specifications);
                        } catch {
                            specsArray = product.specifications.split(',').map(s => s.trim());
                        }
                    } else if (Array.isArray(product.specifications)) {
                        specsArray = product.specifications;
                    } else if (typeof product.specifications === 'object' && product.specifications !== null) {
                        specsArray = Object.entries(product.specifications).map(([key, value]) => `${key}: ${value}`);
                    }
                }
                let images = product.image_paths ? product.image_paths.split(",").map(path => path.trim()).filter(path => path) : product.image ? [product.image] : ['placeholder.jpg'];
                return {
                    id: parseInt(product.id),
                    name: product.name || '',
                    category: product.category || '',
                    brand: product.brand || '',
                    price: parseFloat(product.price) || 0,
                    discount: product.discount_price ? parseFloat(product.discount_price) : null,
                    stock: parseInt(product.stock) || 0,
                    lowStock: parseInt(product.low_stock_threshold) || 10,
                    description: product.description || '',
                    specs: specsArray,
                    weight: product.weight_kg ? parseFloat(product.weight_kg) : null,
                    warranty: product.warranty_months ? parseInt(product.warranty_months) : null,
                    featured: product.is_featured == 1,
                    active: product.is_active == 1,
                    images: images
                };
            });
            renderProducts(products);
            const totalProducts = document.getElementById("total-products");
            if (totalProducts) totalProducts.textContent = products.length;
            generateNotifications();
        })
        .catch(err => {
            console.error('Error fetching products:', err);
            showNotification('Failed to fetch products: ' + err.message, 'error');
        });
}

function renderProducts(productList) {
    const tbody = document.getElementById("products-tbody");
    if (!tbody) return;
    tbody.innerHTML = productList.length === 0 
        ? '<tr><td colspan="7">No products found</td></tr>'
        : productList.map(product => {
            const imageUrl = product.images && product.images.length > 0 ? product.images[0] : 'placeholder.jpg';
            const stockStatus = product.active ? (product.stock <= product.lowStock ? 'Low Stock' : 'In Stock') : 'Inactive';
            const statusClass = product.active ? (product.stock <= product.lowStock ? 'out-of-stock' : 'completed') : 'pending';
            return `
                <tr>
                    <td><img src="${imageUrl}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" onerror="this.src='placeholder.jpg'"></td>
                    <td>${product.name}</td>
                    <td>${product.category}</td>
                    <td>KSh ${product.price.toFixed(2)}</td>
                    <td>${product.stock}</td>
                    <td><span class="status-badge status-${statusClass}">${stockStatus}</span></td>
                    <td class="action-buttons">
                        <button class="btn btn-primary" onclick="openModal('productModal', ${product.id})">Edit</button>
                        <button class="btn btn-danger" onclick="deleteProduct(${product.id})">${product.active ? 'Deactivate' : 'Activate'}</button>
                    </td>
                </tr>
            `;
        }).join("");
}

function deleteProduct(id) {
    const product = products.find(p => p.id === id);
    if (!product || !confirm(`Are you sure you want to ${product.active ? 'deactivate' : 'activate'} this product?`)) return;
    const formData = new FormData();
    formData.append("id", id);
    fetch("api/delete_product.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification(`Product ${data.active ? 'activated' : 'deactivated'} successfully!`, "success");
            fetchProducts();
        } else {
            showNotification("Failed to update product status: " + data.error, "error");
        }
    })
    .catch(err => {
        console.error('Error updating product status:', err);
        showNotification("An error occurred while updating the product status.", "error");
    });
}

function exportData(type) {
    if (type === "products") {
        const csv = [
            ["ID", "Name", "Category", "Brand", "Price", "Discount Price", "Stock", "Low Stock", "Active", "Featured", "Images"],
            ...products.map(p => [
                p.id,
                `"${p.name.replace(/"/g, '""')}"`,
                p.category,
                p.brand || '',
                p.price,
                p.discount || '',
                p.stock,
                p.lowStock,
                p.active ? 1 : 0,
                p.featured ? 1 : 0,
                `"${p.images.join(',')}"`
            ])
        ].map(row => row.join(",")).join("\n");
        const blob = new Blob([csv], { type: "text/csv" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "products.csv";
        a.click();
        URL.revokeObjectURL(url);
        showNotification("Products exported successfully!", "success");
    } else if (type === "orders") {
        const csv = [
            ["ID", "Customer", "Date", "Subtotal", "Tax", "Delivery Fee", "Total", "Status"],
            ...orders.map(o => [
                o.id,
                `"${o.customer_info.firstName} ${o.customer_info.lastName}"`,
                new Date(o.order_date).toLocaleDateString(),
                o.subtotal,
                o.tax,
                o.delivery_fee,
                o.total,
                o.status
            ])
        ].map(row => row.join(",")).join("\n");
        const blob = new Blob([csv], { type: "text/csv" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "orders.csv";
        a.click();
        URL.revokeObjectURL(url);
        showNotification("Orders exported successfully!", "success");
    } else {
        showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} data exported!`, "success");
    }
}

function bulkUpdatePrices() {
    const percentage = prompt("Enter percentage increase (e.g., 10 for 10%):");
    if (percentage && !isNaN(percentage)) {
        const formData = new FormData();
        formData.append('percentage', parseFloat(percentage));
        fetch('api/bulk_update_prices.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(`Prices updated by ${percentage}%!`, "success");
                fetchProducts();
            } else {
                showNotification("Failed to update prices: " + data.error, "error");
            }
        })
        .catch(err => {
            console.error('Error updating prices:', err);
            showNotification("An error occurred while updating prices.", "error");
        });
    } else {
        showNotification("Invalid percentage!", "error");
    }
}

function viewLowStock() {
    showSection("products");
    const lowStockProducts = products.filter(p => p.stock <= p.lowStock && p.active);
    renderProducts(lowStockProducts);
    showNotification("Displaying low stock products!", "success");
}

function saveService(event) {
    event.preventDefault();
    showNotification("Service request updated!", "success");
}

function showNotification(message, type) {
    const notification = document.createElement("div");
    notification.className = `notification ${type} show`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.classList.remove("show");
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function logout() {
    fetch('api/logout.php', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification("Logged out successfully!", "success");
            window.location.href = 'login.html';
        } else {
            showNotification("Failed to log out: " + data.error, "error");
        }
    })
    .catch(err => {
        console.error('Error logging out:', err);
        showNotification("An error occurred while logging out.", "error");
    });
}

function importProducts() {
    const input = document.createElement("input");
    input.type = "file";
    input.accept = ".csv";
    input.onchange = () => {
        const file = input.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('file', file);
            fetch('api/import_products.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification("Products imported successfully!", "success");
                    fetchProducts();
                } else {
                    showNotification("Failed to import products: " + data.error, "error");
                }
            })
            .catch(err => {
                console.error('Error importing products:', err);
                showNotification("An error occurred while importing products.", "error");
            });
        }
    };
    input.click();
}

function assignBulkTechnician() {
    showNotification("Technicians assigned successfully!", "success");
}

function sendNewsletter() {
    const subject = prompt("Enter newsletter subject:");
    const message = prompt("Enter newsletter message:");
    if (subject && message) {
        fetch('api/send_newsletter.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ subject, message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification("Newsletter sent successfully!", "success");
            } else {
                showNotification("Failed to send newsletter: " + data.error, "error");
            }
        })
        .catch(err => {
            console.error('Error sending newsletter:', err);
            showNotification("An error occurred while sending newsletter.", "error");
        });
    } else {
        showNotification("Subject and message are required!", "error");
    }
}

function searchProducts(query) {
    const filtered = products.filter(p => 
        p.name.toLowerCase().includes(query.toLowerCase()) ||
        p.category.toLowerCase().includes(query.toLowerCase()) ||
        (p.brand && p.brand.toLowerCase().includes(query.toLowerCase()))
    );
    renderProducts(filtered);
    showNotification(`Found ${filtered.length} products matching "${query}"`, "success");
}

function filterProducts(category) {
    const filtered = category ? products.filter(p => p.category === category) : products;
    renderProducts(filtered);
    showNotification(category ? `Filtered by ${category}` : "Showing all products", "success");
}

function searchOrders(query) {
    const filtered = orders.filter(o => 
        `${o.customer_info.firstName} ${o.customer_info.lastName}`.toLowerCase().includes(query.toLowerCase()) ||
        o.id.toString().includes(query)
    );
    renderOrders(filtered);
    showNotification(`Found ${filtered.length} orders matching "${query}"`, "success");
}

function filterOrders(status) {
    const filtered = status ? orders.filter(o => o.status === status) : orders;
    renderOrders(filtered);
    showNotification(status ? `Filtered by ${status}` : "Showing all orders", "success");
}

function searchCustomers(query) {
    const filteredOrders = orders.filter(o => 
        `${o.customer_info.firstName} ${o.customer_info.lastName}`.toLowerCase().includes(query.toLowerCase()) ||
        (o.customer_info.email && o.customer_info.email.toLowerCase().includes(query.toLowerCase()))
    );
    renderOrders(filteredOrders);
    showNotification(`Found ${filteredOrders.length} customers matching "${query}"`, "success");
}

function searchServices(query) {
    const filtered = services.filter(s => 
        (s.type && s.type.toLowerCase().includes(query.toLowerCase())) ||
        s.id.toString().includes(query)
    );
    renderServices(filtered);
    showNotification(`Found ${filtered.length} services matching "${query}"`, "success");
}

function filterServices(status) {
    const filtered = status ? services.filter(s => s.status === status) : services;
    renderServices(filtered);
    showNotification(status ? `Filtered by ${status}` : "Showing all services", "success");
}

function generateReport(type) {
    if (type === "sales") {
        const csv = [
            ["Order ID", "Customer", "Date", "Subtotal", "Tax", "Delivery Fee", "Total", "Status"],
            ...orders.map(o => [
                o.id,
                `"${o.customer_info.firstName} ${o.customer_info.lastName}"`,
                new Date(o.order_date).toLocaleDateString(),
                o.subtotal,
                o.tax,
                o.delivery_fee,
                o.total,
                o.status
            ])
        ].map(row => row.join(",")).join("\n");
        const blob = new Blob([csv], { type: "text/csv" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "sales_report.csv";
        a.click();
        URL.revokeObjectURL(url);
        showNotification("Sales report generated successfully!", "success");
    } else if (type === "inventory") {
        const csv = [
            ["ID", "Name", "Category", "Stock", "Low Stock Threshold", "Status"],
            ...products.map(p => [
                p.id,
                `"${p.name.replace(/"/g, '""')}"`,
                p.category,
                p.stock,
                p.lowStock,
                p.active ? (p.stock <= p.lowStock ? 'Low Stock' : 'In Stock') : 'Inactive'
            ])
        ].map(row => row.join(",")).join("\n");
        const blob = new Blob([csv], { type: "text/csv" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "inventory_report.csv";
        a.click();
        URL.revokeObjectURL(url);
        showNotification("Inventory report generated successfully!", "success");
    } else {
        showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} report generated!`, "success");
    }
}

function fetchOrders() {
    fetch('api/get_orders.php')
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (!Array.isArray(data)) {
                console.error('Invalid API response: data is not an array', data);
                throw new Error('Invalid API response: expected an array of orders');
            }
            orders = data.map((order, index) => {
                let cart = [];
                let customer_info = {};
                try {
                    console.log(`Processing order ${order.id || index}:`, order);
                    if (order.hasOwnProperty('cart')) {
                        if (Array.isArray(order.cart)) {
                            cart = order.cart;
                        } else if (typeof order.cart === 'string') {
                            if (order.cart.trim() === '') {
                                console.warn(`Empty cart string for order ${order.id || index}`);
                                cart = [];
                            } else {
                                try {
                                    const parsedCart = JSON.parse(order.cart);
                                    cart = Array.isArray(parsedCart) ? parsedCart : [];
                                } catch (e) {
                                    console.warn(`Failed to parse cart JSON for order ${order.id || index}:`, e);
                                    cart = [];
                                }
                            }
                        } else {
                            console.warn(`Invalid cart type for order ${order.id || index}:`, typeof order.cart, order.cart);
                            cart = [];
                        }
                    }
                    if (order.customer_info) {
                        customer_info = typeof order.customer_info === 'string' ? JSON.parse(order.customer_info) : order.customer_info;
                        if (typeof customer_info !== 'object' || customer_info === null) {
                            console.warn(`Invalid customer_info for order ${order.id || index}`);
                            customer_info = {};
                        }
                    }
                } catch (e) {
                    console.error(`Error parsing items or customer_info for order ${order.id || index}:`, e, order);
                    cart = [];
                    customer_info = {};
                }
                return {
                    id: parseInt(order.id) || 0,
                    customer_info: customer_info,
                    cart: cart,
                    subtotal: parseFloat(order.subtotal) || 0,
                    tax: parseFloat(order.tax) || 0,
                    delivery_fee: parseFloat(order.delivery_fee) || 0,
                    total: parseFloat(order.total) || 0,
                    status: order.status || 'pending',
                    order_date: order.order_date || new Date().toISOString(),
                    delivery_method: order.delivery_method || '',
                    delivery_address: order.delivery_address || '',
                    payment_method: order.payment_method || ''
                };
            });
            console.log('Orders processed:', orders);
            renderOrders(orders);
            generateNotifications();
        })
        .catch(err => {
            console.error('Error fetching orders:', err);
            showNotification('Failed to fetch orders: ' + err.message, 'error');
            orders = [];
            renderOrders(orders);
        });
}

function renderOrders(orderList) {
    const tbody = document.getElementById("orders-tbody");
    if (!tbody) return;
    tbody.innerHTML = orderList.length === 0 
        ? '<tr><td colspan="7">No orders found</td></tr>'
        : orderList.map(order => {
            const items = Array.isArray(order.cart) ? order.cart : [];
            return `
                <tr>
                    <td>${order.id}</td>
                    <td>${order.customer_info.firstName || ''} ${order.customer_info.lastName || ''}</td>
                    <td>${new Date(order.order_date).toLocaleDateString()}</td>
                    <td>${items.map(item => `${item.name || 'Unknown Item'} x${item.quantity || 0}`).join(', ')}</td>
                    <td>KSh ${order.total.toFixed(2)}</td>
                    <td>
                        <select class="status-select" onchange="updateOrderStatus(${order.id}, this.value)">
                            <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                            <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                            <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                            <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            <option value="confirmed" ${order.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                        </select>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-primary" onclick="openModal('orderModal', ${order.id})">View</button>
                        ${order.status !== 'confirmed' ? `<button class="btn btn-secondary" onclick="confirmOrder(${order.id})">Confirm Order</button>` : ''}
                    </td>
                </tr>
            `;
        }).join("");
}

function updateOrderStatus(orderId, status) {
    fetch('api/update_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId, status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification(`Order #${orderId} status updated to ${status}!`, "success");
            fetchOrders();
        } else {
            showNotification("Failed to update order status: " + data.error, "error");
        }
    })
    .catch(err => {
        console.error('Error updating order status:', err);
        showNotification("An error occurred while updating order status.", "error");
    });
}

function confirmOrder(orderId) {
    const confirmBtn = document.querySelector(`button[onclick="confirmOrder(${orderId})"]`);
    if (!confirm(`Confirm order #${orderId}? This will send a receipt to the customer.`)) return;

    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.textContent = "Confirming...";
    }

    fetch('api/confirm_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || `Order #${orderId} confirmed.`, "success");
            fetchOrders();
            closeModal('orderModal');
        } else {
            showNotification(data.error || "Failed to confirm order.", "error");
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Confirm Order";
            }
        }
    })
    .catch(err => {
        console.error('Confirm Order error:', err);
        showNotification("An error occurred while confirming order.", "error");
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = "Confirm Order";
        }
    });
}

function viewOrderDetails(orderId) {
    const order = orders.find(o => o.id == orderId);
    if (!order) {
        showNotification('Order not found!', 'error');
        return;
    }

    // Safely parse cart
    let items = [];
    if (Array.isArray(order.cart)) {
        items = order.cart;
    } else if (typeof order.cart === 'string') {
        try {
            const parsed = JSON.parse(order.cart);
            items = Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            console.warn(`Failed to parse cart for order ${order.id}:`, e);
        }
    } else {
        console.warn(`Unexpected cart type for order ${order.id}:`, typeof order.cart);
    }

    // Render item list with better formatting
    const itemsHtml = items.length > 0
        ? items.map(item => `
            <div class="order-item">
                <div class="item-info">
                    <h5>${item.name || 'Unnamed Item'}</h5>
                    <p>Quantity: ${item.quantity || 0}</p>
                    <p>Unit Price: KSh ${(item.price || 0).toFixed(2)}</p>
                </div>
                <div class="item-total">
                    <strong>KSh ${((item.price || 0) * (item.quantity || 0)).toFixed(2)}</strong>
                </div>
            </div>
        `).join('')
        : '<div class="no-items"><p>No items found in this order.</p></div>';

    // Enhanced Modal HTML with better styling and functionality
    const modalContent = `
        <div class="modal-header">
            <h2>Order Details #${order.id}</h2>
            <span class="close" onclick="closeModal('orderModal')">×</span>
        </div>
        
        <div class="modal-body">
            <!-- Order Status Section -->
            <div class="order-status-section">
                <h3>Order Status</h3>
                <div class="status-controls">
                    <select id="order-status-${order.id}" class="form-control status-select-large" onchange="updateOrderStatusFromModal(${order.id}, this.value)">
                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                        <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                        <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                        <option value="confirmed" ${order.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                    </select>
                    <div class="status-badge-large status-${order.status}">
                        Current Status: ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                    </div>
                </div>
            </div>

            <!-- Order Information Grid -->
            <div class="order-info-grid">
                <!-- Customer Information -->
                <div class="info-card">
                    <h4><i class="fas fa-user"></i> Customer Information</h4>
                    <div class="info-content">
                        <p><strong>Name:</strong> ${order.customer_info.firstName || ''} ${order.customer_info.lastName || ''}</p>
                        <p><strong>Email:</strong> ${order.customer_info.email || 'N/A'}</p>
                        <p><strong>Phone:</strong> ${order.customer_info.phone || 'N/A'}</p>
                        <p><strong>Address:</strong> ${order.customer_info.address || 'N/A'}</p>
                        <p><strong>City:</strong> ${order.customer_info.city || 'N/A'}</p>
                        ${order.customer_info.postalCode ? `<p><strong>Postal Code:</strong> ${order.customer_info.postalCode}</p>` : ''}
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="info-card">
                    <h4><i class="fas fa-truck"></i> Delivery Information</h4>
                    <div class="info-content">
                        <p><strong>Method:</strong> ${order.delivery_method || 'N/A'}</p>
                        <p><strong>Address:</strong> ${order.delivery_address || 'Same as billing'}</p>
                        <p><strong>Payment Method:</strong> ${order.payment_method || 'N/A'}</p>
                        <p><strong>Order Date:</strong> ${new Date(order.order_date).toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</p>
                    </div>
                </div>
            </div>

            <!-- Order Items Section -->
            <div class="order-items-section">
                <h4><i class="fas fa-shopping-cart"></i> Order Items</h4>
                <div class="items-container">
                    ${itemsHtml}
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h4><i class="fas fa-calculator"></i> Order Summary</h4>
                <div class="summary-table">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>KSh ${order.subtotal.toFixed(2)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (16%):</span>
                        <span>KSh ${order.tax.toFixed(2)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <span>KSh ${order.delivery_fee.toFixed(2)}</span>
                    </div>
                    <div class="summary-row total-row">
                        <span><strong>Total Amount:</strong></span>
                        <span><strong>KSh ${order.total.toFixed(2)}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('orderModal')">Close</button>
            <button class="btn btn-primary" onclick="printOrderDetails(${order.id})">Print Order</button>
            ${order.status !== 'confirmed' 
                ? `<button class="btn btn-success" id="confirm-btn-${order.id}" onclick="confirmOrderFromModal(${order.id})">
                    <i class="fas fa-check"></i> Confirm Order
                   </button>`
                : `<button class="btn btn-success" disabled>
                    <i class="fas fa-check-circle"></i> Already Confirmed
                   </button>`
            }
        </div>
    `;

    const modal = document.getElementById("orderModal");
    if (modal) {
        modal.innerHTML = modalContent;
        modal.style.display = "block";
    }
}

function updateOrderStatusFromModal(orderId, status) {
    const statusSelect = document.getElementById(`order-status-${orderId}`);
    const originalValue = statusSelect.value;
    
    // Show loading state
    statusSelect.disabled = true;
    
    fetch('api/update_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId, status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification(`Order #${orderId} status updated to ${status}!`, "success");
            
            // Update the order in the local array
            const orderIndex = orders.findIndex(o => o.id == orderId);
            if (orderIndex !== -1) {
                orders[orderIndex].status = status;
            }
            
            // Update the status badge in the modal
            const statusBadge = document.querySelector('.status-badge-large');
            if (statusBadge) {
                statusBadge.className = `status-badge-large status-${status}`;
                statusBadge.textContent = `Current Status: ${status.charAt(0).toUpperCase() + status.slice(1)}`;
            }
            
            // Update confirm button visibility
            const confirmBtn = document.getElementById(`confirm-btn-${orderId}`);
            if (status === 'confirmed' && confirmBtn) {
                confirmBtn.outerHTML = `<button class="btn btn-success" disabled>
                    <i class="fas fa-check-circle"></i> Already Confirmed
                </button>`;
            }
            
            // Refresh the orders table
            fetchOrders();
            
        } else {
            showNotification("Failed to update order status: " + data.error, "error");
            // Reset to original value
            statusSelect.value = originalValue;
        }
    })
    .catch(err => {
        console.error('Error updating order status:', err);
        showNotification("An error occurred while updating order status.", "error");
        // Reset to original value
        statusSelect.value = originalValue;
    })
    .finally(() => {
        statusSelect.disabled = false;
    });
}

function confirmOrderFromModal(orderId) {
    const confirmBtn = document.getElementById(`confirm-btn-${orderId}`);
    if (!confirm(`Confirm order #${orderId}? This will send a receipt to the customer and update the order status.`)) return;

    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Confirming...';
    }

    fetch('api/confirm_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || `Order #${orderId} confirmed successfully!`, "success");
            
            // Update the order in the local array
            const orderIndex = orders.findIndex(o => o.id == orderId);
            if (orderIndex !== -1) {
                orders[orderIndex].status = 'confirmed';
            }
            
            // Update the modal elements
            const statusSelect = document.getElementById(`order-status-${orderId}`);
            if (statusSelect) {
                statusSelect.value = 'confirmed';
            }
            
            // Update status badge
            const statusBadge = document.querySelector('.status-badge-large');
            if (statusBadge) {
                statusBadge.className = 'status-badge-large status-confirmed';
                statusBadge.textContent = 'Current Status: Confirmed';
            }
            
            // Replace confirm button with confirmed state
            if (confirmBtn) {
                confirmBtn.outerHTML = `<button class="btn btn-success" disabled>
                    <i class="fas fa-check-circle"></i> Already Confirmed
                </button>`;
            }
            
            // Refresh the orders table
            fetchOrders();
            
        } else {
            showNotification(data.error || "Failed to confirm order.", "error");
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Order';
            }
        }
    })
    .catch(err => {
        console.error('Confirm Order error:', err);
        showNotification("An error occurred while confirming order.", "error");
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Order';
        }
    });
}

function printOrderDetails(orderId) {
    const order = orders.find(o => o.id == orderId);
    if (!order) return;
    
    // Create print window
    const printWindow = window.open('', '_blank');
    const printContent = `
        <html>
        <head>
            <title>Order #${order.id} - Details</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .section { margin: 20px 0; }
                .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
                .item { border-bottom: 1px solid #eee; padding: 10px 0; }
                .total { font-weight: bold; font-size: 1.2em; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Order Receipt #${order.id}</h1>
                <p>Date: ${new Date(order.order_date).toLocaleDateString()}</p>
            </div>
            
            <div class="grid">
                <div class="section">
                    <h3>Customer Information</h3>
                    <p>${order.customer_info.firstName} ${order.customer_info.lastName}</p>
                    <p>${order.customer_info.email}</p>
                    <p>${order.customer_info.phone}</p>
                    <p>${order.customer_info.address}, ${order.customer_info.city}</p>
                </div>
                
                <div class="section">
                    <h3>Delivery Information</h3>
                    <p>Method: ${order.delivery_method}</p>
                    <p>Address: ${order.delivery_address}</p>
                    <p>Status: ${order.status}</p>
                </div>
            </div>
            
            <div class="section">
                <h3>Order Items</h3>
                ${Array.isArray(order.cart) ? order.cart.map(item => `
                    <div class="item">
                        <strong>${item.name}</strong> - Qty: ${item.quantity} - KSh ${(item.price * item.quantity).toFixed(2)}
                    </div>
                `).join('') : '<p>No items</p>'}
            </div>
            
            <div class="section">
                <h3>Order Summary</h3>
                <p>Subtotal: KSh ${order.subtotal.toFixed(2)}</p>
                <p>Tax: KSh ${order.tax.toFixed(2)}</p>
                <p>Delivery: KSh ${order.delivery_fee.toFixed(2)}</p>
                <p class="total">Total: KSh ${order.total.toFixed(2)}</p>
            </div>
            
            <button class="no-print" onclick="window.print()">Print</button>
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
}

function fetchServices() {
    services = [];
    renderServices(services);
}

function renderServices(serviceList) {
    const tbody = document.getElementById("services-tbody");
    if (!tbody) return;
    tbody.innerHTML = serviceList.length === 0
        ? '<tr><td colspan="5">No services found.</td></tr>'
        : serviceList.map(service => `
            <tr>
                <td>${service.id}</td>
                <td>${service.type || 'N/A'}</td>
                <td>${service.customer || 'N/A'}</td>
                <td>${new Date(service.date).toLocaleDateString()}</td>
                <td><span class="status-badge status-${service.status || 'pending'}">${service.status || 'pending'}</span></td>
                <td class="action-buttons">
                    <button class="btn btn-primary" onclick="openModal('serviceModal', '${service.id}')">View</button>
                </td>
            </tr>
        `).join("");
}

window.onload = () => {
    fetchProducts();
    fetchOrders();
    fetchServices();
};