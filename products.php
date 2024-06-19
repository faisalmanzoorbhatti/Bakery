<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'config.php';

$conn = getDbConnection();

$sql = "SELECT * FROM categories";
$result = $conn->query($sql);

echo "<!DOCTYPE html>
<html>
<head>
   <br>
   <br>
   <br> <title>Product Categories</title>
    <link rel='stylesheet' type='text/css' href='style.css'>
</head>
<body>
    <div class='top-right'>
        <a href='account_settings.php'>Account Settings</a> |
        <a href='logout.php'>Logout</a>
    </div>
    <div class='container'>
        <h2>Product Categories</h2>";

while ($row = $result->fetch_assoc()) {
    echo "<a href='category.php?id=" . $row['id'] . "'>" . $row['name'] . "</a><br>";
}

echo "    </div>
</body>
</html>";

$conn->close();
?>
