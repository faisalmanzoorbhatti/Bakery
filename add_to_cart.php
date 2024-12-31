<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];

require_once 'config.php';
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if ($product_id > 0) {
        $stmt = $conn->prepare("SELECT id FROM cart WHERE customer_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $customer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt_update = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE customer_id = ? AND product_id = ?");
            $stmt_update->bind_param("iii", $quantity, $customer_id, $product_id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("iii", $customer_id, $product_id, $quantity);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt->close();
    }
}

$conn->close();
header("Location: view_cart.php");
exit();
