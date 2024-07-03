<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.html");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];

require_once 'config.php';

$conn = getDbConnection();

$stmt = $conn->prepare("SELECT SUM(products.price * cart.quantity) AS total FROM cart
                        JOIN products ON cart.product_id = products.id
                        WHERE cart.customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_amount = $row['total'] ?? 0;
$stmt->close();

echo "<!DOCTYPE html>
<html>
<head>
    <br>
    <br>
    <br><title>Checkout</title>
    <link rel='stylesheet' type='text/css' href='style.css'>
</head>
<body>
    <div class='top-right'>
        <a href='account_settings.php'>Account Settings</a> |
        <a href='logout.php'>Logout</a>
    </div>
    <div class='container'>
        <h2>Checkout</h2>
        <p>Total Amount: $" . $total_amount . "</p>";

echo "<form action='process_payment.php' method='post'>
        Payment Method:
        <select name='payment_method'>
            <option value='JazzCash'>JazzCash</option>
            <option value='EasyPaisa'>EasyPaisa</option>
        </select><br>
        <button type='submit' ". ($total_amount == 0 ? 'disabled' : '') .">Pay</button>
      </form>";

echo "    </div>
</body>
</html>";

$conn->close();
?>
