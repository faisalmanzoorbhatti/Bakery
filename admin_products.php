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

// Handle Delete Product
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt_del = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt_del->bind_param("i", $del_id);
    if ($stmt_del->execute()) {
        $msg = "Product deleted successfully from bakery menu.";
    } else {
        $error = "Failed to delete product (item is already referenced in previous orders).";
    }
    $stmt_del->close();
}

// Handle Add Product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_product'])) {
    $category_id = (int)($_POST['category_id'] ?? 1);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $image_url = trim($_POST['image_path'] ?? '');
    $badge = trim($_POST['badge'] ?? '');

    if (empty($image_url)) {
        $image_url = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80';
    }

    if (empty($name) || $price <= 0) {
        $error = "Please provide a valid product name and price.";
    } else {
        $stmt_ins = $conn->prepare("INSERT INTO products (category_id, name, description, price, image, image_path, badge) VALUES (?, ?, ?, ?, '', ?, ?)");
        $stmt_ins->bind_param("issdss", $category_id, $name, $description, $price, $image_url, $badge);
        if ($stmt_ins->execute()) {
            $msg = "New bakery product '{$name}' added to menu successfully!";
        } else {
            $error = "Error adding product: " . htmlspecialchars($conn->error);
        }
        $stmt_ins->close();
    }
}

// Fetch categories
$categories_res = $conn->query("SELECT * FROM categories ORDER BY id ASC");
$categories = [];
while ($c = $categories_res->fetch_assoc()) {
    $categories[$c['id']] = $c['name'];
}

// Fetch all products
$products_res = $conn->query("SELECT products.*, categories.name as category_name 
                               FROM products 
                               LEFT JOIN categories ON products.category_id = categories.id 
                               ORDER BY products.category_id ASC, products.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bakery Products - L'Atelier Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .product-thumb {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            background: #e7e5e4;
        }
    </style>
</head>
<body>
    <div class="top-announcement">
        <span>🛡️ <strong>L'Atelier Admin Control Center:</strong> Menu Catalog & Bakery Items Manager</span>
    </div>

    <nav class="navbar">
        <a href="admin_dashboard.php" class="brand-logo">
            <div class="brand-icon">🥐</div>
            <div class="brand-text-wrap">
                <span class="brand-name">L'Atelier Bakery</span>
                <span class="brand-tagline">Menu Management</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="admin_dashboard.php">Live Orders</a>
            <a href="admin_products.php" class="active">Manage Products</a>
            <a href="products.php" target="_blank" style="color:var(--primary-dark); font-weight:600;">Customer Storefront &nearr;</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 1400px;">
        <div class="section-header" style="margin-bottom:28px;">
            <div>
                <h1 class="section-title">Bakery Menu Management</h1>
                <p class="section-subtitle">Add new oven-fresh creations, set prices, assign categories, and update menu items</p>
            </div>
            <a href="admin_dashboard.php" class="btn btn-secondary">
                &larr; Back to Orders Dashboard
            </a>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Add New Product Form -->
        <div class="form-box" style="margin-bottom: 35px;">
            <h3 style="font-family:var(--font-heading); font-size:1.3rem; color:var(--espresso); margin-bottom:18px;">
                ➕ Add New Product to Bakery Menu
            </h3>
            <form action="admin_products.php" method="POST">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:18px;">
                    <div class="form-group">
                        <label for="name">Product Title</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Pistachio Frangipane Tart" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <?php foreach ($categories as $cid => $cname): ?>
                                <option value="<?php echo $cid; ?>"><?php echo htmlspecialchars($cname); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Price ($ USD)</label>
                        <input type="number" step="0.01" min="0.50" id="price" name="price" class="form-control" placeholder="18.50" required>
                    </div>

                    <div class="form-group">
                        <label for="badge">Badge / Tag (Optional)</label>
                        <input type="text" id="badge" name="badge" class="form-control" placeholder="e.g. ⭐ Best Seller, 🔥 Hot">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image_path">Product Image (Unsplash or Web URL)</label>
                    <input type="url" id="image_path" name="image_path" class="form-control" placeholder="https://images.unsplash.com/photo-...">
                </div>

                <div class="form-group">
                    <label for="description">Appetizing Description & Flavor Notes</label>
                    <textarea id="description" name="description" rows="2" class="form-control" placeholder="Describe the ingredients, crust texture, baking process..."></textarea>
                </div>

                <button type="submit" name="add_product" class="btn btn-primary" style="padding:12px 28px;">
                    Publish to Menu &rarr;
                </button>
            </form>
        </div>

        <!-- Products List Table -->
        <h3 style="font-family:var(--font-heading); font-size:1.4rem; color:var(--espresso); margin-bottom:16px;">
            Current Menu Items (<?php echo $products_res->num_rows; ?>)
        </h3>

        <div style="overflow-x:auto;">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Badge</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($prod = $products_res->fetch_assoc()): 
                        $img = !empty($prod['image_path']) ? $prod['image_path'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=100&q=80';
                    ?>
                        <tr class="order-row">
                            <td style="display:flex; align-items:center; gap:12px;">
                                <img src="<?php echo htmlspecialchars($img); ?>" class="product-thumb">
                                <div>
                                    <strong style="color:var(--espresso);"><?php echo htmlspecialchars($prod['name']); ?></strong><br>
                                    <span style="font-size:0.8rem; color:var(--muted);">ID: #<?php echo $prod['id']; ?></span>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:0.88rem; font-weight:600; color:var(--primary-dark);">
                                    <?php echo htmlspecialchars($prod['category_name'] ?? 'General'); ?>
                                </span>
                            </td>
                            <td>
                                <strong style="font-size:1.1rem; color:var(--espresso);">
                                    $<?php echo number_format($prod['price'], 2); ?>
                                </strong>
                            </td>
                            <td>
                                <?php if (!empty($prod['badge'])): ?>
                                    <span class="card-badge gold" style="position:static; display:inline-block; font-size:0.75rem;">
                                        <?php echo htmlspecialchars($prod['badge']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:var(--muted); font-size:0.85rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width:320px; font-size:0.85rem; color:var(--muted); line-height:1.4;">
                                <?php echo htmlspecialchars($prod['description']); ?>
                            </td>
                            <td>
                                <a href="admin_products.php?delete=<?php echo $prod['id']; ?>" onclick="return confirm('Are you sure you want to delete this bakery item from the menu?');" style="color:#ef4444; font-weight:700; font-size:0.85rem; text-decoration:none;">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery & Boulangerie &bull; Menu Administration
        </div>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
