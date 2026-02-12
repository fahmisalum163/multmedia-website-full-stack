<?php
session_start();
include 'db.php';
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.html");
    exit();
}

// Delete contact message
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM contacts WHERE id=$id");
    header("Location: manage_contacts.php");
    exit();
}

$result = $conn->query("SELECT id, name, email, message, created_at FROM contacts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Contacts - DelishHub</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <h2>Manage Contact Messages</h2>
  <table border="1" cellpadding="10">
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Message</th>
      <th>Created At</th>
      <th>Action</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row["id"] ?></td>
        <td><?= htmlspecialchars($row["name"]) ?></td>
        <td><?= htmlspecialchars($row["email"]) ?></td>
        <td><?= nl2br(htmlspecialchars($row["message"])) ?></td>
        <td><?= $row["created_at"] ?></td>
        <td><a href="manage_contacts.php?delete=<?= $row["id"] ?>">Delete</a></td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
