# Admin Authentication System Setup Guide

## Overview
This admin authentication system provides secure access control for the Jowaki Electrical Services e-commerce platform with role-based permissions, session management, and comprehensive security features.

## Security Features

### 🔐 Authentication & Authorization
- **Multi-factor session validation** with timeout protection
- **Role-based access control** (Super Admin, Admin, Manager)
- **CSRF protection** on all forms and API endpoints
- **Rate limiting** to prevent brute force attacks
- **Account lockout** after failed login attempts
- **Secure password hashing** using Argon2id

### 🛡️ Security Measures
- **Session security** with HttpOnly cookies and SameSite protection
- **Input validation** and sanitization
- **SQL injection prevention** using prepared statements
- **XSS protection** with proper output encoding
- **Security headers** (X-Frame-Options, X-XSS-Protection, etc.)
- **Comprehensive logging** of security events

### 📊 Role-Based Permissions

#### Super Admin
- Manage all admin accounts
- Full system access
- View security logs
- Delete data
- All other permissions

#### Admin
- Manage products, orders, customers
- View analytics and reports
- Manage inventory and categories
- Cannot manage other admins

#### Manager
- View orders and customers
- View analytics and reports
- Manage inventory
- View categories only

## Setup Instructions

### 1. Database Setup
Run the database setup script to create admin tables:

```bash
# Navigate to your project directory
cd /path/to/jowaki_electrical_srvs

# Run the setup script
php API/setup_admin_tables.php
```

This will create:
- `admins` table with user accounts
- `admin_sessions` table for session tracking
- `admin_activity_log` table for security logging
- `admin_permissions` table for granular permissions

### 2. Default Admin Account
The setup script creates a default super admin account:

```
Email: admin@jowaki.com
Password: Admin@Jowaki2024!
```

**⚠️ IMPORTANT:** Change this password immediately after first login!

### 3. File Structure
```
jowaki_electrical_srvs/
├── admin/
│   ├── login.php          # Admin login page
│   ├── dashboard.php      # Admin dashboard
│   └── create_admin.php   # Create new admin accounts
├── API/
│   ├── admin_auth_helper.php    # Authentication functions
│   ├── admin_login.php          # Login API
│   ├── admin_logout.php         # Logout API
│   ├── create_admin.php         # Create admin API
│   ├── check_admin_auth.php     # Auth status check
│   └── setup_admin_tables.php   # Database setup
└── ADMIN_SETUP.md              # This file
```

## Usage

### Accessing Admin Panel
1. Navigate to: `http://your-domain/admin/login.php`
2. Login with admin credentials
3. Access dashboard and manage the system

### Creating New Admin Accounts
1. Login as a super admin
2. Navigate to the admin creation page
3. Fill in the required information
4. Select appropriate role and permissions

### Password Requirements
Admin passwords must meet these requirements:
- Minimum 12 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character

## API Endpoints

### Authentication
- `POST /API/admin_login.php` - Admin login
- `POST /API/admin_logout.php` - Admin logout
- `GET /API/check_admin_auth.php` - Check auth status

### Admin Management
- `POST /API/create_admin.php` - Create new admin (requires permission)

### Request Format
All API endpoints expect JSON requests:

```json
{
  "email": "admin@example.com",
  "password": "SecurePassword123!",
  "csrf_token": "generated_token"
}
```

### Response Format
```json
{
  "success": true,
  "message": "Operation successful",
  "admin": {
    "id": 1,
    "email": "admin@example.com",
    "name": "Admin Name",
    "role": "admin"
  }
}
```

## Security Best Practices

### 1. Password Security
- Use strong, unique passwords
- Change default passwords immediately
- Enable password complexity requirements
- Implement password expiration policies

### 2. Session Management
- Sessions timeout after 30 minutes of inactivity
- Secure session cookies with HttpOnly and SameSite
- Log all login/logout events
- Monitor for suspicious activity

### 3. Access Control
- Implement principle of least privilege
- Regular permission audits
- Monitor admin activity logs
- Disable inactive accounts

### 4. Network Security
- Use HTTPS in production
- Implement IP whitelisting if needed
- Regular security updates
- Monitor access logs

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
- Check database credentials in `API/db_connection.php`
- Ensure MySQL service is running
- Verify database exists and is accessible

#### 2. Session Issues
- Check PHP session configuration
- Verify session directory permissions
- Clear browser cookies and cache

#### 3. Permission Denied
- Verify admin role and permissions
- Check if account is active
- Review activity logs for details

#### 4. CSRF Token Errors
- Ensure JavaScript is enabled
- Check for browser security settings
- Verify token generation and validation

### Log Files
- `API/php_errors.log` - PHP errors and warnings
- `API/admin_security.log` - Security events and admin actions

## Maintenance

### Regular Tasks
1. **Monitor security logs** for suspicious activity
2. **Update admin passwords** regularly
3. **Review and audit permissions** monthly
4. **Backup admin data** regularly
5. **Update security patches** as needed

### Backup Admin Data
```sql
-- Export admin tables
mysqldump -u username -p database_name admins admin_sessions admin_activity_log admin_permissions > admin_backup.sql
```

## Support

For technical support or security issues:
1. Check the log files for error details
2. Review this documentation
3. Contact system administrator
4. Report security incidents immediately

---

**⚠️ Security Notice:** This system handles sensitive administrative access. Always follow security best practices and keep credentials secure. 