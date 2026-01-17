<?php
// Central authentication and permission check
require_once 'admin-auth.php';
checkPageAccess(); // This checks authentication and permission for current page

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'joseph_pot_admin';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create reviews table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS reviews (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    rating INT(1) NOT NULL DEFAULT 5,
    review_text TEXT NOT NULL,
    image_url VARCHAR(500),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    order_id VARCHAR(50),
    menu_items VARCHAR(500),
    admin_reply TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    INDEX status_idx (status),
    INDEX rating_idx (rating),
    INDEX created_at_idx (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($create_table_sql)) {
    die("Error creating reviews table: " . $conn->error);
}

// Create admin_review_actions table if it doesn't exist
$create_actions_table_sql = "CREATE TABLE IF NOT EXISTS admin_review_actions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    review_id INT(11) NOT NULL,
    admin_id INT(11) NOT NULL,
    action ENUM('approve', 'reject', 'reply', 'delete') NOT NULL,
    action_details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX review_id_idx (review_id),
    INDEX admin_id_idx (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($create_actions_table_sql)) {
    die("Error creating admin_review_actions table: " . $conn->error);
}

// Create review_statistics table if it doesn't exist
$create_stats_table_sql = "CREATE TABLE IF NOT EXISTS review_statistics (
    id INT(11) NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    total_reviews INT(11) NOT NULL DEFAULT 0,
    avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    pending_count INT(11) NOT NULL DEFAULT 0,
    approved_count INT(11) NOT NULL DEFAULT 0,
    rejected_count INT(11) NOT NULL DEFAULT 0,
    positive_count INT(11) NOT NULL DEFAULT 0,
    negative_count INT(11) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY date_unique (date),
    INDEX date_idx (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($create_stats_table_sql)) {
    die("Error creating review_statistics table: " . $conn->error);
}

// Function to update review statistics
function updateReviewStatistics($conn) {
    $today = date('Y-m-d');
    
    // Calculate current statistics from reviews table
    $stats_sql = "SELECT 
        COUNT(*) as total_reviews,
        COALESCE(AVG(rating), 0) as avg_rating,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
        SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_count,
        SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative_count
        FROM reviews";

    $stats_result = $conn->query($stats_sql);
    $stats = $stats_result->fetch_assoc();
    
    // Check if statistics for today already exist
    $check_sql = "SELECT id FROM review_statistics WHERE date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $today);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();
    
    if ($check_result->num_rows > 0) {
        // Update existing record
        $update_sql = "UPDATE review_statistics SET 
            total_reviews = ?,
            avg_rating = ?,
            pending_count = ?,
            approved_count = ?,
            rejected_count = ?,
            positive_count = ?,
            negative_count = ?
            WHERE date = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param(
            "idiiiiis",
            $stats['total_reviews'],
            $stats['avg_rating'],
            $stats['pending_count'],
            $stats['approved_count'],
            $stats['rejected_count'],
            $stats['positive_count'],
            $stats['negative_count'],
            $today
        );
        
        if (!$update_stmt->execute()) {
            error_log("Error updating review statistics: " . $update_stmt->error);
        }
        $update_stmt->close();
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO review_statistics 
            (date, total_reviews, avg_rating, pending_count, approved_count, rejected_count, positive_count, negative_count) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param(
            "sidiiiii",
            $today,
            $stats['total_reviews'],
            $stats['avg_rating'],
            $stats['pending_count'],
            $stats['approved_count'],
            $stats['rejected_count'],
            $stats['positive_count'],
            $stats['negative_count']
        );
        
        if (!$insert_stmt->execute()) {
            error_log("Error inserting review statistics: " . $insert_stmt->error);
        }
        $insert_stmt->close();
    }
    
    return $stats;
}

// Ensure uploads directory exists
$uploads_dir = '../uploads/reviews/';
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $review_id = intval($_POST['review_id'] ?? 0);
    
    switch ($action) {
        case 'approve':
            $sql = "UPDATE reviews SET status = 'approved', approved_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $review_id);
            if ($stmt->execute()) {
                // Log admin action
                $admin_id = $_SESSION['admin_id'] ?? 0;
                $log_sql = "INSERT INTO admin_review_actions (review_id, admin_id, action, action_details) VALUES (?, ?, 'approve', 'Review approved')";
                $log_stmt = $conn->prepare($log_sql);
                $log_stmt->bind_param("ii", $review_id, $admin_id);
                $log_stmt->execute();
                $log_stmt->close();
                
                // Update statistics
                updateReviewStatistics($conn);
                
                $_SESSION['success_message'] = "Review approved successfully!";
            } else {
                $_SESSION['error_message'] = "Error approving review: " . $conn->error;
            }
            $stmt->close();
            break;
            
        case 'reject':
            $sql = "UPDATE reviews SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $review_id);
            if ($stmt->execute()) {
                // Log admin action
                $admin_id = $_SESSION['admin_id'] ?? 0;
                $log_sql = "INSERT INTO admin_review_actions (review_id, admin_id, action, action_details) VALUES (?, ?, 'reject', 'Review rejected')";
                $log_stmt = $conn->prepare($log_sql);
                $log_stmt->bind_param("ii", $review_id, $admin_id);
                $log_stmt->execute();
                $log_stmt->close();
                
                // Update statistics
                updateReviewStatistics($conn);
                
                $_SESSION['success_message'] = "Review rejected successfully!";
            } else {
                $_SESSION['error_message'] = "Error rejecting review: " . $conn->error;
            }
            $stmt->close();
            break;
            
        case 'reply':
            $reply = $conn->real_escape_string($_POST['reply'] ?? '');
            $sql = "UPDATE reviews SET admin_reply = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $reply, $review_id);
            if ($stmt->execute()) {
                // Log admin action
                $admin_id = $_SESSION['admin_id'] ?? 0;
                $log_sql = "INSERT INTO admin_review_actions (review_id, admin_id, action, action_details) VALUES (?, ?, 'reply', 'Admin replied to review')";
                $log_stmt = $conn->prepare($log_sql);
                $log_stmt->bind_param("ii", $review_id, $admin_id);
                $log_stmt->execute();
                $log_stmt->close();
                
                // Update statistics
                updateReviewStatistics($conn);
                
                $_SESSION['success_message'] = "Reply saved successfully!";
            } else {
                $_SESSION['error_message'] = "Error saving reply: " . $conn->error;
            }
            $stmt->close();
            break;
            
        case 'delete':
            // Start transaction for atomic operations
            $conn->begin_transaction();
            
            try {
                $admin_id = $_SESSION['admin_id'] ?? 0;
                
                // 1. Log the delete action first (while review still exists)
                $log_sql = "INSERT INTO admin_review_actions (review_id, admin_id, action, action_details) VALUES (?, ?, 'delete', 'Review deleted')";
                $log_stmt = $conn->prepare($log_sql);
                $log_stmt->bind_param("ii", $review_id, $admin_id);
                $log_stmt->execute();
                $log_stmt->close();
                
                // 2. Get image path before deleting
                $select_sql = "SELECT image_url FROM reviews WHERE id = ?";
                $select_stmt = $conn->prepare($select_sql);
                $select_stmt->bind_param("i", $review_id);
                $select_stmt->execute();
                $result = $select_stmt->get_result();
                $image_path = null;
                if ($row = $result->fetch_assoc()) {
                    $image_path = $row['image_url'];
                }
                $select_stmt->close();
                
                // 3. Delete the review (cascade will handle related actions)
                $delete_sql = "DELETE FROM reviews WHERE id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $review_id);
                $delete_stmt->execute();
                $delete_stmt->close();
                
                // 4. Delete image file if exists
                if (!empty($image_path) && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
                
                // 5. Update statistics
                updateReviewStatistics($conn);
                
                // Commit transaction
                $conn->commit();
                
                $_SESSION['success_message'] = "Review deleted successfully!";
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $_SESSION['error_message'] = "Error deleting review: " . $e->getMessage();
                error_log("Delete review error: " . $e->getMessage());
            }
            break;
            
        case 'bulk_action':
            $bulk_action = $_POST['bulk_action'] ?? '';
            $review_ids = $_POST['review_ids'] ?? [];
            
            if (!empty($review_ids) && $bulk_action) {
                $ids = implode(',', array_map('intval', $review_ids));
                $admin_id = $_SESSION['admin_id'] ?? 0;
                
                switch ($bulk_action) {
                    case 'approve_selected':
                        $sql = "UPDATE reviews SET status = 'approved', approved_at = NOW() WHERE id IN ($ids)";
                        $action_type = 'approve';
                        $action_details = 'Bulk approval';
                        break;
                    case 'reject_selected':
                        $sql = "UPDATE reviews SET status = 'rejected' WHERE id IN ($ids)";
                        $action_type = 'reject';
                        $action_details = 'Bulk rejection';
                        break;
                    case 'delete_selected':
                        // Get images first
                        $sql_select = "SELECT image_url FROM reviews WHERE id IN ($ids)";
                        $result = $conn->query($sql_select);
                        $image_paths = [];
                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['image_url'])) {
                                $image_paths[] = $row['image_url'];
                            }
                        }
                        
                        // Start transaction for bulk delete
                        $conn->begin_transaction();
                        
                        try {
                            // 1. Log bulk actions for each review
                            foreach ($review_ids as $rid) {
                                $log_sql = "INSERT INTO admin_review_actions (review_id, admin_id, action, action_details) VALUES (?, ?, 'delete', 'Bulk deletion')";
                                $log_stmt = $conn->prepare($log_sql);
                                $log_stmt->bind_param("ii", $rid, $admin_id);
                                $log_stmt->execute();
                                $log_stmt->close();
                            }
                            
                            // 2. Delete the reviews (cascade will handle related actions)
                            $sql = "DELETE FROM reviews WHERE id IN ($ids)";
                            if ($conn->query($sql)) {
                                // 3. Delete image files
                                foreach ($image_paths as $image_path) {
                                    if (!empty($image_path) && file_exists('../' . $image_path)) {
                                        unlink('../' . $image_path);
                                    }
                                }
                                
                                // Commit transaction
                                $conn->commit();
                                
                                // 4. Update statistics
                                updateReviewStatistics($conn);
                                
                                $_SESSION['success_message'] = "Bulk action completed successfully!";
                            } else {
                                throw new Exception("Error deleting reviews: " . $conn->error);
                            }
                            
                        } catch (Exception $e) {
                            // Rollback transaction on error
                            $conn->rollback();
                            $_SESSION['error_message'] = "Error performing bulk action: " . $e->getMessage();
                            error_log("Bulk delete error: " . $e->getMessage());
                        }
                        $action_type = 'delete';
                        $action_details = 'Bulk deletion';
                        break;
                }
                
                // Handle non-delete bulk actions (approve/reject)
                if ($bulk_action !== 'delete_selected') {
                    if ($conn->query($sql)) {
                        // Log bulk action for each review
                        foreach ($review_ids as $rid) {
                            $log_sql = "INSERT INTO admin_review_actions (review_id, admin_id, action, action_details) VALUES (?, ?, ?, ?)";
                            $log_stmt = $conn->prepare($log_sql);
                            $log_stmt->bind_param("iiss", $rid, $admin_id, $action_type, $action_details);
                            $log_stmt->execute();
                            $log_stmt->close();
                        }
                        
                        // Update statistics
                        updateReviewStatistics($conn);
                        
                        $_SESSION['success_message'] = "Bulk action completed successfully!";
                    } else {
                        $_SESSION['error_message'] = "Error performing bulk action: " . $conn->error;
                    }
                }
            }
            break;
    }
    
    // Redirect to prevent form resubmission
    header("Location: admin-reviews.php");
    exit();
}

