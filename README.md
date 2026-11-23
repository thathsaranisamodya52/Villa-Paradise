# Villa Paradise - Coastal Villa & Room Booking System

**Course:** IT1208 – Web Technologies (Continuous Assessment 2 - 2026)  
**Project Type:** Individual Project  

---

## 1. Project Overview
**Villa Paradise** is a dynamic, web-based coastal villa and room booking application designed for a beachside villa in Sri Lanka. The system allows local and international tourists to explore the villa, check real-time room availability based on dates and guest count, and submit booking requests. It also includes an administrative dashboard for villa owners to manage room inventory and approve or cancel customer reservations while preventing overlapping bookings.

---

## 2. Project Directory Structure
```text
Project/
├── backend/
│   └── My_db.php               # PDO Database connection configuration
├── frontend/
│   ├── uploads/                # Uploaded room and villa images
│   ├── admin-dashboard.php     # Admin panel to manage bookings
│   ├── admin-dashboard.css
│   ├── booking.php             # Room booking form & processing
│   ├── booking.css
│   ├── booking.js
│   ├── index.php               # Home page & room availability search
│   ├── login.php               # User & Admin authentication
│   ├── login.css
│   ├── logout.php              # Session termination
│   ├── manage-rooms.php        # Admin CRUD operations for rooms
│   ├── manage-rooms.css
│   ├── my-bookings.php         # Guest reservation history & cancellation
│   ├── my-bookings.css
│   └── my-bookings.js
└── db_structure.sql            # Database schema and initial data
```

---

## 3. Implemented Features

### Guest / Front-End Features
* **Home Page (`index.php`):** Showcases the coastal villa, main facilities, location highlights, and direct navigation to booking sections.
* **Rooms & Accommodation:** Displays room types, images, descriptions, amenities, maximum occupancy, and nightly rates.
* **Room Availability Search & Filter:** Dynamically filters available rooms based on check-in/check-out dates and guest count, automatically excluding already confirmed bookings.
* **Room Booking System (`booking.php`):** Interactive booking form with client-side (`booking.js`) and server-side (PHP) validation, automatic night count, and total cost calculation.
* **User Authentication (`login.php`, `logout.php`):** Secure guest registration, login, session management, and logout functionality using PHP password hashing (`password_hash`).
* **My Bookings Dashboard (`my-bookings.php`):** Allows registered guests to view their reservation history, check booking statuses (*Pending*, *Confirmed*, *Cancelled*), and cancel pending requests.
* **Responsive Design:** Mobile, tablet, and desktop-friendly interface built with HTML5 and CSS3.

### Administrator Features
* **Admin Dashboard (`admin-dashboard.php`):** Centralized view of all customer bookings with guest details, dates, total pricing, and booking status.
* **Booking Management:** Ability to confirm or cancel pending bookings with built-in server-side overbooking prevention.
* **Room Management (`manage-rooms.php`):** Add new rooms with image uploads (stored in `frontend/uploads/`), view existing rooms, update room details/pricing, and manage room availability status.

---

## 4. Software Requirements
To run this project locally, you need:
* **Local Server Environment:** WAMP Server, XAMPP, or Laragon (Apache)
* **PHP:** Version 7.4 or higher (with **PDO** and **pdo_mysql** extensions enabled)
* **Database:** MySQL / MariaDB
* **Web Browser:** Google Chrome, Mozilla Firefox, Microsoft Edge, or Safari

---

## 5. Installation & Configuration Instructions

1. **Clone or Download the Repository:**
   ```bash
   git clone [ඔබේ-GitHub-Repository-Link-එක-මෙතැනට-දමන්න]
   ```
   *(Or download the ZIP file and extract it).*

2. **Move Project Folder:**
   Place the extracted `Project` folder inside your local server's root directory:
   * For **WAMP:** `C:\wamp64\www\Project`
   * For **XAMPP:** `C:\xampp\htdocs\Project`

3. **Database Setup:**
   * Start Apache and MySQL services in WAMP/XAMPP.
   * Open `http://localhost/phpmyadmin` in your browser.
   * Create a new database named **`villa_db`**.
   * Select the newly created `villa_db` database, go to the **Import** tab, and upload the **`db_structure.sql`** file included in this repository.

4. **Configure Database Connection:**
   * Open `backend/My_db.php` and verify that the PDO database credentials match your local server settings:
     ```php
     \(host = 'localhost';\)dbname = 'villa_db';
     \(username = 'root';\)password = '';
     ```

5. **Run the Application:**
   * Open your web browser and navigate to:
     `http://localhost/Project/frontend/index.php`

---

## 6. Sample Login Credentials

### Administrator Account
* **Email:** `admin@villa.com`
* **Password:** `password`

### Guest Account
* **Email:** `thathsaranisamodya62@gmail.com`
* **Password:** `20031010@Abc`

---

## 7. Academic Integrity & AI Declaration
* This project was developed individually for the IT1208 Web Technologies module.
* **Use of Generative AI:** Generative AI tools (Google Gemini) were used during development for debugging assistance, translating UI alert messages into English, and structuring documentation. All core architecture, logic, and database integrations were reviewed, understood, and implemented by the author.
