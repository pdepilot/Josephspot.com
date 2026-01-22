<?php
// ============================================
// RESERVATION DATABASE AND MODEL CLASSES
// ============================================

class ReservationDatabase {
    private $host = 'localhost';
    private $db_name = 'joseph_pot_admin';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch(PDOException $e) {
            throw new Exception("Admin Database connection failed: " . $e->getMessage());
        }
        
        return $this->conn;
    }
}

class ReservationModel {
    private $conn;
    
    public function __construct() {
        try {
            $db = new ReservationDatabase();
            $this->conn = $db->getConnection();
        } catch (Exception $e) {
            throw new Exception("Could not initialize reservation model: " . $e->getMessage());
        }
    }
    
    public function getAllReservations($filters = []) {
        $sql = "SELECT * FROM reservations WHERE 1=1";
        $params = [];
        
        if (isset($filters['status']) && !empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY reservation_date DESC, reservation_time DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("GetAllReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTodayReservations() {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM reservations WHERE reservation_date = :today ORDER BY reservation_time ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':today' => $today]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("GetTodayReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUpcomingReservations() {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM reservations WHERE reservation_date > :today AND status IN ('pending', 'confirmed') ORDER BY reservation_date ASC, reservation_time ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':today' => $today]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("GetUpcomingReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPendingReservations() {
        $sql = "SELECT * FROM reservations WHERE status = 'pending' ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("GetPendingReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getReservationStats() {
        $today = date('Y-m-d');
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');
        
        $stats = ['today' => 0, 'upcoming' => 0, 'completed_this_month' => 0, 'cancelled_this_month' => 0];
        
        try {
            // Today's count
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE reservation_date = :today");
            $stmt->execute([':today' => $today]);
            $stats['today'] = $stmt->fetch()['count'];
            
            // Upcoming count
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE reservation_date > :today AND status IN ('pending', 'confirmed')");
            $stmt->execute([':today' => $today]);
            $stats['upcoming'] = $stmt->fetch()['count'];
            
            // Completed this month
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE status = 'completed' AND reservation_date BETWEEN :first_day AND :last_day");
            $stmt->execute([':first_day' => $firstDayOfMonth, ':last_day' => $lastDayOfMonth]);
            $stats['completed_this_month'] = $stmt->fetch()['count'];
            
            // Cancelled this month
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE status = 'cancelled' AND reservation_date BETWEEN :first_day AND :last_day");
            $stmt->execute([':first_day' => $firstDayOfMonth, ':last_day' => $lastDayOfMonth]);
            $stats['cancelled_this_month'] = $stmt->fetch()['count'];
            
        } catch (PDOException $e) {
            error_log("GetReservationStats Error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    public function getReservationById($id) {
        $sql = "SELECT * FROM reservations WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("GetReservationById Error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createReservation($data) {
        $sql = "INSERT INTO reservations (
            customer_name, customer_email, customer_phone,
            reservation_date, reservation_time, party_size,
            purpose, special_requests, status
        ) VALUES (
            :customer_name, :customer_email, :customer_phone,
            :reservation_date, :reservation_time, :party_size,
            :purpose, :special_requests, :status
        )";
        
        $params = [
            ':customer_name' => $data['customer_name'],
            ':customer_email' => $data['customer_email'],
            ':customer_phone' => $data['customer_phone'],
            ':reservation_date' => $data['reservation_date'],
            ':reservation_time' => $data['reservation_time'],
            ':party_size' => $data['party_size'],
            ':purpose' => $data['purpose'],
            ':special_requests' => $data['special_requests'],
            ':status' => $data['status'] ?? 'pending'
        ];
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("CreateReservation Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateReservation($id, $data) {
        $sql = "UPDATE reservations SET
            customer_name = :customer_name,
            customer_email = :customer_email,
            customer_phone = :customer_phone,
            reservation_date = :reservation_date,
            reservation_time = :reservation_time,
            party_size = :party_size,
            purpose = :purpose,
            special_requests = :special_requests,
            status = :status,
            updated_at = NOW()
        WHERE id = :id";
        
        $params = [
            ':customer_name' => $data['customer_name'],
            ':customer_email' => $data['customer_email'],
            ':customer_phone' => $data['customer_phone'],
            ':reservation_date' => $data['reservation_date'],
            ':reservation_time' => $data['reservation_time'],
            ':party_size' => $data['party_size'],
            ':purpose' => $data['purpose'],
            ':special_requests' => $data['special_requests'],
            ':status' => $data['status'],
            ':id' => $id
        ];
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("UpdateReservation Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteReservation($id) {
        $sql = "DELETE FROM reservations WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("DeleteReservation Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function searchReservations($searchTerm) {
        $sql = "SELECT * FROM reservations WHERE 
                customer_name LIKE :search OR 
                customer_email LIKE :search OR 
                customer_phone LIKE :search OR 
                special_requests LIKE :search
                ORDER BY reservation_date DESC";
        
        $params = [':search' => "%" . $searchTerm . "%"];
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("SearchReservations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateStatus($id, $status) {
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $sql = "UPDATE reservations SET status = :status WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':status' => $status, ':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("UpdateStatus Error: " . $e->getMessage());
            return false;
        }
    }
}

// ============================================
// ADMIN RESERVATION PAGE LOGIC
// ============================================

// Central authentication and permission check
require_once 'admin-auth.php';
checkPageAccess(); // This checks authentication and permission for current page

// Initialize reservation model
try {
    $reservationModel = new ReservationModel();
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Handle form submissions
$message = '';
$messageType = '';

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($reservationModel->deleteReservation($id)) {
        $message = "Reservation deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to delete reservation.";
        $messageType = "error";
    }
}

// Handle status update
if (isset($_GET['update_status']) && isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];
    
    if ($reservationModel->updateStatus($id, $status)) {
        $message = "Reservation status updated successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to update reservation status.";
        $messageType = "error";
    }
}

// Handle search
$searchTerm = '';
$reservations = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = htmlspecialchars($_GET['search']);
    $reservations = $reservationModel->searchReservations($searchTerm);
} else {
    $reservations = $reservationModel->getAllReservations();
}

// Get today's reservations
$todayReservations = $reservationModel->getTodayReservations();

// Get upcoming reservations
$upcomingReservations = $reservationModel->getUpcomingReservations();

// Get pending reservations
$pendingReservations = $reservationModel->getPendingReservations();

// Get statistics
$stats = $reservationModel->getReservationStats();

// Handle form submission for new/update reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $reservationData = [
        'customer_name' => htmlspecialchars($_POST['customer_name'] ?? ''),
        'customer_email' => filter_input(INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL),
        'customer_phone' => htmlspecialchars($_POST['customer_phone'] ?? ''),
        'reservation_date' => $_POST['reservation_date'] ?? '',
        'reservation_time' => $_POST['reservation_time'] ?? '',
        'party_size' => intval($_POST['party_size'] ?? 0),
        'purpose' => $_POST['purpose'] ?? 'Dining In',
        'special_requests' => htmlspecialchars($_POST['special_requests'] ?? ''),
        'status' => $_POST['status'] ?? 'pending'
    ];
    
    // Basic validation
    if (empty($reservationData['customer_name']) || empty($reservationData['customer_phone']) || 
        empty($reservationData['reservation_date']) || empty($reservationData['reservation_time'])) {
        $message = "Please fill in all required fields.";
        $messageType = "error";
    } else {
        if (isset($_POST['reservation_id']) && !empty($_POST['reservation_id'])) {
            // Update existing reservation
            $id = intval($_POST['reservation_id']);
            if ($reservationModel->updateReservation($id, $reservationData)) {
                // Create notification for reservation update
                try {
                    require_once 'includes/notification_helper.php';
                    $notif_conn = new mysqli('localhost', 'root', '', 'joseph_pot_admin');
                    if (!$notif_conn->connect_error) {
                        $notif_conn->set_charset("utf8mb4");
                        createNotification(
                            $notif_conn,
                            null, // notify all admins
                            'reservation',
                            'Reservation Updated',
                            'Reservation #' . $id . ' has been updated',
                            $id
                        );
                        $notif_conn->close();
                    }
                } catch (Exception $e) {
                    error_log('Notification error: ' . $e->getMessage());
                }
                $message = "Reservation updated successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to update reservation.";
                $messageType = "error";
            }
        } else {
            // Create new reservation
            $newId = $reservationModel->createReservation($reservationData);
            if ($newId) {
                // Create notification for new reservation
                try {
                    require_once 'includes/notification_helper.php';
                    $notif_conn = new mysqli('localhost', 'root', '', 'joseph_pot_admin');
                    if (!$notif_conn->connect_error) {
                        $notif_conn->set_charset("utf8mb4");
                        $customerName = htmlspecialchars($reservationData['customer_name']);
                        $partySize = $reservationData['party_size'];
                        createNotification(
                            $notif_conn,
                            null, // notify all admins
                            'reservation',
                            'New Reservation',
                            $customerName . ' reserved a table for ' . $partySize . ' people',
                            $newId
                        );
                        $notif_conn->close();
                    }
                } catch (Exception $e) {
                    error_log('Notification error: ' . $e->getMessage());
                }
                $message = "Reservation created successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to create reservation.";
                $messageType = "error";
            }
        }
        
        // Refresh data after operation
        $reservations = $reservationModel->getAllReservations();
        $todayReservations = $reservationModel->getTodayReservations();
        $upcomingReservations = $reservationModel->getUpcomingReservations();
        $pendingReservations = $reservationModel->getPendingReservations();
        $stats = $reservationModel->getReservationStats();
    }
}

// Get reservation for editing
$editReservation = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editReservation = $reservationModel->getReservationById($editId);
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
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow);
            z-index: 100;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        /* Mobile Sidebar Behavior */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                transform: translateX(0);
            }
            
            .sidebar .logo-area h1, 
            .sidebar .admin-details, 
            .sidebar .menu-label,
            .sidebar .menu-item span {
                display: none;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .sidebar .admin-info {
                justify-content: center;
                padding: 15px 10px;
            }
            
            .sidebar .menu-item a {
                justify-content: center;
                padding: 15px;
            }
            
            .sidebar .menu-item i {
                margin-right: 0;
            }
            
            /* Hover effect for mobile */
            .sidebar:hover {
                width: 260px;
            }
            
            .sidebar:hover .logo-area h1, 
            .sidebar:hover .admin-details, 
            .sidebar:hover .menu-label,
            .sidebar:hover .menu-item span {
                display: block;
                opacity: 1;
            }
            
            .sidebar:hover .admin-info {
                justify-content: flex-start;
                padding: 15px 20px;
            }
            
            .sidebar:hover .menu-item a {
                justify-content: flex-start;
                padding: 12px 15px;
            }
            
            .sidebar:hover .menu-item i {
                margin-right: 12px;
            }
            
            .main-content {
                margin-left: 80px;
            }
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

        .menu-item a:hover, .menu-item a.active {
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
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 80px;
            }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 25px;
        }

        .header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border: none;
            border-radius: 30px;
            background: white;
            box-shadow: var(--shadow);
            width: 250px;
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
        }

        .reservations-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary);
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
        }

        .add-reservation-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .reservations-tabs {
            display: flex;
            border-bottom: 2px solid var(--gray);
            margin-bottom: 25px;
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
        }

        .reservations-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .reservations-table th {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .reservations-table td {
            padding: 15px;
            border-bottom: 1px solid var(--gray);
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
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid var(--gray);
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

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
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
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--gray);
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-cards {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr 1fr;
            }
            
            .search-box input {
                width: 180px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .reservations-table {
                min-width: 900px;
            }
        }

        @media (max-width: 576px) {
            .reservations-tabs {
                flex-wrap: wrap;
            }
            
            .reservations-tab {
                flex: 1;
                min-width: 120px;
                text-align: center;
            }
            
            .search-box input {
                width: 100%;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- REST OF YOUR HTML CODE REMAINS EXACTLY THE SAME -->
    <!-- Copy the entire HTML body from the previous version starting from line: -->
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
                    <a href="admin-reviews.php">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
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
                
                <li class="menu-label">Analytics</li>
                <li class="menu-item">
                    <a href="../site-traffic.php">
                        <i class="fas fa-chart-line"></i>
                        <span>Traffic Analytics</span>
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
        <div class="main-content">
            <div class="header">
                <h2>Reservations Management</h2>
                <div class="header-actions">
                    <form method="GET" action="" class="search-box" style="display: flex; align-items: center;">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search reservations..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button type="submit" style="display: none;">Search</button>
                    </form>
                </div>
            </div>
            
            <!-- Message Display -->
            <?php if (!empty($message)): ?>
            <div class="message-notification <?php echo $messageType; ?>" style="margin-bottom: 20px; padding: 15px; border-radius: 8px; background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $messageType === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <!-- Reservations Stats -->
            <div class="stats-cards">
                <div class="stat-card today">
                    <i class="fas fa-calendar-day"></i>
                    <div class="stat-value" id="todayCount"><?php echo $stats['today']; ?></div>
                    <div class="stat-label">Today's Reservations</div>
                </div>
                
                <div class="stat-card upcoming">
                    <i class="fas fa-calendar-week"></i>
                    <div class="stat-value" id="upcomingCount"><?php echo $stats['upcoming']; ?></div>
                    <div class="stat-label">Upcoming Reservations</div>
                </div>
                
                <div class="stat-card completed">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-value" id="completedCount"><?php echo $stats['completed_this_month']; ?></div>
                    <div class="stat-label">Completed This Month</div>
                </div>
                
                <div class="stat-card cancelled">
                    <i class="fas fa-times-circle"></i>
                    <div class="stat-value" id="cancelledCount"><?php echo $stats['cancelled_this_month']; ?></div>
                    <div class="stat-label">Cancelled This Month</div>
                </div>
            </div>
            
            <!-- Reservations Management Section -->
            <div class="reservations-management">
                <div class="reservations-header">
                    <h3>All Reservations</h3>
                    <button class="add-reservation-btn" id="addReservationBtn">
                        <i class="fas fa-plus"></i>
                        Add New Reservation
                    </button>
                </div>
                
                <div class="reservations-tabs">
                    <button class="reservations-tab active" data-tab="all">All Reservations</button>
                    <button class="reservations-tab" data-tab="today">Today (<?php echo count($todayReservations); ?>)</button>
                    <button class="reservations-tab" data-tab="upcoming">Upcoming (<?php echo count($upcomingReservations); ?>)</button>
                    <button class="reservations-tab" data-tab="pending">Pending (<?php echo count($pendingReservations); ?>)</button>
                </div>
                
                <!-- All Reservations -->
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
                                <?php if (empty($reservations)): ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-times"></i>
                                            <h4>No reservations found</h4>
                                            <p><?php echo empty($searchTerm) ? 'No reservations in the system yet.' : 'No reservations match your search.'; ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-avatar">
                                                <?php 
                                                $initials = '';
                                                $names = explode(' ', $reservation['customer_name']);
                                                foreach ($names as $name) {
                                                    if (!empty($name)) {
                                                        $initials .= strtoupper(substr($name, 0, 1));
                                                    }
                                                }
                                                echo substr($initials, 0, 2);
                                                ?>
                                            </div>
                                            <div class="customer-details">
                                                <h4><?php echo htmlspecialchars($reservation['customer_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($reservation['customer_email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="reservation-date">
                                            <?php echo date('D, M d, Y', strtotime($reservation['reservation_date'])); ?>
                                        </div>
                                        <div class="reservation-time">
                                            <?php 
                                            $time = $reservation['reservation_time'];
                                            // If time is stored as 'HH:MM:SS', format it nicely
                                            if (strlen($time) > 5) {
                                                echo date('g:i A', strtotime($time));
                                            } else {
                                                echo $time;
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="reservation-party">
                                        <span class="party-size"><?php echo $reservation['party_size']; ?></span>
                                    </td>
                                    <td class="reservation-purpose">
                                        <span class="purpose-badge purpose-<?php echo strtolower(str_replace(' ', '-', $reservation['purpose'])); ?>">
                                            <?php echo htmlspecialchars($reservation['purpose']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($reservation['customer_phone']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="?edit=<?php echo $reservation['id']; ?>" class="table-action-btn edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $reservation['id']; ?>" 
                                               class="table-action-btn delete"
                                               onclick="return confirm('Are you sure you want to delete this reservation?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php if ($reservation['status'] == 'pending'): ?>
                                            <a href="?update_status&id=<?php echo $reservation['id']; ?>&status=confirmed" 
                                               class="table-action-btn confirm"
                                               onclick="return confirm('Confirm this reservation?');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                                <?php if (empty($todayReservations)): ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-times"></i>
                                            <h4>No reservations today</h4>
                                            <p>No reservations scheduled for today.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($todayReservations as $reservation): ?>
                                <tr>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-avatar">
                                                <?php 
                                                $initials = '';
                                                $names = explode(' ', $reservation['customer_name']);
                                                foreach ($names as $name) {
                                                    if (!empty($name)) {
                                                        $initials .= strtoupper(substr($name, 0, 1));
                                                    }
                                                }
                                                echo substr($initials, 0, 2);
                                                ?>
                                            </div>
                                            <div class="customer-details">
                                                <h4><?php echo htmlspecialchars($reservation['customer_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($reservation['customer_email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="reservation-time">
                                            <?php 
                                            $time = $reservation['reservation_time'];
                                            if (strlen($time) > 5) {
                                                echo date('g:i A', strtotime($time));
                                            } else {
                                                echo $time;
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="reservation-party">
                                        <span class="party-size"><?php echo $reservation['party_size']; ?></span>
                                    </td>
                                    <td class="reservation-purpose">
                                        <span class="purpose-badge purpose-<?php echo strtolower(str_replace(' ', '-', $reservation['purpose'])); ?>">
                                            <?php echo htmlspecialchars($reservation['purpose']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($reservation['customer_phone']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="?edit=<?php echo $reservation['id']; ?>" class="table-action-btn edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $reservation['id']; ?>" 
                                               class="table-action-btn delete"
                                               onclick="return confirm('Are you sure you want to delete this reservation?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php if ($reservation['status'] == 'pending'): ?>
                                            <a href="?update_status&id=<?php echo $reservation['id']; ?>&status=confirmed" 
                                               class="table-action-btn confirm"
                                               onclick="return confirm('Confirm this reservation?');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                                <?php if (empty($upcomingReservations)): ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-times"></i>
                                            <h4>No upcoming reservations</h4>
                                            <p>No upcoming reservations found.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($upcomingReservations as $reservation): ?>
                                <tr>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-avatar">
                                                <?php 
                                                $initials = '';
                                                $names = explode(' ', $reservation['customer_name']);
                                                foreach ($names as $name) {
                                                    if (!empty($name)) {
                                                        $initials .= strtoupper(substr($name, 0, 1));
                                                    }
                                                }
                                                echo substr($initials, 0, 2);
                                                ?>
                                            </div>
                                            <div class="customer-details">
                                                <h4><?php echo htmlspecialchars($reservation['customer_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($reservation['customer_email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="reservation-date">
                                            <?php echo date('D, M d, Y', strtotime($reservation['reservation_date'])); ?>
                                        </div>
                                        <div class="reservation-time">
                                            <?php 
                                            $time = $reservation['reservation_time'];
                                            if (strlen($time) > 5) {
                                                echo date('g:i A', strtotime($time));
                                            } else {
                                                echo $time;
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="reservation-party">
                                        <span class="party-size"><?php echo $reservation['party_size']; ?></span>
                                    </td>
                                    <td class="reservation-purpose">
                                        <span class="purpose-badge purpose-<?php echo strtolower(str_replace(' ', '-', $reservation['purpose'])); ?>">
                                            <?php echo htmlspecialchars($reservation['purpose']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($reservation['customer_phone']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="?edit=<?php echo $reservation['id']; ?>" class="table-action-btn edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $reservation['id']; ?>" 
                                               class="table-action-btn delete"
                                               onclick="return confirm('Are you sure you want to delete this reservation?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php if ($reservation['status'] == 'pending'): ?>
                                            <a href="?update_status&id=<?php echo $reservation['id']; ?>&status=confirmed" 
                                               class="table-action-btn confirm"
                                               onclick="return confirm('Confirm this reservation?');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                                <?php if (empty($pendingReservations)): ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-times"></i>
                                            <h4>No pending reservations</h4>
                                            <p>All reservations are confirmed!</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($pendingReservations as $reservation): ?>
                                <tr>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-avatar">
                                                <?php 
                                                $initials = '';
                                                $names = explode(' ', $reservation['customer_name']);
                                                foreach ($names as $name) {
                                                    if (!empty($name)) {
                                                        $initials .= strtoupper(substr($name, 0, 1));
                                                    }
                                                }
                                                echo substr($initials, 0, 2);
                                                ?>
                                            </div>
                                            <div class="customer-details">
                                                <h4><?php echo htmlspecialchars($reservation['customer_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($reservation['customer_email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="reservation-date">
                                            <?php echo date('D, M d, Y', strtotime($reservation['reservation_date'])); ?>
                                        </div>
                                        <div class="reservation-time">
                                            <?php 
                                            $time = $reservation['reservation_time'];
                                            if (strlen($time) > 5) {
                                                echo date('g:i A', strtotime($time));
                                            } else {
                                                echo $time;
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="reservation-party">
                                        <span class="party-size"><?php echo $reservation['party_size']; ?></span>
                                    </td>
                                    <td class="reservation-purpose">
                                        <span class="purpose-badge purpose-<?php echo strtolower(str_replace(' ', '-', $reservation['purpose'])); ?>">
                                            <?php echo htmlspecialchars($reservation['purpose']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($reservation['customer_phone']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="?edit=<?php echo $reservation['id']; ?>" class="table-action-btn edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $reservation['id']; ?>" 
                                               class="table-action-btn delete"
                                               onclick="return confirm('Are you sure you want to delete this reservation?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="?update_status&id=<?php echo $reservation['id']; ?>&status=confirmed" 
                                               class="table-action-btn confirm"
                                               onclick="return confirm('Confirm this reservation?');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Reservation Modal -->
    <div class="modal" id="reservationModal" <?php if ($editReservation): ?>style="display: flex;"<?php endif; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle"><?php echo $editReservation ? 'Edit Reservation' : 'Add New Reservation'; ?></h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="reservationForm">
                    <?php if ($editReservation): ?>
                    <input type="hidden" name="reservation_id" value="<?php echo $editReservation['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customerName">Customer Name *</label>
                            <input type="text" id="customerName" name="customer_name" class="form-control" 
                                   value="<?php echo $editReservation ? htmlspecialchars($editReservation['customer_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="customerPhone">Phone Number *</label>
                            <input type="tel" id="customerPhone" name="customer_phone" class="form-control" 
                                   value="<?php echo $editReservation ? htmlspecialchars($editReservation['customer_phone']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="customerEmail">Email Address</label>
                        <input type="email" id="customerEmail" name="customer_email" class="form-control" 
                               value="<?php echo $editReservation ? htmlspecialchars($editReservation['customer_email']) : ''; ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reservationDate">Reservation Date *</label>
                            <input type="date" id="reservationDate" name="reservation_date" class="form-control" 
                                   value="<?php echo $editReservation ? $editReservation['reservation_date'] : date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="reservationTime">Reservation Time *</label>
                            <select id="reservationTime" name="reservation_time" class="form-control" required>
                                <option value="">Select Time</option>
                                <?php
                                $timeSlots = [
                                    '11:00' => '11:00 AM',
                                    '12:00' => '12:00 PM',
                                    '13:00' => '1:00 PM',
                                    '14:00' => '2:00 PM',
                                    '18:00' => '6:00 PM',
                                    '19:00' => '7:00 PM',
                                    '20:00' => '8:00 PM',
                                    '21:00' => '9:00 PM'
                                ];
                                
                                foreach ($timeSlots as $value => $label):
                                    $selected = '';
                                    if ($editReservation) {
                                        $resTime = $editReservation['reservation_time'];
                                        // Handle both HH:MM and HH:MM:SS formats
                                        if (strlen($resTime) > 5) {
                                            $resTime = substr($resTime, 0, 5);
                                        }
                                        $selected = ($resTime == $value) ? 'selected' : '';
                                    }
                                ?>
                                <option value="<?php echo $value; ?>" <?php echo $selected; ?>>
                                    <?php echo $label; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="partySize">Party Size *</label>
                            <select id="partySize" name="party_size" class="form-control" required>
                                <option value="">Select Size</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <?php $selected = ($editReservation && $editReservation['party_size'] == $i) ? 'selected' : ''; ?>
                                <option value="<?php echo $i; ?>" <?php echo $selected; ?>>
                                    <?php echo $i . ($i == 1 ? ' person' : ' people'); ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="reservationPurpose">Reservation For *</label>
                            <select id="reservationPurpose" name="purpose" class="form-control" required>
                                <option value="">Select Purpose</option>
                                <option value="Dining In" <?php if ($editReservation && $editReservation['purpose'] == 'Dining In') echo 'selected'; ?>>Dining In</option>
                                <option value="Special Event" <?php if ($editReservation && $editReservation['purpose'] == 'Special Event') echo 'selected'; ?>>Special Event</option>
                                <option value="Catering" <?php if ($editReservation && $editReservation['purpose'] == 'Catering') echo 'selected'; ?>>Catering</option>
                                <option value="Takeaway" <?php if ($editReservation && $editReservation['purpose'] == 'Takeaway') echo 'selected'; ?>>Takeaway</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reservationStatus">Status</label>
                        <select id="reservationStatus" name="status" class="form-control" required>
                            <option value="pending" <?php if ($editReservation && $editReservation['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="confirmed" <?php if ($editReservation && $editReservation['status'] == 'confirmed') echo 'selected'; ?>>Confirmed</option>
                            <option value="completed" <?php if ($editReservation && $editReservation['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                            <option value="cancelled" <?php if ($editReservation && $editReservation['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="specialRequests">Special Requests</label>
                        <textarea id="specialRequests" name="special_requests" class="form-control" rows="3" 
                                  placeholder="Any special requests or notes..."><?php echo $editReservation ? htmlspecialchars($editReservation['special_requests']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="admin-reservation.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        const reservationsTabs = document.querySelectorAll('.reservations-tab');
        const reservationsContents = document.querySelectorAll('.reservations-content');
        
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
        const addReservationBtn = document.getElementById('addReservationBtn');
        const reservationModal = document.getElementById('reservationModal');
        const closeModal = document.getElementById('closeModal');
        
        if (addReservationBtn) {
            addReservationBtn.addEventListener('click', () => {
                document.getElementById('modalTitle').textContent = 'Add New Reservation';
                document.getElementById('reservationForm').reset();
                
                // Set default date to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('reservationDate').value = today;
                
                // Remove reservation_id if exists
                const reservationIdInput = document.querySelector('input[name="reservation_id"]');
                if (reservationIdInput) {
                    reservationIdInput.remove();
                }
                
                reservationModal.style.display = 'flex';
            });
        }

        if (closeModal) {
            closeModal.addEventListener('click', () => {
                reservationModal.style.display = 'none';
                window.location.href = 'admin-reservation.php';
            });
        }

        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === reservationModal) {
                reservationModal.style.display = 'none';
                window.location.href = 'admin-reservation.php';
            }
        });

        // Auto-close message after 5 seconds
        setTimeout(() => {
            const message = document.querySelector('.message-notification');
            if (message) {
                message.style.display = 'none';
            }
        }, 5000);

        // Set minimum date to today for reservation date
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('reservationDate');
        if (dateInput && !dateInput.value) {
            dateInput.min = today;
        }

        // Add search form submission on Enter key
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.form.submit();
                }
            });
        }
    </script>
</body>
</html>