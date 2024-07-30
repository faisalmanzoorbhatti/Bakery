<?php
session_start();

require_once 'config.php';

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
        echo "Invalid token or token expired.";
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['reset_token'])) {
    $token = $_SESSION['reset_token'];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        $conn->close();
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt_update = $conn->prepare("UPDATE customers SET password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt_update->bind_param("ss", $hashed_password, $token);

    if ($stmt_update->execute()) {
        unset($_SESSION['reset_token']);
        $_SESSION['password_reset_success'] = true;
        $stmt_update->close();
        $conn->close();
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating record: " . htmlspecialchars($conn->error);
        $stmt_update->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>Reset Password</h2>
    <?php if (isset($_SESSION['reset_token'])): ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            New Password: <input type="password" name="password" required><br>
            Confirm Password: <input type="password" name="confirm_password" required><br>
            <button type="submit">Reset Password</button>
        </form>
    <?php else: ?>
        <p>Invalid or expired token.</p>
    <?php endif; ?>
</body>
</html>
