<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_menu.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch existing item
$stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);

    if ($name !== "" && $price !== "") {
        $stmt = $conn->prepare("UPDATE menu SET name=?, description=?, price=? WHERE id=?");
        $stmt->bind_param("ssdi", $name, $description, $price, $id);
        if ($stmt->execute()) {
            header("Location: manage_menu.php?updated=1");
            exit();
        } else {
            $error = "Failed to update item.";
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
  <title>Edit Menu Item - Admin</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .container { max-width:400px; margin:80px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    h2 { text-align:center; color:#2c3e50; }
    input, textarea { width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:6px; }
    button { width:100%; background:#3498db; color:#fff; border:none; padding:10px; border-radius:6px; cursor:pointer; font-weight:bold; }
    button:hover { background:#2980b9; }
    .error { color:#e74c3c; text-align:center; }
    .link { text-align:center; margin-top:15px; }
    .link a { color:#2c3e50; text-decoration:none; font-weight:600; }
    .link a:hover { color:#f39c12; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Menu Item</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="edit_menu.php?id=<?= $id ?>">
      <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
      <textarea name="description"><?= htmlspecialchars($item['description']) ?></textarea>
      <input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" required>
      <button type="submit">Update Item</button>
    </form>
    <div class="link">
      <p><a href="manage_menu.php">Back to Manage Menu</a></p>
    </div>
  </div>
</body>
</html>
