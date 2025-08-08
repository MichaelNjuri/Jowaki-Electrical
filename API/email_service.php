<?php
// Email Service for Jowaki Electrical Services
// Configured for Gmail SMTP

class EmailService {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = 'jowakielectricalsrvs@gmail.com'; // Your Gmail address
    private $smtp_password = 'your-app-password-here'; // Replace with your Gmail app password
    private $from_email = 'jowakielectricalsrvs@gmail.com';
    private $from_name = 'Jowaki Electrical Services';

    public function __construct() {
        // Configure PHP mail settings for Gmail
        ini_set('SMTP', $this->smtp_host);
        ini_set('smtp_port', $this->smtp_port);
    }

    // Send order confirmation email to customer
    public function sendOrderConfirmationEmail($order_data, $customer_email, $customer_name) {
        $subject = "Order Confirmation - Order #{$order_data['order_id']}";
        
        $html_content = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #2563eb; color: white; padding: 20px; text-align: center;'>
                <h1>Jowaki Electrical Services</h1>
                <h2>Order Confirmation</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8fafc;'>
                <p>Dear {$customer_name},</p>
                
                <p>Thank you for your order! Your order has been received and is being processed.</p>
                
                <div style='background-color: white; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #2563eb; margin-top: 0;'>Order Details</h3>
                    <p><strong>Order ID:</strong> #{$order_data['order_id']}</p>
                    <p><strong>Order Date:</strong> " . date('F j, Y', strtotime($order_data['order_date'])) . "</p>
                    <p><strong>Total Amount:</strong> KSh " . number_format($order_data['total'], 2) . "</p>
                    <p><strong>Payment Method:</strong> {$order_data['payment_method']}</p>
                    <p><strong>Delivery Method:</strong> {$order_data['delivery_method']}</p>
                </div>
                
                <p>We will notify you once your order is ready for delivery or pickup.</p>
                
                <p>If you have any questions, please contact us at:</p>
                <ul>
                    <li>Email: jowakielectricalsrvs@gmail.com</li>
                    <li>Phone: +254 721 442 248</li>
                </ul>
                
                <p>Thank you for choosing Jowaki Electrical Services!</p>
                
                <p>Best regards,<br>
                Jowaki Electrical Services Team</p>
            </div>
            
            <div style='background-color: #1e293b; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                <p>© 2025 Jowaki Electrical Services. All rights reserved.</p>
            </div>
        </div>";
        
        return $this->sendEmail($customer_email, $subject, $html_content);
    }

    // Send order status update email
    public function sendOrderStatusUpdateEmail($order_data, $customer_email, $customer_name, $new_status) {
        $subject = "Order Status Update - Order #{$order_data['order_id']}";
        
        $status_messages = [
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'processing' => 'Your order is being processed and prepared for delivery.',
            'shipped' => 'Your order has been shipped and is on its way to you.',
            'delivered' => 'Your order has been delivered successfully.',
            'cancelled' => 'Your order has been cancelled as requested.'
        ];
        
        $status_message = $status_messages[$new_status] ?? 'Your order status has been updated.';
        
        $html_content = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #2563eb; color: white; padding: 20px; text-align: center;'>
                <h1>Jowaki Electrical Services</h1>
                <h2>Order Status Update</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8fafc;'>
                <p>Dear {$customer_name},</p>
                
                <p>{$status_message}</p>
                
                <div style='background-color: white; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #2563eb; margin-top: 0;'>Order Details</h3>
                    <p><strong>Order ID:</strong> #{$order_data['order_id']}</p>
                    <p><strong>New Status:</strong> " . ucfirst($new_status) . "</p>
                    <p><strong>Total Amount:</strong> KSh " . number_format($order_data['total'], 2) . "</p>
                </div>
                
                <p>If you have any questions, please contact us at:</p>
                <ul>
                    <li>Email: jowakielectricalsrvs@gmail.com</li>
                    <li>Phone: +254 721 442 248</li>
                </ul>
                
                <p>Thank you for choosing Jowaki Electrical Services!</p>
                
                <p>Best regards,<br>
                Jowaki Electrical Services Team</p>
            </div>
            
            <div style='background-color: #1e293b; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                <p>© 2025 Jowaki Electrical Services. All rights reserved.</p>
            </div>
        </div>";
        
        return $this->sendEmail($customer_email, $subject, $html_content);
    }

