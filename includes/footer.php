<?php
// Get store settings if not already loaded
if (!isset($store_settings)) {
    require_once 'load_settings.php';
    $store_settings = getStoreSettings(null);
}
?>
<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Jowaki</h3>
                <p>Your trusted partner for comprehensive security solutions. Protecting what matters most since 2011 with professional excellence and innovative technology.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="#home">Home</a>
                <a href="#about">About Us</a>
                <a href="#services">Services</a>
                <a href="Store.php">Shop</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p>📞 +254 721442248</p>
                <p>📧 info@jowaki.com</p>
                <p>📍 Nairobi, Kenya</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <?php if (isset($store_settings['enable_facebook']) && $store_settings['enable_facebook'] && !empty($store_settings['facebook_url'])): ?>
                    <a href="<?php echo htmlspecialchars($store_settings['facebook_url']); ?>" target="_blank" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (isset($store_settings['enable_whatsapp']) && $store_settings['enable_whatsapp']): ?>
                    <a href="https://wa.me/<?php echo htmlspecialchars($store_settings['whatsapp_number'] ?? '+254721442248'); ?>" target="_blank" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <?php endif; ?>
                    <a href="tel:<?php echo htmlspecialchars($store_settings['store_phone'] ?? '+254721442248'); ?>" title="Call Us">
                        <i class="fas fa-phone"></i>
                    </a>
                    <a href="mailto:<?php echo htmlspecialchars($store_settings['store_email'] ?? 'info@jowaki.com'); ?>" title="Email Us">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Jowaki Electrical Services Ltd. All rights reserved.</p>
        </div>
    </div>
</footer>

