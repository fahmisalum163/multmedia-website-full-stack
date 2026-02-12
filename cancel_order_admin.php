<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view_orders.php");
    exit();
}

$order_id = intval($_GET['id']);
if ($order_id <= 0) {
    header("Location: view_orders.php");
    exit();
}

$status = "Cancelled by Admin";
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();

header("Location: view_orders.php?cancelled=1");
exit();
?>
