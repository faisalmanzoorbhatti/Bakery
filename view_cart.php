<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];
require_once 'config.php';
$conn = getDbConnection();

$msg = "";
$error = "";

// Handle item removal
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    $del_stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ? AND product_id = ?");
    $del_stmt->bind_param("ii", $customer_id, $remove_id);
    $del_stmt->execute();
    $del_stmt->close();
    header("Location: view_cart.php");
    exit();
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);
    if ($pid > 0) {
        if ($qty <= 0) {
            $del_stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ? AND product_id = ?");
            $del_stmt->bind_param("ii", $customer_id, $pid);
            $del_stmt->execute();
            $del_stmt->close();
        } else {
            $up_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE customer_id = ? AND product_id = ?");
            $up_stmt->bind_param("iii", $qty, $customer_id, $pid);
            $up_stmt->execute();
            $up_stmt->close();
        }
    }
    header("Location: view_cart.php");
    exit();
}

// Handle Promo Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
    $code = strtoupper(trim($_POST['promo_code'] ?? ''));
    if ($code === 'BAKERY10') {
        $_SESSION['promo_discount'] = 0.10;
        $_SESSION['promo_code'] = 'BAKERY10';
        $msg = "Coupon BAKERY10 applied! 10% discount activated.";
    } elseif ($code === 'FREESHIP') {
        $_SESSION['promo_free_ship'] = true;
        $_SESSION['promo_code'] = 'FREESHIP';
        $msg = "Free shipping coupon applied!";
    } else {
        $error = "Invalid promo code. Try BAKERY10 for 10% off!";
    }
}

// Fetch cart items
$stmt = $conn->prepare("SELECT products.id as product_id, products.name, products.price, products.image_path, categories.name as category_name, cart.quantity 
                        FROM cart
                        JOIN products ON cart.product_id = products.id
                        LEFT JOIN categories ON products.category_id = categories.id
                        WHERE cart.customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$subtotal = 0;
$total_quantity = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $subtotal += $row['price'] * $row['quantity'];
    $total_quantity += (int)$row['quantity'];
}
$stmt->close();
$conn->close();

