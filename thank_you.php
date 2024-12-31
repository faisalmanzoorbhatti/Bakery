<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}
$order_id = (int)($_GET['order_id'] ?? ($_SESSION['latest_order_id'] ?? 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed! - L'Atelier Gourmet Bakery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 35px 0;
            padding: 0 10px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 30px;
            right: 30px;
            height: 3px;
            background: var(--border);
            z-index: 1;
        }
        .timeline-step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        .timeline-dot {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid var(--border);
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--muted);
            transition: var(--transition);
        }
        .timeline-step.active .timeline-dot {
            border-color: var(--primary);
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 4px 12px var(--primary-glow);
        }
        .timeline-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--muted);
        }
        .timeline-step.active .timeline-label {
            color: var(--espresso);
            font-weight: 700;
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
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 760px; margin-top: 25px; margin-bottom: 50px;">
        <div class="form-box" style="text-align: center; padding: 45px 30px;">
            <div style="font-size: 3.8rem; margin-bottom: 12px;">🎉</div>
            <h1 style="font-family:var(--font-heading); font-size: 2.2rem; color: var(--espresso); margin-bottom: 8px;">
                Order Placed Successfully!
            </h1>
            <p style="color: var(--muted); font-size: 1.05rem; margin-bottom: 25px;">
                Thank you for choosing L'Atelier Gourmet Bakery. Your order <strong>#<?php echo $order_id > 0 ? $order_id : 'NEW'; ?></strong> has been sent to our ovens!
            </p>

            <!-- Order Timeline -->
            <div class="timeline">
                <div class="timeline-step active">
                    <div class="timeline-dot">✓</div>
                    <div class="timeline-label">Order Received</div>
                </div>
                <div class="timeline-step active">
                    <div class="timeline-dot">🔥</div>
                    <div class="timeline-label">In Oven / Prep</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-dot">🛵</div>
                    <div class="timeline-label">Out for Delivery</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-dot">🥖</div>
                    <div class="timeline-label">Warm at Door</div>
                </div>
            </div>

            <div class="alert alert-success" style="text-align: left; margin: 25px 0;">
                <strong>📱 Real-Time Tracking:</strong><br>
                An automated SMS confirmation was sent to your phone. Our riders will deliver your fresh bakery box within the selected time window.
            </div>

            <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap; margin-top:30px;">
                <a href="my_orders.php" class="btn btn-primary" style="padding:14px 28px; font-size:1rem;">
                    Track in My Orders &rarr;
                </a>
                <a href="products.php" class="btn btn-secondary" style="padding:14px 28px; font-size:1rem;">
                    Continue Browsing Bakery
                </a>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-inner">
            <div>
                <h3 style="font-family:var(--font-heading); color:#ffffff; font-size:1.4rem; margin-bottom:8px;">L'Atelier Gourmet Bakery</h3>
                <p style="font-size:0.9rem; max-width:400px; color:#a8a29e;">
                    Artisanal European bakery baking fresh sourdough, Viennoiserie, specialty cakes, and gourmet quiches every morning.
                </p>
            </div>
        </div>
        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery & Boulangerie. All rights reserved.
        </div>
    </footer>
</body>
</html>
