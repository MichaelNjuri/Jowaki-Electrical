// Form Management and Validation - Single Instance
class FormValidator {
    constructor() {
        this.validators = {
            email: this.validateEmail,
            password: this.validatePassword,
            name: this.validateName,
            phone: this.validatePhone,
            confirmPassword: this.validateConfirmPassword
        };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupInputRestrictions();
        this.setupFormSwitching();
        this.initializePage();
    }
    
    initializePage() {
        // Ensure login form is shown by default
        const loginForm = document.getElementById('loginForm');
        const signUpForm = document.getElementById('signUpForm');
        if (loginForm && signUpForm) {
            loginForm.classList.add('active');
            signUpForm.classList.remove('active');
        }
    }
    
    setupFormSwitching() {
        // Switch to sign up
        const switchToSignUpLink = document.getElementById('switchToSignUpLink');
        if (switchToSignUpLink) {
            switchToSignUpLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchToSignUp();
            });
        }
        
        // Switch to login
        const switchToLoginLink = document.getElementById('switchToLoginLink');
        if (switchToLoginLink) {
            switchToLoginLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchToLogin();
            });
        }
        
        // Forgot password
        const forgotPasswordLink = document.getElementById('forgotPasswordLink');
        if (forgotPasswordLink) {
            forgotPasswordLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.showForgotPassword();
            });
        }
        
        // Terms and privacy links
        const showTermsLink = document.getElementById('showTermsLink');
        const showPrivacyLink = document.getElementById('showPrivacyLink');
        
        if (showTermsLink) {
            showTermsLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.showTerms();
            });
        }
        
        if (showPrivacyLink) {
            showPrivacyLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.showPrivacy();
            });
        }
    }
    
    // Input Restrictions for specific fields
    setupInputRestrictions() {
        // Phone number - only allow digits, spaces, +, -, (, )
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', (e) => {
                let value = e.target.value;
                value = value.replace(/[^0-9+\-\s\(\)]/g, '');
                e.target.value = value;
            });
            
            phoneInput.addEventListener('keypress', (e) => {
                const allowedChars = /[0-9+\-\s\(\)]/;
                if (!allowedChars.test(e.key) && !this.isControlKey(e)) {
                    e.preventDefault();
                }
            });
        }
        
        // Name fields - only allow letters and spaces
        ['firstName', 'lastName'].forEach(fieldId => {
            const input = document.getElementById(fieldId);
            if (input) {
                input.addEventListener('input', (e) => {
                    let value = e.target.value;
                    value = value.replace(/[^A-Za-z\s]/g, '');
                    e.target.value = value;
                });
                
                input.addEventListener('keypress', (e) => {
                    const allowedChars = /[A-Za-z\s]/;
                    if (!allowedChars.test(e.key) && !this.isControlKey(e)) {
                        e.preventDefault();
                    }
                });
            }
        });
    }
    
    isControlKey(e) {
        const controlKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'];
        return controlKeys.includes(e.key) || e.ctrlKey || e.metaKey;
    }
    
    setupEventListeners() {
        // Form submissions
        const loginForm = document.getElementById('loginFormElement');
        const signUpForm = document.getElementById('signUpFormElement');
        
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }
        
        if (signUpForm) {
            signUpForm.addEventListener('submit', (e) => this.handleSignUp(e));
        }
        
        // Real-time validation on blur and input
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', (e) => {
                this.clearFieldError(e.target);
                // Real-time validation for confirm password
                if (e.target.id === 'confirmPassword' || e.target.id === 'signUpPassword') {
                    this.validatePasswordMatch();
                }
            });
            
            input.addEventListener('blur', (e) => {
                if (e.target.value.trim()) {
                    this.validateField(e.target);
                }
            });
            
            input.addEventListener('focus', (e) => {
                const formGroup = e.target.closest('.form-group');
                if (formGroup) {
                    formGroup.style.transform = 'scale(1.02)';
                    formGroup.style.transition = 'transform 0.2s ease';
                }
            });
            
            input.addEventListener('blur', (e) => {
                const formGroup = e.target.closest('.form-group');
                if (formGroup) {
                    formGroup.style.transform = 'scale(1)';
                }
            });
        });
    }
    
    validatePasswordMatch() {
        const passwordInput = document.getElementById('signUpPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        
        if (passwordInput && confirmPasswordInput) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.showFieldError(confirmPasswordInput, 'Passwords do not match');
            } else if (confirmPassword && password === confirmPassword) {
                this.clearFieldError(confirmPasswordInput);
            }
        }
    }
    
    validateField(input) {
        const fieldType = this.getFieldType(input);
        const value = input.value.trim();
        
        if (!value && input.required) {
            this.showFieldError(input, this.getRequiredMessage(input));
            return false;
        }
        
        if (value && this.validators[fieldType]) {
            const result = this.validators[fieldType].call(this, value, input);
            if (!result.isValid) {
                this.showFieldError(input, result.message);
                return false;
            }
        }
        
        this.clearFieldError(input);
        return true;
    }
    
    getFieldType(input) {
        if (input.type === 'email') return 'email';
        if (input.type === 'password') {
            if (input.id === 'confirmPassword') return 'confirmPassword';
            return 'password';
        }
        if (input.type === 'tel') return 'phone';
        if (input.id === 'firstName' || input.id === 'lastName') return 'name';
        return 'text';
    }
    
    getRequiredMessage(input) {
        const fieldMap = {
            'loginEmail': 'Email address',
            'loginPassword': 'Password',
            'firstName': 'First name',
            'lastName': 'Last name',
            'signUpEmail': 'Email address',
            'phone': 'Phone number',
            'signUpPassword': 'Password',
            'confirmPassword': 'Password confirmation',
            'terms': 'You must agree to the terms'
        };
        
        return `${fieldMap[input.id] || 'This field'} is required`;
    }
    
    // Validation Methods
    validateEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return { isValid: emailRegex.test(email), message: 'Please enter a valid email address' };
    }
    
    validatePassword(password, input) {
        if (password.length < 8) {
            return { isValid: false, message: 'Password must be at least 8 characters long' };
        }
        if (input.id === 'signUpPassword') {
            if (!/(?=.*[a-z])/.test(password)) {
                return { isValid: false, message: 'Password must contain at least one lowercase letter' };
            }
            if (!/(?=.*[A-Z])/.test(password)) {
                return { isValid: false, message: 'Password must contain at least one uppercase letter' };
            }
            if (!/(?=.*\d)/.test(password)) {
                return { isValid: false, message: 'Password must contain at least one number' };
            }
        }
        return { isValid: true };
    }
    
    validateName(name) {
        if (name.length < 2) {
            return { isValid: false, message: 'Name must be at least 2 characters long' };
        }
        if (!/^[A-Za-z\s]+$/.test(name)) {
            return { isValid: false, message: 'Name can only contain letters and spaces' };
        }
        return { isValid: true };
    }
    
    validatePhone(phone) {
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
        if (cleanPhone.length < 10) {
            return { isValid: false, message: 'Phone number must be at least 10 digits' };
        }
        if (!/^[\+]?[0-9]{10,15}$/.test(cleanPhone)) {
            return { isValid: false, message: 'Please enter a valid phone number' };
        }
        return { isValid: true };
    }
    
    validateConfirmPassword(confirmPassword) {
        const password = document.getElementById('signUpPassword').value;
        return { isValid: password === confirmPassword, message: 'Passwords do not match' };
    }
    
    showFieldError(input, message) {
        const errorElement = document.getElementById(input.id + 'Error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }
        input.classList.add('error');
        input.classList.remove('valid');
    }
    
    clearFieldError(input) {
        const errorElement = document.getElementById(input.id + 'Error');
        if (errorElement) {
            errorElement.classList.remove('show');
            errorElement.textContent = '';
        }
        input.classList.remove('error');
        if (input.value.trim()) {
            input.classList.add('valid');
        } else {
            input.classList.remove('valid');
        }
    }
    
    clearAllErrors() {
        document.querySelectorAll('.error-message').forEach(msg => {
            msg.classList.remove('show');
            msg.textContent = '';
        });
        document.querySelectorAll('input').forEach(input => {
            input.classList.remove('error', 'valid');
        });
    }
    
    handleLogin(e) {
        e.preventDefault();
        this.clearAllErrors();
        
        const emailInput = document.getElementById('loginEmail');
        const passwordInput = document.getElementById('loginPassword');
        let isValid = true;
        
        // Validate email
        if (!this.validateField(emailInput)) {
            isValid = false;
        }
        
        // Validate password (basic validation for login)
        if (!passwordInput.value.trim()) {
            this.showFieldError(passwordInput, 'Password is required');
            isValid = false;
        } else if (passwordInput.value.length < 8) {
            this.showFieldError(passwordInput, 'Password must be at least 8 characters long');
            isValid = false;
        }
        
        if (isValid) {
            // Simulate login (replace with actual backend API call)
            alert('Login successful!');
            emailInput.value = '';
            passwordInput.value = '';
            this.clearAllErrors();
        }
    }
    
    handleSignUp(e) {
        e.preventDefault();
        this.clearAllErrors();
        
        const inputs = [
            document.getElementById('firstName'),
            document.getElementById('lastName'),
            document.getElementById('signUpEmail'),
            document.getElementById('phone'),
            document.getElementById('signUpPassword'),
            document.getElementById('confirmPassword')
        ];
        
        const termsInput = document.getElementById('terms');
        let isValid = true;
        
        // Validate all fields
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        // Validate terms checkbox
        if (!termsInput.checked) {
            this.showFieldError(termsInput, 'You must agree to the terms');
            isValid = false;
        }
        
        if (isValid) {
            // Simulate account creation (replace with actual backend API call)
            const successMessage = document.getElementById('successMessage');
            successMessage.classList.add('show');
            setTimeout(() => {
                successMessage.classList.remove('show');
                this.switchToLogin();
                document.getElementById('signUpFormElement').reset();
            }, 3000);
        }
    }
    
    // Form switching methods
    switchToSignUp() {
        const loginForm = document.getElementById('loginForm');
        const signUpForm = document.getElementById('signUpForm');
        
        loginForm.classList.remove('active');
        signUpForm.classList.add('active');
        
        document.getElementById('loginFormElement').reset();
        document.getElementById('successMessage').classList.remove('show');
        this.clearAllErrors();
    }
    
    switchToLogin() {
        const loginForm = document.getElementById('loginForm');
        const signUpForm = document.getElementById('signUpForm');
        
        signUpForm.classList.remove('active');
        loginForm.classList.add('active');
        
        document.getElementById('signUpFormElement').reset();
        document.getElementById('successMessage').classList.remove('show');
        this.clearAllErrors();
    }
    
    showForgotPassword() {
        const loginForm = document.getElementById('loginForm');
        const signUpForm = document.getElementById('signUpForm');
        
        loginForm.classList.remove('active');
        signUpForm.classList.remove('active');
        
        // Remove existing forgot password form if it exists
        const existingForgotForm = document.querySelector('.forgot-password-form');
        if (existingForgotForm) {
            existingForgotForm.remove();
        }
        
        // Create forgot password form
        const formContainer = document.querySelector('.form-container');
        const forgotPasswordDiv = document.createElement('div');
        forgotPasswordDiv.className = 'form-section active forgot-password-form';
        forgotPasswordDiv.innerHTML = `
            <h2 class="form-title">Reset Password</h2>
            <form id="forgotPasswordForm" novalidate>
                <div class="form-group">
                    <label for="resetEmail">Email Address</label>
                    <input type="email" id="resetEmail" name="email" required autocomplete="email" spellcheck="false" maxlength="254" aria-describedby="resetEmailError">
                    <div class="error-message" id="resetEmailError" role="alert" aria-live="polite"></div>
                </div>
                <button type="submit" class="btn">Send Reset Link</button>
                <div class="switch-form">
                    <a href="#" id="backToLoginLink">Back to Sign In</a>
                </div>
            </form>
        `;
        formContainer.appendChild(forgotPasswordDiv);
        
        // Add event listeners for the forgot password form
        const resetForm = document.getElementById('forgotPasswordForm');
        const backToLoginLink = document.getElementById('backToLoginLink');
        
        resetForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const emailInput = document.getElementById('resetEmail');
            if (this.validateField(emailInput)) {
                alert('Password reset link sent to your email!');
                forgotPasswordDiv.remove();
                this.switchToLogin();
            }
        });
        
        backToLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            forgotPasswordDiv.remove();
            this.switchToLogin();
        });
        
        // Setup validation for the reset email field
        const resetEmailInput = document.getElementById('resetEmail');
        resetEmailInput.addEventListener('input', (e) => {
            this.clearFieldError(e.target);
        });
        
        resetEmailInput.addEventListener('blur', (e) => {
            if (e.target.value.trim()) {
                this.validateField(e.target);
            }
        });
    }
    
    showTerms() {
        alert('Terms of Service:\n\nBy using this service, you agree to our terms and conditions. ');
    }
    
    showPrivacy() {
        alert('Privacy Policy:\n\nWe respect your privacy and are committed to protecting your personal data.');
    }
}

// Initialize the form validator when the page loads
document.addEventListener('DOMContentLoaded', () => {
    new FormValidator();
});