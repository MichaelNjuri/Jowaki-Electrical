<?php
session_start();
require_once 'API/db_connection.php';

// Get product ID from URL parameter
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: Store.php');
    exit();
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: Store.php');
    exit();
}

// Fetch related products (same category, excluding current product)
$related_stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
$related_stmt->bind_param("si", $product['category'], $product_id);
$related_stmt->execute();
$related_products = $related_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Jowaki Store</title>
    <link rel="stylesheet" href="store.css">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-detail-main {
            padding: var(--space-2xl) 0;
            background: var(--background-color);
        }
        
        .product-detail-container {
            max-width: var(--container-max-width);
            margin: 0 auto;
            padding: 0 var(--space-lg);
        }
        
        .breadcrumb {
            background: var(--surface-color);
            padding: var(--space-md) 0;
            margin-bottom: var(--space-xl);
        }
        
        .breadcrumb .container {
            max-width: var(--container-max-width);
            margin: 0 auto;
            padding: 0 var(--space-lg);
        }
        
        .breadcrumb a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        .breadcrumb a:hover {
            color: var(--primary-color);
        }
        
        .breadcrumb .separator {
            margin: 0 var(--space-sm);
            color: var(--text-muted);
        }
        
        .product-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-2xl);
            margin-bottom: var(--space-3xl);
        }
        
        .product-images {
            position: relative;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }
        
        .product-info {
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
        }
        
        .product-header {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }
        
        .product-title {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }
        
        .product-category {
            font-size: var(--font-size-sm);
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .product-price-section {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            flex-wrap: wrap;
        }
        
        .current-price {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--price-highlight);
        }
        
        .original-price {
            font-size: var(--font-size-lg);
            color: var(--text-secondary);
            text-decoration: line-through;
        }
        
        .discount-badge {
            background: var(--price-highlight);
            color: white;
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            font-weight: 600;
        }
        
        .stock-status {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: var(--font-size-sm);
            font-weight: 600;
        }
        
        .stock-status.in-stock {
            color: var(--success-color);
        }
        
        .stock-status.out-of-stock {
            color: var(--error-color);
        }
        
        .stock-status i {
            font-size: var(--font-size-base);
        }
        
        .product-description {
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: var(--font-size-base);
        }
        
        .product-specs {
            background: var(--surface-color);
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
        }
        
        .specs-title {
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--space-md);
        }
        
        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: var(--space-sm) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .spec-item:last-child {
            border-bottom: none;
        }
        
        .spec-label {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .spec-value {
            color: var(--text-secondary);
        }
        
        .product-actions {
            display: flex;
            gap: var(--space-md);
            flex-wrap: wrap;
        }
        
        .btn-add-cart {
            flex: 1;
            padding: var(--space-lg) var(--space-xl);
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--font-size-base);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
        }
        
        .btn-add-cart:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-add-cart:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-whatsapp {
            padding: var(--space-lg) var(--space-xl);
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--font-size-base);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            text-decoration: none;
        }
        
        .btn-whatsapp:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 1px solid var(--border-color);
            background: var(--background-color);
            color: var(--text-primary);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background: var(--surface-color);
            border-color: var(--primary-color);
        }
        
        .quantity-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--font-size-base);
            font-weight: 600;
        }
        
        .social-share {
            display: flex;
            gap: var(--space-sm);
            margin-top: var(--space-lg);
        }
        
        .share-btn {
            width: 40px;
            height: 40px;
            border: 1px solid var(--border-color);
            background: var(--background-color);
            color: var(--text-primary);
            border-radius: 50%;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .share-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .related-section {
            margin-top: var(--space-3xl);
        }
        
        .section-title {
            font-size: var(--font-size-2xl);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--space-xl);
            text-align: center;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: var(--space-lg);
        }
        
        .related-card {
            background: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: all var(--transition-base);
            cursor: pointer;
        }
        
        .related-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .related-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .related-content {
            padding: var(--space-md);
        }
        
        .related-title {
            font-size: var(--font-size-base);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--space-xs);
            line-height: 1.3;
        }
        
        .related-price {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--price-highlight);
        }
        
        @media (max-width: 768px) {
            .product-detail-grid {
                grid-template-columns: 1fr;
                gap: var(--space-xl);
            }
            
            .product-title {
                font-size: var(--font-size-2xl);
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .related-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: var(--space-md) var(--space-lg);
            border-radius: var(--radius-md);
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform var(--transition-base);
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: var(--success-color);
        }
        
        .notification.error {
            background: var(--error-color);
        }
    </style>
</head>
<body>
    <?php include 'store_header.php'; ?>

    <div class="product-detail-main">
        <div class="product-detail-container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <div class="container">
                    <a href="Store.php">Home</a>
                    <span class="separator">/</span>
                    <a href="Store.php">Products</a>
                    <span class="separator">/</span>
                    <span><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
            </nav>

            <!-- Product Detail Grid -->
            <div class="product-detail-grid">
                <!-- Product Images -->
                <div class="product-images">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="main-image">
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <div class="product-header">
                        <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="product-price-section">
                            <?php 
                            $original_price = floatval($product['price']);
                            $discount_price = floatval($product['discount_price']);
                            $current_price = $discount_price > 0 ? $discount_price : $original_price;
                            $discount_percentage = $discount_price > 0 ? round((($original_price - $discount_price) / $original_price) * 100) : 0;
                            ?>
                            
                            <span class="current-price">KSh <?php echo number_format($current_price, 2); ?></span>
                            
                            <?php if ($discount_price > 0): ?>
                                <span class="original-price">KSh <?php echo number_format($original_price, 2); ?></span>
                                <span class="discount-badge">-<?php echo $discount_percentage; ?>%</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <i class="fas fa-<?php echo $product['stock'] > 0 ? 'check-circle' : 'times-circle'; ?>"></i>
                            <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            <?php if ($product['stock'] > 0): ?>
                                (<?php echo $product['stock']; ?> available)
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>

                    <div class="product-specs">
                        <h3 class="specs-title">Product Specifications</h3>
                        <div class="spec-item">
                            <span class="spec-label">Category:</span>
                            <span class="spec-value"><?php echo htmlspecialchars($product['category']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Brand:</span>
                            <span class="spec-value"><?php echo htmlspecialchars($product['brand'] ?? 'Jowaki'); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Stock:</span>
                            <span class="spec-value"><?php echo $product['stock']; ?> units</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">SKU:</span>
                            <span class="spec-value"><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                        <button class="quantity-btn" onclick="updateQuantity(1)">+</button>
                    </div>

                    <div class="product-actions">
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)" 
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i>
                            <?php echo $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                        </button>
                        
                        <a href="https://wa.me/254721442248?text=Hello%20Jowaki%20Electrical,%20I%20would%20like%20to%20order%20<?php echo urlencode($product['name']); ?>%20(Product%20ID:%20<?php echo $product['id']; ?>)" 
                           class="btn-whatsapp" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                            Order via WhatsApp
                        </a>
                    </div>

                    <div class="social-share">
                        <button class="share-btn" onclick="shareProduct()" title="Share Product">
                            <i class="fas fa-share-alt"></i>
                        </button>
                        <button class="share-btn" onclick="shareOnFacebook()" title="Share on Facebook">
                            <i class="fab fa-facebook"></i>
                        </button>
                        <button class="share-btn" onclick="shareOnTwitter()" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
            <div class="related-section">
                <h2 class="section-title">Related Products</h2>
                <div class="related-grid">
                    <?php foreach ($related_products as $related): ?>
                    <div class="related-card" onclick="window.location.href='product-detail.php?id=<?php echo $related['id']; ?>'">
                        <img src="<?php echo htmlspecialchars($related['image']); ?>" 
                             alt="<?php echo htmlspecialchars($related['name']); ?>" 
                             class="related-image">
                        <div class="related-content">
                            <h3 class="related-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="related-price">
                                KSh <?php echo number_format(floatval($related['discount_price'] ?: $related['price']), 2); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/254721442248?text=Hello%20Jowaki%20Electrical,%20I%20would%20like%20to%20inquire%20about%20your%20products." class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
        Chat With Us
    </a>

    <script>
        // Quantity controls
        function updateQuantity(change) {
            const input = document.getElementById('quantity');
            const newValue = parseInt(input.value) + change;
            const max = parseInt(input.getAttribute('max'));
            
            if (newValue >= 1 && newValue <= max) {
                input.value = newValue;
            }
        }

        // Add to cart functionality
        async function addToCart(productId) {
            const quantity = parseInt(document.getElementById('quantity').value);
            
            try {
                const response = await fetch('API/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: productId,
                        quantity: quantity
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Product added to cart successfully!', 'success');
                    // Update cart count if available
                    const cartCountElement = document.querySelector('#cartCount');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.cartCount || 0;
                    }
                } else {
                    showNotification(data.message || 'Failed to add product to cart', 'error');
                }
            } catch (error) {
                showNotification('Error adding product to cart', 'error');
            }
        }

        // Share functionality
        function shareProduct() {
            const productName = '<?php echo addslashes($product['name']); ?>';
            const productUrl = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: productName,
                    text: `Check out this amazing product: ${productName}`,
                    url: productUrl
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(productUrl).then(() => {
                    showNotification('Product link copied to clipboard!', 'success');
                });
            }
        }

        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        }

        function shareOnTwitter() {
            const text = encodeURIComponent(`Check out this amazing product: <?php echo addslashes($product['name']); ?>`);
            const url = encodeURIComponent(window.location.href);
            window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
        }

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>
