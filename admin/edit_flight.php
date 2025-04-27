<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

if (!isset($_GET["flight_id"])) {
    die("Flight ID not provided.");
}

$flight_id = intval($_GET["flight_id"]);

// Process the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seats = intval($_POST["seats"]);

    $sqlUpdate = "UPDATE flights SET seats = ?, updated_on = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("ii", $seats, $flight_id); // "ii" because seats and flight_id are integers

    if ($stmt->execute()) {
        $_SESSION["success_message"] = "Flight updated successfully!";
        header("Location: flights.php?message=Flight updated successfully");
        exit();
    } else {
        $_SESSION["error_message"] = "Error updating flight: " . $conn->error;
    }
}

// Fetch flight details
$sql = "SELECT * FROM flights WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $flight_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Flight not found.");
}
$flight = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Flight - KingAirs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        input[type="number"], input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background: #007bff; color: #fff; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Flight Details</h2>
        <form method="post" action="">
            <label>Flight Number:</label>
            <input type="text" value="<?= htmlspecialchars($flight['flight_number']); ?>" disabled>
            
            <label>Airline Name:</label>
            <input type="text" value="<?= htmlspecialchars($flight['airline_name']); ?>" disabled>
            
            <label>Departure:</label>
            <input type="text" value="<?= htmlspecialchars($flight['departure']); ?>" disabled>
            
            <label>Destination:</label>
            <input type="text" value="<?= htmlspecialchars($flight['destination']); ?>" disabled>
            
            <label>Departure Time:</label>
            <input type="text" value="<?= htmlspecialchars($flight['departure_time']); ?>" disabled>
            
            <label>Arrival Time:</label>
            <input type="text" value="<?= htmlspecialchars($flight['arrival_time']); ?>" disabled>
            
            <label>Price:</label>
            <input type="text" value="<?= htmlspecialchars($flight['price']); ?>" disabled>
            
            <label>Available Seats:</label>
            <input type="number" name="seats" value="<?= htmlspecialchars($flight['seats']); ?>" min="0" required>
            
            <button type="submit">Update Flight</button>
        </form>
        <p><a href="flight.php">Back to Flight Management</a></p>
    </div>
</body>
</html>
