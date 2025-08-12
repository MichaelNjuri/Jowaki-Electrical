@echo off
echo ========================================
echo    FINDING THE CORRECT PATH
echo ========================================
echo.

echo Based on your Hostinger File Manager, we need to find
echo the correct path for jowakielectrical.com specifically.
echo.

echo ========================================
echo    Step 1: Connect to SFTP
echo ========================================
echo sftp -P 65002 u383641303@145.79.28.7
echo.

echo ========================================
echo    Step 2: Explore directories
echo ========================================
echo pwd
echo ls -la
echo.

echo ========================================
echo    Step 3: Try these common paths
echo ========================================
echo cd public_html
echo ls -la
echo.
echo cd domains
echo ls -la
echo.
echo cd jowakielectrical.com
echo ls -la
echo.
echo cd public_html
echo ls -la
echo.

echo ========================================
echo    Step 4: Look for your files
echo ========================================
echo Look for files like:
echo - index.php
echo - config.php
echo - assets folder
echo.

echo ========================================
echo    Step 5: Once you find the right path
echo ========================================
echo If you see your website files, that's the right place!
echo Then run: put -r .
echo.

echo ========================================
echo    START HERE
echo ========================================
echo Copy and paste this command:
echo sftp -P 65002 u383641303@145.79.28.7
echo.

pause
