<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch users
$users = $conn->query("SELECT id, email FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - DelishHub</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .navbar { background:#2c3e50; padding:12px; }
    .navbar a { color:#fff; margin:0 15px; text-decoration:none; font-weight:600; }
    .navbar a:hover { color:#f39c12; }
    .container { padding:20px; }
    h2 { color:#27ae60; }
    table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.1); margin-top:15px; }
    th, td { padding:12px; text-align:left; border-bottom:1px solid #ddd; }
    th { background:#2c3e50; color:#fff; }
    tr:hover { background:#f1f1f1; }
    .btn-delete { background:#e74c3c; color:#fff; padding:6px 12px; border:none; border-radius:4px; text-decoration:none; font-weight:600; }
    .btn-delete:hover { background:#c0392b; }
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
    <h2>Welcome Admin</h2>

    <h3>Registered Users</h3>
    <table>
      <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Action</th>
      </tr>
      <?php while($row = $users->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td>
          <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Delete this user?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
