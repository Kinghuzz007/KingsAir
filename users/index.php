<?php

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>King's Air - Fly with Royalty <?php if ($departure || $destination) echo " - " . htmlspecialchars($departure) . " to " . htmlspecialchars($destination); ?></title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Open+Sans&display=swap" rel="stylesheet">
</head>
<style>
/* Flight Article code */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Open+Sans&display=swap');
.flight-article {
    text-align: center;
    padding: 25px 0;
    margin: 30px 0;
}

.flight-notice {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.flight-info {
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin: auto;
}

.flight-info i {
    font-size: 16px;
    color: darkgreen;
    margin: 0 5px;
}
.flight-notice a {
    font-weight: bold;
    text-decoration: none;
    margin: 10px;
    color: black;
    font-size: 14px;
}

.flight-adv {
    position: relative;
}

.flight-adv img {
    width: 100%;
    height: 350px;
}

.flight-details {
    position: absolute;
    bottom: 10px;
    color: white;
    text-align: left;
    padding: 6px;
}

.flight-details h1 {
    font-family: 'Orbitron', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: 1px;
    margin: 0;
    color: orangered;
}

.flight-details p {
    font-family: 'Open Sans', sans-serif;
    font-size: 0.8rem;
    line-height: 1.6;
}

marquee {
    padding: 5px 0;
}
 
@media (min-width: 800px) {
    .flight-article {
        padding: 10px 45px;
    }
    .flight-info {
        flex-direction: row;
    }
    .flight-details h1 {
        font-size: 3rem;
    }
    .flight-details p {
        font-size: 1rem;
    }
    .flight-notice a {
        margin: 0 10px;
    }
    .flight-adv img {
        height: 450px;
    }
}

@media (min-width: 950px) {
    marquee {
        width: 60%;
    }

    .flight-info {
        width: 40%;
    }
}


/* Hotel section */
.hotel {
    padding: 15px;
    margin-top: 25px;
    background-color: white;
}

.hotel-list {
    gap: 30px;
    display: flex;
    overflow-x: auto;
    scroll-behavior: smooth;
    white-space: nowrap;
    padding: 20px 10px;
}

.hotel-list::-webkit-scrollbar {
  height: 10px;
}

.hotel-list::-webkit-scrollbar-thumb {
  background-color: orange;
  border-radius: 10px;
}

.hotel-list::-webkit-scrollbar-track {
  background-color: #f0f0f0;
  border-radius: 10px;
}

/* Optional scrollbar styling for Firefox */
.hotel-list {
  scrollbar-width: thin;
  scrollbar-color: #999 #f0f0f0;
}

.hotel-list-view .h-location {
    font-size: 12px;
}

.hotel-list-view .h-name {
    font-weight: bold;
    font-size: 14px;
}

.hotel-list-view .off {
    position: absolute;
    top: 119px;
    background: orangered;
    right: 10px;
    padding: 2px;
    font-size: 18px;
    color: white;
    font-weight: bold;
}

.hotel-details {
    padding: 0 5px;
}

.hotel-list span {
    color: brown;
}

.hotel-price-details {
    text-align: right;
    padding: 0 5px;
}

.hotel-price-details p {
        display: flex;
        font-weight: bold;
        justify-content: flex-end;
        color: orangered;
        font-size: 24px;
        letter-spacing: 1px;
    
}

.hotel-price-details span {
    color: black;
    font-size: 12px;
}

.hotel-price-details i {
    color: orangered;
    font-size: 12px;
}
.hotel-list-view {
  flex: 0 0 auto;
  width: 220px;
  box-shadow: 1px 1px 8px rgba(0, 0, 0, 0.4);
  position: relative;
  transition: .4s ease-in;
  cursor: pointer;
}

.hotel-list-view:hover {
    transform: scale(1.05);
}


.hotel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
}

.hotel-head h2 {
    margin: 0;
}

/* .hotel-list-view {
    background-color: orangered;
    width: 220px;
} */

.hotel-head a {
    text-decoration: none;
    color: orangered;
    font-size: 15px;
}
.newsletter {
    background-color: rgb(202, 97, 59);
    padding: 30px 10px;
}

.newletter-subscription {
    display: flex;
    flex-direction: row;
    background-color: white;
}

.newletter-subscription a {
    text-decoration: none;
    color: orangered;
    line-height: 3;
}

.newletter-subscription p {
    font-size: 14px;
    padding: 5px 10px;
}

.newsletter-details {
        display: flex;
        justify-content: center;
        gap: 15px;
        flex-direction: column;
    
}


@media (min-width: 800px) {
    .hotel {
        padding: 15px 40px;
    }

    .newsletter-details {
        flex-direction: row;
    }

    .newsletter {
        padding: 20px 45px;
    }
}




</style>
<body>
<section class="navbar header">
         <img src="../img/King’s Air logo.png" alt="King's Air Logo" width="80" height="50">
        <div class="menu-icon" id="menu-icon">☰</div>
        <nav id="nav-links">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="book_flight.php">Flights</a></li>
                <li><a href="#">Destinations</a></li>
                <li><a href="#ContactUs">Contact</a></li>
            </ul>
        </nav>
