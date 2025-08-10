// Admin Profile Module
const adminProfile = {
    // Initialize admin profile
    init() {
        this.loadProfile();
        this.bindEvents();
    },

    // Load admin profile
    async loadProfile() {
        try {
            const response = await fetch('API/get_admin_profile.php');
            const data = await response.json();

            if (data.success) {
                this.renderProfile(data.profile);
                this.populateEditForm(data.profile);
            } else {
                console.error('Failed to load profile:', data.message);
                this.showError('Failed to load profile');
            }
        } catch (error) {
            console.error('Error loading profile:', error);
            this.showError('Error loading profile');
        }
    },

    // Render profile information
    renderProfile(profile) {
        // Update profile display elements
        const profileElements = {
            'profile-username': profile.username,
            'profile-email': profile.email,
            'profile-full-name': profile.full_name,
            'profile-role': profile.role_name,
            'profile-status': profile.is_active ? 'Active' : 'Inactive',
            'profile-last-login': profile.last_login ? this.formatDate(profile.last_login) : 'Never',
            'profile-created': this.formatDate(profile.created_at),
            'profile-role-description': profile.role_description || 'No description available'
        };

        // Update each element if it exists
        Object.keys(profileElements).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = profileElements[id];
            }
        });

        // Update status badge
        const statusBadge = document.getElementById('profile-status-badge');
        if (statusBadge) {
            statusBadge.className = `badge ${profile.is_active ? 'badge-success' : 'badge-danger'}`;
            statusBadge.textContent = profile.is_active ? 'Active' : 'Inactive';
        }

        // Update profile avatar/name in header
        const headerName = document.getElementById('header-admin-name');
        if (headerName) {
            headerName.textContent = profile.full_name;
        }

        // Update profile dropdown
        const dropdownName = document.getElementById('dropdown-admin-name');
        const dropdownRole = document.getElementById('dropdown-admin-role');
        
        if (dropdownName) {
            dropdownName.textContent = profile.full_name;
        }
        
        if (dropdownRole) {
            const roleText = profile.role_id === 1 ? 'Super Admin' : 'Admin';
            dropdownRole.textContent = roleText;
        }

        // Store profile data for later use
        this.currentProfile = profile;
    },

    // Populate edit form with current profile data
    populateEditForm(profile) {
        const form = document.getElementById('edit-profile-form');
        if (!form) return;

        // Populate basic fields
        const fields = ['username', 'first_name', 'last_name', 'email'];
        fields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.value = profile[field];
            }
        });

        // Clear password fields
        const passwordFields = ['current_password', 'new_password', 'confirm_password'];
        passwordFields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.value = '';
            }
        });
    },

    // Bind event listeners
    bindEvents() {
        // Edit profile form submission
        const editForm = document.getElementById('edit-profile-form');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateProfile();
            });
        }

        // Password confirmation validation
        const newPasswordField = document.querySelector('input[name="new_password"]');
        const confirmPasswordField = document.querySelector('input[name="confirm_password"]');
        
        if (newPasswordField && confirmPasswordField) {
            confirmPasswordField.addEventListener('input', () => {
                if (newPasswordField.value !== confirmPasswordField.value) {
                    confirmPasswordField.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordField.setCustomValidity('');
                }
            });

            newPasswordField.addEventListener('input', () => {
                if (confirmPasswordField.value && newPasswordField.value !== confirmPasswordField.value) {
                    confirmPasswordField.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordField.setCustomValidity('');
                }
            });
        }

        // Show/hide password fields
        const changePasswordToggle = document.getElementById('change-password-toggle');
        const passwordFields = document.getElementById('password-fields');
        
        if (changePasswordToggle && passwordFields) {
            changePasswordToggle.addEventListener('change', () => {
                passwordFields.style.display = changePasswordToggle.checked ? 'block' : 'none';
                
                // Clear password fields when hiding
                if (!changePasswordToggle.checked) {
                    const fields = ['current_password', 'new_password', 'confirm_password'];
                    fields.forEach(field => {
                        const input = document.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.value = '';
                            input.setCustomValidity('');
                        }
                    });
                }
            });
        }
    },

    // Update admin profile
    async updateProfile() {
        const form = document.getElementById('edit-profile-form');
        if (!form) return;

        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // Validate required fields
        if (!data.username || !data.first_name || !data.last_name || !data.email) {
            this.showError('Please fill in all required fields');
            return;
        }

        // Validate email format
        if (!this.isValidEmail(data.email)) {
            this.showError('Please enter a valid email address');
            return;
        }

        // Validate password change if requested
        const changePassword = document.getElementById('change-password-toggle')?.checked;
        if (changePassword) {
            if (!data.current_password) {
                this.showError('Current password is required to change password');
                return;
            }
            if (!data.new_password) {
                this.showError('New password is required');
                return;
            }
            if (data.new_password.length < 6) {
                this.showError('New password must be at least 6 characters long');
                return;
            }
            if (data.new_password !== data.confirm_password) {
                this.showError('New password and confirmation do not match');
                return;
            }
        }

        try {
            // Add admin_id to the request
            const adminUserStr = localStorage.getItem('adminUser');
            if (adminUserStr) {
                const adminUser = JSON.parse(adminUserStr);
                data.admin_id = adminUser.id;
            }

            const response = await fetch('API/update_admin_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('Profile updated successfully');
                this.hideModal('edit-profile-modal');
                
                // Reload profile to show updated information
                this.loadProfile();
                
                // Update localStorage if needed
                this.updateLocalStorage(data);
                
                // Reset form
                form.reset();
                if (document.getElementById('change-password-toggle')) {
                    document.getElementById('change-password-toggle').checked = false;
                    document.getElementById('password-fields').style.display = 'none';
                }
            } else {
                this.showError(result.message || 'Failed to update profile');
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            this.showError('Error updating profile');
        }
    },

    // Update localStorage with new profile data
    updateLocalStorage(data) {
        const adminUserStr = localStorage.getItem('adminUser');
        if (adminUserStr) {
            try {
                const adminUser = JSON.parse(adminUserStr);
                adminUser.username = data.username;
                adminUser.first_name = data.first_name;
                adminUser.last_name = data.last_name;
                adminUser.full_name = `${data.first_name} ${data.last_name}`;
                adminUser.email = data.email;
                localStorage.setItem('adminUser', JSON.stringify(adminUser));
            } catch (error) {
                console.error('Error updating localStorage:', error);
            }
        }
    },

    // Validate email format
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Format date
    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    },

    // Show success message
    showSuccess(message) {
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

    // Show modal
    showModal(modalId) {
        if (window.adminModules && window.adminModules.showModal) {
            window.adminModules.showModal(modalId);
        }
    },

    // Hide modal
    hideModal(modalId) {
        if (window.adminModules && window.adminModules.hideModal) {
            window.adminModules.hideModal(modalId);
        }
    },

    // Refresh profile
    refreshProfile() {
        this.loadProfile();
    }
};

// Export for use in main.js
export default adminProfile;

