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
            const images = Array.from(document.getElementById("image-preview").querySelectorAll("img")).map(img => img.src);
            const product = {
                id: id ? parseInt(id) : products.length + 1,
                name: document.getElementById("product-name").value,
                category: document.getElementById("product-category").value,
                brand: document.getElementById("product-brand").value || null,
                price: parseFloat(document.getElementById("product-price").value),
                discount: parseFloat(document.getElementById("product-discount").value) || null,
                stock: parseInt(document.getElementById("product-stock").value),
                lowStock: parseInt(document.getElementById("product-low-stock").value),
                description: document.getElementById("product-description").value,
                specs: document.getElementById("product-specs").value.split("\n").filter(s => s.trim()),
                weight: parseFloat(document.getElementById("product-weight").value) || null,
                warranty: parseInt(document.getElementById("product-warranty").value) || null,
                featured: document.getElementById("product-featured").checked,
                active: document.getElementById("product-active").checked,
                images
            };

            if (id) {
                const index = products.findIndex(p => p.id === parseInt(id));
                products[index] = product;
            } else {
                products.push(product);
            }

            document.getElementById("total-products").textContent = products.length;
            closeModal("productModal");
            showNotification(`Product ${id ? "updated" : "added"} successfully!`, "success");
            generateNotifications();
            renderProducts(products);
            showSection("products");
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
            products = products.filter(p => p.id !== id);
            document.getElementById("total-products").textContent = products.length;
            showNotification("Product deleted successfully!", "success");
            generateNotifications();
            renderProducts(products);
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
            generateNotifications();
            document.getElementById("total-products").textContent = products.length;
            renderProducts(products);
        };