<?php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// Debug mode
define('DEBUG', true);

// Get database connection
function getDBConnection() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }
    return $conn;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Get admin user data
function getAdminData($admin_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

// Handle logout
function handleLogout() {
    // Clear remember token from database
    if (isset($_SESSION['admin_id'])) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE admins SET remember_token = NULL WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['admin_id']);
            $stmt->execute();
        }
    }
    
    // Clear all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Clear remember me cookie
    setcookie("remember_admin", "", time() - 3600, "/");
    
    return true;
}

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: admin-login.php");
    exit;
}

// Get admin data for display
$admin_data = getAdminData($_SESSION['admin_id']);
$username = 'Admin';
$user_initials = 'AJ';

if ($admin_data) {
    $username = $admin_data['username'];
    $user_initials = strtoupper(substr($admin_data['username'], 0, 2));
    
    // Calculate session duration
    if (isset($_SESSION['login_time'])) {
        $login_time = $_SESSION['login_time'];
    } else {
        $login_time = time();
        $_SESSION['login_time'] = $login_time;
    }
    
    $session_duration = time() - $login_time;
    $hours = floor($session_duration / 3600);
    $minutes = floor(($session_duration % 3600) / 60);
    $session_duration_text = "";
    
    if ($hours > 0) {
        $session_duration_text .= $hours . " hour" . ($hours > 1 ? "s" : "");
    }
    if ($minutes > 0) {
        if ($hours > 0) $session_duration_text .= " ";
        $session_duration_text .= $minutes . " minute" . ($minutes > 1 ? "s" : "");
    }
    if ($hours == 0 && $minutes == 0) {
        $session_duration_text = "Less than a minute";
    }
}

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'logout') {
        if (handleLogout()) {
            // Redirect to login page
            header("Location: admin-login.php?logout=success");
            exit;
        }
    } elseif ($action === 'cancel') {
        // Redirect back to dashboard
        header("Location: admin/dashboard.php");
        exit;
    }
}

// Handle auto-logout (if session expired)
if (isset($_GET['timeout'])) {
    handleLogout();
    header("Location: admin-login.php?timeout=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Joseph's Pot Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8b4513;
            --primary-light: #a0522d;
            --primary-dark: #654321;
            --secondary: #d2691e;
            --accent: #ff7b54;
            --light: #fff8dc;
            --dark: #333333;
            --success: #4CAF50;
            --warning: #FF9800;
            --danger: #F44336;
            --info: #2196F3;
            --gray: #f5f5f5;
            --gray-dark: #e0e0e0;
            --text: #333333;
            --text-light: #666666;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .logout-container {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logout-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .logo-area {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .logo-area img {
            height: 50px;
            margin-right: 12px;
        }

        .logo-area h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .logout-header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .logout-header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .logout-content {
            padding: 30px;
        }

        .user-info {
            display: flex;
            align-items: center;
            background: var(--gray);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }

        .user-details h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .user-details p {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .logout-message {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background: rgba(139, 69, 19, 0.05);
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .logout-message i {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .logout-message h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .logout-message p {
            color: var(--text-light);
            line-height: 1.5;
        }

        .session-info {
            background: var(--gray);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .session-info h4 {
            font-size: 1rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .session-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }

        .session-details div {
            color: var(--text-light);
        }

        .logout-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--gray);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: var(--gray-dark);
            transform: translateY(-2px);
        }

        .logout-footer {
            text-align: center;
            padding: 20px;
            color: var(--text-light);
            font-size: 0.9rem;
            border-top: 1px solid var(--gray);
        }

        .security-tip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
            font-size: 0.8rem;
        }

        .security-tip i {
            color: var(--success);
        }

        /* Loading state */
        .btn.loading {
            position: relative;
            color: transparent;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-header">
            <div class="logo-area">
                <img src="./images/logo3.png" alt="Joseph's Pot Logo">
                <h1>Admin Panel</h1>
            </div>
            <h2>Logout Confirmation</h2>
            <p>You are about to sign out of your account</p>
        </div>
        
        <div class="logout-content">
            <div class="user-info">
                <div class="user-avatar"><?php echo $user_initials; ?></div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p>Super Administrator</p>
                </div>
            </div>
            
            <div class="logout-message">
                <i class="fas fa-sign-out-alt"></i>
                <h3>Are you sure you want to logout?</h3>
                <p>You will need to sign in again to access the admin dashboard. Any unsaved changes will be lost.</p>
            </div>
            
            <div class="session-info">
                <h4>Current Session</h4>
                <div class="session-details">
                    <div>Login Time</div>
                    <div id="loginTime"><?php echo date('F j, Y g:i A', $_SESSION['login_time'] ?? time()); ?></div>
                </div>
                <div class="session-details">
                    <div>Session Duration</div>
                    <div id="sessionDuration"><?php echo $session_duration_text ?? 'Active session'; ?></div>
                </div>
                <div class="session-details">
                    <div>Last Activity</div>
                    <div id="lastActivity">Just now</div>
                </div>
            </div>
            
            <!-- Simple form without JavaScript interference -->
            <form method="POST" action="" id="logoutForm">
                <input type="hidden" name="action" id="formAction" value="">
                <div class="logout-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        Yes, Logout
                    </button>
                </div>
            </form>
            
            <div class="security-tip">
                <i class="fas fa-shield-alt"></i>
                <span>For security, always logout when you're done</span>
            </div>
        </div>
        
        <div class="logout-footer">
            <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved</p>
            <p>Developed By ERIBS tech</p>
        </div>
    </div>

    <script>
        // DOM Elements
        const cancelBtn = document.getElementById('cancelBtn');
        const logoutBtn = document.getElementById('logoutBtn');
        const formAction = document.getElementById('formAction');
        const logoutForm = document.getElementById('logoutForm');

        // Cancel button - redirect to dashboard
        cancelBtn.addEventListener('click', function() {
            formAction.value = 'cancel';
            logoutForm.submit();
        });

        // Logout button - perform logout
        logoutBtn.addEventListener('click', function() {
            // Show loading state
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
            logoutBtn.disabled = true;
            cancelBtn.disabled = true;
            
            // Set form action and submit
            formAction.value = 'logout';
            logoutForm.submit();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape key to cancel
            if (e.key === 'Escape') {
                cancelBtn.click();
            }
            
            // Enter key to logout (with confirmation)
            if (e.key === 'Enter' && document.activeElement !== cancelBtn) {
                if (confirm('Are you sure you want to logout?')) {
                    logoutBtn.click();
                }
            }
        });
    </script>
</body>
</html>