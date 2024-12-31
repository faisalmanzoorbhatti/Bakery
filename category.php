<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$category_id = (int)($_GET['id'] ?? 1);
$customer_id = (int)$_SESSION['customer_id'];

require_once 'config.php';
$conn = getDbConnection();

// Fetch category details
$cat_stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$cat_res = $cat_stmt->get_result();
$category = $cat_res->fetch_assoc();
$cat_stmt->close();

if (!$category) {
    header("Location: products.php");
    exit();
}

$category_name = $category['name'];
$category_banner = !empty($category['image_path']) ? $category['image_path'] : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=1200&q=80';

// Fetch all categories for quick pill switching
$all_cats_res = $conn->query("SELECT categories.*, COUNT(products.id) as product_count 
                              FROM categories 
                              LEFT JOIN products ON categories.id = products.category_id 
                              GROUP BY categories.id 
                              ORDER BY categories.id ASC");
$all_categories = [];
while ($ac = $all_cats_res->fetch_assoc()) {
    $all_categories[] = $ac;
}

// Fetch products in this category
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY is_featured DESC, id ASC");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($p = $result->fetch_assoc()) {
    $products[] = $p;
}
$stmt->close();

// Fetch cart count
$cart_stmt = $conn->prepare("SELECT SUM(quantity) as total_qty, SUM(cart.quantity * products.price) as cart_total 
                             FROM cart 
                             JOIN products ON cart.product_id = products.id 
                             WHERE cart.customer_id = ?");
$cart_stmt->bind_param("i", $customer_id);
$cart_stmt->execute();
$cart_res = $cart_stmt->get_result();
$cart_data = $cart_res->fetch_assoc();
$cart_count = (int)($cart_data['total_qty'] ?? 0);
$cart_total = (float)($cart_data['cart_total'] ?? 0.0);
$cart_stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_name); ?> - L'Atelier Bakery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .category-hero {
            position: relative;
            border-radius: var(--radius-lg);
            overflow: hidden;
            height: clamp(220px, 28vw, 320px);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #ffffff;
            margin-bottom: 30px;
            box-shadow: var(--shadow-lg);
        }
        .category-hero-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.42);
        }
        .category-hero-content {
            position: relative;
            z-index: 2;
            padding: 20px;
            max-width: 700px;
        }
        .toast-notification {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #1c1917;
            color: #ffffff;
            padding: 14px 26px;
            border-radius: var(--radius-pill);
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.94rem;
            font-weight: 600;
            z-index: 1000;
            opacity: 0;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(251, 191, 36, 0.3);
        }
        .toast-notification.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
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
            <a href="products.php" class="active">Menu Catalog</a>
            <a href="my_orders.php">My Orders</a>
            <a href="account_settings.php">Settings</a>
            <a href="view_cart.php" class="cart-nav-badge">
                🛒 Cart <span id="nav-cart-count" class="cart-pill-count"><?php echo $cart_count; ?></span>
            </a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Breadcrumb -->
        <div style="margin-bottom: 20px; font-size: 0.92rem; color: var(--muted);">
            <a href="products.php" style="color:var(--primary-dark); text-decoration:none; font-weight:600;">&larr; Back to Full Menu</a>
            <span style="margin: 0 8px;">/</span>
            <span><?php echo htmlspecialchars($category_name); ?></span>
        </div>

        <!-- Category Banner -->
        <div class="category-hero">
            <img src="<?php echo htmlspecialchars($category_banner); ?>" alt="<?php echo htmlspecialchars($category_name); ?>" class="category-hero-bg">
            <div class="category-hero-content">
                <div class="hero-pill" style="margin-bottom:10px;">Artisanal Collection</div>
                <h1 style="font-family:var(--font-heading); font-size:clamp(2rem, 3.6vw, 3rem); font-weight:700; margin-bottom:8px;">
                    <?php echo htmlspecialchars($category_name); ?>
                </h1>
                <p style="color:#e7e5e4; font-size:1.05rem;">
                    Prepared fresh by our master pastry chefs and bakers each morning.
                </p>
            </div>
        </div>

        <!-- Quick Category Switcher Pills -->
        <div class="category-pills-scroll" style="margin-bottom: 30px;">
            <a href="products.php" class="cat-pill">
                <span>🍽️ All Delights</span>
            </a>
            <?php foreach ($all_categories as $ac): ?>
                <a href="category.php?id=<?php echo $ac['id']; ?>" class="cat-pill <?php echo ($ac['id'] == $category_id) ? 'active' : ''; ?>">
                    <span><?php echo htmlspecialchars($ac['name']); ?></span>
                    <span class="count"><?php echo (int)$ac['product_count']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Section Header -->
        <div class="section-header">
            <div>
                <h2 class="section-title"><?php echo htmlspecialchars($category_name); ?></h2>
                <p class="section-subtitle">Showing <?php echo count($products); ?> handcrafted selections</p>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="product-grid">
            <?php foreach ($products as $prod): 
                $pid = $prod['id'];
                $badge = $prod['badge'] ?? '';
                $image_url = !empty($prod['image_path']) ? $prod['image_path'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80';
            ?>
                <div class="product-card">
                    <div class="card-img-wrap">
                        <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="card-img" loading="lazy">
                        <?php if (!empty($badge)): ?>
                            <div class="card-badge gold"><?php echo htmlspecialchars($badge); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="card-category-tag"><?php echo htmlspecialchars($category_name); ?></div>
                        <h3 class="card-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                        <p class="card-desc"><?php echo htmlspecialchars($prod['description']); ?></p>
                        
                        <div class="card-footer">
                            <div class="product-price">
                                $<?php echo number_format($prod['price'], 2); ?>
                            </div>
                            
                            <form action="add_to_cart.php" method="POST" class="add-cart-form" onsubmit="handleAddToCart(event, this)">
                                <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                <input type="hidden" name="ajax" value="1">
                                <div class="qty-stepper">
                                    <button type="button" class="qty-btn" onclick="adjustQty(this, -1)">&minus;</button>
                                    <input type="number" name="quantity" value="1" min="1" max="99" class="qty-input" readonly>
                                    <button type="button" class="qty-btn" onclick="adjustQty(this, 1)">&plus;</button>
                                </div>
                                <button type="submit" class="btn-add-cart" title="Add to Cart">
                                    <span>🛒</span> Add
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Floating Cart Bar -->
    <a href="view_cart.php" id="floatingCart" class="floating-cart-bar" style="<?php echo ($cart_count > 0) ? 'display:flex;' : 'display:none;'; ?>">
        <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-size:1.4rem;">🛍️</span>
            <div>
                <strong style="font-size:0.95rem; color:#ffffff;">Your Bakery Cart</strong><br>
                <span id="floating-cart-summary" style="font-size:0.82rem; color:#fde68a;">
                    <?php echo $cart_count; ?> item(s) &bull; $<?php echo number_format($cart_total, 2); ?>
                </span>
            </div>
        </div>
        <div style="background:linear-gradient(135deg, #d97706, #b45309); color:#ffffff; padding:6px 16px; border-radius:var(--radius-pill); font-weight:700; font-size:0.85rem;">
            Checkout &rarr;
        </div>
    </a>

    <!-- Toast Notification -->
    <div id="toastNotification" class="toast-notification">
        <span style="color:#fbbf24; font-size:1.2rem;">✓</span>
        <span id="toastMessage">Item added to your bakery cart!</span>
    </div>

    <footer>
        <div class="footer-inner">
            <div>
                <h3 style="font-family:var(--font-heading); color:#ffffff; font-size:1.4rem; margin-bottom:8px;">L'Atelier Gourmet Bakery</h3>
                <p style="font-size:0.9rem; max-width:400px; color:#a8a29e;">
                    Artisanal European bakery baking fresh sourdough, Viennoiserie, specialty cakes, and gourmet quiches every morning.
                </p>
            </div>
            <div style="display:flex; gap:35px; flex-wrap:wrap; font-size:0.9rem;">
                <div>
                    <h4 style="color:#ffffff; margin-bottom:10px; font-size:0.95rem;">Baking Hours</h4>
                    <p>Mon - Sat: 7:00 AM - 10:00 PM</p>
                    <p>Sunday: 8:00 AM - 9:00 PM</p>
                </div>
                <div>
                    <h4 style="color:#ffffff; margin-bottom:10px; font-size:0.95rem;">Customer Care</h4>
                    <p>Express Same-Day Delivery</p>
                    <p>Custom Birthday & Wedding Cakes</p>
                </div>
            </div>
        </div>
        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery & Boulangerie. All rights reserved.
        </div>
    </footer>

    <script>
        function adjustQty(btn, delta) {
            const input = btn.parentElement.querySelector('.qty-input');
            let val = parseInt(input.value) || 1;
            val = Math.max(1, Math.min(99, val + delta));
            input.value = val;
        }

        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const msgEl = document.getElementById('toastMessage');
            msgEl.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function handleAddToCart(e, form) {
            e.preventDefault();
            const formData = new FormData(form);
            const btn = form.querySelector('.btn-add-cart');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>⏳</span> ...';
            btn.disabled = true;

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = '<span>✓</span> Added';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 1200);

                if (data.success) {
                    showToast(data.message || 'Item added to your cart!');
                    const countEl = document.getElementById('nav-cart-count');
                    if (countEl) countEl.textContent = data.cart_count;

                    const floatBar = document.getElementById('floatingCart');
                    if (floatBar) {
                        floatBar.style.display = 'flex';
                        const summary = document.getElementById('floating-cart-summary');
                        if (summary) summary.textContent = `${data.cart_count} item(s) in cart`;
                    }
                }
            })
            .catch(err => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                form.submit();
            });
        }
    </script>
</body>
</html>
