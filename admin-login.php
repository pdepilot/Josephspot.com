<?php

session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// Debug mode
define('DEBUG', false);

// Create database connection and setup
function setupDatabase() {
    // First connect without database to create it
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if ($conn->query($sql) === FALSE) {
        die("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    return $conn;
}

// Get database connection
function getDBConnection() {
    static $conn = null;
    if ($conn === null) {
        $conn = setupDatabase();
    }
    return $conn;
}

// Initialize database table
function initAdminTable() {
    $conn = getDBConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        remember_token VARCHAR(255) NULL,
        reset_token VARCHAR(64) NULL,
        reset_expires_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }
}

// Create a default admin (run once) - FIXED VERSION
function createDefaultAdmin() {
    $conn = getDBConnection();
    
    // First ensure the table exists
    initAdminTable();
    
    // Check if admin already exists
    $result = $conn->query("SELECT id FROM admins WHERE username = 'admin'");
    if ($result->num_rows === 0) {
        $password = 'admin123';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Use INSERT IGNORE to avoid duplicate errors
        $stmt = $conn->prepare("INSERT IGNORE INTO admins (username, email, password) VALUES (?, ?, ?)");
        
        if ($stmt) {
            $username = 'admin';
            $email = 'admin@josephspot.com';
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Verify the admin was created
                $verify_result = $conn->query("SELECT id FROM admins WHERE username = 'admin'");
                if ($verify_result->num_rows === 1) {
                    return "Default admin created: username='admin', password='admin123'";
                } else {
                    return "Admin creation failed - user not found after creation";
                }
            } else {
                return "Error creating admin: " . $stmt->error;
            }
        } else {
            return "Error preparing statement: " . $conn->error;
        }
    }
    return "Admin user already exists";
}

// Security Functions
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Brute Force Protection
function checkBruteForce($username) {
    $max_attempts = 5;
    $lockout_time = 300; // 5 minutes
    
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    
    if (!isset($_SESSION['lockout_time'])) {
        $_SESSION['lockout_time'] = 0;
    }
    
    if (time() < $_SESSION['lockout_time']) {
        $remaining = ceil(($_SESSION['lockout_time'] - time()) / 60);
        return "Too many failed login attempts. Try again in $remaining minutes.";
    }
    
    if ($_SESSION['login_attempts'] >= $max_attempts) {
        $_SESSION['lockout_time'] = time() + $lockout_time;
        $_SESSION['login_attempts'] = 0;
        return "Too many failed login attempts. Account locked for 5 minutes.";
    }
    
    return null;
}

function recordFailedAttempt() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    $_SESSION['login_attempts']++;
}

function clearFailedAttempts() {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

// Auto-login via Remember Me cookie
function checkRememberMe() {
    if (isset($_SESSION['admin_id'])) {
        return true;
    }
    
    if (isset($_COOKIE['remember_admin'])) {
        $cookie_data = explode(':', $_COOKIE['remember_admin']);
        if (count($cookie_data) === 2) {
            $admin_id = intval($cookie_data[0]);
            $token = $cookie_data[1];
            
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    if (password_verify($token, $admin['remember_token'])) {
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        return true;
                    }
                }
            }
        }
        // Invalid cookie, remove it
        setcookie("remember_admin", "", time() - 3600, "/");
    }
    
    return false;
}

// Login Function - FIXED VERSION
function handleLogin($username, $password, $remember = false) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (DEBUG) {
            error_log("Login attempt for user: " . $username);
            error_log("Stored hash: " . $admin['password']);
            error_log("Input password: " . $password);
            error_log("Password verify result: " . (password_verify($password, $admin['password']) ? 'true' : 'false'));
        }
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            clearFailedAttempts();
            
            // Handle Remember Me
            if ($remember) {
                $token = bin2hex(random_bytes(16));
                $hashed_token = password_hash($token, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE admins SET remember_token = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $hashed_token, $admin['id']);
                    $stmt->execute();
                    
                    setcookie("remember_admin", $admin['id'] . ':' . $token, 
                        time() + (30 * 24 * 60 * 60), "/", "", false, true);
                }
            }
            
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            if (DEBUG) {
                error_log("Password verification failed for user: " . $username);
            }
        }
    } else {
        if (DEBUG) {
            error_log("No user found with username: " . $username);
            // Debug: show all users in database
            $all_users = $conn->query("SELECT username FROM admins");
            error_log("All users in database: " . $all_users->num_rows);
            while ($user = $all_users->fetch_assoc()) {
                error_log("User: " . $user['username']);
            }
        }
    }
    
    recordFailedAttempt();
    return ['success' => false, 'message' => 'Invalid username or password'];
}

