<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'checkout.php';
    header("Location: ../login.html");
    exit();
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['cart_message'] = "Your cart is empty.";
    header("Location: cart.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$cart_items = [];
$grand_total = 0.0;
$errors = [];

foreach ($_SESSION['cart'] as $menu_id => $qty) {
    $id = intval($menu_id);
    $quantity = intval($qty);
    if ($id <= 0 || $quantity <= 0) {
        continue;
    }

    $stmt = $conn->prepare("SELECT id, name, price FROM menu WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) {
        continue;
    }

    $line_total = floatval($item['price']) * $quantity;
    $grand_total += $line_total;
    $cart_items[] = [
        'menu_id' => intval($item['id']),
        'name' => $item['name'],
        'price' => floatval($item['price']),
        'quantity' => $quantity,
        'line_total' => $line_total
    ];
}

if (empty($cart_items)) {
    $_SESSION['cart'] = [];
    $_SESSION['cart_message'] = "No valid cart items found.";
    header("Location: cart.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : "";
    $confirm = isset($_POST['confirm_order']) ? $_POST['confirm_order'] : "";
    $allowed = ['cash', 'card', 'mobile'];

    if (!in_array($payment_method, $allowed, true)) {
        $errors[] = "Please choose a payment method.";
    }
    if ($confirm !== '1') {
        $errors[] = "Please confirm your checklist before payment.";
    }

    if (empty($errors)) {
        $status = "Paid (" . ucfirst($payment_method) . ")";
        $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, status) VALUES (?, NOW(), ?)");
        $order_stmt->bind_param("is", $user_id, $status);

        if ($order_stmt->execute()) {
            $order_id = intval($order_stmt->insert_id);
            $detail_stmt = $conn->prepare("INSERT INTO order_details (order_id, menu_id, quantity) VALUES (?, ?, ?)");

            foreach ($cart_items as $row) {
                $menu_id = intval($row['menu_id']);
                $quantity = intval($row['quantity']);
                $detail_stmt->bind_param("iii", $order_id, $menu_id, $quantity);
                $detail_stmt->execute();
            }

            $_SESSION['cart'] = [];
            header("Location: customer_dashboard.php?order_success=1");
            exit();
        } else {
            $errors[] = "Failed to place order. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout</title>
  <style>
    body { font-family: Arial, sans-serif; margin:0; background:#f4f6f9; }
    .container { max-width:900px; margin:24px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    h2 { text-align:center; color:#e67e22; margin-top:0; }
    table { width:100%; border-collapse:collapse; }
    th, td { border-bottom:1px solid #ddd; padding:10px; text-align:center; }
    th { background:#2c3e50; color:#fff; }
    .total { text-align:right; font-size:18px; font-weight:bold; margin-top:12px; }
    .pay-box { margin-top:18px; padding:14px; border:1px solid #ddd; border-radius:8px; background:#fafafa; }
    .pay-box label { display:block; margin:8px 0; }
    .error { color:#c0392b; font-weight:700; margin:5px 0; }
    .btn { margin-top:12px; padding:10px 14px; border:none; border-radius:6px; color:#fff; background:#27ae60; font-weight:700; cursor:pointer; }
    .btn:hover { background:#1e8449; }
    .back { display:inline-block; margin-left:8px; text-decoration:none; padding:10px 14px; border-radius:6px; background:#2c3e50; color:#fff; font-weight:700; }
    .back:hover { background:#1f2d3a; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Checkout</h2>

    <?php foreach ($errors as $error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <table>
      <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Unit Price (TSh)</th>
        <th>Line Total (TSh)</th>
      </tr>
      <?php foreach ($cart_items as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= intval($item['quantity']) ?></td>
          <td><?= number_format($item['price'], 2) ?></td>
          <td><?= number_format($item['line_total'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>

    <p class="total">Grand Total: TSh <?= number_format($grand_total, 2) ?></p>

    <form method="POST" action="checkout.php" class="pay-box">
      <strong>Payment Method</strong>
      <label><input type="radio" name="payment_method" value="cash" required> Cash On Delivery</label>
      <label><input type="radio" name="payment_method" value="card"> Card</label>
      <label><input type="radio" name="payment_method" value="mobile"> Mobile Money</label>
      <label><input type="checkbox" name="confirm_order" value="1"> I confirm this order checklist and payment.</label>

      <button type="submit" class="btn">Pay and Place Order</button>
      <a href="cart.php" class="back">Back to Cart</a>
    </form>
  </div>
</body>
</html>
