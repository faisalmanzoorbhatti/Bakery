<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$conn = getDbConnection();

$customer_id = $_SESSION['customer_id'];
$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_name'])) {
        $name = $_POST['name'];
        $stmt = $conn->prepare("UPDATE customers SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $customer_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['change_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $customer_id);
        $stmt->execute();
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT name, email FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Settings</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>Account Settings</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        Name: <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required><br>
        <button type="submit" name="update_name">Update Name</button>
    </form>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        New Password: <input type="password" name="new_password" required><br>
        <button type="submit" name="change_password">Change Password</button>
    </form>
    <a href="logout.php">Logout</a>
    <a href="products.php">Back to Products</a>
</body>
</html>
