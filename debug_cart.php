<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .debug-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .debug-item {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .debug-item h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .debug-item pre {
            background: #e9ecef;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        .cart-item {
            background: #e3f2fd;
            padding: 10px;
            margin: 5px 0;
            border-radius: 3px;
            border-left: 3px solid #2196f3;
        }
        .clear-cart {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 0;
        }
        .clear-cart:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="debug-container">
        <h1>Cart Debug Information</h1>
        
        <div class="debug-item">
            <h3>Session Information</h3>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
        
        <div class="debug-item">
            <h3>Cart Array</h3>
            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <pre><?php print_r($_SESSION['cart']); ?></pre>
                
                <h4>Cart Items Breakdown:</h4>
                <?php 
                $total_quantity = 0;
                $unique_items = 0;
                foreach ($_SESSION['cart'] as $item): 
                    $total_quantity += $item['quantity'];
                    $unique_items++;
                ?>
                    <div class="cart-item">
                        <strong>ID:</strong> <?php echo $item['id']; ?><br>
                        <strong>Name:</strong> <?php echo $item['name']; ?><br>
                        <strong>Price:</strong> KSh <?php echo $item['price']; ?><br>
                        <strong>Quantity:</strong> <?php echo $item['quantity']; ?><br>
                        <strong>Image:</strong> <?php echo $item['image']; ?>
                    </div>
                <?php endforeach; ?>
                
                <h4>Summary:</h4>
                <p><strong>Total Items:</strong> <?php echo $unique_items; ?></p>
                <p><strong>Total Quantity:</strong> <?php echo $total_quantity; ?></p>
                <p><strong>Cart Count (as calculated):</strong> <?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?></p>
            <?php else: ?>
                <p>Cart is empty</p>
            <?php endif; ?>
        </div>
        
        <div class="debug-item">
            <h3>Cart Count Calculation</h3>
            <p><strong>Method 1 (array_sum):</strong> <?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?></p>
            <p><strong>Method 2 (foreach):</strong> 
                <?php 
                $count = 0;
                if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $count += $item['quantity'];
                    }
                }
                echo $count;
                ?>
            </p>
        </div>
        
        <form method="post">
            <button type="submit" name="clear_cart" class="clear-cart">Clear Cart</button>
        </form>
        
        <p><a href="Store.php">Back to Store</a> | <a href="cart.php">View Cart</a></p>
    </div>
    
    <?php
    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        echo '<script>alert("Cart cleared!"); window.location.reload();</script>';
    }
    ?>
</body>
</html> 