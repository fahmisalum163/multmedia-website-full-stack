<?php
session_start();
include 'db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
    $menu_id = isset($_POST['menu_id']) ? intval($_POST['menu_id']) : 0;

    if ($menu_id <= 0 && isset($_POST['item_name'])) {
        $item_name = trim($_POST['item_name']);
        if ($item_name !== "") {
            $find = $conn->prepare("SELECT id FROM menu WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1");
            $find->bind_param("s", $item_name);
            $find->execute();
            $row = $find->get_result()->fetch_assoc();
            if ($row) {
                $menu_id = intval($row['id']);
            }
        }
    }

    if ($menu_id > 0) {
        if (isset($_SESSION['cart'][$menu_id])) {
            $_SESSION['cart'][$menu_id] += $quantity;
        } else {
            $_SESSION['cart'][$menu_id] = $quantity;
        }
        $_SESSION['cart_message'] = "Item added to cart.";
    } else {
        $_SESSION['cart_message'] = "Product not found in database menu table.";
    }

    header("Location: cart.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_cart']) && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $id => $qty) {
        $menu_id = intval($id);
        $quantity = intval($qty);
        if ($menu_id <= 0) {
            continue;
        }
        if ($quantity > 0) {
            $_SESSION['cart'][$menu_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$menu_id]);
        }
    }
    $_SESSION['cart_message'] = "Cart updated.";
    header("Location: cart.php");
    exit();
}

if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    $_SESSION['cart_message'] = "Item removed.";
    header("Location: cart.php");
    exit();
}

$cart_items = [];
$grand_total = 0.0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $menu_id => $quantity) {
        $stmt = $conn->prepare("SELECT id, name, description, price FROM menu WHERE id = ?");
        $stmt->bind_param("i", $menu_id);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();

        if (!$item) {
            unset($_SESSION['cart'][$menu_id]);
            continue;
        }

        $line_total = floatval($item['price']) * intval($quantity);
        $grand_total += $line_total;
        $cart_items[] = [
            'id' => intval($item['id']),
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => floatval($item['price']),
            'quantity' => intval($quantity),
            'line_total' => $line_total
        ];
    }
}

$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cart</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .navbar { background:#1a2a4f; padding:12px; display:flex; justify-content:center; gap:16px; flex-wrap:wrap; }
    .navbar a { color:#fff; text-decoration:none; font-weight:600; }
    .navbar a:hover { color:#e67e22; }
    .container { max-width:1000px; margin:24px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    h2 { text-align:center; color:#e67e22; margin-top:0; }
    .message { text-align:center; color:#1e8449; font-weight:bold; }
    table { width:100%; border-collapse:collapse; margin-top:14px; }
    th, td { padding:10px; text-align:center; border-bottom:1px solid #ddd; }
    th { background:#2c3e50; color:#fff; }
    .qty { width:70px; padding:6px; text-align:center; }
    .btn { border:none; border-radius:6px; padding:9px 14px; color:#fff; cursor:pointer; text-decoration:none; font-weight:700; display:inline-block; }
    .btn-update { background:#2c3e50; }
    .btn-checkout { background:#27ae60; }
    .btn-checkout:hover { background:#1e8449; }
    .btn-update:hover { background:#1f2d3a; }
    .btn-remove { background:#c0392b; padding:6px 10px; }
    .btn-remove:hover { background:#922b21; }
    .actions { margin-top:16px; display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }
    .hint { text-align:center; margin-top:10px; color:#555; }
    .empty { text-align:center; color:#555; font-weight:bold; }
  </style>
</head>
<body>
  <div class="navbar">
    <a href="../menu.html">Menu</a>
    <a href="cart.php">Cart</a>
    <?php if ($is_logged_in): ?>
      <a href="menu.php">User Menu</a>
      <a href="customer_dashboard.php">My Dashboard</a>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="../login.html">Login</a>
      <a href="../register.html">Register</a>
    <?php endif; ?>
  </div>

  <div class="container">
    <h2>Your Cart</h2>

    <?php if (isset($_SESSION['cart_message'])): ?>
      <p class="message"><?= htmlspecialchars($_SESSION['cart_message']) ?></p>
      <?php unset($_SESSION['cart_message']); ?>
    <?php endif; ?>

    <?php if (!empty($cart_items)): ?>
      <form method="POST" action="cart.php">
        <input type="hidden" name="update_cart" value="1">
        <table>
          <tr>
            <th>Product</th>
            <th>Description</th>
            <th>Qty</th>
            <th>Price (TSh)</th>
            <th>Total (TSh)</th>
            <th>Action</th>
          </tr>
          <?php foreach ($cart_items as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['name']) ?></td>
              <td><?= htmlspecialchars($item['description'] ?: 'No description') ?></td>
              <td><input class="qty" type="number" name="quantities[<?= $item['id'] ?>]" min="0" value="<?= $item['quantity'] ?>"></td>
              <td><?= number_format($item['price'], 2) ?></td>
              <td><?= number_format($item['line_total'], 2) ?></td>
              <td><a class="btn btn-remove" href="cart.php?remove=<?= $item['id'] ?>">Remove</a></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="4"><strong>Grand Total</strong></td>
            <td><strong><?= number_format($grand_total, 2) ?></strong></td>
            <td></td>
          </tr>
        </table>
        <div class="actions">
          <button type="submit" class="btn btn-update">Update Cart</button>
          <?php if ($is_logged_in): ?>
            <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
          <?php else: ?>
            <a href="../login.html" class="btn btn-checkout">Login to Proceed Payment</a>
          <?php endif; ?>
        </div>
      </form>

      <?php if (!$is_logged_in): ?>
        <p class="hint">Cart yako imehifadhiwa. Uki-login utaikuta kama ilivyo na ndipo utaendelea kwenye payment.</p>
      <?php endif; ?>
    <?php else: ?>
      <p class="empty">Cart is empty.</p>
    <?php endif; ?>
  </div>
</body>
</html>
