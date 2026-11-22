<?php
// 1. backend ෆෝල්ඩරයේ ඇති My_db.php සම්බන්ධ කිරීම
include '../backend/My_db.php';

// 2. Cancellation Processing: පරිශීලකයා Cancel බොත්තම එබූ විට ක්‍රියාත්මක වන කොටස
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);
    try {
        // බුකින් එක Pending නම් පමණක් එය Cancelled කිරීමට ඉඩ දීම (Server-side validation)
        $update_stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ? AND booking_status = 'Pending'");
        $update_stmt->execute([$cancel_id]);
        
        // සාර්ථකව යාවත්කාලීන වූ පසු පිටුව Refresh කිරීම
        header("Location: my-bookings.php");
        exit;
    } catch (PDOException $e) {
        $error_msg = "මකාදැමීම අසාර්ථකයි: " . $e->getMessage();
    }
}

try {
    // 3. ඩේටාබේස් එකෙන් බුකින් විස්තර කාමරයේ නමත් සමඟ ලබා ගැනීම (SQL JOIN)
    // සටහන: දැනට Session Login එකක් නැති නිසා සියලුම බුකින් පෙන්වයි.
    $query = "SELECT b.booking_id, r.room_name, b.check_in, b.check_out, b.total_amount, b.booking_status 
              FROM bookings b 
              JOIN rooms r ON b.room_id = r.room_id 
              ORDER BY b.booking_date DESC";
              
    $stmt = $pdo->query($query);
    $my_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("දත්ත ලබා ගැනීමට නොහැකි විය: " . $e->getMessage());
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
        /* Status Badge සඳහා පොදු හැඩතල (අවශ්‍ය නම් CSS එකට දමන්න) */
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
            <p class="dashboard-subtext">ඔබ විසින් මෙතෙක් වෙන්කරවා ගත් කාමර සහ ඒවායේ වත්මන් තත්ත්වයන් (Status) මෙතැනින් පරීක්ෂා කරන්න.</p>
            
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
                                    <!-- Booking ID එක පද්ධතියට ගැළපෙන සේ පෙන්වීම -->
                                    <td data-label="Booking ID">#VP-<?php echo $booking['booking_id']; ?></td>
                                    
                                    <!-- කාමරයේ නම -->
                                    <td data-label="Room Name"><strong><?php echo htmlspecialchars($booking['room_name']); ?></strong></td>
                                    
                                    <!-- දින ගණන් -->
                                    <td data-label="Check-in"><?php echo $booking['check_in']; ?></td>
                                    <td data-label="Check-out"><?php echo $booking['check_out']; ?></td>
                                    
                                    <!-- මුළු මුදල -->
                                    <td data-label="Total Price" class="price-col">$<?php echo htmlspecialchars($booking['total_amount']); ?></td>
                                    
                                    <!-- Status එක අනුව Badge එකේ CSS Class එක මාරු කිරීම -->
                                    <td data-label="Status">
                                        <?php 
                                        $status = $booking['booking_status'];
                                        $badge_class = 'status-pending';
                                        if ($status == 'Confirmed') $badge_class = 'status-confirmed';
                                        if ($status == 'Cancelled') $badge_class = 'status-cancelled';
                                        ?>
                                        <span class="status-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                    </td>
                                    
                                    <!-- Action: Pending නම් පමණක් Cancel බොත්තම පෙන්වීම -->
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
                                <td colspan="7" style="text-align: center; padding: 20px; color: #666;">ඔබ තවමත් කිසිදු කාමරයක් වෙන්කරවා ගෙන නැත.</td>
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

    <!-- Cancellation තහවුරු කිරීමේ ජාවාස්ක්‍රිප්ට් කේතය -->
    <script>
    function confirmCancellation(bookingId) {
        if (confirm("ඔබට ස්ථිරවම මෙම වෙන්කිරීම අවලංගු (Cancel) කිරීමට අවශ්‍යද?")) {
            // ඔව් නම්, අදාළ ID එක URL එක හරහා පිටුවටම යවා මකාදැමීම සිදු කරයි
            window.location.href = "my-bookings.php?cancel_id=" + bookingId;
        }
    }
    </script>
</body>
</html>
