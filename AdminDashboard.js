// Simulated data (replace with actual API calls in production)
        let products = [];
        const orders = [];
        const services = [];

        let notifications = [];

        function generateNotifications() {
            notifications = [];
            const now = new Date();

            products.forEach(product => {
                if (product.stock <= product.lowStock) {
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
                        message: `Pending order ${order.id} from ${order.customer}.`,
                        time: order.date,
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

        function toggleNotifications() {
            const dropdown = document.getElementById("notification-dropdown");
            dropdown.classList.toggle("show");
        }

        function showSection(sectionId) {
            document.querySelectorAll(".content-section").forEach(section => section.classList.remove("active"));
            document.getElementById(sectionId).classList.add("active");
            document.querySelectorAll(".nav-link").forEach(link => link.classList.remove("active"));
            document.querySelector(`.nav-link[data-section="${sectionId}"]`).classList.add("active");
            document.getElementById("page-title").textContent = sectionId.charAt(0).toUpperCase() + sectionId.slice(1);
            document.getElementById("notification-dropdown").classList.remove("show");
            if (sectionId === "products") {
                renderProducts(products);
            }
        }

        function openModal(modalId, id = null) {
            const modal = document.getElementById(modalId);
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
                    document.getElementById("product-featured").checked = product.featured || false;
                    document.getElementById("product-active").checked = product.active;
                    document.getElementById("product-modal-title").textContent = "Edit Product";
                    const preview = document.getElementById("image-preview");
                    preview.innerHTML = product.images.map(url => `<img src="${url}" alt="Product Image">`).join("");
                }
            } else if (modalId === "productModal") {
                document.getElementById("productForm").reset();
                document.getElementById("product-id").value = "";
                document.getElementById("product-active").checked = true;
                document.getElementById("product-low-stock").value = 10;
                document.getElementById("image-preview").innerHTML = "";
                document.getElementById("product-modal-title").textContent = "Add New Product";
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        function handleImageUpload(input) {
            const preview = document.getElementById("image-preview");
            preview.innerHTML = "";
            const files = Array.from(input.files);
            files.forEach(file => {
                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const img = document.createElement("img");
                        img.src = e.target.result;
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

    // Collect existing images from the preview (for edit mode)
    let images = [];
    if (isEditing) {
        formData.append('id', id);
        images = Array.from(document.getElementById("image-preview").querySelectorAll("img"))
            .map(img => img.src)
            .filter(src => !src.startsWith("data:")); // Only keep existing (not new uploads)
        formData.append('existing_images', images.join(','));
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
            fetchProducts(); // fetch updated product list
        } else {
            showNotification(`Failed to ${isEditing ? "update" : "add"} product: ` + data.error, "error");
        }
    })
    .catch(err => {
        console.error(err);
        showNotification(`An error occurred while ${isEditing ? "updating" : "adding"} the product.`, "error");
    });
}


function fetchProducts() {
    fetch('api/get_products.php')
        .then(response => response.json())
        .then(data => {
            products = data.map(product => {
                // --- Begin universal specs parsing ---
                let specsArray = [];
                if (typeof product.specifications === 'string') {
                    try {
                        specsArray = JSON.parse(product.specifications); // if it's a JSON string
                    } catch {
                        specsArray = product.specifications.split(','); // fallback: comma text
                    }
                } else if (typeof product.specifications === 'object' && product.specifications !== null) {
                    specsArray = Object.entries(product.specifications).map(
                        ([key, value]) => `${key}: ${value}`
                    );
                }
                // --- End universal specs parsing ---
                return {
                    id: parseInt(product.id),
                    name: product.name,
                    category: product.category,
                    brand: product.brand,
                    price: parseFloat(product.price),
                    discount: product.discount_price ? parseFloat(product.discount_price) : null,
                    stock: parseInt(product.stock),
                    lowStock: parseInt(product.low_stock_threshold),
                    description: product.description,
                    specs: specsArray,
                    weight: product.weight_kg ? parseFloat(product.weight_kg) : null,
                    warranty: product.warranty_months ? parseInt(product.warranty_months) : null,
                    featured: product.is_featured == "1",
                    active: product.is_active == "1",
                    images: product.image_paths ? product.image_paths.split(",") : []
                };
            });

            renderProducts(products);
            document.getElementById("total-products").textContent = products.length;
            generateNotifications();
        });
}



        function renderProducts(productList) {
            const tbody = document.getElementById("products-tbody");
            tbody.innerHTML = productList.map(product => `
                <tr>
                    <td><img src="${product.images[0] || 'placeholder.jpg'}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                    <td>${product.name}</td>
                    <td>${product.category}</td>
                    <td>${product.price.toFixed(2)}</td>
                    <td>${product.stock}</td>
                    <td><span class="status-badge status-${product.active ? (product.stock <= product.lowStock ? 'out-of-stock' : 'completed') : 'pending'}">${product.active ? (product.stock <= product.lowStock ? 'Low Stock' : 'In Stock') : 'Inactive'}</span></td>
                    <td class="action-buttons">
                        <button class="btn btn-primary" onclick="openModal('productModal', ${product.id})">Edit</button>
                        <button class="btn btn-danger" onclick="deleteProduct(${product.id})">Delete</button>
                    </td>
                </tr>
            `).join("");
        }

        function deleteProduct(id) {
            if (!confirm("Are you sure you want to delete this product?")) return;

            const formData = new FormData();
            formData.append("id", id);

            fetch("api/delete_product.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification("Product deleted successfully!", "success");
                    fetchProducts(); // reload updated list
                } else {
                    showNotification("Failed to delete product: " + data.error, "error");
                }
            })
            .catch(err => {
                console.error(err);
                showNotification("An error occurred while deleting the product.", "error");
            });
        }

        function exportData(type) {
            if (type === "products") {
                const csv = [
                    ["ID", "Name", "Category", "Price", "Stock", "Low Stock", "Active"],
                    ...products.map(p => [
                        p.id,
                        `"${p.name.replace(/"/g, '""')}"`,
                        p.category,
                        p.price,
                        p.stock,
                        p.lowStock,
                        p.active
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
            } else {
                showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} data exported!`, "success");
            }
        }

        function bulkUpdatePrices() {
            const percentage = prompt("Enter percentage increase (e.g., 10 for 10%):");
            if (percentage && !isNaN(percentage)) {
                products = products.map(p => ({
                    ...p,
                    price: p.price * (1 + parseFloat(percentage) / 100)
                }));
                showNotification(`Prices updated by ${percentage}%!`, "success");
                renderProducts(products);
            } else {
                showNotification("Invalid percentage!", "error");
            }
        }

        function viewLowStock() {
            showSection("products");
            const lowStockProducts = products.filter(p => p.stock <= p.lowStock);
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
            showNotification("Logged out successfully!", "success");
        }

        function importProducts() {
            showNotification("Products imported successfully!", "success");
        }

        function assignBulkTechnician() {
            showNotification("Technicians assigned successfully!", "success");
        }

        function sendNewsletter() {
            showNotification("Newsletter sent successfully!", "success");
        }

        function searchProducts(query) {
            const filtered = products.filter(p => p.name.toLowerCase().includes(query.toLowerCase()));
            renderProducts(filtered);
        }

        function filterProducts(category) {
            const filtered = category ? products.filter(p => p.category === category) : products;
            renderProducts(filtered);
        }

        function searchOrders(query) {
            // Implement search logic
        }

        function filterOrders(status) {
            // Implement filter logic
        }

        function searchCustomers(query) {
            // Implement search logic
        }

        function searchServices(query) {
            // Implement search logic
        }

        function filterServices(status) {
            // Implement filter logic
        }

        function generateReport(type) {
            showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} report generated!`, "success");
        }

        window.onload = () => {
            fetchProducts();
            fetchOrders(); // ✅ NEW
        };
function fetchOrders() {
    fetch('api/get_orders.php')
        .then(res => res.json())
        .then(data => {
            renderOrders(data);
        });
}

function renderOrders(orderList) {
    const tbody = document.getElementById("orders-tbody");
    tbody.innerHTML = orderList.map(order => `
        <tr>
            <td>${order.id}</td>
            <td>${order.customer_name}</td>
            <td>${new Date(order.order_date).toLocaleDateString()}</td>
            <td>${order.items}</td>
            <td>${order.total_price}</td>
            <td><span class="status-badge status-${order.status}">${order.status}</span></td>
            <td class="action-buttons">
                <button class="btn btn-primary" onclick="viewOrderDetails(${order.id})">View</button>
            </td>
        </tr>
    `).join("");
}