// Forgot Password Function
function handleForgotPassword($email) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, username FROM admins WHERE email = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error'];
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $reset_expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
        
        $stmt = $conn->prepare("UPDATE admins SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $reset_token, $reset_expires, $admin['id']);
            
            if ($stmt->execute()) {
                // In a real application, you would send an email here
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $reset_token;
                
                return [
                    'success' => true, 
                    'message' => "Password reset link has been generated.",
                    'reset_link' => $reset_link
                ];
            }
        }
    }
    
    return ['success' => true, 'message' => 'If the email exists in our system, a password reset link will be sent.'];
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Redirect if logged in - FIXED FOR admin-login.php
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        // Redirect to admin/dashboard.php
        header("Location: admin/dashboard.php");
        exit;
    }
}

// Initialize the application
function initApp() {
    initAdminTable();
    generateCSRFToken();
    
    // Check for auto-login via remember me
    checkRememberMe();
    
    // Removed auto-redirect to always show login page
    // redirectIfLoggedIn();
}

// Handle incoming requests
function handleRequest() {
    $response = ['success' => false, 'message' => ''];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'login':
                if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    $response['message'] = 'Invalid security token. Please refresh the page and try again.';
                    break;
                }
                
                $brute_force_check = checkBruteForce($_POST['username'] ?? '');
                if ($brute_force_check) {
                    $response['message'] = $brute_force_check;
                    break;
                }
                
                $username = cleanInput($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $remember = isset($_POST['remember']);
                
                if (empty($username) || empty($password)) {
                    $response['message'] = 'All fields are required';
                } else {
                    $response = handleLogin($username, $password, $remember);
                    
                    // If login successful, redirect immediately to admin/dashboard.php
                    if ($response['success']) {
                        // Set a session variable to show success message on redirect
                        $_SESSION['login_success'] = true;
                        
                        // Redirect to admin/dashboard.php
                        header("Location: admin/dashboard.php");
                        exit;
                    }
                }
                break;
                
            case 'forgot_password':
                $email = cleanInput($_POST['email'] ?? '');
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $response['message'] = 'Please enter a valid email address';
                } else {
                    $response = handleForgotPassword($email);
                }
                break;
                
            default:
                $response['message'] = 'Invalid action';
        }
    }
    
    return $response;
}

// Initialize the application
initApp();

// Handle the request if it's a POST
$response = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = handleRequest();
    
    // If it's an AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// For demo purposes - create default admin on first run
$init_message = '';
if (isset($_GET['init']) && $_GET['init'] == '1') {
    $init_message = createDefaultAdmin();
}

