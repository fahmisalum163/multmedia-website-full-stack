<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch menu items
$result = $conn->query("SELECT * FROM menu");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Menu - Admin</title>
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
    .btn { padding:6px 12px; border:none; border-radius:4px; cursor:pointer; font-weight:600; text-decoration:none; }
    .btn-add { background:#27ae60; color:#fff; }
    .btn-add:hover { background:#1e8449; }
    .btn-edit { background:#3498db; color:#fff; }
    .btn-edit:hover { background:#2980b9; }
    .btn-delete { background:#e74c3c; color:#fff; }
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
    <h2>Manage Menu Items</h2>
    <a href="add_menu.php" class="btn btn-add">+ Add New Item</a>
    <table>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Actions</th>
      </tr>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= $row['price'] ?></td>
        <td>
          <a href="edit_menu.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
          <a href="delete_menu.php?id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
