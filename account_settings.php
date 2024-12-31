<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';
$conn = getDbConnection();

$customer_id = (int)$_SESSION['customer_id'];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_name'])) {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $phone, $customer_id);
            if ($stmt->execute()) {
                $_SESSION['customer_name'] = $name;
                $message = "Your profile information has been updated successfully.";
            } else {
                $error = "Failed to update profile.";
            }
            $stmt->close();
        }
    }

    if (isset($_POST['change_password'])) {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $customer_id);
            if ($stmt->execute()) {
                $message = "Password changed successfully.";
            } else {
                $error = "Failed to update password.";
            }
            $stmt->close();
        }
    }
}

// Fetch current user details
$stmt = $conn->prepare("SELECT name, email, phone, role FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $role);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - L'Atelier Gourmet Bakery</title>
    <link rel="stylesheet" href="style.css">
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
            <a href="view_cart.php">Cart</a>
            <a href="account_settings.php" class="active">Settings</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 800px;">
        <div class="section-header" style="margin-bottom:28px;">
            <div>
                <h1 class="section-title">Account Profile Settings</h1>
                <p class="section-subtitle">Manage your personal credentials, contact phone, and security</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="form-box" style="margin-bottom: 25px;">
            <h3 style="font-family:var(--font-heading); font-size:1.3rem; color:var(--espresso); margin-bottom:18px;">
                👤 Personal Profile
            </h3>
            <form action="account_settings.php" method="POST">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="name">Display Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number (for SMS Tracking)</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="0300-1234567">
                    </div>
                </div>

                <div class="form-group">
                    <label>Registered Email Address</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($email ?? ''); ?>" disabled style="background:var(--surface-alt); color:var(--muted); cursor:not-allowed;">
                </div>

                <div class="form-group">
                    <label>Account Role</label>
                    <div>
                        <span class="card-badge gold" style="position:static; display:inline-block;">
                            <?php echo strtoupper(htmlspecialchars($role ?? 'CUSTOMER')); ?>
                        </span>
                    </div>
                </div>

                <button type="submit" name="update_name" class="btn btn-primary" style="padding:12px 24px; font-size:0.95rem;">
                    Save Profile Changes
                </button>
            </form>
        </div>

        <div class="form-box">
            <h3 style="font-family:var(--font-heading); font-size:1.3rem; color:var(--espresso); margin-bottom:18px;">
                🔒 Security & Password Update
            </h3>
            <form action="account_settings.php" method="POST">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" name="change_password" class="btn btn-secondary" style="padding:12px 24px; font-size:0.95rem;">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery & Boulangerie &bull; All rights reserved.
        </div>
    </footer>
</body>
</html>
