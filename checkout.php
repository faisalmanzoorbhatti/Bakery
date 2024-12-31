<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];
require_once 'config.php';
$conn = getDbConnection();

// Fetch customer info
$cust_stmt = $conn->prepare("SELECT name, email, phone FROM customers WHERE id = ?");
$cust_stmt->bind_param("i", $customer_id);
$cust_stmt->execute();
$customer = $cust_stmt->get_result()->fetch_assoc();
$cust_stmt->close();

// Fetch cart items
$stmt = $conn->prepare("SELECT products.name, products.price, cart.quantity, (products.price * cart.quantity) as subtotal 
                        FROM cart
                        JOIN products ON cart.product_id = products.id
                        WHERE cart.customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total_amount = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total_amount += $row['subtotal'];
}
$stmt->close();
$conn->close();

if ($total_amount <= 0) {
    header("Location: view_cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Online Bay Bakery</title>
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

    <div class="container" style="max-width: 800px;">
        <div style="margin-bottom: 20px;">
            <a href="view_cart.php" style="color:var(--gray); text-decoration:none; font-size:0.95rem;">&larr; Back to cart</a>
        </div>

        <div class="page-header" style="text-align: left; margin-bottom: 25px;">
            <h1>Checkout & Payment</h1>
            <p>Complete your order and receive instant SMS confirmation</p>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:25px;">
            <!-- Left: Order Summary -->
            <div class="form-box" style="margin:0; max-width:100%;">
                <h3 style="margin-bottom:15px; font-size:1.15rem; color:var(--dark);">Order Summary</h3>
                <div style="border-bottom:1px solid var(--border); padding-bottom:15px; margin-bottom:15px;">
                    <?php foreach ($items as $item): ?>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.92rem;">
                            <span><?php echo htmlspecialchars($item['name']); ?> &times; <?php echo (int)$item['quantity']; ?></span>
                            <strong>$<?php echo number_format($item['subtotal'], 2); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; font-size:1.2rem; font-weight:700;">
                    <span>Total Due:</span>
                    <span style="color:var(--primary);">$<?php echo number_format($total_amount, 2); ?></span>
                </div>
            </div>

            <!-- Right: Payment & Delivery -->
            <div class="form-box" style="margin:0; max-width:100%;">
                <h3 style="margin-bottom:15px; font-size:1.15rem; color:var(--dark);">Payment Method</h3>
                <form action="process_payment.php" method="POST">
                    <div class="form-group">
                        <label for="payment_method">Select Payment Gateway</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="JazzCash">JazzCash Mobile Wallet</option>
                            <option value="EasyPaisa">EasyPaisa Mobile Account</option>
                            <option value="Cash on Delivery">Cash on Delivery</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Delivery Recipient</label>
                        <div style="background:var(--light-gray); padding:10px 14px; border-radius:8px; font-size:0.9rem;">
                            <strong><?php echo htmlspecialchars($customer['name'] ?? 'Customer'); ?></strong><br>
                            <span style="color:var(--gray);"><?php echo htmlspecialchars($customer['email'] ?? ''); ?></span><br>
                            <span style="color:var(--gray);"><?php echo htmlspecialchars(!empty($customer['phone']) ? $customer['phone'] : 'No phone specified'); ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top:10px;">
                        Place Order ($<?php echo number_format($total_amount, 2); ?>) &rarr;
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Fresh Quality Baked Goods.</p>
    </footer>
</body>
</html>
