<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate
    if ($username === '' || $password === '') {
        $err = urlencode('Please fill in all fields.');
        header("Location: admin_login.php?error={$err}");
        exit();
    }

    // Check admin
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $err = urlencode('Invalid login credentials.');
        header("Location: admin_login.php?error={$err}");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; }
    .login { max-width:400px; margin:80px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    input { width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:6px; }
    button { background:#27ae60; color:#fff; border:none; padding:10px 15px; border-radius:6px; cursor:pointer; }
    button:hover { background:#1e8449; }
    .error { color:#e74c3c; }
  </style>
</head>
<body>
  <div class="login">
    <h2>Admin Login</h2>
    <?php if (isset($_GET['error'])): ?>
      <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <div class="link">
      <p>Not registered yet? <a href="admin_register.php">Register here</a></p>
    </div>
  </div>
</body>
</html>