    // Send welcome email to new customers
    public function sendWelcomeEmail($customer_email, $customer_name, $password = null) {
        $subject = "Welcome to Jowaki Electrical Services!";
        
        $password_info = $password ? "<p><strong>Your temporary password:</strong> {$password}</p><p>Please change your password after your first login.</p>" : "";
        
        $html_content = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #2563eb; color: white; padding: 20px; text-align: center;'>
                <h1>Jowaki Electrical Services</h1>
                <h2>Welcome!</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8fafc;'>
                <p>Dear {$customer_name},</p>
                
                <p>Welcome to Jowaki Electrical Services! We're excited to have you as our customer.</p>
                
                <p>With your account, you can:</p>
                <ul>
                    <li>Browse our wide selection of electrical products</li>
                    <li>Place orders online</li>
                    <li>Track your order status</li>
                    <li>View your order history</li>
                    <li>Manage your profile</li>
                </ul>
                
                {$password_info}
                
                <p>If you have any questions, please contact us at:</p>
                <ul>
                    <li>Email: jowakielectricalsrvs@gmail.com</li>
                    <li>Phone: +254 721 442 248</li>
                </ul>
                
                <p>Thank you for choosing Jowaki Electrical Services!</p>
                
                <p>Best regards,<br>
                Jowaki Electrical Services Team</p>
            </div>
            
            <div style='background-color: #1e293b; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                <p>© 2025 Jowaki Electrical Services. All rights reserved.</p>
            </div>
        </div>";
        
        return $this->sendEmail($customer_email, $subject, $html_content);
    }

    // Send password reset email
    public function sendPasswordResetEmail($customer_email, $customer_name, $reset_token) {
        $subject = "Password Reset Request - Jowaki Electrical Services";
        
        $reset_link = "http://localhost/jowaki_electrical_srvs/reset_password.php?token=" . urlencode($reset_token);
        
        $html_content = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #2563eb; color: white; padding: 20px; text-align: center;'>
                <h1>Jowaki Electrical Services</h1>
                <h2>Password Reset Request</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8fafc;'>
                <p>Dear {$customer_name},</p>
                
                <p>We received a request to reset your password for your Jowaki Electrical Services account.</p>
                
                <p>Click the button below to reset your password:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$reset_link}' style='background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Reset Password</a>
                </div>
                
                <p>If you didn't request this password reset, please ignore this email.</p>
                
                <p>This link will expire in 1 hour for security reasons.</p>
                
                <p>If you have any questions, please contact us at:</p>
                <ul>
                    <li>Email: jowakielectricalsrvs@gmail.com</li>
                    <li>Phone: +254 721 442 248</li>
                </ul>
                
                <p>Best regards,<br>
                Jowaki Electrical Services Team</p>
            </div>
            
            <div style='background-color: #1e293b; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                <p>© 2025 Jowaki Electrical Services. All rights reserved.</p>
            </div>
        </div>";
        
