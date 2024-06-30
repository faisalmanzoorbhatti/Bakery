<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.html");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];

require_once 'config.php';

$conn = getDbConnection();

$stmt = $conn->prepare("SELECT products.name, products.price, cart.quantity FROM cart
                        JOIN products ON cart.product_id = products.id
                        WHERE cart.customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<!DOCTYPE html>
<html>
<head>
   <br>
   <br>
   <br> <title>Your Cart</title>
    <link rel='stylesheet' type='text/css' href='style.css'>
</head>
<body>
    <div class='top-right'>
        <a href='account_settings.php'>Account Settings</a> |
        <a href='logout.php'>Logout</a>
    </div>
    <div class='container'>
        <h2>Your Cart</h2>";

$total = 0;
while ($row = $result->fetch_assoc()) {
    $total += $row['price'] * $row['quantity'];
    echo "<p>" . $row['name'] . " - $" . $row['price'] . " x " . $row['quantity'] . "</p>";
}
echo "<p>Total: $" . $total . "</p>";

$button_disabled = ($total == 0) ? "disabled" : "";

echo "<form action='checkout.php' method='post'>
        <button type='submit' $button_disabled>Proceed to Checkout</button>
      </form>";

echo "    </div>
</body>
</html>";

$conn->close();
?>
