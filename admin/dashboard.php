<?php
// dashboard.php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// Debug mode
define('DEBUG', true);

// Include RBAC functions
require_once 'includes/rbac.php';

// Get database connection
function getDBConnection()
{
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Get admin user data from admin_users table
function getAdminData($admin_id)
{
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, email, full_name, role, last_login, created_at FROM admin_users WHERE id = ?");
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

// Get current logged-in admin data
// Uses admin-auth.php's getCurrentAdmin() if available, otherwise falls back to local getAdminData()
function getCurrentAdminData()
{
    // Use admin-auth.php function if available (preferred - uses admin_users table)
    if (function_exists('getCurrentAdmin')) {
        return getCurrentAdmin();
    }
    
    // Fallback to local implementation
    if (!isset($_SESSION['admin_id'])) {
        return null;
    }
    return getAdminData($_SESSION['admin_id']);
}

// Check if admin has permission for a module (using admin_permissions table)
// This function uses admin-auth.php's hasAdminPermission if available
function checkAdminPermission($module, $permission = 'view')
{
    // Use admin-auth.php function if available (preferred)
    if (function_exists('hasAdminPermission')) {
        return hasAdminPermission($module, $permission);
    }
    
    // Fallback to local implementation if admin-auth.php not loaded
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
        return false;
    }
    
    $role = $_SESSION['admin_role'];
    
    // Super Admin has all permissions
    if ($role === 'super_admin' || $role === 'Super Admin') {
        return true;
    }
    
    $conn = getDBConnection();
    
    // Check if admin_permissions table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'admin_permissions'");
    if ($table_check->num_rows === 0) {
        // Table doesn't exist yet - allow access for now (fail open)
        // This allows admins to access the system while they run the SQL fix
        error_log("admin_permissions table not found. Please run admin/fix_database.sql");
        return true; // Temporarily allow access
    }
    
    // Normalize role for permission checking
    $role_map = [
        'Super Admin' => 'Super Admin',
        'super_admin' => 'Super Admin',
        'Manager' => 'Manager',
        'manager' => 'Manager',
        'Content Manager' => 'Content Manager',
        'content_editor' => 'Content Manager',
        'Support' => 'Support',
        'support' => 'Support'
    ];
    $db_role = isset($role_map[$role]) ? $role_map[$role] : $role;
    
    // Check for 'all' permission first
    $stmt = $conn->prepare("SELECT id FROM admin_permissions WHERE role = ? AND module = ? AND permission = 'all'");
    if ($stmt) {
        $stmt->bind_param("ss", $db_role, $module);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            return true;
        }
    }
    
    // Check for specific permission
    $stmt = $conn->prepare("SELECT id FROM admin_permissions WHERE role = ? AND module = ? AND permission = ?");
    if ($stmt) {
        $stmt->bind_param("sss", $db_role, $module, $permission);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->num_rows > 0;
    }
    
    return false;
}

// Get login history for a user
function getLoginHistory($admin_id, $limit = 10)
{
    $conn = getDBConnection();

    // Check if login_activity table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'login_activity'");
    if ($table_check->num_rows === 0) {
        return [];
    }

    $stmt = $conn->prepare("SELECT * FROM login_activity WHERE admin_id = ? ORDER BY login_time DESC LIMIT ?");
    if ($stmt) {
        $stmt->bind_param("ii", $admin_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }

        return $history;
    }

    return [];
}

// CORRECTED: Get dashboard statistics - Fixed to use correct column names
function getDashboardStats($conn)
{
    $stats = [
        'today_orders' => 0,
        'total_revenue' => 0,
        'active_orders' => 0,
        'today_reservations' => 0
    ];

    // Check if tables exist and get actual data
    $tables = $conn->query("SHOW TABLES");
    $table_list = [];
    while ($table = $tables->fetch_array()) {
        $table_list[] = $table[0];
    }

    // Today's orders - Use created_at column instead of order_date
    if (in_array('orders', $table_list)) {
        $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['today_orders'] = $row['count'];
        }
    } else {
        $stats['today_orders'] = rand(120, 180);
    }

    // Total revenue - Use order_status and total_amount columns
    if (in_array('orders', $table_list)) {
        $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE order_status = 'completed'");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_revenue'] = $row['total'] ? $row['total'] : 0;
        }
    } else {
        $stats['total_revenue'] = rand(280000, 350000);
    }

    // Active Orders - Count orders with status 'pending' or 'processing'
    if (in_array('orders', $table_list)) {
        $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status IN ('pending', 'processing')");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['active_orders'] = $row['count'];
        }
    } else {
        $stats['active_orders'] = rand(15, 25);
    }

    // Today's reservations - Use reservation_date column
    if (in_array('reservations', $table_list)) {
        $result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE DATE(reservation_date) = CURDATE()");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['today_reservations'] = $row['count'];
        }
    } else {
        $stats['today_reservations'] = rand(30, 45);
    }

    return $stats;
}

// Create tables if they don't exist
function createTablesIfNotExist($conn) {
    // Check if orders table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($table_check->num_rows === 0) {
        // Create orders table
        $sql = "CREATE TABLE orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id VARCHAR(20) UNIQUE NOT NULL,
            customer_name VARCHAR(100) NOT NULL,
            customer_email VARCHAR(100) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_state VARCHAR(50),
            delivery_address TEXT NOT NULL,
            delivery_instructions TEXT,
            subtotal DECIMAL(10, 2) NOT NULL,
            delivery_fee DECIMAL(10, 2) DEFAULT 1500.00,
            total_amount DECIMAL(10, 2) NOT NULL,
            payment_method ENUM('cod', 'bank', 'paystack', 'flutterwave') NOT NULL,
            payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
            order_status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
            payment_proof TEXT,
            payment_reference VARCHAR(100),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($sql)) {
            error_log("Error creating orders table: " . $conn->error);
        }
    }
    
    // Check if reservations table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'reservations'");
    if ($table_check->num_rows === 0) {
        // Create reservations table
        $sql = "CREATE TABLE reservations (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            guests INT NOT NULL,
            reservation_date DATE NOT NULL,
            reservation_time TIME NOT NULL,
            special_requests TEXT,
            status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($sql)) {
            error_log("Error creating reservations table: " . $conn->error);
        }
    }
    
    // Check if customers table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'customers'");
    if ($table_check->num_rows === 0) {
        // Create customers table
        $sql = "CREATE TABLE customers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20),
            total_orders INT DEFAULT 0,
            total_spent DECIMAL(10, 2) DEFAULT 0,
            last_order_date DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($sql)) {
            error_log("Error creating customers table: " . $conn->error);
        }
    }
}

// Central authentication and permission check
require_once 'admin-auth.php';
checkPageAccess(); // This enforces authentication and permission for dashboard

// Create tables if they don't exist
$conn = getDBConnection();
createTablesIfNotExist($conn);

// Get current admin data for display
$admin_data = getCurrentAdminData();
$username = 'Admin';
$user_initials = 'AJ';
$admin_role = 'Admin';
$admin_email = '';
$admin_full_name = '';
$last_login = '';

