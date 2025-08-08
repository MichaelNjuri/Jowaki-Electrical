# Jowaki Electrical Services - Complete E-commerce System

## 🏢 About Jowaki Electrical Services

Jowaki Electrical Services is a comprehensive e-commerce platform specializing in electrical and security equipment. The system provides both customer-facing store functionality and comprehensive admin management capabilities.

## 📋 Table of Contents

1. [System Overview](#system-overview)
2. [Customer Features](#customer-features)
3. [Admin Features](#admin-features)
4. [Technical Architecture](#technical-architecture)
5. [Installation & Setup](#installation--setup)
6. [Email System](#email-system)
7. [WhatsApp Integration](#whatsapp-integration)
8. [Database Structure](#database-structure)
9. [API Documentation](#api-documentation)
10. [Troubleshooting](#troubleshooting)
11. [Security Features](#security-features)
12. [Testing](#testing)

## 🎯 System Overview

### Core Components
- **Customer Store**: Product browsing, cart management, checkout
- **Admin Dashboard**: Order management, inventory control, analytics
- **Email System**: Automated notifications for orders and status updates
- **WhatsApp Integration**: Direct customer communication
- **User Management**: Registration, login, password reset
- **Payment Processing**: M-Pesa and card payment support

### Technology Stack
- **Backend**: PHP 7.4+, MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Email**: PHP mail() function with HTML templates
- **Database**: MySQL with optimized queries
- **Security**: Password hashing, SQL injection prevention, XSS protection

## 👥 Customer Features

### 1. User Registration & Authentication
- **Registration**: New user account creation with email verification
- **Login**: Secure authentication with session management
- **Password Reset**: Email-based password recovery system
- **Profile Management**: Update personal information and addresses

### 2. Product Browsing & Shopping
- **Product Catalog**: Browse all available products with images and descriptions
- **Category Filtering**: Filter products by categories (CCTV, Security, etc.)
- **Search Functionality**: Search products by name, description, or category
- **Product Details**: Detailed product pages with specifications and pricing
- **Stock Status**: Real-time stock availability indicators

### 3. Shopping Cart & Checkout
- **Cart Management**: Add/remove items, update quantities
- **Cart Persistence**: Cart items saved across sessions
- **Checkout Process**: Multi-step checkout with delivery options
- **Payment Methods**: M-Pesa and card payment integration
- **Order Confirmation**: Email confirmation with order details

### 4. Order Management
- **Order History**: View all past orders with status
- **Order Tracking**: Track order status updates
- **Order Details**: Detailed view of order items and delivery info
- **Status Notifications**: Email updates for order status changes

### 5. Communication Features
- **WhatsApp Integration**: Direct chat with customer service
- **Contact Form**: Send inquiries and support requests
- **Email Notifications**: Order confirmations and status updates

## 🔧 Admin Features

### 1. Dashboard & Analytics
- **Sales Analytics**: Revenue tracking and sales reports
- **Order Statistics**: Order volume and status distribution
- **Customer Analytics**: Customer behavior and demographics
- **Inventory Overview**: Stock levels and low stock alerts

### 2. Product Management
- **Add Products**: Create new products with images and details
- **Edit Products**: Update product information and pricing
- **Delete Products**: Remove products from catalog
- **Stock Management**: Update stock levels and track inventory
- **Category Management**: Organize products into categories

### 3. Order Management
- **Order Processing**: View and process incoming orders
- **Status Updates**: Update order status (pending, processing, shipped, delivered)
- **Order Details**: View complete order information
- **Customer Communication**: Send order updates and notifications

### 4. Customer Management
- **Customer Database**: View all registered customers
- **Customer Details**: Individual customer profiles and order history
- **Customer Analytics**: Purchase patterns and preferences

### 5. System Settings
- **Store Configuration**: Store name, contact information, logo
- **Payment Settings**: Configure payment methods and fees
- **Delivery Settings**: Set delivery fees and methods
- **Email Settings**: Configure email notifications
- **WhatsApp Settings**: Set WhatsApp number and messages

### 6. Content Management
- **Category Management**: Create and manage product categories
- **Image Upload**: Upload product and category images
- **CSV Import**: Bulk import products from CSV files
- **Backup & Restore**: Database backup and restoration

## 🏗️ Technical Architecture

### File Structure
```
jowaki_electrical_srvs/
├── admin/                          # Admin dashboard files
├── API/                           # Backend API endpoints
│   ├── email_service.php          # Email notification system
│   ├── place_order.php            # Order processing
│   ├── update_order_status.php    # Order status updates
│   ├── contact_form.php           # Contact form handler
│   ├── forgot_password.php        # Password reset request
│   ├── reset_password.php         # Password reset processing
│   └── ...                        # Other API endpoints
├── js/modules/                    # JavaScript modules
│   ├── store-ui.js               # Store UI functionality
│   ├── store-products.js         # Product management
│   ├── store-cart.js             # Cart functionality
│   ├── store-checkout.js         # Checkout process
│   └── ...                       # Other modules
├── css/                          # Stylesheets
├── Uploads/                      # Uploaded images
├── Store.php                     # Main store page
├── cart.php                      # Shopping cart page
├── checkout.php                  # Checkout page
├── Service.php                   # Contact page
└── AdminDashboard.html           # Admin dashboard
```

### Database Schema
```sql
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    subtotal DECIMAL(10,2),
    tax DECIMAL(10,2),
    delivery_fee DECIMAL(10,2),
    total DECIMAL(10,2),
    customer_info JSON,
    cart JSON,
    delivery_method VARCHAR(100),
    delivery_address TEXT,
    payment_method VARCHAR(100),
    order_date DATETIME,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Password resets table
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System settings table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- SSL certificate (recommended for production)

### Installation Steps

1. **Clone/Download the Project**
   ```bash
   # Place files in your web server directory
   /path/to/your/web/root/jowaki_electrical_srvs/
   ```

2. **Database Setup**
   ```sql
   -- Create database
   CREATE DATABASE jowaki_db;
   USE jowaki_db;
   
   -- Import database structure
   -- (Tables will be created automatically by the system)
   ```

3. **Configure Database Connection**
   ```php
   // Edit API/db_connection.php
   $host = 'localhost';
   $user = 'your_db_user';
   $pass = 'your_db_password';
   $dbname = 'jowaki_db';
   ```

4. **Set File Permissions**
   ```bash
   chmod 755 Uploads/
   chmod 644 *.php
   ```

5. **Configure Email Settings**
   ```php
   // Edit API/email_service.php
   private $smtp_host = 'your_smtp_host';
   private $smtp_port = 587;
   private $smtp_username = 'your_email@domain.com';
   private $smtp_password = 'your_email_password';
   ```

6. **Configure WhatsApp Settings**
   ```php
   // Edit API/load_settings.php
   'whatsapp_number' => '254721442248',
   'whatsapp_message' => 'Hello Jowaki Electrical, I would like to inquire about your products.',
   ```

### Initial Setup

1. **Access Admin Dashboard**
   - URL: `http://your-domain/jowaki_electrical_srvs/AdminDashboard.html`
   - Default admin credentials: admin@jowaki.com / admin123

2. **Configure Store Settings**
   - Store name and contact information
   - Payment methods (M-Pesa, card payments)
   - Delivery options and fees
   - Email notification settings

3. **Add Products**
   - Upload product images
   - Set product details and pricing
   - Configure categories

4. **Test Customer Features**
   - Browse products
   - Add items to cart
   - Complete checkout process
   - Test email notifications

## 📧 Email System

### Email Templates
The system includes professional HTML email templates for:
- **Order Confirmations**: Customer order confirmation emails
- **Status Updates**: Order status change notifications
- **Welcome Emails**: New account creation emails
- **Password Reset**: Password reset request emails
- **Admin Notifications**: New order alerts for admins
- **Contact Form**: Contact form submission notifications

### Email Configuration
```php
// Email service configuration
$emailService = new EmailService([
    'smtp_host' => 'your_smtp_host',
    'smtp_port' => 587,
    'smtp_username' => 'noreply@jowaki.com',
    'smtp_password' => 'your_password',
    'from_email' => 'noreply@jowaki.com',
    'from_name' => 'Jowaki Electrical Services'
]);
```

### Email Functions
```php
// Send order confirmation
sendOrderConfirmationEmail($order_data, $customer_email, $customer_name);

// Send status update
sendOrderStatusEmail($order_data, $customer_email, $customer_name, $new_status);

// Send welcome email
sendWelcomeEmail($customer_email, $customer_name, $password);

// Send password reset
sendPasswordResetEmail($customer_email, $reset_token);

// Send admin notification
sendAdminOrderNotification($order_data, $admin_email);

// Send contact form notification
sendContactFormNotification($form_data, $admin_email);
```

## 📱 WhatsApp Integration

### Features
- **Floating WhatsApp Button**: Always visible on store pages
- **Product-Specific Messages**: Pre-filled messages for specific products
- **Dynamic Number**: Configurable WhatsApp number from admin settings
- **Fallback Support**: Handles popup blockers gracefully

### Configuration
```javascript
// WhatsApp number from settings
const whatsappNumber = document.querySelector('meta[name="whatsapp-number"]')?.getAttribute('content') || '254721442248';

// Clean and format number
const cleanNumber = whatsappNumber.replace(/[^\d+]/g, '');
const finalNumber = cleanNumber.startsWith('+') ? cleanNumber.substring(1) : cleanNumber;

// Create WhatsApp URL
const whatsappUrl = `https://wa.me/${finalNumber}?text=${encodeURIComponent(message)}`;
```

### Usage
```javascript
// Order by WhatsApp
window.orderByWhatsApp(productId, storeProducts);

// Share product on WhatsApp
window.shareOnWhatsApp();
```

## 🗄️ Database Structure

### Core Tables

#### Users Table
- Stores customer account information
- Supports profile management
- Links to orders and password resets

#### Orders Table
- Stores complete order information
- JSON fields for flexible data storage
- Status tracking and timestamps

#### Order Items Table
- Individual items within orders
- Links products to orders
- Stores quantity and price at time of purchase

#### Password Resets Table
- Secure password reset functionality
- Token-based authentication
- Automatic expiration

#### System Settings Table
- Configurable store settings
- Admin-managed preferences
- Dynamic configuration updates

### Data Relationships
```
Users (1) → (Many) Orders
Orders (1) → (Many) Order Items
Users (1) → (Many) Password Resets
```

## 🔌 API Documentation

### Authentication Endpoints

#### POST /API/login.php
**Purpose**: User login
**Request**:
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```
**Response**:
```json
{
    "success": true,
    "user": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "user@example.com"
    }
}
```

#### POST /API/signup.php
**Purpose**: User registration
**Request**:
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "user@example.com",
    "password": "password123",
    "phone": "+254721442248"
}
```

#### POST /API/forgot_password.php
**Purpose**: Request password reset
**Request**:
```json
{
    "email": "user@example.com"
}
```

#### POST /API/reset_password.php
**Purpose**: Reset password with token
**Request**:
```json
{
    "token": "reset_token_here",
    "password": "new_password",
    "confirm_password": "new_password"
}
```

### Store Endpoints

#### GET /API/get_products.php
**Purpose**: Get product catalog
**Parameters**:
- `category` (optional): Filter by category
- `search` (optional): Search term
- `sort` (optional): Sort order

#### POST /API/add_to_cart.php
**Purpose**: Add item to cart
**Request**:
```json
{
    "product_id": 1,
    "quantity": 2
}
```

#### POST /API/place_order.php
**Purpose**: Process order
**Request**:
```json
{
    "customer_info": {
        "firstName": "John",
        "lastName": "Doe",
        "email": "user@example.com",
        "phone": "+254721442248",
        "address": "123 Main St",
        "city": "Nairobi",
        "postalCode": "00100"
    },
    "cart": [...],
    "subtotal": 1000.00,
    "tax": 160.00,
    "delivery_fee": 0.00,
    "total": 1160.00,
    "delivery_method": "standard",
    "payment_method": "mpesa"
}
```

### Admin Endpoints

#### GET /API/get_orders.php
**Purpose**: Get order list for admin
**Parameters**:
- `status` (optional): Filter by status
- `date_from` (optional): Start date
- `date_to` (optional): End date

#### POST /API/update_order_status.php
**Purpose**: Update order status
**Request**:
```json
{
    "order_id": 1,
    "status": "processing",
    "notes": "Order is being processed"
}
```

#### GET /API/get_dashboard_stats.php
**Purpose**: Get dashboard statistics
**Response**:
```json
{
    "success": true,
    "stats": {
        "total_orders": 150,
        "total_revenue": 250000.00,
        "pending_orders": 5,
        "total_customers": 45
    }
}
```

## 🔒 Security Features

### Authentication & Authorization
- **Password Hashing**: Bcrypt password hashing
- **Session Management**: Secure session handling
- **Token-based Reset**: Secure password reset tokens
- **Input Validation**: Comprehensive input sanitization

### Data Protection
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Output encoding
- **CSRF Protection**: Token-based CSRF protection
- **Input Sanitization**: Comprehensive input cleaning

### Security Headers
```php
// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

## 🧪 Testing

### Manual Testing Checklist

#### Customer Features
- [ ] User registration and login
- [ ] Product browsing and search
- [ ] Cart functionality (add, remove, update)
- [ ] Checkout process
- [ ] Order placement and confirmation
- [ ] Email notifications
- [ ] WhatsApp integration
- [ ] Contact form submission
- [ ] Password reset functionality

#### Admin Features
- [ ] Admin login and dashboard access
- [ ] Product management (add, edit, delete)
- [ ] Order management and status updates
- [ ] Customer management
- [ ] Analytics and reporting
- [ ] Settings configuration
- [ ] Email notification testing

#### System Features
- [ ] Database connectivity
- [ ] Email sending functionality
- [ ] File upload capabilities
- [ ] Error handling and logging
- [ ] Security features
- [ ] Performance optimization

### Automated Testing
```bash
# Test database connection
php API/test_db.php

# Test email functionality
php API/test_email.php

# Test order placement
php API/test_place_order.php

# Test WhatsApp integration
php API/test_whatsapp.php
```

## 🐛 Troubleshooting

### Common Issues

#### Email Not Sending
1. Check SMTP configuration in `API/email_service.php`
2. Verify server mail settings
3. Check error logs for mail errors
4. Test with simple mail() function

#### WhatsApp Button Not Working
1. Verify WhatsApp number format (should be 254xxxxxxxxx)
2. Check for popup blockers
3. Test URL generation in browser console
4. Verify meta tag content

#### Database Connection Issues
1. Check database credentials in `API/db_connection.php`
2. Verify MySQL service is running
3. Check database permissions
4. Test connection with `API/test_db.php`

#### Order Placement Fails
1. Check cart session data
2. Verify product stock levels
3. Check payment method configuration
4. Review error logs for specific issues

#### Admin Dashboard Issues
1. Verify admin login credentials
2. Check JavaScript console for errors
3. Verify API endpoint responses
4. Check database table structure

### Error Logs
- **PHP Errors**: Check `API/php_errors.log`
- **Application Logs**: Check error_log() outputs
- **Database Logs**: Check MySQL error logs
- **Web Server Logs**: Check Apache/Nginx logs

### Performance Optimization
1. **Database Indexing**: Ensure proper indexes on frequently queried columns
2. **Image Optimization**: Compress product images
3. **Caching**: Implement caching for product data
4. **CDN**: Use CDN for static assets
5. **Database Optimization**: Regular database maintenance

## 📞 Support

### Contact Information
- **Email**: admin@jowaki.com
- **Phone**: +254 721 442 248
- **WhatsApp**: +254 721 442 248

### Documentation Updates
This README is updated regularly. For the latest version, check the project repository.

### Contributing
To contribute to the system:
1. Create a feature branch
2. Make your changes
3. Test thoroughly
4. Submit a pull request

---

**© 2025 Jowaki Electrical Services Ltd. All rights reserved.**

*This system is designed and developed for Jowaki Electrical Services. For technical support or customization requests, please contact the development team.*
