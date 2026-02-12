<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$contacts = $conn->query("SELECT * FROM contacts");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Contacts - Admin</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .navbar { background:#2c3e50; padding:12px; }
    .navbar a { color:#fff; margin:0 15px; text-decoration:none; font-weight:600; }
    .navbar a:hover { color:#f39c12; }
    .container { padding:20px; }
    h2 { color:#27ae60; margin-bottom:20px; }
    table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    th, td { padding:12px; text-align:left; border-bottom:1px solid #ddd; }
    th { background:#2c3e50; color:#fff; }
    tr:hover { background:#f1f1f1; }
    .btn-delete { background:#e74c3c; color:#fff; padding:6px 12px; border:none; border-radius:4px; text-decoration:none; font-weight:600; }
    .btn-delete:hover { background:#c0392b; }
    .footer { text-align:center; padding:15px; margin-top:20px; background:#2c3e50; color:#fff; }
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
    <h2>View Contacts</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Message</th>
        <th>Action</th>
      </tr>
      <?php while($row = $contacts->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['message']) ?></td>
        <td>
          <a href="delete_contact.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Delete this contact?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
  <div class="footer">
    <p>© 2026 DelishHub Admin Panel</p>
  </div>
</body>
</html>
