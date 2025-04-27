<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

// Retrieve all flights
$sql = "SELECT * FROM flights ORDER BY departure_time ASC";
$result = $conn->query($sql);

// Get success message from session
$success_message = isset($_SESSION["success_message"]) ? $_SESSION["success_message"] : "";
unset($_SESSION["success_message"]); // Remove the message after displaying it
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Flights - KingAirs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f4f4f4;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            background: #4CAF50;
            color: white;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table th {
            background: #4CAF50;
            color: white;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
    <h2>Manage Flights</h2>
    <p><a href="index.php">Back to Dashboard</a></p>
    
    <?php if (!empty($success_message)): ?>
        <div class="message"><?= $success_message; ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Flight Number</th>
            <th>Airline</th>
            <th>Route</th>
            <th>Departure</th>
            <th>Arrival</th>
            <th>Price</th>
            <th>Available Seats</th>
            <th>Last Updated</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row["id"]) ?></td>
            <td><?= htmlspecialchars($row["flight_number"]) ?></td>
            <td><?= htmlspecialchars($row["airline_name"]) ?></td>
            <td><?= htmlspecialchars($row["departure"]) ?> to <?= htmlspecialchars($row["destination"]) ?></td>
            <td><?= htmlspecialchars($row["departure_time"]) ?></td>
            <td><?= htmlspecialchars($row["arrival_time"]) ?></td>
            <td><?= htmlspecialchars($row["price"]) ?></td>
            <td><?= htmlspecialchars($row["seats"]) ?></td>
            <td><?= $row["updated_on"] ? htmlspecialchars($row["updated_on"]) : "Never Updated"; ?></td>
            <td>
                <a href="edit_flight.php?flight_id=<?= htmlspecialchars($row["id"]); ?>">Edit</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No flights available.</p>
    <?php endif; ?>
</body>
</html>
