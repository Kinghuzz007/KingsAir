<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["booking_id"])) {
    die("Booking ID is required.");
}

$booking_id = (int) $_GET["booking_id"];
$user_id = $_SESSION["user_id"];

// Retrieve booking details first
$query = "SELECT flight_id, status FROM bookings WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found or you are not authorized to cancel it.");
}

$booking = $result->fetch_assoc();
$currentStatus = $booking["status"];
$flight_id = $booking["flight_id"];

if ($currentStatus === "ticket canceled") {
    // Already canceled
    header("Location: booking_history.php?message=This booking has already been canceled.");
    exit();
}

// Start a transaction to update booking and restore seat atomically
$conn->begin_transaction();

try {
    // Update booking status only if it's not already 'ticket canceled'
    $updateQuery = "UPDATE bookings SET status = 'ticket canceled' WHERE id = ? AND user_id = ? AND status <> 'ticket canceled'";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $booking_id, $user_id);
    $updateStmt->execute();

    if ($updateStmt->affected_rows === 0) {
        throw new Exception("Booking status update failed. It might have already been canceled.");
    }
    
    // Restore the seat count by increasing available seats by 1.
    // Change 'available_seats' to 'seats' if that is your column name.
    $updateFlight = $conn->prepare("UPDATE flights SET seats = seats + 1 WHERE id = ?");
    $updateFlight->bind_param("i", $flight_id);
    $updateFlight->execute();

    $conn->commit();
    header("Location: booking_history.php?message=Booking canceled successfully.");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Error during cancellation: " . $e->getMessage());
}
?>
