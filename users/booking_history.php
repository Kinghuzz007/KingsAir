<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

$user_id = $_SESSION["user_id"];

$sql = "SELECT 
            bookings.id AS booking_id,
            flights.flight_number,
            flights.airline_name,
            flights.departure,
            flights.destination,
            flights.departure_time,
            flights.arrival_time,
            bookings.seat_number,
            bookings.booking_date,
            bookings.status,
            bookings.unique_id
        FROM bookings 
        JOIN flights ON bookings.flight_id = flights.id 
        WHERE bookings.user_id = ? 
        ORDER BY bookings.booking_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Booking History - KingAirs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f4f4f4;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        h2 {
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
        }
        table tr:nth-child(even){ background: #f2f2f2; }
        table th {
            background: #007bff;
            color: white;
            text-align: left;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
        .cancel-btn {
            color: red;
            font-weight: bold;
        }
        .cancelled {
            color: gray;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Booking History</h2>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Unique ID</th>
                    <th>Booking ID</th>
                    <th>Flight Number</th>
                    <th>Airline</th>
                    <th>Route</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                    <th>Seat Number</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Receipt</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["unique_id"]) ?></td>
                        <td><?php echo htmlspecialchars($row["booking_id"]); ?></td>
                        <td><?php echo htmlspecialchars($row["flight_number"]); ?></td>
                        <td><?php echo htmlspecialchars($row["airline_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["departure"]) . " to " . htmlspecialchars($row["destination"]); ?></td>
                        <td><?php echo htmlspecialchars($row["departure_time"]); ?></td>
                        <td><?php echo htmlspecialchars($row["arrival_time"]); ?></td>
                        <td><?php echo htmlspecialchars($row["seat_number"]); ?></td>
                        <td><?php echo htmlspecialchars($row["booking_date"]); ?></td>
                        <td><?php echo (empty($row["status"]) ? "Pending" : htmlspecialchars($row["status"])); ?></td>
                        <td>
                            <?php if ($row["status"] !== "ticket canceled"): ?>
                                <a href="cancel_booking.php?booking_id=<?php echo htmlspecialchars($row["booking_id"]); ?>" 
                                   onclick="return confirm('Are you sure you want to cancel this booking?');" 
                                   class="cancel-btn">Cancel</a>
                            <?php else: ?>
                                <span class="cancelled">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row["status"] !== "ticket canceled"): ?>
                                <a href="generate_receipt.php?booking_id=<?php echo htmlspecialchars($row["booking_id"]); ?>" target="_blank">Download Receipt</a>
                            <?php else: ?>
                                <a href="generate_receipt.php?booking_id=<?php echo htmlspecialchars($row["booking_id"]); ?>" target="_blank">Ticket Cancelled</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>You have no bookings yet.</p>
        <?php endif; ?>
        <p style="text-align:center;"><a href="index.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