</section>

<script>
    const menuIcon = document.getElementById("menu-icon");
    const navLinks = document.getElementById("nav-links");

    menuIcon.addEventListener("click", () => {
        navLinks.classList.toggle("show");
        menuIcon.textContent = navLinks.classList.contains("show") ? "✖" : "☰";
        menuIcon.classList.toggle("active");

        // Adding delay to each link for a smooth effect
        let links = navLinks.querySelectorAll("li");
        links.forEach((link, index) => {
            if (navLinks.classList.contains("show")) {
                setTimeout(() => {
                    link.style.opacity = "1";
                    link.style.transform = "translateY(0)";
                }, index * 100); // Delay each link slightly
            } else {
                link.style.transform = "translateY(-10px)";
            }
        });
    });
</script>

<section class="flight">
        <div class="description">
            <p>search flights</p>
            <small>NB: All payment processed on this platform is a fake payment. for more understanding <button id="openModal">click here</button></small>
        </div>
        <div class="payment-modal" id="paymentModal">
            <div class="payment-modal-content">
                <span id="closeModal">&times;</span>
                <h1>King</h1>
            </div>
        </div>
        <script src="javascript">
            var btn = document.getElementById("openModal")
            var modal = document.getElementById("paymentModal")
            var span = document.getElementById("closeModal");

            btn.onclick = function() {
                modal.style.display = "block"
            }

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
            }

        </script>
        <div class="container">
            <form method="GET" action="" id="flightForm" style="display: flex; gap: 10px;">
            <input type="text" name="departure" placeholder="Departure Location" required value="<?php echo htmlspecialchars($departure); ?>">
            <input type="text" name="destination" placeholder="Destination" value="<?php echo htmlspecialchars($destination); ?>">
            <select name="travel-option" id="">
                    <option value="one-way">One Way</option>
                    <option value="two-way">Return Ticket</option>
                </select>
                <button type="submit">Search Flights</button>
            </form>
        </div>
</section>
  <?php if(isset($_GET['departure']) && isset($_GET['destination'])): ?>
    <div class="f-summary">
    <?php if(count($flights) > 0): ?>
      
        <?php foreach($flights as $flight): ?>
         <div class="travel-search">
            <div class="index-search-details">
                <div class="airplane-container">
                    <div class="airplane-icon"><a href="#"><i class="fas fa-plane"></i></a></div>
                    <div class="icon-item"></div>
                </div>
                <div class="search-destination">
                    <p><?= htmlspecialchars($flight['departure']) ?></p>
                    <p><?= htmlspecialchars($flight['destination']) ?></p>
                </div>
            </div>
            <div class="search-information">
                <div class="departure">
                    <p>Depature</p>
                    <small><?= htmlspecialchars($flight['departure_time']) ?></small>
                </div>
                <div class="arrival">
                    <p>Arrival</p>
                    <small><?= htmlspecialchars($flight['arrival_time']) ?></small>
                </div>
            </div>
            <div class="prices">
                <div class="tabs">
                    <div id="tab1" class="tab active"><p>Price (one-way):</p></div>
                    <div id="tab2" class="tab"><p>Price (Returning):</p></div>
                </div> 
                <div id="content1" class="content">
                    <p>Price (One-Way): $<?= htmlspecialchars($flight['price']) ?></p>
                </div>
                <div id="content2" class="content hidden">
                    <p>Price(Returning): $<?= htmlspecialchars($flight['two_way_ticket']) ?></p>
                </div>
            </div>
            <div class="button">
                    <button class="booking-button"><a href="seat_selection.php?flight_id=<?= htmlspecialchars($flight['id']) ?>">Book Flight</a></button>
            </div>
         </div>
         <script>
    const tab1 = document.getElementById('tab1');
    const tab2 = document.getElementById('tab2');
    const content1 = document.getElementById('content1');
    const content2 = document.getElementById('content2');

    tab1.addEventListener('click', () => {
      tab1.classList.add('active');
      tab2.classList.remove('active');
      content1.classList.remove('hidden');
      content2.classList.add('hidden');
    });

    tab2.addEventListener('click', () => {
      tab2.classList.add('active');
      tab1.classList.remove('active');
      content2.classList.remove('hidden');
      content1.classList.add('hidden');
    });
  </script>
        <?php endforeach; ?>
    <?php else: ?>
      <p>No flights found for this route.</p>
    <?php endif; ?>
    </div>
  <?php endif; ?>

<section class="Flight-Arcticle">
    <div class="flight-article">
        <div class="flight-notice">
            <marquee style="background-color: #e5faff;">
                <b style="color: red;">travel alert:</b>
                New Circular notice from Ministry of Land, 
                infastructure, transport and Tourism
            </marquee>
            <div class="flight-info">
                <a href="booking_history.php"><i class="fas fa-calendar-check"></i> Manage booking</a>
                <a href="flight_info.php"><i class="fas fa-plane-arrival"></i> Flight Status</a>
                <a href="book_flight.php"><i class="fas fa-id-card"></i> Checkin</a>
            </div>
        </div>
        <div class="flight-adv">
            <img src="kingair.jpg" alt="" >
            <div class="flight-details">
                <h1>Experience Luxury in the Skies</h1>
                <p>Book your flight with King's Air and travel like royalty.</p>
            </div>
        </div>
    </div>
