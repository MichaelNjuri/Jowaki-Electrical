<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <img src="assets/images/Logo.jpg" alt="" class="logo-img">
                <span class="logo-text">JOWAKI ELECTRICAL SERVICES LTD</span>
            </div>
            <nav class="main-nav">
                                 <a href="Index.php" class="nav-link">Home</a>
                 <a href="Service.php" class="nav-link">Services</a>
                 <a href="Store.php" class="nav-link shop-link">🛒 Shop</a>

                 <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                     <a href="profile.php" class="nav-link profile-link">👤 Profile</a>
                 <?php else: ?>
                     <a href="login_form.php" class="nav-link login-link">👤 Login</a>
                 <?php endif; ?>
            </nav>
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search products...">
                <button class="search-btn">🔍</button>
            </div>
        </div>
    </div>
</header>
