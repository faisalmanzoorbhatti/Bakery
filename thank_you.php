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
            <a href="products.php">Categories</a>
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
                An automated SMS confirmation has been dispatched to your registered phone number.
            </div>

            <div style="display:flex; justify-content:center; gap:15px;">
                <a href="products.php" class="btn btn-primary" style="width:auto; padding:12px 30px;">
                    Continue Shopping &rarr;
                </a>
                <a href="account_settings.php" class="btn btn-secondary" style="width:auto;">
                    My Account
                </a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Fresh Quality Baked Goods.</p>
    </footer>
</body>
</html>
