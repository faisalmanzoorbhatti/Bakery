<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.html");
    exit();
}

$customer_id = $_SESSION['customer_id'];

require_once 'config.php';

$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    
    $stmt = $conn->prepare("SELECT * FROM cart WHERE customer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $customer_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE customer_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $customer_id, $product_id);
    } else {
        
        $stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $customer_id, $product_id, $quantity);
    }

    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: view_cart.php");
exit();
?>
