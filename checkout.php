<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];
require_once 'config.php';
$conn = getDbConnection();

// Fetch customer info
$cust_stmt = $conn->prepare("SELECT name, email, phone FROM customers WHERE id = ?");
$cust_stmt->bind_param("i", $customer_id);
$cust_stmt->execute();
$customer = $cust_stmt->get_result()->fetch_assoc();
$cust_stmt->close();

// Fetch cart items
$stmt = $conn->prepare("SELECT products.name, products.price, products.image_path, categories.name as category_name, cart.quantity, (products.price * cart.quantity) as subtotal 
                        FROM cart
                        JOIN products ON cart.product_id = products.id
                        LEFT JOIN categories ON products.category_id = categories.id
                        WHERE cart.customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$subtotal = 0;
$total_qty = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $subtotal += $row['subtotal'];
    $total_qty += (int)$row['quantity'];
}
$stmt->close();
$conn->close();

if ($subtotal <= 0) {
    header("Location: view_cart.php");
    exit();
}

// Calculations
$discount_rate = $_SESSION['promo_discount'] ?? 0.0;
$discount_amount = $subtotal * $discount_rate;
$free_ship_by_coupon = $_SESSION['promo_free_ship'] ?? false;
$is_free_shipping = ($subtotal >= 35.00) || $free_ship_by_coupon;
$delivery_fee = ($subtotal > 0 && !$is_free_shipping) ? 3.99 : 0.00;
$tax = ($subtotal - $discount_amount) * 0.05;
$final_total = max(0, ($subtotal - $discount_amount + $delivery_fee + $tax));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout & Delivery - L'Atelier Gourmet Bakery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.95fr;
            gap: 30px;
            align-items: start;
        }
        .gateway-card {
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 12px;
            background: #ffffff;
        }
        .gateway-card:hover {
            border-color: var(--primary);
            background: var(--surface-warm);
        }
        .gateway-card input[type="radio"] {
            accent-color: var(--primary);
            width: 18px;
            height: 18px;
        }
        @media (max-width: 900px) {
            .checkout-grid {
                grid-template-columns: 1fr;
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
            <a href="view_cart.php">Cart (<?php echo $total_qty; ?>)</a>
            <a href="account_settings.php">Settings</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div style="margin-bottom: 20px;">
            <a href="view_cart.php" style="color:var(--muted); text-decoration:none; font-size:0.92rem; font-weight:600;">
                &larr; Back to Cart Review
            </a>
        </div>

        <div class="section-header" style="margin-bottom:28px;">
            <div>
                <h1 class="section-title">Delivery & Checkout</h1>
                <p class="section-subtitle">Provide your delivery address and choose your preferred payment method</p>
            </div>
        </div>

        <form action="process_payment.php" method="POST">
            <div class="checkout-grid">
                <!-- Left: Delivery Details & Payment -->
                <div>
                    <!-- Delivery Address Box -->
                    <div class="form-box" style="margin-bottom:24px;">
                        <h3 style="font-family:var(--font-heading); font-size:1.3rem; color:var(--espresso); margin-bottom:18px;">
                            📍 Delivery Destination
                        </h3>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="form-group">
                                <label for="cust_name">Recipient Name</label>
                                <input type="text" id="cust_name" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="cust_phone">Contact Phone (for SMS Updates)</label>
                                <input type="text" id="cust_phone" name="phone" class="form-control" placeholder="0300-1234567" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Delivery Street Address</label>
                            <input type="text" id="address" name="address" class="form-control" placeholder="House / Flat #, Street, Block, Area, City" value="Baker's Lane, Gulberg III, Lahore" required>
                        </div>

                        <div class="form-group">
                            <label for="delivery_slot">Preferred Delivery Slot</label>
                            <select id="delivery_slot" name="delivery_slot" class="form-control">
                                <option value="Immediate Express (45-60 mins)">⚡ Immediate Express (45 - 60 mins)</option>
                                <option value="Morning Fresh: 9:00 AM - 12:00 PM">🌅 Morning Batch: 9:00 AM - 12:00 PM</option>
                                <option value="Afternoon Tea: 1:00 PM - 5:00 PM">☀️ Afternoon Tea: 1:00 PM - 5:00 PM</option>
                                <option value="Evening Hearth: 6:00 PM - 9:00 PM">🌙 Evening Hearth: 6:00 PM - 9:00 PM</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label for="special_instructions">Special Baker's Note / Message on Cake (Optional)</label>
                            <textarea id="special_instructions" name="special_instructions" rows="2" class="form-control" placeholder="e.g. Please write 'Happy Anniversary' or leave at door step..."></textarea>
                        </div>
                    </div>

                    <!-- Payment Method Box -->
                    <div class="form-box">
                        <h3 style="font-family:var(--font-heading); font-size:1.3rem; color:var(--espresso); margin-bottom:18px;">
                            💳 Payment Method
                        </h3>

                        <label class="gateway-card">
                            <input type="radio" name="payment_method" value="JazzCash" checked>
                            <div style="font-size:1.4rem;">📱</div>
                            <div>
                                <strong style="color:var(--espresso); font-size:0.95rem;">JazzCash Mobile Wallet</strong><br>
                                <span style="font-size:0.82rem; color:var(--muted);">Instant mobile account transfer via OTP</span>
                            </div>
                        </label>

                        <label class="gateway-card">
                            <input type="radio" name="payment_method" value="EasyPaisa">
                            <div style="font-size:1.4rem;">💚</div>
                            <div>
                                <strong style="color:var(--espresso); font-size:0.95rem;">EasyPaisa Mobile Account</strong><br>
                                <span style="font-size:0.82rem; color:var(--muted);">Direct one-click EasyPaisa authorization</span>
                            </div>
                        </label>

                        <label class="gateway-card">
                            <input type="radio" name="payment_method" value="Cash on Delivery">
                            <div style="font-size:1.4rem;">💵</div>
                            <div>
                                <strong style="color:var(--espresso); font-size:0.95rem;">Cash on Delivery (COD)</strong><br>
                                <span style="font-size:0.82rem; color:var(--muted);">Pay cash when rider delivers warm bakery box to your doorstep</span>
                            </div>
                        </label>

                        <label class="gateway-card">
                            <input type="radio" name="payment_method" value="Credit/Debit Card">
                            <div style="font-size:1.4rem;">💳</div>
                            <div>
                                <strong style="color:var(--espresso); font-size:0.95rem;">Credit / Debit Card (Visa, Mastercard, PayPak)</strong><br>
                                <span style="font-size:0.82rem; color:var(--muted);">Secure 256-bit encrypted card gateway</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Right: Order Summary Sidebar -->
                <div class="form-box" style="position:sticky; top:90px;">
                    <h3 style="font-family:var(--font-heading); font-size:1.3rem; color:var(--espresso); margin-bottom:18px;">
                        Order Items (<?php echo $total_qty; ?>)
                    </h3>

                    <div style="max-height: 280px; overflow-y: auto; padding-right: 6px; margin-bottom: 18px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                        <?php foreach ($items as $item): 
                            $img = !empty($item['image_path']) ? $item['image_path'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=200&q=80';
                        ?>
                            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                                <img src="<?php echo htmlspecialchars($img); ?>" style="width:48px; height:48px; border-radius:8px; object-fit:cover; flex-shrink:0;">
                                <div style="flex:1; font-size:0.88rem;">
                                    <strong style="color:var(--espresso);"><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                    <span style="color:var(--muted);"><?php echo (int)$item['quantity']; ?> &times; $<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                                <div style="font-weight:700; color:var(--espresso); font-size:0.95rem;">
                                    $<?php echo number_format($item['subtotal'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.92rem;">
                        <span style="color:var(--muted);">Subtotal:</span>
                        <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                    </div>

                    <?php if ($discount_amount > 0): ?>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.92rem; color:var(--success);">
                            <span>Discount (Coupon):</span>
                            <strong>-$<?php echo number_format($discount_amount, 2); ?></strong>
                        </div>
                    <?php endif; ?>

                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.92rem;">
                        <span style="color:var(--muted);">Express Delivery:</span>
                        <span>
                            <?php if ($delivery_fee == 0): ?>
                                <strong style="color:var(--success);">FREE</strong>
                            <?php else: ?>
                                $<?php echo number_format($delivery_fee, 2); ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-bottom:16px; font-size:0.92rem;">
                        <span style="color:var(--muted);">Bakery Tax (5%):</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding-top:14px; border-top:1.5px dashed var(--border); margin-bottom:22px;">
                        <span style="font-size:1.1rem; font-weight:700; color:var(--espresso);">Total Due:</span>
                        <span style="font-size:1.6rem; font-weight:800; color:var(--primary);">$<?php echo number_format($final_total, 2); ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; padding:16px; font-size:1.05rem;">
                        Confirm & Place Bakery Order ($<?php echo number_format($final_total, 2); ?>) &rarr;
                    </button>

                    <p style="text-align:center; font-size:0.8rem; color:var(--muted); margin-top:12px;">
                        By confirming, you agree to temperature-controlled doorstep delivery.
                    </p>
                </div>
            </div>
        </form>
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
