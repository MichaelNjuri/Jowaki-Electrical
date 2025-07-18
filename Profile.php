
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User Profile</title>
    <link rel="stylesheet" href="profile.css" />
</head>
<body>
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <img src="Logo.jpg" alt="Jowaki Logo" class="logo-img" />
                <span class="logo-text">JOWAKI ELECTRICAL SERVICES LTD</span>
            </div>
            <nav class="main-nav">
                <a href="Index.html" class="nav-link">Home</a>
                <a href="Service.html" class="nav-link">Services</a>
                <a href="Store.html" class="nav-link shop-link">🛒 Shop</a>
                <a href="login.html" class="nav-link login-link">👤 Login</a>
                <a href="Profile.php" class="nav-link profile-link">Profile</a>
            </nav>
            <div class="contact-quick">
                <div class="contact-item">
                    <span>📞 </span><span id="contact-phone">0721442248</span>
                </div>
                <div class="contact-item">
                    <span>✉️</span><span id="contact-email">Jowakielecricalsrvs@gmail.com</span>
                </div>
            </div>
        </div>
    </div>
</header>

<main>
    <section class="section">
        <div class="container">
            <h2 class="section-title">Profile Overview</h2>
            <div class="content-grid">

                <div class="card">
                    <h2><span class="card-icon">👤</span> Personal Information</h2>
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value" id="profile-name">
                            <?php echo htmlspecialchars($userName); ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value" id="profile-email">
                            <?php echo htmlspecialchars($userEmail); ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value" id="profile-phone">
                            <?php echo htmlspecialchars($userExtra['phone']); ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Location</span>
                        <span class="info-value" id="profile-location">Kenya</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Member Since</span>
                        <span class="info-value" id="profile-member-since">
                            <?php echo date("F j, Y", strtotime($userExtra['created_at'])); ?>
                        </span>
                    </div>
                    <a href="/edit-profile" class="edit-btn">Edit Profile</a>
                </div>
<div class="card">
    <h2><span class="card-icon">📍</span> Delivery Address</h2>
    <div class="info-row">
        <span class="info-label">Address</span>
        <span class="info-value" id="address-street">
            <?php echo isset($userExtra['address']) ? htmlspecialchars($userExtra['address']) : 'Not provided yet'; ?>
        </span>
    </div>
    <div class="info-row">
        <span class="info-label">Postal Code</span>
        <span class="info-value" id="address-postal">
            <?php echo isset($userExtra['postal_code']) ? htmlspecialchars($userExtra['postal_code']) : 'Not provided yet'; ?>
        </span>
    </div>
    <div class="info-row">
        <span class="info-label">City</span>
        <span class="info-value" id="address-city">
            <?php echo isset($userExtra['city']) ? htmlspecialchars($userExtra['city']) : 'Not provided yet'; ?>
        </span>
    </div>
    <a href="/edit-address" class="edit-btn">Update Address</a>
</div>


                <div class="card">
                    <h2><span class="card-icon">📦</span> Order History</h2>
                    <div id="order-list">No orders yet.</div>
                </div>

            </div>
        </div>
    </section>
</main>

<script src="profile.js"></script>
</body>
</html>
