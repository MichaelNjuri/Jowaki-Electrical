<?php
session_start();
require_once 'API/db_connection.php';
require_once 'API/load_settings.php';

// Load store settings
$store_settings = getStoreSettings($conn);

// Get product ID from URL parameter
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: Store.php');
    exit();
}

// Fetch product details with all fields
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: Store.php');
    exit();
}

// Process image paths
$image_paths = [];
if ($product['image_paths']) {
    $image_paths = json_decode($product['image_paths'], true);
    if (!is_array($image_paths)) {
        $image_paths = [$product['image_paths']];
    }
}
$main_image = !empty($image_paths) ? $image_paths[0] : 'images/placeholder-product.jpg';

// Process specifications
$specifications = [];
if ($product['specifications']) {
    $specifications = json_decode($product['specifications'], true);
    if (!is_array($specifications)) {
        $specifications = [];
    }
    // Ensure each specification has the required structure
    foreach ($specifications as $key => $spec) {
        if (!is_array($spec) || !isset($spec['label']) || !isset($spec['value'])) {
            unset($specifications[$key]);
        }
    }
}

// Calculate pricing
$original_price = floatval($product['price']);
$discount_price = floatval($product['discount_price']);
$current_price = $discount_price > 0 ? $discount_price : $original_price;
$discount_percentage = $discount_price > 0 ? round((($original_price - $discount_price) / $original_price) * 100) : 0;

