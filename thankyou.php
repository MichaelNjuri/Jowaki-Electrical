<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id === 0) {
    header('Location: Store.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jowaki Store - Order Confirmation</title>
    <link rel="stylesheet" href="store.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php include 'store_header.php'; ?>

    <div class="store-container">
        <div class="store-header">
            <h1>Thank You for Your Order!</h1>
            <p>Order #<?php echo $order_id; ?> has been placed successfully.</p>
        </div>
        <div class="checkout-section">
            <p>Your order confirmation has been sent to your email. If you created an account during checkout, you can view your order history in your <a href="account.php" class="btn btn-secondary">Account</a>.</p>
            <p>We've sent your login credentials to your email if a new account was created for you.</p>
            <a href="Store.php" class="btn btn-primary" style="margin-top: 1rem;">Continue Shopping</a>
        </div>
    </div>
</body>
</html>