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
        $msg = "Product deleted successfully.";
    } else {
        $error = "Failed to delete product (might be referenced in previous orders).";
    }
    $stmt_del->close();
}

// Handle Add Product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_product'])) {
    $category_id = (int)($_POST['category_id'] ?? 1);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $image = trim($_POST['image'] ?? 'cake.jpg');
    $image_path = 'images/' . $image;

    if (empty($name) || $price <= 0) {
        $error = "Please provide a valid product name and price.";
    } else {
        $stmt_ins = $conn->prepare("INSERT INTO products (category_id, name, description, price, image, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_ins->bind_param("issdss", $category_id, $name, $description, $price, $image, $image_path);
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
                              ORDER BY products.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bakery Products - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="admin_dashboard.php" class="brand-logo">
            <span>🛡️</span> Online Bay Bakery <span style="background:#f59e0b; color:#78350f; font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:6px;">Admin</span>
        </a>
        <div class="nav-links">
            <a href="admin_dashboard.php">Orders</a>
            <a href="admin_products.php" class="active">Manage Products</a>
            <a href="products.php" target="_blank" style="color:var(--gray);">Customer View &nearr;</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 1100px;">
        <div style="margin-bottom: 20px;">
            <a href="admin_dashboard.php" style="color:var(--gray); text-decoration:none; font-size:0.95rem;">&larr; Back to Orders Dashboard</a>
        </div>

        <div class="page-header" style="text-align: left; margin-bottom: 25px;">
            <h1>Bakery Menu & Products</h1>
            <p>Add new pastries, artisan breads, and cakes, or manage existing menu items</p>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns: 350px 1fr; gap:25px; align-items:start;">
            <!-- Left: Add Product Form -->
            <div class="form-box" style="margin:0; max-width:100%;">
                <h3 style="margin-bottom:15px; font-size:1.15rem; color:var(--dark);">Add New Bakery Item</h3>
                <form action="admin_products.php" method="POST">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <?php foreach ($categories as $cid => $cname): ?>
                                <option value="<?php echo $cid; ?>"><?php echo htmlspecialchars($cname); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Red Velvet Cake" required>
                    </div>

                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" step="0.01" min="0.5" id="price" name="price" class="form-control" placeholder="15.00" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Brief delicious description of the baked good..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Showcase Image</label>
                        <select name="image" id="image" class="form-control">
                            <option value="cake.jpg">Cake (cake.jpg)</option>
                            <option value="chocolate_cake.jpg.jpeg">Chocolate Cake (chocolate_cake.jpg.jpeg)</option>
                            <option value="img_0130.jpg">Artisan Bakery Table (img_0130.jpg)</option>
                        </select>
                    </div>

                    <button type="submit" name="add_product" class="btn btn-primary" style="margin-top:10px;">
                        Add to Bakery Menu
                    </button>
                </form>
            </div>

            <!-- Right: Product Inventory Table -->
            <div style="background:#ffffff; border-radius:var(--radius); box-shadow:var(--shadow); border:1px solid var(--border); overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="font-size:1.15rem; color:var(--dark); margin:0;">Active Menu Items</h3>
                    <span style="color:var(--gray); font-size:0.9rem;"><?php echo $products_res->num_rows; ?> Products</span>
                </div>
                <table class="cart-table" style="box-shadow:none; margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($prod = $products_res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($prod['name']); ?></strong><br>
                                    <span style="font-size:0.82rem; color:var(--gray);"><?php echo htmlspecialchars($prod['description'] ?? ''); ?></span>
                                </td>
                                <td>
                                    <span style="background:var(--light-gray); padding:3px 8px; border-radius:6px; font-size:0.85rem;">
                                        <?php echo htmlspecialchars($prod['category_name'] ?? 'General'); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color:var(--primary);">$<?php echo number_format($prod['price'], 2); ?></strong>
                                </td>
                                <td style="text-align:center;">
                                    <a href="admin_products.php?delete=<?php echo (int)$prod['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" style="color:#dc2626; font-size:0.85rem; font-weight:600; text-decoration:none; padding:4px 8px; border-radius:6px; background:#fee2e2;">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Admin Control Center.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
