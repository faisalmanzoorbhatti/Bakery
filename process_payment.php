<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Twilio\Rest\Client;

$customer_id = (int)$_SESSION['customer_id'];
$payment_method = $_POST['payment_method'] ?? 'Cash';

$conn = getDbConnection();

// Fetch customer phone number
$stmt_cust = $conn->prepare("SELECT phone FROM customers WHERE id = ?");
$stmt_cust->bind_param("i", $customer_id);
$stmt_cust->execute();
$cust_res = $stmt_cust->get_result();
$customer_data = $cust_res->fetch_assoc();
$stmt_cust->close();

$customer_phone = !empty($customer_data['phone']) ? $customer_data['phone'] : TWILIO_ADMIN_PHONE;

// Calculate total cart amount using prepared statements
$stmt_cart = $conn->prepare("SELECT SUM(products.price * cart.quantity) AS total FROM cart
                             JOIN products ON cart.product_id = products.id
                             WHERE cart.customer_id = ?");
$stmt_cart->bind_param("i", $customer_id);
$stmt_cart->execute();
$cart_res = $stmt_cart->get_result();
$row = $cart_res->fetch_assoc();
$total_amount = $row['total'] ?? 0;
$stmt_cart->close();

if ($total_amount <= 0) {
    header("Location: view_cart.php");
    exit();
}

// Insert order
$stmt_order = $conn->prepare("INSERT INTO orders (customer_id, total_amount, payment_method, payment_status) VALUES (?, ?, ?, 'Paid')");
$stmt_order->bind_param("ids", $customer_id, $total_amount, $payment_method);

if ($stmt_order->execute()) {
    $order_id = $stmt_order->insert_id;
    $stmt_order->close();

    // Insert order items
    $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price)
                                 SELECT ?, product_id, quantity, products.price FROM cart
                                 JOIN products ON cart.product_id = products.id
                                 WHERE cart.customer_id = ?");
    $stmt_items->bind_param("ii", $order_id, $customer_id);
    $stmt_items->execute();
    $stmt_items->close();

    // Clear cart
    $stmt_del = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
    $stmt_del->bind_param("i", $customer_id);
    $stmt_del->execute();
    $stmt_del->close();

    // Send SMS via Twilio if configured
    if (!empty(TWILIO_ACCOUNT_SID) && !empty(TWILIO_AUTH_TOKEN) && !empty(TWILIO_PHONE_NUMBER) && class_exists('Twilio\Rest\Client')) {
        try {
            $client = new Client(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
            $sms_body = "Payment Successful! Your Order #" . $order_id . " has been placed. Thank you for shopping with Online Bay Bakery!";
            if (!empty($customer_phone)) {
                $client->messages->create(
                    $customer_phone,
                    [
                        'from' => TWILIO_PHONE_NUMBER,
                        'body' => $sms_body
                    ]
                );
            }
        } catch (Exception $e) {
            // Log SMS error without breaking the user checkout flow
            error_log("Twilio SMS Notification Error: " . $e->getMessage());
        }
    }

    $conn->close();
    header("Location: thank_you.php");
    exit();
} else {
    echo "Error processing order: " . htmlspecialchars($conn->error);
    $conn->close();
}
?>
