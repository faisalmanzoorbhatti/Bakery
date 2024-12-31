<?php
session_start();

// Strict Admin Access Control
if (!isset($_SESSION['customer_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php?error=" . urlencode("Unauthorized access. Admin privileges required."));
    exit();
}

require_once 'config.php';
$conn = getDbConnection();

$msg = "";
$error = "";

// Handle Order Status Update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_order_status'])) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = trim($_POST['status'] ?? '');

    $allowed_statuses = ['Paid', 'Pending', 'Preparing', 'Out for Delivery', 'Completed', 'Cancelled'];
    if ($order_id > 0 && in_array($new_status, $allowed_statuses)) {
        $stmt_up = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt_up->bind_param("si", $new_status, $order_id);
        if ($stmt_up->execute()) {
            $msg = "Order #{$order_id} status updated to '{$new_status}' successfully.";
        } else {
            $error = "Failed to update order status.";
        }
        $stmt_up->close();
    }
}

// Statistics
$total_revenue = $conn->query("SELECT SUM(total_amount) as rev FROM orders WHERE payment_status IN ('Paid', 'Preparing', 'Out for Delivery', 'Completed')")->fetch_assoc()['rev'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as cnt FROM orders")->fetch_assoc()['cnt'] ?? 0;
$total_customers = $conn->query("SELECT COUNT(*) as cnt FROM customers WHERE role != 'admin'")->fetch_assoc()['cnt'] ?? 0;
$total_products = $conn->query("SELECT COUNT(*) as cnt FROM products")->fetch_assoc()['cnt'] ?? 0;

// Filter & Search
$status_filter = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');

$sql_orders = "SELECT orders.*, customers.name as customer_name, customers.email as customer_email, customers.phone as customer_phone 
               FROM orders 
               LEFT JOIN customers ON orders.customer_id = customers.id 
               WHERE 1=1";

if (!empty($status_filter)) {
    $sql_orders .= " AND orders.payment_status = '" . $conn->real_escape_string($status_filter) . "'";
}
if (!empty($search)) {
    $escaped = $conn->real_escape_string($search);
    $sql_orders .= " AND (customers.name LIKE '%$escaped%' OR customers.email LIKE '%$escaped%' OR orders.id = '$escaped')";
}

$sql_orders .= " ORDER BY orders.order_date DESC";
$orders_result = $conn->query($sql_orders);

// Fetch all order items mapped by order_id
$items_sql = "SELECT order_items.*, products.name as product_name, products.image_path, categories.name as category_name 
              FROM order_items 
              JOIN products ON order_items.product_id = products.id 
              LEFT JOIN categories ON products.category_id = categories.id";
$items_res = $conn->query($items_sql);
$order_items_map = [];
while ($it = $items_res->fetch_assoc()) {
    $order_items_map[$it['order_id']][] = $it;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - L'Atelier Gourmet Bakery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-nav-badge {
            background: #fef3c7;
            color: #92400e;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: var(--radius-pill);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 22px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 18px;
            transition: var(--transition);
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .stat-icon {
            font-size: 2.2rem;
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface-alt);
            border-radius: var(--radius-md);
            flex-shrink: 0;
        }
        .stat-info .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--espresso);
            line-height: 1.1;
        }
        .stat-info .stat-label {
            color: var(--muted);
            font-size: 0.88rem;
            margin-top: 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            font-size: 0.82rem;
            font-weight: 700;
        }
        .status-Paid, .status-Completed { background: #dcfce7; color: #15803d; }
        .status-Pending { background: #fef3c7; color: #b45309; }
        .status-Preparing { background: #e0f2fe; color: #0369a1; }
        .status-Out-for-Delivery { background: #f3e8ff; color: #7e22ce; }
        .status-Cancelled { background: #fee2e2; color: #b91c1c; }
        .order-row:hover {
            background: #fafaf9;
        }
        .items-collapse {
            background: #fdfbf7;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-top: 10px;
            font-size: 0.88rem;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <div class="top-announcement">
        <span>🛡️ <strong>L'Atelier Admin Control Center:</strong> Live Bakery Operations & Orders Monitor</span>
    </div>

    <nav class="navbar">
        <a href="admin_dashboard.php" class="brand-logo">
            <div class="brand-icon">🥐</div>
            <div class="brand-text-wrap">
                <span class="brand-name">L'Atelier Bakery</span>
                <span class="brand-tagline">Admin Control Center</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="admin_dashboard.php" class="active">Live Orders</a>
            <a href="admin_products.php">Manage Products (34)</a>
            <a href="products.php" target="_blank" style="color:var(--primary-dark); font-weight:600;">Customer Storefront &nearr;</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 1400px;">
        <div class="section-header" style="margin-bottom:28px;">
            <div>
                <h1 class="section-title">Bakery Orders & Sales Operations</h1>
                <p class="section-subtitle">Monitor live customer orders, update delivery pipeline, and inspect item breakdowns</p>
            </div>
            <a href="admin_products.php" class="btn btn-primary" style="padding:10px 22px; font-size:0.92rem;">
                ➕ Add New Bakery Item
            </a>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Key Metrics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-label">Total Sales Revenue</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo (int)$total_orders; ?></div>
                    <div class="stat-label">Total Orders Placed</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo (int)$total_customers; ?></div>
                    <div class="stat-label">Registered Customers</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🥐</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo (int)$total_products; ?></div>
                    <div class="stat-label">Menu Items (34)</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="filter-search-section" style="margin-bottom:24px;">
            <form action="admin_dashboard.php" method="GET" style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
                <input type="text" name="search" placeholder="Search by customer name, email, or Order #..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="flex:1; min-width:260px;">
                <select name="status" class="form-control" style="width:auto; min-width:180px;">
                    <option value="">All Order Statuses</option>
                    <option value="Paid" <?php echo ($status_filter === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="Preparing" <?php echo ($status_filter === 'Preparing') ? 'selected' : ''; ?>>Preparing (In Oven)</option>
                    <option value="Out for Delivery" <?php echo ($status_filter === 'Out for Delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                    <option value="Completed" <?php echo ($status_filter === 'Completed') ? 'selected' : ''; ?>>Completed (Delivered)</option>
                    <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Cancelled" <?php echo ($status_filter === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-primary" style="padding:12px 24px; white-space:nowrap;">
                    Filter Orders
                </button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="admin_dashboard.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Orders Table -->
        <?php if ($orders_result->num_rows === 0): ?>
            <div class="form-box" style="text-align:center; padding:50px 20px;">
                <div style="font-size:3rem; margin-bottom:12px;">📋</div>
                <h3>No Orders Found</h3>
                <p style="color:var(--muted);">There are no orders matching your current filter.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer Contact</th>
                            <th>Placed At</th>
                            <th>Amount</th>
                            <th>Gateway</th>
                            <th>Status</th>
                            <th>Quick Status Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ord = $orders_result->fetch_assoc()): 
                            $oid = $ord['id'];
                            $status_class = str_replace(' ', '-', $ord['payment_status']);
                            $items = $order_items_map[$oid] ?? [];
                        ?>
                            <tr class="order-row">
                                <td>
                                    <strong style="color:var(--primary-dark); font-size:1.1rem;">#<?php echo $oid; ?></strong>
                                </td>
                                <td>
                                    <strong style="color:var(--espresso);"><?php echo htmlspecialchars($ord['customer_name'] ?? 'Walk-in Guest'); ?></strong><br>
                                    <span style="font-size:0.85rem; color:var(--muted);"><?php echo htmlspecialchars($ord['customer_email'] ?? ''); ?></span><br>
                                    <span style="font-size:0.85rem; color:var(--body-text); font-weight:600;">📞 <?php echo htmlspecialchars($ord['customer_phone'] ?? 'N/A'); ?></span>
                                    
                                    <!-- Expandable Items Breakdown -->
                                    <details style="margin-top:8px; cursor:pointer;">
                                        <summary style="color:var(--primary-dark); font-weight:700; font-size:0.85rem;">
                                            📦 View Items (<?php echo count($items); ?>)
                                        </summary>
                                        <div class="items-collapse">
                                            <?php if (empty($items)): ?>
                                                <span style="color:var(--muted);">No item records found.</span>
                                            <?php else: ?>
                                                <?php foreach ($items as $item): 
                                                    $item_img = !empty($item['image_path']) ? $item['image_path'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=100&q=80';
                                                ?>
                                                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; border-bottom:1px dashed var(--border); padding-bottom:6px;">
                                                        <img src="<?php echo htmlspecialchars($item_img); ?>" style="width:34px; height:34px; border-radius:6px; object-fit:cover;">
                                                        <div style="flex:1;">
                                                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                            <span style="color:var(--muted); font-size:0.82rem;"> &times; <?php echo (int)$item['quantity']; ?></span>
                                                        </div>
                                                        <strong style="color:var(--espresso);">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                </td>
                                <td style="font-size:0.88rem; color:var(--body-text);">
                                    <?php echo date('M d, Y', strtotime($ord['order_date'])); ?><br>
                                    <small style="color:var(--muted);"><?php echo date('h:i A', strtotime($ord['order_date'])); ?></small>
                                </td>
                                <td>
                                    <strong style="font-size:1.2rem; color:var(--espresso);">$<?php echo number_format($ord['total_amount'], 2); ?></strong>
                                </td>
                                <td>
                                    <span style="font-weight:600; font-size:0.9rem;"><?php echo htmlspecialchars($ord['payment_method']); ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($status_class); ?>">
                                        <?php echo htmlspecialchars($ord['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="admin_dashboard.php" method="POST" style="display:flex; gap:8px; align-items:center;">
                                        <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                        <select name="status" class="form-control" style="padding:8px 12px; font-size:0.85rem; width:140px;">
                                            <option value="Paid" <?php echo ($ord['payment_status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                            <option value="Preparing" <?php echo ($ord['payment_status'] === 'Preparing') ? 'selected' : ''; ?>>Preparing</option>
                                            <option value="Out for Delivery" <?php echo ($ord['payment_status'] === 'Out for Delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                                            <option value="Completed" <?php echo ($ord['payment_status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Pending" <?php echo ($ord['payment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Cancelled" <?php echo ($ord['payment_status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_order_status" class="btn btn-secondary" style="padding:8px 14px; font-size:0.82rem;">
                                            Save
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery & Boulangerie &bull; Admin Portal
        </div>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
