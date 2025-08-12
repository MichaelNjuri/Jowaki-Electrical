<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jowaki Electrical Services Ltd - Security & Electrical Solutions</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">Jowaki Electrical</div>
                <nav class="main-nav">
                    <a href="#home">Home</a>
                    <a href="#services">Services</a>
                    <a href="Store.php">Shop</a>
                    <a href="#contact">Contact</a>
                    <a href="login_form.php">Login</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Security & Electrical Solutions</h1>
                <p>Champion high levels of quality services and integrity, with perfection to be the preferred service provider</p>
                <div class="hero-buttons">
                    <a href="Store.php" class="cta-button">Shop Now</a>
                    <a href="#contact" class="cta-button-secondary">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main>
        <!-- Features Section -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Why Choose Us</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="w-16 h-16 bg-blue-100">
                            <span class="text-2xl">🚚</span>
                        </div>
                        <h3>Fast Delivery</h3>
                        <p>Quick installation and delivery services across Kenya with professional efficiency</p>
                    </div>
                    <div class="stat-item">
                        <div class="w-16 h-16 bg-yellow-100">
                            <span class="text-2xl">🛡️</span>
                        </div>
                        <h3>Certified Products</h3>
                        <p>Only the highest quality, certified security and electrical products from trusted manufacturers</p>
                    </div>
                    <div class="stat-item">
                        <div class="w-16 h-16 bg-green-100">
                            <span class="text-2xl">👨‍🔧</span>
                        </div>
                        <h3>Expert Support</h3>
                        <p>Professional installation and 24/7 technical support by our experienced team</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="section gallery-section">
            <div class="container">
                <h2 class="section-title">Our Work Gallery</h2>
                <div class="gallery-grid">
                    <div class="gallery-item">
                        <img src="assets/images/IMG_1.jpg" alt="Electric Fence Installation">
                        <div class="gallery-overlay">
                            <h4>Clear View Fence</h4>
                            <p>Professional electric fence installation with clear visibility</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="assets/images/IMg-2.jpg" alt="Razor Wire on Wall">
                        <div class="gallery-overlay">
                            <h4>Razor Wire Security</h4>
                            <p>Industrial-grade razor wire installation for maximum security</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="assets/images/IMG_3.jpg" alt="CCTV Camera">
                        <div class="gallery-overlay">
                            <h4>CCTV Surveillance</h4>
                            <p>Advanced CCTV camera systems with night vision capabilities</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="section contact-section">
            <div class="container">
                <h2 class="section-title">Contact Us</h2>
                <div class="contact-grid">
                    <div class="contact-info">
                        <h3>Get In Touch</h3>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <p><strong>Phone</strong></p>
                                <p>+254 721442248</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <p><strong>Email</strong></p>
                                <p>info@jowaki.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <p><strong>Location</strong></p>
                                <p>Nairobi, Kenya</p>
                            </div>
                        </div>
                    </div>
                    <div class="contact-form">
                        <form id="contact-form">
                            <div class="form-group">
                                <input type="text" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="form-group">
                                <input type="tel" name="phone" placeholder="Your Phone">
                            </div>
                            <div class="form-group">
                                <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="cta-button">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

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
                    <a href="#home">Home</a>
                    <a href="#services">Services</a>
                    <a href="Store.php">Shop</a>
                    <a href="#contact">Contact</a>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p>📞 +254 721442248</p>
                    <p>📧 info@jowaki.com</p>
                    <p>📍 Nairobi, Kenya</p>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="https://wa.me/254721442248" target="_blank" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="tel:+254721442248" title="Call Us">
                            <i class="fas fa-phone"></i>
                        </a>
                        <a href="mailto:info@jowaki.com" title="Email Us">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 Jowaki Electrical Services Ltd. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/index.js"></script>
</body>
</html>

