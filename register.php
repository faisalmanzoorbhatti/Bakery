<?php
session_start();

require_once 'config.php';

$conn = getDbConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $phone = trim($_POST['phone'] ?? '');

    $stmt = $conn->prepare("INSERT INTO customers (name, email, password, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $phone);

    if ($stmt->execute()) {
        $_SESSION['registration_success'] = true;
        $stmt->close();
        $conn->close();
        header("Location: login.php");
        exit(); 
    } else {
        $registration_error = "Error: " . htmlspecialchars($stmt->error);
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Registration</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>Customer Registration</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        Name: <input type="text" name="name" required><br>
        Email: <input type="email" name="email" required><br>
        Password: <input type="password" name="password" required><br>
        Phone: <input type="text" name="phone" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
