<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'load_settings.php';

// Establish database connection
require_once 'db_connection.php';

// Load store settings
$store_settings = getStoreSettings($conn);

$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!-- Store Header Styles -->
<style>
        /* CSS Variables for consistent theming */
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: #ffffff;
            padding-top: 70px; /* Reduced to match main header */
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: var(--shadow-light);
        }

        .header-content {
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            align-items: center;
            gap: 2rem;
            padding: 0.5rem 0;
            min-height: 70px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: translateY(-1px);
        }

        .logo-img {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: var(--shadow-light);
        }

        .logo-text {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
            max-width: 200px;
        }

        .main-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            background: transparent;
        }

        .nav-link:hover {
            color: var(--primary-color);
            background: rgba(37, 99, 235, 0.1);
        }

        .shop-link {
            background: linear-gradient(135deg, var(--secondary-color), var(--secondary-dark));
            color: white;
            font-weight: 600;
            box-shadow: var(--shadow-light);
        }

        .shop-link:hover {
            background: linear-gradient(135deg, var(--secondary-dark), var(--secondary-color));
            color: white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }

        .login-link, .profile-link {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            font-weight: 600;
            box-shadow: var(--shadow-light);
        }

        .login-link:hover, .profile-link:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            color: white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }

        .cart-link {
            background: linear-gradient(135deg, var(--accent-color), var(--accent-dark));
            color: white;
            font-weight: 600;
            box-shadow: var(--shadow-light);
            position: relative;
        }

        .cart-link:hover {
            background: linear-gradient(135deg, var(--accent-dark), var(--accent-color));
            color: white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
            border: 2px solid white;
        }

        /* Search Section */
        .search-section {
            position: relative;
            min-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem;
            padding-right: 3rem;
            border: 2px solid var(--border-light);
            border-radius: 50px;
            font-size: 0.9rem;
            background: var(--background-white);
            color: var(--text-dark);
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }

        .search-input::placeholder {
            color: var(--text-light);
        }

        .search-btn {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, var(--secondary-color), var(--secondary-dark));
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-light);
        }

        .search-btn:hover {
            background: linear-gradient(135deg, var(--secondary-dark), var(--secondary-color));
            transform: translateY(-50%) scale(1.1);
            box-shadow: var(--shadow-medium);
        }

        .contact-quick {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            align-items: flex-end;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-light);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            background: #f8fafc;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .contact-item:hover {
            background: #e2e8f0;
            color: var(--text-dark);
        }

        .contact-item span:first-child {
            font-size: 1rem;
        }



        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            flex-direction: column;
            gap: 4px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        .menu-toggle:hover {
            background: var(--background-light);
        }

        .menu-toggle span {
            width: 24px;
            height: 2px;
            background: var(--text-dark);
            border-radius: 2px;
            transition: var(--transition);
        }

        .menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }

        /* Mobile Navigation */
        .mobile-nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--background-white);
            border-top: 1px solid var(--border-light);
            box-shadow: var(--shadow-medium);
            padding: 1rem 0;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mobile-nav.active {
            opacity: 1;
            transform: translateY(0);
        }

        .mobile-nav-content {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 0 2rem;
        }

        .mobile-nav .nav-link {
            justify-content: flex-start;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            width: 100%;
        }

        .mobile-contact {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-light);
        }

        .mobile-contact .contact-item {
            justify-content: flex-start;
            width: 100%;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin-bottom: 0.25rem;
        }

        /* Scroll Effects */
        .header-scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.1);
        }

        .header-scrolled .header-content {
            padding: 0.75rem 0;
        }

        .header-scrolled .logo-img {
            width: 45px;
            height: 45px;
        }

        .header-scrolled .logo-text {
            font-size: 1.1rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .header-content {
                grid-template-columns: auto 1fr auto;
                gap: 1.5rem;
            }

            .logo-text {
                font-size: 1.1rem;
                max-width: 180px;
            }

            .contact-quick {
                display: none;
            }

            .search-section {
                min-width: 250px;
            }
        }

        @media (max-width: 992px) {
            .main-nav {
                gap: 0.25rem;
            }

            .nav-link {
                padding: 0.65rem 1rem;
                font-size: 0.9rem;
            }

            .logo-text {
                max-width: 160px;
                font-size: 1rem;
            }

            .search-section {
                min-width: 200px;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                grid-template-columns: auto 1fr auto;
                gap: 1rem;
                padding: 0 1rem;
            }

            .main-nav {
                display: none;
            }

            .menu-toggle {
                display: flex;
            }

            .mobile-nav {
                display: block;
            }

            .contact-quick {
                display: none;
            }

            .logo-text {
                font-size: 0.9rem;
                max-width: 140px;
            }

            .logo-img {
                width: 40px;
                height: 40px;
            }
        }

        @media (max-width: 480px) {
            .header-content {
                padding: 0.5rem 0;
            }

            .logo-text {
                font-size: 0.8rem;
                max-width: 120px;
            }

            .logo-img {
                width: 35px;
                height: 35px;
            }

            .mobile-nav-content {
                padding: 0 1rem;
            }
        }
    </style>