// Removed auto-redirect to always show login page
// if (isLoggedIn()) {
//     redirectIfLoggedIn();
// }
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
        /* Your existing CSS styles here */
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

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(210, 105, 30, 0.6);
            background: linear-gradient(45deg, var(--accent), var(--brown-light));
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid rgba(46, 204, 113, 0.5);
            color: #2ecc71;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid rgba(231, 76, 60, 0.5);
            color: #e74c3c;
        }
        
        .alert-info {
            background: rgba(52, 152, 219, 0.2);
            border: 1px solid rgba(52, 152, 219, 0.5);
            color: #3498db;
        }

        .debug-info {
            background: rgba(0,0,0,0.3);
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 12px;
            color: #ccc;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            position: relative;
        }

        .modal-content h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .modal-content input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .modal-content button {
            width: 100%;
            padding: 10px;
            border: none;
            background: #d2691e;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-left">
            <div class="logo-container">
                <img src="./images/logo3.png" alt="Joseph's Pot Logo" onerror="this.style.display='none'" />
                <h1>JOSEPH'S POT</h1>
                <p>Premium Restaurant Management System</p>
            </div>
            <div class="features">
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <p>Secure Admin Access</p>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <p>Real-time Analytics</p>
                </div>
                <div class="feature">
                    <i class="fas fa-cogs"></i>
                    <p>Menu Management</p>
                </div>
                <div class="feature">
                    <i class="fas fa-users"></i>
                    <p>Staff Management</p>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="login-form-container">
                <div class="login-header">
                    <h2>ADMIN LOGIN</h2>
                    <p>Access your restaurant dashboard</p>
                </div>

                <?php if ($init_message): ?>
                    <div class="alert alert-info">
                        <?php echo $init_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($response['message']) && $response['message']): ?>
                    <div class="alert <?php echo $response['success'] ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo $response['message']; ?>
                        <?php if (isset($response['reset_link'])): ?>
                            <br><small>Reset Link: <?php echo $response['reset_link']; ?></small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form class="login-form" id="loginForm" method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username: admin" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : 'admin'; ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password: admin123" required value="admin123" />
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" name="remember" id="remember" />
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password" id="forgotPasswordLink">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i> SIGN IN
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" id="closeModal">&times;</button>
            <h3>Reset Password</h3>
            <p>Enter your registered email:</p>
            <input type="email" id="resetEmail" placeholder="admin@josephspot.com">
            <button id="resetBtn">Send Reset Link</button>
            <div id="resetMsg" style="margin-top:10px;"></div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Password toggle functionality
            const passwordToggle = document.getElementById("passwordToggle");
            const passwordInput = document.getElementById("password");

            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener("click", function() {
                    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
                    passwordInput.setAttribute("type", type);
                    this.innerHTML = type === "password" ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
            }

            // Form submission loading state
            const loginForm = document.getElementById("loginForm");
            if (loginForm) {
                loginForm.addEventListener("submit", function() {
                    const loginBtn = this.querySelector('.login-btn');
                    if (loginBtn) {
                        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> AUTHENTICATING...';
                        loginBtn.disabled = true;
                    }
                });
            }

            // Forgot Password Modal JS - FIXED VERSION
            const forgotLink = document.getElementById('forgotPasswordLink');
            const modal = document.getElementById('forgotModal');
            const closeModal = document.getElementById('closeModal');
            const resetBtn = document.getElementById('resetBtn');
            const resetEmail = document.getElementById('resetEmail');
            const resetMsg = document.getElementById('resetMsg');

            forgotLink.addEventListener('click', function(e){
                e.preventDefault();
                modal.style.display = 'flex';
                resetMsg.innerHTML = '';
                resetEmail.value = 'admin@josephspot.com'; 
            });

            closeModal.addEventListener('click', function(){ 
                modal.style.display = 'none'; 
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e){
                if(e.target === modal){
                    modal.style.display = 'none';
                }
            });

            resetBtn.addEventListener('click', function(){
                const email = resetEmail.value.trim();
                if(email === ''){
                    resetMsg.innerHTML = '<span style="color:red;">Please enter your email.</span>';
                    return;
                }

                resetBtn.disabled = true;
                resetBtn.innerHTML = 'Sending...';
                resetMsg.innerHTML = '';

                // Create form data
                const formData = new FormData();
                formData.append('action', 'forgot_password');
                formData.append('email', email);
                formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

                // Add XMLHttpRequest header to ensure PHP treats this as AJAX
                fetch('', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if(data.success){
                        resetMsg.innerHTML = '<span style="color:green;">' + data.message + '</span>';
                        if(data.reset_link){
                            resetMsg.innerHTML += '<br><small><strong>Reset Link:</strong> ' + data.reset_link + '</small>';
                            // Also log to console for easy access
                            console.log('Password reset link:', data.reset_link);
                        }
                        // Clear the email field after successful request
                        resetEmail.value = '';
                    } else {
                        resetMsg.innerHTML = '<span style="color:red;">' + data.message + '</span>';
                    }
                    resetBtn.disabled = false;
                    resetBtn.innerHTML = 'Send Reset Link';
                })
                .catch(err => {
                    console.error('Error:', err);
                    resetMsg.innerHTML = '<span style="color:red;">Error sending request. Please check console for details.</span>';
                    resetBtn.disabled = false;
                    resetBtn.innerHTML = 'Send Reset Link';
                });
            });

            // Allow pressing Enter in the email field to trigger reset
            resetEmail.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    resetBtn.click();
                }
            });
        });
    </script>
</body>
</html>