// Fetch related products
$related_stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND is_active = 1 LIMIT 8");
$related_stmt->bind_param("si", $product['category'], $product_id);
$related_stmt->execute();
$related_products = $related_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Don't close connection here - it's needed by store_header.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Jowaki Electrical Services</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 160)); ?>">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="store.css">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="css/product-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($product['name']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($product['description']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($main_image); ?>">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <meta name="whatsapp-number" content="<?php echo htmlspecialchars($store_settings['whatsapp_number']); ?>">
</head>
<body>
    <?php include 'store_header.php'; ?>

    <!-- Main Content -->
    <main class="product-detail-main">


        <!-- Product Hero Section -->
        <section class="product-hero">
            <div class="container">
                <div class="product-grid">
                    <!-- Product Gallery -->
                    <div class="product-gallery">
                        <div class="main-image-container">
                            <img src="<?php echo htmlspecialchars($main_image); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 id="mainProductImage"
                                 class="main-image">
                            
                            <?php if ($discount_percentage > 0): ?>
                            <div class="discount-badge">
                                <span>-<?php echo $discount_percentage; ?>%</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="image-actions">
                                <button class="action-btn" onclick="openImageModal()" title="View Full Size">
                                    <i class="fas fa-expand"></i>
                                </button>
                                <button class="action-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>)" title="Add to Wishlist">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        
                        <?php if (count($image_paths) > 1): ?>
                        <div class="image-thumbnails">
                            <?php foreach ($image_paths as $index => $image_path): ?>
                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 onclick="changeMainImage('<?php echo htmlspecialchars($image_path); ?>', this)">
                                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?> - Image <?php echo $index + 1; ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Details -->
                    <div class="product-details">
                        <div class="product-meta">
                            <span class="category-tag">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($product['category']); ?>
                            </span>
                            <?php if ($product['stock'] > 0): ?>
                            <span class="stock-badge in-stock">
                                <i class="fas fa-check-circle"></i>
                                In Stock
                            </span>
                            <?php else: ?>
                            <span class="stock-badge out-of-stock">
                                <i class="fas fa-times-circle"></i>
                                Out of Stock
                            </span>
                            <?php endif; ?>
                        </div>

                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                        <div class="price-section">
                            <?php if ($discount_percentage > 0): ?>
                            <div class="price-display">
                                <span class="current-price">KSh <?php echo number_format($current_price, 0); ?></span>
                                <span class="original-price">KSh <?php echo number_format($original_price, 0); ?></span>
                                <span class="discount-text">Save KSh <?php echo number_format($original_price - $current_price, 0); ?></span>
                            </div>
                            <?php else: ?>
                            <div class="price-display">
                                <span class="current-price">KSh <?php echo number_format($current_price, 0); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="product-description">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>

                        <!-- Product Options -->
                        <div class="product-options">
                            <div class="option-group">
                                <label class="option-label">Quantity</label>
                                <div class="quantity-controls">
                                    <button type="button" class="qty-btn" onclick="changeQuantity(-1)" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" 
                                           onchange="updateQuantity(this.value)" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                    <button type="button" class="qty-btn" onclick="changeQuantity(1)" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <span class="stock-info"><?php echo $product['stock'] > 0 ? 'Items available' : 'No items available'; ?></span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button class="btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart"></i>
                                Add to Cart
                            </button>
                            <a href="https://wa.me/<?php echo htmlspecialchars($store_settings['whatsapp_number']); ?>?text=Hello%20Jowaki%20Electrical,%20I%20would%20like%20to%20inquire%20about%20<?php echo urlencode($product['name']); ?>" 
                               class="btn-whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                                Order via WhatsApp
                            </a>
                        </div>

                        <!-- Product Features -->
                        <div class="product-features">
                            <div class="feature-item">
                                <i class="fas fa-headset"></i>
                                <span>24/7 Customer Support</span>
                            </div>
                        </div>

                        <!-- Social Share -->
                        <div class="social-share">
                            <span>Share this product:</span>
                            <div class="share-buttons">
                                <button class="share-btn facebook" onclick="shareOnFacebook()" title="Share on Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </button>
                                <button class="share-btn twitter" onclick="shareOnTwitter()" title="Share on Twitter">
                                    <i class="fab fa-twitter"></i>
                                </button>
                                <button class="share-btn whatsapp" onclick="shareOnWhatsApp()" title="Share on WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                                <button class="share-btn copy" onclick="copyProductLink()" title="Copy Link">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product Specifications -->
        <?php if (!empty($specifications)): ?>
        <section class="specifications-section">
            <div class="container">
                <div class="section-header">
                    <h2>Product Specifications</h2>
                    <p>Technical details and specifications</p>
                </div>
                <div class="specifications-grid">
                    <?php foreach ($specifications as $spec): ?>
                    <div class="spec-item">
                        <div class="spec-label"><?php echo htmlspecialchars($spec['label']); ?></div>
                        <div class="spec-value"><?php echo htmlspecialchars($spec['value']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Related Products -->
        <section class="related-products-section">
            <div class="container">
                <div class="section-header">
                    <h2>For More Like This</h2>
                </div>
                
                <?php if (!empty($related_products)): ?>
                <div class="products-scroll-container">
                    <div class="products-scroll">
                        <?php foreach ($related_products as $related): 
                            // Process related product image paths
                            $related_image_paths = [];
                            if ($related['image_paths']) {
                                $related_image_paths = json_decode($related['image_paths'], true);
                                if (!is_array($related_image_paths)) {
                                    $related_image_paths = [$related['image_paths']];
                                }
                            }
                            $related_image = !empty($related_image_paths) ? $related_image_paths[0] : 'images/placeholder-product.jpg';
                            
                            // Calculate related product price
                            $related_original = floatval($related['price']);
                            $related_discount = floatval($related['discount_price']);
                            $related_current = $related_discount > 0 ? $related_discount : $related_original;
                            $related_discount_percentage = $related_discount > 0 ? round((($related_original - $related_discount) / $related_original) * 100) : 0;
                        ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($related_image); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>"
                                     loading="lazy">
                                
                                <?php if ($related_discount_percentage > 0): ?>
                                <div class="product-badge">
                                    <span>-<?php echo $related_discount_percentage; ?>%</span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <button class="action-btn" onclick="addToCart(<?php echo $related['id']; ?>)" title="Add to Cart">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="action-btn" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="action-btn" onclick="toggleWishlist(<?php echo $related['id']; ?>)" title="Add to Wishlist">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="product-detail.php?id=<?php echo $related['id']; ?>">
                                        <?php echo htmlspecialchars($related['name']); ?>
                                    </a>
                                </h3>
                                
                                <div class="product-price">
                                    <span class="current-price">KSh <?php echo number_format($related_current, 0); ?></span>
                                    <?php if ($related_discount_percentage > 0): ?>
                                    <span class="original-price">KSh <?php echo number_format($related_original, 0); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="no-recommendations">
                    <i class="fas fa-box-open"></i>
                    <p>No related products found</p>
                    <a href="Store.php" class="btn-primary">Browse All Products</a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" src="" alt="Product Image">
        </div>
    </div>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/<?php echo htmlspecialchars($store_settings['whatsapp_number']); ?>?text=Hello%20Jowaki%20Electrical,%20I%20would%20like%20to%20inquire%20about%20your%20products." 
       class="whatsapp-float" target="_blank" title="Chat with us on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Scripts -->
    <script src="js/product-detail.js"></script>
    
    <!-- Fallback script in case external JS fails to load -->
    <script>
        // Check if functions are loaded, if not provide fallbacks
        if (typeof addToCart === 'undefined') {
            console.log('External JS not loaded, using fallback functions');
            
            window.addToCart = function(productId) {
                alert('Add to cart functionality is loading...');
            };
            
            window.changeQuantity = function(change) {
                const input = document.getElementById('quantity');
                if (input) {
                    let value = parseInt(input.value) + change;
                    if (value >= 1) input.value = value;
                }
            };
            
            window.updateQuantity = function(value) {
                const input = document.getElementById('quantity');
                if (input) input.value = value;
            };
            
            window.changeMainImage = function(imageSrc, thumbnail) {
                const mainImage = document.getElementById('mainProductImage');
                if (mainImage) mainImage.src = imageSrc;
                
                // Update active thumbnail
                document.querySelectorAll('.thumbnail').forEach(thumb => {
                    thumb.classList.remove('active');
                });
                if (thumbnail) thumbnail.classList.add('active');
            };
            
            window.shareOnFacebook = function() {
                window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank');
            };
            
            window.shareOnTwitter = function() {
                window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent('Check out this product!') + '&url=' + encodeURIComponent(window.location.href), '_blank');
            };
            
            window.shareOnWhatsApp = function() {
                window.open('https://wa.me/<?php echo htmlspecialchars($store_settings['whatsapp_number']); ?>?text=' + encodeURIComponent('Check out this product! ' + window.location.href), '_blank');
            };
            
            window.copyProductLink = function() {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link copied to clipboard!');
                });
            };
        }
    </script>
    
    <?php
    // Close the database connection at the very end
    if (isset($conn) && $conn) {
        $conn->close();
    }
    ?>
</body>
</html>