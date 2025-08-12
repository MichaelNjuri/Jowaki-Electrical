<?php
session_start();
require_once 'includes/load_settings.php';

// Load store settings
$store_settings = getStoreSettings(null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jowaki Electrical Services</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

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
                <div class="form-header">
                    <div class="logo-section">
                        <img src="assets/images/Logo.jpg" alt="Jowaki Logo" class="form-logo">
                        <h2>Welcome Back</h2>
                        <p>Sign in to your account to continue</p>
                    </div>
                </div>
                
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
                    <span>or continue with email</span>
                </div>

                <form id="loginFormElement">
                    <input type="hidden" id="redirectField" name="redirect" value="">
                    <input type="hidden" id="returnToCheckoutField" name="return_to_checkout" value="">
                    
                    <div class="form-group">
                        <label for="loginEmail">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="loginEmail" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" id="loginPasswordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message"></div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-group">
                            <input type="checkbox" id="rememberMe" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" id="forgotPasswordLink" class="forgot-link">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                    
                    <div class="form-footer">
                        <p>Don't have an account? <a href="#" id="switchToSignUpLink">Sign up here</a></p>
                    </div>
                </form>
            </div>

            <!-- Sign Up Form -->
            <div id="signUpForm" class="form-section">
                <div class="form-header">
                    <div class="logo-section">
                        <img src="assets/images/Logo.jpg" alt="Jowaki Logo" class="form-logo">
                        <h2>Create Account</h2>
                        <p>Join us and start shopping today</p>
                    </div>
                </div>
                
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
                    <span>or sign up with email</span>
                </div>

                <form id="signUpFormElement">
                    <input type="hidden" id="signupRedirectField" name="redirect" value="">
                    <input type="hidden" id="signupReturnToCheckoutField" name="return_to_checkout" value="">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="firstName" name="firstName" placeholder="Enter first name" required>
                            </div>
                            <div class="error-message"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="lastName" name="lastName" placeholder="Enter last name" required>
                            </div>
                            <div class="error-message"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="signUpEmail">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="signUpEmail" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="Enter phone number" required>
                        </div>
                        <div class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="signUpPassword">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="signUpPassword" name="password" placeholder="Create a password" required>
                            <button type="button" class="password-toggle" id="signUpPasswordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                            <button type="button" class="password-toggle" id="confirmPasswordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-group">
                            <input type="checkbox" name="terms" required>
                            <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                        </label>
                        <div class="error-message"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                    
                    <div class="form-footer">
                        <p>Already have an account? <a href="#" id="switchToLoginLink">Sign in here</a></p>
                    </div>
                </form>
            </div>

            <!-- Forgot Password Form -->
            <div id="forgotPasswordForm" class="form-section">
                <div class="form-header">
                    <div class="logo-section">
                        <img src="assets/images/Logo.jpg" alt="Jowaki Logo" class="form-logo">
                        <h2>Reset Password</h2>
                        <p>Enter your email to receive a reset link</p>
                    </div>
                </div>
                
                <form id="resetPasswordFormElement">
                    <div class="form-group">
                        <label for="resetEmail">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="resetEmail" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="error-message"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Send Reset Link
                    </button>
                    
                    <div class="form-footer">
                        <p><a href="#" id="backToLoginLink">Back to Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </main>

       <script src="assets/js/login.js"></script>
   
</body>
</html>