        return $this->sendEmail($customer_email, $subject, $html_content);
    }

    // Send admin notification for new orders
    public function sendAdminOrderNotification($order_data, $admin_email) {
        $subject = "New Order Received - Order #{$order_data['order_id']}";
        
        $html_content = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #2563eb; color: white; padding: 20px; text-align: center;'>
                <h1>Jowaki Electrical Services</h1>
                <h2>New Order Notification</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8fafc;'>
                <p>A new order has been received and requires your attention.</p>
                
                <div style='background-color: white; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #2563eb; margin-top: 0;'>Order Details</h3>
                    <p><strong>Order ID:</strong> #{$order_data['order_id']}</p>
                    <p><strong>Customer:</strong> {$order_data['customer_info']['firstName']} {$order_data['customer_info']['lastName']}</p>
                    <p><strong>Email:</strong> {$order_data['customer_info']['email']}</p>
                    <p><strong>Phone:</strong> {$order_data['customer_info']['phone']}</p>
                    <p><strong>Total Amount:</strong> KSh " . number_format($order_data['total'], 2) . "</p>
                    <p><strong>Payment Method:</strong> {$order_data['payment_method']}</p>
                    <p><strong>Delivery Method:</strong> {$order_data['delivery_method']}</p>
                </div>
                
                <p>Please log into your admin dashboard to process this order.</p>
                
                <p>Best regards,<br>
                Jowaki Electrical Services System</p>
            </div>
            
            <div style='background-color: #1e293b; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                <p>© 2025 Jowaki Electrical Services. All rights reserved.</p>
            </div>
        </div>";
        
        return $this->sendEmail($admin_email, $subject, $html_content);
    }

    // Send contact form notification to admin
    public function sendContactFormNotification($form_data, $admin_email) {
        $subject = "New Contact Form Submission - {$form_data['subject']}";
        
        $html_content = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #2563eb; color: white; padding: 20px; text-align: center;'>
                <h1>Jowaki Electrical Services</h1>
                <h2>New Contact Form Submission</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8fafc;'>
                <p>A new contact form submission has been received.</p>
                
                <div style='background-color: white; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #2563eb; margin-top: 0;'>Contact Details</h3>
                    <p><strong>Name:</strong> {$form_data['name']}</p>
                    <p><strong>Email:</strong> {$form_data['email']}</p>
                    <p><strong>Subject:</strong> {$form_data['subject']}</p>
                    <p><strong>Message:</strong></p>
                    <div style='background-color: #f1f5f9; padding: 10px; border-radius: 4px; margin-top: 10px;'>
                        " . nl2br(htmlspecialchars($form_data['message'])) . "
                    </div>
                    <p><strong>Submitted:</strong> {$form_data['submitted_at']}</p>
                    <p><strong>IP Address:</strong> {$form_data['ip_address']}</p>
                </div>
                
                <p>Please respond to this inquiry as soon as possible.</p>
                
                <p>Best regards,<br>
                Jowaki Electrical Services System</p>
            </div>
            
            <div style='background-color: #1e293b; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                <p>© 2025 Jowaki Electrical Services. All rights reserved.</p>
            </div>
        </div>";
        
        return $this->sendEmail($admin_email, $subject, $html_content);
    }

    // Core email sending function
    private function sendEmail($to, $subject, $html_content) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $message = $html_content;
        
        // Suppress warnings for mail() function
        return @mail($to, $subject, $message, implode("\r\n", $headers));
    }
}

// Global functions for backward compatibility
function sendOrderConfirmationEmail($order_data, $customer_email, $customer_name) {
    $emailService = new EmailService();
    return $emailService->sendOrderConfirmationEmail($order_data, $customer_email, $customer_name);
}

function sendOrderStatusUpdateEmail($order_data, $customer_email, $customer_name, $new_status) {
    $emailService = new EmailService();
    return $emailService->sendOrderStatusUpdateEmail($order_data, $customer_email, $customer_name, $new_status);
}

function sendWelcomeEmail($customer_email, $customer_name, $password = null) {
    $emailService = new EmailService();
    return $emailService->sendWelcomeEmail($customer_email, $customer_name, $password);
}

function sendPasswordResetEmail($customer_email, $customer_name, $reset_token) {
    $emailService = new EmailService();
    return $emailService->sendPasswordResetEmail($customer_email, $customer_name, $reset_token);
}

function sendAdminOrderNotification($order_data, $admin_email) {
    $emailService = new EmailService();
    return $emailService->sendAdminOrderNotification($order_data, $admin_email);
}

function sendContactFormNotification($form_data, $admin_email) {
    $emailService = new EmailService();
    return $emailService->sendContactFormNotification($form_data, $admin_email);
}
?>
