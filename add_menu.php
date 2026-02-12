<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);

    if ($name !== "" && $price !== "") {
        $stmt = $conn->prepare("INSERT INTO menu (name, description, price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $description, $price);
        if ($stmt->execute()) {
            header("Location: manage_menu.php?success=1");
            exit();
        } else {
            $error = "Failed to add item.";
        }
    } else {
        $error = "Name and Price are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Menu Item - Admin</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .container { max-width:400px; margin:80px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    h2 { text-align:center; color:#2c3e50; }
    input, textarea { width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:6px; }
    button { width:100%; background:#27ae60; color:#fff; border:none; padding:10px; border-radius:6px; cursor:pointer; font-weight:bold; }
    button:hover { background:#1e8449; }
    .error { color:#e74c3c; text-align:center; }
    .link { text-align:center; margin-top:15px; }
    .link a { color:#2c3e50; text-decoration:none; font-weight:600; }
    .link a:hover { color:#f39c12; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Add New Menu Item</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="add_menu.php">
      <input type="text" name="name" placeholder="Item Name" required>
      <textarea name="description" placeholder="Description"></textarea>
      <input type="number" step="0.01" name="price" placeholder="Price" required>
      <button type="submit">Add Item</button>
    </form>
    <div class="link">
      <p><a href="manage_menu.php">Back to Manage Menu</a></p>
    </div>
  </div>
</body>
</html>
