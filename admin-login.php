<?php
session_start();
require 'includes/db.php'; // your DB connection file
require 'includes/mail.php'; // optional: PHPMailer for sending emails

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Brute-force protection
$max_attempts = 5;
$lockout_time = 300; // 5 minutes

if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['lockout_time'])) $_SESSION['lockout_time'] = 0;

$error = '';
$success = false;

// Auto-login via cookie
if (!isset($_SESSION['admin_id']) && isset($_COOKIE['remember_admin'])) {
    list($admin_id, $token) = explode(':', $_COOKIE['remember_admin']);
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id=?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($token, $admin['remember_token'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: admin/dashboard.php");
            exit;
        }
    }
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (time() < $_SESSION['lockout_time']) {
        $error = "Too many failed login attempts. Try again in " . ceil(($_SESSION['lockout_time'] - time()) / 60) . " minutes.";
    } else {
        if ($_SESSION['login_attempts'] >= $max_attempts) {
            $_SESSION['login_attempts'] = 0;
        }

        // CSRF check
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "Invalid request. Please refresh the page and try again.";
        } else {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $remember = isset($_POST['remember']);

            if ($username === '' || $password === '') {
                $error = "All fields are required";
            } else {
                $stmt = $conn->prepare("SELECT * FROM admins WHERE username=?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    if (password_verify($password, $admin['password'])) {
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];

                        // Remember Me
                        if ($remember) {
                            $token = bin2hex(random_bytes(16));
                            $stmt = $conn->prepare("UPDATE admins SET remember_token=? WHERE id=?");
                            $stmt->bind_param("si", password_hash($token, PASSWORD_DEFAULT), $admin['id']);
                            $stmt->execute();
                            setcookie("remember_admin", $admin['id'] . ':' . $token, time() + (30 * 24 * 60 * 60), "/", "", true, true);
                        }

                        $_SESSION['login_attempts'] = 0;
                        $success = true;
                    } else {
                        $error = "Invalid username or password";
                        $_SESSION['login_attempts'] += 1;
                        if ($_SESSION['login_attempts'] >= $max_attempts) {
                            $_SESSION['lockout_time'] = time() + $lockout_time;
                            $error = "Too many failed login attempts. Locked for 5 minutes.";
                        }
                    }
                } else {
                    $error = "Invalid username or password";
                    $_SESSION['login_attempts'] += 1;
                    if ($_SESSION['login_attempts'] >= $max_attempts) {
                        $_SESSION['lockout_time'] = time() + $lockout_time;
                        $error = "Too many failed login attempts. Locked for 5 minutes.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="./images/logo3.png" />
    <title>Joseph's Pot - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Exo+2:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <style>
        /* existing CSS here - keep all your previous styles for login container and form */
        :root {
            --brown: #8b4513;
            --brown-light: #a0522d;
            --brown-dark: #654321;
            --white: #ffffff;
            --pale-orange: #ffe4b5;
            --pale-orange-light: #fff8dc;
            --accent: #d2691e;
            --text: #333333;
            --text-light: #666666;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--brown-dark) 0%, var(--brown) 50%, var(--brown-light) 100%);
            color: var(--pale-orange-light);
            font-family: "Exo 2", sans-serif;
            line-height: 1.6;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cyber-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(139, 69, 19, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(139, 69, 19, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: -1;
            pointer-events: none;
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: var(--pale-orange);
            border-radius: 50%;
            opacity: 0.3;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }

            10% {
                opacity: 0.3;
            }

            90% {
                opacity: 0.3;
            }

            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        .login-container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 228, 181, 0.2);
            box-shadow: 0 0 50px rgba(210, 105, 30, 0.3);
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg,
                    rgba(210, 105, 30, 0.2),
                    rgba(101, 67, 33, 0.7)),
                url("https://res.cloudinary.com/dl4hjr1p2/image/upload/v1762379534/unnamed_2_j7h5xd.webp") center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 50%,
                    rgba(255, 228, 181, 0.2) 0%,
                    transparent 50%),
                radial-gradient(circle at 70% 20%,
                    rgba(255, 255, 255, 0.1) 0%,
                    transparent 50%),
                radial-gradient(circle at 40% 80%,
                    rgba(210, 105, 30, 0.2) 0%,
                    transparent 50%);
            z-index: 0;
        }

        .logo-container {
            text-align: center;
            z-index: 1;
            margin-bottom: 40px;
        }

        .logo-container img {
            width: 120px;
            height: 120px;
            filter: drop-shadow(0 0 10px var(--accent));
            margin-bottom: 20px;
            border-radius: 50%;
            background: var(--pale-orange);
            padding: 10px;
        }

        .logo-container h1 {
            font-family: "Orbitron", sans-serif;
            font-size: 3rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, var(--pale-orange), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 0 20px rgba(210, 105, 30, 0.5);
            letter-spacing: 2px;
        }

        .logo-container p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 400px;
            color: var(--pale-orange-light);
        }

        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
            z-index: 1;
        }

        .feature {
            display: flex;
            align-items: center;
            background: rgba(101, 67, 33, 0.4);
            padding: 15px;
            border-radius: 10px;
            border-left: 3px solid var(--accent);
            transition: var(--transition);
        }

        .feature:hover {
            transform: translateY(-5px);
            background: rgba(160, 82, 45, 0.4);
        }

        .feature i {
            font-size: 1.5rem;
            margin-right: 15px;
            color: var(--pale-orange);
        }

        .feature p {
            font-size: 0.9rem;
            color: var(--pale-orange-light);
        }

        .login-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            background: rgba(101, 67, 33, 0.2);
        }

        .login-form-container {
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-family: "Orbitron", sans-serif;
            font-size: 2.2rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, var(--pale-orange), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .login-header p {
            opacity: 0.8;
            font-size: 1rem;
            color: var(--pale-orange-light);
        }

        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--pale-orange);
            font-size: 0.9rem;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent);
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 228, 181, 0.3);
            border-radius: 8px;
            color: var(--pale-orange-light);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control::placeholder {
            color: rgba(255, 228, 181, 0.5);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 10px rgba(210, 105, 30, 0.3);
            background: rgba(255, 255, 255, 0.15);
        }

        .form-control:focus+i {
            color: var(--pale-orange);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--pale-orange-light);
            cursor: pointer;
            opacity: 0.7;
            transition: var(--transition);
        }

        .password-toggle:hover {
            opacity: 1;
            color: var(--pale-orange);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 8px;
            accent-color: var(--accent);
        }

        .remember-me label {
            color: var(--pale-orange-light);
        }

        .forgot-password {
            color: var(--pale-orange);
            text-decoration: none;
            transition: var(--transition);
        }

        .forgot-password:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(45deg, var(--brown-light), var(--accent));
            color: var(--pale-orange-light);
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(210, 105, 30, 0.4);
        }

        .login-btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg,
                    transparent,
                    rgba(255, 255, 255, 0.2),
                    transparent);
            transform: translateX(-100%);
        }

        .login-btn:hover::before {
            animation: shine 1.5s;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(210, 105, 30, 0.6);
            background: linear-gradient(45deg, var(--accent), var(--brown-light));
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9rem;
            opacity: 0.7;
            color: var(--pale-orange-light);
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            background: var(--accent);
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(210, 105, 30, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(210, 105, 30, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(210, 105, 30, 0);
            }
        }

        .error-message {
            color: var(--pale-orange);
            font-size: 0.9rem;
            margin-top: 5px;
            display: none;
        }

        /* Success Animation */
        .success-animation {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .success-animation i {
            font-size: 4rem;
            color: var(--pale-orange);
            margin-bottom: 20px;
            animation: bounce 1s ease infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .success-animation h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: var(--pale-orange);
        }

        .success-animation p {
            font-size: 1rem;
            opacity: 0.8;
            color: var(--pale-orange-light);
        }

        /* Media Queries */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }

            .login-left {
                padding: 30px 20px;
            }

            .features {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .login-container {
                width: 95%;
                border-radius: 15px;
            }

            .logo-container h1 {
                font-size: 2.2rem;
            }

            .login-header h2 {
                font-size: 1.8rem;
            }

            .login-left,
            .login-right {
                padding: 20px;
            }
        }

        /* Forgot Password Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            color: #ffe4b5;
            text-align: center;
        }

        .modal-content input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #d2691e;
        }

        .modal-content button {
            width: 100%;
            padding: 12px;
            background: #d2691e;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
        }

        .modal-content button:hover {
            background: #a0522d;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .message {
            margin-top: 10px;
            font-size: 0.95rem;
        }

        .message.error {
            color: #ffcccb;
        }

        .message.success {
            color: #b0ffb0;
        }
    </style>
</head>

<body>
    <div class="cyber-grid"></div>
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <!-- your existing login-left and login-right HTML here -->

        <div class="login-right">
            <div class="login-form-container">
                <div class="login-header">
                    <h2>ADMIN LOGIN</h2>
                    <p>Access your restaurant dashboard</p>
                </div>

                <form class="login-form" id="loginForm" method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <!-- username and password fields as before -->
                    ...
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" name="remember" id="remember" />
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> SIGN IN</button>
                    <div class="success-animation" id="successAnimation">
                        <i class="fas fa-check-circle"></i>
                        <h2>Login Successful!</h2>
                        <p>Redirecting to dashboard...</p>
                    </div>
                    <?php if ($error): ?>
                        <div class="error-message" style="display:block; margin-top:15px; text-align:center;"><?= $error ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Forgot Password</h2>
            <p>Enter your email to receive a password reset link</p>
            <form id="forgotForm" method="POST">
                <input type="email" name="email" placeholder="Your email" required />
                <button type="submit">Send Reset Link</button>
            </form>
            <div id="forgotMessage" class="message"></div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
<div id="forgotModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:9999;">
    <div style="background:#fff; padding:30px; border-radius:10px; max-width:400px; width:90%; position:relative;">
        <h3 style="margin-bottom:15px; color:#333;">Reset Password</h3>
        <p>Enter your registered email:</p>
        <input type="email" id="resetEmail" placeholder="Email" style="width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc;">
        <button id="resetBtn" style="width:100%; padding:10px; border:none; background:#d2691e; color:#fff; border-radius:5px; cursor:pointer;">Send Reset Link</button>
        <div id="resetMsg" style="margin-top:10px;"></div>
        <button id="closeModal" style="position:absolute; top:10px; right:15px; background:none; border:none; font-size:1.2rem; cursor:pointer;">&times;</button>
    </div>
</div>


    <script>
        // existing particles + password toggle + success animation
        // Create floating particles
        document.addEventListener("DOMContentLoaded", function() {
            const particlesContainer = document.getElementById("particles");
            const particleCount = 30;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement("div");
                particle.classList.add("particle");

                // Random size
                const size = Math.random() * 5 + 2;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;

                // Random position
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;

                // Random animation delay and duration
                const delay = Math.random() * 15;
                const duration = Math.random() * 10 + 15;
                particle.style.animationDelay = `${delay}s`;
                particle.style.animationDuration = `${duration}s`;

                particlesContainer.appendChild(particle);
            }

            // Password toggle functionality
            const passwordToggle = document.getElementById("passwordToggle");
            const passwordInput = document.getElementById("password");

            passwordToggle.addEventListener("click", function() {
                const type =
                    passwordInput.getAttribute("type") === "password" ?
                    "text" :
                    "password";
                passwordInput.setAttribute("type", type);

                // Toggle eye icon
                this.innerHTML =
                    type === "password" ?
                    '<i class="fas fa-eye"></i>' :
                    '<i class="fas fa-eye-slash"></i>';
            });

            // Form validation
            const loginForm = document.getElementById("loginForm");
            const usernameInput = document.getElementById("username");
            const usernameError = document.getElementById("usernameError");
            const passwordError = document.getElementById("passwordError");
            const successAnimation = document.getElementById("successAnimation");

            loginForm.addEventListener("submit", function(e) {
                e.preventDefault();

                let isValid = true;

                // Validate username
                if (usernameInput.value.trim() === "") {
                    usernameError.style.display = "block";
                    usernameInput.style.borderColor = "var(--accent)";
                    isValid = false;
                } else {
                    usernameError.style.display = "none";
                    usernameInput.style.borderColor = "rgba(255, 228, 181, 0.3)";
                }

                // Validate password
                if (passwordInput.value.trim() === "") {
                    passwordError.style.display = "block";
                    passwordInput.style.borderColor = "var(--accent)";
                    isValid = false;
                } else {
                    passwordError.style.display = "none";
                    passwordInput.style.borderColor = "rgba(255, 228, 181, 0.3)";
                }

                if (isValid) {
                    // Simulate login process
                    const loginBtn = loginForm.querySelector(".login-btn");
                    loginBtn.innerHTML =
                        '<i class="fas fa-spinner fa-spin"></i> AUTHENTICATING...';
                    loginBtn.disabled = true;

                    // Simulate API call
                    setTimeout(() => {
                        // Show success animation
                        loginForm.style.display = 'none';
                        successAnimation.style.display = 'block';

                        // Redirect after delay
                        setTimeout(() => {
                            // For demo purposes, redirect to dashboard
                            // In a real application, you would validate credentials first
                            alert('Login successful! Redirecting to dashboard...');
                            // window.location.href = "admin-dashboard.html";
                        }, 2000);
                    }, 1500);
                }
            });

            // Input focus effects
            const inputs = document.querySelectorAll(".form-control");
            inputs.forEach((input) => {
                input.addEventListener("focus", function() {
                    this.parentElement.querySelector("i").style.color =
                        "var(--pale-orange)";
                });

                input.addEventListener("blur", function() {
                    this.parentElement.querySelector("i").style.color = "var(--accent)";
                });
            });
        });


        // Forgot Password Modal JS
        const forgotLink = document.querySelector('.forgot-password');
        const modal = document.getElementById('forgotModal');
        const closeBtn = modal.querySelector('.close');
        const forgotForm = document.getElementById('forgotForm');
        const forgotMessage = document.getElementById('forgotMessage');

        forgotLink.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'flex';
        });

        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            forgotForm.reset();
            forgotMessage.innerHTML = '';
        });

        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                forgotForm.reset();
                forgotMessage.innerHTML = '';
            }
        });

        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            forgotMessage.innerHTML = 'Sending reset link...';
            forgotMessage.className = 'message';

            const formData = new FormData(forgotForm);

            fetch('forgot-password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        forgotMessage.textContent = data.message;
                        forgotMessage.classList.add('success');
                    } else {
                        forgotMessage.textContent = data.message;
                        forgotMessage.classList.add('error');
                    }
                })
                .catch(() => {
                    forgotMessage.textContent = 'An error occurred. Try again.';
                    forgotMessage.classList.add('error');
                });
        });

        document.addEventListener("DOMContentLoaded", function(){
    const forgotLink = document.querySelector('.forgot-password');
    const modal = document.getElementById('forgotModal');
    const closeModal = document.getElementById('closeModal');
    const resetBtn = document.getElementById('resetBtn');
    const resetEmail = document.getElementById('resetEmail');
    const resetMsg = document.getElementById('resetMsg');

    forgotLink.addEventListener('click', function(e){
        e.preventDefault();
        modal.style.display = 'flex';
        resetMsg.innerHTML = '';
        resetEmail.value = '';
    });

    closeModal.addEventListener('click', function(){ modal.style.display = 'none'; });

    resetBtn.addEventListener('click', function(){
        const email = resetEmail.value.trim();
        if(email === ''){
            resetMsg.innerHTML = '<span style="color:red;">Please enter your email.</span>';
            return;
        }

        resetBtn.disabled = true;
        resetMsg.innerHTML = 'Sending...';

        fetch('forgot-password.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'email=' + encodeURIComponent(email)
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success'){
                resetMsg.innerHTML = '<span style="color:green;">' + data.message + '</span>';
            } else {
                resetMsg.innerHTML = '<span style="color:red;">' + data.message + '</span>';
            }
            resetBtn.disabled = false;
        })
        .catch(err => {
            resetMsg.innerHTML = '<span style="color:red;">Error sending request.</span>';
            resetBtn.disabled = false;
        });
    });
});

    </script>
</body>

</html>