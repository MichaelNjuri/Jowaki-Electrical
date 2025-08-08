# Category Scroll Setup Guide

## Overview
This update implements a new category scroll design that matches the provided design image, featuring category cards with images/icons and labels instead of simple buttons.

## Features
- **Dynamic Category Cards**: Categories are loaded from the database with support for images and icons
- **Responsive Design**: Horizontal scrollable category cards with smooth animations
- **Admin Management**: Full CRUD operations for managing store categories
- **Backward Compatibility**: Falls back to legacy categories if no store categories exist

## Database Setup

### 1. Create the store_categories table
Run the following SQL in your MySQL database:

```sql
CREATE TABLE IF NOT EXISTS store_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    image_url TEXT,
    icon_class VARCHAR(100) DEFAULT 'fas fa-box',
    filter_value VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. Insert sample categories
Run the PHP script to create sample categories:

```bash
php API/create_store_categories_table.php
```

Or manually insert the sample categories:

```sql
INSERT INTO store_categories (name, display_name, icon_class, filter_value, sort_order) VALUES
('4G_WIFI_DC_CAMERA', '4G/WIFI DC Camera', 'fas fa-video', '4G_WIFI_DC_CAMERA', 1),
('A9_HD_HIDDEN_CAMERA', 'A9 HD Hidden CAMERA', 'fas fa-eye', 'A9_HD_HIDDEN_CAMERA', 2),
('ACCESSORIES', 'Accessories', 'fas fa-tools', 'ACCESSORIES', 3),
('CABLES', 'Cables', 'fas fa-plug', 'CABLES', 4),
('CCTV', 'CCTV', 'fas fa-shield-alt', 'CCTV', 5),
('COMPUTER_KEYBOARDS', 'Computer Keyboards', 'fas fa-keyboard', 'COMPUTER_KEYBOARDS', 6),
('DAHUA', 'Dahua', 'fas fa-camera', 'DAHUA', 7);
```

## Files Modified/Created

### New Files:
- `API/get_store_categories.php` - API endpoint for store categories
- `API/store_categories_admin.php` - Admin API for managing store categories
- `API/create_store_categories_table.php` - Database setup script
- `js/modules/storeCategories.js` - Store categories management module

### Modified Files:
- `Store.php` - Updated to use dynamic category cards
- `store.css` - Added new category card styles
- `js/modules/store-products.js` - Updated to load categories dynamically
- `AdminDashboard.html` - Added store categories management section
- `js/main.js` - Integrated store categories module

## Usage

### Store Frontend
The category scroll now automatically loads categories from the database and displays them as cards with images/icons and labels.

### Admin Dashboard
1. Navigate to the "Store Categories Management" section
2. Add new categories with images, icons, and filter values
3. Edit existing categories
4. Manage category order and status

## API Endpoints

### Store Categories (Public)
- `GET /API/get_store_categories.php` - Get active store categories

### Store Categories (Admin)
- `GET /API/store_categories_admin.php` - Get all store categories
- `POST /API/store_categories_admin.php` - Add new category
- `PUT /API/store_categories_admin.php` - Update category
- `DELETE /API/store_categories_admin.php` - Delete category

## CSS Classes

### New Category Card Styles:
- `.category-card` - Main category card container
- `.category-card-image` - Image/icon container
- `.category-card-label` - Category label text
- `.category-card.active` - Active state styling

## JavaScript Functions

### Store Categories Module:
- `loadStoreCategories()` - Load categories from API
- `renderStoreCategories()` - Render categories in admin table
- `addStoreCategory()` - Add new category
- `editStoreCategory()` - Update category
- `deleteStoreCategory()` - Delete category
- `searchStoreCategories()` - Search categories
- `exportStoreCategoriesCSV()` - Export to CSV

## Troubleshooting

### Common Issues:

1. **Categories not loading**: Check database connection and ensure the `store_categories` table exists
2. **Images not displaying**: Verify image URLs are accessible and properly formatted
3. **Admin not working**: Ensure all JavaScript modules are loaded correctly

### Database Connection:
Make sure your MySQL server is running and the connection details in the API files match your setup.

## Migration from Legacy Categories

The system automatically falls back to legacy categories if no store categories exist. To migrate:

1. Create the `store_categories` table
2. Import your existing categories or use the sample data
3. Update product categories to match the new filter values

## Design Features

- **Card-based Layout**: Each category is displayed as a card with image/icon and label
- **Horizontal Scroll**: Smooth horizontal scrolling with arrow navigation
- **Hover Effects**: Cards lift and show shadows on hover
- **Active States**: Clear visual indication of selected category
- **Responsive**: Works on mobile and desktop devices
