<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

// Retrieve all users (excluding admins if you want to manage only regular users)
$sql = "SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - KingAirs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #4CAF50; color: white; }
        a { text-decoration: none; color: #4CAF50; }
    </style>
</head>
<body>
    <h2>Manage Users</h2>
    <p><a href="index.php">Back to Dashboard</a></p>
    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Created At</th>
            <!-- Add actions like Delete/Edit if needed -->
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row["id"]) ?></td>
            <td><?= htmlspecialchars($row["name"]) ?></td>
            <td><?= htmlspecialchars($row["email"]) ?></td>
            <td><?= htmlspecialchars($row["created_at"]) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>
</body>
</html>