// Fetch reviews with pagination and filtering
$current_filter = $_GET['filter'] ?? 'all';
$search_query = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where_conditions = [];
$params = [];
$param_types = '';

if ($current_filter !== 'all') {
    switch ($current_filter) {
        case 'approved':
            $where_conditions[] = "r.status = 'approved'";
            break;
        case 'pending':
            $where_conditions[] = "r.status = 'pending'";
            break;
        case 'positive':
            $where_conditions[] = "r.rating >= 4";
            break;
        case 'negative':
            $where_conditions[] = "r.rating <= 2";
            break;
    }
}

if (!empty($search_query)) {
    $where_conditions[] = "(r.name LIKE ? OR r.email LIKE ? OR r.review_text LIKE ? OR r.order_id LIKE ?)";
    $search_param = "%$search_query%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $param_types .= 'ssss';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM reviews r $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_reviews = $count_result->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_reviews / $per_page);
$count_stmt->close();

// Fetch reviews
$reviews_sql = "SELECT r.*, 
                DATE_FORMAT(r.created_at, '%W, %M %e, %Y') as formatted_date,
                TIMESTAMPDIFF(DAY, r.created_at, NOW()) as days_ago
                FROM reviews r 
                $where_clause 
                ORDER BY r.created_at DESC 
                LIMIT ? OFFSET ?";
$reviews_stmt = $conn->prepare($reviews_sql);
$params[] = $per_page;
$params[] = $offset;
$param_types .= 'ii';

if (!empty($param_types)) {
    $reviews_stmt->bind_param($param_types, ...$params);
}
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    // Generate avatar initials
    $initials = '';
    $name_parts = explode(' ', $row['name']);
    foreach ($name_parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
        if (strlen($initials) >= 2) break;
    }
    if (empty($initials)) {
        $initials = strtoupper(substr($row['name'], 0, 2));
    }
    
    // Determine avatar color based on name
    $colors = ['#2196F3', '#FF9800', '#4CAF50', '#9C27B0', '#F44336', '#8b4513', '#607D8B', '#795548'];
    $color_index = crc32($row['name']) % count($colors);
    $avatar_color = $colors[$color_index];
    
    // Format timestamp
    $timestamp = '';
    if ($row['days_ago'] == 0) {
        $timestamp = 'Today, ' . date('g:i A', strtotime($row['created_at']));
    } elseif ($row['days_ago'] == 1) {
        $timestamp = 'Yesterday, ' . date('g:i A', strtotime($row['created_at']));
    } elseif ($row['days_ago'] < 7) {
        $timestamp = $row['days_ago'] . ' days ago';
    } else {
        $timestamp = date('M j, Y', strtotime($row['created_at']));
    }
    
    // Parse menu items
    $menu_items = [];
    if (!empty($row['menu_items'])) {
        $menu_items = explode(',', $row['menu_items']);
        $menu_items = array_map('trim', $menu_items);
    }
    
    // Handle image URL - FIXED HERE
    $image_url = $row['image_url'];
    if (!empty($image_url)) {
        // Add ../ prefix for admin dashboard access (admin folder is one level above uploads)
        $image_url = '../' . $image_url;
        
        // Check if file exists, if not use default
        if (!file_exists($image_url)) {
            $image_url = 'https://randomuser.me/api/portraits/neutral/default.jpg';
        }
    } else {
        $image_url = 'https://randomuser.me/api/portraits/neutral/default.jpg';
    }
    
    $reviews[] = [
        'id' => $row['id'],
        'customer' => [
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email'] ?? ''),
            'avatar' => $initials,
            'avatar_color' => $avatar_color
        ],
        'rating' => intval($row['rating']),
        'text' => htmlspecialchars($row['review_text']),
        'status' => $row['status'],
        'date' => $row['created_at'],
        'formatted_date' => $row['formatted_date'],
        'order_id' => $row['order_id'] ?? 'N/A',
        'menu_items' => $menu_items,
        'timestamp' => $timestamp,
        'admin_reply' => $row['admin_reply'] ?? '',
        'image_url' => $image_url
    ];
}
$reviews_stmt->close();

