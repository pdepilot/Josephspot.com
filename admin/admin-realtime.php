<?php
// admin-realtime.php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'joseph_pot_admin');

// Get database connection
function getDBConnection()
{
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
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Get new reviews
function getNewReviews($last_check)
{
    $conn = getDBConnection();
    $reviews = [];
    
    $stmt = $conn->prepare("
        SELECT r.*, 
               DATE_FORMAT(r.created_at, '%b %d, %Y %h:%i %p') as formatted_date,
               TIMESTAMPDIFF(MINUTE, r.created_at, NOW()) as minutes_ago
        FROM reviews r 
        WHERE r.created_at > ? 
        AND r.status = 'pending'
        ORDER BY r.created_at DESC 
        LIMIT 10
    ");
    
    if ($stmt) {
        $stmt->bind_param("s", $last_check);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $time_ago = '';
            if ($row['minutes_ago'] < 1) {
                $time_ago = 'Just now';
            } elseif ($row['minutes_ago'] < 60) {
                $time_ago = $row['minutes_ago'] . ' min ago';
            } elseif ($row['minutes_ago'] < 1440) {
                $hours = floor($row['minutes_ago'] / 60);
                $time_ago = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
            } else {
                $days = floor($row['minutes_ago'] / 1440);
                $time_ago = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
            }
            
            $row['time_ago'] = $time_ago;
            $reviews[] = $row;
        }
    }
    
    return $reviews;
}

// Get unread notifications count
function getUnreadNotificationsCount($admin_id)
{
    $conn = getDBConnection();
    $count = 0;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE (admin_id = ? OR admin_id IS NULL) 
        AND is_read = FALSE
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'];
    }
    
    return $count;
}

// Get recent activities
function getRecentActivities($limit = 10)
{
    $conn = getDBConnection();
    $activities = [];
    
    $query = "
        (SELECT 'review' as type, customer_name as title, 
                CONCAT('Left a ', rating, '-star review') as description, 
                created_at, id
         FROM reviews 
         WHERE status = 'pending')
        UNION
        (SELECT 'order' as type, CONCAT('Order #', order_id) as title, 
                CONCAT(customer_name, ' - ₦', total_amount) as description, 
                created_at, id
         FROM orders 
         WHERE status = 'pending' 
         AND DATE(created_at) = CURDATE())
        UNION
        (SELECT 'reservation' as type, CONCAT('Table #', table_number) as title, 
                CONCAT(name, ' for ', guests, ' guests') as description, 
                created_at, id
         FROM reservations 
         WHERE DATE(reservation_date) = CURDATE() 
         AND status = 'pending')
        ORDER BY created_at DESC 
        LIMIT ?
    ";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $time_ago = '';
            $now = time();
            $activity_time = strtotime($row['created_at']);
            $time_diff = $now - $activity_time;
            
            if ($time_diff < 60) {
                $time_ago = 'Just now';
            } elseif ($time_diff < 3600) {
                $minutes = floor($time_diff / 60);
                $time_ago = $minutes . ' min ago';
            } elseif ($time_diff < 86400) {
                $hours = floor($time_diff / 3600);
                $time_ago = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
            } else {
                $days = floor($time_diff / 86400);
                $time_ago = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
            }
            
            $row['time_ago'] = $time_ago;
            $activities[] = $row;
        }
    }
    
    return $activities;
}

// Mark notification as read
function markNotificationAsRead($notification_id, $admin_id)
{
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = TRUE 
        WHERE id = ? AND admin_id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("ii", $notification_id, $admin_id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    
    return false;
}

// Add notification
function addNotification($admin_id, $type, $title, $message, $reference_id = null)
{
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        INSERT INTO notifications (admin_id, type, title, message, reference_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if ($stmt) {
        $stmt->bind_param("isssi", $admin_id, $type, $title, $message, $reference_id);
        return $stmt->execute();
    }
    
    return false;
}

// Main request handler
$response = ['success' => false, 'message' => 'Invalid request'];

if (!isLoggedIn()) {
    $response['message'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_updates':
        $last_check = $_POST['last_check'] ?? date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $admin_id = $_SESSION['admin_id'];
        
        $response = [
            'success' => true,
            'data' => [
                'reviews' => getNewReviews($last_check),
                'notifications_count' => getUnreadNotificationsCount($admin_id),
                'activities' => getRecentActivities(5),
                'server_time' => date('Y-m-d H:i:s')
            ]
        ];
        break;
        
    case 'mark_read':
        $notification_id = $_POST['notification_id'] ?? 0;
        $admin_id = $_SESSION['admin_id'];
        
        if ($notification_id > 0 && markNotificationAsRead($notification_id, $admin_id)) {
            $response = ['success' => true, 'message' => 'Notification marked as read'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to mark as read'];
        }
        break;
        
    case 'get_notifications':
        $admin_id = $_SESSION['admin_id'];
        $limit = $_GET['limit'] ?? 10;
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("
            SELECT n.*, DATE_FORMAT(n.created_at, '%b %d, %Y %h:%i %p') as formatted_date
            FROM notifications n 
            WHERE (n.admin_id = ? OR n.admin_id IS NULL)
            ORDER BY n.created_at DESC 
            LIMIT ?
        ");
        
        if ($stmt) {
            $stmt->bind_param("ii", $admin_id, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $notifications = [];
            
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            
            $response = ['success' => true, 'notifications' => $notifications];
        }
        break;
        
    case 'update_review_status':
        $review_id = $_POST['review_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $admin_id = $_SESSION['admin_id'];
        
        if ($review_id > 0 && in_array($status, ['approved', 'rejected'])) {
            $conn = getDBConnection();
            $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id = ?");
            
            if ($stmt) {
                $stmt->bind_param("si", $status, $review_id);
                if ($stmt->execute()) {
                    // Add notification about review update
                    addNotification($admin_id, 'review', 'Review Updated', 
                        "Review #$review_id has been $status", $review_id);
                    
                    $response = ['success' => true, 'message' => "Review $status successfully"];
                } else {
                    $response = ['success' => false, 'message' => 'Database error'];
                }
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid parameters'];
        }
        break;
        
    default:
        $response = ['success' => false, 'message' => 'Invalid action'];
}

echo json_encode($response);
?>