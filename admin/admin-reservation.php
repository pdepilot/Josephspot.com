<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit();
}

// Database connection - Use the SAME connection as frontend
$servername = "localhost";
$username = "root"; // Must be same as frontend
$password = ""; // Must be same as frontend
$database = "joseph_pot_admin"; // Must be same database

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
$message = '';
$message_type = '';

// Add new reservation (from admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_reservation') {
        $customer_name = $conn->real_escape_string($_POST['customer_name']);
        $customer_phone = $conn->real_escape_string($_POST['customer_phone']);
        $customer_email = $conn->real_escape_string($_POST['customer_email']);
        $reservation_date = $conn->real_escape_string($_POST['reservation_date']);
        $reservation_time = $conn->real_escape_string($_POST['reservation_time']);
        $party_size = intval($_POST['party_size']);
        $purpose = $conn->real_escape_string($_POST['purpose']);
        $status = $conn->real_escape_string($_POST['status']);
        $special_requests = $conn->real_escape_string($_POST['special_requests']);
        
        $sql = "INSERT INTO reservations (customer_name, customer_phone, customer_email, reservation_date, reservation_time, party_size, purpose, status, special_requests, source) 
                VALUES ('$customer_name', '$customer_phone', '$customer_email', '$reservation_date', '$reservation_time', $party_size, '$purpose', '$status', '$special_requests', 'admin')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "Reservation added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding reservation: " . $conn->error;
            $message_type = "error";
        }
    }
    
    // Update reservation
    if ($_POST['action'] === 'update_reservation') {
        $id = intval($_POST['reservation_id']);
        $customer_name = $conn->real_escape_string($_POST['customer_name']);
        $customer_phone = $conn->real_escape_string($_POST['customer_phone']);
        $customer_email = $conn->real_escape_string($_POST['customer_email']);
        $reservation_date = $conn->real_escape_string($_POST['reservation_date']);
        $reservation_time = $conn->real_escape_string($_POST['reservation_time']);
        $party_size = intval($_POST['party_size']);
        $purpose = $conn->real_escape_string($_POST['purpose']);
        $status = $conn->real_escape_string($_POST['status']);
        $special_requests = $conn->real_escape_string($_POST['special_requests']);
        
        $sql = "UPDATE reservations SET 
                customer_name = '$customer_name',
                customer_phone = '$customer_phone',
                customer_email = '$customer_email',
                reservation_date = '$reservation_date',
                reservation_time = '$reservation_time',
                party_size = $party_size,
                purpose = '$purpose',
                status = '$status',
                special_requests = '$special_requests'
                WHERE id = $id";
        
        if ($conn->query($sql) === TRUE) {
            $message = "Reservation updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating reservation: " . $conn->error;
            $message_type = "error";
        }
    }
}

// Delete reservation
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    $sql = "DELETE FROM reservations WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        $message = "Reservation deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting reservation: " . $conn->error;
        $message_type = "error";
    }
}

// Confirm reservation
if (isset($_GET['confirm_id'])) {
    $id = intval($_GET['confirm_id']);
    
    $sql = "UPDATE reservations SET status = 'confirmed' WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        $message = "Reservation confirmed successfully!";
        $message_type = "success";
    } else {
        $message = "Error confirming reservation: " . $conn->error;
        $message_type = "error";
    }
}

// Get all reservations (both website and admin)
$sql_all = "SELECT * FROM reservations ORDER BY reservation_date DESC, reservation_time DESC";
$result_all = $conn->query($sql_all);

// Get today's reservations
$today = date('Y-m-d');
$sql_today = "SELECT * FROM reservations WHERE reservation_date = '$today' ORDER BY reservation_time ASC";
$result_today = $conn->query($sql_today);

// Get upcoming reservations
$sql_upcoming = "SELECT * FROM reservations WHERE reservation_date > '$today' AND status != 'cancelled' ORDER BY reservation_date ASC, reservation_time ASC";
$result_upcoming = $conn->query($sql_upcoming);

// Get pending reservations
$sql_pending = "SELECT * FROM reservations WHERE status = 'pending' ORDER BY reservation_date ASC, reservation_time ASC";
$result_pending = $conn->query($sql_pending);

