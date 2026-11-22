<?php
// 1. Database සම්බන්ධ කිරීම
include '../backend/My_db.php';

$message = "";

// 2. Status එක වෙනස් කිරීමේ (Toggle Status) Back-end කොටස
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $current_status = $_GET['toggle_status'];
    $room_id = intval($_GET['id']);
    
    // නව තත්ත්වය තීරණය කිරීම
    $new_status = ($current_status == 'available') ? 'unavailable' : 'available';
    
    try {
        $status_stmt = $pdo->prepare("UPDATE rooms SET room_status = ? WHERE room_id = ?");
        $status_stmt->execute([$new_status, $room_id]);
        $message = "<div class='alert success'>කාමරයේ තත්ත්වය (Status) සාර්ථකව යාවත්කාලීන කරන ලදි!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert error'>තත්ත්වය වෙනස් කිරීමට නොහැකි විය: " . $e->getMessage() . "</div>";
    }
}

// 3. කාමරයක් සම්පූර්ණයෙන්ම පද්ධතියෙන් ඉවත් කිරීමේ (Delete Room) Back-end කොටස
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    try {
        $del_stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
        $del_stmt->execute([$delete_id]);
        $message = "<div class='alert success'>කාමරය පද්ධතියෙන් සාර්ථකව ඉවත් කරන ලදි!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert error'>කාමරය ඉවත් කිරීමට නොහැකි විය: " . $e->getMessage() . "</div>";
    }
}

// 4. දත්ත ගබඩාවෙන් සියලුම කාමර ලබා ගැනීම
try {
    $rooms_stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_id DESC");
    $all_rooms = $rooms_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("දත්ත ලබා ගැනීමට නොහැකි විය: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - Manage Rooms</title>
    <link rel="stylesheet" href="style.css"> <!-- පරණ Menu Style එක සඳහා -->
    <link rel="stylesheet" href="manage-rooms.css"> <!-- 👈 අලුත් CSS එක මෙතැනට සම්බන්ධ කළා -->
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
                <li><a href="admin-dashboard.php">Admin Dashboard</a></li>
                <li><a href="manage-rooms.php" class="active">Manage Rooms</a></li>
                <li><a href="room.php">View Site</a></li>
            </ul>
        </nav>
    </header>

    <main class="manage-container">
        
        <!-- සර්වර් පණිවිඩ (Success/Error Alerts) ਪෙන්වීම -->
        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <div class="top-action-bar">
            <a href="admin-dashboard.php" class="btn-back-dashboard">← Back to Dashboard</a>
        </div>

        <!-- සියලුම කාමර පෙන්වන වගුව (Rooms Management Table) -->
        <div class="rooms-card">
            <h2>Current Villa Rooms & Suites</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Room ID</th>
                            <th>Room Name</th>
                            <th>Type</th>
                            <th>Price Per Night</th>
                            <th>Max Guests</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($all_rooms) > 0): ?>
                            <?php foreach ($all_rooms as $room): ?>
                                <tr>
                                    <td>#RM-<?php echo $room['room_id']; ?></td>
                                    <td class="room-name-td"><?php echo htmlspecialchars($room['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                                    <td>$<?php echo htmlspecialchars($room['price_per_night']); ?></td>
                                    <td>👥 <?php echo htmlspecialchars($room['max_guests']); ?> Adults</td>
                                    <td>
                                        <!-- Status Badge එක පෙන්වීම -->
                                        <?php if ($room['room_status'] == 'available'): ?>
                                            <span class="room-status-badge badge-available">Available</span>
                                        <?php else: ?>
                                            <span class="room-status-badge badge-unavailable">Unavailable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-links">
                                            <!-- Status එක මාරු කිරීමේ බොත්තම (Toggle Button) -->
                                            <?php if ($room['room_status'] == 'available'): ?>
                                                <a href="manage-rooms.php?toggle_status=available&id=<?php echo $room['room_id']; ?>" class="btn-status btn-make-unavailable">Make Unavailable</a>
                                            <?php else: ?>
                                                <a href="manage-rooms.php?toggle_status=unavailable&id=<?php echo $room['room_id']; ?>" class="btn-status btn-make-available">Make Available</a>
                                            <?php endif; ?>

                                            <!-- කාමරය මකාදැමීමේ බොත්තම -->
                                            <a href="manage-rooms.php?delete_id=<?php echo $room['room_id']; ?>" class="btn-delete-room" onclick="return confirm('මෙම කාමරය පද්ධතියෙන් සම්පූර්ණයෙන්ම ඉවත් කිරීමට අවශ්‍යද?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding: 20px; color: #666;">දැනට පද්ධතිය තුළ කිසිදු කාමරයක් ඇතුළත් කර නැත.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </main>

</body>
</html>
