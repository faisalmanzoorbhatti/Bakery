<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];
require_once 'config.php';
$conn = getDbConnection();

// Handle remove item from cart
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    $del_stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ? AND product_id = ?");
    $del_stmt->bind_param("ii", $customer_id, $remove_id);
    $del_stmt->execute();
    $del_stmt->close();
    header("Location: view_cart.php");
    exit();
}

// Fetch cart items
$stmt = $conn->prepare("SELECT products.id as product_id, products.name, products.price, products.image_path, cart.quantity 
                        FROM cart
                        JOIN products ON cart.product_id = products.id
                        WHERE cart.customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - Online Bay Bakery</title>
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
            <a href="view_cart.php" class="active">Cart</a>
            <a href="account_settings.php">Settings</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 900px;">
        <div class="page-header" style="text-align: left; margin-bottom: 25px;">
            <h1>Shopping Cart</h1>
            <p>Review your selected bakery treats before proceeding to checkout</p>
        </div>

        <?php if (empty($items)): ?>
            <div class="form-box" style="text-align:center; max-width:100%; padding:50px 20px;">
                <div style="font-size:3rem; margin-bottom:15px;">🛒</div>
                <h2>Your Cart is Empty</h2>
                <p style="color:var(--gray); margin-bottom:25px;">You have no items in your bakery cart right now.</p>
                <a href="products.php" class="btn btn-primary" style="display:inline-flex; width:auto;">
                    Browse Categories &rarr;
                </a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo (int)$item['quantity']; ?></td>
                            <td><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>
                            <td style="text-align:center;">
                                <a href="view_cart.php?remove=<?php echo (int)$item['product_id']; ?>" style="color:#dc2626; text-decoration:none; font-size:0.85rem; font-weight:600; padding:4px 8px; border-radius:6px; background:#fee2e2;">
                                    Remove
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-total-box">
                <div>
                    <span style="color:var(--gray); font-size:0.95rem;">Order Total:</span>
                    <div class="total-amount">$<?php echo number_format($total, 2); ?></div>
                </div>
                <div style="display:flex; gap:12px;">
                    <a href="products.php" class="btn btn-secondary">Add More Items</a>
                    <a href="checkout.php" class="btn btn-primary" style="width:auto; padding:12px 28px;">Proceed to Checkout &rarr;</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Fresh Quality Baked Goods.</p>
    </footer>
</body>
</html>
