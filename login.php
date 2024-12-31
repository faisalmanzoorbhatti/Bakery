<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header("Location: index.php?error=" . urlencode("Please enter both email and password."));
    exit();
}

try {
    $conn = getDbConnection();

    $stmt = $conn->prepare("SELECT id, name, password, role FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            session_regenerate_id(true);
            $_SESSION['customer_id'] = $id;
            $_SESSION['customer_name'] = $name;
            $_SESSION['role'] = $role ?? 'customer';
            $stmt->close();
            $conn->close();

            if ($role === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: products.php");
            }
            exit();
        } else {
            $stmt->close();
            $conn->close();
            header("Location: index.php?error=" . urlencode("Invalid password. Please check your credentials."));
            exit();
        }
    } else {
        $stmt->close();
        $conn->close();
        header("Location: index.php?error=" . urlencode("No user found with this email."));
        exit();
    }
} catch (Exception $e) {
    header("Location: index.php?error=" . urlencode("Database connection error: " . $e->getMessage()));
    exit();
}
