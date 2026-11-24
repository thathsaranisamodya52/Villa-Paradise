<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../backend/My_db.php';


$selected_room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$pre_checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$pre_checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';

$success_message = false;
$error_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guest_name = trim($_POST['guest_name']);
    $guest_email = trim($_POST['guest_email']);
    $guest_phone = trim($_POST['guest_phone']);
    $room_id = intval($_POST['room_selection']);
    $check_in = $_POST['check_in_date'];
    $check_out = $_POST['check_out_date'];

    
    if (empty($guest_name) || empty($guest_email) || empty($guest_phone) || empty($room_id) || empty($check_in) || empty($check_out)) {
        $error_message = "Please fill in all the details.";
    } elseif (strtotime($check_in) >= strtotime($check_out)) {
        $error_message = "The check-out date must be after the check-in date.";
    } elseif (strtotime($check_in) < strtotime(date('Y-m-d'))) {
        $error_message = "You cannot book a room for a past date.";
    } else {
        try {
        
            $room_stmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE room_id = ? AND room_status = 'available'");
            $room_stmt->execute([$room_id]);
            $room_data = $room_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room_data) {
                $error_message = "The selected room is not available.";
            } else {
                
                $days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
                $total_amount = $days * $room_data['price_per_night'];

                
                $user_stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
                $user_stmt->execute([$guest_email]);
                $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $user_id = $user['user_id'];
                } else {
                    $hashed_password = password_hash("villa123", PASSWORD_DEFAULT); // Default Password
                    $ins_user = $pdo->prepare("INSERT INTO users (name, email, contact_number, password, role) VALUES (?, ?, ?, ?, 'guest')");
                    $ins_user->execute([$guest_name, $guest_email, $guest_phone, $hashed_password]);
                    $user_id = $pdo->lastInsertId();
                }

                
                $check_overlap = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND booking_status = 'Confirmed' AND NOT (check_out <= ? OR check_in >= ?)");
                $check_overlap->execute([$room_id, $check_in, $check_out]);
                
                if ($check_overlap->fetchColumn() > 0) {
                    $error_message = "Sorry, the selected room is not available for the chosen dates.";
                } else {
                    
                    $booking_stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, total_amount, booking_status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                    $booking_stmt->execute([$user_id, $room_id, $check_in, $check_out, $total_amount]);
                    $success_message = true;
                }
            }
        } catch (PDOException $e) {
            $error_message = "An error occurred while processing your request: " . $e->getMessage();
        }
    }
}


$rooms_stmt = $pdo->query("SELECT room_id, room_name, price_per_night FROM rooms WHERE room_status = 'available'");
$all_rooms = $rooms_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - Room Booking</title>
    <link rel="stylesheet" href="booking.css">
    <style>
        
        .error-msg { background-color: #ffdbdb; color: #cc0000; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; border-left: 5px solid #cc0000; text-align: center; }
        .modal-overlay { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:2000; justify-content:center; align-items:center; }
        .modal-overlay.active { display: flex; }
    </style>
</head>
<body>

    
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

    <!-- Main Booking Container -->
    <main class="booking-page-container">
        <section class="booking-card">
            <h2>Complete Your Reservation</h2>
            <p class="booking-subtext">Please enter the following details accurately to confirm your room reservation.</p>
            
            
            <?php if (!empty($error_message)): ?>
                <div class="error-msg"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="" method="POST" class="booking-form" id="mainBookingForm">
                
                <!-- Guest Details Section -->
                <div class="form-section">
                    <h3>1. Guest Details </h3>
                    <div class="form-group">
                        <label for="guest-name">Full Name </label>
                        <input type="text" id="guest-name" name="guest_name" placeholder="John Doe" required>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="guest-email">Email Address </label>
                            <input type="email" id="guest-email" name="guest_email" placeholder="johndoe@example.com" required>
                        </div>
                        <div class="form-group">
                            <label for="guest-phone">Contact Number </label>
                            <input type="tel" id="guest-phone" name="guest_phone" placeholder="0771234567" required>
                        </div>
                    </div>
                </div>

                <!-- Room & Dates Section -->
                <div class="form-section">
                    <h3>2. Booking Details </h3>
                    <div class="form-group">
                        <label for="room-select">Room Selection </label>
                        <select id="room-select" name="room_selection" required>
                            <option value="" disabled <?php echo ($selected_room_id == 0) ? 'selected' : ''; ?>>Select a room...</option>
                            <?php foreach ($all_rooms as $rm): ?>
                                <option value="<?php echo $rm['room_id']; ?>" 
                                        data-price="<?php echo $rm['price_per_night']; ?>" 
                                        data-name="<?php echo htmlspecialchars($rm['room_name']); ?>"
                                        <?php echo ($rm['room_id'] == $selected_room_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rm['room_name']); ?> - $<?php echo $rm['price_per_night']; ?> / Night
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="check-in">Check-in Date </label>
                            <input type="date" id="check-in" name="check_in_date" value="<?php echo htmlspecialchars($pre_checkin); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="check-out">Check-out Date </label>
                            <input type="date" id="check-out" name="check_out_date" value="<?php echo htmlspecialchars($pre_checkout); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Price Calculation Summary Display -->
                <div class="price-summary-box">
                    <h3>3. Price Summary </h3>
                    <div class="summary-row">
                        <span>Selected Room:</span>
                        <span id="summary-room">-</span>
                    </div>
                    <div class="summary-row">
                        <span>Total Nights:</span>
                        <span id="summary-nights">0</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total Price :</span>
                        <span id="summary-total">$0</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-confirm">Confirm & Book Now</button>
            </form>
        </section>
    </main>

    
    <?php if ($success_message): ?>
    <div class="modal-overlay active">
        <div class="modal-box" style="background: #fff; padding: 30px; border-radius: 10px; text-align: center; max-width: 420px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            <h3 style="color: #2e7d32; margin-bottom: 10px;">Reservation Confirmed!</h3>
            <p style="color: #555; margin-bottom: 20px;">Your booking request has been submitted successfully.</p>
            <a href="my-bookings.php" class="btn-confirm" style="display: inline-block; text-decoration: none; padding: 10px 25px;">Go to My Bookings</a>
        </div>
    </div>
    <?php endif; ?>

    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const roomSelect = document.getElementById("room-select");
            const checkinInput = document.getElementById("check-in");
            const checkoutInput = document.getElementById("check-out");

            const summaryRoom = document.getElementById("summary-room");
            const summaryNights = document.getElementById("summary-nights");
            const summaryTotal = document.getElementById("summary-total");

            function updatePriceSummary() {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                const pricePerNight = (selectedOption && selectedOption.value !== "") ? parseFloat(selectedOption.getAttribute("data-price")) : 0;
                const roomName = (selectedOption && selectedOption.value !== "") ? selectedOption.getAttribute("data-name") : "-";

                let nights = 0;
                if (checkinInput.value && checkoutInput.value) {
                    const start = new Date(checkinInput.value);
                    const end = new Date(checkoutInput.value);
                    if (end > start) {
                        const diffTime = end - start;
                        nights = Math.round(diffTime / (1000 * 60 * 60 * 24));
                    }
                }

                const totalPrice = pricePerNight * nights;

                summaryRoom.textContent = roomName;
                summaryNights.textContent = nights;
                summaryTotal.textContent = "$" + totalPrice.toFixed(2);
            }

            roomSelect.addEventListener("change", updatePriceSummary);
            checkinInput.addEventListener("change", updatePriceSummary);
            checkoutInput.addEventListener("change", updatePriceSummary);

            
            updatePriceSummary();
        });
    </script>

</body>
</html>
