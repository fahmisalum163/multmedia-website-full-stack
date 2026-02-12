<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_to_cart'])) {
    $menu_id = isset($_POST['menu_id']) ? intval($_POST['menu_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($menu_id > 0 && $quantity > 0) {
        $check = $conn->prepare("SELECT id FROM menu WHERE id = ?");
        $check->bind_param("i", $menu_id);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();

        if ($exists) {
            if (isset($_SESSION['cart'][$menu_id])) {
                $_SESSION['cart'][$menu_id] += $quantity;
            } else {
                $_SESSION['cart'][$menu_id] = $quantity;
            }
            $_SESSION['menu_message'] = "Item added to cart.";
        } else {
            $_SESSION['menu_message'] = "Selected item does not exist.";
        }
    } else {
        $_SESSION['menu_message'] = "Invalid quantity.";
    }

    header("Location: menu.php");
    exit();
}

$selected_product = null;
if (isset($_GET['product'])) {
    $product_id = intval($_GET['product']);
    if ($product_id > 0) {
        $product_stmt = $conn->prepare("SELECT id, name, description, price, image, created_at FROM menu WHERE id = ?");
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $selected_product = $product_stmt->get_result()->fetch_assoc();
    }
}

$items = $conn->query("SELECT id, name, description, price, image FROM menu ORDER BY id DESC");
$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Menu - DelishHub</title>
  <style>
    body { font-family: Arial, sans-serif; margin:0; background:#f4f6f9; color:#2c3e50; }
    .navbar { background:#1a2a4f; padding:12px; display:flex; justify-content:center; gap:18px; flex-wrap:wrap; }
    .navbar a { color:#fff; text-decoration:none; font-weight:600; }
    .navbar a:hover { color:#e67e22; }
    .container { max-width:1080px; margin:24px auto; padding:0 16px; }
    h2 { text-align:center; color:#e67e22; margin-bottom:16px; }
    .notice { text-align:center; margin:10px 0 20px; color:#1e8449; font-weight:bold; }
    .top-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:10px; }
    .cart-link { text-decoration:none; background:#e67e22; color:#fff; padding:10px 16px; border-radius:6px; font-weight:bold; }
    .cart-link:hover { background:#d35400; }
    .grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:16px; }
    .card { background:#fff; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.08); overflow:hidden; display:flex; flex-direction:column; }
    .card img { width:100%; height:170px; object-fit:cover; background:#eaeaea; }
    .card-content { padding:14px; display:flex; flex-direction:column; gap:8px; }
    .name { font-size:18px; font-weight:700; margin:0; }
    .desc { margin:0; color:#555; min-height:40px; }
    .price { margin:0; font-weight:bold; color:#27ae60; }
    .actions { display:flex; justify-content:space-between; align-items:center; gap:8px; }
    .actions input { width:70px; padding:7px; border:1px solid #ccc; border-radius:6px; }
    .btn { border:none; border-radius:6px; padding:8px 10px; cursor:pointer; color:#fff; font-weight:600; }
    .btn-add { background:#27ae60; }
    .btn-add:hover { background:#1e8449; }
    .btn-detail { background:#3498db; text-decoration:none; display:inline-block; text-align:center; }
    .btn-detail:hover { background:#2980b9; }
    .detail { background:#fff; border-left:4px solid #3498db; padding:16px; border-radius:8px; margin:14px 0 22px; }
    .detail h3 { margin:0 0 10px; color:#2c3e50; }
    .detail p { margin:6px 0; }
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
    <h2>Our Menu</h2>

    <?php if (isset($_SESSION['menu_message'])): ?>
      <p class="notice"><?= htmlspecialchars($_SESSION['menu_message']) ?></p>
      <?php unset($_SESSION['menu_message']); ?>
    <?php endif; ?>

    <div class="top-row">
      <p><strong>Cart Items:</strong> <?= intval($cart_count) ?></p>
      <a href="cart.php" class="cart-link">Go to Cart</a>
    </div>

    <?php if ($selected_product): ?>
      <div class="detail">
        <h3>Product Details</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars($selected_product['name']) ?></p>
        <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($selected_product['description'] ?? 'No description provided.')) ?></p>
        <p><strong>Price:</strong> TSh <?= number_format((float)$selected_product['price'], 2) ?></p>
        <p><strong>Product ID:</strong> <?= intval($selected_product['id']) ?></p>
        <p><strong>Created:</strong> <?= htmlspecialchars($selected_product['created_at']) ?></p>
      </div>
    <?php endif; ?>

    <div class="grid">
      <?php if ($items && $items->num_rows > 0): ?>
        <?php while ($row = $items->fetch_assoc()): ?>
          <div class="card">
            <?php if (!empty($row['image'])): ?>
              <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <?php else: ?>
              <img src="../images/pasta.jpg" alt="No image">
            <?php endif; ?>
            <div class="card-content">
              <p class="name"><?= htmlspecialchars($row['name']) ?></p>
              <p class="desc"><?= htmlspecialchars($row['description'] ?: 'No description available.') ?></p>
              <p class="price">TSh <?= number_format((float)$row['price'], 2) ?></p>

              <form method="POST" action="menu.php">
                <input type="hidden" name="add_to_cart" value="1">
                <input type="hidden" name="menu_id" value="<?= intval($row['id']) ?>">
                <div class="actions">
                  <input type="number" name="quantity" min="1" value="1" required>
                  <button type="submit" class="btn btn-add">Add</button>
                  <a href="menu.php?product=<?= intval($row['id']) ?>" class="btn btn-detail">Details</a>
                </div>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No menu items available.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
