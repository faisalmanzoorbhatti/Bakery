<?php
session_start();
require_once 'config.php';

$error = "";
$conn = getDbConnection();

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['token'])) {
    $token = trim($_GET['token']);

    $stmt = $conn->prepare("SELECT id FROM customers WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['reset_token'] = $token;
    } else {
        $error = "This password reset link is invalid or has expired.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['reset_token'])) {
    $token = $_SESSION['reset_token'];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt_update = $conn->prepare("UPDATE customers SET password = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt_update->bind_param("ss", $hashed_password, $token);

        if ($stmt_update->execute()) {
            unset($_SESSION['reset_token']);
            $stmt_update->close();
            $conn->close();
            header("Location: index.php?reset_success=1");
            exit();
        } else {
            $error = "Error updating password. Please try again.";
            $stmt_update->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Online Bay Bakery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-banner">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="brand-icon" style="background:#ffffff; color:var(--primary);">🥐</div>
                <span style="font-family:var(--font-heading); font-size:1.5rem; font-weight:700; color:#ffffff; letter-spacing:-0.3px;">L'Atelier Bakery</span>
            </div>
            
            <div class="auth-banner-content">
                <div class="hero-pill" style="margin-bottom:14px;">Password Reset</div>
                <h1>Choose a Secure Password</h1>
                <p>Create a secure new password for your customer account to continue ordering fresh bakes.</p>
            </div>

            <div style="font-size:0.85rem; color:#d6d3d1;">
                &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery. All rights reserved.
            </div>
        </div>
        <div class="auth-form-side">
            <div class="auth-card">
                <h2>Set New Password</h2>
                <p class="subtitle">Enter and confirm your new password below</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['reset_token'])): ?>
                    <form action="reset_password.php" method="POST">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Update Password</button>
                    </form>
                <?php else: ?>
                    <div style="margin-top: 20px;">
                        <a href="forgot_password.php" class="btn btn-primary">Request a New Reset Link</a>
                    </div>
                <?php endif; ?>

                <div class="auth-card-footer">
                    <a href="index.php">Back to Sign In</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
