<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get all flights
$sql = "SELECT * FROM flights";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book a Flight</title>
    <style>
      .table-responsive {
    width: 100%;
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

.selected {
  background-color: #c8e6c9; /* light green background */
}

    </style>
</head>
<body>
<h2>Available Flights</h2>
<div class="table-responsive">
<table id="flightTable">
    <tr>
        <th>Flight Number</th>
        <th>Airline</th>
        <th>Departure</th>
        <th>Destination</th>
        <th>Departure Time</th>
        <th>Arrival Time</th>
        <th>Price</th>
        <th>Action</th>
    </tr>
    <?php while($flight = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $flight['flight_number'] ?></td>
        <td><?= $flight['airline_name'] ?></td>
        <td><?= $flight['departure'] ?></td>
        <td><?= $flight['destination'] ?></td>
        <td><?= $flight['departure_time'] ?></td>
        <td><?= $flight['arrival_time'] ?></td>
        <td><?= $flight['price'] ?></td>
        <td>
            <a href="seat_selection.php?flight_id=<?= $flight['id'] ?>">Select Seat &amp; Book</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</div>
<p><a href="index.php">Back to Dashboard</a></p>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const rows = document.querySelectorAll("#flightTable tbody tr");
    
    rows.forEach(function(row) {
      row.addEventListener("click", function() {
        // Remove "selected" class from all rows
        rows.forEach(r => r.classList.remove("selected"));
        // Add "selected" class only to the clicked row
        row.classList.add("selected");
      });
    });
  });
</script>

</body>
</html>
