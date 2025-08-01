<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Jowaki Electrical Services Ltd</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="service.css">
</head>
<body>
   <?php include 'header.php'; ?>


    <main>
        <div class="container">
            <div class="contact-section">
                <h1 class="section-title">Contact Us</h1>
                
                <div class="contact-container">
                    <!-- Contact Details -->
                    <div class="contact-details">
                        <h2><i class="fas fa-address-book"></i> Contact Details</h2>
                        
                        <div class="contact-item">
                            <h3><i class="fas fa-phone"></i> Call Us</h3>
                            <p>
                                <a href="tel:+254721442248">+254 721 442 248</a><br>
                                <a href="tel:+254723340274">+254 723 340 274</a><br>
                                <a href="tel:+254720947507">+254 720 947 507</a>
                            </p>
                        </div>
                        
                        <div class="contact-item">
                            <h3><i class="fas fa-envelope"></i> Mail Us</h3>
                            <p>
                                <a href="mailto:kibukush@gmail.com">kibukush@gmail.com</a>
                            </p>
                        </div>
                        
                        <div class="contact-item">
                            <h3><i class="fas fa-map-marker-alt"></i> Address</h3>
                            <p>
                                P.O Box 63616 – 00619<br>
                                Nairobi, Kenya
                            </p>
                        </div>
                        
                        <div class="social-links">
                            <a href="#" class="social-link" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="contact-form">
                        <h2><i class="fas fa-paper-plane"></i> Send Us an Email</h2>
                        
                        <form id="contactForm">
                            <div class="form-group">
                                <label for="name">Your Name *</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Your Email *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject *</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" placeholder="Tell us how we can help you..." required></textarea>
                            </div>
                            
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-paper-plane"></i>
                                Contact Us
                            </button>
                            
                            <div class="success-message" id="successMessage">
                                <i class="fas fa-check-circle"></i>
                                Message sent successfully! We'll get back to you soon.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- WhatsApp Float Button -->
  <a href="https://wa.me/254721442248?text=Hello%20Jowaki%20Electrical,%20I%20would%20like%20to%20inquire%20about%20your%20services." class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
        Chat With Us
    </a>


<script src="service.js"></script>

</body>
</html>