// Get statistics - always update to ensure accuracy
$stats = updateReviewStatistics($conn);

// Calculate changes from last month using review_statistics table
$last_month = date('Y-m-d', strtotime('-1 month'));
$changes_sql = "SELECT 
    total_reviews as last_month_total,
    avg_rating as last_month_avg,
    pending_count as last_month_pending,
    negative_count as last_month_negative
    FROM review_statistics 
    WHERE date >= ? 
    ORDER BY date ASC 
    LIMIT 1";

$changes_stmt = $conn->prepare($changes_sql);
$changes_stmt->bind_param("s", $last_month);
$changes_stmt->execute();
$changes_result = $changes_stmt->get_result();
$last_month_stats = $changes_result->fetch_assoc();
$changes_stmt->close();

// Calculate percentage changes
$total_change = '0%';
$rating_change = '0.0';
$pending_change = '0';
$negative_change = '0';

if ($last_month_stats && $last_month_stats['last_month_total'] > 0) {
    $total_change_percent = (($stats['total_reviews'] - $last_month_stats['last_month_total']) / $last_month_stats['last_month_total']) * 100;
    $total_change = ($total_change_percent >= 0 ? '+' : '') . round($total_change_percent) . '%';
    
    $rating_change_num = ($stats['avg_rating'] - $last_month_stats['last_month_avg']);
    $rating_change = ($rating_change_num >= 0 ? '+' : '') . round($rating_change_num, 1);
    
    $pending_change_num = $stats['pending_count'] - $last_month_stats['last_month_pending'];
    $pending_change = ($pending_change_num >= 0 ? '+' : '') . $pending_change_num;
    
    $negative_change_num = $stats['negative_count'] - $last_month_stats['last_month_negative'];
    $negative_change = ($negative_change_num >= 0 ? '+' : '') . $negative_change_num;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo3.png">
    <title>Reviews Management - Joseph's Pot</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Your existing CSS file -->
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

        /* Notification messages */
        .notification-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            box-shadow: var(--shadow);
            animation: slideInRight 0.3s ease, fadeOut 0.3s ease 3s forwards;
        }

        .notification-message.success {
            background: var(--success);
        }

        .notification-message.error {
            background: var(--danger);
        }

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

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(100%);
            }
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

        .sidebar-overlay.active {
            display: block;
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

        /* FIXED NOTIFICATION AND USER MENU STYLES */
        .notification-user-container {
            display: flex;
            align-items: center;
            gap: 8px;
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
            top: 50px;
            right: 0;
            width: 350px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .notification-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .notification-header {
            padding: 15px 20px;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h4 {
            font-size: 1rem;
            font-weight: 600;
        }

        .mark-all-read {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .mark-all-read:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .notification-item:hover {
            background: var(--gray);
        }

        .notification-item.unread {
            background: rgba(33, 150, 243, 0.05);
        }

        .notification-title {
            font-weight: 500;
            margin-bottom: 5px;
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

        /* User Dropdown Menu */
        .user-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            width: 200px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .user-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .user-dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text);
            text-decoration: none;
            transition: var(--transition);
        }

        .user-dropdown-item:hover {
            background: var(--gray);
        }

        .user-dropdown-item i {
            margin-right: 10px;
            color: var(--primary);
            width: 20px;
        }

        .user-dropdown-divider {
            height: 1px;
            background: var(--gray-dark);
            margin: 5px 0;
        }

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

        .stat-card.total::before {
            background: var(--info);
        }

        .stat-card.positive::before {
            background: var(--success);
        }

        .stat-card.pending::before {
            background: var(--warning);
        }

        .stat-card.negative::before {
            background: var(--danger);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stat-card.total i {
            color: var(--info);
        }

        .stat-card.positive i {
            color: var(--success);
        }

        .stat-card.pending i {
            color: var(--warning);
        }

        .stat-card.negative i {
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

        /* Review Filters */
        .review-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 30px;
            background: white;
            color: var(--text);
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
            font-weight: 500;
            min-width: 140px;
            text-align: center;
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
        }

        .filter-btn:hover:not(.active) {
            background: var(--gray);
            transform: translateY(-2px);
        }

        /* Bulk Actions */
        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-left: auto;
        }

        .bulk-select-all {
            padding: 10px 15px;
            border: 1px solid var(--primary);
            background: white;
            color: var(--primary);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .bulk-select-all:hover {
            background: var(--primary);
            color: white;
        }

        .bulk-action-select {
            padding: 10px 15px;
            border: 1px solid var(--gray-dark);
            border-radius: 6px;
            background: white;
            color: var(--text);
            font-weight: 500;
        }

        .bulk-action-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            background: var(--primary);
            color: white;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .bulk-action-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Reviews Container */
        .reviews-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .review-item {
            display: flex;
            padding: 20px 0;
            border-bottom: 1px solid var(--gray);
            transition: var(--transition);
            position: relative;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-item:hover {
            background: rgba(139, 69, 19, 0.03);
        }

        .review-checkbox {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            z-index: 1;
        }

        .review-content {
            flex: 1;
            min-width: 0;
            margin-left: 35px;
        }

        .review-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .reviewer-info h4 {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .reviewer-info p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .review-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }

        .rating-stars {
            display: flex;
            gap: 2px;
        }

        .rating-stars i {
            color: #ffc107;
            font-size: 0.9rem;
        }

        .rating-value {
            font-weight: 600;
            color: var(--primary);
        }

        .review-text {
            margin-bottom: 10px;
            line-height: 1.5;
            word-break: break-word;
        }

        .review-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .review-date {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .review-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .review-action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
        }

        .review-action-btn.view-details {
            background: var(--primary);
            color: white;
        }

        .review-action-btn.approve {
            background: var(--success);
            color: white;
        }

        .review-action-btn.reject {
            background: var(--danger);
            color: white;
        }

        .review-action-btn.reply {
            background: var(--info);
            color: white;
        }

        .review-action-btn.delete {
            background: var(--gray);
            color: var(--text);
        }

        .review-action-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .review-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            display: inline-block;
            white-space: nowrap;
        }

        .status-published {
            background: rgba(76, 175, 80, 0.2);
            color: var(--success);
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.2);
            color: var(--warning);
        }

        .status-rejected {
            background: rgba(244, 67, 54, 0.2);
            color: var(--danger);
        }

        /* Admin reply styling */
        .admin-reply {
            background: var(--gray);
            border-left: 4px solid var(--primary);
            padding: 10px 15px;
            margin-top: 15px;
            border-radius: 4px;
        }

        .admin-reply-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .admin-reply-header i {
            color: var(--primary);
        }

        .admin-reply-header span {
            font-weight: 600;
            color: var(--primary);
        }

        .admin-reply-text {
            color: var(--text);
            line-height: 1.5;
            word-break: break-word;
        }

        /* Mobile Card View */
        .review-mobile-view {
            display: none;
        }

        .review-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
            position: relative;
        }

        .review-card-checkbox {
            position: absolute;
            top: 15px;
            left: 15px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            z-index: 1;
        }

        .review-card-content {
            margin-left: 30px;
        }

        .review-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .review-card-user {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            width: 100%;
        }

        .review-card-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }

        .review-card-user-info {
            flex: 1;
            min-width: 0;
        }

        .review-card-user-info h4 {
            font-size: 1rem;
            margin-bottom: 3px;
        }

        .review-card-user-info p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .review-card-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .review-detail-item {
            display: flex;
            flex-direction: column;
        }

        .review-detail-label {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-bottom: 3px;
        }

        .review-detail-value {
            font-weight: 500;
            font-size: 0.9rem;
        }

        .review-text-mobile {
            font-size: 0.9rem;
            color: var(--text);
            line-height: 1.5;
            word-break: break-word;
        }

        .review-card-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid var(--gray);
            flex-wrap: wrap;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray);
        }

        .pagination-btn {
            padding: 8px 15px;
            border: 1px solid var(--gray-dark);
            background: white;
            color: var(--text);
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .pagination-btn:hover:not(:disabled) {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-dots {
            color: var(--text-light);
            padding: 0 5px;
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
            max-width: 700px;
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
            transition: var(--transition);
        }

        .close-modal:hover {
            color: var(--primary);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 0;
        }

        .review-details-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .review-details-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }

        .review-details-info {
            flex: 1;
            min-width: 0;
        }

        .review-details-info h4 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            color: var(--primary);
        }

        .review-details-info p {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 10px;
            word-break: break-all;
        }

        .review-details-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .review-details-text {
            background: var(--gray);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            line-height: 1.6;
            word-break: break-word;
            font-size: 1rem;
            border-left: 4px solid var(--primary);
        }

        .review-details-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .meta-item {
            background: var(--gray);
            padding: 15px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            border-left: 4px solid var(--info);
        }

        .meta-label {
            font-weight: 500;
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .meta-value {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text);
        }

        .meta-item.status {
            border-left-color: var(--warning);
        }

        .meta-item.menu-items {
            grid-column: span 2;
            border-left-color: var(--success);
        }

        .reply-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--gray);
        }

        .reply-section h4 {
            margin-bottom: 10px;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .reply-textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--gray-dark);
            border-radius: 8px;
            resize: vertical;
            min-height: 120px;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .reply-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--gray);
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
        }

        .btn-secondary {
            background: var(--gray);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: var(--gray-dark);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #3d8b40;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #d32f2f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
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

        /* Animation */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Loading State */
        .loading-state {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #8b4513;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-cards {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
            
            .review-filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .bulk-actions {
                margin-left: 0;
                margin-top: 10px;
                width: 100%;
            }
            
            .notification-dropdown {
                width: 300px;
                right: -50px;
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
            
            .notification-user-container {
                align-self: flex-end;
                margin-left: auto;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .filter-btn {
                min-width: 120px;
            }
            
            .review-item {
                flex-direction: column;
            }
            
            .review-avatar {
                margin-bottom: 10px;
            }
            
            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .review-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .review-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            .desktop-review-view {
                display: none;
            }
            
            .review-mobile-view {
                display: block;
            }
            
            .review-details-meta {
                grid-template-columns: 1fr;
            }
            
            .meta-item.menu-items {
                grid-column: span 1;
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
            
            .notification-dropdown {
                width: 280px;
                right: -70px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .reviews-container {
                padding: 20px 15px;
            }
            
            .review-filters {
                flex-direction: column;
            }
            
            .filter-btn {
                width: 100%;
            }
            
            .review-card-details {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                padding: 20px 15px;
            }
            
            .review-details-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .review-details-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .bulk-actions {
                flex-direction: column;
            }
            
            .bulk-select-all,
            .bulk-action-select,
            .bulk-action-btn {
                width: 100%;
                text-align: center;
            }
            
            .notification-dropdown {
                width: 250px;
                right: -100px;
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
            
            .review-action-btn {
                padding: 5px 10px;
                font-size: 0.75rem;
            }
            
            .review-card-actions {
                justify-content: center;
            }
            
            .pagination-btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .notification-dropdown {
                width: 220px;
                right: -120px;
            }
        }
    </style>
</head>
<body>
    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="notification-message success">
            <?php echo $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="notification-message error">
            <?php echo $_SESSION['error_message'];
            unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

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
                    <h3><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin Joseph'); ?></h3>
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
                    <a href="admin-reviews.php" class="active">
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
                <h2>Reviews Management</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search reviews..." 
                               value="<?php echo htmlspecialchars($search_query); ?>"
                               onkeydown="if(event.key === 'Enter') searchReviews()">
                        <button id="searchButton" style="display:none;"></button>
                    </div>
                    <div class="notification-user-container">
                        <!-- Notification Dropdown -->
                        <div class="notification-icon" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge"><?php echo $stats['pending_count'] ?? 0; ?></span>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <h4>Notifications</h4>
                                    <button class="mark-all-read" id="markAllRead">Mark all as read</button>
                                </div>
                                <div class="notification-list" id="notificationList">
                                    <!-- Notifications will be dynamically added here -->
                                    <div class="notification-empty" id="emptyNotifications">
                                        <i class="fas fa-bell-slash" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                        <p>No new notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Dropdown Menu -->
                        <div class="user-menu" id="userMenu">
                            <i class="fas fa-user-circle"></i>
                            <div class="user-dropdown" id="userDropdown">
                                <a href="#" class="user-dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="admin-settings.php" class="user-dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                                <div class="user-dropdown-divider"></div>
                                <a href="admin-logout.php" class="user-dropdown-item" onclick="return confirmLogout()">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card total reveal">
                    <i class="fas fa-star"></i>
                    <div class="stat-value" id="totalReviews"><?php echo $stats['total_reviews'] ?? 0; ?></div>
                    <div class="stat-label">Total Reviews</div>
                    <div class="stat-change <?php echo strpos($total_change, '+') === 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo strpos($total_change, '+') === 0 ? 'up' : 'down'; ?>"></i> 
                        <span id="totalChange"><?php echo $total_change; ?></span> from last month
                    </div>
                </div>
                
                <div class="stat-card positive reveal reveal-delay-1">
                    <i class="fas fa-thumbs-up"></i>
                    <div class="stat-value" id="avgRating"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></div>
                    <div class="stat-label">Average Rating</div>
                    <div class="stat-change <?php echo strpos($rating_change, '+') === 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo strpos($rating_change, '+') === 0 ? 'up' : 'down'; ?>"></i> 
                        <span id="ratingChange"><?php echo $rating_change; ?></span> from last month
                    </div>
                </div>
                
                <div class="stat-card pending reveal reveal-delay-2">
                    <i class="fas fa-clock"></i>
                    <div class="stat-value" id="pendingCount"><?php echo $stats['pending_count'] ?? 0; ?></div>
                    <div class="stat-label">Pending Reviews</div>
                    <div class="stat-change <?php echo strpos($pending_change, '+') === 0 ? 'negative' : 'positive'; ?>">
                        <i class="fas fa-arrow-<?php echo strpos($pending_change, '+') === 0 ? 'up' : 'down'; ?>"></i> 
                        <span id="pendingChange"><?php echo $pending_change; ?></span> from last month
                    </div>
                </div>
                
                <div class="stat-card negative reveal reveal-delay-3">
                    <i class="fas fa-thumbs-down"></i>
                    <div class="stat-value" id="negativeCount"><?php echo $stats['negative_count'] ?? 0; ?></div>
                    <div class="stat-label">Negative Reviews</div>
                    <div class="stat-change <?php echo strpos($negative_change, '+') === 0 ? 'negative' : 'positive'; ?>">
                        <i class="fas fa-arrow-<?php echo strpos($negative_change, '+') === 0 ? 'up' : 'down'; ?>"></i> 
                        <span id="negativeChange"><?php echo $negative_change; ?></span> from last month
                    </div>
                </div>
            </div>
            
            <!-- Review Filters and Bulk Actions -->
            <div class="review-filters">
                <button class="filter-btn <?php echo $current_filter === 'all' ? 'active' : ''; ?>" 
                        onclick="setFilter('all')">All Reviews</button>
                <button class="filter-btn <?php echo $current_filter === 'approved' ? 'active' : ''; ?>" 
                        onclick="setFilter('approved')">Published</button>
                <button class="filter-btn <?php echo $current_filter === 'pending' ? 'active' : ''; ?>" 
                        onclick="setFilter('pending')">Pending</button>
                <button class="filter-btn <?php echo $current_filter === 'positive' ? 'active' : ''; ?>" 
                        onclick="setFilter('positive')">Positive (4-5★)</button>
                <button class="filter-btn <?php echo $current_filter === 'negative' ? 'active' : ''; ?>" 
                        onclick="setFilter('negative')">Negative (1-2★)</button>
                
                <div class="bulk-actions">
                    <button class="bulk-select-all" onclick="toggleSelectAll()">Select All</button>
                    <select class="bulk-action-select" id="bulkActionSelect">
                        <option value="">Bulk Actions</option>
                        <option value="approve_selected">Approve Selected</option>
                        <option value="reject_selected">Reject Selected</option>
                        <option value="delete_selected">Delete Selected</option>
                    </select>
                    <button class="bulk-action-btn" onclick="performBulkAction()">Apply</button>
                </div>
            </div>
            
            <!-- Desktop Reviews List -->
            <div class="reviews-container desktop-review-view">
                <form id="bulkActionForm" method="POST">
                    <input type="hidden" name="action" value="bulk_action">
                    <input type="hidden" name="bulk_action" id="bulkActionValue" value="">
                    
                    <div id="reviewsList">
                        <?php if (empty($reviews)): ?>
                            <div class="loading-state">
                                <i class="fas fa-star" style="font-size: 3rem; color: var(--gray-dark); margin-bottom: 20px;"></i>
                                <h4>No reviews found</h4>
                                <p>No reviews match your current filter criteria.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <input type="checkbox" class="review-checkbox" name="review_ids[]" value="<?php echo $review['id']; ?>" 
                                           id="review_<?php echo $review['id']; ?>">
                                    
                                    <div class="review-content">
                                        <div class="review-avatar" style="background: <?php echo $review['customer']['avatar_color']; ?>">
                                            <?php echo $review['customer']['avatar']; ?>
                                        </div>
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <h4><?php echo $review['customer']['name']; ?></h4>
                                                <p><?php echo $review['customer']['email']; ?></p>
                                                <div class="review-rating">
                                                    <div class="rating-stars" id="ratingStars_<?php echo $review['id']; ?>">
                                                        <?php echo str_repeat('<i class="fas fa-star"></i>', $review['rating']); ?>
                                                        <?php echo str_repeat('<i class="far fa-star"></i>', 5 - $review['rating']); ?>
                                                    </div>
                                                    <span class="rating-value"><?php echo $review['rating']; ?>.0</span>
                                                    <span class="review-status status-<?php echo $review['status']; ?>">
                                                        <?php echo ucfirst($review['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php if (!empty($review['image_url']) && $review['image_url'] !== 'https://randomuser.me/api/portraits/neutral/default.jpg'): ?>
                                            <div class="review-image-preview">
                                                <img src="<?php echo $review['image_url']; ?>" alt="Review Image" 
                                                     style="width: 60px; height: 60px; border-radius: 5px; object-fit: cover; border: 2px solid var(--gray-dark);"
                                                     onclick="viewReviewImage('<?php echo $review['image_url']; ?>', '<?php echo $review['customer']['name']; ?>')">
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="review-text"><?php echo nl2br($review['text']); ?></div>
                                        
                                        <?php if (!empty($review['admin_reply'])): ?>
                                            <div class="admin-reply">
                                                <div class="admin-reply-header">
                                                    <i class="fas fa-user-tie"></i>
                                                    <span>Admin Reply</span>
                                                </div>
                                                <div class="admin-reply-text"><?php echo nl2br($review['admin_reply']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="review-meta">
                                            <div class="review-date">
                                                <?php echo $review['timestamp']; ?> • Order: <?php echo $review['order_id']; ?>
                                            </div>
                                            <div class="review-actions">
                                                <button type="button" class="review-action-btn view-details" 
                                                        onclick="viewReviewDetails(<?php echo $review['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                                <?php if ($review['status'] === 'pending'): ?>
                                                <button type="button" class="review-action-btn approve" 
                                                        onclick="approveReview(<?php echo $review['id']; ?>)">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button type="button" class="review-action-btn reject" 
                                                        onclick="rejectReview(<?php echo $review['id']; ?>)">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                                <?php endif; ?>
                                                <button type="button" class="review-action-btn reply" 
                                                        onclick="replyToReview(<?php echo $review['id']; ?>)">
                                                    <i class="fas fa-reply"></i> Reply
                                                </button>
                                                <button type="button" class="review-action-btn delete" 
                                                        onclick="deleteReview(<?php echo $review['id']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </form>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <button class="pagination-btn <?php echo $page == 1 ? 'active' : ''; ?>" 
                                onclick="goToPage(1)" <?php echo $page == 1 ? 'disabled' : ''; ?>>
                            <i class="fas fa-angle-double-left"></i>
                        </button>
                        <button class="pagination-btn <?php echo $page == 1 ? 'active' : ''; ?>" 
                                onclick="goToPage(<?php echo max(1, $page - 1); ?>)" <?php echo $page == 1 ? 'disabled' : ''; ?>>
                            <i class="fas fa-angle-left"></i>
                        </button>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <button class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>" 
                                    onclick="goToPage(<?php echo $i; ?>)">
                                <?php echo $i; ?>
                            </button>
                        <?php endfor; ?>
                        
                        <?php if ($page + 2 < $total_pages): ?>
                            <span class="pagination-dots">...</span>
                            <button class="pagination-btn" onclick="goToPage(<?php echo $total_pages; ?>)">
                                <?php echo $total_pages; ?>
                            </button>
                        <?php endif; ?>
                        
                        <button class="pagination-btn <?php echo $page == $total_pages ? 'active' : ''; ?>" 
                                onclick="goToPage(<?php echo min($total_pages, $page + 1); ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>
                            <i class="fas fa-angle-right"></i>
                        </button>
                        <button class="pagination-btn <?php echo $page == $total_pages ? 'active' : ''; ?>" 
                                onclick="goToPage(<?php echo $total_pages; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Reviews List -->
            <div class="reviews-container review-mobile-view" id="reviewsMobileList">
                <form id="bulkActionFormMobile" method="POST">
                    <input type="hidden" name="action" value="bulk_action">
                    <input type="hidden" name="bulk_action" id="bulkActionValueMobile" value="">
                    
                    <?php if (empty($reviews)): ?>
                        <div class="loading-state">
                            <i class="fas fa-star" style="font-size: 3rem; color: var(--gray-dark); margin-bottom: 20px;"></i>
                            <h4>No reviews found</h4>
                            <p>No reviews match your current filter criteria.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <input type="checkbox" class="review-card-checkbox" name="review_ids[]" value="<?php echo $review['id']; ?>" 
                                       id="review_mobile_<?php echo $review['id']; ?>">
                                
                                <div class="review-card-content">
                                    <div class="review-card-header">
                                        <div class="review-card-user">
                                            <div class="review-card-avatar" style="background: <?php echo $review['customer']['avatar_color']; ?>">
                                                <?php echo $review['customer']['avatar']; ?>
                                            </div>
                                            <div class="review-card-user-info">
                                                <h4><?php echo $review['customer']['name']; ?></h4>
                                                <p><?php echo $review['customer']['email']; ?></p>
                                            </div>
                                        </div>
                                        <div class="rating-stars">
                                            <?php echo str_repeat('<i class="fas fa-star"></i>', $review['rating']); ?>
                                            <?php echo str_repeat('<i class="far fa-star"></i>', 5 - $review['rating']); ?>
                                        </div>
                                        <span class="review-status status-<?php echo $review['status']; ?>">
                                            <?php echo ucfirst($review['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($review['image_url']) && $review['image_url'] !== 'https://randomuser.me/api/portraits/neutral/default.jpg'): ?>
                                    <div class="review-card-image">
                                        <img src="<?php echo $review['image_url']; ?>" alt="Review Image" 
                                             style="width: 100%; height: 150px; object-fit: cover; border-radius: 5px; margin-bottom: 10px; border: 1px solid var(--gray-dark);"
                                             onclick="viewReviewImage('<?php echo $review['image_url']; ?>', '<?php echo $review['customer']['name']; ?>')">
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="review-card-details">
                                        <div class="review-detail-item">
                                            <span class="review-detail-label">Rating</span>
                                            <span class="review-detail-value"><?php echo $review['rating']; ?>.0/5.0</span>
                                        </div>
                                        <div class="review-detail-item">
                                            <span class="review-detail-label">Order ID</span>
                                            <span class="review-detail-value"><?php echo $review['order_id']; ?></span>
                                        </div>
                                        <div class="review-detail-item">
                                            <span class="review-detail-label">Date</span>
                                            <span class="review-detail-value"><?php echo $review['timestamp']; ?></span>
                                        </div>
                                        <div class="review-detail-item">
                                            <span class="review-detail-label">Menu Items</span>
                                            <span class="review-detail-value"><?php echo !empty($review['menu_items']) ? implode(', ', $review['menu_items']) : 'Not specified'; ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="review-card-content">
                                        <div class="review-text-mobile"><?php echo nl2br($review['text']); ?></div>
                                        
                                        <?php if (!empty($review['admin_reply'])): ?>
                                            <div class="admin-reply" style="margin-top: 10px;">
                                                <div class="admin-reply-header">
                                                    <i class="fas fa-user-tie"></i>
                                                    <span>Admin Reply</span>
                                                </div>
                                                <div class="admin-reply-text"><?php echo nl2br($review['admin_reply']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="review-card-actions">
                                        <button type="button" class="review-action-btn view-details" 
                                                onclick="viewReviewDetails(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-eye"></i> Details
                                        </button>
                                        <?php if ($review['status'] === 'pending'): ?>
                                        <button type="button" class="review-action-btn approve" 
                                                onclick="approveReview(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button type="button" class="review-action-btn reject" 
                                                onclick="rejectReview(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" class="review-action-btn reply" 
                                                onclick="replyToReview(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-reply"></i> Reply
                                        </button>
                                        <button type="button" class="review-action-btn delete" 
                                                onclick="deleteReview(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Mobile Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <button class="pagination-btn <?php echo $page == 1 ? 'active' : ''; ?>" 
                                        onclick="goToPage(1)" <?php echo $page == 1 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button class="pagination-btn <?php echo $page == 1 ? 'active' : ''; ?>" 
                                        onclick="goToPage(<?php echo max(1, $page - 1); ?>)" <?php echo $page == 1 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-angle-left"></i>
                                </button>
                                
                                <button class="pagination-btn active">
                                    <?php echo $page; ?> of <?php echo $total_pages; ?>
                                </button>
                                
                                <button class="pagination-btn <?php echo $page == $total_pages ? 'active' : ''; ?>" 
                                        onclick="goToPage(<?php echo min($total_pages, $page + 1); ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>
                                    <i class="fas fa-angle-right"></i>
                                </button>
                                <button class="pagination-btn <?php echo $page == $total_pages ? 'active' : ''; ?>" 
                                        onclick="goToPage(<?php echo $total_pages; ?>)" <?php echo $page == $total_pages ? 'disabled' : ''; ?>>
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved. | Developed by ERIBS Tech</p>
            </div>
        </div>
    </div>

    <!-- Review Details Modal -->
    <div class="modal" id="reviewDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Review Details</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="review-details-header">
                    <div class="review-details-avatar" id="reviewDetailsAvatar">JS</div>
                    <div class="review-details-info">
                        <h4 id="reviewerName">Loading...</h4>
                        <p id="reviewerEmail">Loading...</p>
                        <div class="review-details-rating">
                            <div class="rating-stars" id="modalRatingStars">
                                <!-- Stars will be dynamically added here -->
                            </div>
                            <span class="rating-value" id="modalRatingValue">0.0</span>
                        </div>
                    </div>
                </div>
                
                <div class="review-image-preview-large" id="reviewImagePreview" style="display: none;">
                    <img id="reviewImage" src="" alt="Review Image" style="width: 100%; max-height: 250px; object-fit: contain; border-radius: 8px; margin-bottom: 15px; border: 1px solid var(--gray-dark);">
                </div>
                
                <div class="review-details-text" id="reviewDetailsText">
                    Loading review content...
                </div>
                
                <div class="review-details-meta">
                    <div class="meta-item">
                        <span class="meta-label">Order ID</span>
                        <span class="meta-value" id="reviewOrderId">N/A</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Date Submitted</span>
                        <span class="meta-value" id="reviewDate">Loading...</span>
                    </div>
                    <div class="meta-item status">
                        <span class="meta-label">Status</span>
                        <span class="meta-value" id="reviewStatus">Loading...</span>
                    </div>
                    <div class="meta-item menu-items">
                        <span class="meta-label">Menu Items Ordered</span>
                        <span class="meta-value" id="reviewItems">Not specified</span>
                    </div>
                </div>
                
                <div class="reply-section">
                    <h4>Admin Reply</h4>
                    <textarea class="reply-textarea" id="replyTextarea" placeholder="Type your reply here..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-danger" id="rejectReviewBtn">
                        <i class="fas fa-times"></i>
                        Reject Review
                    </button>
                    <button class="btn btn-success" id="approveReviewBtn">
                        <i class="fas fa-check"></i>
                        Approve Review
                    </button>
                    <button class="btn btn-primary" id="saveReplyBtn">
                        <i class="fas fa-reply"></i>
                        Save Reply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="modal" id="imageViewModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 id="imageViewTitle">Review Image</h3>
                <button class="close-modal" id="closeImageViewModal">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center;">
                <img id="fullReviewImage" src="" alt="Review Image" style="max-width: 100%; max-height: 500px; border-radius: 8px;">
            </div>
        </div>
    </div>

    <!-- Hidden forms for single actions -->
    <form id="approveForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="approve">
        <input type="hidden" name="review_id" id="approveReviewId" value="">
    </form>
    
    <form id="rejectForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="review_id" id="rejectReviewId" value="">
    </form>
    
    <form id="replyForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="reply">
        <input type="hidden" name="review_id" id="replyReviewId" value="">
        <input type="hidden" name="reply" id="replyContent" value="">
    </form>
    
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="review_id" id="deleteReviewId" value="">
    </form>

    <!-- ... rest of your PHP/HTML code remains the same until the JavaScript section ... -->

<script>
        // PHP data for JavaScript
        const reviewsData = <?php echo json_encode($reviews); ?>;
        const currentFilter = '<?php echo $current_filter; ?>';
        const currentPage = <?php echo $page; ?>;
        const totalPages = <?php echo $total_pages; ?>;
        const searchQuery = '<?php echo addslashes($search_query); ?>';
        const pendingCount = <?php echo $stats['pending_count'] ?? 0; ?>;

        // DOM Elements
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const searchInput = document.getElementById('searchInput');
        const reviewDetailsModal = document.getElementById('reviewDetailsModal');
        const imageViewModal = document.getElementById('imageViewModal');
        const closeModal = document.getElementById('closeModal');
        const closeImageViewModal = document.getElementById('closeImageViewModal');
        const approveReviewBtn = document.getElementById('approveReviewBtn');
        const rejectReviewBtn = document.getElementById('rejectReviewBtn');
        const saveReplyBtn = document.getElementById('saveReplyBtn');
        const replyTextarea = document.getElementById('replyTextarea');
        const bulkActionSelect = document.getElementById('bulkActionSelect');
        const reviewImagePreview = document.getElementById('reviewImagePreview');
        const reviewImage = document.getElementById('reviewImage');
        const fullReviewImage = document.getElementById('fullReviewImage');
        const imageViewTitle = document.getElementById('imageViewTitle');
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const emptyNotifications = document.getElementById('emptyNotifications');
        const markAllReadBtn = document.getElementById('markAllRead');
        const userMenu = document.getElementById('userMenu');
        const userDropdown = document.getElementById('userDropdown');
        
        // Hidden forms
        const approveForm = document.getElementById('approveForm');
        const rejectForm = document.getElementById('rejectForm');
        const replyForm = document.getElementById('replyForm');
        const deleteForm = document.getElementById('deleteForm');
        const bulkActionForm = document.getElementById('bulkActionForm');
        const bulkActionFormMobile = document.getElementById('bulkActionFormMobile');
        const bulkActionValue = document.getElementById('bulkActionValue');
        const bulkActionValueMobile = document.getElementById('bulkActionValueMobile');

        // Current review being viewed
        let currentReviewId = null;
        let activeDropdown = null;

        // Sample notification data
        const notifications = [
            {
                id: 1,
                title: 'New Review Submitted',
                message: 'John Doe submitted a 5-star review',
                time: '10 minutes ago',
                read: false,
                type: 'review'
            },
            {
                id: 2,
                title: 'Review Needs Attention',
                message: 'A negative review (2 stars) requires your attention',
                time: '2 hours ago',
                read: false,
                type: 'warning'
            },
            {
                id: 3,
                title: 'Review Approved',
                message: 'You approved Jane Smith\'s review',
                time: '1 day ago',
                read: true,
                type: 'success'
            },
            {
                id: 4,
                title: 'System Update',
                message: 'Reviews system has been updated to version 2.0',
                time: '3 days ago',
                read: true,
                type: 'info'
            }
        ];

        // Real-time Clock Functionality
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';

            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12;

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

        // Mobile sidebar toggler functionality
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            closeAllDropdowns();
        });

        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            closeAllDropdowns();
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

        // Navigation functions
        function setFilter(filter) {
            const params = new URLSearchParams(window.location.search);
            params.set('filter', filter);
            params.set('page', '1');
            window.location.href = 'admin-reviews.php?' + params.toString();
        }

        function goToPage(page) {
            const params = new URLSearchParams(window.location.search);
            params.set('page', page);
            window.location.href = 'admin-reviews.php?' + params.toString();
        }

        function searchReviews() {
            const searchTerm = searchInput.value.trim();
            const params = new URLSearchParams(window.location.search);
            params.set('search', searchTerm);
            params.set('page', '1');
            window.location.href = 'admin-reviews.php?' + params.toString();
        }

        // Review action functions
        function approveReview(reviewId) {
            if (confirm('Are you sure you want to approve this review?')) {
                document.getElementById('approveReviewId').value = reviewId;
                approveForm.submit();
            }
        }

        function rejectReview(reviewId) {
            if (confirm('Are you sure you want to reject this review?')) {
                document.getElementById('rejectReviewId').value = reviewId;
                rejectForm.submit();
            }
        }

        function deleteReview(reviewId) {
            if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
                document.getElementById('deleteReviewId').value = reviewId;
                deleteForm.submit();
            }
        }

        function replyToReview(reviewId) {
            // Find the review in the reviewsData array
            const review = reviewsData.find(r => r.id == reviewId);
            if (review) {
                showReviewDetails(review);
                // Focus on reply textarea
                setTimeout(() => {
                    replyTextarea.focus();
                }, 300);
            } else {
                console.error('Review not found:', reviewId);
                alert('Error: Review details not found. Please refresh the page and try again.');
            }
        }

        // Bulk actions
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.review-checkbox, .review-card-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
        }

        function performBulkAction() {
            const action = bulkActionSelect.value;
            if (!action) {
                alert('Please select a bulk action');
                return;
            }

            const checkboxes = document.querySelectorAll('.review-checkbox:checked, .review-card-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Please select at least one review');
                return;
            }

            if (confirm(`Are you sure you want to perform this action on ${checkboxes.length} review(s)?`)) {
                bulkActionValue.value = action;
                bulkActionValueMobile.value = action;
                
                if (window.innerWidth <= 768) {
                    bulkActionFormMobile.submit();
                } else {
                    bulkActionForm.submit();
                }
            }
        }

        // Show review details modal - FIXED VERSION
        function viewReviewDetails(reviewId) {
            console.log('Opening review details for ID:', reviewId);
            console.log('Available reviews data:', reviewsData);
            
            // Find the review in the reviewsData array
            const review = reviewsData.find(r => r.id == reviewId);
            
            if (!review) {
                console.error('Review not found in reviewsData:', reviewId);
                alert('Error: Could not load review details. The review may have been deleted or the page needs to be refreshed.');
                return;
            }
            
            currentReviewId = reviewId;
            
            // Update modal content
            document.getElementById('reviewDetailsAvatar').textContent = review.customer.avatar;
            document.getElementById('reviewDetailsAvatar').style.background = review.customer.avatar_color;
            document.getElementById('reviewerName').textContent = review.customer.name;
            document.getElementById('reviewerEmail').textContent = review.customer.email;
            
            // Update stars
            const starsContainer = document.getElementById('modalRatingStars');
            starsContainer.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                const star = document.createElement('i');
                star.className = i <= review.rating ? 'fas fa-star' : 'far fa-star';
                star.style.color = '#ffc107';
                starsContainer.appendChild(star);
            }
            
            document.getElementById('modalRatingValue').textContent = review.rating + '.0';
            document.getElementById('reviewDetailsText').textContent = review.text;
            document.getElementById('reviewOrderId').textContent = review.order_id;
            document.getElementById('reviewDate').textContent = review.formatted_date;
            
            // Update status with color coding
            const statusElement = document.getElementById('reviewStatus');
            statusElement.textContent = review.status.charAt(0).toUpperCase() + review.status.slice(1);
            statusElement.style.color = review.status === 'approved' ? 'var(--success)' : 
                                      review.status === 'pending' ? 'var(--warning)' : 
                                      'var(--danger)';
            
            // Format menu items for display
            const menuItemsText = review.menu_items && review.menu_items.length > 0 ? 
                review.menu_items.join(', ') : 'Not specified';
            document.getElementById('reviewItems').textContent = menuItemsText;
            
            // Show/hide image preview
            if (review.image_url && review.image_url !== 'https://randomuser.me/api/portraits/neutral/default.jpg') {
                reviewImagePreview.style.display = 'block';
                reviewImage.src = review.image_url;
                reviewImage.onerror = function() {
                    reviewImagePreview.style.display = 'none';
                    console.error('Failed to load image:', review.image_url);
                };
            } else {
                reviewImagePreview.style.display = 'none';
            }
            
            // Set reply textarea
            replyTextarea.value = review.admin_reply || '';
            
            // Show modal
            reviewDetailsModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Close sidebar on mobile when modal opens
            if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
            
            closeAllDropdowns();
            
            console.log('Modal opened successfully for review:', review);
        }

        // View full review image
        function viewReviewImage(imageUrl, customerName) {
            fullReviewImage.src = imageUrl;
            imageViewTitle.textContent = `${customerName}'s Review Image`;
            imageViewModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Notification functionality
        function loadNotifications() {
            notificationList.innerHTML = '';
            
            if (notifications.length === 0) {
                emptyNotifications.style.display = 'block';
                return;
            }
            
            emptyNotifications.style.display = 'none';
            
            notifications.forEach(notification => {
                const notificationItem = document.createElement('div');
                notificationItem.className = `notification-item ${notification.read ? '' : 'unread'}`;
                notificationItem.innerHTML = `
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">${notification.time}</div>
                `;
                
                notificationItem.addEventListener('click', () => {
                    markNotificationAsRead(notification.id);
                });
                
                notificationList.appendChild(notificationItem);
            });
        }

        function markNotificationAsRead(notificationId) {
            const notification = notifications.find(n => n.id === notificationId);
            if (notification && !notification.read) {
                notification.read = true;
                loadNotifications();
                updateNotificationBadge();
            }
        }

        function markAllNotificationsAsRead() {
            notifications.forEach(notification => {
                notification.read = true;
            });
            loadNotifications();
            updateNotificationBadge();
        }

        function updateNotificationBadge() {
            const unreadCount = notifications.filter(n => !n.read).length;
            const badge = notificationIcon.querySelector('.notification-badge');
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }

        // Dropdown functionality
        function toggleDropdown(dropdown) {
            closeAllDropdowns();
            if (activeDropdown !== dropdown) {
                dropdown.classList.add('active');
                activeDropdown = dropdown;
            } else {
                activeDropdown = null;
            }
        }

        function closeAllDropdowns() {
            notificationDropdown.classList.remove('active');
            userDropdown.classList.remove('active');
            activeDropdown = null;
        }

        // Modal actions
        approveReviewBtn.addEventListener('click', function() {
            if (currentReviewId && confirm('Are you sure you want to approve this review?')) {
                document.getElementById('approveReviewId').value = currentReviewId;
                approveForm.submit();
            }
        });

        rejectReviewBtn.addEventListener('click', function() {
            if (currentReviewId && confirm('Are you sure you want to reject this review?')) {
                document.getElementById('rejectReviewId').value = currentReviewId;
                rejectForm.submit();
            }
        });

        saveReplyBtn.addEventListener('click', function() {
            if (currentReviewId) {
                document.getElementById('replyReviewId').value = currentReviewId;
                document.getElementById('replyContent').value = replyTextarea.value.trim();
                
                if (replyTextarea.value.trim()) {
                    if (confirm('Save reply to this review?')) {
                        replyForm.submit();
                    }
                } else {
                    alert('Please enter a reply before saving.');
                }
            }
        });

        // Close modals
        closeModal.addEventListener('click', function() {
            reviewDetailsModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        closeImageViewModal.addEventListener('click', function() {
            imageViewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === reviewDetailsModal) {
                reviewDetailsModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
            if (event.target === imageViewModal) {
                imageViewModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
            
            // Close dropdowns when clicking outside
            if (!notificationIcon.contains(event.target) && !notificationDropdown.contains(event.target)) {
                notificationDropdown.classList.remove('active');
            }
            if (!userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.remove('active');
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                if (reviewDetailsModal.style.display === 'flex') {
                    reviewDetailsModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
                if (imageViewModal.style.display === 'flex') {
                    imageViewModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
                closeAllDropdowns();
            }
        });

        // Logout confirmation function
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }

        // Event listeners for notification and user menu
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(notificationDropdown);
        });

        userMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(userDropdown);
        });

        markAllReadBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            markAllNotificationsAsRead();
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
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, reviewsData:', reviewsData);
            
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
            
            // Set up search input
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchReviews();
                }
            });
            
            // Load notifications
            loadNotifications();
            updateNotificationBadge();
            
            // Add pending reviews to notifications if they exist
            if (pendingCount > 0) {
                notifications.unshift({
                    id: notifications.length + 1,
                    title: 'Pending Reviews',
                    message: `You have ${pendingCount} review(s) pending approval`,
                    time: 'Just now',
                    read: false,
                    type: 'warning'
                });
                loadNotifications();
                updateNotificationBadge();
            }
            
            // Add click event listeners to all view details buttons
            document.querySelectorAll('.review-action-btn.view-details').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Get the review ID from the onclick attribute
                    const onclickAttr = this.getAttribute('onclick');
                    if (onclickAttr) {
                        const match = onclickAttr.match(/viewReviewDetails\((\d+)\)/);
                        if (match && match[1]) {
                            const reviewId = parseInt(match[1]);
                            viewReviewDetails(reviewId);
                        }
                    }
                });
            });
            
            // Debug: Log all review IDs available
            console.log('Available review IDs:', reviewsData.map(r => r.id));
        });
    </script>
</body>
</html>