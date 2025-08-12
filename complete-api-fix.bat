@echo off
echo ========================================
echo    COMPLETE API & FILE PATH FIX
echo ========================================
echo.

echo This script will fix all API and file path issues
echo using the GitHub repository structure.
echo.

echo ========================================
echo    Step 1: Download from GitHub
echo ========================================
echo git clone https://github.com/MichaelNjuri/Jowaki-Electrical.git temp_repo
echo.

echo ========================================
echo    Step 2: Copy missing API files
echo ========================================
echo Copy these files from temp_repo to your includes/:
echo - add_to_cart.php
echo - remove_from_cart.php
echo - update_cart_quantity.php
echo - get_cart_count.php
echo - get_products.php
echo - get_categories.php
echo - get_user_info.php
echo - place_order.php
echo - login.php
echo - signup.php
echo - reset_password.php
echo - contact_form.php
echo - get_featured_products.php
echo.

echo ========================================
echo    Step 3: Copy missing JavaScript files
echo ========================================
echo Copy these files from temp_repo/js/ to your assets/js/:
echo - store-cart.js
echo - store-products.js
echo - store-checkout.js
echo - store-ui.js
echo.

echo ========================================
echo    Step 4: Copy missing CSS files
echo ========================================
echo Copy these files from temp_repo/css/ to your assets/css/:
echo - index.css
echo - store.css
echo - login.css
echo - checkout.css
echo.

echo ========================================
echo    Step 5: Copy missing PHP files
echo ========================================
echo Copy these files from temp_repo root to your root:
echo - index.php
echo - Store.php
echo - cart.php
echo - checkout.php
echo - login_form.php
echo - profile.php
echo.

echo ========================================
echo    Step 6: Copy includes directory
echo ========================================
echo Copy entire includes/ directory from temp_repo
echo.

echo ========================================
echo    Step 7: Copy config directory
echo ========================================
echo Copy entire config/ directory from temp_repo
echo.

echo ========================================
echo    Step 8: Upload everything to server
echo ========================================
echo Use Hostinger File Manager or SFTP to upload:
echo 1. All updated files
echo 2. All new API files
echo 3. All JavaScript modules
echo.

echo ========================================
echo    Step 9: Test functionality
echo ========================================
echo Visit: https://jowakielectrical.com/test-all-paths.php
echo.

echo ========================================
echo    START HERE
echo ========================================
echo 1. Clone the repository
echo 2. Copy all missing files
echo 3. Upload to server
echo 4. Test functionality
echo.

pause




