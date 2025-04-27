<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<h2>Welcome to KingAirs</h2>
<a href="flight_info.php">Search Flight info</a>
<p><a href="book_flight.php">check flight</a></p></p>
<p><a href="search_flights.php">search flight</a></p></p>
<p><a href="book_flight.php">Book a Flight</a></p>
<p><a href="booking_history.php">View Booking History</a></p>
<p><a href="../logout.php">Logout</a></p>
