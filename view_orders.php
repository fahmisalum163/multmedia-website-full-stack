<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT o.id, o.order_date, o.status, u.username AS customer, u.email,
           SUM(od.quantity * m.price) AS total
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    JOIN order_details od ON o.id = od.order_id
    JOIN menu m ON od.menu_id = m.id
    GROUP BY o.id, o.order_date, o.status, u.username, u.email
    ORDER BY o.order_date DESC
");
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Orders - Admin</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .navbar { background:#2c3e50; padding:12px; display:flex; gap:16px; justify-content:center; flex-wrap:wrap; }
    .navbar a { color:#fff; text-decoration:none; font-weight:600; }
    .navbar a:hover { color:#f39c12; }
    .container { padding:20px; }
    h2 { color:#27ae60; text-align:center; }
    .message { text-align:center; font-weight:700; color:#27ae60; }
    table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.1); margin-top:15px; }
    th, td { padding:12px; text-align:center; border-bottom:1px solid #ddd; }
    th { background:#2c3e50; color:#fff; }
    tr:hover { background:#f1f1f1; }
    .btn-cancel { background:#e74c3c; color:#fff; padding:7px 12px; border-radius:4px; text-decoration:none; font-weight:600; }
    .btn-cancel:hover { background:#c0392b; }
  </style>
</head>
<body>
  <div class="navbar">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_menu.php">Manage Menu</a>
    <a href="view_orders.php">View Orders</a>
    <a href="view_contacts.php">View Contacts</a>
    <a href="logout.php">Logout</a>
  </div>

  <div class="container">
    <h2>All Orders</h2>

    <?php if (isset($_GET['cancelled'])): ?>
      <p class="message">Order cancelled successfully.</p>
    <?php endif; ?>

    <table>
      <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Email</th>
        <th>Date</th>
        <th>Status</th>
        <th>Total (TSh)</th>
        <th>Action</th>
      </tr>
      <?php if ($orders->num_rows > 0): ?>
        <?php while($row = $orders->fetch_assoc()): ?>
          <tr>
            <td><?= intval($row['id']) ?></td>
            <td><?= htmlspecialchars($row['customer']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['order_date']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= number_format((float)$row['total'], 2) ?></td>
            <td>
              <?php if (stripos($row['status'], 'cancel') === false): ?>
                <a class="btn-cancel" href="cancel_order_admin.php?id=<?= intval($row['id']) ?>" onclick="return confirm('Cancel this order as admin?')">Cancel</a>
              <?php else: ?>
                Cancelled
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="7">No orders found.</td>
        </tr>
      <?php endif; ?>
    </table>
  </div>
</body>
</html>
