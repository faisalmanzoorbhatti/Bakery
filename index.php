<?php
session_start();
if (isset($_SESSION['customer_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: products.php");
    }
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
    <title>Sign In - L'Atelier Gourmet Bakery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .role-btn {
            background: #ffffff;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            padding: 10px 14px;
            cursor: pointer;
            text-align: left;
            transition: var(--transition);
        }
        .role-btn:hover {
            border-color: var(--primary);
            background: var(--surface-warm);
        }
        .role-btn.active {
            border-color: var(--primary);
            background: var(--primary-light);
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <!-- Left Photography Banner -->
        <div class="auth-banner">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="brand-icon" style="background:#ffffff; color:var(--primary);">🥐</div>
                <span style="font-family:var(--font-heading); font-size:1.5rem; font-weight:700; color:#ffffff; letter-spacing:-0.3px;">L'Atelier Bakery</span>
            </div>
            
            <div class="auth-banner-content">
                <div class="hero-pill" style="margin-bottom:14px;">Master Boulangerie & Pâtisserie</div>
                <h1>Artisanal Hearth Baking, Delivered Warm.</h1>
                <p>Welcome to our online craft bakery. Experience slow-fermented French sourdough, flaky croissants, and rich Belgian chocolate cakes baked fresh daily.</p>
            </div>

            <div style="font-size:0.85rem; color:#d6d3d1;">
                &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery. All rights reserved.
            </div>
        </div>

        <!-- Right Sign In Form -->
        <div class="auth-form-side">
            <div class="auth-card">
                <h2>Welcome Back</h2>
                <p class="subtitle">Please enter your credentials to access the bakery</p>

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
                            <a href="forgot_password.php" style="font-size:0.85rem; color:var(--primary-dark); text-decoration:none; font-weight:600;">Forgot Password?</a>
                        </div>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required value="password123">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; padding:14px; margin-top:8px; font-size:1rem;">
                        Sign In &rarr;
                    </button>
                </form>

                <!-- One-Click Demo Role Selectors -->
                <div style="margin-top: 24px; padding: 16px; background: var(--surface-alt); border-radius: var(--radius-md); border: 1px dashed var(--border);">
                    <div style="font-size:0.82rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
                        💡 Quick Demo Login (Click to Autofill)
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <button type="button" class="role-btn" onclick="fillRole('customer@example.com', 'password123')">
                            <strong style="color:var(--espresso); font-size:0.88rem;">👤 Customer</strong><br>
                            <span style="font-size:0.78rem; color:var(--muted);">Storefront / Order</span>
                        </button>
                        <button type="button" class="role-btn" onclick="fillRole('admin@example.com', 'password123')">
                            <strong style="color:#b45309; font-size:0.88rem;">🛡️ Admin</strong><br>
                            <span style="font-size:0.78rem; color:var(--muted);">Orders Dashboard</span>
                        </button>
                    </div>
                </div>

                <div style="text-align:center; margin-top:22px; font-size:0.92rem; color:var(--muted);">
                    Don't have an account? <a href="register.php" style="color:var(--primary-dark); font-weight:700; text-decoration:none;">Register Now</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillRole(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
