<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: customer_dashboard.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$order_id = intval($_GET['id']);

if ($order_id <= 0) {
    header("Location: customer_dashboard.php");
    exit();
}

$conn->begin_transaction();

try {
    // Hakikisha order ni ya customer aliye-login
    $check = $conn->prepare("SELECT id FROM orders WHERE id = ? AND customer_id = ? LIMIT 1");
    $check->bind_param("ii", $order_id, $user_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if (!$exists) {
        $conn->rollback();
        header("Location: customer_dashboard.php");
        exit();
    }

    $delete_details = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
    $delete_details->bind_param("i", $order_id);
    $delete_details->execute();

    $delete_order = $conn->prepare("DELETE FROM orders WHERE id = ? AND customer_id = ?");
    $delete_order->bind_param("ii", $order_id, $user_id);
    $delete_order->execute();

    $conn->commit();
    header("Location: customer_dashboard.php?deleted=1");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    header("Location: customer_dashboard.php?error=1");
    exit();
}
?>
