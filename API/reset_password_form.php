<?php
session_start();
require 'db_connection.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Validate token
if (empty($token)) {
    $error = "Invalid reset link. Please request a new password reset.";
} else {
    // Check if token exists and is valid
    $stmt = $conn->prepare("SELECT email, expires_at, used FROM password_resets WHERE token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "Invalid or expired reset link. Please request a new password reset.";
        } else {
            $resetData = $result->fetch_assoc();
            
            if ($resetData['used'] == 1) {
                $error = "This reset link has already been used. Please request a new password reset.";
            } elseif (strtotime($resetData['expires_at']) < time()) {
                $error = "This reset link has expired. Please request a new password reset.";
            }
        }
        $stmt->close();
    } else {
        $error = "Database error occurred. Please try again.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validate password
    if (empty($newPassword)) {
        $errors[] = "Password is required.";
    } elseif (strlen($newPassword) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/(?=.*[a-z])/', $newPassword)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/(?=.*[A-Z])/', $newPassword)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/(?=.*\d)/', $newPassword)) {
        $errors[] = "Password must contain at least one number.";
    }
    
    if (empty($confirmPassword)) {
        $errors[] = "Password confirmation is required.";
    } elseif ($newPassword !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    
    if (empty($errors)) {
        // Get email from token
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
        if ($stmt) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $resetData = $result->fetch_assoc();
                $email = $resetData['email'];
                
                // Hash new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update user's password
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("ss", $hashedPassword, $email);
                    
                    if ($updateStmt->execute()) {
                        // Mark token as used
                        $markUsed = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                        if ($markUsed) {
                            $markUsed->bind_param("s", $token);
                            $markUsed->execute();
                            $markUsed->close();
                        }
                        
                        $success = "Your password has been reset successfully. You can now login with your new password.";
                        $token = ''; // Clear token to hide form
                    } else {
                        $errors[] = "Failed to update password. Please try again.";
                    }
                    $updateStmt->close();
                } else {
                    $errors[] = "Database error occurred. Please try again.";
                }
            } else {
                $errors[] = "Invalid or expired reset link.";
            }
            $stmt->close();
        } else {
            $errors[] = "Database error occurred. Please try again.";
        }
    }
    
    if (!empty($errors)) {
        $error = implode(" ", $errors);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Jowaki Electrical Services</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .password-requirements {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-size: 14px;
        }
        .password-requirements h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        .password-requirements li {
            margin: 5px 0;
            color: #6c757d;
        }
        .error-alert {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .success-alert {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .back-to-login a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <section class="login-section section">
            <div class="container">
                <div class="form-container">
                    <h2>Reset Your Password</h2>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error-alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="success-alert">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        <div class="back-to-login">
                            <a href="login.php">← Back to Login</a>
                        </div>
                    <?php elseif (!empty($token) && empty($error)): ?>
                        <p style="text-align: center; color: #666; margin-bottom: 20px;">
                            Please enter your new password below.
                        </p>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="password-requirements">
                                <h4>Password Requirements:</h4>
                                <ul>
                                    <li>At least 8 characters long</li>
                                    <li>Contains at least one lowercase letter</li>
                                    <li>Contains at least one uppercase letter</li>
                                    <li>Contains at least one number</li>
                                </ul>
                            </div>
                            
                            <button type="submit" class="btn">Reset Password</button>
                        </form>
                        
                        <div class="back-to-login">
                            <a href="login.php">← Back to Login</a>
                        </div>
                    <?php else: ?>
                        <div class="back-to-login">
                            <a href="login.php">← Back to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Add real-time password validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');
            
            if (passwordInput && confirmInput) {
                function validatePasswordMatch() {
                    if (confirmInput.value && passwordInput.value !== confirmInput.value) {
                        confirmInput.setCustomValidity('Passwords do not match');
                    } else {
                        confirmInput.setCustomValidity('');
                    }
                }
                
                passwordInput.addEventListener('input', validatePasswordMatch);
                confirmInput.addEventListener('input', validatePasswordMatch);
            }
        });
    </script>
</body>
</html>