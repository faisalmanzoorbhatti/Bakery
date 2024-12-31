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
$payment_method = trim($_POST['payment_method'] ?? 'Cash on Delivery');
$delivery_phone = trim($_POST['phone'] ?? '');
$delivery_address = trim($_POST['address'] ?? '');
$delivery_slot = trim($_POST['delivery_slot'] ?? 'Immediate Express (45 mins)');
$special_instructions = trim($_POST['special_instructions'] ?? '');

$conn = getDbConnection();

// Update customer phone if provided
if (!empty($delivery_phone)) {
    $stmt_up_phone = $conn->prepare("UPDATE customers SET phone = ? WHERE id = ?");
    $stmt_up_phone->bind_param("si", $delivery_phone, $customer_id);
    $stmt_up_phone->execute();
    $stmt_up_phone->close();
}

// Fetch customer phone number
$stmt_cust = $conn->prepare("SELECT phone, name FROM customers WHERE id = ?");
$stmt_cust->bind_param("i", $customer_id);
$stmt_cust->execute();
$cust_res = $stmt_cust->get_result();
$customer_data = $cust_res->fetch_assoc();
$stmt_cust->close();

$customer_phone = !empty($delivery_phone) ? $delivery_phone : (!empty($customer_data['phone']) ? $customer_data['phone'] : TWILIO_ADMIN_PHONE);

// Calculate total cart amount
$stmt_cart = $conn->prepare("SELECT SUM(products.price * cart.quantity) AS subtotal FROM cart
                             JOIN products ON cart.product_id = products.id
                             WHERE cart.customer_id = ?");
$stmt_cart->bind_param("i", $customer_id);
$stmt_cart->execute();
$cart_res = $stmt_cart->get_result();
$row = $cart_res->fetch_assoc();
$subtotal = (float)($row['subtotal'] ?? 0);
$stmt_cart->close();

if ($subtotal <= 0) {
    header("Location: view_cart.php");
    exit();
}

// Calculate discount & delivery
$discount_rate = $_SESSION['promo_discount'] ?? 0.0;
$discount_amount = $subtotal * $discount_rate;
$free_ship_by_coupon = $_SESSION['promo_free_ship'] ?? false;
$is_free_shipping = ($subtotal >= 35.00) || $free_ship_by_coupon;
$delivery_fee = ($subtotal > 0 && !$is_free_shipping) ? 3.99 : 0.00;
$tax = ($subtotal - $discount_amount) * 0.05;
$final_total = max(0, ($subtotal - $discount_amount + $delivery_fee + $tax));

$initial_status = ($payment_method === 'Cash on Delivery') ? 'Preparing' : 'Paid';

// Insert order
$stmt_order = $conn->prepare("INSERT INTO orders (customer_id, total_amount, payment_method, payment_status) VALUES (?, ?, ?, ?)");
$stmt_order->bind_param("idss", $customer_id, $final_total, $payment_method, $initial_status);

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

    // Clear applied promo
    unset($_SESSION['promo_discount']);
    unset($_SESSION['promo_free_ship']);
    unset($_SESSION['promo_code']);
    $_SESSION['latest_order_id'] = $order_id;

    // Send SMS via Twilio if configured
    if (!empty(TWILIO_ACCOUNT_SID) && !empty(TWILIO_AUTH_TOKEN) && !empty(TWILIO_PHONE_NUMBER) && class_exists('Twilio\Rest\Client')) {
        try {
            $client = new Client(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
            $sms_body = "Order Confirmed! Your Bakery Order #" . $order_id . " ($" . number_format($final_total, 2) . ") is being prepared. Thank you for choosing L'Atelier Bakery!";
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
            error_log("Twilio SMS Notification Error: " . $e->getMessage());
        }
    }

    $conn->close();
    header("Location: thank_you.php?order_id=" . $order_id);
    exit();
} else {
    echo "Error processing order: " . htmlspecialchars($conn->error);
    $conn->close();
}
