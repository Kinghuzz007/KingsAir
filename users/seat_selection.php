<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

// Initialize variables for flight search and selection.
$departure = "";
$destination = "";
$flights = [];
$selectedFlight = null;
$message = "";

// Flight Search Logic
if (isset($_GET["departure"]) || isset($_GET["destination"])) {
    $departure = isset($_GET["departure"]) ? trim($_GET["departure"]) : "";
    $destination = isset($_GET["destination"]) ? trim($_GET["destination"]) : "";
    
    $stmt = $conn->prepare("SELECT * FROM flights 
                            WHERE (departure LIKE ? OR ? = '')
                              AND (destination LIKE ? OR ? = '')
                            ORDER BY departure_time ASC");
    $paramDeparture = "%" . $departure . "%";
    $paramDestination = "%" . $destination . "%";
    $stmt->bind_param("ssss", $paramDeparture, $departure, $paramDestination, $destination);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
    }
}

// Flight Selection Logic
if (isset($_GET["flight_id"])) {
    $flight_id = intval($_GET["flight_id"]);
    $stmt = $conn->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $selectedFlight = $result->fetch_assoc();
    }
}

// Booking Process
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["book_flight"])) {
    $flight_id_post = intval($_POST["flight_id"]);
    $seats_requested = intval($_POST["seats"]);
    $ticket_type = $_POST["ticket_type"];
    $user_id = $_SESSION["user_id"];
    
    // Generate a unique booking reference
    $unique_id = "KING-AIR-" . sprintf("%08d", rand(0, 99999999));
    
    // Determine which price to use based on ticket_type
    $price_field = ($ticket_type === "two_way") ? "two_way_ticket" : "price";
    $price_query = $conn->prepare("SELECT $price_field FROM flights WHERE id = ?");
    $price_query->bind_param("i", $flight_id_post);
    $price_query->execute();
    $price_result = $price_query->get_result();
    $price_row = $price_result->fetch_assoc();
    $ticket_price = floatval($price_row[$price_field]);
    $total_price = $ticket_price * $seats_requested;
    
    // Auto-generate seat numbers.
    $assignedSeats = [];
    $seatQuery = $conn->prepare("SELECT seat_number FROM bookings WHERE flight_id = ? AND status <> 'ticket canceled'");
    $seatQuery->bind_param("i", $flight_id_post);
    $seatQuery->execute();
    $seatResult = $seatQuery->get_result();
    while ($seatRow = $seatResult->fetch_assoc()) {
        $seatsArray = explode(",", $seatRow['seat_number']);
        foreach ($seatsArray as $seat) {
            $seatNum = intval(trim(substr($seat, 1)));
            if ($seatNum > 0) {
                $assignedSeats[] = $seatNum;
            }
        }
    }
    sort($assignedSeats);
    $newSeatNumbers = [];
    $nextSeatNumber = 1;
    for ($i = 0; $i < $seats_requested; $i++) {
        while (in_array($nextSeatNumber, $assignedSeats)) {
            $nextSeatNumber++;
        }
        $newSeatNumbers[] = "A" . $nextSeatNumber;
        $assignedSeats[] = $nextSeatNumber;
        $nextSeatNumber++;
    }
    $seat_numbers = implode(",", $newSeatNumbers);
    
    // Insert booking record (6 parameters: user_id, flight_id, seat_number, ticket_type, price, unique_id)
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, flight_id, seat_number, ticket_type, price, booking_date, unique_id, status) VALUES (?, ?, ?, ?, ?, NOW(), ?, 'pending')");
    // Correct the bind_param type to "iissds" for 6 parameters.
    $stmt->bind_param("iissds", $user_id, $flight_id_post, $seat_numbers, $ticket_type, $total_price, $unique_id);
    
    // Update the available seats in the flights table (reduce available seats by the number booked)
    $updateStmt = $conn->prepare("UPDATE flights SET seats = seats - ? WHERE id = ?");
    $updateStmt->bind_param("ii", $seats_requested, $flight_id_post);
    
    if ($stmt->execute() && $updateStmt->execute()) {
        echo "<p style='color:green;'>Booking successful! Your booking reference is <strong>$unique_id</strong> and your seats: <strong>$seat_numbers</strong>. Total Price: ₦" . number_format($total_price, 2) . ".</p>";
        echo "<p><a href='booking_history.php'>View Booking History & Receipt</a></p>";
        exit();
    } else {
        echo "<p style='color:red;'>Error processing booking: " . $conn->error . "</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Seat Selection - KingAirs<?php echo (!empty($departure) || !empty($destination)) ? " - " . htmlspecialchars($departure . " to " . $destination) : ""; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h2, h3 { text-align: center; }
        form.search-form { display: flex; justify-content: center; margin-bottom: 20px; }
        form.search-form input[type="text"] { padding: 10px; width: 40%; margin: 0 10px; border: 1px solid #ccc; border-radius: 5px; }
        form.search-form button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: center; }
        th { background-color: #007bff; color: white; }
        .book-btn { display: inline-block; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px; }
        .results-title { text-align: center; margin-bottom: 10px; }
        .message { text-align: center; margin-bottom: 20px; font-size: 1.1em; }
    </style>
    <script>
        function calculateTotalPrice() {
            var seatCount = parseInt(document.getElementById("seat_count").value) || 0;
            var oneWayPrice = parseFloat(document.getElementById("one_way_price").value) || 0;
            var twoWayPrice = parseFloat(document.getElementById("two_way_price").value) || 0;
            var ticketType = document.querySelector('input[name="ticket_type"]:checked').value;
            var total = (ticketType === "one_way") ? oneWayPrice * seatCount : twoWayPrice * seatCount;
            document.getElementById("total_price").innerText = "Total Price: ₦" + total.toFixed(2);
        }
        window.addEventListener("load", function(){
            calculateTotalPrice();
        });
    </script>
</head>
<body>
<div class="container">
    <h2>Search for Flights</h2>
    <!-- Search Form -->
    <form method="GET" action="" class="search-form">
        <label>Departure:</label>
        <input type="text" name="departure" placeholder="Enter Departure Location" value="<?php echo htmlspecialchars($departure); ?>" required>
        <label>Destination:</label>
        <input type="text" name="destination" placeholder="Enter Destination" value="<?php echo htmlspecialchars($destination); ?>" required>
        <button type="submit">Search Flights</button>
    </form>

    <?php if (count($flights) > 0 && !isset($_GET["flight_id"])): ?>
        <h3 class="results-title">Search Results for: <?= htmlspecialchars($departure) ?> to <?= htmlspecialchars($destination) ?></h3>
        <table>
            <tr>
                <th>Flight Number</th>
                <th>Airline</th>
                <th>Departure</th>
                <th>Destination</th>
                <th>Departure Time</th>
                <th>Arrival Time</th>
                <th>Price (One Way)</th>
                <th>Price (Returning)</th>
                <th>Available Seats</th>
                <th>Action</th>
            </tr>
            <?php foreach ($flights as $flight): ?>
                <tr>
                    <td><?= htmlspecialchars($flight['flight_number']) ?></td>
                    <td><?= htmlspecialchars($flight['airline_name']) ?></td>
                    <td><?= htmlspecialchars($flight['departure']) ?></td>
                    <td><?= htmlspecialchars($flight['destination']) ?></td>
                    <td><?= htmlspecialchars($flight['departure_time']) ?></td>
                    <td><?= htmlspecialchars($flight['arrival_time']) ?></td>
                    <td>₦<?= htmlspecialchars($flight['price']) ?></td>
                    <td>₦<?= htmlspecialchars($flight['two_way_ticket']) ?></td>
                    <td><?= htmlspecialchars($flight['seats']) ?></td>
                    <td>
                        <a class="book-btn" href="?departure=<?= urlencode($departure) ?>&destination=<?= urlencode($destination) ?>&flight_id=<?= $flight['id'] ?>">Book Flight</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif(isset($_GET["flight_id"]) && $selectedFlight): ?>
        <!-- Display the booking form for the selected flight -->
        <h3>Book Flight: <?= htmlspecialchars($selectedFlight['flight_number']) ?> - <?= htmlspecialchars($selectedFlight['airline_name']) ?></h3>
        <p>
            <strong>Route:</strong> <?= htmlspecialchars($selectedFlight['departure']) ?> to <?= htmlspecialchars($selectedFlight['destination']) ?><br>
            <strong>Departure:</strong> <?= htmlspecialchars($selectedFlight['departure_time']) ?> | 
            <strong>Arrival:</strong> <?= htmlspecialchars($selectedFlight['arrival_time']) ?><br>
            <strong>Price (One Way):</strong> ₦<?= htmlspecialchars($selectedFlight['price']) ?> | 
            <strong>Price (Returning):</strong> ₦<?= htmlspecialchars($selectedFlight['two_way_ticket']) ?><br>
            <strong>Available Seats:</strong> <?= htmlspecialchars($selectedFlight['seats']) ?>
        </p>

        <form method="POST" action="">
            <input type="hidden" name="flight_id" value="<?= $selectedFlight['id'] ?>">
            <label for="seat_count">Number of Seats (min 1, max 3):</label>
            <input type="number" id="seat_count" name="seats" value="1" min="1" max="3" onchange="calculateTotalPrice()" required><br>

            <label>Ticket Type:</label>
            <input type="radio" name="ticket_type" value="one_way" checked onchange="calculateTotalPrice()"> One Way
            <input type="radio" name="ticket_type" value="two_way" onchange="calculateTotalPrice()"> Returning<br><br>

            <!-- Hidden prices for dynamic calculation -->
            <input type="hidden" id="one_way_price" value="<?= htmlspecialchars($selectedFlight['price']) ?>">
            <input type="hidden" id="two_way_price" value="<?= htmlspecialchars($selectedFlight['two_way_ticket']) ?>">

            <p id="total_price">Total Price: ₦<?= htmlspecialchars($selectedFlight['price']) ?></p>

            <!-- Seat numbers will be auto-assigned -->
            <button type="submit" name="book_flight">Book Flight</button>
        </form>
    <?php endif; ?>

    <?php
    // Process booking if form is submitted:
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["book_flight"])) {
        $flight_id_post = intval($_POST["flight_id"]);
        $seats_requested = intval($_POST["seats"]);
        $ticket_type = $_POST["ticket_type"];
        $user_id = $_SESSION["user_id"];
        
        // Generate unique booking reference.
        $unique_id = "KING-AIR-" . sprintf("%08d", rand(0, 99999999));
        
        // Determine price field and calculate total price.
        $price_field = ($ticket_type === "two_way") ? "two_way_ticket" : "price";
        $price_query = $conn->prepare("SELECT $price_field FROM flights WHERE id = ?");
        $price_query->bind_param("i", $flight_id_post);
        $price_query->execute();
        $price_result = $price_query->get_result();
        $price_row = $price_result->fetch_assoc();
        $ticket_price = floatval($price_row[$price_field]);
        $total_price = $ticket_price * $seats_requested;
        
        // --- Auto-generate Seat Numbers ---
        $assignedSeats = [];
        $seatQuery = $conn->prepare("SELECT seat_number FROM bookings WHERE flight_id = ? AND status <> 'ticket canceled'");
        $seatQuery->bind_param("i", $flight_id_post);
        $seatQuery->execute();
        $seatResult = $seatQuery->get_result();
        while ($seatRow = $seatResult->fetch_assoc()) {
            $seatsArray = explode(",", $seatRow['seat_number']);
            foreach ($seatsArray as $seat) {
                $seatNum = intval(trim(substr($seat, 1)));
                if ($seatNum > 0) {
                    $assignedSeats[] = $seatNum;
                }
            }
        }
        sort($assignedSeats);
        $newSeatNumbers = [];
        $nextSeatNumber = 1;
        for ($i = 0; $i < $seats_requested; $i++) {
            while (in_array($nextSeatNumber, $assignedSeats)) {
                $nextSeatNumber++;
            }
            $newSeatNumbers[] = "A" . $nextSeatNumber;
            $assignedSeats[] = $nextSeatNumber;
            $nextSeatNumber++;
        }
        $seat_numbers = implode(",", $newSeatNumbers);
        // --- End Auto-generate Seat Numbers ---
        
        // Insert booking record. (Ensure bookings table has columns: user_id, flight_id, seat_number, ticket_type, price, booking_date, unique_id, status)
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, flight_id, seat_number, ticket_type, price, booking_date, unique_id, status) VALUES (?, ?, ?, ?, ?, NOW(), ?, 'pending')");
        $stmt->bind_param("iissds", $user_id, $flight_id_post, $seat_numbers, $ticket_type, $total_price, $unique_id);
        
        // Update flight seats (reduce available seats).
        $updateStmt = $conn->prepare("UPDATE flights SET seats = seats - ? WHERE id = ?");
        $updateStmt->bind_param("ii", $seats_requested, $flight_id_post);
        
        if ($stmt->execute() && $updateStmt->execute()) {
            echo "<p style='color:green;'>Booking successful! Your booking reference is <strong>$unique_id</strong> and your seats: <strong>$seat_numbers</strong>. Total Price: ₦" . number_format($total_price, 2) . ".</p>";
            echo "<p><a href='booking_history.php'>View Booking History & Receipt</a></p>";
            exit();
        } else {
            echo "<p style='color:red;'>Error processing booking: " . $conn->error . "</p>";
        }
    }
    ?>
</div>
</body>
</html>
