<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jowaki Electrical Services</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-content">
            <div class="logo">
                <img src="logo.png" alt="Jowaki Electrical Services" class="logo-img">
                <span class="logo-text">Jowaki Electrical Services</span>
            </div>
            
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Home</a>
                <a href="services.php" class="nav-link">Services</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="shop.php" class="nav-link shop-link">Shop</a>
                <a href="#" class="nav-link login-link" id="auth-link">Sign Up</a>
            </nav>
            
            <div class="contact-quick">
                <div class="contact-item">
                    <span>📞</span>
                    <span>+254 123 456 789</span>
                </div>
                <div class="contact-item">
                    <span>✉️</span>
                    <span>info@jowaki.com</span>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="form-container">
            <div id="generalError" class="general-error">
                <!-- Error messages will be displayed here -->
            </div>

            <div id="successMessage" class="success-message">
                <!-- Success messages will be displayed here -->
            </div>

            <!-- Login Form -->
            <div id="loginForm" class="form-section active">
                <h2>Welcome Back</h2>
                
                <!-- Google Login Button -->
                <button type="button" class="btn google-btn" id="googleLoginBtn">
                    <svg class="google-icon" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continue with Google
                </button>

                <div class="divider">
                    <span>or</span>
                </div>

                <form id="loginFormElement">
                    <input type="hidden" id="redirectField" name="redirect" value="">
                    <input type="hidden" id="returnToCheckoutField" name="return_to_checkout" value="">
                    
                    <div class="form-group">
                        <label for="loginEmail">Email Address</label>
                        <input type="email" id="loginEmail" name="email" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" id="loginPassword" name="password" required>
                        <div class="error-message"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Sign In</button>
                    <div class="form-links">
                        <a href="#" id="forgotPasswordLink">Forgot your password?</a>
                    </div>
                    <div class="form-links">
                        <a href="#" id="switchToSignUpLink">Don't have an account? Sign up here</a>
                    </div>
                </form>
            </div>

            <!-- Sign Up Form -->
            <div id="signUpForm" class="form-section">
                <h2>Create Account</h2>
                
                <!-- Google Signup Button -->
                <button type="button" class="btn google-btn" id="googleSignupBtn">
                    <svg class="google-icon" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign up with Google
                </button>

                <div class="divider">
                    <span>or</span>
                </div>

                <form id="signUpFormElement">
                    <input type="hidden" id="signupRedirectField" name="redirect" value="">
                    <input type="hidden" id="signupReturnToCheckoutField" name="return_to_checkout" value="">
                    
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="signUpEmail">Email Address</label>
                        <input type="email" id="signUpEmail" name="email" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="tel" id="phoneNumber" name="phoneNumber" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="signUpPassword">Password</label>
                        <input type="password" id="signUpPassword" name="password" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-group">
                            <input type="checkbox" name="terms" required>
                            <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                        </label>
                        <div class="error-message"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                    <div class="form-links">
                        <a href="#" id="switchToLoginLink">Already have an account? Sign in here</a>
                    </div>
                </form>
            </div>

            <!-- Forgot Password Form -->
            <div id="forgotPasswordForm" class="form-section">
                <h2>Reset Password</h2>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    Enter your email address and we'll send you a link to reset your password.
                </p>
                <form id="resetPasswordFormElement">
                    <div class="form-group">
                        <label for="resetEmail">Email Address</label>
                        <input type="email" id="resetEmail" name="email" required>
                        <div class="error-message"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                    <div class="form-links">
                        <a href="#" id="backToLoginLink">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

   <script src="login.js"></script>
   
</body>
</html>