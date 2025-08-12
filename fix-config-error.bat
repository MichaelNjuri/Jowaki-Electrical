@echo off
echo ========================================
echo    FIX CONFIG ERROR
echo ========================================
echo.

echo The website is showing a fatal error because config/config.php is missing.
echo This script will help you upload it quickly.
echo.

echo ========================================
echo    CRITICAL: Upload This File First
echo ========================================
echo File: config/config.php
echo This will fix the fatal error immediately.
echo.

echo ========================================
echo    SFTP Commands to Run
echo ========================================
echo 1. sftp -P 65002 u383641303@145.79.28.7
echo 2. cd public_html
echo 3. put config/config.php
echo 4. ls -la config/
echo 5. exit
echo.

echo ========================================
echo    Test After Upload
echo ========================================
echo Visit: https://jowakielectrical.com
echo If it works, then upload the JavaScript files.
echo.

echo ========================================
echo    JavaScript Files to Upload Next
echo ========================================
echo put assets/js/store-cart.js
echo put assets/js/store-products.js
echo put assets/js/store-checkout.js
echo put assets/js/store.js
echo.

echo Press any key to continue...
pause

echo.
echo ========================================
echo    START HERE
echo ========================================
echo Copy and paste this command:
echo sftp -P 65002 u383641303@145.79.28.7
echo.




