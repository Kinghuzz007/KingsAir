<?php
// search_flight.php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php"; // Adjust the path if necessary

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Search Flight - KingAirs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 700px; margin: auto; background: #fff; padding: 20px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        form { margin-bottom: 20px; text-align: center; }
        input[type="text"] { padding: 10px; width: 80%; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: center; }
        th { background-color: #007bff; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h2>Search Flight by Airline Name or Booking Reference</h2>
    <form method="GET" action="">
        <input type="text" name="search_term" placeholder="Enter Airline Name, flight number or Booking Reference" required>
        <button type="submit">Search</button>
    </form>

    <?php
    if (isset($_GET['search_term'])) {
        $search_term = trim($_GET['search_term']);

        // Query flights joining bookings to search by airline_name or booking unique_id.
        $sql = "SELECT DISTINCT 
                    flights.flight_number,
                    flights.airline_name,
                    flights.departure,
                    flights.destination,
                    flights.departure_time,
                    flights.arrival_time
                FROM flights
                LEFT JOIN bookings ON bookings.flight_id = flights.id
                WHERE flights.airline_name LIKE ?
                OR flights.flight_number LIKE ? 
                OR bookings.unique_id = ?
                ORDER BY flights.departure_time ASC";
        $stmt = $conn->prepare($sql);
        $like_search = "%" . $search_term . "%";
        $stmt->bind_param("sss", $like_search, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<h3>Flight Information:</h3>";
            echo "<table>";
            echo "<tr>
                    <th>Flight Number</th>
                    <th>Airline</th>
                    <th>Departure</th>
                    <th>Destination</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                  </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['flight_number']) . "</td>";
                echo "<td>" . htmlspecialchars($row['airline_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['departure']) . "</td>";
                echo "<td>" . htmlspecialchars($row['destination']) . "</td>";
                echo "<td>" . htmlspecialchars($row['departure_time']) . "</td>";
                echo "<td>" . htmlspecialchars($row['arrival_time']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No matching flight found.</p>";
        }
    }
    ?>
</div>
</body>
</html>
