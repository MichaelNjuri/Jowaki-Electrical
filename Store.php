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
    <title>Jowaki Store - Professional Security Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/store.css">
</head>
<body>
    <?php include 'includes/store_header.php'; ?>

    <!-- Main Content -->
    <main class="store-main">
        <div class="store-container">
            
            <!-- Category Scroll -->
            <div class="category-scroll-container">
                <button class="scroll-arrow left" onclick="scrollCategories('left')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="category-scroll" id="categoryScroll">
                    <!-- Category cards will be loaded dynamically by JavaScript -->
                </div>
                <button class="scroll-arrow right" onclick="scrollCategories('right')">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Search and Sort Section -->
            <div class="search-sort-section">
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search products...">
                    <button class="search-btn" onclick="searchProducts()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="sort-container">
                    <select id="sortSelect" class="sort-select" onchange="sortProducts()">
                        <option value="featured">Featured</option>
                        <option value="name">Name A-Z</option>
                        <option value="name-desc">Name Z-A</option>
                        <option value="price-low">Price Low to High</option>
                        <option value="price-high">Price High to Low</option>
                        <option value="newest">Newest First</option>
                    </select>
                    <div class="view-options">
                        <button class="view-btn active" onclick="setView('grid')" title="Grid View">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" onclick="setView('list')" title="List View">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <section class="products-section" id="products">
                <div class="section-header">
                    <div class="header-left">
                        <h2>Our Products</h2>
                        <p class="section-subtitle">Professional security equipment and solutions</p>
                        <div class="results-info">
                            <span id="productsCount">0</span> products found
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div class="loading-state" id="loadingState">
                    <div class="loading-spinner"></div>
                    <p>Loading products...</p>
                </div>

                <!-- Products Grid -->
                <div class="products-grid" id="productsGrid">
                    <!-- Products will be loaded here -->
                </div>
            </section>
        </div>
    </main>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/<?php echo htmlspecialchars($store_settings['whatsapp_number']); ?>?text=Hello%20Jowaki%20Electrical,%20I%20would%20like%20to%20inquire%20about%20your%20products." class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <!-- Floating Cart Button -->
    <div id="floatingCartButton" class="floating-cart-button" onclick="window.location.href='cart.php'" style="display: none;">
        <i class="fas fa-shopping-cart"></i>
        <span id="floatingCartCount" class="floating-cart-count" style="display: none;">0</span>
    </div>

    <!-- Product Detail Modal -->
    <div id="productDetailModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="productDetailTitle">Product Details</h2>
                <button onclick="hideProductDetail()" class="btn-close">✕</button>
            </div>
            <div id="productDetailContent">
                <!-- Product details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script type="module" src="assets/js/store.js"></script>
</body>
</html> 