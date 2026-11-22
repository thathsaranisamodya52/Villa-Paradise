<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database සම්බන්ධ කිරීම
include '../backend/My_db.php';

// පරිශීලකයා Search Form එකෙන් එවූ දත්ත ලබා ගැනීම
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 0;

$error_msg = "";
$rooms = [];

try {
    // පරිශීලකයා දිනයන් සහ අමුත්තන් ගණන තෝරා Search කර ඇත්නම්
    if (!empty($checkin) && !empty($checkout) && $guests > 0) {
        if ($checkout <= $checkin) {
            $error_msg = "Check-out දිනය අනිවාර්යයෙන්ම Check-in දිනයට පසු දිනයක් විය යුතුය.";
        } else {
            // 1. Guests ගණනට ගැළපෙන සහ 'available' තත්ත්වයේ ඇති කාමර පරීක්ෂා කිරීම
            // 2. එම දිනවලදී දැනටමත් Confirmed booking එකක් නොමැති කාමර පමණක් තෝරා ගැනීම
            $sql = "SELECT * FROM rooms r
                    WHERE LOWER(r.room_status) = 'available' 
                    AND r.max_guests >= ? 
                    AND NOT EXISTS (
                         SELECT 1 FROM bookings b 
                        WHERE b.room_id = r.room_id 
                        AND LOWER(TRIM(b.booking_status)) = 'confirmed' 
                        AND b.check_in < ? 
                         AND b.check_out > ?
                    )
                    ORDER BY r.room_id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$guests, $checkin, $checkout]);
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        // Search කර නැති සාමාන්‍ය අවස්ථාවේදී දැනට ඇති සියලුම Available කාමර පෙන්වීම
        $stmt = $pdo->query("SELECT * FROM rooms WHERE room_status = 'available' ORDER BY room_id DESC");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error_msg = "දත්ත ලබා ගැනීමේ දෝෂයකි: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - Room Availability Search</title>
    <link rel="stylesheet" href="search.css?v=1.1">
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

    <!-- Main Content Container -->
    <main class="search-page-container">
        
        <!-- 3. Room Availability Search Form -->
        <section class="search-card">
            <h2>Room Search & Filter</h2>
            <p class="search-subtext">ඇතුළත් කරන දින අනුව ඔබට ගැළපෙනම සුඛෝපභෝගී කාමර වෙන්කරවා ගන්න.</p>
            
            <form action="search.php" method="GET" class="availability-form">
                <div class="form-grid">
                    <!-- Check-in Date -->
                    <div class="form-group">
                        <label for="checkin">Check-in Date (පැමිණෙන දිනය)</label>
                        <input type="date" id="checkin" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <!-- Check-out Date -->
                    <div class="form-group">
                        <label for="checkout">Check-out Date (පිටවන දිනය)</label>
                        <input type="date" id="checkout" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>

                    <!-- Number of Guests -->
                    <div class="form-group">
                        <label for="guests">Number of Guests (අමුත්තන් ගණන)</label>
                        <select id="guests" name="guests" required>
                            <option value="" disabled <?php echo $guests == 0 ? 'selected' : ''; ?>>තෝරන්න...</option>
                            <option value="1" <?php echo $guests == 1 ? 'selected' : ''; ?>>1 Adult</option>
                            <option value="2" <?php echo $guests == 2 ? 'selected' : ''; ?>>2 Adults</option>
                            <option value="3" <?php echo $guests == 3 ? 'selected' : ''; ?>>3 Adults</option>
                            <option value="4" <?php echo $guests == 4 ? 'selected' : ''; ?>>4 Adults or Family</option>
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <button type="submit" class="btn-search">Search Available Rooms</button>
            </form>
        </section>

        <!-- Available Rooms Results (කාමර පෙන්වන කොටස) -->
        <section class="results-section">
            <h3>
                <?php echo (!empty($checkin) && !empty($checkout)) ? "Available Rooms for Selected Dates" : "Available Rooms (තිබෙන කාමර)"; ?>
            </h3>

            <?php if (!empty($error_msg)): ?>
                <p style="color: #c81e1e; background: #fde8e8; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($error_msg); ?>
                </p>
            <?php endif; ?>
            
            <div class="results-grid">
                <?php if (count($rooms) > 0): ?>
                    <?php foreach ($rooms as $room): ?>
                        <?php 
                            // Database එකේ ඇති පින්තූරය ලබා ගැනීම
                            $room_img = !empty($room['image']) ? $room['image'] : ($room['room_type'] == 'Deluxe' ? 'room.webp' : 'family.jpg');
                            
                            // Select Room ක්ලික් කළ විට booking.php වෙත යන URL එක සැකසීම
                            $book_url = "booking.php?room_id=" . $room['room_id'];
                            if (!empty($checkin) && !empty($checkout)) {
                                $book_url .= "&checkin=" . urlencode($checkin) . "&checkout=" . urlencode($checkout);
                            }
                        ?>
                        <div class="result-item">
                            <div class="result-img">
                                <img src="<?php echo htmlspecialchars($room_img); ?>" alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                            </div>
                            <div class="result-info">
                                <h4><?php echo htmlspecialchars($room['room_name']); ?></h4>
                                <p><?php echo htmlspecialchars($room['description']); ?></p>
                                <p style="font-size: 13px; color: #777; margin-top: 5px;">👥 Max Guests: <?php echo $room['max_guests']; ?> Adults</p>
                                <span class="price-tag">$<?php echo htmlspecialchars($room['price_per_night']); ?> / Night</span>
                                <a href="<?php echo $book_url; ?>" class="btn-select">Select Room</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%; padding: 20px; color: #666; font-size: 16px;">
                        තෝරාගත් දින හෝ අමුත්තන් ගණන සඳහා දැනට හිස් කාමර නොමැත. කරුණාකර වෙනත් දිනයක් උත්සාහ කරන්න.
                    </p>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 Villa Paradise. All Rights Reserved.</p>
    </footer>

</body>
</html>
