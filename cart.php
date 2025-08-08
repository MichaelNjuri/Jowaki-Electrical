<?php
session_start();
require_once 'API/db_connection.php';
require_once 'API/load_settings.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Load store settings
$store_settings = getStoreSettings($conn);

// Calculate totals
$subtotal = array_reduce($cart, function($sum, $item) {
    return $sum + ($item['price'] * $item['quantity']);
}, 0);
$tax = $subtotal * ($store_settings['tax_rate'] / 100);
$delivery_fee = 0; // Will be calculated based on delivery method
$total = $subtotal + $tax + $delivery_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Jowaki Store</title>
    <link rel="stylesheet" href="store.css">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .cart-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .cart-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
        }
        
        .cart-items {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            border-radius: 0.5rem;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .cart-item-price {
            color: var(--price-highlight);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid var(--border-color);
            background: white;
            border-radius: 0.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .quantity-btn:hover {
            background: var(--surface-color);
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid var(--border-color);
            border-radius: 0.25rem;
            padding: 0.25rem;
        }
        
        .remove-btn {
            background: var(--error-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .remove-btn:hover {
            background: #b91c1c;
        }
        
        .cart-summary {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .summary-header {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .summary-row.total {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .cart-actions {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: var(--surface-color);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover {
            background: var(--border-color);
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }
        
        .empty-cart-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-cart h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .cart-summary {
                position: static;
            }
            
            .cart-container {
                padding: 1rem;
            }
            
            .cart-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'store_header.php'; ?>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>

        <?php if (empty($cart)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <a href="Store.php" class="btn btn-primary" style="margin-top: 2rem;">
                <i class="fas fa-shopping-bag"></i>
                Continue Shopping
            </a>
        </div>
        <?php else: ?>
        <div class="cart-content">
            <div class="cart-items">
                <h3>Shopping Cart (<?php echo count($cart); ?> items)</h3>
                <?php foreach ($cart as $item): ?>
                <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                    <div class="cart-item-image">
                        <img src="<?php echo htmlspecialchars($item['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="cart-item-price">KSh <?php echo number_format($item['price'], 2); ?></div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" onchange="updateQuantity(<?php echo $item['id']; ?>, this.value - <?php echo $item['quantity']; ?>)">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                        </div>
                    </div>
                    <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                        <i class="fas fa-trash"></i>
                        Remove
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-header">
                    <i class="fas fa-calculator"></i>
                    Order Summary
                </div>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>KSh <?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (<?php echo $store_settings['tax_rate']; ?>%)</span>
                    <span>KSh <?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span>KSh <?php echo number_format($delivery_fee, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>KSh <?php echo number_format($total, 2); ?></span>
                </div>
                
                <div class="cart-actions">
                    <a href="checkout.php" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i>
                        Proceed to Checkout
                    </a>
                    <a href="Store.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Cart functionality
        async function updateQuantity(productId, change) {
            try {
                const response = await fetch('API/update_cart_quantity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        change: change
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload(); // Refresh page to show updated cart
                } else {
                    alert('Error updating quantity: ' + data.error);
                }
            } catch (error) {
                console.error('Error updating quantity:', error);
                alert('Error updating quantity');
            }
        }
        
        async function removeFromCart(productId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }
            
            try {
                const response = await fetch('API/remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload(); // Refresh page to show updated cart
                } else {
                    alert('Error removing item: ' + data.error);
                }
            } catch (error) {
                console.error('Error removing item:', error);
                alert('Error removing item');
            }
        }
    </script>
</body>
</html>