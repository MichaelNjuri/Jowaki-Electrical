<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'load_settings.php';

// Set $logged_in based on session
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Load store settings - pass null to let getStoreSettings handle the connection
$store_settings = getStoreSettings(null);
?>

<style>
    :root {
        --primary-color: hsl(207, 90%, 54%);
        --secondary-color: hsl(45, 93%, 47%);
        --accent-color: hsl(151, 55%, 42%);
        --primary-dark: hsl(207, 90%, 40%);
        --secondary-dark: hsl(45, 93%, 35%);
        --accent-dark: hsl(151, 55%, 30%);
        --text-dark: hsl(216, 12%, 20%);
        --text-light: hsl(216, 12%, 50%);
        --background-white: hsl(0, 0%, 100%);
        --border-light: hsl(216, 12%, 90%);
        --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .header-container {
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
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 2rem;
        padding: 0.5rem 0;
        min-height: 70px;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
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

    .menu-toggle {
        display: none;
        flex-direction: column;
        gap: 3px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 4px;
    }

    .menu-toggle span {
        width: 20px;
        height: 2px;
        background: var(--text-dark);
        border-radius: 1px;
        transition: all 0.3s ease;
    }

    .mobile-nav {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--background-white);
        border-top: 1px solid var(--border-light);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 1rem 0;
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

<header id="header" class="header-container">
    <div class="header-content">
        <!-- Logo and Name -->
        <div class="logo">
            <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../Index.php' : 'Index.php'; ?>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 1rem;">
                <img src="assets/images/Logo.jpg" alt="Jowaki Logo" class="logo-img" />
                <span class="logo-text">JOWAKI ELECTRICAL SERVICES LTD</span>
            </a>
        </div>
        
        <!-- Navigation -->
        <nav class="main-nav">
            <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../Index.php' : 'Index.php'; ?>" class="nav-link">Home</a>
            <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../Service.php' : 'Service.php'; ?>" class="nav-link">Services</a>
            <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../Store.php' : 'Store.php'; ?>" class="nav-link shop-link">🛒 Shop</a>
            <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../cart.php' : 'cart.php'; ?>" class="nav-link cart-link">
                🛒 Cart
                <?php if ($cart_count > 0): ?>
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <?php if ($logged_in): ?>
                <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? 'Profile.php' : 'API/Profile.php'; ?>" class="nav-link profile-link">👤 Profile</a>
            <?php else: ?>
                <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../login_form.php' : 'login_form.php'; ?>" class="nav-link login-link">👤 Login</a>
            <?php endif; ?>
        </nav>
        
        <!-- Contact Info -->
        <div class="contact-quick">
            <div class="contact-item" onclick="window.open('tel:<?php echo htmlspecialchars($store_settings['store_phone']); ?>', '_self')">
                <span>📞</span>
                <span id="contact-phone"><?php echo htmlspecialchars($store_settings['store_phone']); ?></span>
            </div>
            <div class="contact-item" onclick="window.open('mailto:Jowakielecricalsrvs@gmail.com', '_self')">
                <span>✉️</span>
                <span id="contact-email">Jowakielecricalsrvs@gmail.com</span>
            </div>
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
            <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../Index.php' : 'Index.php'; ?>" class="nav-link">Home</a>
            <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../Service.php' : 'Service.php'; ?>" class="nav-link">Services</a>
            <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../Store.php' : 'Store.php'; ?>" class="nav-link shop-link">🛒 Shop</a>
            <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../cart.php' : 'cart.php'; ?>" class="nav-link cart-link">
                🛒 Cart
                <?php if ($cart_count > 0): ?>
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <?php if ($logged_in): ?>
                <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? 'Profile.php' : 'API/Profile.php'; ?>" class="nav-link profile-link">👤 Profile</a>
            <?php else: ?>
                <a href="<?php echo isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/API/') !== false ? '../login_form.php' : 'login_form.php'; ?>" class="nav-link login-link">👤 Login</a>
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
</header>

<script>
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

    // Close mobile menu when clicking outside
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
</script>