</section>

<section class="hotel">
        <div class="hotel-head">
            <h2>Hotels</h2>
            <a href="#">View all Hotels</a>
        </div>
        <div class="hotel-list">
            <div class="hotel-list-view">
                <img src="flightwing.jpg" alt="" width="220" height="150">
                <p class="off">29% Off</p>
                <div class="hotel-details">
                    <p class="h-location">Taipei</p>
                    <p class="h-name">Polais de China Hotel</p>
                    <span>5 Stars</span>
                </div>
                <div class="hotel-price-details">
                    <span>*Per Night</span>
                    <p><i>&#8358</i> 100,000</p>
                </div>
            </div>
            <div class="hotel-list-view">
               <img src="flightwing.jpg" alt="" width="220" height="150">
               <p class="off">29% Off</p>
                <div class="hotel-details">
                    <p class="h-location">Taipei</p>
                    <p class="h-name">Polais de China Hotel</p>
                    <span>5 Stars</span>
                </div>
                <div class="hotel-price-details">
                    <span>*Per Night</span>
                    <p><i>&#8358</i> 100,000</p>
                </div>
            </div>
            <div class="hotel-list-view">
               <img src="flightwing.jpg" alt="" width="220" height="150">
               <p class="off">29% Off</p>
                <div class="hotel-details">
                    <p class="h-location">Taipei</p>
                    <p class="h-name">Polais de China Hotel</p>
                    <span>5 Stars</span>
                </div>
                <div class="hotel-price-details">
                    <span>*Per Night</span>
                    <p><i>&#8358</i> 100,000</p>
                </div>
            </div>
            <div class="hotel-list-view">
               <img src="flightwing.jpg" alt="" width="220" height="150">
               <p class="off">29% Off</p>
                <div class="hotel-details">
                    <p class="h-location">Taipei</p>
                    <p class="h-name">Polais de China Hotel</p>
                    <span>5 Stars</span>
                </div>
                <div class="hotel-price-details">
                    <span>*Per Night</span>
                    <p><i>&#8358</i> 100,000</p>
                </div>
            </div>
            <div class="hotel-list-view">
               <img src="flightwing.jpg" alt="" width="220" height="150">
               <p class="off">29% Off</p>
                <div class="hotel-details">
                    <p class="h-location">Taipei</p>
                    <p class="h-name">Polais de China Hotel</p>
                    <span>5 Stars</span>
                </div>
                <div class="hotel-price-details">
                    <span>*Per Night</span>
                    <p><i>&#8358</i> 100,000</p>
                </div>
            </div>
            <div class="hotel-list-view">
               <img src="flightwing.jpg" alt="" width="220" height="150">
               <p class="off">29% Off</p>
                <div class="hotel-details">
                    <p class="h-location">Taipei</p>
                    <p class="h-name">Polais de China Hotel</p>
                    <span>5 Stars</span>
                </div>
                <div class="hotel-price-details">
                    <span>*Per Night</span>
                    <p><i>&#8358</i> 100,000</p>
                </div>
            </div>
            <div class="hotel-list-view">
               <img src="flightwing.jpg" alt="" width="220" height="150">
               <p class="off">29% Off</p>
                <div class="hotel-details">
                    <p class="h-location">Taipei</p>
                    <p class="h-name">Polais de China Hotel</p>
                    <span>5 Stars</span>
                </div>
                <div class="hotel-price-details">
                    <span>*Per Night</span>
                    <p><i>&#8358</i> 100,000</p>
                </div>
            </div>
        </div>
        <div class="hotel-details">
            <small>Hotel special prices are per room, per night. select a special offer for more information,
                including travel period and occupancy period
            </small>
        </div>
    </section>

    <section class="newsletter">
        <div class="newsletter-details">
            <div class="newletter-subscription">
                <img src="flightwing.jpg" alt="" width="120">
                <p>Get the Latest deals and special offers delivered straight to your inbox <br>
                    <a href="#">Sign up to our newsletter</a></p>
            </div>
            <div class="newletter-subscription">
                <img src="flightwing.jpg" alt="" width="120">
                <p>Report any inconveniences with our airline or any of our staff <br>
                    <a href="#">Reach out to us</a></p>
            </div>
        </div>
    </section>



</body>
</html>
<!-- <h2>Welcome to KingAirs</h2>
<a href="flight_info.php">Search Flight info</a>
<p><a href="book_flight.php">check flight</a></p></p>
<p><a href="search_flights.php">search flight</a></p></p>
<p><a href="book_flight.php">Book a Flight</a></p>
<p><a href="booking_history.php">View Booking History</a></p>
<p><a href="../logout.php">Logout</a></p> -->
