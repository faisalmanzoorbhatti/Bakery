<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Please sign in first.']);
        exit();
    }
    header("Location: index.php");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];
require_once 'config.php';
$conn = getDbConnection();

$is_ajax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

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

// Calculate total cart items
$count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE customer_id = ?");
$count_stmt->bind_param("i", $customer_id);
$count_stmt->execute();
$count_res = $count_stmt->get_result();
$total_cart = (int)($count_res->fetch_assoc()['total'] ?? 0);
$count_stmt->close();
$conn->close();

if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'cart_count' => $total_cart, 'message' => 'Item added to your cart!']);
    exit();
}

if (isset($_POST['redirect_to_cart'])) {
    header("Location: view_cart.php");
} else {
    $referer = $_SERVER['HTTP_REFERER'] ?? 'products.php';
    header("Location: " . $referer);
}
exit();
