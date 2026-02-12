<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $err = urlencode('Please fill in all fields.');
        header("Location: admin_register.php?error={$err}");
        exit();
    }

    // Check kama username tayari ipo
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        $err = urlencode('Username already exists.');
        header("Location: admin_register.php?error={$err}");
        exit();
    }
    $stmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert admin mpya
    $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        header("Location: admin_register.php?success=1");
        exit();
    } else {
        $err = urlencode('Failed to register admin.');
        header("Location: admin_register.php?error={$err}");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Registration - DelishHub</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .container { max-width:400px; margin:80px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    h2 { text-align:center; color:#2c3e50; }
    input { width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:6px; }
    button { width:100%; background:#27ae60; color:#fff; border:none; padding:10px; border-radius:6px; cursor:pointer; font-weight:bold; }
    button:hover { background:#1e8449; }
    .error { color:#e74c3c; text-align:center; }
    .success { color:#27ae60; text-align:center; }
    .link { text-align:center; margin-top:15px; }
    .link a { color:#2c3e50; text-decoration:none; font-weight:600; }
    .link a:hover { color:#f39c12; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Admin Registration</h2>
    <?php if (isset($_GET['error'])): ?>
      <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
      <p class="success">Admin registered successfully!</p>
    <?php endif; ?>
    <form method="POST" action="admin_register.php">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Register</button>
    </form>
    <div class="link">
      <p>Already an Admin? <a href="admin_login.php">Login here</a></p>
    </div>
  </div>
</body>
</html>
