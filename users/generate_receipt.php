<?php
session_start();
require_once "../config/database.php";
require_once "../fpdf/fpdf.php"; // Adjust the path if necessary

if (!isset($_GET['booking_id'])) {
    die("Invalid request.");
}

$booking_id = (int) $_GET['booking_id'];

// Fetch booking details including the unique serial number (unique_id)
$sql = "SELECT 
            bookings.id, 
            bookings.unique_id,
            bookings.booking_date, 
            bookings.status, 
            flights.flight_number, 
            flights.airline_name, 
            flights.departure, 
            flights.destination, 
            flights.departure_time,
            flights.arrival_time,
            bookings.price,
            bookings.seat_number 
        FROM bookings 
        JOIN flights ON bookings.flight_id = flights.id 
        WHERE bookings.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Booking not found.");
}

$row = $result->fetch_assoc();

// Generate PDF receipt using FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Header
$pdf->SetFont("Arial", "B", 16);
$pdf->Cell(190, 10, "KingAirs Booking Receipt", 0, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "", 12);

// Display Unique Serial Number
$pdf->Cell(50, 10, "Serial Number: ", 0, 0);
$pdf->Cell(0, 10, $row['unique_id'], 0, 1);

// Booking ID
$pdf->Cell(50, 10, "Booking ID: ", 0, 0);
$pdf->Cell(0, 10, $row['id'], 0, 1);

// Flight details
$pdf->Cell(50, 10, "Flight Number: ", 0, 0);
$pdf->Cell(0, 10, $row['flight_number'], 0, 1);

$pdf->Cell(50, 10, "Airline: ", 0, 0);
$pdf->Cell(0, 10, $row['airline_name'], 0, 1);

// Route
$pdf->Cell(50, 10, "Route: ", 0, 0);
$pdf->Cell(0, 10, $row['departure'] . " to " . $row['destination'], 0, 1);


//Flight Schedule
$pdf->Cell(50, 10, "Flight Schedule: ", 0, 0);
$pdf->Cell(0, 10, $row['departure_time'] . " to " . $row['arrival_time'], 0, 1);


// Seat Number
$pdf->Cell(50, 10, "Seat Number: ", 0, 0);
$pdf->Cell(0, 10, $row['seat_number'], 0, 1);

// Booking Date
$pdf->Cell(50, 10, "Booking Date: ", 0, 0);
$pdf->Cell(0, 10, $row['booking_date'], 0, 1);

// Status
$pdf->Cell(50, 10, "Status: ", 0, 0);
$pdf->Cell(0, 10, $row['status'], 0, 1);

$pdf->Cell(50, 10, "Total Price: ", 0, 0);
$pdf->Cell(0, 10, "$" . number_format($row['price'], 2), 0, 1);

$pdf->Ln(10);
$pdf->Cell(190, 10, "Thank you for booking with KingAirs!", 0, 1, "C");

// Output the PDF and force download
$pdf->Output("D", "BookingReceipt_{$booking_id}.pdf");
exit();
?>
