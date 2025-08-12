

<?php
session_start();
require_once 'includes/load_settings.php';

// Load store settings - pass null to let getStoreSettings handle the connection
$store_settings = getStoreSettings(null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Jowaki Electrical Services Ltd</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        /* CSS Variables for consistent theming - Matching Index Page */
        :root {
            --primary-color: hsl(207, 90%, 54%);
            --secondary-color: hsl(45, 93%, 47%);
            --accent-color: hsl(151, 55%, 42%);
            --primary-dark: hsl(207, 90%, 40%);
            --secondary-dark: hsl(45, 93%, 35%);
            --accent-dark: hsl(151, 55%, 30%);
            --text-dark: hsl(216, 12%, 20%);
            --text-light: hsl(216, 12%, 50%);
            --background-light: hsl(0, 0%, 98%);
            --background-white: hsl(0, 0%, 100%);
            --border-light: hsl(216, 12%, 90%);
            --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 8px 24px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 16px 48px rgba(0, 0, 0, 0.15);
            --border-radius: 1rem;
            --transition: all 0.3s ease;
        }

        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--background-light);
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        
        /* Header Override */
        header {
            min-height: 60px !important;
        }
        
        .header-content {
            min-height: 60px !important;
            padding: 0.25rem 0 !important;
        }
        
        .logo-img {
            width: 40px !important;
            height: 40px !important;
        }
        
        .logo-text {
            font-size: 0.9rem !important;
        }
        
        /* Main Content */
        main {
            padding-top: 20px;
            min-height: 100vh;
        }



        /* Section General Styles */
        .section {
            padding: 5rem 0;
        }

        .section-title {
            text-align: center;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        /* Contact Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 3rem;
            margin-top: 3rem;
        }

        .contact-card {
            background: var(--background-white);
            padding: 3rem 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: var(--transition);
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transform: scaleX(0);
            transition: var(--transition);
        }

        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }

        .contact-card:hover::before {
            transform: scaleX(1);
        }

        .contact-icon {
            width: 4rem;
            height: 4rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-medium);
        }

        .contact-card h4 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 2rem;
        }

        .contact-card h5 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-item {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--background-light);
            border-radius: 0.75rem;
            border-left: 4px solid var(--primary-color);
        }

        .contact-item p {
            color: var(--text-dark);
            font-size: 1rem;
            line-height: 1.6;
            font-weight: 500;
        }

        .contact-item a {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 600;
        }

        .contact-item a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Social Links */
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: var(--transition);
            box-shadow: var(--shadow-light);
        }

        .social-link:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: var(--shadow-medium);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 1rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--border-light);
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--background-white);
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }

        .form-group textarea {
            height: 140px;
            resize: vertical;
        }

        /* CTA Button */
        .cta-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 1.25rem 2rem;
            background: linear-gradient(135deg, var(--secondary-color), var(--secondary-dark));
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            box-shadow: var(--shadow-medium);
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .cta-button:hover {
            background: linear-gradient(135deg, var(--secondary-dark), var(--secondary-color));
            transform: translateY(-3px);
            box-shadow: var(--shadow-heavy);
        }

        .cta-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Success Message */
        .success-message {
            background: linear-gradient(135deg, var(--accent-color), var(--accent-dark));
            color: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-top: 2rem;
            display: none;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            box-shadow: var(--shadow-medium);
        }

        /* WhatsApp Float Button */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-dark));
            color: white;
            border-radius: 50px;
            padding: 1rem 1.5rem;
            text-decoration: none;
            font-weight: 600;
            box-shadow: var(--shadow-heavy);
            transition: var(--transition);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .whatsapp-float:hover {
            background: linear-gradient(135deg, var(--accent-dark), var(--accent-color));
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 20px 40px rgba(46, 204, 113, 0.3);
        }

        /* Additional Info Section */
        .additional-info {
            background: var(--background-white);
            margin-top: 3rem;
        }

        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .info-card {
            background: var(--background-light);
            padding: 2rem;
            border-radius: var(--border-radius);
            text-align: center;
            transition: var(--transition);
            border: 1px solid var(--border-light);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
            background: var(--background-white);
        }

        .info-card .icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .info-card h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .info-card p {
            color: var(--text-light);
            line-height: 1.6;
        }

        /* Footer - Matching Index Page */
        .footer {
            background: var(--text-dark);
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: white;
        }

        .footer-section p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .footer-section a {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.25rem 0;
            transition: var(--transition);
        }

        .footer-section a:hover {
            color: var(--primary-color);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom p {
            color: rgba(255, 255, 255, 0.6);
        }

        /* Error states */
        .form-group input.error,
        .form-group textarea.error {
            border-color: #ff6b6b;
            background-color: #fff5f5;
        }

        .form-group input.error:focus,
        .form-group textarea.error:focus {
            border-color: #ff6b6b;
            box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.1);
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .contact-icon {
            animation: float 3s ease-in-out infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .contact-card {
                padding: 2rem 1.5rem;
            }

            .section-title {
                font-size: 2rem;
            }



            .whatsapp-float {
                padding: 0.75rem 1.25rem;
                font-size: 0.9rem;
            }

            .header-content {
                flex-direction: column;
                padding: 0.5rem 0;
            }

            .main-nav {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .section {
                padding: 3rem 0;
            }

            .contact-card {
                padding: 1.5rem 1rem;
            }

            .cta-button {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }



            .info-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <main>


        <!-- Main Contact Section -->
        <section class="section">
            <div class="container">
                <div class="contact-grid">
                    <!-- Contact Details -->
                    <div class="contact-card">
                        <div class="contact-icon"><i class="fas fa-address-book"></i></div>
                        <h4>Contact Details</h4>
                        
                        <div class="contact-item">
                            <h5><i class="fas fa-phone"></i> Call Us</h5>
                            <p>
                                <a href="tel:+254721442248">+254 721 442 248</a><br>
                                <a href="tel:+254723340274">+254 723 340 274</a><br>
                                <a href="tel:+254720947507">+254 720 947 507</a>
                            </p>
                        </div>
                        
                        <div class="contact-item">
                            <h5><i class="fas fa-envelope"></i> Mail Us</h5>
                            <p>
                                <a href="mailto:jowakielectricalsrvs@gmail.com">jowakielectricalsrvs@gmail.com</a>
                            </p>
                        </div>
                        
                        <div class="contact-item">
                            <h5><i class="fas fa-map-marker-alt"></i> Our Address</h5>
                            <p>
                                P.O Box 63616 – 00619<br>
                                Nairobi, Kenya
                            </p>
                        </div>
                        
                        <div class="social-links">
                            <?php if ($store_settings['enable_facebook'] && !empty($store_settings['facebook_url'])): ?>
                            <a href="<?php echo htmlspecialchars($store_settings['facebook_url']); ?>" class="social-link" title="Facebook" target="_blank">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($store_settings['enable_whatsapp']): ?>
                            <a href="https://wa.me/<?php echo htmlspecialchars($store_settings['whatsapp_number']); ?>" class="social-link" title="WhatsApp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <?php endif; ?>
                            <a href="tel:<?php echo htmlspecialchars($store_settings['store_phone']); ?>" class="social-link" title="Call Us">
                                <i class="fas fa-phone"></i>
                            </a>
                            <a href="mailto:<?php echo htmlspecialchars($store_settings['store_email']); ?>" class="social-link" title="Email Us">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="contact-card">
                        <div class="contact-icon"><i class="fas fa-paper-plane"></i></div>
                        <h4>Send Us a Message</h4>
                        <form id="contactForm">
                            <div class="form-group">
                                <label for="name">Your Name *</label>
                                <input type="text" id="name" name="name" required placeholder="Enter your full name">
                            </div>
                            <div class="form-group">
                                <label for="email">Your Email *</label>
                                <input type="email" id="email" name="email" required placeholder="Enter your email address">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
                            </div>
                            <div class="form-group">
                                <label for="subject">Subject *</label>
                                <input type="text" id="subject" name="subject" required placeholder="What's this about?">
                            </div>
                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" placeholder="Tell us how we can help you..." required></textarea>
                            </div>
                            <button type="submit" class="cta-button">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                            <div class="success-message" id="successMessage">
                                <i class="fas fa-check-circle"></i>
                                Message sent successfully! We'll get back to you soon.
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="additional-info">
                    <div class="info-cards">
                        <div class="info-card">
                            <div class="icon">⚡</div>
                            <h4>Quick Response</h4>
                            <p>We respond to all inquiries within 24 hours and provide emergency services when needed.</p>
                        </div>
                        <div class="info-card">
                            <div class="icon">🎯</div>
                            <h4>Professional Service</h4>
                            <p>Our certified technicians ensure quality installation and reliable maintenance services.</p>
                        </div>
                        <div class="info-card">
                            <div class="icon">🛡️</div>
                            <h4>Trusted Solutions</h4>
                            <p>Over 12 years of experience delivering security solutions across Kenya with excellence.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/<?php echo htmlspecialchars($store_settings['whatsapp_number']); ?>?text=Hello%20Jowaki%20Electrical,%20I%20would%20like%20to%20inquire%20about%20your%20services." class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
        Chat With Us
    </a>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Jowaki</h3>
                    <p>Your trusted partner for comprehensive security solutions. Protecting what matters most since 2011 with professional excellence and innovative technology.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="index.php">Home</a>
                    <a href="index.php#about">About Us</a>
                    <a href="index.php#services">Services</a>
                    <a href="store.php">Shop</a>
                    <a href="service.php">Contact</a>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p>📞 +254 721442248</p>
                    <p>📧 jowakielectricalsrvs@gmail.com</p>
                    <p>📍 Nairobi, Kenya</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 Jowaki Electrical Services Ltd. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Pre-fill service form with user data if logged in
        function prefillServiceForm() {
            fetch('api/get_user_data.php', {
                method: 'GET',
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const userData = data.data;
                    
                    // Pre-fill form fields
                    if (userData.fullName) document.getElementById('name').value = userData.fullName;
                    if (userData.email) document.getElementById('email').value = userData.email;
                    
                    // Show a subtle notification that form was pre-filled
                    const notification = document.createElement('div');
                    notification.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: rgba(5, 150, 105, 0.9);
                        color: white;
                        padding: 1rem;
                        border-radius: 0.5rem;
                        z-index: 1000;
                        font-size: 0.9rem;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    `;
                    notification.innerHTML = '✅ Form pre-filled with your profile data';
                    document.body.appendChild(notification);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                }
            })
            .catch(error => {
                console.log('No user data available or user not logged in');
            });
        }

        // Call pre-fill function when page loads
        document.addEventListener('DOMContentLoaded', prefillServiceForm);

        // Form handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('.cta-button');
            const successMessage = document.getElementById('successMessage');
            
            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            
            // Collect form data
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                subject: document.getElementById('subject').value,
                message: document.getElementById('message').value
            };
            
            // Send contact form data
            fetch('api/contact_form.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    successMessage.style.display = 'flex';
                    successMessage.textContent = data.message;
                    
                    // Reset form
                    this.reset();
                    
                } else {
                    // Show error message
                    alert('Error: ' + (data.error || 'Failed to send message'));
                }
            })
            .catch(error => {
                console.error('Contact form error:', error);
                alert('Error: Failed to send message. Please try again.');
            })
            .finally(() => {
                // Reset button
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
                
                // Hide success message after 5 seconds
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 5000);
            });
        });

        // Form validation
        const inputs = document.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('error') && this.value.trim()) {
                    this.classList.remove('error');
                }
            });
        });

        // Email validation
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.add('error');
            }
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe sections for animation
        document.querySelectorAll('.section, .contact-card, .info-card').forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(element);
        });

        // Add loading animation to contact icons
        const contactIcons = document.querySelectorAll('.contact-icon');
        contactIcons.forEach((icon, index) => {
            icon.style.animationDelay = `${index * 0.2}s`;
        });
    </script>
</body>
</html>