if ($admin_data) {
    $username = isset($admin_data['username']) ? $admin_data['username'] : 'Admin';
    $admin_full_name = isset($admin_data['full_name']) ? $admin_data['full_name'] : $username;
    $admin_email = isset($admin_data['email']) ? $admin_data['email'] : '';
    $admin_role = isset($admin_data['role']) ? ucwords(str_replace('_', ' ', $admin_data['role'])) : 'Admin';
    $last_login = isset($admin_data['last_login']) && $admin_data['last_login'] ? date('M j, Y g:i A', strtotime($admin_data['last_login'])) : 'Never';
    
    // Generate initials from full_name or username
    if (!empty($admin_data['full_name'])) {
        $nameParts = explode(' ', $admin_data['full_name']);
        $user_initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : substr($nameParts[0], 1, 1)));
    } else {
        $user_initials = strtoupper(substr($username, 0, 2));
    }
}

// Get dashboard statistics
$dashboard_stats = getDashboardStats($conn);

// Get login history
$login_history = getLoginHistory($_SESSION['admin_id'], 5);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo3.png">
    <title>Admin Dashboard - Joseph's Pot</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* Your existing CSS styles remain exactly the same */
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

        /* User Menu Dropdown Styles */
        .user-menu-dropdown {
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
            overflow: hidden;
        }

        .user-menu-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .user-menu-header {
            padding: 15px;
            background: var(--gray);
            border-bottom: 1px solid var(--gray-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-menu-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 1rem;
        }

        .user-menu-info h4 {
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .user-menu-info p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .user-menu-items {
            list-style: none;
        }

        .user-menu-item {
            padding: 12px 15px;
            border-bottom: 1px solid var(--gray);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-menu-item:hover {
            background: var(--gray);
        }

        .user-menu-item i {
            font-size: 1rem;
            color: var(--text-light);
            width: 20px;
            text-align: center;
        }

        .user-menu-item span {
            font-size: 0.9rem;
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

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }

        .stat-card.orders::before {
            background: var(--info);
        }

        .stat-card.revenue::before {
            background: var(--success);
        }

        .stat-card.active-orders::before { /* Changed from customers to active-orders */
            background: var(--warning);
        }

        .stat-card.reservations::before {
            background: var(--danger);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stat-card.orders i {
            color: var(--info);
        }

        .stat-card.revenue i {
            color: var(--success);
        }

        .stat-card.active-orders i { /* Changed from customers to active-orders */
            color: var(--warning);
        }

        .stat-card.reservations i {
            color: var(--danger);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .stat-change {
            font-size: 0.8rem;
            margin-top: 10px;
            display: flex;
            align-items: center;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .chart-header h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .chart-actions select {
            padding: 8px 12px;
            border: 1px solid var(--gray-dark);
            border-radius: 6px;
            background: white;
            color: var(--text);
            width: 100%;
            max-width: 200px;
        }

        .chart-container {
            height: 300px;
            position: relative;
            overflow: hidden;
        }

        /* Activity Section */
        .activity-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .activity-card,
        .top-items-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .activity-card h3,
        .top-items-card h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray);
            flex-wrap: wrap;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            flex-shrink: 0;
        }

        .activity-icon.order {
            background: var(--info);
        }

        .activity-icon.reservation {
            background: var(--danger);
        }

        .activity-icon.review {
            background: var(--warning);
        }

        .activity-icon.payment {
            background: var(--success);
        }

        .activity-details {
            flex: 1;
            min-width: 200px;
            margin-bottom: 10px;
        }

        .activity-details h4 {
            font-size: 0.95rem;
            margin-bottom: 5px;
        }

        .activity-details p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-left: auto;
        }

        /* Top Items */
        .top-items-list {
            list-style: none;
        }

        .top-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray);
            flex-wrap: wrap;
        }

        .top-item:last-child {
            border-bottom: none;
        }

        .item-rank {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .item-rank.rank-1 {
            background: gold;
            color: var(--dark);
        }

        .item-rank.rank-2 {
            background: silver;
            color: var(--dark);
        }

        .item-rank.rank-3 {
            background: #cd7f32;
            color: white;
        }

        .item-details {
            flex: 1;
            min-width: 150px;
            margin-bottom: 10px;
        }

        .item-details h4 {
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .item-details p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .item-sales {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--primary);
            margin-left: auto;
        }

        /* Admin Management Section */
        .admin-management {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-top: 30px;
        }

        .admin-management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .admin-management-header h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .add-admin-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .add-admin-btn:hover {
            background: var(--primary-dark);
        }

        .admins-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .admin-card {
            background: var(--gray);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: var(--transition);
            position: relative;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .admin-card-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }

        .admin-card-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .admin-card-role {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .admin-card-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            position: relative;
            z-index: 10;
            pointer-events: auto;
        }

        .admin-card-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            z-index: 10;
            pointer-events: auto;
        }

        .admin-card-btn.edit {
            background: var(--info);
            color: white;
        }

        .admin-card-btn.delete {
            background: var(--danger);
            color: white;
        }

        .admin-card-btn:hover {
            transform: scale(1.1);
        }

        /* Login History Section */
        .login-history {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-top: 30px;
            overflow-x: auto;
        }

        .login-history h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .login-history-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .login-history-table th,
        .login-history-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray);
        }

        .login-history-table th {
            background: var(--gray);
            font-weight: 600;
        }

        .login-history-table tr:hover {
            background: var(--gray);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-success {
            background: #e8f5e8;
            color: var(--success);
        }

        .status-failed {
            background: #ffeaea;
            color: var(--danger);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--gray-dark);
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
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

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
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

        .reveal-delay-1 {
            transition-delay: 0.1s;
        }

        .reveal-delay-2 {
            transition-delay: 0.2s;
        }

        .reveal-delay-3 {
            transition-delay: 0.3s;
        }

        .reveal-delay-4 {
            transition-delay: 0.4s;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .activity-section {
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
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .notification-dropdown {
                position: fixed;
                top: 70px;
                right: 15px;
                left: 15px;
                width: auto;
                max-height: 60vh;
            }
            
            .user-menu-dropdown {
                position: fixed;
                top: 70px;
                right: 15px;
                left: 15px;
                width: auto;
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
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .real-time-clock {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .clock-container {
                margin-bottom: 15px;
            }
            
            .activity-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .activity-details {
                margin-bottom: 10px;
            }
            
            .activity-time {
                margin-left: 0;
            }
            
            .top-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .item-details {
                margin-bottom: 10px;
            }
            
            .item-sales {
                margin-left: 0;
            }
            
            .admins-grid {
                grid-template-columns: repeat(2, 1fr);
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
            
            .chart-container {
                height: 250px;
            }
            
            .admin-management-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .add-admin-btn {
                width: 100%;
                justify-content: center;
            }
            
            .admins-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                padding: 20px 15px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .notification-dropdown {
                width: calc(100% - 30px);
                left: 15px;
                right: 15px;
            }
            
            .user-menu-dropdown {
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
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .chart-header h3,
            .activity-card h3,
            .top-items-card h3,
            .login-history h3,
            .admin-management-header h3 {
                font-size: 1.1rem;
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
                    <h3><?php echo htmlspecialchars($admin_full_name ? $admin_full_name : $username); ?></h3>
                    <p><?php echo htmlspecialchars($admin_role); ?></p>
                    <?php if ($last_login && $last_login !== 'Never'): ?>
                        <small style="font-size: 0.75rem; opacity: 0.8;">Last login: <?php echo $last_login; ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <ul class="menu-items">
                <li class="menu-label">Main</li>
                <?php if (checkAdminPermission('dashboard', 'view')): ?>
                <li class="menu-item">
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (checkAdminPermission('contact_messages', 'view')): ?>
                <li class="menu-item">
                    <a href="admin-contact-messages.php">
                        <i class="fas fa-envelope"></i>
                        <span>Contact Messages</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (checkAdminPermission('menu_management', 'view')): ?>
                <li class="menu-item">
                    <a href="admin-menu-management.php">
                        <i class="fas fa-utensils"></i>
                        <span>Menu Management</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (checkAdminPermission('reservations', 'view')): ?>
                <li class="menu-item">
                    <a href="admin-reservation.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Reservations</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (checkAdminPermission('orders', 'view')): ?>
                <li class="menu-item">
                    <a href="admin-orders.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (checkAdminPermission('order_online_menu', 'view')): ?>
                <li class="menu-item">
                    <a href="admin-order-online-menu.php">
                        <i class="fas fa-car"></i>
                        <span>Order-Online Menu</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="menu-label">Content</li>
                <?php if (checkAdminPermission('reviews', 'view')): ?>
                <li class="menu-item">
                    <a href="admin-reviews.php">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (checkAdminPermission('events', 'view')): ?>
                <li class="menu-item">
                    <a href="admin-events.php">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Events</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (checkAdminPermission('gallery', 'view')): ?>
                <li class="menu-item">
                    <a href="admin-gallery.php">
                        <i class="fas fa-image"></i>
                        <span>Gallery</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="menu-label">Account</li>
                <?php if (checkAdminPermission('admin_management', 'view')): ?>
                <li class="menu-item">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('addAdminBtn').click();">
                        <i class="fas fa-user-plus"></i>
                        <span>Admin Management</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (checkAdminPermission('settings', 'view')): ?>
                <li class="menu-item">
                    <a href="admin-settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="menu-item">
                    <a href="./admin-logout.php" onclick="return confirmLogout()">
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
                <h2>Dashboard Overview</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search...">
                    </div>
                    <div class="notification-user-container">
                        <div class="notification-icon" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">5</span>
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
                        <div class="user-menu" id="userMenuBtn">
                            <i class="fas fa-user-circle"></i>
                            <!-- User Menu Dropdown -->
                            <div class="user-menu-dropdown" id="userMenuDropdown">
                                <div class="user-menu-header">
                                    <div class="user-menu-avatar"><?php echo $user_initials; ?></div>
                                    <div class="user-menu-info">
                                        <h4><?php echo htmlspecialchars($admin_full_name ? $admin_full_name : $username); ?></h4>
                                        <p><?php echo htmlspecialchars($admin_role); ?></p>
                                        <?php if ($admin_email): ?>
                                            <small style="font-size: 0.75rem; opacity: 0.7;"><?php echo htmlspecialchars($admin_email); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <ul class="user-menu-items">
                                    <li class="user-menu-item" onclick="openEditProfileModal()">
                                        <i class="fas fa-user-edit"></i>
                                        <span>Edit My Profile</span>
                                    </li>
                                    <li class="user-menu-item" onclick="window.location.href='admin-settings.php'">
                                        <i class="fas fa-cog"></i>
                                        <span>Account Settings</span>
                                    </li>
                                    <li class="user-menu-item" onclick="window.location.href='admin-logout.php'">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card orders reveal">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="stat-value"><?php echo $dashboard_stats['today_orders']; ?></div>
                    <div class="stat-label">Today's Orders</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 12% from yesterday
                    </div>
                </div>

                <div class="stat-card revenue reveal reveal-delay-1">
                    <i class="fa-solid fa-naira-sign"></i>
                    <div class="stat-value">₦<?php echo number_format($dashboard_stats['total_revenue']); ?></div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 8% from last week
                    </div>
                </div>

                <!-- Changed from "Total Customers" to "Active Orders" -->
                <div class="stat-card active-orders reveal reveal-delay-2">
                    <i class="fas fa-clock"></i>
                    <div class="stat-value"><?php echo $dashboard_stats['active_orders']; ?></div>
                    <div class="stat-label">Active Orders</div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i> 2 from yesterday
                    </div>
                </div>

                <div class="stat-card reservations reveal reveal-delay-3">
                    <i class="fas fa-calendar-check"></i>
                    <div class="stat-value"><?php echo $dashboard_stats['today_reservations']; ?></div>
                    <div class="stat-label">Today's Reservations</div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i> 3% from yesterday
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card reveal">
                    <div class="chart-header">
                        <h3>Revenue Overview</h3>
                        <div class="chart-actions">
                            <select id="revenuePeriodSelect">
                                <option value="7">Last 7 Days</option>
                                <option value="30">Last 30 Days</option>
                                <option value="90">Last 3 Months</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <div class="chart-card reveal reveal-delay-1">
                    <div class="chart-header">
                        <h3>Order Status</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="orderStatusChart"></canvas>
                        <div id="orderStatusLegend" style="display: flex; justify-content: center; gap: 20px; margin-top: 20px; flex-wrap: wrap;"></div>
                    </div>
                </div>
            </div>

            <!-- Activity Section -->
            <div class="activity-section">
                <div class="activity-card reveal">
                    <h3>Recent Activity</h3>
                    <ul class="activity-list" id="activityList">
                        <!-- Activity items will be loaded here -->
                    </ul>
                </div>

                <div class="top-items-card reveal reveal-delay-1">
                    <h3>Top Menu Items</h3>
                    <ul class="top-items-list" id="topItemsList">
                        <!-- Top items will be loaded here -->
                    </ul>
                </div>
            </div>
            <!-- Login History Section -->
            <div class="login-history reveal">
                <h3>Recent Login Activity</h3>
                <table class="login-history-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Device</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($login_history)): ?>
                            <?php foreach ($login_history as $login): ?>
                                <tr>
                                    <td><?php echo date('M j, Y g:i A', strtotime($login['login_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($login['ip_address']); ?></td>
                                    <td><?php echo htmlspecialchars($login['city'] . ', ' . $login['country']); ?></td>
                                    <td><?php echo htmlspecialchars($login['device_type'] . ' - ' . $login['browser']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $login['status'] === 'success' ? 'status-success' : 'status-failed'; ?>">
                                            <?php echo ucfirst($login['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No login history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Admin Management Section -->
            <?php if (checkAdminPermission('admin_management', 'view')): ?>
            <div class="admin-management reveal">
                <div class="admin-management-header">
                    <h3>Admin Management</h3>
                    <?php if (checkAdminPermission('admin_management', 'create')): ?>
                    <button class="add-admin-btn" id="addAdminBtn">
                        <i class="fas fa-plus"></i>
                        Add New Admin
                    </button>
                    <?php endif; ?>
                </div>
                <div class="admins-grid" id="adminsGrid">
                    <!-- Admin cards will be dynamically added here -->
                </div>
            </div>
            <?php endif; ?>

            <?php include "footer.php"; ?>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal" id="addAdminModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Admin</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <form id="adminForm">
                <div class="form-group">
                    <label for="adminName">Full Name</label>
                    <input type="text" id="adminName" required>
                </div>
                <div class="form-group">
                    <label for="adminEmail">Email Address</label>
                    <input type="email" id="adminEmail" required>
                </div>
                <div class="form-group">
                    <label for="adminPassword">Password</label>
                    <input type="password" id="adminPassword" placeholder="Enter password" autocomplete="new-password" required>
                    <small class="form-hint" id="passwordHint" style="display: none; color: #666; font-size: 0.85em; margin-top: 5px;">Leave blank to keep existing password</small>
                </div>
                <div class="form-group">
                    <label for="adminRole">Role</label>
                    <select id="adminRole" required>
                        <option value="">Select Role</option>
                        <option value="Super Admin">Super Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Content Manager">Content Manager</option>
                        <option value="Support">Support</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="adminPermissions">Permissions</label>
                    <select id="adminPermissions">
                        <option value="">Select Permissions</option>
                        <option value="Full Access">Full Access</option>
                        <option value="Limited Access">Limited Access</option>
                        <option value="View Only">View Only</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Admin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit My Profile</h3>
                <button class="close-modal" id="closeEditProfileModal">&times;</button>
            </div>
            <form id="editProfileForm">
                <div class="form-group">
                    <label for="profileUsername">Username</label>
                    <input type="text" id="profileUsername" value="<?php echo htmlspecialchars(isset($admin_data['username']) ? $admin_data['username'] : ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="profileEmail">Email Address</label>
                    <input type="email" id="profileEmail" value="<?php echo htmlspecialchars(isset($admin_data['email']) ? $admin_data['email'] : ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="currentPassword">Current Password <span style="color: red;">*</span></label>
                    <input type="password" id="currentPassword" placeholder="Enter current password to verify changes" required>
                    <small class="form-hint" style="color: #666; font-size: 0.85em; margin-top: 5px;">Required to confirm any changes</small>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" placeholder="Leave blank to keep current password" autocomplete="new-password">
                    <small class="form-hint" style="color: #666; font-size: 0.85em; margin-top: 5px;">Leave blank if you don't want to change password</small>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" placeholder="Confirm new password" autocomplete="new-password">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelEditProfileBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
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

    // Admin data (loaded from database)
    // Make it accessible globally for event delegation closure
    let admins = [];
    window.admins = admins; // Initialize global reference

    // Sample notification data
    const notifications = [
        {
            id: 1,
            title: 'New Order',
            message: 'Order #JP-2848 has been placed',
            time: '2 minutes ago',
            unread: true
        },
        {
            id: 2,
            title: 'Reservation Confirmed',
            message: 'Table reservation for 4 people confirmed',
            time: '15 minutes ago',
            unread: true
        },
        {
            id: 3,
            title: 'Payment Received',
            message: '₦8,500 payment confirmed for Order #JP-2845',
            time: '1 hour ago',
            unread: false
        },
        {
            id: 4,
            title: 'New Review',
            message: 'Customer left a 5-star review',
            time: '3 hours ago',
            unread: false
        },
        {
            id: 5,
            title: 'System Update',
            message: 'Dashboard has been updated to version 2.1',
            time: '1 day ago',
            unread: false
        }
    ];

    // DOM Elements
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const addAdminBtn = document.getElementById('addAdminBtn');
    const addAdminModal = document.getElementById('addAdminModal');
    const closeModal = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const adminForm = document.getElementById('adminForm');
    const adminsGrid = document.getElementById('adminsGrid');
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllRead');
    const notificationBadge = document.querySelector('.notification-badge');
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenuDropdown = document.getElementById('userMenuDropdown');
    const editProfileModal = document.getElementById('editProfileModal');
    const editProfileForm = document.getElementById('editProfileForm');
    const closeEditProfileModal = document.getElementById('closeEditProfileModal');
    const cancelEditProfileBtn = document.getElementById('cancelEditProfileBtn');

    // ==================== EDIT PROFILE FUNCTIONS ====================
    
    // Open Edit Profile Modal
    function openEditProfileModal() {
        if (editProfileModal) {
            editProfileModal.style.display = 'flex';
            // Close user menu dropdown
            if (userMenuDropdown) {
                userMenuDropdown.classList.remove('active');
            }
        }
    }
    
    // Close Edit Profile Modal
    function closeEditProfileModalFunc() {
        if (editProfileModal) {
            editProfileModal.style.display = 'none';
            editProfileForm.reset();
        }
    }

    // ==================== ADMIN MANAGEMENT FUNCTIONS ====================
    // Note: Functions are defined after DOM elements to ensure they're available when referenced
    
    // Helper function to reset password field for add mode
    function resetPasswordFieldForAddMode() {
        const passwordHint = document.getElementById('passwordHint');
        const passwordField = document.getElementById('adminPassword');
        if (passwordHint) passwordHint.style.display = 'none';
        if (passwordField) {
            passwordField.placeholder = 'Enter password';
            passwordField.setAttribute('required', 'required');
        }
    }

    // Admin delete function
    function deleteAdmin(adminId, showSuccessMessage = true) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', adminId);
        
        fetch('api/manage-admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload admins from database
                loadAdmins();
                
                // Show success message if requested
                if (showSuccessMessage) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Admin has been deleted successfully.',
                            confirmButtonColor: '#8b4513',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('Admin deleted successfully!');
                    }
                }
            } else {
                // Show error message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete admin',
                        confirmButtonColor: '#8b4513'
                    });
                } else {
                    alert(data.message || 'Failed to delete admin');
                }
            }
        })
        .catch(error => {
            console.error('Error deleting admin:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete admin. Please try again.',
                    confirmButtonColor: '#8b4513'
                });
            } else {
                alert('Failed to delete admin. Please try again.');
            }
        });
    }

    // Admin edit function - opens modal in edit mode
    function editAdmin(adminId, adminData) {
        console.log('[DEBUG] editAdmin() called with adminId:', adminId, 'adminData:', adminData);
        console.log('[DEBUG] adminForm:', adminForm);
        console.log('[DEBUG] addAdminModal:', addAdminModal);
        
        if (!adminForm || !addAdminModal) {
            console.error('[DEBUG] Admin form or modal not found');
            console.error('[DEBUG] adminForm exists:', !!adminForm);
            console.error('[DEBUG] addAdminModal exists:', !!addAdminModal);
            alert('Error: Form elements not found. Please refresh the page.');
            return;
        }
        
        console.log('[DEBUG] Populating form fields');
        // Populate form with admin data
        const nameField = document.getElementById('adminName');
        const emailField = document.getElementById('adminEmail');
        const roleField = document.getElementById('adminRole');
        const passwordField = document.getElementById('adminPassword');
        
        console.log('[DEBUG] Form fields found:', {
            nameField: !!nameField,
            emailField: !!emailField,
            roleField: !!roleField,
            passwordField: !!passwordField
        });
        
        if (nameField) nameField.value = adminData.name || '';
        if (emailField) emailField.value = adminData.email || '';
        if (roleField) roleField.value = adminData.role || '';
        // Clear password field - don't show existing password for security
        if (passwordField) passwordField.value = '';
        // Note: permissions field is not stored in database, so we don't populate it
        
        // Change modal title and submit button text
        const modalHeader = document.querySelector('#addAdminModal .modal-header h3');
        const submitBtn = document.querySelector('#adminForm button[type="submit"]');
        
        console.log('[DEBUG] Modal elements:', {
            modalHeader: !!modalHeader,
            submitBtn: !!submitBtn
        });
        
        if (modalHeader) modalHeader.textContent = 'Edit Admin';
        if (submitBtn) submitBtn.textContent = 'Update Admin';
        
        // Show password hint for edit mode
        const passwordHint = document.getElementById('passwordHint');
        if (passwordHint) passwordHint.style.display = 'block';
        if (passwordField) {
            passwordField.placeholder = 'Leave blank to keep existing password';
            passwordField.removeAttribute('required');
        }
        
        // Store edit mode info
        adminForm.dataset.editMode = 'true';
        adminForm.dataset.editId = adminId;
        
        console.log('[DEBUG] Showing modal');
        // Show modal
        addAdminModal.style.display = 'flex';
        console.log('[DEBUG] Modal display set to flex, computed display:', window.getComputedStyle(addAdminModal).display);
    }
    
    // SweetAlert confirmation for admin delete
    function confirmAdminDelete(adminId, adminData) {
        console.log('[DEBUG] confirmAdminDelete() called with adminId:', adminId, 'adminData:', adminData);
        console.log('[DEBUG] Swal available:', typeof Swal !== 'undefined');
        
        if (typeof Swal === 'undefined') {
            console.log('[DEBUG] Using browser confirm dialog');
            // Fallback to browser confirm
            if (confirm('Are you sure you want to delete ' + (adminData ? adminData.name : 'this admin') + '? This action cannot be undone.')) {
                console.log('[DEBUG] User confirmed deletion');
                deleteAdmin(adminId, true);
            } else {
                console.log('[DEBUG] User cancelled deletion');
            }
            return;
        }

        console.log('[DEBUG] Using SweetAlert for confirmation');
        Swal.fire({
            icon: 'warning',
            title: 'Delete Admin?',
            html: `Are you sure you want to delete <strong>${adminData.name}</strong>?<br><br>This action is permanent and cannot be undone.`,
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#F44336',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            console.log('[DEBUG] SweetAlert result:', result);
            if (result.isConfirmed) {
                console.log('[DEBUG] User confirmed deletion via SweetAlert');
                deleteAdmin(adminId, true);
            } else {
                console.log('[DEBUG] User cancelled deletion via SweetAlert');
            }
        });
    }

    // Render admins in the grid
    function renderAdmins() {
        console.log('[DEBUG] renderAdmins() called');
        console.log('[DEBUG] adminsGrid:', adminsGrid);
        console.log('[DEBUG] admins array:', admins);
        
        if (!adminsGrid) {
            console.error('[DEBUG] adminsGrid is null!');
            return;
        }
        
        adminsGrid.innerHTML = '';

        if (!admins || admins.length === 0) {
            console.log('[DEBUG] No admins to render');
            adminsGrid.innerHTML = '<p>No admins found. Click "Add New Admin" to create one.</p>';
            return;
        }

        console.log(`[DEBUG] Rendering ${admins.length} admin cards`);

        admins.forEach((admin, index) => {
            const adminCard = document.createElement('div');
            adminCard.className = 'admin-card';
            
            // Generate avatar initials
            const nameParts = admin.name.split(' ');
            const initials = nameParts.length >= 2 
                ? (nameParts[0].charAt(0) + nameParts[nameParts.length - 1].charAt(0)).toUpperCase()
                : admin.name.substring(0, 2).toUpperCase();
            
            adminCard.innerHTML = `
                <div class="admin-card-avatar">${initials}</div>
                <div class="admin-card-name">${admin.name}</div>
                <div class="admin-card-role">${admin.role}</div>
                <div class="admin-card-actions">
                    <button type="button" class="admin-card-btn edit" title="Edit Admin" data-admin-id="${admin.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="admin-card-btn delete" title="Delete Admin" data-admin-id="${admin.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

            adminsGrid.appendChild(adminCard);
            console.log(`[DEBUG] Admin card ${index + 1} appended for admin ID: ${admin.id}, Name: ${admin.name}`);
        });
        
        console.log('[DEBUG] All admin cards rendered, setting up event delegation');
    }

    // Load admins from database (defined after renderAdmins since it calls renderAdmins)
    function loadAdmins() {
        console.log('[DEBUG] loadAdmins() called');
        fetch('api/manage-admin.php?action=list')
            .then(response => response.json())
            .then(data => {
                console.log('[DEBUG] API response:', data);
                if (data.success && data.admins) {
                    admins = data.admins;
                    window.admins = admins; // Update global reference
                    console.log('[DEBUG] Loaded admins:', admins);
                    console.log('[DEBUG] Updated window.admins:', window.admins);
                    renderAdmins();
                } else {
                    console.error('[DEBUG] Error loading admins:', data.message);
                    // Render empty state
                    if (adminsGrid) {
                        adminsGrid.innerHTML = '<p>No admins found or error loading admins.</p>';
                    }
                }
            })
            .catch(error => {
                console.error('[DEBUG] Fetch error loading admins:', error);
                if (adminsGrid) {
                    adminsGrid.innerHTML = '<p>Error loading admins. Please refresh the page.</p>';
                }
            });
    }

    // Set up event delegation for dynamically created admin card buttons
    // This function should only be called once to avoid duplicate listeners
    let eventDelegationSetup = false;
    function setupAdminCardEventDelegation() {
        console.log('[DEBUG] setupAdminCardEventDelegation() called');
        
        if (eventDelegationSetup) {
            console.log('[DEBUG] Event delegation already set up, skipping');
            return;
        }
        
        const adminsGridElement = document.getElementById('adminsGrid');
        console.log('[DEBUG] adminsGrid element:', adminsGridElement);
        
        if (!adminsGridElement) {
            console.error('[DEBUG] adminsGrid element not found');
            return;
        }

        // Use event delegation on the grid container
        adminsGridElement.addEventListener('click', function(e) {
            console.log('[DEBUG] Click event detected on adminsGrid, target:', e.target);
            console.log('[DEBUG] Current admins array:', admins);
            console.log('[DEBUG] Current admins array length:', admins ? admins.length : 'null');
            
            // Check if click is on edit button or its icon
            const editBtn = e.target.closest('.edit');
            if (editBtn) {
                console.log('[DEBUG] Edit button clicked!');
                e.preventDefault();
                e.stopPropagation();
                const adminId = parseInt(editBtn.getAttribute('data-admin-id'));
                console.log('[DEBUG] Edit - adminId:', adminId, 'type:', typeof adminId);
                console.log('[DEBUG] Searching in admins array:', admins);
                
                // Get fresh admins array from the global scope
                const currentAdmins = window.admins || admins;
                console.log('[DEBUG] Using admins array:', currentAdmins);
                
                const adminData = currentAdmins.find(a => {
                    console.log('[DEBUG] Comparing admin.id:', a.id, 'type:', typeof a.id, 'with adminId:', adminId, 'type:', typeof adminId);
                    return a.id === adminId || a.id == adminId || parseInt(a.id) === adminId;
                });
                console.log('[DEBUG] Edit - adminData found:', adminData);
                
                if (adminData) {
                    console.log('[DEBUG] Calling editAdmin function');
                    editAdmin(adminId, adminData);
                } else {
                    console.error('[DEBUG] Admin data not found for ID:', adminId);
                    console.error('[DEBUG] Available admin IDs:', currentAdmins.map(a => ({ id: a.id, type: typeof a.id })));
                }
                return;
            }

            // Check if click is on delete button or its icon
            const deleteBtn = e.target.closest('.delete');
            if (deleteBtn) {
                console.log('[DEBUG] Delete button clicked!');
                e.preventDefault();
                e.stopPropagation();
                const adminId = parseInt(deleteBtn.getAttribute('data-admin-id'));
                console.log('[DEBUG] Delete - adminId:', adminId, 'type:', typeof adminId);
                
                // Get fresh admins array from the global scope
                const currentAdmins = window.admins || admins;
                console.log('[DEBUG] Using admins array for delete:', currentAdmins);
                
                const adminData = currentAdmins.find(a => {
                    console.log('[DEBUG] Comparing admin.id:', a.id, 'type:', typeof a.id, 'with adminId:', adminId, 'type:', typeof adminId);
                    return a.id === adminId || a.id == adminId || parseInt(a.id) === adminId;
                });
                console.log('[DEBUG] Delete - adminData found:', adminData);
                
                if (adminData) {
                    console.log('[DEBUG] Calling confirmAdminDelete function');
                    confirmAdminDelete(adminId, adminData);
                } else {
                    console.error('[DEBUG] Admin data not found for ID:', adminId);
                    console.error('[DEBUG] Available admin IDs:', currentAdmins.map(a => ({ id: a.id, type: typeof a.id })));
                }
                return;
            }
        });
        
        eventDelegationSetup = true;
        console.log('[DEBUG] Event delegation set up successfully');
    }

    // ==================== END ADMIN MANAGEMENT FUNCTIONS ====================

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

    // Modal functionality
    addAdminBtn.addEventListener('click', function() {
        // Reset form and password field for add mode
        adminForm.reset();
        resetPasswordFieldForAddMode();
        delete adminForm.dataset.editMode;
        delete adminForm.dataset.editId;
        
        // Reset modal title and button text
        const modalHeader = document.querySelector('#addAdminModal .modal-header h3');
        const submitBtn = document.querySelector('#adminForm button[type="submit"]');
        if (modalHeader) modalHeader.textContent = 'Add New Admin';
        if (submitBtn) submitBtn.textContent = 'Add Admin';
        
        addAdminModal.style.display = 'flex';
    });

    closeModal.addEventListener('click', function() {
        // Restore modal title and button text if in edit mode
        if (adminForm.dataset.editMode === 'true') {
            const modalHeader = document.querySelector('#addAdminModal .modal-header h3');
            const submitBtn = document.querySelector('#adminForm button[type="submit"]');
            if (modalHeader) modalHeader.textContent = 'Add New Admin';
            if (submitBtn) submitBtn.textContent = 'Add Admin';
            delete adminForm.dataset.editMode;
            delete adminForm.dataset.editId;
        }
        // Reset password field for add mode
        resetPasswordFieldForAddMode();
        addAdminModal.style.display = 'none';
    });

    cancelBtn.addEventListener('click', function() {
        // Restore modal title and button text if in edit mode
        if (adminForm.dataset.editMode === 'true') {
            const modalHeader = document.querySelector('#addAdminModal .modal-header h3');
            const submitBtn = document.querySelector('#adminForm button[type="submit"]');
            if (modalHeader) modalHeader.textContent = 'Add New Admin';
            if (submitBtn) submitBtn.textContent = 'Add Admin';
            delete adminForm.dataset.editMode;
            delete adminForm.dataset.editId;
        }
        // Reset password field for add mode
        resetPasswordFieldForAddMode();
        addAdminModal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === addAdminModal) {
            // Restore modal title and button text if in edit mode
            if (adminForm.dataset.editMode === 'true') {
                const modalHeader = document.querySelector('#addAdminModal .modal-header h3');
                const submitBtn = document.querySelector('#adminForm button[type="submit"]');
                if (modalHeader) modalHeader.textContent = 'Add New Admin';
                if (submitBtn) submitBtn.textContent = 'Add Admin';
                delete adminForm.dataset.editMode;
                delete adminForm.dataset.editId;
            }
            // Reset password field for add mode
            resetPasswordFieldForAddMode();
            addAdminModal.style.display = 'none';
        }
    });

    // Handle form submission
    adminForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const name = document.getElementById('adminName').value;
        const email = document.getElementById('adminEmail').value;
        const password = document.getElementById('adminPassword').value;
        const role = document.getElementById('adminRole').value;
        const permissions = document.getElementById('adminPermissions').value;

        // URGENT DEBUG: Log form values before submission
        console.log('===========================================');
        console.log('DEBUG dashboard.php adminForm submit:');
        console.log('  - name:', name);
        console.log('  - email:', email);
        console.log('  - role:', role, '(type: ' + typeof role + ')');
        console.log('  - password:', password ? 'SET (hidden)' : 'EMPTY');
        console.log('  - adminRole element:', document.getElementById('adminRole'));
        console.log('  - adminRole selectedIndex:', document.getElementById('adminRole').selectedIndex);
        console.log('  - adminRole selectedOption:', document.getElementById('adminRole').options[document.getElementById('adminRole').selectedIndex]);
        console.log('===========================================');

        // Check if in edit mode
        const isEditMode = this.dataset.editMode === 'true';
        const editId = this.dataset.editId ? parseInt(this.dataset.editId) : null;

        if (isEditMode && editId) {
            // Edit mode - confirm with SweetAlert
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: 'Save Changes?',
                    html: `Confirm saving changes to <strong>${name}</strong>?`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Save',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#8b4513',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Update admin via API
                        const formData = new FormData();
                        formData.append('action', 'update');
                        formData.append('id', editId);
                        formData.append('name', name);
                        formData.append('email', email);
                        formData.append('role', role);
                        // Only append password if it's provided (optional in edit mode)
                        if (password.trim() !== '') {
                            formData.append('password', password);
                        }
                        
                        fetch('api/manage-admin.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Close modal and reset form
                                addAdminModal.style.display = 'none';
                                adminForm.reset();
                                resetPasswordFieldForAddMode();
                                delete adminForm.dataset.editMode;
                                delete adminForm.dataset.editId;
                                
                                // Restore modal title and submit button text
                                const modalHeader = document.querySelector('#addAdminModal .modal-header h3');
                                const submitBtn = document.querySelector('#adminForm button[type="submit"]');
                                if (modalHeader) modalHeader.textContent = 'Add New Admin';
                                if (submitBtn) submitBtn.textContent = 'Add Admin';
                                
                                // Reload admins from database
                                loadAdmins();
                                
                                // Show success message
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: `Admin ${name} updated successfully!`,
                                    confirmButtonColor: '#8b4513',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                // Show error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message || 'Failed to update admin',
                                    confirmButtonColor: '#8b4513'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error updating admin:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update admin. Please try again.',
                                confirmButtonColor: '#8b4513'
                            });
                        });
                    }
                });
            } else {
                // Fallback to browser confirm
                if (confirm('Save changes to ' + name + '?')) {
                    const formData = new FormData();
                    formData.append('action', 'update');
                    formData.append('id', editId);
                    formData.append('name', name);
                    formData.append('email', email);
                    formData.append('role', role);
                    // Only append password if it's provided (optional in edit mode)
                    if (password.trim() !== '') {
                        formData.append('password', password);
                    }
                    
                    fetch('api/manage-admin.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            addAdminModal.style.display = 'none';
                            adminForm.reset();
                            resetPasswordFieldForAddMode();
                            delete adminForm.dataset.editMode;
                            delete adminForm.dataset.editId;
                            const modalHeader = document.querySelector('#addAdminModal .modal-header h3');
                            const submitBtn = document.querySelector('#adminForm button[type="submit"]');
                            if (modalHeader) modalHeader.textContent = 'Add New Admin';
                            if (submitBtn) submitBtn.textContent = 'Add Admin';
                            loadAdmins();
                            alert(`Admin ${name} updated successfully!`);
                        } else {
                            alert(data.message || 'Failed to update admin');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating admin:', error);
                        alert('Failed to update admin. Please try again.');
                    });
                }
            }
        } else {
            // Add mode - create new admin via API
            // Validate password is provided for new admins
            if (!password || password.trim() === '') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Required',
                        text: 'Please enter a password for the new admin.',
                        confirmButtonColor: '#8b4513'
                    });
                } else {
                    alert('Please enter a password for the new admin.');
                }
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('name', name);
            formData.append('email', email);
            formData.append('role', role);
            formData.append('password', password);
            
            // URGENT DEBUG: Log FormData contents
            console.log('DEBUG: FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}:`, value);
            }
            console.log('DEBUG: role value being sent:', role);
            
            fetch('api/manage-admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal and reset form
                    addAdminModal.style.display = 'none';
                    adminForm.reset();
                    resetPasswordFieldForAddMode();
                    
                    // Reload admins from database
                    loadAdmins();
                    
                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: `Admin ${name} added successfully!`,
                            confirmButtonColor: '#8b4513'
                        });
                    } else {
                        alert(`Admin ${name} added successfully!`);
                    }
                } else {
                    // Show error message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to create admin',
                            confirmButtonColor: '#8b4513'
                        });
                    } else {
                        alert(data.message || 'Failed to create admin');
                    }
                }
            })
            .catch(error => {
                console.error('Error creating admin:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to create admin. Please try again.',
                        confirmButtonColor: '#8b4513'
                    });
                } else {
                    alert('Failed to create admin. Please try again.');
                }
            });
        }
    });

    // User Menu functionality
    if (userMenuBtn) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('active');
            // Close notification dropdown if open
            notificationDropdown.classList.remove('active');
        });
    }

    // Notification functionality
    function renderNotifications(notificationsData) {
        if (!notificationList) return;
        
        notificationList.innerHTML = '';
        
        if (!notificationsData || notificationsData.length === 0) {
            notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
            return;
        }
        
        notificationsData.forEach(notification => {
            const notificationItem = document.createElement('li');
            notificationItem.className = `notification-item ${!notification.is_read ? 'unread' : ''}`;
            notificationItem.dataset.id = notification.id;
            
            const timeAgo = getTimeAgo(notification.created_at);
            
            notificationItem.innerHTML = `
                <div class="notification-dot" style="${!notification.is_read ? 'background: var(--primary)' : 'background: transparent'}"></div>
                <div class="notification-content">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">${timeAgo}</div>
                </div>
            `;
            
            if (!notification.is_read) {
                notificationItem.addEventListener('click', function() {
                    markAsRead(notification.id);
                });
            }
            
            notificationList.appendChild(notificationItem);
        });
    }

    function updateNotificationBadge(count) {
        if (notificationBadge) {
            notificationBadge.textContent = count || 0;
            notificationBadge.style.display = (count > 0) ? 'flex' : 'none';
        }
    }

    function loadNotifications() {
        fetch('api/get-notifications.php?limit=10')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderNotifications(data.data);
                    updateNotificationBadge(data.unread_count);
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
            });
    }

    function markAsRead(notificationId) {
        fetch('api/mark-notification-read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload notifications to update UI
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    function markAllAsRead() {
        fetch('api/mark-all-notifications-read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload notifications to update UI
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    }

    // Toggle notification dropdown
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('active');
            // Close user menu dropdown if open
            userMenuDropdown.classList.remove('active');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        // Close notification dropdown
        if (notificationIcon && !notificationIcon.contains(e.target) && notificationDropdown && !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('active');
        }
        
        // Close user menu dropdown
        if (userMenuBtn && !userMenuBtn.contains(e.target) && userMenuDropdown && !userMenuDropdown.contains(e.target)) {
            userMenuDropdown.classList.remove('active');
        }
        
        // Close edit profile modal
        if (editProfileModal && e.target === editProfileModal) {
            closeEditProfileModalFunc();
        }
    });
    
    // Edit Profile Modal Event Listeners
    if (closeEditProfileModal) {
        closeEditProfileModal.addEventListener('click', closeEditProfileModalFunc);
    }
    
    if (cancelEditProfileBtn) {
        cancelEditProfileBtn.addEventListener('click', closeEditProfileModalFunc);
    }
    
    // Handle Edit Profile Form Submission
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('profileUsername').value.trim();
            const email = document.getElementById('profileEmail').value.trim();
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validate new password if provided
            if (newPassword && newPassword !== confirmPassword) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Mismatch',
                        text: 'New password and confirm password do not match.',
                        confirmButtonColor: '#8b4513'
                    });
                } else {
                    alert('New password and confirm password do not match.');
                }
                return;
            }
            
            // Prepare form data
            const formData = new FormData();
            formData.append('username', username);
            formData.append('email', email);
            formData.append('current_password', currentPassword);
            if (newPassword) {
                formData.append('new_password', newPassword);
                formData.append('confirm_password', confirmPassword);
            }
            
            // Disable submit button
            const submitBtn = editProfileForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.textContent : 'Update Profile';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Updating...';
            }
            
            // Submit to API
            fetch('api/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
                
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Profile updated successfully.',
                            confirmButtonColor: '#8b4513',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload page to reflect changes
                            window.location.reload();
                        });
                    } else {
                        alert(data.message || 'Profile updated successfully.');
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to update profile.',
                            confirmButtonColor: '#8b4513'
                        });
                    } else {
                        alert(data.message || 'Failed to update profile.');
                    }
                }
            })
            .catch(error => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
                console.error('Error updating profile:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating your profile. Please try again.',
                        confirmButtonColor: '#8b4513'
                    });
                } else {
                    alert('An error occurred while updating your profile. Please try again.');
                }
            });
        });
    }

    // Mark all as read button
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            markAllAsRead();
        });
    }
    
    // Initial notification load
    loadNotifications();

    // Initialize WebSocket for real-time aggregated notifications
    let mainDashboardWS = null;
    try {
        const script = document.createElement('script');
        script.src = 'js/websocket-client.js?v=2.0';
        script.onload = function() {
            mainDashboardWS = initWebSocket('main_dashboard');
            if (mainDashboardWS) {
                // Listen for all notification types
                mainDashboardWS.on('new_order', function(data) {
                    loadNotifications();
                });
                mainDashboardWS.on('new_message', function(data) {
                    loadNotifications();
                });
                mainDashboardWS.on('new_reservation', function(data) {
                    loadNotifications();
                });
            }
        };
        document.head.appendChild(script);
    } catch (e) {
        console.error('WebSocket initialization error:', e);
    }

    // Poll for notifications every 30 seconds (fallback if WebSocket fails)
    setInterval(loadNotifications, 30000);

    // Scroll Reveal Functionality
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

    // Chart.js initialization
    let revenueChart = null;
    let orderStatusChart = null;

    function initializeCharts() {
        // Initialize revenue chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Revenue (₦)',
                        data: [],
                        backgroundColor: 'rgba(33, 150, 243, 0.6)',
                        borderColor: 'rgba(33, 150, 243, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₦' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Load initial revenue data
            loadRevenueData(7);
        }

        // Initialize order status chart
        const orderStatusCtx = document.getElementById('orderStatusChart');
        if (orderStatusCtx) {
            orderStatusChart = new Chart(orderStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            'rgba(76, 175, 80, 0.8)',
                            'rgba(255, 152, 0, 0.8)',
                            'rgba(244, 67, 54, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Load order status data
            loadOrderStatusData();
        }

        // Bind revenue period selector
        const periodSelect = document.getElementById('revenuePeriodSelect');
        if (periodSelect) {
            periodSelect.addEventListener('change', function() {
                const days = parseInt(this.value);
                loadRevenueData(days);
            });
        }
    }

    function loadRevenueData(days) {
        fetch(`api/get-revenue.php?days=${days}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && revenueChart) {
                    if (data.data && data.data.length > 0) {
                        const labels = data.data.map(item => {
                            const date = new Date(item.date);
                            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                        });
                        const revenues = data.data.map(item => parseFloat(item.revenue));
                        
                        revenueChart.data.labels = labels;
                        revenueChart.data.datasets[0].data = revenues;
                        revenueChart.update();
                    } else {
                        // Handle empty data
                        revenueChart.data.labels = ['No data'];
                        revenueChart.data.datasets[0].data = [0];
                        revenueChart.update();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading revenue data:', error);
            });
    }

    function loadOrderStatusData() {
        fetch('api/get-order-status.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && orderStatusChart) {
                    const labels = data.data.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
                    const values = data.data.map(item => item.count);
                    const percentages = data.data.map(item => item.percentage);
                    
                    orderStatusChart.data.labels = labels;
                    orderStatusChart.data.datasets[0].data = values;
                    orderStatusChart.update();
                    
                    // Update legend
                    const legendDiv = document.getElementById('orderStatusLegend');
                    if (legendDiv) {
                        legendDiv.innerHTML = '';
                        data.data.forEach((item, index) => {
                            const colors = ['var(--success)', 'var(--warning)', 'var(--danger)'];
                            const color = colors[index] || 'var(--gray)';
                            const legendItem = document.createElement('div');
                            legendItem.style.display = 'flex';
                            legendItem.style.alignItems = 'center';
                            legendItem.innerHTML = `
                                <div style="width: 12px; height: 12px; background: ${color}; border-radius: 50%; margin-right: 5px;"></div>
                                <span>${item.status.charAt(0).toUpperCase() + item.status.slice(1)} (${item.percentage}%)</span>
                            `;
                            legendDiv.appendChild(legendItem);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error loading order status data:', error);
            });
    }

    function loadActivityFeed() {
        fetch('api/get-activity-feed.php?limit=5')
            .then(response => response.json())
            .then(data => {
                const activityList = document.getElementById('activityList');
                if (!activityList) return;
                
                if (data.success && data.data && data.data.length > 0) {
                    activityList.innerHTML = '';
                    data.data.forEach(activity => {
                        const timeAgo = getTimeAgo(activity.timestamp);
                        const iconClass = getActivityIcon(activity.type);
                        const iconBg = getActivityIconBg(activity.type);
                        
                        const item = document.createElement('li');
                        item.className = 'activity-item';
                        item.innerHTML = `
                            <div class="activity-icon ${iconBg}">
                                <i class="${iconClass}"></i>
                            </div>
                            <div class="activity-details">
                                <h4>${activity.title}</h4>
                                <p>${activity.message}</p>
                            </div>
                            <div class="activity-time">${timeAgo}</div>
                        `;
                        activityList.appendChild(item);
                    });
                } else {
                    // Fallback static data
                    activityList.innerHTML = `
                        <li class="activity-item">
                            <div class="activity-icon order">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="activity-details">
                                <h4>New Order Received</h4>
                                <p>Order #JP-2847 for 2 people</p>
                            </div>
                            <div class="activity-time">10 min ago</div>
                        </li>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading activity feed:', error);
            });
    }

    function loadTopMenuItems() {
        fetch('api/get-top-menu-items.php')
            .then(response => response.json())
            .then(data => {
                const topItemsList = document.getElementById('topItemsList');
                if (!topItemsList) return;
                
                if (data.success && data.data && data.data.length > 0) {
                    topItemsList.innerHTML = '';
                    data.data.forEach((item, index) => {
                        const rank = index + 1;
                        const rankClass = rank <= 3 ? `rank-${rank}` : '';
                        const itemEl = document.createElement('li');
                        itemEl.className = 'top-item';
                        itemEl.innerHTML = `
                            <div class="item-rank ${rankClass}">${rank}</div>
                            <div class="item-details">
                                <h4>${item.item_name}</h4>
                                <p>Menu item</p>
                            </div>
                            <div class="item-sales">${item.total_quantity} sales</div>
                        `;
                        topItemsList.appendChild(itemEl);
                    });
                } else {
                    // Fallback static data
                    topItemsList.innerHTML = `
                        <li class="top-item">
                            <div class="item-rank rank-1">1</div>
                            <div class="item-details">
                                <h4>Ofe Owerri Special</h4>
                                <p>Traditional Igbo soup</p>
                            </div>
                            <div class="item-sales">142 sales</div>
                        </li>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading top menu items:', error);
            });
    }

    function getTimeAgo(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const time = Math.floor(new Date(timestamp).getTime() / 1000);
        const diff = now - time;
        
        if (diff < 60) {
            return 'Just now';
        } else if (diff < 3600) {
            const minutes = Math.floor(diff / 60);
            return minutes + ' min ago';
        } else if (diff < 86400) {
            const hours = Math.floor(diff / 3600);
            return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
        } else {
            const days = Math.floor(diff / 86400);
            return days + ' day' + (days > 1 ? 's' : '') + ' ago';
        }
    }

    function getActivityIcon(type) {
        const icons = {
            'order': 'fas fa-shopping-bag',
            'reservation': 'fas fa-calendar-plus',
            'payment': 'fas fa-credit-card',
            'review': 'fas fa-star'
        };
        return icons[type] || 'fas fa-circle';
    }

    function getActivityIconBg(type) {
        const backgrounds = {
            'order': 'order',
            'reservation': 'reservation',
            'payment': 'payment',
            'review': 'review'
        };
        return backgrounds[type] || 'order';
    }

    // Load SweetAlert2 if not already loaded
    (function() {
        if (typeof Swal === 'undefined') {
            const swalScript = document.createElement('script');
            swalScript.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            swalScript.onload = function() {
                console.log('SweetAlert2 loaded successfully');
            };
            swalScript.onerror = function() {
                console.warn('Failed to load SweetAlert2, falling back to browser confirm');
            };
            document.head.appendChild(swalScript);
        }
    })();

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Simple animation for stats cards on load
        const statCards = document.querySelectorAll('.stat-card');

        statCards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Set initial state for animation
        statCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });

        // Initialize admin cards
        loadAdmins();
        
        // Set up event delegation for admin card buttons (only once, works for all dynamically created cards)
        setupAdminCardEventDelegation();
        
        // Load notifications
        loadNotifications();
        
        // Poll for notifications every 45 seconds
        setInterval(loadNotifications, 45000);

        // Initialize scroll reveal
        window.addEventListener('scroll', revealOnScroll);
        // Trigger once on load to check initial position
        revealOnScroll();
        
        // Initialize charts
        initializeCharts();
        
        // Load activity feed and top items
        loadActivityFeed();
        loadTopMenuItems();
    });
</script>
</body>
</html>