<?php
// admin-settings.php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Get admin user data
function getAdminData($admin_id)
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        return null;
    }
    
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Joseph's Pot</title>
    <link rel="icon" href="../images/logo3.png">
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
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Mobile Menu Toggle Button */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: var(--shadow);
            align-items: center;
            justify-content: center;
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow);
            z-index: 999;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transform: translateX(0);
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .logo-area {
            display: flex;
            align-items: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .logo-area img {
            height: 40px;
            margin-right: 10px;
        }

        .logo-area h1 {
            font-size: 1.5rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .admin-info {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            margin: 0 15px 20px;
            border-radius: 10px;
            transition: var(--transition);
        }

        .admin-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .admin-details h3 {
            font-size: 1rem;
            margin-bottom: 3px;
        }

        .admin-details p {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .menu-items {
            list-style: none;
            padding: 0 15px;
        }

        .menu-item {
            margin-bottom: 5px;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .menu-item a:hover,
        .menu-item a.active {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .menu-item i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .menu-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 20px 15px 10px;
            opacity: 0.7;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
            transition: var(--transition);
            width: calc(100% - 260px);
        }

        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
            width: 100%;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
            justify-content: space-between;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border: none;
            border-radius: 30px;
            background: white;
            box-shadow: var(--shadow);
            width: 100%;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        /* FIXED NOTIFICATION AND USER MENU STYLES */
        .notification-user-container {
            display: flex;
            align-items: center;
            gap: 8px; /* Reduced gap */
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }

        .notification-icon:hover {
            background: var(--gray);
        }

        .notification-icon i {
            font-size: 1.3rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .notification-icon:hover i {
            color: var(--secondary);
        }

        .user-menu {
            position: relative;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }

        .user-menu:hover {
            background: var(--gray);
        }

        .user-menu i {
            font-size: 1.3rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .user-menu:hover i {
            color: var(--secondary);
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            pointer-events: none;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            z-index: 1000;
            display: none;
            margin-top: 5px;
        }

        .notification-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .notification-dropdown-header {
            padding: 15px;
            border-bottom: 1px solid var(--gray-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-dropdown-header h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .notification-dropdown-header .mark-all-read {
            background: none;
            border: none;
            color: var(--info);
            cursor: pointer;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .notification-dropdown-header .mark-all-read:hover {
            color: var(--primary);
        }

        .notification-list {
            list-style: none;
        }

        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid var(--gray);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .notification-item:hover {
            background: var(--gray);
        }

        .notification-item.unread {
            background: #f9f9f9;
        }

        .notification-dot {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 3px;
            color: var(--text);
        }

        .notification-message {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .notification-time {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .notification-empty {
            padding: 30px 20px;
            text-align: center;
            color: var(--text-light);
        }

        /* User Menu Dropdown */
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            z-index: 1000;
            display: none;
            margin-top: 5px;
        }

        .user-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .user-dropdown-item {
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text);
            text-decoration: none;
            transition: var(--transition);
            border-bottom: 1px solid var(--gray);
        }

        .user-dropdown-item:last-child {
            border-bottom: none;
        }

        .user-dropdown-item:hover {
            background: var(--gray);
        }

        .user-dropdown-item i {
            width: 20px;
            text-align: center;
        }

        /* Real-time Clock Styles */
        .real-time-clock {
            background: white;
            border-radius: 10px;
            padding: 12px 20px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 4px solid var(--primary);
            flex-wrap: wrap;
        }

        .clock-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .clock-icon {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .time-display {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
        }

        .date-display {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        /* Settings Styles */
        .settings-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
        }

        .settings-sidebar {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .settings-nav {
            list-style: none;
        }

        .settings-nav-item {
            margin-bottom: 5px;
        }

        .settings-nav-item a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .settings-nav-item a:hover,
        .settings-nav-item a.active {
            background: var(--primary);
            color: white;
        }

        .settings-nav-item i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .settings-content {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
        }

        .settings-section {
            margin-bottom: 30px;
            display: none;
        }

        .settings-section.active {
            display: block;
        }

        .settings-section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary);
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-dark);
            border-radius: 6px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input {
            width: auto;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: var(--primary);
        }

        input:checked+.toggle-slider:before {
            transform: translateX(30px);
        }

        .settings-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: var(--gray);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: var(--gray-dark);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #d32f2f;
        }

        .card {
            background: var(--gray);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .card-title {
            font-weight: 600;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        .card-action-btn {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-size: 0.9rem;
        }

        .card-action-btn:hover {
            text-decoration: underline;
        }

        .admin-list {
            list-style: none;
        }

        .admin-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-dark);
        }

        .admin-item:last-child {
            border-bottom: none;
        }

        .admin-avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: bold;
            color: white;
        }

        .admin-details-sm {
            flex: 1;
        }

        .admin-details-sm h4 {
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .admin-details-sm p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .admin-role {
            font-size: 0.8rem;
            padding: 4px 10px;
            background: var(--light);
            color: var(--primary);
            border-radius: 20px;
        }

        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            margin-right: 10px;
            border: 1px solid var(--gray-dark);
        }

        .theme-option {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--gray-dark);
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: var(--transition);
        }

        .theme-option:hover {
            border-color: var(--primary);
        }

        .theme-option.active {
            border-color: var(--primary);
            background: rgba(139, 69, 19, 0.05);
        }

        .theme-info {
            flex: 1;
        }

        .theme-name {
            font-weight: 500;
            margin-bottom: 3px;
        }

        .theme-desc {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 30px;
            color: var(--text-light);
            font-size: 0.9rem;
            border-top: 1px solid var(--gray-dark);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scroll Reveal Animation Styles */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .settings-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding-top: 70px;
            }

            .header h2 {
                font-size: 1.5rem;
            }

            .notification-dropdown {
                position: fixed;
                top: 70px;
                right: 15px;
                left: 15px;
                width: auto;
                max-height: 60vh;
            }

            .user-dropdown {
                position: fixed;
                top: 70px;
                right: 15px;
                width: calc(100% - 30px);
            }

            .settings-sidebar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .search-box {
                max-width: 100%;
            }

            .real-time-clock {
                flex-direction: column;
                align-items: flex-start;
            }

            .clock-container {
                margin-bottom: 15px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .notification-user-container {
                align-self: flex-end;
                margin-left: auto;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }

            .settings-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .notification-dropdown,
            .user-dropdown {
                width: calc(100% - 30px);
                left: 15px;
                right: 15px;
            }
        }

        @media (max-width: 480px) {
            .logo-area h1 {
                font-size: 1.2rem;
            }

            .header h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="logo-area">
                <img src="../images/logo3.png" alt="Joseph's Pot Logo">
                <h1>Admin Panel</h1>
            </div>

            <div class="admin-info">
                <div class="admin-avatar"><?php echo $user_initials; ?></div>
                <div class="admin-details">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p>Super Admin</p>
                </div>
            </div>

            <ul class="menu-items">
                <li class="menu-label">Main</li>
                <li class="menu-item">
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-contact-messages.php">
                        <i class="fas fa-envelope"></i>
                        <span>Contact Messages</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-menu-management.php">
                        <i class="fas fa-utensils"></i>
                        <span>Menu Management</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-reservation.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Reservations</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-orders.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-order-online-menu.php">
                        <i class="fas fa-car"></i>
                        <span>Order-Online Menu</span>
                    </a>
                </li>

                <li class="menu-label">Content</li>
                <!-- <li class="menu-item">
                    <a href="admin-customers.php">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li> -->
                <li class="menu-item">
                    <a href="admin-reviews.php">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-events.php">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-gallery.php">
                        <i class="fas fa-image"></i>
                        <span>Gallery</span>
                    </a>
                </li>

                <li class="menu-label">Settings</li>
                <li class="menu-item">
                    <a href="#" class="active">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-logout.php" onclick="return confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Real-time Clock -->
            <div class="real-time-clock reveal">
                <div class="clock-container">
                    <i class="fas fa-clock clock-icon"></i>
                    <div>
                        <div class="time-display" id="currentTime">Loading...</div>
                        <div class="date-display" id="currentDate">Loading...</div>
                    </div>
                </div>
                <div class="location-info">
                    <i class="fas fa-map-marker-alt"></i> Owerri, Nigeria
                </div>
            </div>

            <div class="header">
                <h2>Admin Settings</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search settings...">
                    </div>
                    <div class="notification-user-container">
                        <div class="notification-icon" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">2</span>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-dropdown-header">
                                    <h4>Notifications</h4>
                                    <button class="mark-all-read" id="markAllRead">Mark all as read</button>
                                </div>
                                <ul class="notification-list" id="notificationList">
                                    <!-- Notifications will be loaded here -->
                                </ul>
                            </div>
                        </div>
                        <div class="user-menu" id="userMenuIcon">
                            <i class="fas fa-user-circle"></i>
                            <div class="user-dropdown" id="userDropdown">
                                <a href="#" class="user-dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="#" class="user-dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Account Settings</span>
                                </a>
                                <a href="#" class="user-dropdown-item">
                                    <i class="fas fa-question-circle"></i>
                                    <span>Help & Support</span>
                                </a>
                                <a href="admin-logout.php" class="user-dropdown-item" onclick="return confirmLogout()">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Container -->
            <div class="settings-container">
                <!-- Settings Sidebar (Hidden on mobile) -->
                <div class="settings-sidebar reveal">
                    <ul class="settings-nav">
                        <li class="settings-nav-item">
                            <a href="#general" class="active">
                                <i class="fas fa-sliders-h"></i>
                                <span>General Settings</span>
                            </a>
                        </li>
                        <li class="settings-nav-item">
                            <a href="#restaurant">
                                <i class="fas fa-utensils"></i>
                                <span>Restaurant Info</span>
                            </a>
                        </li>
                        <li class="settings-nav-item">
                            <a href="#notifications">
                                <i class="fas fa-bell"></i>
                                <span>Notifications</span>
                            </a>
                        </li>
                        <li class="settings-nav-item">
                            <a href="#users">
                                <i class="fas fa-users"></i>
                                <span>User Management</span>
                            </a>
                        </li>
                        <li class="settings-nav-item">
                            <a href="#appearance">
                                <i class="fas fa-palette"></i>
                                <span>Appearance</span>
                            </a>
                        </li>
                        <li class="settings-nav-item">
                            <a href="#security">
                                <i class="fas fa-shield-alt"></i>
                                <span>Security</span>
                            </a>
                        </li>
                        <li class="settings-nav-item">
                            <a href="#backup">
                                <i class="fas fa-database"></i>
                                <span>Backup & Restore</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Settings Content -->
                <div class="settings-content reveal">
                    <!-- General Settings -->
                    <div class="settings-section active" id="general-settings">
                        <h3>General Settings</h3>

                        <div class="form-group">
                            <label for="siteName">Site Name</label>
                            <input type="text" id="siteName" value="Joseph's Pot">
                        </div>

                        <div class="form-group">
                            <label for="siteDescription">Site Description</label>
                            <textarea id="siteDescription">Authentic Nigerian cuisine restaurant offering traditional dishes in a warm and welcoming atmosphere.</textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="currency">Currency</label>
                                <select id="currency">
                                    <option value="NGN" selected>Nigerian Naira (₦)</option>
                                    <option value="USD">US Dollar ($)</option>
                                    <option value="EUR">Euro (€)</option>
                                    <option value="GBP">British Pound (£)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="timezone">Timezone</label>
                                <select id="timezone">
                                    <option value="Africa/Lagos" selected>West Africa Time (WAT)</option>
                                    <option value="UTC">UTC</option>
                                    <option value="America/New_York">Eastern Time (ET)</option>
                                    <option value="Europe/London">Greenwich Mean Time (GMT)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dateFormat">Date Format</label>
                            <select id="dateFormat">
                                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                <option value="DD/MM/YYYY" selected>DD/MM/YYYY</option>
                                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" id="maintenanceMode">
                                <span>Enable Maintenance Mode</span>
                            </label>
                            <small style="display: block; margin-top: 5px; color: var(--text-light);">When enabled,
                                the site will be temporarily unavailable to visitors.</small>
                        </div>

                        <div class="settings-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Restaurant Info -->
                    <div class="settings-section" id="restaurant-settings">
                        <h3>Restaurant Information</h3>

                        <div class="form-group">
                            <label for="restaurantName">Restaurant Name</label>
                            <input type="text" id="restaurantName" value="Joseph's Pot">
                        </div>

                        <div class="form-group">
                            <label for="restaurantTagline">Tagline</label>
                            <input type="text" id="restaurantTagline" value="Authentic Nigerian Cuisine">
                        </div>

                        <div class="form-group">
                            <label for="restaurantAddress">Address</label>
                            <textarea id="restaurantAddress">123 Food Street, Victoria Island, Lagos, Nigeria</textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="restaurantPhone">Phone Number</label>
                                <input type="text" id="restaurantPhone" value="+234 801 234 5678">
                            </div>
                            <div class="form-group">
                                <label for="restaurantEmail">Email Address</label>
                                <input type="email" id="restaurantEmail" value="info@josephspot.com">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="openingHours">Opening Hours</label>
                            <textarea id="openingHours">Monday - Friday: 8:00 AM - 10:00 PM
Saturday - Sunday: 9:00 AM - 11:00 PM</textarea>
                        </div>

                        <div class="settings-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="settings-section" id="notifications-settings">
                        <h3>Notification Settings</h3>

                        <div class="form-group">
                            <label>Email Notifications</label>
                            <div class="checkbox-group">
                                <input type="checkbox" id="emailOrders" checked>
                                <span>New orders</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="emailReservations" checked>
                                <span>New reservations</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="emailReviews">
                                <span>New reviews</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="emailPromotions" checked>
                                <span>Promotions & updates</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Push Notifications</label>
                            <div class="checkbox-group">
                                <input type="checkbox" id="pushOrders" checked>
                                <span>New orders</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="pushReservations">
                                <span>New reservations</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="pushLowStock" checked>
                                <span>Low stock alerts</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notificationSound">Notification Sound</label>
                            <select id="notificationSound">
                                <option value="default" selected>Default</option>
                                <option value="chime">Chime</option>
                                <option value="bell">Bell</option>
                                <option value="ding">Ding</option>
                                <option value="none">None</option>
                            </select>
                        </div>

                        <div class="settings-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- User Management -->
                    <div class="settings-section" id="users-settings">
                        <h3>User Management</h3>

                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Administrators</div>
                                <div class="card-actions">
                                    <button class="card-action-btn" id="addAdminBtn">
                                        <i class="fas fa-plus"></i> Add Admin
                                    </button>
                                </div>
                            </div>

                            <ul class="admin-list">
                                <li class="admin-item">
                                    <div class="admin-avatar-sm">AJ</div>
                                    <div class="admin-details-sm">
                                        <h4>Admin Joseph</h4>
                                        <p>joseph@josephspot.com</p>
                                    </div>
                                    <div class="admin-role">Super Admin</div>
                                </li>
                                <li class="admin-item">
                                    <div class="admin-avatar-sm">MD</div>
                                    <div class="admin-details-sm">
                                        <h4>Manager David</h4>
                                        <p>david@josephspot.com</p>
                                    </div>
                                    <div class="admin-role">Manager</div>
                                </li>
                                <li class="admin-item">
                                    <div class="admin-avatar-sm">CS</div>
                                    <div class="admin-details-sm">
                                        <h4>Content Sarah</h4>
                                        <p>sarah@josephspot.com</p>
                                    </div>
                                    <div class="admin-role">Content Manager</div>
                                </li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="userRegistration">User Registration</label>
                            <div class="toggle-switch">
                                <input type="checkbox" id="userRegistration" checked>
                                <span class="toggle-slider"></span>
                            </div>
                            <small style="display: block; margin-top: 5px; color: var(--text-light);">Allow new users
                                to register accounts on the website.</small>
                        </div>

                        <div class="form-group">
                            <label for="defaultUserRole">Default User Role</label>
                            <select id="defaultUserRole">
                                <option value="customer" selected>Customer</option>
                                <option value="subscriber">Subscriber</option>
                            </select>
                        </div>

                        <div class="settings-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Appearance -->
                    <div class="settings-section" id="appearance-settings">
                        <h3>Appearance Settings</h3>

                        <div class="form-group">
                            <label>Theme</label>
                            <div class="theme-option active">
                                <div class="color-preview" style="background: #8b4513;"></div>
                                <div class="theme-info">
                                    <div class="theme-name">Warm Brown</div>
                                    <div class="theme-desc">Default theme with warm brown tones</div>
                                </div>
                            </div>
                            <div class="theme-option">
                                <div class="color-preview" style="background: #2c5530;"></div>
                                <div class="theme-info">
                                    <div class="theme-name">Forest Green</div>
                                    <div class="theme-desc">Nature-inspired green theme</div>
                                </div>
                            </div>
                            <div class="theme-option">
                                <div class="color-preview" style="background: #1e3a5f;"></div>
                                <div class="theme-info">
                                    <div class="theme-name">Ocean Blue</div>
                                    <div class="theme-desc">Cool blue color scheme</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="primaryColor">Primary Color</label>
                            <input type="color" id="primaryColor" value="#8b4513">
                        </div>

                        <div class="form-group">
                            <label for="logoUpload">Logo</label>
                            <input type="file" id="logoUpload" accept="image/*">
                            <small style="display: block; margin-top: 5px; color: var(--text-light);">Recommended size:
                                200x60 pixels</small>
                        </div>

                        <div class="form-group">
                            <label for="faviconUpload">Favicon</label>
                            <input type="file" id="faviconUpload" accept="image/*">
                            <small style="display: block; margin-top: 5px; color: var(--text-light);">Recommended size:
                                32x32 pixels</small>
                        </div>

                        <div class="settings-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="settings-section" id="security-settings">
                        <h3>Security Settings</h3>

                        <div class="form-group">
                            <label for="passwordMinLength">Minimum Password Length</label>
                            <input type="number" id="passwordMinLength" value="8" min="6" max="20">
                        </div>

                        <div class="form-group">
                            <label for="passwordRequireSpecial">Password Requirements</label>
                            <div class="checkbox-group">
                                <input type="checkbox" id="passwordRequireUppercase" checked>
                                <span>Require uppercase letters</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="passwordRequireLowercase" checked>
                                <span>Require lowercase letters</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="passwordRequireNumbers" checked>
                                <span>Require numbers</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="passwordRequireSpecial">
                                <span>Require special characters</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="sessionTimeout">Session Timeout (minutes)</label>
                            <input type="number" id="sessionTimeout" value="30" min="5" max="240">
                        </div>

                        <div class="form-group">
                            <label for="loginAttempts">Max Login Attempts</label>
                            <input type="number" id="loginAttempts" value="5" min="3" max="10">
                        </div>

                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" id="twoFactorAuth">
                                <span>Enable Two-Factor Authentication</span>
                            </label>
                        </div>

                        <div class="settings-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Backup & Restore -->
                    <div class="settings-section" id="backup-settings">
                        <h3>Backup & Restore</h3>

                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Latest Backups</div>
                                <div class="card-actions">
                                    <button class="card-action-btn" id="createBackupBtn">
                                        <i class="fas fa-plus"></i> Create Backup
                                    </button>
                                </div>
                            </div>

                            <ul class="admin-list">
                                <li class="admin-item">
                                    <div class="admin-details-sm">
                                        <h4>Full System Backup</h4>
                                        <p>Created on October 15, 2025 at 2:30 PM</p>
                                    </div>
                                    <div class="card-actions">
                                        <button class="card-action-btn">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button class="card-action-btn">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </li>
                                <li class="admin-item">
                                    <div class="admin-details-sm">
                                        <h4>Database Backup</h4>
                                        <p>Created on October 10, 2025 at 10:15 AM</p>
                                    </div>
                                    <div class="card-actions">
                                        <button class="card-action-btn">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button class="card-action-btn">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="autoBackup">Automatic Backups</label>
                            <div class="toggle-switch">
                                <input type="checkbox" id="autoBackup" checked>
                                <span class="toggle-slider"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="backupFrequency">Backup Frequency</label>
                            <select id="backupFrequency">
                                <option value="daily">Daily</option>
                                <option value="weekly" selected>Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="backupRetention">Backup Retention (days)</label>
                            <input type="number" id="backupRetention" value="30" min="7" max="365">
                        </div>

                        <div class="settings-actions">
                            <button class="btn btn-danger">
                                <i class="fas fa-redo"></i> Restore Defaults
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved | Developed By ERIBS tech</p>
            </div>
        </div>
    </div>

    <script>
        // Logout confirmation function
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }

        // Real-time Clock Functionality
        function updateClock() {
            const now = new Date();

            // Format time
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';

            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'

            // Add leading zeros
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            // Format date
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const dateString = now.toLocaleDateString('en-US', options);

            // Update the DOM
            document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
            document.getElementById('currentDate').textContent = dateString;
        }

        // Update the clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);

        // Sample notification data
        const notifications = [{
                id: 1,
                title: 'Settings Updated',
                message: 'General settings have been saved successfully',
                time: '2 minutes ago',
                unread: true
            },
            {
                id: 2,
                title: 'Backup Created',
                message: 'System backup created successfully',
                time: '1 hour ago',
                unread: true
            },
            {
                id: 3,
                title: 'New Admin Added',
                message: 'New administrator account has been created',
                time: '3 hours ago',
                unread: false
            },
            {
                id: 4,
                title: 'Security Alert',
                message: 'Multiple login attempts detected',
                time: '5 hours ago',
                unread: false
            },
            {
                id: 5,
                title: 'System Update',
                message: 'Settings module updated to version 2.1',
                time: '1 day ago',
                unread: false
            }
        ];

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const markAllReadBtn = document.getElementById('markAllRead');
        const notificationBadge = document.querySelector('.notification-badge');
        const userMenuIcon = document.getElementById('userMenuIcon');
        const userDropdown = document.getElementById('userDropdown');
        const settingsNavItems = document.querySelectorAll('.settings-nav-item a');
        const settingsSections = document.querySelectorAll('.settings-section');
        const themeOptions = document.querySelectorAll('.theme-option');
        const saveButtons = document.querySelectorAll('.btn-primary');
        const createBackupBtn = document.getElementById('createBackupBtn');
        const addAdminBtn = document.getElementById('addAdminBtn');

        // Mobile sidebar toggler functionality
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });

        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Close sidebar when clicking on a menu item on mobile
        const menuItems = document.querySelectorAll('.menu-item a');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        });

        // Notification functionality
        function renderNotifications() {
            notificationList.innerHTML = '';

            if (notifications.length === 0) {
                notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
                return;
            }

            notifications.forEach(notification => {
                const notificationItem = document.createElement('li');
                notificationItem.className = `notification-item ${notification.unread ? 'unread' : ''}`;
                notificationItem.dataset.id = notification.id;
                notificationItem.innerHTML = `
                    <div class="notification-dot" style="${notification.unread ? 'background: var(--primary)' : 'background: transparent'}"></div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${notification.time}</div>
                    </div>
                `;

                notificationItem.addEventListener('click', function() {
                    markAsRead(notification.id);
                });

                notificationList.appendChild(notificationItem);
            });

            // Update badge count
            updateNotificationBadge();
        }

        function updateNotificationBadge() {
            const unreadCount = notifications.filter(n => n.unread).length;
            if (notificationBadge) {
                notificationBadge.textContent = unreadCount;
                notificationBadge.style.display = unreadCount > 0 ? 'flex' : 'none';
            }
        }

        function markAsRead(notificationId) {
            const notification = notifications.find(n => n.id === notificationId);
            if (notification && notification.unread) {
                notification.unread = false;
                renderNotifications();
            }
        }

        function markAllAsRead() {
            notifications.forEach(notification => {
                notification.unread = false;
            });
            renderNotifications();
        }

        // Toggle notification dropdown
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('active');
            // Close user dropdown if open
            userDropdown.classList.remove('active');
        });

        // Toggle user dropdown
        userMenuIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
            // Close notification dropdown if open
            notificationDropdown.classList.remove('active');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
            if (!userMenuIcon.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });

        // Mark all as read button
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAllAsRead();
            });
        }

        // Settings Navigation
        settingsNavItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all items
                settingsNavItems.forEach(navItem => {
                    navItem.classList.remove('active');
                });

                // Add active class to clicked item
                this.classList.add('active');

                // Get target section
                const targetId = this.getAttribute('href').substring(1);

                // Hide all sections
                settingsSections.forEach(section => {
                    section.classList.remove('active');
                });

                // Show target section
                document.getElementById(`${targetId}-settings`).classList.add('active');
            });
        });

        // Theme Selection
        themeOptions.forEach(option => {
            option.addEventListener('click', function() {
                themeOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Save Settings
        saveButtons.forEach(button => {
            button.addEventListener('click', function() {
                // In a real application, this would save the settings to a database
                alert('Settings saved successfully!');
            });
        });

        // Create Backup
        if (createBackupBtn) {
            createBackupBtn.addEventListener('click', function() {
                // In a real application, this would trigger a backup process
                alert('Backup creation started. You will be notified when it completes.');
            });
        }

        // Add Admin
        if (addAdminBtn) {
            addAdminBtn.addEventListener('click', function() {
                // In a real application, this would open a modal to add a new admin
                alert('This would open a form to add a new administrator.');
            });
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize notifications
            renderNotifications();

            // Initialize scroll reveal
            function revealOnScroll() {
                const reveals = document.querySelectorAll('.reveal');

                for (let i = 0; i < reveals.length; i++) {
                    const windowHeight = window.innerHeight;
                    const elementTop = reveals[i].getBoundingClientRect().top;
                    const elementVisible = 150;

                    if (elementTop < windowHeight - elementVisible) {
                        reveals[i].classList.add('active');
                    } else {
                        reveals[i].classList.remove('active');
                    }
                }
            }

            window.addEventListener('scroll', revealOnScroll);
            // Trigger once on load to check initial position
            revealOnScroll();
        });
    </script>
</body>
</html>