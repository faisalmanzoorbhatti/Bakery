<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];
require_once 'config.php';
$conn = getDbConnection();

// Fetch customer's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders_result = $stmt->get_result();

$orders = [];
$order_ids = [];
while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
    $order_ids[] = (int)$row['id'];
}
$stmt->close();

// Fetch items for these orders
$items_map = [];
if (!empty($order_ids)) {
    $id_list = implode(',', $order_ids);
    $items_res = $conn->query("SELECT order_items.*, products.name as product_name, products.image_path 
                               FROM order_items 
                               JOIN products ON order_items.product_id = products.id 
                               WHERE order_items.order_id IN ($id_list)");
    if ($items_res) {
        while ($it = $items_res->fetch_assoc()) {
            $items_map[$it['order_id']][] = $it;
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Online Bay Bakery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .status-Paid, .status-Completed { background: #dcfce7; color: #15803d; }
        .status-Pending { background: #fef3c7; color: #b45309; }
        .status-Preparing { background: #e0f2fe; color: #0369a1; }
        .status-Out-for-Delivery { background: #f3e8ff; color: #7e22ce; }
        .status-Cancelled { background: #fee2e2; color: #b91c1c; }
        .order-card {
            background: #ffffff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .order-header {
            padding: 16px 20px;
            background: #fafafa;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .order-body {
            padding: 20px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .item-row:last-child {
            border-bottom: none;
        }
    </style>
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
            <a href="my_orders.php" class="active">My Orders</a>
            <a href="view_cart.php">Cart</a>
            <a href="account_settings.php">Settings</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 900px;">
        <div class="page-header" style="text-align: left; margin-bottom: 25px;">
            <h1>My Order History</h1>
            <p>Track your bakery orders, preparation updates, and delivery status</p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="form-box" style="text-align:center; padding:50px 20px; max-width:100%;">
                <div style="font-size:3rem; margin-bottom:12px;">🛍️</div>
                <h3>No Orders Yet</h3>
                <p style="color:var(--gray); margin-bottom:20px;">You haven't placed any bakery orders yet. Explore our fresh cakes, breads, and pastries!</p>
                <a href="products.php" class="btn btn-primary" style="width:auto; padding:12px 28px;">
                    Explore Menu &rarr;
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $ord): 
                $oid = $ord['id'];
                $status_class = str_replace(' ', '-', $ord['payment_status']);
                $items = $items_map[$oid] ?? [];
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <span style="font-size:0.85rem; color:var(--gray);">Order placed:</span>
                            <strong style="color:var(--dark); margin-left:5px;">
                                <?php echo date('M d, Y - h:i A', strtotime($ord['order_date'])); ?>
                            </strong>
                            <span style="margin-left:12px; color:var(--primary); font-weight:700;">#<?php echo $oid; ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span class="status-badge status-<?php echo htmlspecialchars($status_class); ?>">
                                <?php echo htmlspecialchars($ord['payment_status']); ?>
                            </span>
                            <strong style="font-size:1.15rem; color:var(--dark);">
                                $<?php echo number_format($ord['total_amount'], 2); ?>
                            </strong>
                        </div>
                    </div>
                    <div class="order-body">
                        <div style="font-size:0.88rem; color:var(--gray); margin-bottom:12px;">
                            <strong>Payment Method:</strong> <?php echo htmlspecialchars($ord['payment_method']); ?>
                        </div>

                        <div style="border-top:1px solid #f1f5f9; padding-top:10px;">
                            <?php if (empty($items)): ?>
                                <p style="color:var(--gray); font-size:0.9rem;">Items details recorded.</p>
                            <?php else: ?>
                                <?php foreach ($items as $it): ?>
                                    <div class="item-row">
                                        <div>
                                            <strong style="color:var(--dark);"><?php echo htmlspecialchars($it['product_name']); ?></strong>
                                            <span style="color:var(--gray); font-size:0.85rem; margin-left:8px;">&times; <?php echo (int)$it['quantity']; ?></span>
                                        </div>
                                        <div style="font-weight:600; color:var(--dark);">
                                            $<?php echo number_format($it['price'] * $it['quantity'], 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Fresh Quality Baked Goods.</p>
    </footer>
</body>
</html>
