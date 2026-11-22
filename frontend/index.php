<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>



<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - Home</title>
    <!-- Font Awesome Icons සම්බන්ධ කිරීම -->
    <link rel="stylesheet" href="https://cloudflare.com">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- 1. Header & Navigation (මෙනුව) -->
    <header>
        <div class="logo">
            <a href="#">
                <img src="logo1.jpg" alt="Villa Paradise Logo">
                <span>Villa Paradise</span>
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#about">About Us</a></li>
                <li><a href="room.php">Facilities</a></li> <!-- Rooms පිටුවට සම්බන්ධ කළා -->
                <li><a href="search.php">Book Now</a></li> <!-- 👈 අලුතින් එකතු කළා -->
                <li><a href="my-bookings.php">My Bookings</a></li> <!-- 👈 අලුතින් එකතු කළා -->
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php" style="color: #b85d19; font-weight: bold;">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php" style="color: #b85d19; font-weight: bold;">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- 2. Hero Section (මුල් දර්ශනය) -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-left">
                <h1>Villa Paradise</h1>
                <p class="tagline">Rest easy. Wake up inspired.</p>
                <a href="room.php" class="btn-hero-book">Book Now</a> 
            </div>
            <div class="hero-right-gallery">
                <div class="img-box box-1"><img src="pool.jpg" alt="Villa Pool"></div>
                <div class="img-box box-2"><img src="room.webp" alt="Villa Room"></div>
                <div class="img-box box-3"><img src="view.jpg" alt="Villa View"></div>
            </div>
        </div>
    </section>

    <!-- 3. Welcome Section -->
    <section class="welcome-section">
        <div class="welcome-container">
            <div class="welcome-left-img">
                <img src="beach.jpeg" alt="Mirissa Beach">
            </div>
            <div class="welcome-content">
                <h2>Welcome To Villa Paradise</h2>
                <p>Located in the heart of Mirissa, Sri Lanka, overlooking a beautiful private beach layout. Experience the ultimate luxury and tranquility by the ocean. Your perfect getaway destination awaits.</p>
                
            </div>
        </div>
    </section>

    <!-- 4. About Us Section -->
    <section id="about" class="about-section">
        <div class="about-container">
            <div class="about-content">
                <h2>About Villa Paradise</h2>
                <p>"Experience an unforgettable luxury holiday at Villa Paradise, located in Mirissa, one of the most beautiful beaches in Sri Lanka. The secluded private beach, the breathtaking views of the Indian Ocean, and our premium staff will make your vacation truly meaningful."
</p>
            </div>
            <div class="about-image">
                <img src="lobby.avif" alt="Villa Lobby">
            </div>
        </div>
    </section>

    <!-- 5. Our Best Service (Facilities) Section -->
    <section class="services-section">
        <h2>Our Best Service</h2>
        <div class="services-grid">
            <div class="service-card">
                <i class="fa-solid fa-person-swimming"></i>
                <h3>Infinity Pool</h3>
                <p>Spend a relaxing time in our beautiful infinity swimming pool overlooking the ocean.</p>
            </div>
            <div class="service-card">
                <i class="fa-solid fa-wifi"></i>
                <h3>Free Wi-Fi</h3>
                <p>Stay connected always with our complimentary ultra high-speed Wi-Fi network.</p>
            </div>
            <div class="service-card">
                <i class="fa-solid fa-snowflake"></i>
                <h3>Full A/C</h3>
                <p>All rooms are equipped with premium air conditioning for your complete comfort.</p>
            </div>
        </div>
    </section>

    <!-- 6. Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-brand">
                <h3>Villa Paradise</h3>
                <p>📍 Beach Road, Mirissa, Sri Lanka.</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <a href="#">Home</a>
                <a href="#about">About Us</a>
                <a href="rooms.html">Facilities</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Villa Paradise. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>
