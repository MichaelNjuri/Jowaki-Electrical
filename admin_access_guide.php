<?php
// Admin Access Guide for Jowaki Store
echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Admin Access Guide - Jowaki Store</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".step { margin: 15px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }";
echo ".warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo "h1 { color: #333; text-align: center; }";
echo "h2 { color: #555; }";
echo ".login-box { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔐 Admin Panel Access Guide</h1>";

echo "<div class='info'>";
echo "<h2>📋 Quick Access Information</h2>";
echo "<p><strong>Admin URL:</strong> <a href='/admin/login.php' target='_blank'>https://jowakielectrical.com/admin/login.php</a></p>";
echo "<p><strong>Dashboard URL:</strong> <a href='/admin/index.php' target='_blank'>https://jowakielectrical.com/admin/index.php</a></p>";
echo "</div>";

echo "<div class='step'>";
echo "<h2>🔑 Default Admin Credentials</h2>";
echo "<div class='login-box'>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Email:</strong> admin@jowaki.com</p>";
echo "<p><strong>Password:</strong> admin123</p>";
echo "</div>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>⚠️ Important Security Notes:</h3>";
echo "<ul>";
echo "<li>Change the default password immediately after first login</li>";
echo "<li>Use a strong password (12+ characters, mix of letters, numbers, symbols)</li>";
echo "<li>Never share admin credentials</li>";
echo "<li>Log out when not using the admin panel</li>";
echo "</ul>";
echo "</div>";

echo "<div class='step'>";
echo "<h2>🚀 How to Access Admin Panel</h2>";
echo "<ol>";
echo "<li><strong>Open your browser</strong> and go to: <a href='/admin/login.php' target='_blank'>https://jowakielectrical.com/admin/login.php</a></li>";
echo "<li><strong>Enter credentials:</strong>";
echo "<ul>";
echo "<li>Username: <code>admin</code></li>";
echo "<li>Password: <code>admin123</code></li>";
echo "</ul></li>";
echo "<li><strong>Click Login</strong> to access the admin dashboard</li>";
echo "<li><strong>Change password</strong> immediately after login</li>";
echo "</ol>";
echo "</div>";

echo "<div class='step'>";
echo "<h2>🔧 If Login Fails</h2>";
echo "<p>If the default credentials don't work, try these steps:</p>";
echo "<ol>";
echo "<li><strong>Check database connection:</strong> Visit <a href='/test_db_connection.php' target='_blank'>Database Test</a></li>";
echo "<li><strong>Run admin setup:</strong> Visit <a href='/admin_setup.php' target='_blank'>Admin Setup</a> to create admin account</li>";
echo "<li><strong>Check diagnostic:</strong> Visit <a href='/diagnostic_troubleshooting.php' target='_blank'>System Diagnostic</a></li>";
echo "</ol>";
echo "</div>";

echo "<div class='step'>";
echo "<h2>📊 Admin Panel Features</h2>";
echo "<div class='success'>";
echo "<h3>✅ Available Features:</h3>";
echo "<ul>";
echo "<li><strong>Dashboard:</strong> Overview statistics and recent activity</li>";
echo "<li><strong>Product Management:</strong> Add, edit, delete products</li>";
echo "<li><strong>Order Management:</strong> View and process orders</li>";
echo "<li><strong>Customer Management:</strong> View customer information</li>";
echo "<li><strong>Category Management:</strong> Manage product categories</li>";
echo "<li><strong>Settings:</strong> Configure store settings</li>";
echo "<li><strong>Reports:</strong> Sales and analytics reports</li>";
echo "</ul>";
echo "</div>";
echo "</div>";

echo "<div class='step'>";
echo "<h2>🔗 Quick Access Links</h2>";
echo "<p><a href='/admin/login.php' class='btn' target='_blank'>🔐 Admin Login</a></p>";
echo "<p><a href='/admin/index.php' class='btn' target='_blank'>📊 Admin Dashboard</a></p>";
echo "<p><a href='/test_db_connection.php' class='btn' target='_blank'>🧪 Test Database</a></p>";
echo "<p><a href='/diagnostic_troubleshooting.php' class='btn' target='_blank'>🔍 System Diagnostic</a></p>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>🛡️ Security Reminder</h3>";
echo "<p>After successful login, please:</p>";
echo "<ol>";
echo "<li>Change the default password immediately</li>";
echo "<li>Delete setup files (admin_setup.php, test_db_connection.php, etc.)</li>";
echo "<li>Monitor admin activity logs</li>";
echo "<li>Use strong, unique passwords</li>";
echo "</ol>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
