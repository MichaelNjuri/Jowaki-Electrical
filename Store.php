<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jowaki Store - Complete E-commerce System</title>
    <link rel="stylesheet" href="store.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>
   <?php include 'store_header.php'; ?>


    <div id="app">
        <!-- Notification -->
        <div class="notification" id="notification"></div>

        <!-- Main Store View -->
        <div id="storeView" class="store-container">
            <div class="store-header">
                <h1>🔒 Jowaki Security Store</h1>
                <p>Professional Security Solutions & Equipment</p>
            </div>

            <div class="store-nav">
                <button class="nav-btn active" onclick="filterProducts('all')">All Products</button>
                <button class="nav-btn" onclick="filterProducts('fencing')">Electric Fencing</button>
                <button class="nav-btn" onclick="filterProducts('alarms')">Alarm Systems</button>
                <button class="nav-btn" onclick="filterProducts('cctv')">CCTV</button>
                <button class="nav-btn" onclick="filterProducts('gates')">Gates</button>
                <button class="nav-btn" onclick="filterProducts('razor')">Razor Wire</button>
            </div>

            <div class="products-grid" id="productsGrid">
                <!-- Products will be loaded here -->
            </div>
        </div>

       

        
                

        <!-- Cart -->
        <div class="cart-container" onclick="toggleCart()">
            🛒
            <span class="cart-count" id="cartCount">0</span>
        </div>

        <div class="cart-sidebar" id="cartSidebar">
            <div class="cart-header">
                <h3>Shopping Cart</h3>
                <button onclick="toggleCart()" class="btn btn-secondary">✕</button>
            </div>
            <div class="cart-items" id="cartItems">
                <p style="text-align: center; padding: 20px; color: #7f8c8d;">Your cart is empty</p>
            </div>
            <div class="cart-total" id="cartTotal">
                <div class="cart-summary">
                    <p><span>Subtotal:</span> <span id="cartSubtotal">KSh 0</span></p>
                    <p><span>Tax (16%):</span> <span id="cartTax">KSh 0</span></p>
                    <p><strong>Total:</strong> <strong id="cartTotalAmount">KSh 0</strong></p>
                </div>
                <button class="btn btn-primary" onclick="startCheckout()" style="width: 100%; margin-top: 15px;">Checkout</button>
                <button class="btn btn-clear-cart" onclick="clearCart()" style="width: 100%; margin-top: 10px;">Clear Cart</button>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="modal hidden">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Checkout</h2>
                <button onclick="hideCheckout()" class="btn btn-secondary">✕</button>
            </div>
            
            <div class="checkout-steps">
                <div class="step active" id="step1">1. Customer Info</div>
                <div class="step" id="step2">2. Delivery</div>
                <div class="step" id="step3">3. Payment</div>
                <div class="step" id="step4">4. Confirmation</div>
            </div>

            <div id="checkoutContent">
                <!-- Checkout content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>🔒 Jowaki Security</h3>
                    <p>Your trusted partner for comprehensive security solutions. Protecting what matters most since 2010.</p>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p>📞 +254 721442248</p>
                    <p>📧 <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="f7bd9880969c9eb29b929483859e94969b84858184b7909a969e9bd994989a">[email&#160;protected]</a></p>
                    <p>📍 Nairobi, Kenya</p>
                </div>
                <div class="footer-section">
                    <h3>Services</h3>
                    <a href="#" onclick="showServices()">Electric Fencing</a>
                    <a href="#" onclick="showServices()">CCTV Systems</a>
                    <a href="#" onclick="showServices()">Alarm Systems</a>
                    <a href="#" onclick="showServices()">Automated Gates</a>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="#" onclick="showHome()">Home</a>
                    <a href="#" onclick="showAbout()">About Us</a>
                    <a href="#" onclick="showServices()">Services</a>
                    <a href="#">Contact</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 Jowaki Security Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script src="store.js"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'963d34bbfcbc87a4',t:'MTc1MzI5NDk3NS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>