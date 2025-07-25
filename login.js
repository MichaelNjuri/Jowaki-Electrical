document.addEventListener('DOMContentLoaded', () => {
    // Check for success message from signup
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === 'true') {
        const signUpForm = document.getElementById('signUpForm');
        const loginForm = document.getElementById('loginForm');
        const successMessage = document.getElementById('successMessage');
        
        if (signUpForm && loginForm && successMessage) {
            signUpForm.classList.add('active');
            loginForm.classList.remove('active');
            successMessage.classList.add('show');
            setTimeout(() => {
                successMessage.classList.remove('show');
                signUpForm.classList.remove('active');
                loginForm.classList.add('active');
                document.getElementById('signUpFormElement').reset();
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 3000);
        }
    }

    // Check for error messages from URL
    if (urlParams.get('error')) {
        const errorMessage = urlParams.get('error');
        showError(decodeURIComponent(errorMessage));
    }

    // Form switching
    document.getElementById('switchToSignUpLink')?.addEventListener('click', (e) => {
        e.preventDefault();
        switchToForm('signUpForm');
    });

    document.getElementById('switchToLoginLink')?.addEventListener('click', (e) => {
        e.preventDefault();
        switchToForm('loginForm');
    });

    document.getElementById('forgotPasswordLink')?.addEventListener('click', (e) => {
        e.preventDefault();
        switchToForm('forgotPasswordForm');
    });

    document.getElementById('backToLoginLink')?.addEventListener('click', (e) => {
        e.preventDefault();
        switchToForm('loginForm');
    });

    // Helper functions
    function switchToForm(targetFormId) {
        const forms = document.querySelectorAll('.form-section');
        forms.forEach(form => form.classList.remove('active'));
        
        const targetForm = document.getElementById(targetFormId);
        if (targetForm) {
            targetForm.classList.add('active');
        }
    }

    function showError(message) {
        let errorDiv = document.getElementById('generalError');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'generalError';
            errorDiv.className = 'error-message show';
            errorDiv.style.marginBottom = '1rem';
            errorDiv.style.textAlign = 'center';
            errorDiv.style.display = 'block';
            errorDiv.style.opacity = '1';
            errorDiv.style.transform = 'translateY(0)';
            
            const formContainer = document.querySelector('.form-container');
            formContainer.insertBefore(errorDiv, formContainer.firstChild);
        }
        
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
        
        setTimeout(() => {
            errorDiv.classList.remove('show');
        }, 5000);
    }

    // Login form submission
    document.getElementById('loginFormElement')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const submitButton = e.target.querySelector('button[type="submit"]');
        submitButton.classList.add('loading');
        submitButton.disabled = true;

        fetch('api/login.php', { // Updated to api/login.php
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text || response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                showError(data.error || 'Login failed');
            }
        })
        .catch(error => {
            console.error('Login error:', error.message, error.stack);
            showError(error.message.includes('SyntaxError') 
                ? 'Server response error. Please try again.'
                : error.message);
        })
        .finally(() => {
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
        });
    });

    // Signup form submission
    document.getElementById('signUpFormElement')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const submitButton = e.target.querySelector('button[type="submit"]');
        submitButton.classList.add('loading');
        submitButton.disabled = true;

        fetch('api/signup.php', { // Updated to api/signup.php
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text || response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = 'login.html?success=true';
            } else {
                showError(data.error || 'Signup failed');
            }
        })
        .catch(error => {
            console.error('Signup error:', error.message, error.stack);
            showError(error.message.includes('SyntaxError') 
                ? 'Server response error. Please try again.'
                : error.message);
        })
        .finally(() => {
            submitButton.classList.remove('loading');
            submitButton.disabled = false;
        });
    });
});