// Get stats
$sql_today_count = "SELECT COUNT(*) as count FROM reservations WHERE reservation_date = '$today'";
$result_today_count = $conn->query($sql_today_count);
$today_count = $result_today_count->fetch_assoc()['count'];

$sql_upcoming_count = "SELECT COUNT(*) as count FROM reservations WHERE reservation_date > '$today' AND status != 'cancelled'";
$result_upcoming_count = $conn->query($sql_upcoming_count);
$upcoming_count = $result_upcoming_count->fetch_assoc()['count'];

$sql_completed_count = "SELECT COUNT(*) as count FROM reservations WHERE status = 'completed' AND MONTH(reservation_date) = MONTH(CURDATE()) AND YEAR(reservation_date) = YEAR(CURDATE())";
$result_completed_count = $conn->query($sql_completed_count);
$completed_count = $result_completed_count->fetch_assoc()['count'];

$sql_cancelled_count = "SELECT COUNT(*) as count FROM reservations WHERE status = 'cancelled' AND MONTH(reservation_date) = MONTH(CURDATE()) AND YEAR(reservation_date) = YEAR(CURDATE())";
$result_cancelled_count = $conn->query($sql_cancelled_count);
$cancelled_count = $result_cancelled_count->fetch_assoc()['count'];

// Get reservation for editing
$edit_reservation = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    $sql_edit = "SELECT * FROM reservations WHERE id = $id";
    $result_edit = $conn->query($sql_edit);
    if ($result_edit->num_rows > 0) {
        $edit_reservation = $result_edit->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo3.png">
    <title>Reservations - Joseph's Pot</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Add message styles */
        .message {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideIn 0.3s ease;
        }
        
        .message.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .message.error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .message .close-message {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .message .close-message:hover {
            opacity: 1;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Rest of your existing CSS remains the same */
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

        /* Header Styles */
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

        /* User Menu for Smaller Screens */
        .user-menu-mobile {
            display: none;
            position: relative;
            cursor: pointer;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
            background: var(--gray);
        }

        .user-menu-mobile i {
            font-size: 1.3rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .user-menu-mobile:hover {
            background: var(--gray-dark);
        }

        /* Reservations Management Styles */
        .reservations-management {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .reservations-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .reservations-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
            width: 100%;
        }

        .add-reservation-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            font-weight: 500;
            width: 100%;
            justify-content: center;
        }

        .add-reservation-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .reservations-tabs {
            display: flex;
            border-bottom: 2px solid var(--gray);
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .reservations-tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-light);
            transition: var(--transition);
            position: relative;
            flex: 1;
            min-width: 150px;
            text-align: center;
        }

        .reservations-tab.active {
            color: var(--primary);
        }

        .reservations-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
        }

        .reservations-tab:hover {
            color: var(--primary);
        }

        .reservations-content {
            display: none;
        }

        .reservations-content.active {
            display: block;
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

        .stat-card.today::before {
            background: var(--info);
        }

        .stat-card.upcoming::before {
            background: var(--warning);
        }

        .stat-card.completed::before {
            background: var(--success);
        }

        .stat-card.cancelled::before {
            background: var(--danger);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stat-card.today i {
            color: var(--info);
        }

        .stat-card.upcoming i {
            color: var(--warning);
        }

        .stat-card.completed i {
            color: var(--success);
        }

        .stat-card.cancelled i {
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

        /* Reservations Table */
        .reservations-table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .reservations-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 1000px;
        }

        .reservations-table th {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
        }

        .reservations-table td {
            padding: 15px;
            border-bottom: 1px solid var(--gray);
            vertical-align: middle;
        }

        .reservations-table tr:hover {
            background: var(--gray);
        }

        .reservations-table tr:last-child td {
            border-bottom: none;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }

        .customer-details h4 {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .customer-details p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .reservation-date {
            font-weight: 600;
        }

        .reservation-time {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .reservation-party {
            text-align: center;
        }

        .party-size {
            display: inline-block;
            background: var(--primary-light);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .reservation-purpose {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .purpose-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .purpose-dining {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .purpose-event {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info);
        }

        .purpose-catering {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .purpose-takeaway {
            background: rgba(156, 39, 176, 0.1);
            color: #9c27b0;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
            white-space: nowrap;
        }

        .status-confirmed {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .status-cancelled {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .status-completed {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info);
        }

        .table-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        .table-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .table-action-btn.edit {
            background: var(--info);
            color: white;
        }

        .table-action-btn.delete {
            background: var(--danger);
            color: white;
        }

        .table-action-btn.confirm {
            background: var(--success);
            color: white;
        }

        .table-action-btn:hover {
            transform: scale(1.1);
        }

        /* Mobile Card View (Alternative to Table) */
        .reservations-mobile-view {
            display: none;
        }

        .reservation-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }

        .reservation-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .reservation-card-customer {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .reservation-card-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .reservation-detail-item {
            display: flex;
            flex-direction: column;
        }

        .reservation-detail-label {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-bottom: 3px;
        }

        .reservation-detail-value {
            font-weight: 500;
        }

        .reservation-card-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid var(--gray);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--gray-dark);
        }

        .empty-state h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
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
            max-width: 600px;
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

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--gray-dark);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            color: var(--text-light);
            font-size: 0.9rem;
            border-top: 1px solid var(--gray-dark);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-cards {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .reservations-table {
                min-width: 900px;
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

            .reservations-header h3 {
                font-size: 1.2rem;
            }

            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .user-menu-mobile {
                display: flex;
            }

            .form-row {
                grid-template-columns: 1fr;
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

            .reservations-tabs {
                flex-direction: column;
            }

            .reservations-tab {
                min-width: 100%;
                text-align: left;
                padding: 10px 15px;
            }

            .reservations-tab.active::after {
                width: 5px;
                height: 100%;
                left: 0;
                top: 0;
                bottom: 0;
            }

            .add-reservation-btn {
                margin-top: 10px;
            }

            .reservations-table-container {
                display: none;
            }

            .reservations-mobile-view {
                display: block;
            }

            .customer-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .table-actions {
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }

            .reservations-management {
                padding: 20px 15px;
            }

            .reservation-card-details {
                grid-template-columns: 1fr;
            }

            .reservations-tab {
                padding: 8px 12px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-value {
                font-size: 1.5rem;
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
        }

        @media (max-width: 480px) {
            .logo-area h1 {
                font-size: 1.2rem;
            }

            .header h2 {
                font-size: 1.3rem;
            }

            .reservations-header h3 {
                font-size: 1.1rem;
            }

            .customer-avatar {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }

            .table-action-btn {
                width: 28px;
                height: 28px;
                font-size: 0.8rem;
            }

            .status-badge {
                padding: 4px 8px;
                font-size: 0.7rem;
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
                <div class="admin-avatar">AJ</div>
                <div class="admin-details">
                    <h3>Admin Joseph</h3>
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
                    <a href="admin-reservation.php" class="active">
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
                <li class="menu-item">
                    <a href="admin-customers.php">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li>
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
                    <a href="admin-settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="admin-logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="header">
                <h2>Reservations Management</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search reservations...">
                    </div>
                    <div class="user-menu-mobile">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Display Messages -->
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <span><?php echo htmlspecialchars($message); ?></span>
                    <button class="close-message">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Reservations Stats -->
            <div class="stats-cards">
                <div class="stat-card today reveal">
                    <i class="fas fa-calendar-day"></i>
                    <div class="stat-value" id="todayCount"><?php echo $today_count; ?></div>
                    <div class="stat-label">Today's Reservations</div>
                </div>

                <div class="stat-card upcoming reveal reveal-delay-1">
                    <i class="fas fa-calendar-week"></i>
                    <div class="stat-value" id="upcomingCount"><?php echo $upcoming_count; ?></div>
                    <div class="stat-label">Upcoming Reservations</div>
                </div>

                <div class="stat-card completed reveal reveal-delay-2">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-value" id="completedCount"><?php echo $completed_count; ?></div>
                    <div class="stat-label">Completed This Month</div>
                </div>

                <div class="stat-card cancelled reveal reveal-delay-3">
                    <i class="fas fa-times-circle"></i>
                    <div class="stat-value" id="cancelledCount"><?php echo $cancelled_count; ?></div>
                    <div class="stat-label">Cancelled This Month</div>
                </div>
            </div>

            <!-- Reservations Management Section -->
            <div class="reservations-management reveal">
                <div class="reservations-header">
                    <h3>All Reservations</h3>
                    <button class="add-reservation-btn" id="addReservationBtn">
                        <i class="fas fa-plus"></i>
                        Add New Reservation
                    </button>
                </div>

                <div class="reservations-tabs">
                    <button class="reservations-tab active" data-tab="all">All Reservations</button>
                    <button class="reservations-tab" data-tab="today">Today</button>
                    <button class="reservations-tab" data-tab="upcoming">Upcoming</button>
                    <button class="reservations-tab" data-tab="pending">Pending</button>
                </div>

                <!-- All Reservations (Table View) -->
                <div class="reservations-content active" id="all">
                    <div class="reservations-table-container">
                        <table class="reservations-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Date & Time</th>
                                    <th>Party Size</th>
                                    <th>Reservation For</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="allReservations">
                                <?php if ($result_all->num_rows > 0): ?>
                                    <?php while($row = $result_all->fetch_assoc()): ?>
                                        <?php
                                        // Get initials for avatar
                                        $initials = '';
                                        $name_parts = explode(' ', $row['customer_name']);
                                        foreach ($name_parts as $part) {
                                            if (!empty($part)) {
                                                $initials .= strtoupper($part[0]);
                                            }
                                        }
                                        $initials = substr($initials, 0, 2);
                                        
                                        // Format date
                                        $date = new DateTime($row['reservation_date']);
                                        $formatted_date = $date->format('D, M d, Y');
                                        
                                        // Purpose badge class
                                        $purpose_class = '';
                                        $purpose_text = '';
                                        switch($row['purpose']) {
                                            case 'dining':
                                                $purpose_class = 'purpose-dining';
                                                $purpose_text = 'Dining In';
                                                break;
                                            case 'event':
                                                $purpose_class = 'purpose-event';
                                                $purpose_text = 'Special Event';
                                                break;
                                            case 'catering':
                                                $purpose_class = 'purpose-catering';
                                                $purpose_text = 'Catering';
                                                break;
                                            case 'takeaway':
                                                $purpose_class = 'purpose-takeaway';
                                                $purpose_text = 'Takeaway';
                                                break;
                                        }
                                        
                                        // Status badge class
                                        $status_class = '';
                                        switch($row['status']) {
                                            case 'confirmed':
                                                $status_class = 'status-confirmed';
                                                break;
                                            case 'pending':
                                                $status_class = 'status-pending';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'status-cancelled';
                                                break;
                                            case 'completed':
                                                $status_class = 'status-completed';
                                                break;
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="customer-info">
                                                    <div class="customer-avatar"><?php echo $initials; ?></div>
                                                    <div class="customer-details">
                                                        <h4><?php echo htmlspecialchars($row['customer_name']); ?></h4>
                                                        <p><?php echo htmlspecialchars($row['customer_email']); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="reservation-date"><?php echo $formatted_date; ?></div>
                                                <div class="reservation-time"><?php echo date('g:i A', strtotime($row['reservation_time'])); ?></div>
                                            </td>
                                            <td class="reservation-party">
                                                <span class="party-size"><?php echo $row['party_size']; ?></span>
                                            </td>
                                            <td class="reservation-purpose">
                                                <span class="purpose-badge <?php echo $purpose_class; ?>"><?php echo $purpose_text; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="?edit_id=<?php echo $row['id']; ?>" class="table-action-btn edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete_id=<?php echo $row['id']; ?>" class="table-action-btn delete" onclick="return confirm('Are you sure you want to delete this reservation?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <?php if ($row['status'] == 'pending'): ?>
                                                        <a href="?confirm_id=<?php echo $row['id']; ?>" class="table-action-btn confirm" onclick="return confirm('Confirm this reservation?')">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-times"></i>
                                                <h4>No reservations found</h4>
                                                <p>There are no reservations in the system yet</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Today's Reservations -->
                <div class="reservations-content" id="today">
                    <div class="reservations-table-container">
                        <table class="reservations-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Time</th>
                                    <th>Party Size</th>
                                    <th>Reservation For</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="todayReservations">
                                <?php if ($result_today->num_rows > 0): ?>
                                    <?php while($row = $result_today->fetch_assoc()): ?>
                                        <?php
                                        // Get initials for avatar
                                        $initials = '';
                                        $name_parts = explode(' ', $row['customer_name']);
                                        foreach ($name_parts as $part) {
                                            if (!empty($part)) {
                                                $initials .= strtoupper($part[0]);
                                            }
                                        }
                                        $initials = substr($initials, 0, 2);
                                        
                                        // Purpose badge class
                                        $purpose_class = '';
                                        $purpose_text = '';
                                        switch($row['purpose']) {
                                            case 'dining':
                                                $purpose_class = 'purpose-dining';
                                                $purpose_text = 'Dining In';
                                                break;
                                            case 'event':
                                                $purpose_class = 'purpose-event';
                                                $purpose_text = 'Special Event';
                                                break;
                                            case 'catering':
                                                $purpose_class = 'purpose-catering';
                                                $purpose_text = 'Catering';
                                                break;
                                            case 'takeaway':
                                                $purpose_class = 'purpose-takeaway';
                                                $purpose_text = 'Takeaway';
                                                break;
                                        }
                                        
                                        // Status badge class
                                        $status_class = '';
                                        switch($row['status']) {
                                            case 'confirmed':
                                                $status_class = 'status-confirmed';
                                                break;
                                            case 'pending':
                                                $status_class = 'status-pending';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'status-cancelled';
                                                break;
                                            case 'completed':
                                                $status_class = 'status-completed';
                                                break;
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="customer-info">
                                                    <div class="customer-avatar"><?php echo $initials; ?></div>
                                                    <div class="customer-details">
                                                        <h4><?php echo htmlspecialchars($row['customer_name']); ?></h4>
                                                        <p><?php echo htmlspecialchars($row['customer_email']); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="reservation-time"><?php echo date('g:i A', strtotime($row['reservation_time'])); ?></div>
                                            </td>
                                            <td class="reservation-party">
                                                <span class="party-size"><?php echo $row['party_size']; ?></span>
                                            </td>
                                            <td class="reservation-purpose">
                                                <span class="purpose-badge <?php echo $purpose_class; ?>"><?php echo $purpose_text; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="?edit_id=<?php echo $row['id']; ?>" class="table-action-btn edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete_id=<?php echo $row['id']; ?>" class="table-action-btn delete" onclick="return confirm('Are you sure you want to delete this reservation?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <?php if ($row['status'] == 'pending'): ?>
                                                        <a href="?confirm_id=<?php echo $row['id']; ?>" class="table-action-btn confirm" onclick="return confirm('Confirm this reservation?')">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-times"></i>
                                                <h4>No reservations for today</h4>
                                                <p>There are no reservations scheduled for today</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Upcoming Reservations -->
                <div class="reservations-content" id="upcoming">
                    <div class="reservations-table-container">
                        <table class="reservations-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Date & Time</th>
                                    <th>Party Size</th>
                                    <th>Reservation For</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="upcomingReservations">
                                <?php if ($result_upcoming->num_rows > 0): ?>
                                    <?php while($row = $result_upcoming->fetch_assoc()): ?>
                                        <?php
                                        // Get initials for avatar
                                        $initials = '';
                                        $name_parts = explode(' ', $row['customer_name']);
                                        foreach ($name_parts as $part) {
                                            if (!empty($part)) {
                                                $initials .= strtoupper($part[0]);
                                            }
                                        }
                                        $initials = substr($initials, 0, 2);
                                        
                                        // Format date
                                        $date = new DateTime($row['reservation_date']);
                                        $formatted_date = $date->format('D, M d, Y');
                                        
                                        // Purpose badge class
                                        $purpose_class = '';
                                        $purpose_text = '';
                                        switch($row['purpose']) {
                                            case 'dining':
                                                $purpose_class = 'purpose-dining';
                                                $purpose_text = 'Dining In';
                                                break;
                                            case 'event':
                                                $purpose_class = 'purpose-event';
                                                $purpose_text = 'Special Event';
                                                break;
                                            case 'catering':
                                                $purpose_class = 'purpose-catering';
                                                $purpose_text = 'Catering';
                                                break;
                                            case 'takeaway':
                                                $purpose_class = 'purpose-takeaway';
                                                $purpose_text = 'Takeaway';
                                                break;
                                        }
                                        
                                        // Status badge class
                                        $status_class = '';
                                        switch($row['status']) {
                                            case 'confirmed':
                                                $status_class = 'status-confirmed';
                                                break;
                                            case 'pending':
                                                $status_class = 'status-pending';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'status-cancelled';
                                                break;
                                            case 'completed':
                                                $status_class = 'status-completed';
                                                break;
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="customer-info">
                                                    <div class="customer-avatar"><?php echo $initials; ?></div>
                                                    <div class="customer-details">
                                                        <h4><?php echo htmlspecialchars($row['customer_name']); ?></h4>
                                                        <p><?php echo htmlspecialchars($row['customer_email']); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="reservation-date"><?php echo $formatted_date; ?></div>
                                                <div class="reservation-time"><?php echo date('g:i A', strtotime($row['reservation_time'])); ?></div>
                                            </td>
                                            <td class="reservation-party">
                                                <span class="party-size"><?php echo $row['party_size']; ?></span>
                                            </td>
                                            <td class="reservation-purpose">
                                                <span class="purpose-badge <?php echo $purpose_class; ?>"><?php echo $purpose_text; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="?edit_id=<?php echo $row['id']; ?>" class="table-action-btn edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete_id=<?php echo $row['id']; ?>" class="table-action-btn delete" onclick="return confirm('Are you sure you want to delete this reservation?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <?php if ($row['status'] == 'pending'): ?>
                                                        <a href="?confirm_id=<?php echo $row['id']; ?>" class="table-action-btn confirm" onclick="return confirm('Confirm this reservation?')">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-times"></i>
                                                <h4>No upcoming reservations</h4>
                                                <p>There are no upcoming reservations scheduled</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pending Reservations -->
                <div class="reservations-content" id="pending">
                    <div class="reservations-table-container">
                        <table class="reservations-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Date & Time</th>
                                    <th>Party Size</th>
                                    <th>Reservation For</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pendingReservations">
                                <?php if ($result_pending->num_rows > 0): ?>
                                    <?php while($row = $result_pending->fetch_assoc()): ?>
                                        <?php
                                        // Get initials for avatar
                                        $initials = '';
                                        $name_parts = explode(' ', $row['customer_name']);
                                        foreach ($name_parts as $part) {
                                            if (!empty($part)) {
                                                $initials .= strtoupper($part[0]);
                                            }
                                        }
                                        $initials = substr($initials, 0, 2);
                                        
                                        // Format date
                                        $date = new DateTime($row['reservation_date']);
                                        $formatted_date = $date->format('D, M d, Y');
                                        
                                        // Purpose badge class
                                        $purpose_class = '';
                                        $purpose_text = '';
                                        switch($row['purpose']) {
                                            case 'dining':
                                                $purpose_class = 'purpose-dining';
                                                $purpose_text = 'Dining In';
                                                break;
                                            case 'event':
                                                $purpose_class = 'purpose-event';
                                                $purpose_text = 'Special Event';
                                                break;
                                            case 'catering':
                                                $purpose_class = 'purpose-catering';
                                                $purpose_text = 'Catering';
                                                break;
                                            case 'takeaway':
                                                $purpose_class = 'purpose-takeaway';
                                                $purpose_text = 'Takeaway';
                                                break;
                                        }
                                        
                                        // Status badge class
                                        $status_class = '';
                                        switch($row['status']) {
                                            case 'confirmed':
                                                $status_class = 'status-confirmed';
                                                break;
                                            case 'pending':
                                                $status_class = 'status-pending';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'status-cancelled';
                                                break;
                                            case 'completed':
                                                $status_class = 'status-completed';
                                                break;
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="customer-info">
                                                    <div class="customer-avatar"><?php echo $initials; ?></div>
                                                    <div class="customer-details">
                                                        <h4><?php echo htmlspecialchars($row['customer_name']); ?></h4>
                                                        <p><?php echo htmlspecialchars($row['customer_email']); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="reservation-date"><?php echo $formatted_date; ?></div>
                                                <div class="reservation-time"><?php echo date('g:i A', strtotime($row['reservation_time'])); ?></div>
                                            </td>
                                            <td class="reservation-party">
                                                <span class="party-size"><?php echo $row['party_size']; ?></span>
                                            </td>
                                            <td class="reservation-purpose">
                                                <span class="purpose-badge <?php echo $purpose_class; ?>"><?php echo $purpose_text; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="?edit_id=<?php echo $row['id']; ?>" class="table-action-btn edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete_id=<?php echo $row['id']; ?>" class="table-action-btn delete" onclick="return confirm('Are you sure you want to delete this reservation?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <?php if ($row['status'] == 'pending'): ?>
                                                        <a href="?confirm_id=<?php echo $row['id']; ?>" class="table-action-btn confirm" onclick="return confirm('Confirm this reservation?')">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-times"></i>
                                                <h4>No pending reservations</h4>
                                                <p>There are no pending reservations at the moment</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Reservation Modal -->
    <div class="modal" id="reservationModal" style="<?php echo ($edit_reservation) ? 'display: flex;' : ''; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle"><?php echo ($edit_reservation) ? 'Edit Reservation' : 'Add New Reservation'; ?></h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reservationForm" method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo ($edit_reservation) ? 'update_reservation' : 'add_reservation'; ?>">
                    <?php if ($edit_reservation): ?>
                        <input type="hidden" name="reservation_id" value="<?php echo $edit_reservation['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customerName">Customer Name</label>
                            <input type="text" id="customerName" name="customer_name" class="form-control" required value="<?php echo ($edit_reservation) ? htmlspecialchars($edit_reservation['customer_name']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="customerPhone">Phone Number</label>
                            <input type="tel" id="customerPhone" name="customer_phone" class="form-control" required value="<?php echo ($edit_reservation) ? htmlspecialchars($edit_reservation['customer_phone']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="customerEmail">Email Address</label>
                        <input type="email" id="customerEmail" name="customer_email" class="form-control" value="<?php echo ($edit_reservation) ? htmlspecialchars($edit_reservation['customer_email']) : ''; ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="reservationDate">Reservation Date</label>
                            <input type="date" id="reservationDate" name="reservation_date" class="form-control" required value="<?php echo ($edit_reservation) ? $edit_reservation['reservation_date'] : date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="reservationTime">Reservation Time</label>
                            <select id="reservationTime" name="reservation_time" class="form-control" required>
                                <option value="">Select Time</option>
                                <?php
                                $time_slots = array(
                                    '11:00:00' => '11:00 AM',
                                    '12:00:00' => '12:00 PM',
                                    '13:00:00' => '1:00 PM',
                                    '14:00:00' => '2:00 PM',
                                    '18:00:00' => '6:00 PM',
                                    '19:00:00' => '7:00 PM',
                                    '20:00:00' => '8:00 PM',
                                    '21:00:00' => '9:00 PM'
                                );
                                
                                foreach ($time_slots as $time_value => $time_label) {
                                    $selected = ($edit_reservation && $edit_reservation['reservation_time'] == $time_value) ? 'selected' : '';
                                    echo "<option value=\"$time_value\" $selected>$time_label</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="partySize">Party Size</label>
                            <select id="partySize" name="party_size" class="form-control" required>
                                <option value="">Select Size</option>
                                <?php
                                $party_sizes = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
                                foreach ($party_sizes as $size) {
                                    $selected = ($edit_reservation && $edit_reservation['party_size'] == $size) ? 'selected' : '';
                                    $label = $size . ($size == 9 ? '+ people' : ' person' . ($size > 1 ? 's' : ''));
                                    if ($size == 9) $label = '9+ people';
                                    echo "<option value=\"$size\" $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reservationPurpose">Reservation For</label>
                            <select id="reservationPurpose" name="purpose" class="form-control" required>
                                <option value="">Select Purpose</option>
                                <option value="dining" <?php echo ($edit_reservation && $edit_reservation['purpose'] == 'dining') ? 'selected' : ''; ?>>Dining In</option>
                                <option value="event" <?php echo ($edit_reservation && $edit_reservation['purpose'] == 'event') ? 'selected' : ''; ?>>Special Event</option>
                                <option value="catering" <?php echo ($edit_reservation && $edit_reservation['purpose'] == 'catering') ? 'selected' : ''; ?>>Catering</option>
                                <option value="takeaway" <?php echo ($edit_reservation && $edit_reservation['purpose'] == 'takeaway') ? 'selected' : ''; ?>>Takeaway</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reservationStatus">Status</label>
                        <select id="reservationStatus" name="status" class="form-control" required>
                            <option value="pending" <?php echo ($edit_reservation && $edit_reservation['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo ($edit_reservation && $edit_reservation['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="completed" <?php echo ($edit_reservation && $edit_reservation['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo ($edit_reservation && $edit_reservation['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="specialRequests">Special Requests</label>
                        <textarea id="specialRequests" name="special_requests" class="form-control" rows="3" placeholder="Any special requests or notes..."><?php echo ($edit_reservation) ? htmlspecialchars($edit_reservation['special_requests']) : ''; ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary"><?php echo ($edit_reservation) ? 'Update Reservation' : 'Save Reservation'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved | Developed By ERIBS tech</p>
    </div>

    <script>
        // DOM Elements
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');
        const reservationsTabs = document.querySelectorAll('.reservations-tab');
        const reservationsContents = document.querySelectorAll('.reservations-content');
        const addReservationBtn = document.getElementById('addReservationBtn');
        const reservationModal = document.getElementById('reservationModal');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const reservationForm = document.getElementById('reservationForm');
        const modalTitle = document.getElementById('modalTitle');

        // Close message button
        const closeMessageButtons = document.querySelectorAll('.close-message');
        closeMessageButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.message').style.display = 'none';
            });
        });

        // Auto-hide message after 5 seconds
        const messages = document.querySelectorAll('.message');
        messages.forEach(message => {
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000);
        });

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

        // Tab switching functionality
        reservationsTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.getAttribute('data-tab');

                // Update active tab
                reservationsTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                // Show corresponding content
                reservationsContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === targetTab) {
                        content.classList.add('active');
                    }
                });
            });
        });

        // Modal functionality
        addReservationBtn.addEventListener('click', () => {
            modalTitle.textContent = 'Add New Reservation';
            reservationForm.action = '';
            reservationForm.querySelector('input[name="action"]').value = 'add_reservation';
            reservationForm.querySelector('input[name="reservation_id"]')?.remove();

            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('reservationDate').value = today;

            // Reset form
            const form = document.getElementById('reservationForm');
            const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
            inputs.forEach(input => {
                if (input.type !== 'submit' && input.type !== 'button') {
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                }
            });

            reservationModal.style.display = 'flex';
        });

        closeModal.addEventListener('click', () => {
            reservationModal.style.display = 'none';
            // Remove edit parameters from URL
            if (window.location.search.includes('edit_id')) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

        cancelBtn.addEventListener('click', () => {
            reservationModal.style.display = 'none';
            // Remove edit parameters from URL
            if (window.location.search.includes('edit_id')) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === reservationModal) {
                reservationModal.style.display = 'none';
                // Remove edit parameters from URL
                if (window.location.search.includes('edit_id')) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
        });

        // Search functionality
        const searchInput = document.querySelector('.search-box input');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tables = document.querySelectorAll('.reservations-table tbody');
            
            tables.forEach(table => {
                const rows = table.querySelectorAll('tr');
                let hasVisibleRows = false;
                
                rows.forEach(row => {
                    if (row.querySelector('.empty-state')) return;
                    
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                        hasVisibleRows = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Show empty state if no rows visible
                const emptyState = table.querySelector('.empty-state');
                if (emptyState) {
                    if (hasVisibleRows) {
                        emptyState.style.display = 'none';
                    } else {
                        emptyState.style.display = '';
                        emptyState.innerHTML = `
                            <i class="fas fa-search"></i>
                            <h4>No matching reservations found</h4>
                            <p>Try searching with different keywords</p>
                        `;
                    }
                }
            });
        });

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

        // Initialize the page
        document.addEventListener('DOMContentLoaded', () => {
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

            // Initialize scroll reveal
            window.addEventListener('scroll', revealOnScroll);
            // Trigger once on load to check initial position
            revealOnScroll();
            
            // Auto-close modal if editing and form submitted
            <?php if ($edit_reservation && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                setTimeout(() => {
                    reservationModal.style.display = 'none';
                    // Remove edit parameters from URL
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 100);
            <?php endif; ?>
        });
    </script>
</body>

</html>
<?php
$conn->close();
?>