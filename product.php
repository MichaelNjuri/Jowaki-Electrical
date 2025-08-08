<?php
session_start();
require_once 'API/db_connection.php';

// Get product ID from URL parameter
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if (!$product_id) {
    header('Location: Store.php');
    exit();
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: Store.php');
    exit();
}

// Fetch related products (same category, excluding current product)
$related_stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND is_active = 1 LIMIT 4");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-page {
            padding: 2rem 0;
            background: var(--background-color);
        }
        
        .product-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .breadcrumb {
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        .breadcrumb a {
            color: var(--text-secondary);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            color: var(--primary-color);
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }
        
        .product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .product-info {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .product-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }
        
        .product-category {
            color: var(--primary-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .current-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--price-highlight);
        }
        
        .original-price {
            font-size: 1.2rem;
            color: var(--text-secondary);
            text-decoration: line-through;
        }
        
        .discount-badge {
            background: var(--price-highlight);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .stock-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }
        
        .stock-status.in-stock {
            color: var(--success-color);
        }
        
        .stock-status.out-of-stock {
            color: var(--error-color);
        }
        
        .product-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .add-to-cart-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .quantity-input {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .quantity-btn {
            background: var(--surface-color);
            border: none;
            padding: 0.75rem;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--text-primary);
        }
        
        .quantity-btn:hover {
            background: var(--border-color);
        }
        
        .quantity-input input {
            border: none;
            padding: 0.75rem;
            text-align: center;
            width: 60px;
            font-size: 1rem;
        }
        
        .add-to-cart-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .add-to-cart-btn:hover {
            background: var(--primary-dark);
        }
        
        .add-to-cart-btn:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
        }
        
        .related-products {
            margin-top: 4rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2rem;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .related-product {
            background: var(--surface-color);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .related-product:hover {
            transform: translateY(-2px);
        }
        
        .related-product img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .related-product-info {
            padding: 1rem;
        }
        
        .related-product-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .related-product-price {
            color: var(--price-highlight);
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .product-title {
                font-size: 1.5rem;
            }
            
            .current-price {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="product-page">
        <div class="product-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="Store.php">Store</a> / 
                <a href="Store.php?category=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars(ucfirst($product['category'])); ?></a> / 
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </div>
            
            <!-- Product Details -->
            <div class="product-grid">
                <!-- Product Image -->
                <div class="product-image-section">
                    <img src="<?php echo htmlspecialchars($product['image_paths']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                </div>
                
                <!-- Product Info -->
                <div class="product-info">
                    <div class="product-category"><?php echo htmlspecialchars(ucfirst($product['category'])); ?></div>
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <!-- Price Section -->
                    <div class="product-price">
                        <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                            <span class="current-price">KSh <?php echo number_format($product['discount_price'], 2); ?></span>
                            <span class="original-price">KSh <?php echo number_format($product['price'], 2); ?></span>
                            <span class="discount-badge">
                                <?php 
                                $discount = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                                echo $discount . '% OFF';
                                ?>
                            </span>
                        <?php else: ?>
                            <span class="current-price">KSh <?php echo number_format($product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <i class="fas fa-<?php echo $product['stock'] > 0 ? 'check-circle' : 'times-circle'; ?>"></i>
                        <?php if ($product['stock'] > 0): ?>
                            In Stock (<?php echo $product['stock']; ?> available)
                        <?php else: ?>
                            Out of Stock
                        <?php endif; ?>
                    </div>
                    
                    <!-- Description -->
                    <?php if ($product['description']): ?>
                        <div class="product-description">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Add to Cart Section -->
                    <div class="add-to-cart-section">
                        <div class="quantity-input">
                            <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" 
                                   onchange="updateQuantity()">
                            <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                        </div>
                        
                        <button class="add-to-cart-btn" 
                                onclick="addToCart(<?php echo $product['id']; ?>, getQuantity())"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
                <div class="related-products">
                    <h2 class="section-title">More Products Like This</h2>
                    <div class="related-grid">
                        <?php foreach ($related_products as $related): ?>
                            <div class="related-product">
                                <a href="product.php?product_id=<?php echo $related['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($related['image_paths']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>">
                                    <div class="related-product-info">
                                        <div class="related-product-title"><?php echo htmlspecialchars($related['name']); ?></div>
                                        <div class="related-product-price">
                                            KSh <?php echo number_format($related['discount_price'] ?: $related['price'], 2); ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script type="module">
        import { addToCart } from './js/modules/store-cart.js';
        
        // Make addToCart available globally
        window.addToCart = addToCart;
        
        // Quantity functions
        window.changeQuantity = function(change) {
            const input = document.getElementById('quantity');
            const newValue = parseInt(input.value) + change;
            const max = parseInt(input.getAttribute('max'));
            const min = parseInt(input.getAttribute('min'));
            
            if (newValue >= min && newValue <= max) {
                input.value = newValue;
            }
        };
        
        window.getQuantity = function() {
            return parseInt(document.getElementById('quantity').value);
        };
        
        window.updateQuantity = function() {
            const input = document.getElementById('quantity');
            const value = parseInt(input.value);
            const max = parseInt(input.getAttribute('max'));
            const min = parseInt(input.getAttribute('min'));
            
            if (value < min) input.value = min;
            if (value > max) input.value = max;
        };
        
        // Load store products for cart integration
        async function loadStoreProducts() {
            try {
                const response = await fetch('/jowaki_electrical_srvs/API/get_products.php');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        window.storeProducts = data.products;
                    }
                }
            } catch (error) {
                console.error('Error loading store products:', error);
            }
        }
        
        // Initialize
        loadStoreProducts();
    </script>
</body>
</html> 