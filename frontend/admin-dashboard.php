<?php
// 1. Database සම්බන්ධ කිරීම
include '../backend/My_db.php';

$message = "";

// 2. Booking එකක් Confirm හෝ Cancel කිරීමේ Back-end කොටස
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = intval($_GET['id']);
    
    try {
        if ($action == 'confirm') {
            // Overbooking පරීක්ෂා කිරීම (Confirm කිරීමට පෙර මේ දිනවල කාමරය වෙන් කර ඇත්දැයි බැලීම)
            $b_stmt = $pdo->prepare("SELECT room_id, check_in, check_out FROM bookings WHERE booking_id = ?");
            $b_stmt->execute([$booking_id]);
            $b_data = $b_stmt->fetch(PDO::FETCH_ASSOC);

            // එකම දිනවලදී වෙනත් අයෙකුගේ Booking එකක් Confirmed වෙලා තියෙනවාදැයි බැලීම
            $check_overlap = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND booking_status = 'Confirmed' AND booking_id != ? AND NOT (check_out <= ? OR check_in >= ?)");
            $check_overlap->execute([$b_data['room_id'], $booking_id, $b_data['check_in'], $b_data['check_out']]);
            
            if ($check_overlap->fetchColumn() > 0) {
                $message = "<div class='alert error'>කණගාටුයි! මෙම දිනවලදී මෙම කාමරය වෙනත් අයෙකුට Confirmed කර ඇත. Overbooking වැළැක්විය යුතුය!</div>";
            } else {
                $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = ?");
                $stmt->execute([$booking_id]);
                $message = "<div class='alert success'>Booking එක සාර්ථකව Confirm කරන ලදි!</div>";
            }
        } elseif ($action == 'cancel') {
            $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
            $message = "<div class='alert success'>Booking එක සාර්ථකව Cancel කරන ලදි!</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert error'>දෝෂයකි: " . $e->getMessage() . "</div>";
    }
}

// 3. අලුත් කාමරයක් ඇතුළත් කිරීමේ (Add Room) Back-end කොටස
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $room_name = trim($_POST['room_name']);
    $room_type = $_POST['room_type'];
    $price = floatval($_POST['price']);
    $max_guests = intval($_POST['max_guests']);
    $desc = trim($_POST['description']);

    $image_path = "room.webp"; // පින්තූරයක් තේරුවේ නැතිනම් default පින්තූරය
    
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] == 0) {
        $target_dir = "uploads/";
        $file_ext = strtolower(pathinfo($_FILES["room_image"]["name"], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid("room_") . "." . $file_ext;
            $target_file = $target_dir . $new_file_name;
            
            if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file; // උදා: uploads/room_65a1b2.jpg
            }
        } else {
            $message = "<div class='alert error'>කරුණාකර JPG, JPEG, PNG හෝ WEBP වර්ගයේ පින්තූරයක් පමණක් තෝරන්න!</div>";
        }
    }

    if (!empty($room_name) && !empty($price)) {
        try {
            $ins_stmt = $pdo->prepare("INSERT INTO rooms (room_name, room_type, description, price_per_night, max_guests, room_status,image) VALUES (?, ?, ?, ?, ?, 'available',?)");
            $ins_stmt->execute([$room_name, $room_type, $desc, $price, $max_guests, $image_path]);
            $message = "<div class='alert success'>නව කාමරය සාර්ථකව පද්ධතියට ඇතුළත් කරන ලදි!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert error'>කාමරය ඇතුළත් කිරීම අසාර්ථකයි: " . $e->getMessage() . "</div>";
        }
    }
}

// 4. දත්ත ගබඩාවෙන් සියලුම බුකින්ස් ලබා ගැනීම (SQL JOIN මඟින්)
$bookings_stmt = $pdo->query("SELECT b.booking_id, u.name AS guest_name, r.room_name, b.check_in, b.check_out, b.total_amount, b.booking_status FROM bookings b JOIN users u ON b.user_id = u.user_id JOIN rooms r ON b.room_id = r.room_id ORDER BY b.booking_date DESC");
$all_bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css"> <!-- පරණ Menu Style එක සඳහා -->
    <link rel="stylesheet" href="admin-dashboard.css"> <!-- අපි වෙන් කර සාදපු CSS එක -->
