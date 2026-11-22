<?php
session_start();
require_once '../backend/My_db.php'; // මෙහි $pdo ඇත

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "කරුණාකර Email සහ Password ඇතුළත් කරන්න.";
    } else {
        try {
            // PDO මඟින් පරිශීලකයාගේ විස්තර ලබා ගැනීම
            $stmt = $pdo->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Password එක පරීක්ෂා කිරීම
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];

                    // Role එක අනුව අදාළ පිටුවට යොමු කිරීම
                    if ($user['role'] === 'admin') {
                        header("Location: admin-dashboard.php");
                    } elseif (isset($_SESSION['redirect_url'])) {
                    $url = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    header("Location: " . $url);
                        header("Location: admin-dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $error = "ඇතුළත් කළ Password එක වැරදියි.";
                }
            } else {
                $error = "මෙම Email ලිපිනය සඳහා ගිණුමක් නොමැත.";
            }
        } catch (PDOException $e) {
            $error = "Database දෝෂයකි: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Paradise - Login</title>
    <link rel="stylesheet" href="login.css?v=4">
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

            <!-- Right Side Login Form Section -->
            <div class="right-panel">
                <div class="form-header">
                    <div class="user-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#b85d19" stroke-width="1.8">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"></path>
                        </svg>
                    </div>
                    <h2>Login</h2>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="login.php" novalidate>
                    <div class="input-group">
                        <input type="email" id="email" name="email" placeholder="Email" required>
                        <small class="field-error" id="emailError"></small>
                    </div>

                    <div class="input-group password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <span class="toggle-password" onclick="togglePass()">&#128065;</span>
                        <small class="field-error" id="passwordError"></small>
                    </div>

                    <button type="submit" class="btn-login">LOGIN</button>
                </form>

                <p class="signup-text">
                    Don't have an account? <a href="register.php">Sign Up</a>
                </p>
            </div>

        </div>
    </div>

    <script>
        function togglePass() {
            const passInput = document.getElementById('password');
            passInput.type = passInput.type === 'password' ? 'text' : 'password';
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let valid = true;
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            document.getElementById('emailError').textContent = '';
            document.getElementById('passwordError').textContent = '';

            if (!email || !email.includes('@')) {
                document.getElementById('emailError').textContent = 'නිවැරදි Email ලිපිනයක් ඇතුළත් කරන්න.';
                valid = false;
            }
            if (password.length < 4) {
                document.getElementById('passwordError').textContent = 'Password එක ඇතුළත් කරන්න.';
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    </script>
</body>
</html>
