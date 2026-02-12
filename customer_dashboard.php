<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders for this user
$stmt = $conn->prepare("
    SELECT o.id, o.order_date, o.status, SUM(od.quantity * m.price) AS total
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    JOIN menu m ON od.menu_id = m.id
    WHERE o.customer_id = ?
    GROUP BY o.id, o.order_date, o.status
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .navbar { background:#2c3e50; padding:12px; display:flex; justify-content:center; }
    .navbar a { color:#fff; margin:0 15px; text-decoration:none; font-weight:600; }
    .navbar a:hover { color:#f39c12; }
    .container { padding:20px; }
    h2 { color:#27ae60; margin-bottom:20px; text-align:center; }
    table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    th, td { padding:12px; text-align:center; border-bottom:1px solid #ddd; }
    th { background:#2c3e50; color:#fff; }
    tr:hover { background:#f1f1f1; }
    .message { text-align:center; margin-top:20px; font-weight:bold; }
    .success { color:#27ae60; }
    .error { color:#e74c3c; }
  </style>
</head>
<body>
  <div class="navbar">
    <a href="menu.php">Menu</a>
    <a href="cart.php">Cart</a>
    <a href="customer_dashboard.php">My Dashboard</a>
    <a href="update_user.php">Update Info</a>
    <a href="logout.php">Logout</a>
  </div>
  <div class="container">
    <h2>My Orders</h2>

    <?php if (isset($_GET['updated'])): ?>
      <p class="message success">Information updated successfully!</p>
    <?php endif; ?>
    <?php if (isset($_GET['order_success'])): ?>
      <p class="message success">
        Order placed successfully.
        <?php if (isset($_GET['payment'])): ?>
          Payment method: <?= htmlspecialchars(ucfirst($_GET['payment'])) ?>.
        <?php endif; ?>
      </p>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
      <p class="message success">Order deleted successfully.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
      <p class="message error">Failed to delete order.</p>
    <?php endif; ?>

    <table>
      <tr>
        <th>Order ID</th>
        <th>Date</th>
        <th>Status</th>
        <th>Total</th>
        <th>Action</th>
      </tr>
      <?php if ($orders->num_rows > 0): ?>
        <?php while($row = $orders->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= $row['order_date'] ?></td>
          <td><?= $row['status'] ?></td>
          <td><?= number_format($row['total'], 2) ?></td>
          <td><a href="cancel_order.php?id=<?= $row['id'] ?>" onclick="return confirm('Cancel this order?')">Cancel</a></td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="5">You have no orders yet.</td>
        </tr>
      <?php endif; ?>
    </table>
  </div>
</body>
</html>
