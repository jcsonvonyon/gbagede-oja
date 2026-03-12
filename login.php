<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // Fetch user mapping through user_roles which is the active source of truth
        $stmt = $pdo->prepare("SELECT u.*, ur.role_name, ur.permissions 
                             FROM users u 
                             JOIN user_roles ur ON u.role_id = ur.id 
                             WHERE u.username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'Active') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role_name'];
                
                // Parse and store permissions
                $perms = [];
                if (!empty($user['permissions'])) {
                    $perms = json_decode($user['permissions'], true) ?: [];
                }
                $_SESSION['permissions'] = $perms;

                // Update last login
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Your account is inactive. Please contact the admin.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Gbàgede-Ọjà Inventory</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <!-- Left Side: Login Form -->
        <div class="login-left">
            <div class="login-form-wrapper">
                <div class="logo-container">
                    <div class="logo-placeholder">
                        <img src="assets/img/logo.png" alt="Gbàgede-Ọjà Logo" style="max-height: 50px;">
                    </div>
                </div>

                <h1>Login in to your account</h1>
                <p class="subtitle">Welcome back. Enter your credentials to continue.</p>

                <?php if ($error): ?>
                    <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username <span>*</span></label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password <span>*</span></label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="•••••" required>
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember" style="margin-bottom: 0; font-weight: 400; color: #64748b;">Keep me logged in</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="sign-in-btn">Sign In</button>
                </form>

                <div class="signup-prompt">
                    Don't have an account yet?
                    <a href="#" class="signup-link">Schedule a demo</a>
                </div>
            </div>
        </div>

        <!-- Right Side: Green Marketing Side -->
        <div class="login-right">
            <h2>Simplifying accounting for smarter business.</h2>
            <p>Modern businesses and finance teams use Gbàgede Ọjà to manage their financial operations the smart way.</p>
            
            <div class="mockup-display">
                <div class="play-button">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </div>
                <div class="floating-badge">
                    <div class="badge-dot"></div>
                    Accounting the smart way!
                </div>
            </div>
        </div>
    </div>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye slash icon
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
