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
    <title>Order Confirmation - Jowaki Electrical Services</title>
    <link rel="stylesheet" href="assets/css/store.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        .thank-you-container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            width: 60px;
            height: 60px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }

        .order-number {
            background: #f3f4f6;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            display: inline-block;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #374151;
        }

        .info-text {
            color: #6b7280;
            margin: 1.5rem 0;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .contact-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .contact-info a {
            color: #2563eb;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .thank-you-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'store_header.php'; ?>

    <div class="store-container">
        <div class="thank-you-container">
            <!-- Success Icon -->
            <div class="success-icon">✓</div>

            <!-- Main Message -->
            <h1 style="color: #1f2937; margin-bottom: 0.5rem;">Thank You!</h1>
            <p class="info-text">Your order has been successfully placed.</p>

            <!-- Order Number -->
            <div class="order-number">Order #<?php echo $order_id; ?></div>

            <!-- Brief Info -->
            <p class="info-text">
                You'll receive an order confirmation email shortly. 
                We'll contact you to confirm delivery details.
            </p>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="Store.php" class="btn btn-primary">Continue Shopping</a>
                <a href="profile.php" class="btn btn-secondary">My Account</a>
            </div>

            <!-- Contact Info -->
            <div class="contact-info">
                <p>Need help? <a href="mailto:jowakielectricalsrvs@gmail.com">Email us</a> or call <a href="tel:+254721442248">+254 721 442 248</a></p>
            </div>
        </div>
    </div>
</body>
</html>