<!-- Store Header -->
<header id="header">
    <div class="container">
        <div class="header-content">
            <!-- Logo and Name -->
            <div class="logo">
                <a href="index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 1rem;">
                    <img src="assets/images/Logo.jpg" alt="Jowaki Logo" class="logo-img" />
                    <span class="logo-text">JOWAKI ELECTRICAL SERVICES LTD</span>
                </a>
            </div>
            
            <!-- Navigation -->
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Home</a>
                <a href="Service.php" class="nav-link">Services</a>
                <a href="Store.php" class="nav-link shop-link">🛒 Shop</a>
                <a href="cart.php" class="nav-link cart-link">
                    🛒 Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($logged_in): ?>
                    <a href="profile.php" class="nav-link profile-link">👤 Profile</a>
                <?php else: ?>
                    <a href="login_form.php" class="nav-link login-link">👤 Login</a>
                <?php endif; ?>
            </nav>
            
            <!-- Search Section -->
            <div class="search-section">
                <input type="text" class="search-input" placeholder="Search products...">
                <button class="search-btn">🔍</button>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div class="mobile-nav" id="mobileNav">
            <div class="mobile-nav-content">
                <a href="index.php" class="nav-link">Home</a>
                <a href="Service.php" class="nav-link">Services</a>
                <a href="Store.php" class="nav-link shop-link">🛒 Shop</a>
                <a href="cart.php" class="nav-link cart-link">
                    🛒 Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($logged_in): ?>
                    <a href="profile.php" class="nav-link profile-link">👤 Profile</a>
                <?php else: ?>
                    <a href="login_form.php" class="nav-link login-link">👤 Login</a>
                <?php endif; ?>
                <div class="mobile-contact">
                    <div class="contact-item" onclick="window.open('tel:<?php echo htmlspecialchars($store_settings['store_phone']); ?>', '_self')">
                        <span>📞</span>
                        <span><?php echo htmlspecialchars($store_settings['store_phone']); ?></span>
                    </div>
                    <div class="contact-item" onclick="window.open('mailto:Jowakielecricalsrvs@gmail.com', '_self')">
                        <span>✉️</span>
                        <span>Jowakielecricalsrvs@gmail.com</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Store Header JavaScript
    const header = document.getElementById('header');
    const menuToggle = document.getElementById('menuToggle');
    const mobileNav = document.getElementById('mobileNav');

    // Scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Mobile menu toggle
    menuToggle.addEventListener('click', () => {
        menuToggle.classList.toggle('active');
        mobileNav.classList.toggle('active');
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!header.contains(e.target)) {
            menuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
        }
    });

    // Close mobile menu when clicking on nav links
    document.querySelectorAll('.mobile-nav .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            menuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
        });
    });

    // Enhanced contact item interactions
    document.querySelectorAll('.contact-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(-5px) scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0) scale(1)';
        });
    });

    // Logo hover effect
    document.querySelector('.logo').addEventListener('mouseenter', function() {
        this.querySelector('.logo-text').style.color = 'var(--primary-color)';
    });

    document.querySelector('.logo').addEventListener('mouseleave', function() {
        this.querySelector('.logo-text').style.color = 'var(--text-dark)';
    });

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');

    searchBtn.addEventListener('click', function() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            console.log('Searching for:', searchTerm);
            // Add your search logic here
        }
    });

    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
