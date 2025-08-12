// Admin Management Module
const adminManagement = {
    init() {
        this.loadAdmins();
        this.loadActivity();
        this.bindEvents();
    },

    bindEvents() {
        // Add admin form submission
        const addAdminForm = document.getElementById('add-admin-form');
        if (addAdminForm) {
            addAdminForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.createAdmin();
            });
        }

        // Password confirmation validation
        const passwordInput = document.querySelector('input[name="password"]');
        const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
        
        if (passwordInput && confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', () => {
                if (passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            });
        }
    },

    async loadAdmins() {
        try {
            const response = await fetch('API/get_admins.php');
            const data = await response.json();
            
            if (data.success) {
                this.renderAdmins(data.admins);
                this.updateAdminStats(data.admins);
            } else {
                console.error('Failed to load admins:', data.message);
            }
        } catch (error) {
            console.error('Error loading admins:', error);
        }
    },

    renderAdmins(admins) {
        const tbody = document.getElementById('admins-table-body');
        if (!tbody) return;

        if (admins.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No admins found</td></tr>';
            return;
        }

        tbody.innerHTML = admins.map(admin => `
            <tr>
                <td>
                    <div class="user-info">
                        <div class="user-name">${admin.full_name}</div>
                    </div>
                </td>
                <td>${admin.username}</td>
                <td>${admin.email}</td>
                <td>
                    <span class="status-badge ${admin.is_active ? 'active' : 'inactive'}">
                        ${admin.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>${admin.last_login ? this.formatDate(admin.last_login) : 'Never'}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-secondary" onclick="adminManagement.viewAdmin(${admin.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="adminManagement.editAdmin(${admin.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm ${admin.is_active ? 'btn-warning' : 'btn-success'}" 
                                onclick="adminManagement.toggleAdminStatus(${admin.id}, ${admin.is_active})" 
                                title="${admin.is_active ? 'Deactivate' : 'Activate'}">
                            <i class="fas fa-${admin.is_active ? 'ban' : 'check'}"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    updateAdminStats(admins) {
        const totalAdmins = admins.length;
        const activeAdmins = admins.filter(admin => admin.is_active).length;
        const recentLogins = admins.filter(admin => {
            if (!admin.last_login) return false;
            const lastLogin = new Date(admin.last_login);
            const now = new Date();
            const diffHours = (now - lastLogin) / (1000 * 60 * 60);
            return diffHours <= 24;
        }).length;

        document.getElementById('total-admins').textContent = totalAdmins;
        document.getElementById('active-admins').textContent = activeAdmins;
        document.getElementById('recent-logins').textContent = recentLogins;
    },

    async loadActivity() {
        try {
            const response = await fetch('API/get_admin_activity.php');
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

    renderActivity(activities) {
        const tbody = document.getElementById('activity-table-body');
        if (!tbody) return;

        if (activities.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No activity found</td></tr>';
            return;
        }

        tbody.innerHTML = activities.map(activity => `
            <tr>
                <td>${activity.admin_name || 'System'}</td>
                <td>${activity.action}</td>
                <td>${activity.details || '-'}</td>
                <td>${activity.ip_address || '-'}</td>
                <td>${this.formatDate(activity.created_at)}</td>
            </tr>
        `).join('');
    },

    async createAdmin() {
        const form = document.getElementById('add-admin-form');
        const formData = new FormData(form);
        
        // Validate password confirmation
        const password = formData.get('password');
        const confirmPassword = formData.get('confirm_password');
        
        if (password !== confirmPassword) {
            this.showError('Passwords do not match');
            return;
        }

        const data = {
            username: formData.get('username'),
            email: formData.get('email'),
            password: password,
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            is_active: formData.get('is_active') === 'on'
        };

        try {
            const response = await fetch('API/create_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('Admin created successfully');
                adminModules.hideModal('add-admin-modal');
                form.reset();
                this.loadAdmins();
            } else {
                this.showError(result.message || 'Failed to create admin');
            }
        } catch (error) {
            console.error('Error creating admin:', error);
            this.showError('Network error. Please try again.');
        }
    },

    refreshAdmins() {
        this.loadAdmins();
    },

    refreshActivity() {
        this.loadActivity();
    },

    viewAdmin(adminId) {
        // TODO: Implement admin details view
        console.log('View admin:', adminId);
    },

    editAdmin(adminId) {
        // TODO: Implement admin editing
        console.log('Edit admin:', adminId);
    },

    toggleAdminStatus(adminId, currentStatus) {
        // TODO: Implement admin status toggle
        console.log('Toggle admin status:', adminId, currentStatus);
    },

    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString();
    },

    showSuccess(message) {
        if (window.adminModules && window.adminModules.showSuccess) {
            window.adminModules.showSuccess(message);
        } else {
            alert('Success: ' + message);
        }
    },

    showError(message) {
        if (window.adminModules && window.adminModules.showError) {
            window.adminModules.showError(message);
        } else {
            alert('Error: ' + message);
        }
    }
};

// Export for use in main.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = adminManagement;
} else {
    window.adminManagement = adminManagement;
}


