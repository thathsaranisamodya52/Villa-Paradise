<?php
session_start();
require_once '../backend/My_db.php'; 

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'guest';

    // Server-side Validation
    if (empty($name) || empty($email) || empty($contact) || empty($password)) {
        $error = "Please enter all details.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "The passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            
            $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);

            if ($checkStmt->rowCount() > 0) {
                $error = "This email address is already registered.";
            } else {
                // Password Hashing
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                
                $stmt = $pdo->prepare("INSERT INTO users (name, email, contact_number, password, role) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$name, $email, $contact, $hashed_password, $role])) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "An error occurred. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - Sign Up</title>
    <link rel="stylesheet" href="login.css?v=4">
    <style>
        .success-msg {
            background-color: #def7ec;
            color: #03543f;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 13px;
            text-align: center;
            border-left: 4px solid #0e9f6e;
        }
        .success-msg a {
            color: #03543f;
            font-weight: bold;
        }
        .register-panel {
            padding: 35px 50px;
        }
        .register-panel .input-group {
            margin-bottom: 12px;
        }
        .register-panel .input-group input {
            padding: 11px 14px;
        }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <div class="login-card">
            
            <!-- Left Side Image Section -->
            <div class="left-panel" style="background-image: url('view.jpg');">
                <div class="overlay">
                    <div class="brand-header">
                        <img src="logo1.jpg" alt="Villa Paradise Logo" class="brand-logo">
                        <span>Villa Paradise</span>
                    </div>

                    <div class="bottom-caption">
                        <a href="index.php" class="back-btn" title="Back to Home">&#8592;</a>
                        <p>Away from Home,<br>Yet Feels Like Home</p>
                    </div>
                </div>
            </div>

            <!-- Right Side Sign Up Form Section -->
            <div class="right-panel register-panel">
                <div class="form-header" style="margin-bottom: 20px;">
                    <div class="user-icon" style="width: 48px; height: 48px;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#b85d19" stroke-width="1.8">
                            <circle cx="9" cy="8" r="4"></circle>
                            <path d="M2 20c0-4 3.1-7 7-7s7 3 7 7"></path>
                            <line x1="19" y1="8" x2="19" y2="14"></line>
                            <line x1="16" y1="11" x2="22" y2="11"></line>
                        </svg>
                    </div>
                    <h2>Create Account</h2>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="success-msg">
                        <?php echo htmlspecialchars($success); ?> 
                        <a href="login.php">Login වන්න</a>
                    </div>
                <?php endif; ?>

                <form id="registerForm" method="POST" action="register.php" novalidate>
                    <div class="input-group">
                        <input type="text" id="name" name="name" placeholder="Full Name" required>
                        <small class="field-error" id="nameError"></small>
                    </div>

                    <div class="input-group">
                        <input type="email" id="email" name="email" placeholder="Email Address" required>
                        <small class="field-error" id="emailError"></small>
                    </div>

                    <div class="input-group">
                        <input type="text" id="contact_number" name="contact_number" placeholder="Contact Number (e.g. 0771234567)" required>
                        <small class="field-error" id="contactError"></small>
                    </div>

                    <div class="input-group password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Password (min 6 chars)" required>
                        <span class="toggle-password" onclick="togglePass('password')" style="top: 10px;">&#128065;</span>
                        <small class="field-error" id="passwordError"></small>
                    </div>

                    <div class="input-group password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        <span class="toggle-password" onclick="togglePass('confirm_password')" style="top: 10px;">&#128065;</span>
                        <small class="field-error" id="confirmError"></small>
                    </div>

                    <button type="submit" class="btn-login">SIGN UP</button>
                </form>

                <p class="signup-text" style="margin-top: 15px;">
                    Already have an account? <a href="login.php">Login</a>
                </p>
            </div>

        </div>
    </div>

    <script>
        function togglePass(fieldId) {
            const passInput = document.getElementById(fieldId);
            passInput.type = passInput.type === 'password' ? 'text' : 'password';
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            let valid = true;
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const contact = document.getElementById('contact_number').value.trim();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;

            document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

            if (name.length < 3) {
                document.getElementById('nameError').textContent = 'Please enter a valid full name.';
                valid = false;
            }
            if (!email || !email.includes('@')) {
                document.getElementById('emailError').textContent = 'Please enter a valid email address.';
                valid = false;
            }
            if (contact.length < 9 || isNaN(contact)) {
                document.getElementById('contactError').textContent = 'Please enter a valid phone number.';
                valid = false;
            }
            if (password.length < 6) {
                document.getElementById('passwordError').textContent = 'Password must be at least 6 characters long.';
                valid = false;
            }
            if (password !== confirm) {
                document.getElementById('confirmError').textContent = 'The passwords do not match.';
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    </script>
</body>
</html>
