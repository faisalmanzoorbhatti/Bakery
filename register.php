<?php
session_start();
if (isset($_SESSION['customer_id'])) {
    header("Location: products.php");
    exit();
}

require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $conn = getDbConnection();

        // Check if email already exists
        $stmt_check = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "An account with this email already exists. Please sign in.";
            $stmt_check->close();
        } else {
            $stmt_check->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt_insert = $conn->prepare("INSERT INTO customers (name, email, password, phone) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $name, $email, $hashed_password, $phone);

            if ($stmt_insert->execute()) {
                $_SESSION['registration_success'] = "Account created successfully! You can now log in.";
                $stmt_insert->close();
                $conn->close();
                header("Location: index.php?registered=1");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
                $stmt_insert->close();
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Bay Bakery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-banner">
            <div class="auth-banner-content">
                <h1>Online Bay Bakery</h1>
                <p>Join us today to explore fresh handcrafted bakery items, artisanal breads, and custom confections.</p>
            </div>
        </div>
        <div class="auth-form-side">
            <div class="auth-card">
                <h2>Create Account</h2>
                <p class="subtitle">Enter your details to create a new customer account</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. John Doe" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Create a strong password" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="e.g. 03001234567" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Create Account</button>
                </form>

                <div class="auth-card-footer">
                    Already have an account? <a href="index.php">Sign In</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
