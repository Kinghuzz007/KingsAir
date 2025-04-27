<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

// Initialize variables
$departure = "";
$destination = "";
$flights = [];

if (isset($_GET['departure']) && isset($_GET['destination'])) {
    $departure = trim($_GET['departure']);
    $destination = trim($_GET['destination']);
    
    // Query to search for flights where departure and destination match
    $sql = "SELECT * FROM flights 
            WHERE departure LIKE ? AND destination LIKE ? 
            ORDER BY departure_time ASC";
    
    $stmt = $conn->prepare($sql);
    $paramDeparture = "%" . $departure . "%";
    $paramDestination = "%" . $destination . "%";
    $stmt->bind_param("ss", $paramDeparture, $paramDestination);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Search Flights - KingAirs<?php if ($departure || $destination) echo " - " . htmlspecialchars($departure) . " to " . htmlspecialchars($destination); ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 20px;
      padding: 20px;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 20px;
      box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
    }
    form {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }
    input[type="text"] {
      padding: 10px;
      width: 40%;
      margin: 0 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    table th, table td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }
    table th {
      background-color: #007bff;
      color: white;
    }
    p {
      text-align: center;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Search Available Flights</h2>
  <form method="GET" action="">
    <input type="text" name="departure" placeholder="Departure Location" value="<?php echo htmlspecialchars($departure); ?>">
    <input type="text" name="destination" placeholder="Destination" value="<?php echo htmlspecialchars($destination); ?>">
    <select name="travel-option" id="">
                    <option value="one-way">One Way</option>
                    <option value="two-way">Return Ticket</option>
    </select>
    <button type="submit">Search Flights</button>
  </form>

  <?php if(isset($_GET['departure']) && isset($_GET['destination'])): ?>
    <h3>Search Results for: <?= htmlspecialchars($departure) ?> to <?= htmlspecialchars($destination) ?></h3>
    <?php if(count($flights) > 0): ?>
      <table>
        <tr>
          <th>Flight Number</th>
          <th>Airline</th>
          <th>Departure</th>
          <th>Destination</th>
          <th>Departure Time</th>
          <th>Arrival Time</th>
          <th>One-Way</th>
          <th>Returning</th>
          <th>Available Seats</th>
          <th>Action</th>
        </tr>
        <?php foreach($flights as $flight): ?>
          <tr>
            <td><?= htmlspecialchars($flight['flight_number']) ?></td>
            <td><?= htmlspecialchars($flight['airline_name']) ?></td>
            <td><?= htmlspecialchars($flight['departure']) ?></td>
            <td><?= htmlspecialchars($flight['destination']) ?></td>
            <td><?= htmlspecialchars($flight['departure_time']) ?></td>
            <td><?= htmlspecialchars($flight['arrival_time']) ?></td>
            <td><?= htmlspecialchars($flight['price']) ?></td>
            <td><?= htmlspecialchars($flight['two_way_ticket']) ?></td>
            <td><?= htmlspecialchars($flight['seats']) ?></td>
            <td><a href="seat_selection.php?flight_id=<?= htmlspecialchars($flight['id']) ?>">Book Flight</a></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php else: ?>
      <p>No flights found for this route.</p>
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>
