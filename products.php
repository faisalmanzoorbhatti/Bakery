<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = getDbConnection();

// Fetch categories
$sql = "SELECT categories.*, COUNT(products.id) as product_count 
        FROM categories 
        LEFT JOIN products ON categories.id = products.category_id 
        GROUP BY categories.id";
$result = $conn->query($sql);

// Category fallback images
$default_images = [
    1 => 'images/cake.jpg',
    2 => 'images/chocolate_cake.jpg.jpeg',
    3 => 'images/img_0130.jpg',
    4 => 'images/cake.jpg',
];

// Fetch cart count
$customer_id = (int)$_SESSION['customer_id'];
$cart_stmt = $conn->prepare("SELECT SUM(quantity) as total_qty FROM cart WHERE customer_id = ?");
$cart_stmt->bind_param("i", $customer_id);
$cart_stmt->execute();
$cart_res = $cart_stmt->get_result();
$cart_row = $cart_res->fetch_assoc();
$cart_count = $cart_row['total_qty'] ?? 0;
$cart_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakery Categories - Online Bay Bakery</title>
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
            <a href="products.php" class="active">Categories</a>
            <a href="my_orders.php">My Orders</a>
            <a href="view_cart.php">Cart <?php if ($cart_count > 0): ?><span style="background:var(--primary); color:white; padding:2px 7px; border-radius:10px; font-size:0.75rem;"><?php echo $cart_count; ?></span><?php endif; ?></a>
            <a href="account_settings.php">Settings</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Freshly Baked Daily</h1>
            <p>Select a category below to explore our freshly prepared artisanal treats</p>
        </div>

        <div class="grid">
            <?php while ($row = $result->fetch_assoc()): 
                $img = !empty($row['image_path']) && file_exists($row['image_path']) 
                    ? $row['image_path'] 
                    : ($default_images[$row['id']] ?? 'images/cake.jpg');
            ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="card-img">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p class="card-desc"><?php echo (int)$row['product_count']; ?> artisanal items available</p>
                        <a href="category.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-primary">
                            Explore <?php echo htmlspecialchars($row['name']); ?> &rarr;
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Fresh Quality Baked Goods.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
