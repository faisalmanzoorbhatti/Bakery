<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Online Bay Bakery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="products.php" class="brand-logo">
            <span>🥖</span> Online Bay Bakery
        </a>
        <div class="nav-links">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" style="background:#fef3c7; color:#92400e; font-weight:600;">🛡️ Admin Dashboard</a>
            <?php endif; ?>
            <a href="products.php">Categories</a>
            <a href="my_orders.php">My Orders</a>
            <a href="view_cart.php">Cart</a>
            <a href="account_settings.php">Settings</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 600px; text-align: center; margin-top: 50px;">
        <div class="form-box" style="padding: 45px 30px; max-width: 100%;">
            <div style="font-size: 3.5rem; margin-bottom: 15px;">🎉</div>
            <h1 style="font-size: 1.85rem; color: var(--dark); margin-bottom: 12px;">Order Placed Successfully!</h1>
            <p style="color: var(--gray); font-size: 1.05rem; margin-bottom: 25px;">
                Thank you for your purchase from Online Bay Bakery. Your delicious baked goods are being prepared with care.
            </p>

            <div class="alert alert-success" style="text-align: left; margin-bottom: 30px;">
                <strong>📱 Order Confirmation:</strong><br>
                Your order is confirmed and has been sent to the bakery kitchen. You can track preparation status live.
            </div>

            <div style="display:flex; justify-content:center; gap:15px; flex-wrap:wrap;">
                <a href="my_orders.php" class="btn btn-primary" style="width:auto; padding:12px 25px;">
                    Track in My Orders &rarr;
                </a>
                <a href="products.php" class="btn btn-secondary" style="width:auto; padding:12px 25px;">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Fresh Quality Baked Goods.</p>
    </footer>
</body>
</html>
