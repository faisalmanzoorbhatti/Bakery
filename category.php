<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.html");
    exit();
}

$category_id = (int)($_GET['id'] ?? 0);

require_once 'config.php';

$conn = getDbConnection();

$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<!DOCTYPE html>
<html>
<head>
   <br>
   <br>
   <br> <title>Products</title>
    <link rel='stylesheet' type='text/css' href='style.css'>
</head>
<body>
    <div class='top-right'>
        <a href='account_settings.php'>Account Settings</a> |
        <a href='logout.php'>Logout</a>
    </div>
    <div class='container'>
        <h2>Products</h2>";

while ($row = $result->fetch_assoc()) {
    echo "<div>
            <h3>" . $row['name'] . "</h3>
            <p>" . $row['description'] . "</p>
            <p>$" . $row['price'] . "</p>
            <form action='add_to_cart.php' method='post'>
                <input type='hidden' name='product_id' value='" . $row['id'] . "'>
                Quantity: <input type='number' name='quantity' value='1' min='1'>
                <button type='submit'>Add to Cart</button>
            </form>
          </div>";
}

echo "    </div>
</body>
</html>";

$conn->close();
?>
