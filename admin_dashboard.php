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
            $msg = "Order #{$order_id} status updated to '{$new_status}'.";
        } else {
            $error = "Failed to update order status.";
        }
        $stmt_up->close();
    }
}

// Statistics
$total_revenue = $conn->query("SELECT SUM(total_amount) as rev FROM orders WHERE payment_status = 'Paid'")->fetch_assoc()['rev'] ?? 0;
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
$items_sql = "SELECT order_items.*, products.name as product_name, products.image_path 
              FROM order_items 
              JOIN products ON order_items.product_id = products.id";
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
    <title>Admin Dashboard - Online Bay Bakery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-nav-badge {
            background: #f59e0b;
            color: #78350f;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #ffffff;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            font-size: 2.2rem;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-light);
            border-radius: 12px;
        }
        .stat-info .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--dark);
        }
        .stat-info .stat-label {
            color: var(--gray);
            font-size: 0.88rem;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .status-Paid, .status-Completed { background: #dcfce7; color: #15803d; }
        .status-Pending { background: #fef3c7; color: #b45309; }
        .status-Preparing { background: #e0f2fe; color: #0369a1; }
        .status-Out-for-Delivery { background: #f3e8ff; color: #7e22ce; }
        .status-Cancelled { background: #fee2e2; color: #b91c1c; }
        .order-row {
            transition: background 0.15s;
        }
        .order-row:hover {
            background: #fafafa;
        }
        .items-collapse {
            background: #fbfbfb;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 0.88rem;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="admin_dashboard.php" class="brand-logo">
            <span>🛡️</span> Online Bay Bakery <span class="admin-nav-badge">Admin Portal</span>
        </a>
        <div class="nav-links">
            <a href="admin_dashboard.php" class="active">Orders</a>
            <a href="admin_products.php">Manage Products</a>
            <a href="products.php" target="_blank" style="color:var(--gray);">Customer View &nearr;</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 1200px;">
        <div class="page-header" style="text-align: left; margin-bottom: 25px;">
            <h1>Bakery Management & Orders</h1>
            <p>Monitor live customer orders, update delivery statuses, and oversee bakery sales</p>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-label">Total Revenue (Paid)</div>
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
                    <div class="stat-label">Products on Menu</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div style="background:#ffffff; border-radius:var(--radius); padding:16px 20px; box-shadow:var(--shadow); margin-bottom:25px; border:1px solid var(--border); display:flex; flex-wrap:wrap; gap:15px; justify-content:space-between; align-items:center;">
            <form action="admin_dashboard.php" method="GET" style="display:flex; gap:12px; flex:1; flex-wrap:wrap;">
                <input type="text" name="search" placeholder="Search by customer name, email, or Order #..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="max-width:350px;">
                <select name="status" class="form-control" style="width:auto;">
                    <option value="">All Statuses</option>
                    <option value="Paid" <?php echo ($status_filter === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Preparing" <?php echo ($status_filter === 'Preparing') ? 'selected' : ''; ?>>Preparing</option>
                    <option value="Out for Delivery" <?php echo ($status_filter === 'Out for Delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                    <option value="Completed" <?php echo ($status_filter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?php echo ($status_filter === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-primary" style="width:auto; padding:10px 20px;">Filter</button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="admin_dashboard.php" class="btn btn-secondary" style="width:auto;">Clear</a>
                <?php endif; ?>
            </form>

            <a href="admin_products.php" class="btn btn-secondary" style="width:auto;">
                ➕ Add New Bakery Product
            </a>
        </div>

        <!-- Orders Table -->
        <?php if ($orders_result->num_rows === 0): ?>
            <div class="form-box" style="text-align:center; max-width:100%; padding:40px;">
                <div style="font-size:2.5rem; margin-bottom:10px;">📋</div>
                <h3>No Orders Found</h3>
                <p style="color:var(--gray);">There are no customer orders matching your search or filter.</p>
            </div>
        <?php else: ?>
            <table class="cart-table" style="background:#ffffff;">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer Details</th>
                        <th>Date & Time</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Order Status</th>
                        <th>Update Status</th>
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
                                <strong style="color:var(--primary); font-size:1.05rem;">#<?php echo $oid; ?></strong>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($ord['customer_name'] ?? 'Guest Customer'); ?></strong><br>
                                <span style="font-size:0.85rem; color:var(--gray);"><?php echo htmlspecialchars($ord['customer_email'] ?? 'No email'); ?></span><br>
                                <span style="font-size:0.85rem; color:var(--gray);">📞 <?php echo htmlspecialchars($ord['customer_phone'] ?? 'N/A'); ?></span>
                                
                                <!-- Items Dropdown / Summary -->
                                <details style="margin-top:6px; cursor:pointer;">
                                    <summary style="color:var(--primary); font-weight:600; font-size:0.85rem;">
                                        View Items (<?php echo count($items); ?>)
                                    </summary>
                                    <div class="items-collapse">
                                        <?php if (empty($items)): ?>
                                            <span>No items recorded.</span>
                                        <?php else: ?>
                                            <?php foreach ($items as $item): ?>
                                                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                                    <span>• <?php echo htmlspecialchars($item['product_name']); ?> &times; <?php echo (int)$item['quantity']; ?></span>
                                                    <strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            </td>
                            <td style="font-size:0.88rem; color:var(--dark);">
                                <?php echo date('M d, Y h:i A', strtotime($ord['order_date'])); ?>
                            </td>
                            <td>
                                <strong style="font-size:1.1rem; color:var(--dark);">$<?php echo number_format($ord['total_amount'], 2); ?></strong>
                            </td>
                            <td>
                                <span style="font-weight:600;"><?php echo htmlspecialchars($ord['payment_method']); ?></span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($status_class); ?>">
                                    <?php echo htmlspecialchars($ord['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <form action="admin_dashboard.php" method="POST" style="display:flex; gap:6px;">
                                    <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                    <select name="status" class="form-control" style="padding:6px 10px; font-size:0.85rem; width:130px;">
                                        <option value="Paid" <?php echo ($ord['payment_status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="Preparing" <?php echo ($ord['payment_status'] === 'Preparing') ? 'selected' : ''; ?>>Preparing</option>
                                        <option value="Out for Delivery" <?php echo ($ord['payment_status'] === 'Out for Delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                                        <option value="Completed" <?php echo ($ord['payment_status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Pending" <?php echo ($ord['payment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Cancelled" <?php echo ($ord['payment_status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_order_status" class="btn btn-secondary" style="padding:6px 12px; font-size:0.8rem;">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Admin Control Center.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
