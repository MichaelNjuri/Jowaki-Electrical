<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jowaki Electrical Services Ltd - Security & Electrical Solutions</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

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

        <!-- Product Showcase Section -->
        <section id="product-showcase" class="section">
            <div class="container">
                <h2 class="section-title">Featured Products</h2>
                <p class="text-center text-gray-600 mb-8">Discover our top-quality electrical and security products, professionally selected for your needs.</p>
                <div id="product-list" class="product-showcase-grid">
                    <!-- Loading indicator -->
                    <div id="products-loading" class="text-center py-8">
                        <div class="loading-spinner mx-auto mb-4"></div>
                        <p class="text-gray-500">Loading products...</p>
                    </div>
                </div>
                <div class="text-center mt-8">
                    <a href="Store.php" class="cta-button">View All Products</a>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="section gallery-section">
            <div class="container">
                <h2 class="section-title">Our Work Gallery</h2>
                <div class="gallery-grid">
                    <div class="gallery-item">
                        <img src="IMG_1.jpg" alt="Electric Fence Installation">
                        <div class="gallery-overlay">
                            <h4>Clear View Fence</h4>
                            <p>Professional electric fence installation with clear visibility</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="IMg-2.jpg" alt="Razor Wire on Wall">
                        <div class="gallery-overlay">
                            <h4>Wooden Posts Fence</h4>
                            <p>Durable wooden post fencing solutions for residential properties</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="IMG-3.jpg" alt="Electric Fence and Razor Wire">
                        <div class="gallery-overlay">
                            <h4>Wall Top Fence / Razor</h4>
                            <p>Enhanced perimeter security with razor wire installation</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="IMG-4.jpg" alt="Perimeter Electric Fence">
                        <div class="gallery-overlay">
                            <h4>Sliding Wooden Gate</h4>
                            <p>Automated sliding gate systems for convenient access control</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="IMG-6.jpg" alt="Wall with Razor Wire">
                        <div class="gallery-overlay">
                            <h4>Pre-Fabricated Panel Wall</h4>
                            <p>Modern concrete panel walls for maximum security</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="IMG_7.jpg" alt="Electric Fence Security">
                        <div class="gallery-overlay">
                            <h4>Chain Link Fence</h4>
                            <p>Industrial-grade chain link fencing for commercial properties</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="section services-enhanced">
            <div class="container">
                <h2 class="section-title">Our Services</h2>
                <div class="services-grid">
                    <div class="service-card">
                        <div class="service-icon">🏠</div>
                        <h3>Residential Services</h3>
                        <ul class="service-list">
                            <li>Home electric fence installation</li>
                            <li>Residential CCTV systems</li>
                            <li>Home alarm systems</li>
                            <li>Gate automation for homes</li>
                            <li>Perimeter wall protection</li>
                            <li>Emergency repair services</li>
                        </ul>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">🏢</div>
                        <h3>Commercial Services</h3>
                        <ul class="service-list">
                            <li>Industrial electric fencing</li>
                            <li>Commercial CCTV networks</li>
                            <li>Access control systems</li>
                            <li>Warehouse security solutions</li>
                            <li>Office building protection</li>
                            <li>Maintenance contracts</li>
                        </ul>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">🚨</div>
                        <h3>Emergency Services</h3>
                        <ul class="service-list">
                            <li>24/7 emergency response</li>
                            <li>Urgent repair services</li>
                            <li>Security breach assistance</li>
                            <li>System troubleshooting</li>
                            <li>Emergency installations</li>
                            <li>Technical support hotline</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="section about-enhanced">
            <div class="container">
                <h2 class="section-title">About Us</h2>
                <div class="about-content">
                    <div class="about-row">
                        <div class="about-text">
                            <h2>About Jowaki Electrical Services</h2>
                            <p class="lead">Established in June 2011 and incorporated in June 2013, we have grown from identifying market gaps in security services to becoming a trusted name in the industry.</p>
                        </div>
                        <div class="about-image">
                            <img src="IMG_11.JPG" alt="Jowaki Team at Work">
                        </div>
                    </div>
                    <div class="about-row">
                        <div class="about-image">
                            <img src="IMG-8.JPG" alt="Professional Installation">
                        </div>
                        <div class="about-highlights">
                            <div class="highlight-item">
                                <h4>Our Vision</h4>
                                <p>To be a household name in providing quality services with perfection.</p>
                            </div>
                            <div class="highlight-item">
                                <h4>Our Mission</h4>
                                <p>Champion high levels of quality services and integrity, with perfection to be the preferred service provider.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section id="team" class="section team-enhanced">
            <div class="container">
                <h2 class="section-title">Our Team</h2>
                <div class="team-grid">
                    <div class="team-card">
                        <h4>Joseph N. Kibuku</h4>
                        <h5>Managing Director</h5>
                        <p>Leading with vision and expertise in security solutions with over 12 years of industry experience.</p>
                    </div>
                    <div class="team-card">
                        <h4>Josephine Ingoshe</h4>
                        <h5>Director</h5>
                        <p>Driving strategic growth and client satisfaction with a focus on innovative security technologies.</p>
                    </div>
                    <div class="team-card">
                        <h4>Eric Ndungu</h4>
                        <h5>Technical Manager</h5>
                        <p>Overseeing technical excellence and project execution with precision and professional standards.</p>
                    </div>
                    <div class="team-card">
                        <h4>Caroline N. Njoroge</h4>
                        <h5>Administrator</h5>
                        <p>Ensuring smooth operations and excellent customer service delivery across all departments.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="section contact-enhanced">
            <div class="container">
                <h2 class="section-title">Get In Touch</h2>
                <div class="contact-grid">
                    <div class="contact-card">
                        <div class="contact-icon">📞</div>
                        <h4>Call Us</h4>
                        <p>+254 721442248</p>
                        <p>Available 24/7 for emergencies</p>
                    </div>
                    <div class="contact-card">
                        <div class="contact-icon">📧</div>
                        <h4>Email Us</h4>
                        <p>Jowakielecricalsrvs@gmail.com</p>
                        <p>We respond within 24 hours</p>
                    </div>
                    <div class="contact-card">
                        <div class="contact-icon">📍</div>
                        <h4>Visit Us</h4>
                        <p>Nairobi, Kenya</p>
                        <p>Professional consultation available</p>
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
                    <a href="#about">About Us</a>
                    <a href="#services">Services</a>
                    <a href="Store.php">Shop</a>
                    <a href="#contact">Contact</a>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p>📞 +254 721442248</p>
                    <p>📧 Jowakielecricalsrvs@gmail.com</p>
                    <p>📍 Nairobi, Kenya</p>
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