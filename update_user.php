<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user info
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if ($username !== "" && $email !== "") {
        if ($password !== "") {
            // Validate password strength
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
                $error = "Password must be at least 8 characters and include an uppercase letter, a number, and a symbol.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
                $stmt->bind_param("sssi", $username, $email, $hashed, $user_id);
            }
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $username, $email, $user_id);
        }

        if (!isset($error)) {
            if ($stmt->execute()) {
                header("Location: customer_dashboard.php?updated=1");
                exit();
            } else {
                $error = "Failed to update information.";
            }
        }
    } else {
        $error = "Username and Email are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Information</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; }
    .container { max-width:400px; margin:80px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    h2 { text-align:center; color:#2c3e50; }
    input { width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:6px; }
    button { width:100%; background:#3498db; color:#fff; border:none; padding:10px; border-radius:6px; cursor:pointer; font-weight:bold; }
    button:hover { background:#2980b9; }
    .error { color:#e74c3c; text-align:center; }
    .link { text-align:center; margin-top:15px; }
    .link a { color:#2c3e50; text-decoration:none; font-weight:600; }
    .link a:hover { color:#f39c12; }
  </style>
  <script>
    function validateForm() {
      const password = document.querySelector('input[name="password"]').value;
      if (password !== "") {
        const regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        if (!regex.test(password)) {
          alert("Password must be at least 8 characters and include an uppercase letter, a number, and a symbol.");
          return false;
        }
      }
      return true;
    }
  </script>
</head>
<body>
  <div class="container">
    <h2>Update Your Information</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="update_user.php" onsubmit="return validateForm()">
      <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
      <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
      <button type="submit">Update</button>
    </form>
    <div class="link">
      <p><a href="customer_dashboard.php">Back to Dashboard</a></p>
    </div>
  </div>
</body>
</html>
