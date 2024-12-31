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
        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE customers SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $customer_id);
            if ($stmt->execute()) {
                $_SESSION['customer_name'] = $name;
                $message = "Your name has been updated successfully.";
            } else {
                $error = "Failed to update name.";
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
$stmt = $conn->prepare("SELECT name, email, phone FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Online Bay Bakery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="products.php" class="brand-logo">
            <span>🥖</span> Online Bay Bakery
        </a>
        <div class="nav-links">
            <a href="products.php">Categories</a>
            <a href="view_cart.php">Cart</a>
            <a href="account_settings.php" class="active">Settings</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 600px;">
        <div class="page-header" style="text-align: left; margin-bottom: 25px;">
            <h1>Account Settings</h1>
            <p>Manage your profile name and password credentials</p>
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

        <div class="form-box" style="margin: 0 0 25px 0; max-width: 100%;">
            <h3 style="margin-bottom: 15px; font-size: 1.15rem; color: var(--dark);">Profile Information</h3>
            <form action="account_settings.php" method="POST">
                <div class="form-group">
                    <label for="name">Display Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address (Read-only)</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($email ?? ''); ?>" disabled style="background:var(--light-gray); color:var(--gray);">
                </div>
                <button type="submit" name="update_name" class="btn btn-primary" style="width: auto;">
                    Update Name
                </button>
            </form>
        </div>

        <div class="form-box" style="margin: 0; max-width: 100%;">
            <h3 style="margin-bottom: 15px; font-size: 1.15rem; color: var(--dark);">Change Password</h3>
            <form action="account_settings.php" method="POST">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary" style="width: auto;">
                    Change Password
                </button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Bay Bakery. Fresh Quality Baked Goods.</p>
    </footer>
</body>
</html>
