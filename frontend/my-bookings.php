<?php

include '../backend/My_db.php';


if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);
    try {
        
        $update_stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ? AND booking_status = 'Pending'");
        $update_stmt->execute([$cancel_id]);
        
        
        header("Location: my-bookings.php");
        exit;
    } catch (PDOException $e) {
        $error_msg = "Cancellation failed: " . $e->getMessage();
    }
}

try {
    
    
    $query = "SELECT b.booking_id, r.room_name, b.check_in, b.check_out, b.total_amount, b.booking_status 
              FROM bookings b 
              JOIN rooms r ON b.room_id = r.room_id 
              ORDER BY b.booking_date DESC";
              
    $stmt = $pdo->query($query);
    $my_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Unable to retrieve data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - My Bookings</title>
    <link rel="stylesheet" href="my-bookings.css?v=1.1">
    <style>
        
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 13px; font-weight: bold; color: white; display: inline-block; }
        .status-confirmed { background-color: #28a745; }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-cancelled { background-color: #dc3545; }
        .btn-cancel { background-color: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-cancel:hover { background-color: #bd2130; }
    </style>
</head>
<body>

    <!-- Navigation Menu (මෙනුව) -->
    <header>
        <div class="logo">
            <a href="index.php">
                <img src="logo1.jpg" alt="Villa Paradise Logo">
                <span>Villa Paradise</span>
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#about">About Us</a></li>
                <li><a href="room.php">Facilities</a></li> 
                <li><a href="search.php">Book Now</a></li> 
                <li><a href="my-bookings.php">My Bookings</a></li> 
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php" style="color: #b85d19; font-weight: bold;">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php" style="color: #b85d19; font-weight: bold;">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Main Dashboard Container -->
    <main class="dashboard-container">
        <section class="dashboard-card">
            <h2>My Bookings</h2>
            <p class="dashboard-subtext">View your past and upcoming bookings along with their statuses.</p>
            
            <!-- Bookings Table Layout -->
            <div class="table-responsive">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Room Name</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($my_bookings) > 0): ?>
                            <?php foreach ($my_bookings as $booking): ?>
                                <tr>
                                    
                                    <td data-label="Booking ID">#VP-<?php echo $booking['booking_id']; ?></td>
                                    
                                    
                                    <td data-label="Room Name"><strong><?php echo htmlspecialchars($booking['room_name']); ?></strong></td>
                                    
                                    
                                    <td data-label="Check-in"><?php echo $booking['check_in']; ?></td>
                                    <td data-label="Check-out"><?php echo $booking['check_out']; ?></td>
                                    
                                    
                                    <td data-label="Total Price" class="price-col">$<?php echo htmlspecialchars($booking['total_amount']); ?></td>
                                    
                                    
                                    <td data-label="Status">
                                        <?php 
                                        $status = $booking['booking_status'];
                                        $badge_class = 'status-pending';
                                        if ($status == 'Confirmed') $badge_class = 'status-confirmed';
                                        if ($status == 'Cancelled') $badge_class = 'status-cancelled';
                                        ?>
                                        <span class="status-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                    </td>
                                    
                                    
                                    <td data-label="Action">
                                        <?php if ($status == 'Pending'): ?>
                                            <button class="btn-cancel" onclick="confirmCancellation(<?php echo $booking['booking_id']; ?>)">Cancel</button>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px; color: #666;">You have not made any room bookings yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 Villa Paradise. All Rights Reserved.</p>
    </footer>

    
    <script>
    function confirmCancellation(bookingId) {
        if (confirm("Are you sure you want to cancel this booking?")) {
            
            window.location.href = "my-bookings.php?cancel_id=" + bookingId;
        }
    }
    </script>
</body>
</html>
