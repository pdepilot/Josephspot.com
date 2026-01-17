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

// Get admin user data
function getAdminData($admin_id)
{
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

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: admin-login.php");
    exit;
}

// Create tables if they don't exist
$conn = getDBConnection();
createTablesIfNotExist($conn);

// Get admin data for display
$admin_data = getAdminData($_SESSION['admin_id']);
$username = 'Admin';
$user_initials = 'AJ';

if ($admin_data) {
    $username = $admin_data['username'];
    $user_initials = strtoupper(substr($admin_data['username'], 0, 2));
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
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p>Super Admin</p>
                </div>
            </div>

            <ul class="menu-items">
                <li class="menu-label">Main</li>
                <li class="menu-item">
                    <a href="dashboard.php" class="active">
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
                <li class="menu-item">
                    <a href="admin-career.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Careers</span>
                    </a>
                </li>

                <li class="menu-label">Settings</li>
                <li class="menu-item">
                    <a href="admin-settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="./admin-logout.php" onclick="return confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>