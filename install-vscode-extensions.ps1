# VS Code Extensions Auto-Install Script
# Run this in PowerShell to install all recommended extensions

Write-Host "🚀 Installing VS Code Extensions for Jowaki Development..." -ForegroundColor Green

# Check if VS Code is installed
$vscodePath = Get-Command code -ErrorAction SilentlyContinue
if (-not $vscodePath) {
    Write-Host "❌ VS Code not found. Please install VS Code first." -ForegroundColor Red
    Write-Host "Download from: https://code.visualstudio.com/" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ VS Code found! Installing extensions..." -ForegroundColor Green

# List of essential extensions
$extensions = @(
    "Natizyskunk.sftp",
    "DEVSENSE.phptools-vscode",
    "esbenp.prettier-vscode",
    "eamodio.gitlens",
    "ritwickdey.LiveServer",
    "ms-vscode.vscode-json",
    "bradlc.vscode-tailwindcss",
    "formulahendry.auto-rename-tag",
    "ms-vscode.sublime-keybindings"
)

# Install each extension
foreach ($extension in $extensions) {
    Write-Host "📦 Installing: $extension" -ForegroundColor Cyan
    try {
        & code --install-extension $extension --force
        Write-Host "✅ Installed: $extension" -ForegroundColor Green
    }
    catch {
        Write-Host "❌ Failed to install: $extension" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "🎉 Extension installation complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next Steps:" -ForegroundColor Yellow
Write-Host "1. Restart VS Code" -ForegroundColor White
Write-Host "2. Open your project folder: C:\Users\USER\OneDrive\Desktop\public_html" -ForegroundColor White
Write-Host "3. Edit .vscode\sftp.json and add your SSH password" -ForegroundColor White
Write-Host "4. Press Ctrl+Shift+P and run 'SFTP: Sync Local -> Remote'" -ForegroundColor White
Write-Host ""
Write-Host "🚀 You're ready for professional development!" -ForegroundColor Green
