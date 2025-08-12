// Admin Management Module
const adminManagement = {
    // Initialize admin management
    init() {
        this.loadAdmins();
        this.loadActivity();
        this.loadRoles();
        this.bindEvents();
        this.checkPermissions();
    },

    // Check user permissions and show/hide admin management
    checkPermissions() {
        // This will be called after login to check if user has admin management permissions
        const adminManagementLink = document.getElementById('admin-management-link');
        if (adminManagementLink) {
            // Get admin user from localStorage
            const adminUserStr = localStorage.getItem('adminUser');
            if (adminUserStr) {
                try {
                    const adminUser = JSON.parse(adminUserStr);
                    // Only Super Admins (role_id = 1) should see admin management
                    if (adminUser.role_id === 1) {
                        adminManagementLink.style.display = 'block';
                        console.log('Super Admin - Admin management access granted');
                    } else {
                        adminManagementLink.style.display = 'none';
                        console.log('Regular Admin - Admin management access denied');
                    }
                } catch (error) {
                    console.error('Error parsing admin user data:', error);
                    adminManagementLink.style.display = 'none';
                }
            } else {
                console.log('No admin user found in localStorage');
                adminManagementLink.style.display = 'none';
            }
        }
    },

    // Bind event listeners
    bindEvents() {
        // Add admin form submission
        const addAdminForm = document.getElementById('add-admin-form');
        if (addAdminForm) {
            addAdminForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.createAdmin();
            });
        }

        // Edit admin form submission
        const editAdminForm = document.getElementById('edit-admin-form');
        if (editAdminForm) {
            editAdminForm.addEventListener('submit', (e) => this.handleEditAdminForm(e));
        }

        // Password confirmation validation
        const passwordField = document.querySelector('input[name="password"]');
        const confirmPasswordField = document.querySelector('input[name="confirm_password"]');
        if (passwordField && confirmPasswordField) {
            confirmPasswordField.addEventListener('input', () => {
                if (passwordField.value !== confirmPasswordField.value) {
                    confirmPasswordField.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordField.setCustomValidity('');
                }
            });
        }
    },

    // Load admin users
    async loadAdmins() {
        try {
            const response = await fetch('API/get_admins_fixed.php');
            const data = await response.json();

            if (data.success) {
                this.renderAdmins(data.admins);
                this.updateAdminStats(data.admins);
            } else {
                console.error('Failed to load admins:', data.message);
                this.showError('Failed to load admin users');
            }
        } catch (error) {
            console.error('Error loading admins:', error);
            this.showError('Error loading admin users');
        }
    },

    // Render admin users table
    renderAdmins(admins) {
        const tbody = document.getElementById('admins-table-body');
        if (!tbody) return;

        if (admins.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="loading-state"><span>No admin users found</span></td></tr>';
            return;
        }

        tbody.innerHTML = admins.map(admin => `
            <tr>
                <td>
                    <div class="admin-info">
                        <div class="admin-avatar">
                            <i class="fas fa-${admin.role_id === 1 ? 'crown' : 'user-shield'}"></i>
                        </div>
                        <div class="admin-details">
                            <h5>${admin.full_name || 'N/A'}</h5>
                            <p>@${admin.username} • ${admin.email}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="role-badge ${admin.role_id === 1 ? 'super' : 'admin'}">
                        <i class="fas fa-${admin.role_id === 1 ? 'crown' : 'user-shield'}"></i>
                        ${admin.role_id === 1 ? 'Super Admin' : 'Admin'}
                    </span>
                </td>
                <td>
                    <span class="status-badge ${admin.is_active ? 'active' : 'inactive'}">
                        ${admin.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <span class="activity-time">${admin.last_login ? this.formatDate(admin.last_login) : 'Never'}</span>
                </td>
                <td>
                    <div class="admin-actions">
                        <button class="btn btn-outline btn-sm" onclick="adminManagement.viewAdmin(${admin.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${admin.role_id !== 1 ? `
                            <button class="btn btn-primary btn-sm" onclick="adminManagement.editAdmin(${admin.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-${admin.is_active ? 'danger' : 'success'} btn-sm" 
                                    onclick="adminManagement.toggleAdminStatus(${admin.id}, ${admin.is_active})" 
                                    title="${admin.is_active ? 'Deactivate' : 'Activate'}">
                                <i class="fas fa-${admin.is_active ? 'ban' : 'check'}"></i>
                            </button>
                        ` : '<span class="text-muted">Protected</span>'}
                    </div>
                </td>
            </tr>
        `).join('');
    },

    // Update admin statistics
    updateAdminStats(admins) {
        const totalAdmins = admins.length;
        const activeAdmins = admins.filter(admin => admin.is_active).length;
        const superAdmins = admins.filter(admin => admin.role_id === 1).length;
        const recentLogins = admins.filter(admin => {
            if (!admin.last_login) return false;
            const lastLogin = new Date(admin.last_login);
            const now = new Date();
            const diffDays = (now - lastLogin) / (1000 * 60 * 60 * 24);
            return diffDays <= 7;
        }).length;

        // Update stats only if elements exist
        const totalAdminsEl = document.getElementById('total-admins');
        const activeAdminsEl = document.getElementById('active-admins');
        const superAdminsEl = document.getElementById('super-admins');
        const recentLoginsEl = document.getElementById('recent-logins');

        if (totalAdminsEl) totalAdminsEl.textContent = totalAdmins;
        if (activeAdminsEl) activeAdminsEl.textContent = activeAdmins;
        if (superAdminsEl) superAdminsEl.textContent = superAdmins;
        if (recentLoginsEl) recentLoginsEl.textContent = recentLogins;
    },

    // Load admin roles for the form
    async loadRoles() {
        try {
            const response = await fetch('API/get_admin_roles_fixed.php');
            const data = await response.json();

            if (data.success) {
                this.renderRoles(data.roles);
            } else {
                console.error('Failed to load roles:', data.message);
            }
        } catch (error) {
            console.error('Error loading roles:', error);
        }
    },

    // Render roles in select dropdown
    renderRoles(roles) {
        const roleSelect = document.querySelector('select[name="role_id"]');
        if (!roleSelect) return;

        roleSelect.innerHTML = '<option value="">Select a role...</option>' + 
            roles.map(role => `<option value="${role.id}">${role.role_name} - ${role.role_description}</option>`).join('');
    },

    // Create new admin
    async createAdmin() {
        const form = document.getElementById('add-admin-form');
        const formData = new FormData(form);

        // Validate password confirmation
        if (formData.get('password') !== formData.get('confirm_password')) {
            this.showError('Passwords do not match');
            return;
        }

        try {
            const response = await fetch('API/create_admin.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Admin user created successfully');
                this.hideModal('add-admin-modal');
                form.reset();
                this.loadAdmins();
            } else {
                this.showError(data.message || 'Failed to create admin user');
            }
        } catch (error) {
            console.error('Error creating admin:', error);
            this.showError('Error creating admin user');
        }
    },

    // Load admin activity
    async loadActivity() {
        try {
            const response = await fetch('API/get_admin_activity_fixed.php?limit=20');
            const data = await response.json();

            if (data.success) {
                this.renderActivity(data.activities);
            } else {
                console.error('Failed to load activity:', data.message);
            }
        } catch (error) {
            console.error('Error loading activity:', error);
        }
    },

    // Render activity list
    renderActivity(activities) {
        const activityList = document.getElementById('activity-list');
        if (!activityList) return;

        if (activities.length === 0) {
            activityList.innerHTML = '<div class="loading-state"><span>No activity found</span></div>';
            return;
        }

        activityList.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-${this.getActivityIcon(activity.action)}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">${activity.action}</div>
                    <div class="activity-details">${activity.details || 'No details available'}</div>
                </div>
                <div class="activity-time">${this.formatDate(activity.created_at)}</div>
            </div>
        `).join('');
    },

    // Get activity icon based on action
    getActivityIcon(action) {
        const iconMap = {
            'Login': 'sign-in-alt',
            'Logout': 'sign-out-alt',
            'Create Admin': 'user-plus',
            'Update Profile': 'user-edit',
            'Delete Admin': 'user-minus',
            'View Admins': 'users',
            'Update Settings': 'cog',
            'default': 'circle'
        };
        return iconMap[action] || iconMap.default;
    },

    // Refresh admins
    refreshAdmins() {
        this.loadAdmins();
    },

    // Refresh activity
    refreshActivity() {
        this.loadActivity();
    },

    // View admin details
    async viewAdmin(adminId) {
        try {
            const response = await fetch(`API/get_admin_details.php?id=${adminId}`);
            const data = await response.json();

            if (data.success) {
                this.showAdminDetails(data.admin, data.activities);
                this.showModal('view-admin-modal');
            } else {
                this.showError(data.message || 'Failed to load admin details');
            }
        } catch (error) {
            console.error('Error loading admin details:', error);
            this.showError('Error loading admin details');
        }
    },

    // Show admin details in modal
    showAdminDetails(admin, activities) {
        const content = document.getElementById('admin-details-content');
        if (!content) return;

        const roleText = admin.role_id === 1 ? 'Super Admin' : 'Admin';
        const statusText = admin.is_active ? 'Active' : 'Inactive';
        const statusClass = admin.is_active ? 'active' : 'inactive';

        content.innerHTML = `
            <div class="admin-detail-header">
                <div class="admin-detail-avatar">
                    <i class="fas fa-${admin.role_id === 1 ? 'crown' : 'user-shield'}"></i>
                </div>
                <div class="admin-detail-info">
                    <h4>${admin.full_name}</h4>
                    <p>@${admin.username}</p>
                    <span class="role-badge ${admin.role_id === 1 ? 'super' : 'admin'}">
                        <i class="fas fa-${admin.role_id === 1 ? 'crown' : 'user-shield'}"></i>
                        ${roleText}
                    </span>
                </div>
            </div>

            <div class="admin-detail-grid">
                <div class="admin-detail-item">
                    <label>Email</label>
                    <span>${admin.email}</span>
                </div>
                <div class="admin-detail-item">
                    <label>Status</label>
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
                <div class="admin-detail-item">
                    <label>Last Login</label>
                    <span>${admin.last_login ? this.formatDate(admin.last_login) : 'Never'}</span>
                </div>
                <div class="admin-detail-item">
                    <label>Created</label>
                    <span>${this.formatDate(admin.created_at)}</span>
                </div>
            </div>

            <div class="admin-detail-section">
                <h5>Recent Activity</h5>
                <div class="admin-activity-list">
                    ${activities.length > 0 ? activities.map(activity => `
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-${this.getActivityIcon(activity.action)}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">${activity.action}</div>
                                <div class="activity-details">${activity.details || 'No details'}</div>
                            </div>
                            <div class="activity-time">${this.formatDate(activity.created_at)}</div>
                        </div>
                    `).join('') : '<p class="text-muted">No recent activity</p>'}
                </div>
            </div>
        `;
    },

    // Edit admin
    async editAdmin(adminId) {
        try {
            const response = await fetch(`API/get_admin_details.php?id=${adminId}`);
            const data = await response.json();

            if (data.success) {
                this.showEditAdminForm(data.admin);
                this.showModal('edit-admin-modal');
            } else {
                this.showError(data.message || 'Failed to load admin details');
            }
        } catch (error) {
            console.error('Error loading admin details for edit:', error);
            this.showError('Error loading admin details');
        }
    },

    // Show edit admin form
    showEditAdminForm(admin) {
        const form = document.getElementById('edit-admin-form');
        if (!form) return;

        // Populate form fields
        form.querySelector('[name="admin_id"]').value = admin.id;
        form.querySelector('[name="username"]').value = admin.username;
        form.querySelector('[name="email"]').value = admin.email;
        form.querySelector('[name="first_name"]').value = admin.first_name;
        form.querySelector('[name="last_name"]').value = admin.last_name;
        
        // Set role select
        const roleSelect = form.querySelector('[name="role_id"]');
        if (roleSelect) {
            roleSelect.value = admin.role_id;
        }

        // Update modal title
        const modalTitle = document.querySelector('#edit-admin-modal .modal-title');
        if (modalTitle) {
            modalTitle.textContent = `Edit Admin: ${admin.full_name}`;
        }
    },

    // Toggle admin status
    async toggleAdminStatus(adminId, currentStatus) {
        if (!confirm(`Are you sure you want to ${currentStatus ? 'deactivate' : 'activate'} this admin?`)) {
            return;
        }

        try {
            const response = await fetch('API/toggle_admin_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    admin_id: adminId,
                    status: !currentStatus
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(`Admin ${currentStatus ? 'deactivated' : 'activated'} successfully`);
                this.loadAdmins(); // Refresh the admin list
            } else {
                this.showError(data.message || 'Failed to update admin status');
            }
        } catch (error) {
            console.error('Error toggling admin status:', error);
            this.showError('Error updating admin status');
        }
    },

    // Get permissions list for a role
    getPermissionsList(roleId) {
        const permissions = {
            1: [ // Super Admin
                'Full system access',
                'Admin management',
                'All dashboard features',
                'System settings'
            ],
            2: [ // Admin
                'Dashboard access',
                'Order management',
                'Product management',
                'Customer management',
                'Analytics view'
            ]
        };
        
        const rolePermissions = permissions[roleId] || ['Basic access'];
        return rolePermissions.map(perm => `<div class="permission-item"><i class="fas fa-check"></i> ${perm}</div>`).join('');
    },

    // Format date
    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    },

    // Show success message
    showSuccess(message) {
        // Use existing notification system or create one
        if (window.showNotification) {
            window.showNotification(message, 'success');
        } else {
            alert(message);
        }
    },

    // Show error message
    showError(message) {
        if (window.showNotification) {
            window.showNotification(message, 'error');
        } else {
            alert('Error: ' + message);
        }
    },

    // Show modal (delegate to adminModules)
    showModal(modalId) {
        if (window.adminModules && window.adminModules.showModal) {
            window.adminModules.showModal(modalId);
        }
    },

    // Hide modal (delegate to adminModules)
    hideModal(modalId) {
        if (window.adminModules && window.adminModules.hideModal) {
            window.adminModules.hideModal(modalId);
        }
    },

    // Handle edit admin form submission
    async handleEditAdminForm(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            admin_id: formData.get('admin_id'),
            username: formData.get('username'),
            email: formData.get('email'),
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            role_id: parseInt(formData.get('role_id')),
            new_password: formData.get('new_password')
        };

        try {
            const response = await fetch('API/update_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('Admin updated successfully');
                this.hideModal('edit-admin-modal');
                this.loadAdmins(); // Refresh the admin list
            } else {
                this.showError(result.message || 'Failed to update admin');
            }
        } catch (error) {
            console.error('Error updating admin:', error);
            this.showError('Error updating admin');
        }
    }
};

// Export for use in main.js
export default adminManagement;

