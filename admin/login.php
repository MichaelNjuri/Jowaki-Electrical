<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Jowaki Electrical Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo {
            margin-bottom: 2rem;
        }

        .logo i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .logo h1 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .logo p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group .input-icon {
            position: relative;
        }

        .form-group .input-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .form-group .input-icon input {
            padding-left: 40px;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .loading {
            display: none;
            margin-right: 0.5rem;
        }

        .loading.show {
            display: inline-block;
        }

        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e1e5e9;
            color: #666;
            font-size: 0.8rem;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 10px;
            margin-bottom: 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
            <h1>Admin Login</h1>
            <p>Jowaki Electrical Services</p>
        </div>

        <div id="message"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" required placeholder="Enter username">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required placeholder="Enter password">
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <span class="loading" id="loading">
                    <div class="spinner"></div>
                </span>
                <span id="loginText">Login</span>
            </button>
        </form>

        <div class="footer">
            <p>Need help? <a href="diagnose_admin_system.php">Check System Status</a></p>
        </div>
    </div>

    <script>
        // Check if already logged in (commented out to prevent auto-redirect)
        // if (localStorage.getItem('adminUser')) {
        //     window.location.href = 'AdminDashboard.html';
        // }

        // Check for logout message in URL parameters
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        // Display logout message if present
        const logoutMessage = getUrlParameter('message');
        if (logoutMessage) {
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = `<div class="message success">${logoutMessage}</div>`;
            
            // Clear the message from URL
            const url = new URL(window.location);
            url.searchParams.delete('message');
            window.history.replaceState({}, document.title, url.pathname);
        }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');
            const loginText = document.getElementById('loginText');
            const messageDiv = document.getElementById('message');

            // Show loading state
            loginBtn.disabled = true;
            loading.classList.add('show');
            loginText.textContent = 'Logging in...';
            messageDiv.innerHTML = '';

            try {
                const response = await fetch('admin_login_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (data.success) {
                    // Store admin data
                    localStorage.setItem('adminUser', JSON.stringify(data.admin));
                    
                    // Show success message
                    messageDiv.innerHTML = '<div class="message success">Login successful! <a href="AdminDashboard.html" style="color: white; text-decoration: underline;">Click here to go to Dashboard</a></div>';
                    
                    // Optional: Add a button to redirect
                    messageDiv.innerHTML += '<button onclick="window.location.href=\'AdminDashboard.html\'" style="margin-top: 10px; padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Go to Dashboard</button>';
                } else {
                    if (data.redirect) {
                        messageDiv.innerHTML = `<div class="message error">Admin system not initialized. <a href="${data.redirect}">Initialize Now</a></div>`;
                    } else {
                        messageDiv.innerHTML = `<div class="message error">${data.message}</div>`;
                    }
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="message error">Connection error. Please try again.</div>';
            } finally {
                // Reset loading state
                loginBtn.disabled = false;
                loading.classList.remove('show');
                loginText.textContent = 'Login';
            }
        });
    </script>
</body>
</html>