</head>
<body>

    <!-- Header / Navigation Menu -->
    <header>
        <div class="logo">
            <a href="index.php">
                <img src="logo1.jpg" alt="Villa Paradise Logo"> 
                <span>Villa Paradise</span>
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="admin-dashboard.php" class="active">Admin Dashboard</a></li>
                <li><a href="room.php">View Site (Rooms)</a></li>
            </ul>
        </nav>
    </header>

    <div class="admin-container">
        
        <!-- සර්වර් පණිවිඩ (Success/Error Alerts) පෙන්වීම -->
        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <!-- 1. Booking කළමනාකරණ වගුව (Manage Customer Bookings) -->
        <div class="card">
            <h2>Manage Customer Bookings</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Guest Name</th>
                            <th>Room Booked</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($all_bookings) > 0): ?>
                            <?php foreach ($all_bookings as $b): ?>
                                <tr>
                                    <td>#VP-<?php echo $b['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($b['guest_name']); ?></td>
                                    <td><?php echo htmlspecialchars($b['room_name']); ?></td>
                                    <td><?php echo $b['check_in']; ?></td>
                                    <td><?php echo $b['check_out']; ?></td>
                                    <td>$<?php echo $b['total_amount']; ?></td>
                                    <td><span class="status status-<?php echo $b['booking_status']; ?>"><?php echo $b['booking_status']; ?></span></td>
                                    <td>
                                        <?php if ($b['booking_status'] == 'Pending'): ?>
                                            <a href="admin-dashboard.php?action=confirm&id=<?php echo $b['booking_id']; ?>" class="btn btn-approve" onclick="return confirm('මෙම බුකින් එක ස්ථිර (Confirm) කිරීමට අවශ්‍යද?')">Confirm</a>
                                            <a href="admin-dashboard.php?action=cancel&id=<?php echo $b['booking_id']; ?>" class="btn btn-cancel" onclick="return confirm('මෙම බුකින් එක අවලංගු (Cancel) කිරීමට අවශ්‍යද?')">Cancel</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding: 20px; color: #666;">කිසිදු පාරිභෝගික බුකින් එකක් මෙතෙක් සිදුකර නැත.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. අලුත් කාමර ඇතුළත් කිරීමේ පෝරමය (Add New Villa Room) -->
        <div class="card">
            <h2>Add New Villa Room</h2>
            <form action="admin-dashboard.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="room_name">Room Name (කාමරයේ නම)</label>
                        <input type="text" id="room_name" name="room_name" placeholder="eg: Deluxe Ocean View Room" required>
                    </div>
                    <div class="form-group">
                        <label for="room_type">Room Type (කාමර වර්ගය)</label>
                        <select id="room_type" name="room_type">
                            <option value="Deluxe">Deluxe</option>
                            <option value="Suite">Suite</option>
                            <option value="Standard">Standard</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="price">Price Per Night (එක් රාත්‍රියකට මිල $)</label>
                        <input type="number" id="price" name="price" step="0.01" placeholder="150.00" required>
                    </div>
                    <div class="form-group">
                        <label for="max_guests">Max Guests (උපරිම පුද්ගලයන් ගණන)</label>
                        <input type="number" id="max_guests" name="max_guests" value="2" min="1" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="room_image">Room Image (කාමරයේ ඡායාරූපය)</label>
                    <input type="file" id="room_image" name="room_image" accept="image/*" required style="padding: 8px; background: #fff;">
                </div>
                <div class="form-group">
                    <label for="description">Description (කාමරය පිළිබඳ විස්තරයක්)</label>
                    <textarea id="description" name="description" rows="4" placeholder="කාමරයේ ඇති පහසුකම් සහ අනෙකුත් විස්තර මෙහි ඇතුළත් කරන්න..." required></textarea>
                </div>
                <button type="submit" name="add_room" class="btn-submit">Add Room to System</button>
            </form>
        </div>
        
    </div>

</body>
</html>
