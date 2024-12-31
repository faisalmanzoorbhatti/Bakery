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
    $items_res = $conn->query("SELECT order_items.*, products.name as product_name, products.image_path, categories.name as category_name 
                               FROM order_items 
                               JOIN products ON order_items.product_id = products.id 
                               LEFT JOIN categories ON products.category_id = categories.id
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
    <title>My Orders - L'Atelier Gourmet Bakery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: var(--radius-pill);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .status-Paid, .status-Completed { background: #dcfce7; color: #15803d; }
        .status-Pending { background: #fef3c7; color: #b45309; }
        .status-Preparing { background: #e0f2fe; color: #0369a1; }
        .status-Out-for-Delivery { background: #f3e8ff; color: #7e22ce; }
        .status-Cancelled { background: #fee2e2; color: #b91c1c; }
        .order-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            margin-bottom: 24px;
            overflow: hidden;
            transition: var(--transition);
        }
        .order-card:hover {
            box-shadow: var(--shadow-md);
            border-color: rgba(217, 119, 6, 0.3);
        }
        .order-header {
            padding: 18px 24px;
            background: var(--surface-alt);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .order-body {
            padding: 24px;
        }
        .order-item-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
        }
        .order-item-row:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="top-announcement">
        <span>🥖 <strong>Fresh Morning Bakes:</strong> Batch #1 freshly pulled from hearth ovens</span>
        <span class="badge-pill">Free Express Delivery on orders over $35</span>
    </div>

    <nav class="navbar">
        <a href="products.php" class="brand-logo">
            <div class="brand-icon">🥐</div>
            <div class="brand-text-wrap">
                <span class="brand-name">L'Atelier Bakery</span>
                <span class="brand-tagline">Artisanal Boulangerie</span>
            </div>
        </a>
        <div class="nav-links">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" style="background:#fef3c7; color:#92400e; font-weight:700;">🛡️ Admin Portal</a>
            <?php endif; ?>
            <a href="products.php">Menu Catalog</a>
            <a href="my_orders.php" class="active">My Orders</a>
            <a href="view_cart.php">Cart</a>
            <a href="account_settings.php">Settings</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 1000px;">
        <div class="section-header" style="margin-bottom:30px;">
            <div>
                <h1 class="section-title">My Bakery Orders</h1>
                <p class="section-subtitle">Track your freshly baked items and delivery milestones in real-time</p>
            </div>
            <a href="products.php" class="btn btn-secondary" style="padding:9px 20px; font-size:0.9rem;">
                &larr; Order More Bakery Items
            </a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="form-box" style="text-align:center; padding:60px 20px;">
                <div style="font-size:3.5rem; margin-bottom:14px;">🛍️</div>
                <h2 style="font-family:var(--font-heading); color:var(--espresso); margin-bottom:8px;">No Orders Placed Yet</h2>
                <p style="color:var(--muted); margin-bottom:25px; font-size:1rem;">
                    You haven't ordered any delicious baked goods yet. Explore our artisan sourdough, croissants, and cakes!
                </p>
                <a href="products.php" class="btn btn-primary" style="padding:14px 32px;">
                    Explore Menu & Order &rarr;
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
                            <span style="font-size:0.85rem; color:var(--muted);">Order Ref:</span>
                            <strong style="color:var(--primary-dark); font-size:1.15rem; margin-left:4px;">#<?php echo $oid; ?></strong>
                            <span style="margin:0 8px; color:var(--border);">&bull;</span>
                            <span style="font-size:0.88rem; color:var(--body-text);">
                                <?php echo date('M d, Y - h:i A', strtotime($ord['order_date'])); ?>
                            </span>
                        </div>
                        <div style="display:flex; align-items:center; gap:14px;">
                            <span class="status-badge status-<?php echo htmlspecialchars($status_class); ?>">
                                <?php 
                                    if ($ord['payment_status'] === 'Preparing') echo '🔥 In Oven / Preparing';
                                    elseif ($ord['payment_status'] === 'Out for Delivery') echo '🛵 Out for Delivery';
                                    elseif ($ord['payment_status'] === 'Completed') echo '✓ Delivered';
                                    else echo htmlspecialchars($ord['payment_status']);
                                ?>
                            </span>
                            <strong style="font-size:1.25rem; color:var(--espresso);">
                                $<?php echo number_format($ord['total_amount'], 2); ?>
                            </strong>
                        </div>
                    </div>

                    <div class="order-body">
                        <div style="font-size:0.88rem; color:var(--muted); margin-bottom:16px;">
                            <strong>Payment:</strong> <?php echo htmlspecialchars($ord['payment_method']); ?>
                        </div>

                        <!-- Ordered Items Breakdown -->
                        <div style="border-top:1px solid var(--border-light); padding-top:12px;">
                            <?php foreach ($items as $it): 
                                $it_img = !empty($it['image_path']) ? $it['image_path'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=200&q=80';
                            ?>
                                <div class="order-item-row">
                                    <img src="<?php echo htmlspecialchars($it_img); ?>" style="width:50px; height:50px; border-radius:8px; object-fit:cover; flex-shrink:0;" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=200&q=80';">
                                    <div style="flex:1;">
                                        <div style="font-size:0.75rem; text-transform:uppercase; color:var(--primary-dark); font-weight:700;">
                                            <?php echo htmlspecialchars($it['category_name'] ?? 'Bakery Item'); ?>
                                        </div>
                                        <strong style="color:var(--espresso); font-size:0.95rem;">
                                            <?php echo htmlspecialchars($it['product_name']); ?>
                                        </strong>
                                        <span style="color:var(--muted); font-size:0.85rem; margin-left:8px;">
                                            &times; <?php echo (int)$it['quantity']; ?>
                                        </span>
                                    </div>
                                    <div style="font-weight:700; color:var(--espresso); font-size:1rem;">
                                        $<?php echo number_format($it['price'] * $it['quantity'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-inner">
            <div>
                <h3 style="font-family:var(--font-heading); color:#ffffff; font-size:1.4rem; margin-bottom:8px;">L'Atelier Gourmet Bakery</h3>
                <p style="font-size:0.9rem; max-width:400px; color:#a8a29e;">
                    Artisanal European bakery baking fresh sourdough, Viennoiserie, specialty cakes, and gourmet quiches every morning.
                </p>
            </div>
        </div>
        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery & Boulangerie. All rights reserved.
        </div>
    </footer>
</body>
</html>
