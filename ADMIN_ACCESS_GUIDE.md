# 🔐 Admin Access Guide - Jowaki Electrical Services

## 📋 **Complete Admin Setup Process**

### **Step 1: Upload Files to Hostinger**
1. Upload all files from `C:\Users\USER\OneDrive\Desktop\Jowaki Details\public_html\` to your Hostinger `public_html` folder
2. Use File Manager or ZIP upload method

### **Step 2: Run Admin Setup**
1. Visit: `https://jowakielectrical.com/admin_setup.php`
2. This will:
   - ✅ Test database connection
   - ✅ Fix database structure
   - ✅ Create admin user
   - ✅ Initialize system settings

### **Step 3: Access Admin Panel**
After setup, you can access admin through:
- **Direct URL**: `https://jowakielectrical.com/admin/`
- **API Endpoint**: `https://jowakielectrical.com/includes/admin_login_fixed.php`

## 🔧 **Admin System Features**

### **Available Admin Functions:**
- 📊 **Dashboard Analytics**
- 🛍️ **Product Management**
- 📦 **Order Management**
- 👥 **Customer Management**
- 📂 **Category Management**
- ⚙️ **System Settings**
- 👨‍💼 **Admin User Management**
- 📈 **Sales Reports**
- 📧 **Contact Messages**
- 💾 **Database Backup**

### **Admin API Endpoints:**
```
/includes/admin_login_fixed.php - Admin login
/includes/get_dashboard_stats.php - Dashboard statistics
/includes/get_products_admin.php - Product management
/includes/admin_orders.php - Order management
/includes/get_admins_fixed.php - Admin user list
/includes/update_settings.php - System settings
/includes/backup_database.php - Database backup
```

## 🛠️ **Database Fix Utilities**

### **Available Fix Scripts:**
- `includes/fix_database.php` - Fix database structure
- `includes/check_tables.php` - Check table integrity
- `includes/create_admin_tables.php` - Create admin tables
- `includes/fix_store_categories_table.php` - Fix categories

### **Quick Database Checks:**
- `test_db_connection.php` - Test database connection
- `check_tables.php` - Verify all tables exist
- `fix_database.php` - Auto-fix database issues

## 🔑 **Default Admin Credentials**

After running `admin_setup.php`:
- **Username**: `admin`
- **Email**: `admin@jowaki.com`
- **Password**: `admin123`

**⚠️ IMPORTANT**: Change these credentials immediately after first login!

## 📁 **Admin File Structure**

```
public_html/
├── admin/                    # Admin panel files
├── includes/
│   ├── admin_login_fixed.php     # Admin login API
│   ├── get_dashboard_stats.php   # Dashboard data
│   ├── get_products_admin.php    # Product management
│   ├── admin_orders.php          # Order management
│   ├── get_admins_fixed.php      # Admin user management
│   ├── update_settings.php       # System settings
│   ├── backup_database.php       # Database backup
│   ├── fix_database.php          # Database fixes
│   └── [other admin APIs]
├── config/
│   └── config.php               # Database configuration
└── admin_setup.php              # Setup script
```

## 🚀 **Quick Setup Commands**

### **1. Test Database Connection:**
```
https://jowakielectrical.com/test_db_connection.php
```

### **2. Run Admin Setup:**
```
https://jowakielectrical.com/admin_setup.php
```

### **3. Fix Database Issues:**
```
https://jowakielectrical.com/includes/fix_database.php
```

### **4. Check Tables:**
```
https://jowakielectrical.com/includes/check_tables.php
```

## 🔒 **Security Checklist**

### **After Setup:**
- [ ] Delete `admin_setup.php`
- [ ] Delete `test_db_connection.php`
- [ ] Delete `import_database.php`
- [ ] Change default admin password
- [ ] Set up proper file permissions
- [ ] Enable HTTPS (SSL)
- [ ] Configure firewall rules

### **File Permissions:**
```
config/ - 755
uploads/ - 755
includes/ - 755
.htaccess - 644
*.php - 644
```

## 📞 **Troubleshooting**

### **Common Issues:**

**1. Database Connection Failed:**
- Check credentials in `config/config.php`
- Verify database exists in Hostinger
- Ensure MySQL service is running

**2. Admin Login Not Working:**
- Run `admin_setup.php` first
- Check if admin_users table exists
- Verify admin user was created

**3. Missing Tables:**
- Run `fix_database.php`
- Check `check_tables.php`
- Import database if needed

**4. Permission Errors:**
- Set proper file permissions
- Check .htaccess configuration
- Verify PHP settings

## 🎯 **Success Indicators**

Your admin system is working when:
- ✅ Database connection test passes
- ✅ Admin login works
- ✅ Dashboard loads with statistics
- ✅ Product management functions
- ✅ Order management works
- ✅ Settings can be updated

## 📞 **Support**

If you encounter issues:
1. Check error logs in Hostinger
2. Run diagnostic scripts
3. Verify database connectivity
4. Check file permissions

---

**Remember**: Delete setup files after successful configuration for security!
