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