// Calculations
$discount_rate = $_SESSION['promo_discount'] ?? 0.0;
$discount_amount = $subtotal * $discount_rate;
$free_ship_by_coupon = $_SESSION['promo_free_ship'] ?? false;
$free_ship_threshold = 35.00;
$is_free_shipping = ($subtotal >= $free_ship_threshold) || $free_ship_by_coupon;
$delivery_fee = ($subtotal > 0 && !$is_free_shipping) ? 3.99 : 0.00;
$tax = ($subtotal - $discount_amount) * 0.05; // 5% bakery sales tax
$final_total = max(0, ($subtotal - $discount_amount + $delivery_fee + $tax));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - L'Atelier Gourmet Bakery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            align-items: start;
        }
        .cart-item-card {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .cart-item-card:hover {
            border-color: rgba(217, 119, 6, 0.3);
            box-shadow: var(--shadow-md);
        }
        .cart-item-thumb {
            width: 100px;
            height: 100px;
            border-radius: var(--radius-md);
            object-fit: cover;
            background: #e7e5e4;
            flex-shrink: 0;
        }
        .free-shipping-progress {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: var(--radius-md);
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #92400e;
        }
        .progress-bar-wrap {
            height: 8px;
            background: #fde68a;
            border-radius: 4px;
            margin-top: 8px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b, #d97706);
            border-radius: 4px;
            transition: width 0.4s ease;
        }
        .summary-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 26px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 90px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
            color: var(--body-text);
        }
        .summary-row.total {
            border-top: 1.5px dashed var(--border);
            padding-top: 16px;
            margin-top: 16px;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--espresso);
        }
        @media (max-width: 900px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            .cart-item-card {
                flex-wrap: wrap;
            }
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
            <a href="my_orders.php">My Orders</a>
            <a href="account_settings.php">Settings</a>
            <a href="view_cart.php" class="cart-nav-badge">
                🛒 Cart <span class="cart-pill-count"><?php echo $total_quantity; ?></span>
            </a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Page Title -->
        <div class="section-header" style="margin-bottom:30px;">
            <div>
                <h1 class="section-title">Your Bakery Basket</h1>
                <p class="section-subtitle">Review fresh bakes before proceeding to secure checkout</p>
            </div>
            <a href="products.php" class="btn btn-secondary" style="padding:8px 18px; font-size:0.9rem;">
                &larr; Continue Browsing Menu
            </a>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="form-box" style="text-align:center; padding:70px 20px; max-width:650px; margin:40px auto;">
                <div style="font-size:3.8rem; margin-bottom:15px;">🧺</div>
                <h2 style="font-family:var(--font-heading); color:var(--espresso); margin-bottom:10px;">Your Cart is Empty</h2>
                <p style="color:var(--muted); margin-bottom:30px; font-size:1.05rem;">
                    You haven't selected any treats yet. Fresh loaves, hot croissants, and decadent cakes are ready for you.
                </p>
                <a href="products.php" class="btn btn-primary" style="padding:14px 34px; font-size:1.05rem;">
                    Browse Fresh Bakery Menu &rarr;
                </a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <!-- Left: Items List -->
                <div>
                    <!-- Free Delivery Progress Bar -->
                    <div class="free-shipping-progress">
                        <?php if ($is_free_shipping): ?>
                            <strong>🎉 Congratulations!</strong> You have unlocked <strong>FREE Express Delivery</strong>!
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width: 100%;"></div>
                            </div>
                        <?php else: 
                            $remaining = $free_ship_threshold - $subtotal;
                            $pct = min(100, round(($subtotal / $free_ship_threshold) * 100));
                        ?>
                            Add <strong>$<?php echo number_format($remaining, 2); ?></strong> more bakery items to unlock <strong>FREE Delivery</strong>!
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width: <?php echo $pct; ?>%;"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Cart Item Cards -->
                    <?php foreach ($items as $item): 
                        $item_subtotal = $item['price'] * $item['quantity'];
                        $img_src = !empty($item['image_path']) ? $item['image_path'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=300&q=80';
                    ?>
                        <div class="cart-item-card">
                            <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-thumb" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=300&q=80';">
                            <div style="flex:1;">
                                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--primary-dark); font-weight:700; letter-spacing:0.8px;">
                                    <?php echo htmlspecialchars($item['category_name'] ?? 'Bakery Item'); ?>
                                </div>
                                <h3 style="font-family:var(--font-heading); font-size:1.15rem; color:var(--espresso); margin-bottom:4px;">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </h3>
                                <div style="font-weight:700; color:var(--primary-dark); font-size:1.05rem;">
                                    $<?php echo number_format($item['price'], 2); ?> each
                                </div>
                            </div>

                            <!-- Stepper Update Form -->
                            <form action="view_cart.php" method="POST" style="display:flex; align-items:center; gap:8px;">
                                <input type="hidden" name="update_qty" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <div class="qty-stepper">
                                    <button type="submit" name="quantity" value="<?php echo max(0, $item['quantity'] - 1); ?>" class="qty-btn">&minus;</button>
                                    <span class="qty-input" style="line-height:28px;"><?php echo (int)$item['quantity']; ?></span>
                                    <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="qty-btn">&plus;</button>
                                </div>
                            </form>

                            <!-- Subtotal & Delete -->
                            <div style="text-align:right; min-width:90px;">
                                <div style="font-size:1.2rem; font-weight:800; color:var(--espresso);">
                                    $<?php echo number_format($item_subtotal, 2); ?>
                                </div>
                                <a href="view_cart.php?remove=<?php echo $item['product_id']; ?>" onclick="return confirm('Remove this item from your cart?');" style="color:#ef4444; font-size:0.82rem; text-decoration:none; font-weight:600; display:inline-block; margin-top:4px;">
                                    Remove
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Right: Sticky Order Summary -->
                <div class="summary-card">
                    <h3 style="font-family:var(--font-heading); font-size:1.35rem; color:var(--espresso); margin-bottom:18px;">
                        Order Summary
                    </h3>

                    <div class="summary-row">
                        <span>Items Subtotal (<?php echo $total_quantity; ?>):</span>
                        <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                    </div>

                    <?php if ($discount_amount > 0): ?>
                        <div class="summary-row" style="color:var(--success);">
                            <span>Discount (10% Off):</span>
                            <strong>-$<?php echo number_format($discount_amount, 2); ?></strong>
                        </div>
                    <?php endif; ?>

                    <div class="summary-row">
                        <span>Oven Express Delivery:</span>
                        <span>
                            <?php if ($delivery_fee == 0): ?>
                                <strong style="color:var(--success);">FREE</strong>
                            <?php else: ?>
                                $<?php echo number_format($delivery_fee, 2); ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="summary-row">
                        <span>Estimated Tax (5%):</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>

                    <div class="summary-row total">
                        <span>Estimated Total:</span>
                        <span style="color:var(--primary);">$<?php echo number_format($final_total, 2); ?></span>
                    </div>

                    <!-- Promo Code Form -->
                    <form action="view_cart.php" method="POST" style="margin: 22px 0 15px;">
                        <input type="hidden" name="apply_promo" value="1">
                        <label style="font-size:0.82rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:6px;">
                            Promotional Coupon
                        </label>
                        <div style="display:flex; gap:8px;">
                            <input type="text" name="promo_code" placeholder="Try BAKERY10" value="<?php echo htmlspecialchars($_SESSION['promo_code'] ?? ''); ?>" class="form-control" style="padding:10px 14px; font-size:0.9rem; text-transform:uppercase;">
                            <button type="submit" class="btn btn-secondary" style="padding:10px 18px; font-size:0.88rem; white-space:nowrap;">
                                Apply
                            </button>
                        </div>
                    </form>

                    <!-- Checkout Button -->
                    <a href="checkout.php" class="btn btn-primary" style="width:100%; padding:15px; font-size:1.05rem; margin-top:10px;">
                        Proceed to Checkout &rarr;
                    </a>

                    <!-- Trust Perks -->
                    <div style="margin-top:20px; padding-top:18px; border-top:1px solid var(--border-light); font-size:0.82rem; color:var(--muted); display:flex; flex-direction:column; gap:8px;">
                        <div>🔒 256-Bit SSL Encrypted Checkout</div>
                        <div>🥖 Baked fresh & delivered temperature-controlled</div>
                        <div>⏱️ Same-day dispatch within 45-60 minutes</div>
                    </div>
                </div>
            </div>
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
</body>
</html>
