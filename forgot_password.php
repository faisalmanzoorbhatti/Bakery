<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

session_start();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        try {
            $conn = getDbConnection();
            $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $token = bin2hex(random_bytes(32));
                $stmt_update = $conn->prepare("UPDATE customers SET reset_token = ? WHERE email = ?");
                $stmt_update->bind_param("ss", $token, $email);

                if ($stmt_update->execute()) {
                    $reset_link = rtrim(APP_URL, '/') . "/reset_password.php?token=" . urlencode($token);

                    if (class_exists('PHPMailer\PHPMailer\PHPMailer') && !empty(SMTP_USER) && !empty(SMTP_PASS)) {
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = SMTP_HOST;
                            $mail->SMTPAuth   = true;
                            $mail->Username   = SMTP_USER;
                            $mail->Password   = SMTP_PASS;
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = SMTP_PORT;

                            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                            $mail->addAddress($email);

                            $mail->isHTML(true);
                            $mail->Subject = 'Password Reset - ' . SMTP_FROM_NAME;
                            $mail->Body    = "Hello,<br><br>Click the link below to reset your password:<br><br><a href='$reset_link'>$reset_link</a><br><br>If you did not request this, please ignore this email.";

                            $mail->send();
                            $message = 'A password reset link has been dispatched to your email address.';
                        } catch (Exception $e) {
                            $error = "Mailer Error: " . htmlspecialchars($mail->ErrorInfo);
                        }
                    } else {
                        // Demo mode message showing simulated link
                        $message = "Demo Reset Link Generated: <br><a href='$reset_link' style='word-break:break-all;'>$reset_link</a>";
                    }
                }
                $stmt_update->close();
            } else {
                $error = "No user found with this email address.";
            }
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Online Bay Bakery</title>
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
                <div class="hero-pill" style="margin-bottom:14px;">Account Security</div>
                <h1>Account Recovery Portal</h1>
                <p>Recover access to your artisanal bakery account and orders quickly and securely.</p>
            </div>

            <div style="font-size:0.85rem; color:#d6d3d1;">
                &copy; <?php echo date('Y'); ?> L'Atelier Gourmet Bakery. All rights reserved.
            </div>
        </div>
        <div class="auth-form-side">
            <div class="auth-card">
                <h2>Forgot Password</h2>
                <p class="subtitle">Enter your registered email address to receive a reset link</p>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="forgot_password.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Send Reset Link</button>
                </form>

                <div class="auth-card-footer">
                    Remember your password? <a href="index.php">Back to Sign In</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
