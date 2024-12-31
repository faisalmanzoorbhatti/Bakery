<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];
require_once 'config.php';
$conn = getDbConnection();

// Fetch categories
$cats_res = $conn->query("SELECT categories.*, COUNT(products.id) as product_count 
                          FROM categories 
                          LEFT JOIN products ON categories.id = products.category_id 
                          GROUP BY categories.id 
                          ORDER BY categories.id ASC");
$categories = [];
$total_products_count = 0;
while ($c = $cats_res->fetch_assoc()) {
    $categories[] = $c;
    $total_products_count += (int)$c['product_count'];
}

// Fetch all products
$prods_res = $conn->query("SELECT products.*, categories.name as category_name 
                           FROM products 
                           LEFT JOIN categories ON products.category_id = categories.id 
                           ORDER BY products.category_id ASC, products.is_featured DESC, products.id ASC");
$products = [];
while ($p = $prods_res->fetch_assoc()) {
    $products[] = $p;
}

// Fetch cart count & subtotal
$cart_stmt = $conn->prepare("SELECT SUM(cart.quantity) as total_qty, SUM(cart.quantity * products.price) as cart_total 
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
    <title>L'Atelier Gourmet Bakery - Artisanal Oven-Fresh Delights</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
        .toast-icon {
            color: #fbbf24;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <!-- Top Announcement Bar -->
    <div class="top-announcement">
        <span>🥖 <strong>Fresh Morning Bakes:</strong> Batch #1 freshly pulled from hearth ovens</span>
        <span class="badge-pill">Free Express Delivery on orders over $35</span>
        <span>📞 Order Hotline: 1-800-BAY-BAKE</span>
    </div>

    <!-- Luxury Sticky Navbar -->
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

    <!-- Main Container -->
    <div class="container">
        <!-- Hero Showcase Banner -->
        <div class="bakery-hero">
            <div class="hero-content">
                <div class="hero-pill">
                    <span>✨</span> Master Pâtisserie & Sourdough Boulangerie
                </div>
                <h1 class="hero-title">
                    Where Craft Meets Passion: <span>Artisanal Bakery</span>
                </h1>
                <p class="hero-desc">
                    Slow-fermented French sourdoughs, flaky butter croissants, Belgian chocolate cakes, and gourmet quiches. Freshly baked every day with organic heritage ingredients.
                </p>
                <div class="hero-perks">
                    <div class="hero-perk-item">
                        <span>🌾</span> 100% Organic Flours
                    </div>
                    <div class="hero-perk-item">
                        <span>🧈</span> Normandy Cultured Butter
                    </div>
                    <div class="hero-perk-item">
                        <span>⏱️</span> 48-Hour Cold Proof
                    </div>
                    <div class="hero-perk-item">
                        <span>🚚</span> Same-Day Oven Fresh Delivery
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar Section -->
        <div class="filter-search-section">
            <div class="search-input-wrapper">
                <span class="search-icon-svg">🔍</span>
                <input type="text" id="productSearch" class="search-input" placeholder="Search by cake name, sourdough, croissants, brownies, or flavors...">
            </div>

            <!-- Category Filter Tabs -->
            <div class="category-pills-scroll">
                <button class="cat-pill active" data-category="all">
                    <span>🍽️ All Delights</span>
                    <span class="count"><?php echo $total_products_count; ?></span>
                </button>
                <?php foreach ($categories as $cat): ?>
                    <button class="cat-pill" data-category="<?php echo $cat['id']; ?>">
                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                        <span class="count"><?php echo (int)$cat['product_count']; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Section Header -->
        <div class="section-header">
            <div>
                <h2 id="catalogHeading" class="section-title">All Handcrafted Bakery Delights</h2>
                <p id="catalogSubtitle" class="section-subtitle">Showing all 42 freshly baked gourmet creations</p>
            </div>
            <div style="font-size:0.92rem; color:var(--muted); font-weight:500;">
                <span id="visibleCount"><?php echo count($products); ?></span> items available
            </div>
        </div>

        <!-- Product Grid -->
        <div id="productGrid" class="product-grid">
            <?php foreach ($products as $prod): 
                $pid = $prod['id'];
                $cid = $prod['category_id'];
                $badge = $prod['badge'] ?? '';
                $image_url = !empty($prod['image_path']) ? $prod['image_path'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80';
            ?>
                <div class="product-card" data-category-id="<?php echo $cid; ?>" data-title="<?php echo strtolower(htmlspecialchars($prod['name'])); ?>" data-desc="<?php echo strtolower(htmlspecialchars($prod['description'])); ?>">
                    <div class="card-img-wrap">
                        <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="card-img" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80';">
                        <?php if (!empty($badge)): ?>
                            <div class="card-badge gold"><?php echo htmlspecialchars($badge); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="card-category-tag"><?php echo htmlspecialchars($prod['category_name'] ?? 'Bakery Item'); ?></div>
                        <h3 class="card-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                        <p class="card-desc"><?php echo htmlspecialchars($prod['description']); ?></p>
                        
                        <div class="card-footer">
                            <div class="product-price">
                                $<?php echo number_format($prod['price'], 2); ?>
                            </div>
                            
                            <!-- Add to Cart Form -->
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

        <div id="noProductsFound" style="display:none; text-align:center; padding:60px 20px; background:#ffffff; border-radius:var(--radius-lg); border:1px solid var(--border); margin-bottom:40px;">
            <div style="font-size:3rem; margin-bottom:12px;">🔍</div>
            <h3 style="font-family:var(--font-heading); font-size:1.6rem; color:var(--espresso); margin-bottom:8px;">No Bakery Items Match Your Search</h3>
            <p style="color:var(--muted); margin-bottom:20px;">Try typing a different bakery keyword or reset category filters.</p>
            <button onclick="resetFilters()" class="btn btn-secondary">Clear Filters & View All</button>
        </div>
    </div>

    <!-- Floating Cart Bar (Sticky when cart > 0) -->
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

    <!-- Toast Notification Element -->
    <div id="toastNotification" class="toast-notification">
        <span class="toast-icon">✓</span>
        <span id="toastMessage">Item added to your bakery cart!</span>
    </div>

    <!-- Luxury Footer -->
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
            &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery & Boulangerie &bull; Architecture & Design by <strong style="color:#fbbf24;">Faisal Manzoor Bhatti</strong>. All rights reserved.
        </div>
    </footer>

    <!-- Interactive Search & Filter Script -->
    <script>
        const searchInput = document.getElementById('productSearch');
        const categoryPills = document.querySelectorAll('.cat-pill');
        const productCards = document.querySelectorAll('.product-card');
        const visibleCountEl = document.getElementById('visibleCount');
        const noProductsEl = document.getElementById('noProductsFound');
        const catalogHeading = document.getElementById('catalogHeading');
        const catalogSubtitle = document.getElementById('catalogSubtitle');
        
        let currentCategory = 'all';

        function filterProducts() {
            const query = searchInput.value.trim().toLowerCase();
            let visible = 0;

            productCards.forEach(card => {
                const cardCat = card.getAttribute('data-category-id');
                const cardTitle = card.getAttribute('data-title');
                const cardDesc = card.getAttribute('data-desc');

                const matchesCat = (currentCategory === 'all' || cardCat === currentCategory);
                const matchesSearch = (!query || cardTitle.includes(query) || cardDesc.includes(query));

                if (matchesCat && matchesSearch) {
                    card.style.display = 'flex';
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });

            visibleCountEl.textContent = visible;
            noProductsEl.style.display = visible === 0 ? 'block' : 'none';
        }

        searchInput.addEventListener('input', filterProducts);

        categoryPills.forEach(pill => {
            pill.addEventListener('click', function() {
                categoryPills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                currentCategory = this.getAttribute('data-category');
                
                const catName = this.querySelector('span:first-child').textContent.replace(/[^\w\s&]/gi, '').trim();
                if (currentCategory === 'all') {
                    catalogHeading.textContent = "All Handcrafted Bakery Delights";
                    catalogSubtitle.textContent = "Showing all 34 freshly baked gourmet creations";
                } else {
                    catalogHeading.textContent = catName;
                    catalogSubtitle.textContent = `Freshly baked selections in ${catName}`;
                }

                filterProducts();
            });
        });

        function resetFilters() {
            searchInput.value = '';
            categoryPills.forEach(p => p.classList.remove('active'));
            document.querySelector('.cat-pill[data-category="all"]').classList.add('active');
            currentCategory = 'all';
            catalogHeading.textContent = "All Handcrafted Bakery Delights";
            catalogSubtitle.textContent = "Showing all 42 freshly baked gourmet creations";
            filterProducts();
        }

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
                form.submit(); // fallback to standard submit
            });
        }
    </script>
</body>
</html>
