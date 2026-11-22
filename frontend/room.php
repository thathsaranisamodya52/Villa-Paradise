<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// backend ෆෝල්ඩරයේ ඇති My_db.php සම්බන්ධ කිරීම
include '../backend/My_db.php';

try {
    // දත්ත ගබඩාවෙන් දැනට ලබාගත හැකි (available) සියලුම කාමර ලබා ගැනීම
    $stmt = $pdo->query("SELECT * FROM rooms WHERE room_status = 'available' ORDER BY room_id DESC");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("දත්ත ලබා ගැනීමට නොහැකි විය: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - Rooms & Accommodation</title>
    <link rel="stylesheet" href="style.css">
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

    <!-- Rooms Page Heading -->
    <section class="rooms-hero">
        <h1>Rooms & Accommodation</h1>
        <p>ඔබගේ නිවාඩුව උපරිම සුඛෝපභෝගීත්වයෙන් ගත කිරීමට අප සතු කාමර පහසුකම්</p>
    </section>

    <!-- Rooms List Section -->
    <section class="rooms-container">
        
        <?php if (count($rooms) > 0): ?>
            <?php foreach ($rooms as $room): ?>
                <?php 
                    // Database එකේ image එකක් ඇත්නම් එය ගැනීම, නැතිනම් default පින්තූරයක් යෙදීම
                    $room_img = !empty($room['image']) ? $room['image'] : ($room['room_type'] == 'Deluxe' ? 'room.webp' : 'family.jpg');
                ?>
                <div class="room-card">
                    <div class="room-image">
                        <img src="<?php echo htmlspecialchars($room_img); ?>" alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                    </div>
                    <div class="room-details">
                        <h2><?php echo htmlspecialchars($room['room_name']); ?></h2>
                        
                        <p class="room-desc"><?php echo htmlspecialchars($room['description']); ?></p>
                        
                        <div class="room-info">
                            <strong>Facilities:</strong> Free Wi-Fi, AC, Private Balcony, Cable TV
                        </div>
                        
                        <div class="room-meta">
                            <span class="guests">👥 Max Guests: <?php echo htmlspecialchars($room['max_guests']); ?> Adults</span>
                            <span class="price">$<?php echo htmlspecialchars($room['price_per_night']); ?> <span>/ Night</span></span>
                        </div>
                        
                        <a href="booking.php?room_id=<?php echo $room['room_id']; ?>" class="btn-book-room">Book Room</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%; font-size: 18px; color: #666;">දැනට ලබාගත හැකි කාමර කිසිවක් නැත.</p>
        <?php endif; ?>

    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 Villa Paradise. All Rights Reserved.</p>
    </footer>

</body>
</html>
