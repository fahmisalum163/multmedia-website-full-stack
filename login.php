<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row["password"])) {
            $_SESSION["user_id"] = $row["id"];
            $redirect = isset($_SESSION['after_login_redirect']) ? $_SESSION['after_login_redirect'] : 'menu.php';
            unset($_SESSION['after_login_redirect']);
            header("Location: " . $redirect);
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Login - DelishHub</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .container { max-width:400px; margin:80px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    h2 { text-align:center; color:#2c3e50; }
    input { width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:6px; }
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
    <h2>User Login</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="login.php">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <div class="link">
      <p>Not registered yet? <a href="../register.html">Register here</a></p>
      <p>Are you an Admin? <a href="admin_login.php">Login here</a></p>
    </div>
  </div>
</body>
</html>
