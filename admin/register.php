<?php
session_start();
include "../config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST["fullname"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $security_code = $_POST["security_code"];

    // Required security code
    $admin_security_code = "abckingairs11";

    if ($security_code !== $admin_security_code) {
        $error = "Invalid security code!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with this email already exists!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new admin
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
            $stmt->bind_param("sss", $fullname, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = "Admin account created successfully! <a href='login.php'>Login here</a>";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Registration - KingAirs</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .register-container { width: 350px; margin: 100px auto; padding: 20px; background: #fff; border: 1px solid #ccc; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 8px; margin: 8px 0; }
        button { width: 100%; padding: 10px; background: #4CAF50; color: #fff; border: none; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Admin Registration</h2>
        <?php if ($error != ""): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success != ""): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="password" name="security_code" placeholder="Enter Security Code" required>
            <button type="submit">Register as Admin</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
