<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

session_start();

$conn = getDbConnection();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');

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

            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
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
                    $message = 'Reset link has been sent to your email.';
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: " . htmlspecialchars($mail->ErrorInfo);
                }
            } else {
                $error = "PHPMailer is not installed. Please run 'composer install'.";
            }
        } else {
            $error = "Error updating reset token: " . htmlspecialchars($conn->error);
        }
        $stmt_update->close();
    } else {
        $error = "No user found with this email.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>Forgot Password</h2>
    <?php if (!empty($message)): ?>
        <p style="color: green; text-align: center;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        Email: <input type="email" name="email" required><br>
        <button type="submit">Reset Password</button>
    </form>
    <div style="text-align: center; margin-top: 15px;">
        <a href="index.php">Back to Login</a>
    </div>
</body>
</html>
