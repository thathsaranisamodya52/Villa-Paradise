<?php

include '../backend/My_db.php';

$message = "";


if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = intval($_GET['id']);
    
    try {
        if ($action == 'confirm') {
            
            $b_stmt = $pdo->prepare("SELECT room_id, check_in, check_out FROM bookings WHERE booking_id = ?");
            $b_stmt->execute([$booking_id]);
            $b_data = $b_stmt->fetch(PDO::FETCH_ASSOC);

            
            $check_overlap = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND booking_status = 'Confirmed' AND booking_id != ? AND NOT (check_out <= ? OR check_in >= ?)");
            $check_overlap->execute([$b_data['room_id'], $booking_id, $b_data['check_in'], $b_data['check_out']]);
            
            if ($check_overlap->fetchColumn() > 0) {
                $message = "<div class='alert error'>Sorry! This room is already confirmed for another guest on these dates. Overbooking must be prevented!</div>";
            } else {
                $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = ?");
                $stmt->execute([$booking_id]);
                $message = "<div class='alert success'>Booking confirmed successfully!</div>";
            }
        } elseif ($action == 'cancel') {
            $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
            $message = "<div class='alert success'>Booking cancelled successfully!</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $room_name = trim($_POST['room_name']);
    $room_type = $_POST['room_type'];
    $price = floatval($_POST['price']);
    $max_guests = intval($_POST['max_guests']);
    $desc = trim($_POST['description']);

    $image_path = "room.webp"; 
    
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] == 0) {
        $target_dir = "uploads/";
        $file_ext = strtolower(pathinfo($_FILES["room_image"]["name"], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid("room_") . "." . $file_ext;
            $target_file = $target_dir . $new_file_name;
            
            if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file; 
            }
        } else {
            $message = "<div class='alert error'>Please select only JPG, JPEG, PNG or WEBP format images!</div>";
        }
    }

    if (!empty($room_name) && !empty($price)) {
        try {
            $ins_stmt = $pdo->prepare("INSERT INTO rooms (room_name, room_type, description, price_per_night, max_guests, room_status,image) VALUES (?, ?, ?, ?, ?, 'available',?)");
            $ins_stmt->execute([$room_name, $room_type, $desc, $price, $max_guests, $image_path]);
            $message = "<div class='alert success'>New room added successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert error'>Failed to add room: " . $e->getMessage() . "</div>";
        }
    }
}


$bookings_stmt = $pdo->query("SELECT b.booking_id, u.name AS guest_name, r.room_name, b.check_in, b.check_out, b.total_amount, b.booking_status FROM bookings b JOIN users u ON b.user_id = u.user_id JOIN rooms r ON b.room_id = r.room_id ORDER BY b.booking_date DESC");
$all_bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="admin-dashboard.css"> 
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
        
        
        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        
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
                                            <a href="admin-dashboard.php?action=confirm&id=<?php echo $b['booking_id']; ?>" class="btn btn-approve" onclick="return confirm('Are you sure you want to confirm this booking?')">Confirm</a>
                                            <a href="admin-dashboard.php?action=cancel&id=<?php echo $b['booking_id']; ?>" class="btn btn-cancel" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding: 20px; color: #666;">No customer bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="card">
            <h2>Add New Villa Room</h2>
            <form action="admin-dashboard.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="room_name">Room Name </label>
                        <input type="text" id="room_name" name="room_name" placeholder="eg: Deluxe Ocean View Room" required>
                    </div>
                    <div class="form-group">
                        <label for="room_type">Room Type</label>
                        <select id="room_type" name="room_type">
                            <option value="Deluxe">Deluxe</option>
                            <option value="Suite">Suite</option>
                            <option value="Standard">Standard</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="price">Price Per Night</label>
                        <input type="number" id="price" name="price" step="0.01" placeholder="150.00" required>
                    </div>
                    <div class="form-group">
                        <label for="max_guests">Max Guests</label>
                        <input type="number" id="max_guests" name="max_guests" value="2" min="1" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="room_image">Room Image</label>
                    <input type="file" id="room_image" name="room_image" accept="image/*" required style="padding: 8px; background: #fff;">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Enter room amenities and other details here..." required></textarea>
                </div>
                <button type="submit" name="add_room" class="btn-submit">Add Room to System</button>
            </form>
        </div>
        
    </div>

</body>
</html>