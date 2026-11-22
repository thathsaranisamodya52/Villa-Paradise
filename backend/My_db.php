<?php
$host = 'localhost';
$dbname = 'villa_db'; // අපි phpMyAdmin එකේ හදපු නම
$username = 'root';   // WampServer හි default නම
$password = '';       // WampServer හි default password එක හිස්ව තබන්න

try {
    // PDO මඟින් Database එකට සම්බන්ධ වීම
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // වැරදි (Errors) ආවොත් ඒවා පෙන්වීමට සකස් කිරීම
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // සම්බන්ධතාවය අසාර්ථක වුවහොත් පණිවිඩයක් පෙන්වීම
    die("Database සම්බන්ධතාවය අසාර්ථකයි: " . $e->getMessage());
}
?>
