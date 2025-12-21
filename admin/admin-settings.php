<?php
// admin-settings.php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// PDO Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Get admin user data
function getAdminData($pdo, $admin_id)
{
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

// CSRF Token Generation
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token Validation
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get setting helper function
function get_setting($pdo, $section, $key, $default = '') {
    try {
        $table = $section . '_settings';
        $stmt = $pdo->prepare("SELECT setting_value FROM `{$table}` WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch(PDOException $e) {
        return $default;
    }
}

// Set setting helper function
function set_setting($pdo, $section, $key, $value) {
    try {
        $table = $section . '_settings';
        $sql = "INSERT INTO `{$table}` (setting_key, setting_value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$key, $value]);
    } catch(PDOException $e) {
        return false;
    }
}

// Get notification setting (can be per admin)
function get_notification_setting($pdo, $key, $admin_id = null, $default = '0') {
    try {
        if ($admin_id) {
            $stmt = $pdo->prepare("SELECT setting_value FROM notification_settings WHERE setting_key = ? AND admin_id = ? LIMIT 1");
            $stmt->execute([$key, $admin_id]);
        } else {
            $stmt = $pdo->prepare("SELECT setting_value FROM notification_settings WHERE setting_key = ? AND admin_id IS NULL LIMIT 1");
            $stmt->execute([$key]);
        }
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch(PDOException $e) {
        return $default;
    }
}

// Set notification setting
function set_notification_setting($pdo, $key, $value, $admin_id = null) {
    try {
        if ($admin_id) {
            $sql = "INSERT INTO notification_settings (setting_key, setting_value, admin_id) VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$key, $value, $admin_id]);
        } else {
            $sql = "INSERT INTO notification_settings (setting_key, setting_value, admin_id) VALUES (?, ?, NULL) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$key, $value]);
        }
    } catch(PDOException $e) {
        return false;
    }
}

// Get all admins
function get_all_admins($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM admins ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: admin-login.php");
    exit;
}

// Get admin data for display
$admin_data = getAdminData($pdo, $_SESSION['admin_id']);
$username = 'Admin';
$user_initials = 'AJ';
$is_super_admin = false;

if ($admin_data) {
    $username = $admin_data['username'];
    $user_initials = strtoupper(substr($admin_data['username'], 0, 2));
    $is_super_admin = ($admin_data['role'] === 'super_admin');
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Load all settings
$settings = [
    'general' => [
        'site_name' => get_setting($pdo, 'general', 'site_name', "Joseph's Pot"),
        'site_description' => get_setting($pdo, 'general', 'site_description', 'Authentic Nigerian cuisine restaurant offering traditional dishes in a warm and welcoming atmosphere.'),
        'currency' => get_setting($pdo, 'general', 'currency', 'NGN'),
        'timezone' => get_setting($pdo, 'general', 'timezone', 'Africa/Lagos'),
        'date_format' => get_setting($pdo, 'general', 'date_format', 'DD/MM/YYYY'),
        'maintenance_mode' => get_setting($pdo, 'general', 'maintenance_mode', '0')
    ],
    'restaurant' => [
        'restaurant_name' => get_setting($pdo, 'restaurant', 'restaurant_name', "Joseph's Pot"),
        'restaurant_tagline' => get_setting($pdo, 'restaurant', 'restaurant_tagline', 'Authentic Nigerian Cuisine'),
        'restaurant_address' => get_setting($pdo, 'restaurant', 'restaurant_address', '123 Food Street, Victoria Island, Lagos, Nigeria'),
        'restaurant_phone' => get_setting($pdo, 'restaurant', 'restaurant_phone', '+234 801 234 5678'),
        'restaurant_email' => get_setting($pdo, 'restaurant', 'restaurant_email', 'info@josephspot.com'),
        'opening_hours' => get_setting($pdo, 'restaurant', 'opening_hours', "Monday - Friday: 8:00 AM - 10:00 PM\nSaturday - Sunday: 9:00 AM - 11:00 PM")
    ],
    'notifications' => [
        'email_orders' => get_notification_setting($pdo, 'email_orders', $_SESSION['admin_id'], '1'),
        'email_reservations' => get_notification_setting($pdo, 'email_reservations', $_SESSION['admin_id'], '1'),
        'email_reviews' => get_notification_setting($pdo, 'email_reviews', $_SESSION['admin_id'], '0'),
        'email_promotions' => get_notification_setting($pdo, 'email_promotions', $_SESSION['admin_id'], '1'),
        'push_orders' => get_notification_setting($pdo, 'push_orders', $_SESSION['admin_id'], '1'),
        'push_reservations' => get_notification_setting($pdo, 'push_reservations', $_SESSION['admin_id'], '0'),
        'push_low_stock' => get_notification_setting($pdo, 'push_low_stock', $_SESSION['admin_id'], '1'),
        'notification_sound' => get_notification_setting($pdo, 'notification_sound', $_SESSION['admin_id'], 'default')
    ],
    'security' => [
        'password_min_length' => get_setting($pdo, 'security', 'password_min_length', '8'),
        'password_require_uppercase' => get_setting($pdo, 'security', 'password_require_uppercase', '1'),
        'password_require_lowercase' => get_setting($pdo, 'security', 'password_require_lowercase', '1'),
        'password_require_numbers' => get_setting($pdo, 'security', 'password_require_numbers', '1'),
        'password_require_special' => get_setting($pdo, 'security', 'password_require_special', '0'),
        'session_timeout' => get_setting($pdo, 'security', 'session_timeout', '30'),
        'login_attempts' => get_setting($pdo, 'security', 'login_attempts', '5'),
        'two_factor_auth' => get_setting($pdo, 'security', 'two_factor_auth', '0')
    ],
    'appearance' => [
        'theme' => get_setting($pdo, 'appearance', 'theme', 'warm_brown'),
        'primary_color' => get_setting($pdo, 'appearance', 'primary_color', '#8b4513'),
        'logo_path' => get_setting($pdo, 'appearance', 'logo_path', ''),
        'favicon_path' => get_setting($pdo, 'appearance', 'favicon_path', '')
    ]
];

// Get all admins for user management
$all_admins = get_all_admins($pdo);
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
                            <input type="text" id="siteName" value="<?php echo htmlspecialchars($settings['general']['site_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="siteDescription">Site Description</label>
                            <textarea id="siteDescription"><?php echo htmlspecialchars($settings['general']['site_description']); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="currency">Currency</label>
                                <select id="currency">
                                    <option value="NGN" <?php echo $settings['general']['currency'] === 'NGN' ? 'selected' : ''; ?>>Nigerian Naira (₦)</option>
                                    <option value="USD" <?php echo $settings['general']['currency'] === 'USD' ? 'selected' : ''; ?>>US Dollar ($)</option>
                                    <option value="EUR" <?php echo $settings['general']['currency'] === 'EUR' ? 'selected' : ''; ?>>Euro (€)</option>
                                    <option value="GBP" <?php echo $settings['general']['currency'] === 'GBP' ? 'selected' : ''; ?>>British Pound (£)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="timezone">Timezone</label>
                                <select id="timezone">
                                    <option value="Africa/Lagos" <?php echo $settings['general']['timezone'] === 'Africa/Lagos' ? 'selected' : ''; ?>>West Africa Time (WAT)</option>
                                    <option value="UTC" <?php echo $settings['general']['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    <option value="America/New_York" <?php echo $settings['general']['timezone'] === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time (ET)</option>
                                    <option value="Europe/London" <?php echo $settings['general']['timezone'] === 'Europe/London' ? 'selected' : ''; ?>>Greenwich Mean Time (GMT)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dateFormat">Date Format</label>
                            <select id="dateFormat">
                                <option value="MM/DD/YYYY" <?php echo $settings['general']['date_format'] === 'MM/DD/YYYY' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                <option value="DD/MM/YYYY" <?php echo $settings['general']['date_format'] === 'DD/MM/YYYY' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                <option value="YYYY-MM-DD" <?php echo $settings['general']['date_format'] === 'YYYY-MM-DD' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" id="maintenanceMode" <?php echo $settings['general']['maintenance_mode'] === '1' ? 'checked' : ''; ?>>
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
                            <input type="text" id="restaurantName" value="<?php echo htmlspecialchars($settings['restaurant']['restaurant_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="restaurantTagline">Tagline</label>
                            <input type="text" id="restaurantTagline" value="<?php echo htmlspecialchars($settings['restaurant']['restaurant_tagline']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="restaurantAddress">Address</label>
                            <textarea id="restaurantAddress"><?php echo htmlspecialchars($settings['restaurant']['restaurant_address']); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="restaurantPhone">Phone Number</label>
                                <input type="text" id="restaurantPhone" value="<?php echo htmlspecialchars($settings['restaurant']['restaurant_phone']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="restaurantEmail">Email Address</label>
                                <input type="email" id="restaurantEmail" value="<?php echo htmlspecialchars($settings['restaurant']['restaurant_email']); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="openingHours">Opening Hours</label>
                            <textarea id="openingHours"><?php echo htmlspecialchars($settings['restaurant']['opening_hours']); ?></textarea>
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
                                <input type="checkbox" id="emailOrders" <?php echo $settings['notifications']['email_orders'] === '1' ? 'checked' : ''; ?>>
                                <span>New orders</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="emailReservations" <?php echo $settings['notifications']['email_reservations'] === '1' ? 'checked' : ''; ?>>
                                <span>New reservations</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="emailReviews" <?php echo $settings['notifications']['email_reviews'] === '1' ? 'checked' : ''; ?>>
                                <span>New reviews</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="emailPromotions" <?php echo $settings['notifications']['email_promotions'] === '1' ? 'checked' : ''; ?>>
                                <span>Promotions & updates</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Push Notifications</label>
                            <div class="checkbox-group">
                                <input type="checkbox" id="pushOrders" <?php echo $settings['notifications']['push_orders'] === '1' ? 'checked' : ''; ?>>
                                <span>New orders</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="pushReservations" <?php echo $settings['notifications']['push_reservations'] === '1' ? 'checked' : ''; ?>>
                                <span>New reservations</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="pushLowStock" <?php echo $settings['notifications']['push_low_stock'] === '1' ? 'checked' : ''; ?>>
                                <span>Low stock alerts</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notificationSound">Notification Sound</label>
                            <select id="notificationSound">
                                <option value="default" <?php echo $settings['notifications']['notification_sound'] === 'default' ? 'selected' : ''; ?>>Default</option>
                                <option value="chime" <?php echo $settings['notifications']['notification_sound'] === 'chime' ? 'selected' : ''; ?>>Chime</option>
                                <option value="bell" <?php echo $settings['notifications']['notification_sound'] === 'bell' ? 'selected' : ''; ?>>Bell</option>
                                <option value="ding" <?php echo $settings['notifications']['notification_sound'] === 'ding' ? 'selected' : ''; ?>>Ding</option>
                                <option value="none" <?php echo $settings['notifications']['notification_sound'] === 'none' ? 'selected' : ''; ?>>None</option>
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
                                <?php if (empty($all_admins)): ?>
                                    <li class="admin-item">
                                        <div class="admin-details-sm">
                                            <p>No administrators found.</p>
                                        </div>
                                    </li>
                                <?php else: ?>
                                    <?php foreach ($all_admins as $admin): 
                                        $admin_initials = strtoupper(substr($admin['username'], 0, 2));
                                        $admin_role = ucfirst(str_replace('_', ' ', $admin['role'] ?? 'manager'));
                                    ?>
                                    <li class="admin-item">
                                        <div class="admin-avatar-sm"><?php echo htmlspecialchars($admin_initials); ?></div>
                                        <div class="admin-details-sm">
                                            <h4><?php echo htmlspecialchars($admin['username']); ?></h4>
                                            <p><?php echo htmlspecialchars($admin['email']); ?></p>
                                        </div>
                                        <div class="admin-role"><?php echo htmlspecialchars($admin_role); ?></div>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="userRegistration">User Registration</label>
                            <div class="toggle-switch">
                                <input type="checkbox" id="userRegistration" <?php echo get_setting($pdo, 'general', 'user_registration', '1') === '1' ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </div>
                            <small style="display: block; margin-top: 5px; color: var(--text-light);">Allow new users
                                to register accounts on the website.</small>
                        </div>

                        <div class="form-group">
                            <label for="defaultUserRole">Default User Role</label>
                            <select id="defaultUserRole">
                                <option value="customer" <?php echo get_setting($pdo, 'general', 'default_user_role', 'customer') === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                <option value="subscriber" <?php echo get_setting($pdo, 'general', 'default_user_role', 'customer') === 'subscriber' ? 'selected' : ''; ?>>Subscriber</option>
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
                            <div class="theme-option <?php echo $settings['appearance']['theme'] === 'warm_brown' ? 'active' : ''; ?>" data-theme="warm_brown">
                                <div class="color-preview" style="background: #8b4513;"></div>
                                <div class="theme-info">
                                    <div class="theme-name">Warm Brown</div>
                                    <div class="theme-desc">Default theme with warm brown tones</div>
                                </div>
                            </div>
                            <div class="theme-option <?php echo $settings['appearance']['theme'] === 'forest_green' ? 'active' : ''; ?>" data-theme="forest_green">
                                <div class="color-preview" style="background: #2c5530;"></div>
                                <div class="theme-info">
                                    <div class="theme-name">Forest Green</div>
                                    <div class="theme-desc">Nature-inspired green theme</div>
                                </div>
                            </div>
                            <div class="theme-option <?php echo $settings['appearance']['theme'] === 'ocean_blue' ? 'active' : ''; ?>" data-theme="ocean_blue">
                                <div class="color-preview" style="background: #1e3a5f;"></div>
                                <div class="theme-info">
                                    <div class="theme-name">Ocean Blue</div>
                                    <div class="theme-desc">Cool blue color scheme</div>
                                </div>
                            </div>
                            <input type="hidden" id="selectedTheme" value="<?php echo htmlspecialchars($settings['appearance']['theme']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="primaryColor">Primary Color</label>
                            <input type="color" id="primaryColor" value="<?php echo htmlspecialchars($settings['appearance']['primary_color']); ?>">
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
                            <input type="number" id="passwordMinLength" value="<?php echo htmlspecialchars($settings['security']['password_min_length']); ?>" min="6" max="20">
                        </div>

                        <div class="form-group">
                            <label for="passwordRequireSpecial">Password Requirements</label>
                            <div class="checkbox-group">
                                <input type="checkbox" id="passwordRequireUppercase" <?php echo $settings['security']['password_require_uppercase'] === '1' ? 'checked' : ''; ?>>
                                <span>Require uppercase letters</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="passwordRequireLowercase" <?php echo $settings['security']['password_require_lowercase'] === '1' ? 'checked' : ''; ?>>
                                <span>Require lowercase letters</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="passwordRequireNumbers" <?php echo $settings['security']['password_require_numbers'] === '1' ? 'checked' : ''; ?>>
                                <span>Require numbers</span>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="passwordRequireSpecial" <?php echo $settings['security']['password_require_special'] === '1' ? 'checked' : ''; ?>>
                                <span>Require special characters</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="sessionTimeout">Session Timeout (minutes)</label>
                            <input type="number" id="sessionTimeout" value="<?php echo htmlspecialchars($settings['security']['session_timeout']); ?>" min="5" max="240">
                        </div>

                        <div class="form-group">
                            <label for="loginAttempts">Max Login Attempts</label>
                            <input type="number" id="loginAttempts" value="<?php echo htmlspecialchars($settings['security']['login_attempts']); ?>" min="3" max="10">
                        </div>

                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" id="twoFactorAuth" <?php echo $settings['security']['two_factor_auth'] === '1' ? 'checked' : ''; ?>>
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

                            <ul class="admin-list" id="backupList">
                                <?php
                                // Load backups from database
                                try {
                                    $stmt = $pdo->query("SELECT meta_key, meta_value, created_at FROM admin_settings_meta WHERE meta_type = 'backup' ORDER BY created_at DESC LIMIT 10");
                                    $backups = $stmt->fetchAll();
                                    
                                    if (empty($backups)) {
                                        echo '<li class="admin-item"><div class="admin-details-sm"><p>No backups found. Create your first backup.</p></div></li>';
                                    } else {
                                        foreach ($backups as $backup) {
                                            $meta = json_decode($backup['meta_value'], true);
                                            $filename = $backup['meta_key'];
                                            $created = new DateTime($backup['created_at']);
                                            $formatted_date = $created->format('F j, Y \a\t g:i A');
                                            $size = isset($meta['size']) ? number_format($meta['size'] / 1024, 2) . ' KB' : 'Unknown size';
                                            
                                            echo '<li class="admin-item" data-backup="' . htmlspecialchars($filename) . '">';
                                            echo '<div class="admin-details-sm">';
                                            echo '<h4>' . htmlspecialchars($filename) . '</h4>';
                                            echo '<p>Created on ' . htmlspecialchars($formatted_date) . ' (' . $size . ')</p>';
                                            echo '</div>';
                                            echo '<div class="card-actions">';
                                            echo '<button class="card-action-btn download-backup" data-file="' . htmlspecialchars($filename) . '">';
                                            echo '<i class="fas fa-download"></i> Download';
                                            echo '</button>';
                                            echo '<button class="card-action-btn delete-backup" data-file="' . htmlspecialchars($filename) . '">';
                                            echo '<i class="fas fa-trash"></i> Delete';
                                            echo '</button>';
                                            echo '</div>';
                                            echo '</li>';
                                        }
                                    }
                                } catch(PDOException $e) {
                                    echo '<li class="admin-item"><div class="admin-details-sm"><p>Error loading backups.</p></div></li>';
                                }
                                ?>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="autoBackup">Automatic Backups</label>
                            <div class="toggle-switch">
                                <input type="checkbox" id="autoBackup" <?php echo get_setting($pdo, 'general', 'auto_backup', '1') === '1' ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="backupFrequency">Backup Frequency</label>
                            <select id="backupFrequency">
                                <option value="daily" <?php echo get_setting($pdo, 'general', 'backup_frequency', 'weekly') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                <option value="weekly" <?php echo get_setting($pdo, 'general', 'backup_frequency', 'weekly') === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                <option value="monthly" <?php echo get_setting($pdo, 'general', 'backup_frequency', 'weekly') === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="backupRetention">Backup Retention (days)</label>
                            <input type="number" id="backupRetention" value="<?php echo htmlspecialchars(get_setting($pdo, 'general', 'backup_retention', '30')); ?>" min="7" max="365">
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
                // Update hidden input
                const hiddenInput = document.getElementById('selectedTheme');
                if (hiddenInput) {
                    hiddenInput.value = this.dataset.theme;
                }
            });
        });

        // CSRF Token
        const csrfToken = '<?php echo $csrf_token; ?>';
        
        // Save Settings
        saveButtons.forEach(button => {
            button.addEventListener('click', async function() {
                const section = this.closest('.settings-section');
                if (!section) return;
                
                // Determine which section
                let sectionName = '';
                let data = {};
                
                if (section.id === 'general-settings') {
                    sectionName = 'general';
                    data = {
                        site_name: document.getElementById('siteName').value,
                        site_description: document.getElementById('siteDescription').value,
                        currency: document.getElementById('currency').value,
                        timezone: document.getElementById('timezone').value,
                        date_format: document.getElementById('dateFormat').value,
                        maintenance_mode: document.getElementById('maintenanceMode').checked ? '1' : '0'
                    };
                } else if (section.id === 'restaurant-settings') {
                    sectionName = 'restaurant';
                    data = {
                        restaurant_name: document.getElementById('restaurantName').value,
                        restaurant_tagline: document.getElementById('restaurantTagline').value,
                        restaurant_address: document.getElementById('restaurantAddress').value,
                        restaurant_phone: document.getElementById('restaurantPhone').value,
                        restaurant_email: document.getElementById('restaurantEmail').value,
                        opening_hours: document.getElementById('openingHours').value
                    };
                } else if (section.id === 'notifications-settings') {
                    sectionName = 'notifications';
                    data = {
                        email_orders: document.getElementById('emailOrders').checked ? '1' : '0',
                        email_reservations: document.getElementById('emailReservations').checked ? '1' : '0',
                        email_reviews: document.getElementById('emailReviews').checked ? '1' : '0',
                        email_promotions: document.getElementById('emailPromotions').checked ? '1' : '0',
                        push_orders: document.getElementById('pushOrders').checked ? '1' : '0',
                        push_reservations: document.getElementById('pushReservations').checked ? '1' : '0',
                        push_low_stock: document.getElementById('pushLowStock').checked ? '1' : '0',
                        notification_sound: document.getElementById('notificationSound').value
                    };
                } else if (section.id === 'security-settings') {
                    sectionName = 'security';
                    data = {
                        password_min_length: document.getElementById('passwordMinLength').value,
                        password_require_uppercase: document.getElementById('passwordRequireUppercase').checked ? '1' : '0',
                        password_require_lowercase: document.getElementById('passwordRequireLowercase').checked ? '1' : '0',
                        password_require_numbers: document.getElementById('passwordRequireNumbers').checked ? '1' : '0',
                        password_require_special: document.getElementById('passwordRequireSpecial').checked ? '1' : '0',
                        session_timeout: document.getElementById('sessionTimeout').value,
                        login_attempts: document.getElementById('loginAttempts').value,
                        two_factor_auth: document.getElementById('twoFactorAuth').checked ? '1' : '0'
                    };
                } else if (section.id === 'appearance-settings') {
                    sectionName = 'appearance';
                    const selectedTheme = document.querySelector('.theme-option.active');
                    data = {
                        theme: selectedTheme ? selectedTheme.dataset.theme : 'warm_brown',
                        primary_color: document.getElementById('primaryColor').value
                    };
                } else if (section.id === 'users-settings') {
                    sectionName = 'general'; // User management settings go to general
                    data = {
                        user_registration: document.getElementById('userRegistration').checked ? '1' : '0',
                        default_user_role: document.getElementById('defaultUserRole').value
                    };
                } else if (section.id === 'backup-settings') {
                    sectionName = 'general'; // Backup settings go to general
                    data = {
                        auto_backup: document.getElementById('autoBackup').checked ? '1' : '0',
                        backup_frequency: document.getElementById('backupFrequency').value,
                        backup_retention: document.getElementById('backupRetention').value
                    };
                }
                
                if (!sectionName) return;
                
                // Show loading state
                const originalText = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                
                try {
                    const formData = new FormData();
                    formData.append('section', sectionName);
                    formData.append('csrf_token', csrfToken);
                    formData.append('data', JSON.stringify(data));
                    
                    const response = await fetch('api/save_settings.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Show success message
                        showNotification('Settings saved successfully!', 'success');
                        
                        // If appearance settings, reload page after short delay to show changes
                        if (sectionName === 'appearance') {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    } else {
                        showNotification(result.message || 'Error saving settings', 'error');
                    }
                } catch (error) {
                    showNotification('Error saving settings. Please try again.', 'error');
                } finally {
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            });
        });
        
        // Notification function
        function showNotification(message, type = 'success') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification-toast ${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#4CAF50' : '#F44336'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                animation: slideInRight 0.3s ease;
                display: flex;
                align-items: center;
                gap: 10px;
            `;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Add CSS animations for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // File Upload Handlers
        const logoUpload = document.getElementById('logoUpload');
        const faviconUpload = document.getElementById('faviconUpload');
        
        if (logoUpload) {
            logoUpload.addEventListener('change', async function() {
                if (!this.files[0]) return;
                
                const formData = new FormData();
                formData.append('file', this.files[0]);
                formData.append('file_type', 'logo');
                formData.append('csrf_token', csrfToken);
                
                try {
                    const response = await fetch('api/upload_file.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showNotification('Logo uploaded successfully!', 'success');
                    } else {
                        showNotification(result.message || 'Error uploading logo', 'error');
                    }
                } catch (error) {
                    showNotification('Error uploading logo. Please try again.', 'error');
                }
            });
        }
        
        if (faviconUpload) {
            faviconUpload.addEventListener('change', async function() {
                if (!this.files[0]) return;
                
                const formData = new FormData();
                formData.append('file', this.files[0]);
                formData.append('file_type', 'favicon');
                formData.append('csrf_token', csrfToken);
                
                try {
                    const response = await fetch('api/upload_file.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showNotification('Favicon uploaded successfully!', 'success');
                    } else {
                        showNotification(result.message || 'Error uploading favicon', 'error');
                    }
                } catch (error) {
                    showNotification('Error uploading favicon. Please try again.', 'error');
                }
            });
        }

        // Create Backup
        if (createBackupBtn) {
            createBackupBtn.addEventListener('click', async function() {
                if (!confirm('Create a backup of all settings? This may take a few moments.')) {
                    return;
                }
                
                const originalText = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'create_backup');
                    formData.append('csrf_token', csrfToken);
                    
                    const response = await fetch('api/backup_settings.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showNotification('Backup created successfully!', 'success');
                        // Reload page to show new backup
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(result.message || 'Error creating backup', 'error');
                    }
                } catch (error) {
                    showNotification('Error creating backup. Please try again.', 'error');
                } finally {
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            });
        }

        // Add Admin (only for super admin)
        if (addAdminBtn) {
            <?php if ($is_super_admin): ?>
            addAdminBtn.addEventListener('click', function() {
                // This would open a modal to add a new admin
                // For now, redirect to a separate admin creation page or show alert
                if (confirm('This will open the admin creation form. Continue?')) {
                    // You can implement a modal here or redirect
                    alert('Admin creation form would open here. Implement as needed.');
                }
            });
            <?php else: ?>
            addAdminBtn.style.display = 'none';
            <?php endif; ?>
        }
        
        // Restore Defaults button
        const restoreDefaultsBtn = document.querySelector('.btn-danger');
        if (restoreDefaultsBtn && restoreDefaultsBtn.textContent.includes('Restore Defaults')) {
            restoreDefaultsBtn.addEventListener('click', async function() {
                if (!confirm('Are you sure you want to restore all settings to default values? This cannot be undone.')) {
                    return;
                }
                
                const originalText = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring...';
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'restore_defaults');
                    formData.append('csrf_token', csrfToken);
                    
                    const response = await fetch('api/backup_settings.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showNotification('Settings restored to defaults!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(result.message || 'Error restoring defaults', 'error');
                    }
                } catch (error) {
                    showNotification('Error restoring defaults. Please try again.', 'error');
                } finally {
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            });
        }
        
        // Backup download and delete handlers
        document.addEventListener('click', async function(e) {
            if (e.target.closest('.download-backup')) {
                const filename = e.target.closest('.download-backup').dataset.file;
                window.location.href = 'api/download_backup.php?file=' + encodeURIComponent(filename) + '&csrf_token=' + csrfToken;
            }
            
            if (e.target.closest('.delete-backup')) {
                const filename = e.target.closest('.delete-backup').dataset.file;
                if (!confirm('Are you sure you want to delete this backup?')) {
                    return;
                }
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'delete_backup');
                    formData.append('filename', filename);
                    formData.append('csrf_token', csrfToken);
                    
                    const response = await fetch('api/backup_settings.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showNotification('Backup deleted successfully!', 'success');
                        e.target.closest('.admin-item').remove();
                    } else {
                        showNotification(result.message || 'Error deleting backup', 'error');
                    }
                } catch (error) {
                    showNotification('Error deleting backup. Please try again.', 'error');
                }
            }
        });

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