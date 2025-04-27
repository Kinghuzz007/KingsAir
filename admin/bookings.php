<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

// Retrieve all bookings joined with flight and user details.
$sql = "SELECT 
            b.id AS booking_id,
            u.name,
            u.email,
            f.flight_number,
            f.airline_name,
            f.departure,
            f.destination,
            b.seat_number,
            b.booking_date,
            b.status
        FROM bookings b 
        JOIN users u ON b.user_id = u.id
        JOIN flights f ON b.flight_id = f.id
        ORDER BY b.booking_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Bookings - KingAirs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #4CAF50; color: white; }
        a { text-decoration: none; color: #4CAF50; }
    </style>
</head>
<body>
    <h2>Manage Bookings</h2>
    <p><a href="index.php">Back to Dashboard</a></p>
    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Booking ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Flight</th>
            <th>Route</th>
            <th>Seat Number</th>
            <th>Booking Date</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row["booking_id"]) ?></td>
            <td><?= htmlspecialchars($row["name"]) ?></td>
            <td><?= htmlspecialchars($row["email"]) ?></td>
            <td><?= htmlspecialchars($row["flight_number"]) ?> (<?= htmlspecialchars($row["airline_name"]) ?>)</td>
            <td><?= htmlspecialchars($row["departure"]) ?> to <?= htmlspecialchars($row["destination"]) ?></td>
            <td><?= htmlspecialchars($row["seat_number"]) ?></td>
            <td><?= htmlspecialchars($row["booking_date"]) ?></td>
            <td><?= htmlspecialchars($row["status"]) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No bookings found.</p>
    <?php endif; ?>
</body>
</html>
