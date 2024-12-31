<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$category_id = (int)($_GET['id'] ?? 1);

require_once 'config.php';
$conn = getDbConnection();

// Fetch category name
$cat_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$cat_res = $cat_stmt->get_result();
$category = $cat_res->fetch_assoc();
$cat_stmt->close();

$category_name = $category['name'] ?? 'Bakery Products';

// Fetch products
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch cart count
$customer_id = (int)$_SESSION['customer_id'];
$cart_stmt = $conn->prepare("SELECT SUM(quantity) as total_qty FROM cart WHERE customer_id = ?");
$cart_stmt->bind_param("i", $customer_id);
$cart_stmt->execute();
$cart_res = $cart_stmt->get_result();
$cart_row = $cart_res->fetch_assoc();
$cart_count = $cart_row['total_qty'] ?? 0;
$cart_stmt->close();

// Product image map
$product_images = [
    1 => 'images/chocolate_cake.jpg.jpeg',
    2 => 'images/cake.jpg',
    3 => 'images/chocolate_cake.jpg.jpeg',
    4 => 'images/img_0130.jpg',
    5 => 'images/img_0130.jpg',
    6 => 'images/img_0130.jpg',
    7 => 'images/cake.jpg',
    8 => 'images/cake.jpg',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_name); ?> - Online Bay Bakery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="products.php" class="brand-logo">
            <span>🥖</span> Online Bay Bakery
        </a>
        <div class="nav-links">
            <a href="products.php">Categories</a>
            <a href="view_cart.php">Cart <?php if ($cart_count > 0): ?><span style="background:var(--primary); color:white; padding:2px 7px; border-radius:10px; font-size:0.75rem;"><?php echo $cart_count; ?></span><?php endif; ?></a>
            <a href="account_settings.php">Settings</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div style="margin-bottom: 25px;">
            <a href="products.php" style="color:var(--gray); text-decoration:none; font-size:0.95rem; font-weight:500;">
                &larr; Back to all categories
            </a>
        </div>

        <div class="page-header" style="text-align: left; margin-bottom: 30px;">
            <h1><?php echo htmlspecialchars($category_name); ?></h1>
            <p>Handmade daily with premium ingredients and time-honored recipes</p>
        </div>

        <?php if ($result->num_rows === 0): ?>
            <div class="alert alert-error">
                No products found in this category right now. Please check back later!
            </div>
        <?php else: ?>
            <div class="grid">
                <?php while ($row = $result->fetch_assoc()): 
                    $img = !empty($row['image_path']) && file_exists($row['image_path']) 
                        ? $row['image_path'] 
                        : ($product_images[$row['id']] ?? 'images/cake.jpg');
                ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="card-img">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p class="card-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="card-price">$<?php echo number_format($row['price'], 2); ?></div>
                            
                            <form action="add_to_cart.php" method="POST" style="display:flex; gap:10px;">
                                <input type="hidden" name="product_id" value="<?php echo (int)$row['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="50" class="form-control" style="width:75px; text-align:center; padding:8px;">
                                <button type="submit" class="btn btn-primary" style="flex:1;">
                                    Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Fresh Quality Baked Goods.</p>
    </footer>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
