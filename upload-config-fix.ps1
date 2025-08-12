# Upload Config Fix Script
# This script uploads the config file and fixed includes files

Write-Host "🔧 Uploading Config Fix Files..." -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green

# SFTP Configuration
$sftpHost = "145.79.28.7"
$sftpPort = "65002"
$sftpUser = "u383641303"
$sftpPass = "Jowakiftp1."

# Files to upload
$filesToUpload = @(
    "config/config.php",
    "includes/load_settings.php",
    "includes/header_include.php"
)

Write-Host "📋 Files to upload:" -ForegroundColor Yellow
foreach ($file in $filesToUpload) {
    if (Test-Path $file) {
        Write-Host "✅ $file" -ForegroundColor Green
    } else {
        Write-Host "❌ $file (MISSING)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "🔧 Manual SFTP Commands:" -ForegroundColor Yellow
Write-Host "1. Connect: sftp -P $sftpPort $sftpUser@$sftpHost" -ForegroundColor White
Write-Host "2. Navigate: cd public_html" -ForegroundColor White
Write-Host "3. Upload files:" -ForegroundColor White
Write-Host "   put config/config.php" -ForegroundColor White
Write-Host "   put includes/load_settings.php" -ForegroundColor White
Write-Host "   put includes/header_include.php" -ForegroundColor White
Write-Host "4. Verify: ls -la config/" -ForegroundColor White
Write-Host "5. Verify: ls -la includes/" -ForegroundColor White
Write-Host "6. Exit: exit" -ForegroundColor White

Write-Host ""
Write-Host "🌐 Test URL (after upload):" -ForegroundColor Yellow
Write-Host "   https://jowakielectrical.com" -ForegroundColor Cyan

Write-Host ""
Write-Host "💡 Quick Start:" -ForegroundColor Yellow
Write-Host "   sftp -P $sftpPort $sftpUser@$sftpHost" -ForegroundColor White

Write-Host ""
Write-Host "Press any key to continue..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
