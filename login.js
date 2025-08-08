 // Parse URL parameters on page load
        const urlParams = new URLSearchParams(window.location.search);
        const redirect = urlParams.get('redirect');
        const returnToCheckout = urlParams.get('return_to_checkout');
        const error = urlParams.get('error');
        const success = urlParams.get('success');
        const message = urlParams.get('message');

        // Set hidden fields with redirect parameters
        function setRedirectFields() {
            if (redirect) {
                document.getElementById('redirectField').value = redirect;
                document.getElementById('signupRedirectField').value = redirect;
            }
            if (returnToCheckout) {
                document.getElementById('returnToCheckoutField').value = returnToCheckout;
                document.getElementById('signupReturnToCheckoutField').value = returnToCheckout;
            }
        }

        // Initialize redirect fields
        setRedirectFields();

        // Password toggle functionality
        function initializePasswordToggles() {
            const passwordToggles = document.querySelectorAll('.password-toggle');
            
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
        }

        // Show alert messages
        function showAlert(message, type) {
            const alertElement = type === 'error' ? 
                document.getElementById('generalError') : 
                document.getElementById('successMessage');
            
            alertElement.textContent = message;
            alertElement.classList.add('show');
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    alertElement.classList.remove('show');
                }, 5000);
            }
        }

        // Show error or success messages from URL
        if (error) {
            showAlert(decodeURIComponent(error), 'error');
        }
        if (success && message) {
            showAlert(decodeURIComponent(message), 'success');
        }

        // Update auth link text based on current form
        function updateAuthLink() {
            const authLink = document.getElementById('auth-link');
            const currentForm = document.querySelector('.form-section.active');
            
            if (currentForm && currentForm.id === 'signUpForm') {
                authLink.textContent = 'Sign In';
            } else {
                authLink.textContent = 'Sign Up';
            }
        }

        // Form switching functionality
        function switchForm(hideFormId, showFormId) {
            const hideForm = document.getElementById(hideFormId);
            const showForm = document.getElementById(showFormId);
            
            hideForm.classList.remove('active');
            setTimeout(() => {
                showForm.classList.add('active');
                // Clear any error messages
                document.getElementById('generalError').classList.remove('show');
                document.getElementById('successMessage').classList.remove('show');
                // Clear form validation errors
                clearFormErrors();
                // Update auth link
                updateAuthLink();
                // Initialize password toggles for new form
                initializePasswordToggles();
            }, 250);
        }

        // Clear form validation errors
        function clearFormErrors() {
            const errorMessages = document.querySelectorAll('.error-message');
            const inputFields = document.querySelectorAll('input.error');
            
            errorMessages.forEach(error => {
                error.classList.remove('show');
                error.textContent = '';
            });
            
            inputFields.forEach(input => {
                input.classList.remove('error');
            });
        }

        // Show field-specific error
        function showFieldError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorElement = field.parentElement.querySelector('.error-message');
            
            field.classList.add('error');
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }

        // Validate email format
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Validate phone number format (basic validation)
        function isValidPhone(phone) {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
            return phoneRegex.test(phone);
        }

        // Validate password strength
        function validatePassword(password) {
            if (password.length < 6) {
                return 'Password must be at least 6 characters long';
            }
            return null;
        }

        // Event listeners for form switching
        document.getElementById('switchToSignUpLink').addEventListener('click', (e) => {
            e.preventDefault();
            switchForm('loginForm', 'signUpForm');
        });

        document.getElementById('switchToLoginLink').addEventListener('click', (e) => {
            e.preventDefault();
            switchForm('signUpForm', 'loginForm');
        });

        document.getElementById('forgotPasswordLink').addEventListener('click', (e) => {
            e.preventDefault();
            switchForm('loginForm', 'forgotPasswordForm');
        });

        document.getElementById('backToLoginLink').addEventListener('click', (e) => {
            e.preventDefault();
            switchForm('forgotPasswordForm', 'loginForm');
        });

        // Google login button handlers
        document.getElementById('googleLoginBtn').addEventListener('click', function() {
            // Show loading state
            this.classList.add('loading');
            this.disabled = true;
            
            // Redirect to Google OAuth endpoint
            const params = new URLSearchParams();
            if (redirect) params.append('redirect', redirect);
            if (returnToCheckout) params.append('return_to_checkout', returnToCheckout);
            
            const googleAuthUrl = `/jowaki_electrical_srvs/api/google_auth.php?${params.toString()}`;
            window.location.href = googleAuthUrl;
        });

        document.getElementById('googleSignupBtn').addEventListener('click', function() {
            // Show loading state
            this.classList.add('loading');
            this.disabled = true;
            
            // Redirect to Google OAuth endpoint with signup flag
            const params = new URLSearchParams();
            params.append('signup', 'true');
            if (redirect) params.append('redirect', redirect);
            if (returnToCheckout) params.append('return_to_checkout', returnToCheckout);
            
            const googleAuthUrl = `/jowaki_electrical_srvs/api/google_auth.php?${params.toString()}`;
            window.location.href = googleAuthUrl;
        });

        // Handle login form submission
        document.getElementById('loginFormElement').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Clear previous errors
            clearFormErrors();
            
            // Basic validation
            let hasErrors = false;
            if (!email) {
                showFieldError('loginEmail', 'Email is required');
                hasErrors = true;
            } else if (!isValidEmail(email)) {
                showFieldError('loginEmail', 'Please enter a valid email address');
                hasErrors = true;
            }
            
            if (!password) {
                showFieldError('loginPassword', 'Password is required');
                hasErrors = true;
            }
            
            if (hasErrors) {
                return;
            }
            
            // Show loading state
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            
            // Clear previous alerts
            document.getElementById('generalError').classList.remove('show');
            document.getElementById('successMessage').classList.remove('show');
            
            fetch('/jowaki_electrical_srvs/api/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Login successful! Redirecting...', 'success');
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showAlert(data.error || 'Login failed. Please try again.', 'error');
                    // Reset button state
                    submitButton.classList.remove('loading');
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                // Reset button state
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
            });
        });

        // Handle signup form submission
        document.getElementById('signUpFormElement').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Get form values
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('signUpEmail').value.trim();
            const phone = document.getElementById('phoneNumber').value.trim();
            const password = document.getElementById('signUpPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const termsChecked = this.querySelector('input[name="terms"]').checked;
            
            // Clear previous errors
            clearFormErrors();
            
            // Validation
            let hasErrors = false;
            
            if (!firstName) {
                showFieldError('firstName', 'First name is required');
                hasErrors = true;
            }
            
            if (!lastName) {
                showFieldError('lastName', 'Last name is required');
                hasErrors = true;
            }
            
            if (!email) {
                showFieldError('signUpEmail', 'Email is required');
                hasErrors = true;
            } else if (!isValidEmail(email)) {
                showFieldError('signUpEmail', 'Please enter a valid email address');
                hasErrors = true;
            }
            
            if (!phone) {
                showFieldError('phoneNumber', 'Phone number is required');
                hasErrors = true;
            } else if (!isValidPhone(phone)) {
                showFieldError('phoneNumber', 'Please enter a valid phone number');
                hasErrors = true;
            }
            
            if (!password) {
                showFieldError('signUpPassword', 'Password is required');
                hasErrors = true;
            } else {
                const passwordError = validatePassword(password);
                if (passwordError) {
                    showFieldError('signUpPassword', passwordError);
                    hasErrors = true;
                }
            }
            
            if (!confirmPassword) {
                showFieldError('confirmPassword', 'Please confirm your password');
                hasErrors = true;
            } else if (password !== confirmPassword) {
                showFieldError('confirmPassword', 'Passwords do not match');
                hasErrors = true;
            }
            
            if (!termsChecked) {
                const termsCheckbox = this.querySelector('input[name="terms"]');
                const errorElement = termsCheckbox.parentElement.parentElement.querySelector('.error-message');
                errorElement.textContent = 'You must agree to the Terms of Service and Privacy Policy';
                errorElement.classList.add('show');
                hasErrors = true;
            }
            
            if (hasErrors) {
                return;
            }
            
            // Show loading state
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            
            // Clear previous alerts
            document.getElementById('generalError').classList.remove('show');
            document.getElementById('successMessage').classList.remove('show');
            
            fetch('/jowaki_electrical_srvs/api/signup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // For signup, we expect a redirect, not JSON
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.text();
                }
            })
            .then(text => {
                if (text) {
                    // If we get text back, it might be an error page
                    console.error('Signup response:', text);
                    showAlert('An error occurred during signup. Please try again.', 'error');
                    submitButton.classList.remove('loading');
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Signup error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                // Reset button state
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
            });
        });

        // Handle password reset form submission
        document.getElementById('resetPasswordFormElement').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const email = document.getElementById('resetEmail').value.trim();
            
            // Clear previous errors
            clearFormErrors();
            
            // Validation
            let hasErrors = false;
            if (!email) {
                showFieldError('resetEmail', 'Email is required');
                hasErrors = true;
            } else if (!isValidEmail(email)) {
                showFieldError('resetEmail', 'Please enter a valid email address');
                hasErrors = true;
            }
            
            if (hasErrors) {
                return;
            }
            
            // Show loading state
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            
            // Clear previous alerts
            document.getElementById('generalError').classList.remove('show');
            document.getElementById('successMessage').classList.remove('show');
            
            fetch('/jowaki_electrical_srvs/api/reset_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Password reset link sent! Check your email for instructions.', 'success');
                    
                    // Switch back to login form after delay
                    setTimeout(() => {
                        switchForm('forgotPasswordForm', 'loginForm');
                    }, 3000);
                } else {
                    showAlert(data.error || 'Failed to send reset link. Please try again.', 'error');
                }
                
                // Reset button state
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
            })
            .catch(error => {
                console.error('Password reset error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                // Reset button state
                submitButton.classList.remove('loading');
                submitButton.disabled = false;
            });
        });

        // Real-time validation for better UX
        document.getElementById('loginEmail').addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !isValidEmail(email)) {
                showFieldError('loginEmail', 'Please enter a valid email address');
            } else if (email) {
                this.classList.remove('error');
                const errorElement = this.parentElement.querySelector('.error-message');
                errorElement.classList.remove('show');
            }
        });

        document.getElementById('signUpEmail').addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !isValidEmail(email)) {
                showFieldError('signUpEmail', 'Please enter a valid email address');
            } else if (email) {
                this.classList.remove('error');
                const errorElement = this.parentElement.querySelector('.error-message');
                errorElement.classList.remove('show');
            }
        });

        document.getElementById('phoneNumber').addEventListener('blur', function() {
            const phone = this.value.trim();
            if (phone && !isValidPhone(phone)) {
                showFieldError('phoneNumber', 'Please enter a valid phone number');
            } else if (phone) {
                this.classList.remove('error');
                const errorElement = this.parentElement.querySelector('.error-message');
                errorElement.classList.remove('show');
            }
        });

        document.getElementById('confirmPassword').addEventListener('blur', function() {
            const password = document.getElementById('signUpPassword').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                showFieldError('confirmPassword', 'Passwords do not match');
            } else if (confirmPassword) {
                this.classList.remove('error');
                const errorElement = this.parentElement.querySelector('.error-message');
                errorElement.classList.remove('show');
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize auth link
            updateAuthLink();
            
            // Initialize password toggles
            initializePasswordToggles();
            
            // Handle auth link click in header
            document.getElementById('auth-link').addEventListener('click', function(e) {
                e.preventDefault();
                const currentActive = document.querySelector('.form-section.active');
                
                if (currentActive.id === 'loginForm' || currentActive.id === 'forgotPasswordForm') {
                    switchForm(currentActive.id, 'signUpForm');
                } else {
                    switchForm('signUpForm', 'loginForm');
                }
            });
            
            // Form switching event listeners
            document.getElementById('switchToSignUpLink').addEventListener('click', function(e) {
                e.preventDefault();
                switchForm('loginForm', 'signUpForm');
            });
            
            document.getElementById('switchToLoginLink').addEventListener('click', function(e) {
                e.preventDefault();
                switchForm('signUpForm', 'loginForm');
            });
            
            document.getElementById('forgotPasswordLink').addEventListener('click', function(e) {
                e.preventDefault();
                switchForm('loginForm', 'forgotPasswordForm');
            });
            
            document.getElementById('backToLoginLink').addEventListener('click', function(e) {
                e.preventDefault();
                switchForm('forgotPasswordForm', 'loginForm');
            });
        });