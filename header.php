<?php 
if (session_status() === PHP_SESSION_NONE) {     
    session_start(); 
} 
?>
<header>
    <div class="container">
        <div class="header-content">
            <!-- Logo and Name -->
            <div class="logo">
                <img src="Logo.jpg" alt="Jowaki Logo" class="logo-img" />
                <span class="logo-text">JOWAKI ELECTRICAL SERVICES LTD</span>
            </div>
            
            <!-- Navigation -->
            <nav class="main-nav">
                <a href="Index.php" class="nav-link">Home</a>
                <a href="Service.php" class="nav-link">Services</a>
                <a href="Store.php" class="nav-link shop-link">🛒 Shop</a>
                
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <a href="api/Profile.php" class="nav-link profile-link">👤 Profile</a>
                <?php else: ?>
                    <a href="login_form.php" class="nav-link login-link">👤 Login</a>
                <?php endif; ?>
            </nav>
            
            <!-- Contact Info -->
            <div class="contact-quick">
                <div class="contact-item">
                    <span>📞 </span>
                    <span id="contact-phone">0721442248</span>
                </div>
                <div class="contact-item">
                    <span>✉️</span>
                    <span id="contact-email">Jowakielecricalsrvs@gmail.com</span>
                </div>
            </div>
        </div>
    </div>
</header>