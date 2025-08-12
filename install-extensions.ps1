Write-Host "Installing VS Code Extensions..." -ForegroundColor Green

code --install-extension Natizyskunk.sftp
code --install-extension DEVSENSE.phptools-vscode  
code --install-extension esbenp.prettier-vscode
code --install-extension eamodio.gitlens
code --install-extension ritwickdey.LiveServer

Write-Host "Extensions installed! Please restart VS Code." -ForegroundColor Green
