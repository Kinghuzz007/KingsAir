<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

// Initialize search term and results array.
$searchQuery = "";
$searchResults = [];

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = trim($_GET['search']);
    
    // Query to search by user's name, email, unique_id (from bookings), airline name, or flight number
    $sql = "SELECT 
                users.id, 
                users.name, 
                users.email, 
                b.unique_id, 
                f.flight_number, 
                f.airline_name, 
                f.departure, 
                f.arrival_time, 
                b.status, 
                COUNT(b.id) AS total_bookings 
            FROM users 
            JOIN bookings b ON users.id = b.user_id
            JOIN flights f ON b.flight_id = f.id
            WHERE users.name LIKE ? 
               OR users.email LIKE ? 
               OR b.unique_id LIKE ? 
               OR f.airline_name LIKE ? 
               OR f.flight_number LIKE ?
            GROUP BY users.id, b.unique_id, f.flight_number, f.airline_name, f.departure, f.arrival_time, b.status";
    
    $stmt = $conn->prepare($sql);
    $param = "%" . $searchQuery . "%";
    $stmt->bind_param("sssss", $param, $param, $param, $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        Admin Dashboard - KingAirs<?php if (!empty($searchQuery)) echo " - Search: " . htmlspecialchars($searchQuery); ?>
    </title>
    <style>
       body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        header {
            text-align: center;
            margin-bottom: 10px;
        }
        nav {
            text-align: center;
            margin-bottom: 10px;
        }
        nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        hr {
            margin-bottom: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 10px;
            width: 70%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 15px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        .results-message {
            text-align: center;
            font-size: 1.1em;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        p {
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h2>Admin Dashboard</h2>
    </header>
    
    <nav>
        <a href="flights.php">Manage Flights</a>
        <a href="users.php">Manage Users</a>
        <a href="bookings.php">Manage Bookings</a>
        <a href="logout.php">Logout</a>
    </nav>
    
    <hr>
    
    <p>Welcome, Admin! Use the navigation above to manage the system.</p>
    
    <div class="container">
        <h2>Search</h2>
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by Name, Email, Unique ID, Flight Name, or Flight Number" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </form>
        
        <?php if (isset($_GET['search'])) { ?>
            <div class="results-message">
                Search results for: <strong><?php echo htmlspecialchars($searchQuery); ?></strong>
            </div>
            <?php if (!empty($searchResults)) { ?>
                <table>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Unique ID</th>
                        <th>Flight Number</th>
                        <th>Airline</th>
                        <th>Route</th>
                        <th>Status</th>
                        <th>Total Bookings</th>
                    </tr>
                    <?php foreach ($searchResults as $result) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['id']); ?></td>
                            <td><?php echo htmlspecialchars($result['name']); ?></td>
                            <td><?php echo htmlspecialchars($result['email']); ?></td>
                            <td><?php echo htmlspecialchars($result['unique_id']); ?></td>
                            <td><?php echo htmlspecialchars($result['flight_number']); ?></td>
                            <td><?php echo htmlspecialchars($result['airline_name']); ?></td>
                            <td><?php echo htmlspecialchars($result['departure']) . " to " . htmlspecialchars($result['arrival_time']); ?></td>
                            <td><?php echo htmlspecialchars($result['status']); ?></td>
                            <td><?php echo htmlspecialchars($result['total_bookings']); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p style="color:red; text-align:center;">No results found.</p>
            <?php } ?>
        <?php } ?>
    </div>
</body>
</html>
