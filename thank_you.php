<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.html");
    exit();
}

echo "<!DOCTYPE html>
<html>
<head>
    <br>
    <br>
    <br><title>Thank You</title>
    <link rel='stylesheet' type='text/css' href='style.css'>
</head>
<body>
    <div class='top-right'>
        <a href='account_settings.php'>Account Settings</a> |
        <a href='logout.php'>Logout</a>
    </div>
    <div class='container'>
        <h2>Thank You!</h2>
        <p>Payment Successful! Your order has been placed successfully. You will receive a confirmation message shortly.</p>
    </div>
</body>
</html>";
?>
