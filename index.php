<?php
session_start();
if (isset($_SESSION['customer_id'])) {
    header("Location: products.php");
    exit();
}

$error = $_GET['error'] ?? '';
$registered = isset($_GET['registered']);
$reset_success = isset($_GET['reset_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bay Bakery - Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-banner">
            <div class="auth-banner-content">
                <h1>Online Bay Bakery</h1>
                <p>Welcome to our online bakery. Browse freshly baked artisan bread, decadent cakes, crispy cookies, and fine pastries delivered right to your door.</p>
            </div>
        </div>
        <div class="auth-form-side">
            <div class="auth-card">
                <h2>Welcome Back</h2>
                <p class="subtitle">Please enter your credentials to access your account</p>

                <?php if ($registered): ?>
                    <div class="alert alert-success">
                        Account created successfully! Please sign in below.
                    </div>
                <?php endif; ?>

                <?php if ($reset_success): ?>
                    <div class="alert alert-success">
                        Password updated successfully! Please log in with your new password.
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="customer@example.com" required value="customer@example.com">
                    </div>

                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label for="password" style="margin-bottom:0;">Password</label>
                            <a href="forgot_password.php" style="font-size:0.85rem; color:var(--primary); text-decoration:none;">Forgot Password?</a>
                        </div>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required value="password123">
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Sign In</button>
                </form>

                <div style="margin-top: 20px; padding: 12px 16px; background: var(--light-gray); border-radius: 8px; font-size: 0.85rem; border: 1px dashed var(--border);">
                    <strong>💡 Demo Credentials:</strong><br>
                    <span>Email: <code>customer@example.com</code></span><br>
                    <span>Password: <code>password123</code></span>
                </div>

                <div class="auth-card-footer">
                    Don't have an account? <a href="register.php">Register Now</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
