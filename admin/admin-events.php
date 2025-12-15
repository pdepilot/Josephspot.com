<?php
// Start session at the VERY TOP of the file
session_start();

// Check if admin is logged in using consistent session variables
$is_logged_in = false;

// Method 1: Check for admin_logged_in 
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $is_logged_in = true;
}

// Method 2: Check for admin_id and admin_username (set by admin-login.php)
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_username'])) {
    $is_logged_in = true;
}

// If not logged in, redirect to login page
if (!$is_logged_in) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'admin-login.php') {
        header("Location: admin-login.php");
        exit();
    }
}

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'joseph_pot_admin';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch current auto-rotation setting
$auto_rotate_setting = true; // default
$settings_sql = "SELECT setting_value FROM site_settings WHERE setting_key = 'events_auto_rotate'";
$settings_result = $conn->query($settings_sql);
if ($settings_result && $settings_result->num_rows > 0) {
    $setting_row = $settings_result->fetch_assoc();
    $auto_rotate_setting = ($setting_row['setting_value'] == '1');
}

// Handle auto-rotation toggle
if (isset($_POST['toggle_auto_rotate'])) {
    $new_value = $_POST['toggle_auto_rotate'] == '1' ? '1' : '0';
    $update_sql = "UPDATE site_settings SET setting_value = '$new_value' WHERE setting_key = 'events_auto_rotate'";
    if ($conn->query($update_sql)) {
        $auto_rotate_setting = ($new_value == '1');
        $_SESSION['success_message'] = "Auto-rotation setting updated!";
    }
}

// Ensure uploads directory exists
$uploads_dir = '../uploads/';
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'create':
                // Create new event
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $event_date = $conn->real_escape_string($_POST['event_date']);
                $location = $conn->real_escape_string($_POST['location']);
                $status = $conn->real_escape_string($_POST['status']);

                // Handle image upload
                $image_url = '';

                // Method 1: Check for base64 image data
                if (!empty($_POST['image_base64'])) {
                    $base64_image = $_POST['image_base64'];

                    // If it's a base64 data URL, extract the base64 part
                    if (strpos($base64_image, 'data:image') === 0) {
                        list($type, $base64_image) = explode(';', $base64_image);
                        list(, $base64_image) = explode(',', $base64_image);
                    }

                    // Decode base64 and save as file
                    $image_data = base64_decode($base64_image);
                    if ($image_data !== false) {
                        $image_name = 'event_' . time() . '_' . rand(1000, 9999) . '.jpg';
                        $image_path = '../uploads/' . $image_name;

                        if (file_put_contents($image_path, $image_data)) {
                            $image_url = 'uploads/' . $image_name;
                        }
                    }
                }

                // Method 2: Check for image URL (if manually entered)
                if (empty($image_url) && !empty($_POST['image_url'])) {
                    $image_url = $conn->real_escape_string($_POST['image_url']);
                }

                $sql = "INSERT INTO events (title, description, event_date, location, image_url, status) 
                        VALUES ('$title', '$description', '$event_date', '$location', '$image_url', '$status')";

                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Event created successfully!";
                } else {
                    $_SESSION['error_message'] = "Error creating event: " . $conn->error;
                }
                break;

            case 'update':
                // Update existing event
                $id = intval($_POST['id']);
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $event_date = $conn->real_escape_string($_POST['event_date']);
                $location = $conn->real_escape_string($_POST['location']);
                $status = $conn->real_escape_string($_POST['status']);

                // Handle image upload/update
                $image_url = '';

                // Check if we have base64 image data
                if (!empty($_POST['image_base64'])) {
                    $base64_image = $_POST['image_base64'];

                    // If it's a base64 data URL, extract the base64 part
                    if (strpos($base64_image, 'data:image') === 0) {
                        list($type, $base64_image) = explode(';', $base64_image);
                        list(, $base64_image) = explode(',', $base64_image);
                    }

                    // Decode base64 and save as file
                    $image_data = base64_decode($base64_image);
                    if ($image_data !== false) {
                        $image_name = 'event_' . time() . '_' . rand(1000, 9999) . '.jpg';
                        $image_path = '../uploads/' . $image_name;

                        if (file_put_contents($image_path, $image_data)) {
                            $image_url = 'uploads/' . $image_name;

                            // Delete old image if it exists and is in uploads folder
                            $old_image_sql = "SELECT image_url FROM events WHERE id = $id";
                            $old_result = $conn->query($old_image_sql);
                            if ($old_result && $old_row = $old_result->fetch_assoc()) {
                                $old_image = $old_row['image_url'];
                                if ($old_image && strpos($old_image, 'uploads/') === 0) {
                                    $old_path = '../' . $old_image;
                                    if (file_exists($old_path)) {
                                        unlink($old_path);
                                    }
                                }
                            }
                        }
                    }
                }

                // Check if image URL was manually entered
                if (empty($image_url) && !empty($_POST['image_url'])) {
                    $image_url = $conn->real_escape_string($_POST['image_url']);
                }

                // If no new image, keep the old one
                if (empty($image_url)) {
                    $old_image_sql = "SELECT image_url FROM events WHERE id = $id";
                    $old_result = $conn->query($old_image_sql);
                    if ($old_result && $old_row = $old_result->fetch_assoc()) {
                        $image_url = $old_row['image_url'];
                    }
                }

                $sql = "UPDATE events SET 
                        title = '$title',
                        description = '$description',
                        event_date = '$event_date',
                        location = '$location',
                        image_url = '$image_url',
                        status = '$status',
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = $id";

                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Event updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Error updating event: " . $conn->error;
                }
                break;

            case 'delete':
                // Delete event
                $id = intval($_POST['id']);

                // Get event image before deleting
                $image_sql = "SELECT image_url FROM events WHERE id = $id";
                $image_result = $conn->query($image_sql);
                if ($image_result && $image_row = $image_result->fetch_assoc()) {
                    $image_url = $image_row['image_url'];
                    // Delete uploaded image file if it exists in uploads folder
                    if ($image_url && strpos($image_url, 'uploads/') === 0) {
                        $image_path = '../' . $image_url;
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                    }
                }

                $sql = "DELETE FROM events WHERE id = $id";

                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Event deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting event: " . $conn->error;
                }
                break;
        }

        // Redirect to prevent form resubmission
        header("Location: admin-events.php");
        exit();
    }
}

// Fetch events from database
$upcoming_events = [];
$ongoing_events = [];
$past_events = [];
$all_events = [];

$sql = "SELECT * FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $event = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'description' => htmlspecialchars($row['description']),
            'event_date' => $row['event_date'],
            'location' => htmlspecialchars($row['location']),
            'image_url' => $row['image_url'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];

        $all_events[] = $event;

        // Categorize events
        $event_date = new DateTime($row['event_date']);
        $now = new DateTime();

        if ($row['status'] === 'upcoming') {
            $upcoming_events[] = $event;
        } elseif ($row['status'] === 'ongoing') {
            $ongoing_events[] = $event;
        } elseif ($event_date < $now || $row['status'] === 'completed') {
            $past_events[] = $event;
        }
    }
}

// Get stats
$total_events = count($all_events);
$upcoming_count = count($upcoming_events);
$ongoing_count = count($ongoing_events);
$past_count = count($past_events);

// Prepare events for preview (upcoming and ongoing only)
$preview_events = array_merge($upcoming_events, $ongoing_events);
usort($preview_events, function ($a, $b) {
    return strtotime($a['event_date']) - strtotime($b['event_date']);
});

// Pass settings to JavaScript
$auto_rotate_js = $auto_rotate_setting ? 'true' : 'false';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo3.png">
    <title>Events Dashboard - Joseph's Pot</title>
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
            --brown: #8b4513;
            --pale-orange: #fff8dc;
            --white: #ffffff;
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

        /* Events Dashboard Specific Styles */
        .events-dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .events-dashboard {
                grid-template-columns: 1fr;
            }
        }

        .events-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .events-section:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray);
        }

        .section-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header h3 i {
            color: var(--secondary);
        }

        .event-count {
            background: var(--primary);
            color: white;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .events-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .event-card {
            display: flex;
            background: var(--gray);
            border-radius: 10px;
            overflow: hidden;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .event-date {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: white;
            padding: 15px;
            min-width: 80px;
            text-align: center;
        }

        .event-day {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .event-month {
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .event-details {
            flex: 1;
            padding: 15px;
        }

        .event-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary-dark);
        }

        .event-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .event-info i {
            color: var(--secondary);
            width: 16px;
        }

        .event-description {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .event-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-upcoming {
            background: rgba(76, 175, 80, 0.2);
            color: var(--success);
        }

        .status-ongoing {
            background: rgba(33, 150, 243, 0.2);
            color: var(--info);
        }

        .status-completed {
            background: rgba(158, 158, 158, 0.2);
            color: var(--text-light);
        }

        .status-cancelled {
            background: rgba(244, 67, 54, 0.2);
            color: var(--danger);
        }

        .event-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .event-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-btn.edit {
            background: var(--info);
            color: white;
        }

        .event-btn.delete {
            background: var(--danger);
            color: white;
        }

        .event-btn.view {
            background: var(--primary);
            color: white;
        }

        .event-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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

        /* Event Stats */
        .event-stats {
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
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.upcoming {
            background: linear-gradient(135deg, var(--success), #66bb6a);
        }

        .stat-icon.ongoing {
            background: linear-gradient(135deg, var(--info), #64b5f6);
        }

        .stat-icon.completed {
            background: linear-gradient(135deg, var(--warning), #ffb74d);
        }

        .stat-icon.total {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .stat-info {
            flex: 1;
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

        /* Add Event Button */
        .add-event-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 6px 20px rgba(139, 69, 19, 0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: var(--transition);
            z-index: 100;
        }

        .add-event-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(139, 69, 19, 0.5);
        }

        /* Site Preview Section */
        .site-preview-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            transition: var(--transition);
        }

        .site-preview-section:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray);
            flex-wrap: wrap;
        }

        .preview-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            width: 100%;
        }

        .preview-header h3 i {
            color: var(--secondary);
        }

        /* Rotation Control Toggle Styles */
        .preview-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .rotation-control {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--gray);
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            transition: var(--transition);
            user-select: none;
        }

        .rotation-control:hover {
            background: var(--gray-dark);
        }

        .rotation-control.active {
            background: var(--primary);
            color: white;
        }

        .rotation-control.active .toggle-icon {
            color: white;
        }

        .rotation-control .toggle-icon {
            color: var(--primary);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .rotation-control .toggle-text {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .rotation-control.active .toggle-text {
            color: white;
        }

        .preview-notice {
            background: #e3f2fd;
            border-left: 4px solid var(--info);
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: var(--text);
            word-break: break-word;
        }

        .preview-notice i {
            color: var(--info);
            margin-right: 8px;
        }

        /* UPCOMING EVENTS PREVIEW */
        .events-preview {
            background-color: var(--pale-orange);
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            padding: 30px;
        }

        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }

        .preview-section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .preview-section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--brown);
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .preview-section-title::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--brown);
        }

        .preview-section-subtitle {
            font-size: 1.1rem;
            color: var(--brown);
            font-weight: 400;
        }

        .preview-event-card {
            display: flex;
            flex-direction: column;
            background: var(--brown);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        @media (min-width: 768px) {
            .preview-event-card {
                flex-direction: row;
                height: 400px;
            }
        }

        .preview-event-media {
            position: relative;
            flex: 1;
            min-height: 250px;
            overflow: hidden;
        }

        .preview-event-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .preview-event-card:hover .preview-event-image {
            transform: scale(1.03);
        }

        .preview-event-date-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(80, 7, 0, 0.7);
            color: var(--white);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-align: center;
            backdrop-filter: blur(5px);
        }

        .preview-event-day {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .preview-event-date {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .preview-event-details {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .preview-event-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .preview-event-description {
            color: var(--white);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .preview-countdown-container {
            margin-top: auto;
        }

        .preview-countdown-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .preview-countdown {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .preview-countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .preview-countdown-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
            background: var(--pale-orange);
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            min-width: 50px;
            text-align: center;
        }

        .preview-countdown-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--white);
            margin-top: 0.25rem;
        }

        .preview-countdown-separator {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
            margin-top: -10px;
        }

        .preview-event-controls {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        .preview-event-nav-button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--brown);
            border: 1px solid var(--pale-orange);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .preview-event-nav-button:hover {
            background: var(--brown);
            border-color: var(--accent);
            color: var(--white);
        }

        .preview-event-nav-button svg {
            width: 24px;
            height: 24px;
            stroke: var(--white);
        }

        .preview-event-nav-button:hover svg {
            stroke: var(--white);
        }

        /* Preview slider dots */
        .preview-slider-dots {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .preview-slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(139, 69, 19, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .preview-slider-dot.active {
            background: var(--brown);
            transform: scale(1.2);
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
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            transition: var(--transition);
        }

        .close-modal:hover {
            color: var(--danger);
            transform: rotate(90deg);
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

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-dark);
            border-radius: 8px;
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
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--gray);
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
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
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--gray);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: var(--gray-dark);
        }

        /* Image preview styles */
        .image-preview-container {
            margin-top: 10px;
        }

        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
        }

        .image-preview.visible {
            display: block;
        }

        .image-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background: var(--gray);
            border: 1px dashed var(--gray-dark);
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }

        .image-upload-btn:hover {
            background: var(--gray-dark);
        }

        .image-upload-btn input[type="file"] {
            display: none;
        }

        .image-url-hint {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 5px;
            font-style: italic;
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

        /* Animation Styles */
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
            .event-stats {
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

            .event-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .preview-section-title {
                font-size: 2rem;
            }

            .preview-event-details {
                padding: 1.5rem;
            }

            .preview-event-title {
                font-size: 1.5rem;
            }

            .preview-countdown-value {
                font-size: 1.2rem;
                min-width: 40px;
            }

            .events-preview {
                padding: 20px;
            }

            .preview-controls {
                flex-direction: column;
                align-items: flex-start;
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

            .event-stats {
                grid-template-columns: 1fr;
            }

            .preview-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .preview-controls {
                width: 100%;
                justify-content: space-between;
            }

            .preview-section-title {
                font-size: 1.8rem;
            }

            .preview-event-title {
                font-size: 1.4rem;
            }

            .preview-event-details {
                padding: 1rem;
            }

            .preview-countdown-value {
                font-size: 1rem;
                min-width: 40px;
                padding: 0.4rem 0.6rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .event-card {
                flex-direction: column;
            }

            .event-date {
                flex-direction: row;
                justify-content: space-between;
                min-width: auto;
            }

            .event-actions {
                flex-wrap: wrap;
            }

            .modal-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }

            .events-section {
                padding: 20px 15px;
            }

            .preview-section-title {
                font-size: 1.5rem;
            }

            .preview-section-title::after {
                width: 80px;
            }

            .preview-event-title {
                font-size: 1.2rem;
            }

            .preview-event-description {
                font-size: 0.9rem;
            }

            .preview-countdown {
                gap: 0.3rem;
            }

            .preview-countdown-value {
                font-size: 0.9rem;
                min-width: 35px;
                padding: 0.3rem 0.5rem;
            }

            .preview-countdown-separator {
                font-size: 1.2rem;
                margin-top: -8px;
            }

            .preview-slider-dots {
                margin-top: 15px;
            }

            .preview-slider-dot {
                width: 10px;
                height: 10px;
            }

            .modal-content {
                padding: 20px 15px;
            }

            .form-actions {
                flex-direction: column;
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

            .preview-section-title {
                font-size: 1.3rem;
            }

            .preview-event-details {
                padding: 1rem;
            }

            .preview-event-title {
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
            }

            .preview-event-description {
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }

            .preview-countdown-container {
                margin-top: 1rem;
            }

            .preview-countdown-value {
                font-size: 0.8rem;
                min-width: 30px;
            }

            .preview-countdown-label {
                font-size: 0.6rem;
            }

            .event-btn {
                padding: 5px 10px;
                font-size: 0.75rem;
            }

            .preview-event-controls {
                margin-top: 1rem;
            }

            .preview-event-nav-button {
                width: 40px;
                height: 40px;
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
                    <a href="admin-events.php" class="active">
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
                <h2>Events Management</h2>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search events..." onkeyup="searchEvents()">
                    </div>
                    <div class="notification-user-container">
                        <div class="notification-icon" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge"><?php echo $upcoming_count + $ongoing_count; ?></span>
                        </div>
                        <div class="user-menu">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Site Preview Section -->
            <div class="site-preview-section reveal">
                <div class="preview-header">
                    <h3><i class="fas fa-desktop"></i> Site Events Preview</h3>
                    <div class="preview-controls">
                        <div class="rotation-control <?php echo $auto_rotate_setting ? 'active' : ''; ?>" id="rotationToggle">
                            <span class="toggle-icon">
                                <i class="fas <?php echo $auto_rotate_setting ? 'fa-play-circle' : 'fa-pause-circle'; ?>"></i>
                            </span>
                            <span class="toggle-text">Auto Rotate: <?php echo $auto_rotate_setting ? 'ON' : 'OFF'; ?></span>
                        </div>
                        <span class="event-count">Live Preview (<?php echo count($preview_events); ?>)</span>
                    </div>
                </div>

                <div class="preview-notice">
                    <i class="fas fa-info-circle"></i>
                    This is how your events will appear on the main website. Changes made here will be reflected on the live site.
                    <span id="rotationStatus" style="font-weight: 600; <?php echo $auto_rotate_setting ? 'color: var(--success);' : 'color: var(--warning);'; ?>">
                        Auto-rotation is currently <?php echo $auto_rotate_setting ? 'enabled' : 'disabled'; ?>.
                    </span>
                </div>

                <!-- Upcoming Events Preview -->
                <div class="events-preview">
                    <div class="preview-container" id="eventContainer">
                        <div class="preview-section-header">
                            <h2 class="preview-section-title">Upcoming Events</h2>
                            <p class="preview-section-subtitle">Exclusive culinary experiences</p>
                        </div>

                        <div class="preview-event-card" id="previewEventCard">
                            <div class="preview-event-media">
                                <img src="../images/IM41.jpg" loading="lazy" alt="Event Image" id="previewEventImage" class="preview-event-image" />
                                <div class="preview-event-date-badge">
                                    <span id="previewEventDay" class="preview-event-day">Friday</span>
                                    <span id="previewEventDate" class="preview-event-date">15 Aug</span>
                                </div>
                            </div>

                            <div class="preview-event-details">
                                <h3 id="previewEventTitle" class="preview-event-title">Event Title</h3>
                                <p id="previewEventDesc" class="preview-event-description">Event description here...</p>

                                <div class="preview-countdown-container">
                                    <div class="preview-countdown-title">Time Remaining:</div>
                                    <div id="previewCountdown" class="preview-countdown">
                                        <div class="preview-countdown-item">
                                            <span class="days preview-countdown-value">00</span>
                                            <span class="preview-countdown-label">Days</span>
                                        </div>
                                        <div class="preview-countdown-separator">:</div>
                                        <div class="preview-countdown-item">
                                            <span class="hours preview-countdown-value">00</span>
                                            <span class="preview-countdown-label">Hours</span>
                                        </div>
                                        <div class="preview-countdown-separator">:</div>
                                        <div class="preview-countdown-item">
                                            <span class="minutes preview-countdown-value">00</span>
                                            <span class="preview-countdown-label">Min</span>
                                        </div>
                                        <div class="preview-countdown-separator">:</div>
                                        <div class="preview-countdown-item">
                                            <span class="seconds preview-countdown-value">00</span>
                                            <span class="preview-countdown-label">Sec</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-event-controls">
                            <button id="previewPrevEventBtn" class="preview-event-nav-button" aria-label="Previous event" style="margin-right: 10px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 12H5M12 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button id="previewNextEventBtn" class="preview-event-nav-button" aria-label="Next event">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <div class="preview-slider-dots" id="previewSliderDots">
                            <!-- Dots will be dynamically added here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Stats -->
            <div class="event-stats">
                <div class="stat-card reveal">
                    <div class="stat-icon upcoming">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $upcoming_count; ?></div>
                        <div class="stat-label">Upcoming Events</div>
                    </div>
                </div>

                <div class="stat-card reveal reveal-delay-1">
                    <div class="stat-icon ongoing">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $ongoing_count; ?></div>
                        <div class="stat-label">Ongoing Events</div>
                    </div>
                </div>

                <div class="stat-card reveal reveal-delay-2">
                    <div class="stat-icon completed">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $past_count; ?></div>
                        <div class="stat-label">Past Events</div>
                    </div>
                </div>

                <div class="stat-card reveal reveal-delay-3">
                    <div class="stat-icon total">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $total_events; ?></div>
                        <div class="stat-label">Total Events</div>
                    </div>
                </div>
            </div>

            <!-- Events Dashboard -->
            <div class="events-dashboard">
                <!-- Upcoming Events -->
                <div class="events-section reveal">
                    <div class="section-header">
                        <h3><i class="fas fa-calendar-plus"></i> Upcoming Events</h3>
                        <span class="event-count"><?php echo $upcoming_count; ?></span>
                    </div>
                    <div class="events-list" id="upcomingEvents">
                        <?php if (empty($upcoming_events)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-plus"></i>
                                <h4>No Upcoming Events</h4>
                                <p>Create a new event to get started</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($upcoming_events as $event): ?>
                                <?php
                                $date = new DateTime($event['event_date']);
                                $day = $date->format('d');
                                $month = $date->format('M');
                                $time = $date->format('h:i A');

                                // Get proper image URL for display
                                $display_image = '../images/IM41.jpg';
                                if ($event['image_url']) {
                                    if (strpos($event['image_url'], 'data:image') === 0) {
                                        // Base64 data - use default
                                        $display_image = '../images/IM41.jpg';
                                    } elseif (strpos($event['image_url'], 'uploads/') === 0) {
                                        // Uploaded file
                                        $display_image = '../' . $event['image_url'];
                                    } elseif (strpos($event['image_url'], 'http') === 0 || strpos($event['image_url'], './') === 0) {
                                        // External URL or relative path
                                        $display_image = $event['image_url'];
                                    }
                                }
                                ?>
                                <div class="event-card" data-id="<?php echo $event['id']; ?>">
                                    <div class="event-date">
                                        <div class="event-day"><?php echo $day; ?></div>
                                        <div class="event-month"><?php echo $month; ?></div>
                                    </div>
                                    <div class="event-details">
                                        <div class="event-title"><?php echo $event['title']; ?></div>
                                        <div class="event-info">
                                            <span><i class="fas fa-clock"></i> <?php echo $time; ?></span>
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo $event['location'] ?: 'TBA'; ?></span>
                                        </div>
                                        <div class="event-description"><?php echo substr($event['description'], 0, 100) . '...'; ?></div>
                                        <span class="event-status status-upcoming">Upcoming</span>
                                        <div class="event-actions">
                                            <button class="event-btn view" onclick="viewEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="event-btn edit" onclick="editEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="event-btn delete" onclick="deleteEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Past Events -->
                <div class="events-section reveal reveal-delay-1">
                    <div class="section-header">
                        <h3><i class="fas fa-history"></i> Past Events</h3>
                        <span class="event-count"><?php echo $past_count; ?></span>
                    </div>
                    <div class="events-list" id="pastEvents">
                        <?php if (empty($past_events)): ?>
                            <div class="empty-state">
                                <i class="fas fa-history"></i>
                                <h4>No Past Events</h4>
                                <p>Past events will appear here</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($past_events as $event): ?>
                                <?php
                                $date = new DateTime($event['event_date']);
                                $day = $date->format('d');
                                $month = $date->format('M');
                                $time = $date->format('h:i A');
                                $status_class = $event['status'] === 'completed' ? 'status-completed' : 'status-cancelled';
                                $status_text = $event['status'] === 'completed' ? 'Completed' : 'Cancelled';

                                // Get proper image URL for display
                                $display_image = '../images/IM41.jpg';
                                if ($event['image_url']) {
                                    if (strpos($event['image_url'], 'data:image') === 0) {
                                        // Base64 data - use default
                                        $display_image = '../images/IM41.jpg';
                                    } elseif (strpos($event['image_url'], 'uploads/') === 0) {
                                        // Uploaded file
                                        $display_image = '../' . $event['image_url'];
                                    } elseif (strpos($event['image_url'], 'http') === 0 || strpos($event['image_url'], './') === 0) {
                                        // External URL or relative path
                                        $display_image = $event['image_url'];
                                    }
                                }
                                ?>
                                <div class="event-card" data-id="<?php echo $event['id']; ?>">
                                    <div class="event-date">
                                        <div class="event-day"><?php echo $day; ?></div>
                                        <div class="event-month"><?php echo $month; ?></div>
                                    </div>
                                    <div class="event-details">
                                        <div class="event-title"><?php echo $event['title']; ?></div>
                                        <div class="event-info">
                                            <span><i class="fas fa-clock"></i> <?php echo $time; ?></span>
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo $event['location'] ?: 'TBA'; ?></span>
                                        </div>
                                        <div class="event-description"><?php echo substr($event['description'], 0, 100) . '...'; ?></div>
                                        <span class="event-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        <div class="event-actions">
                                            <button class="event-btn view" onclick="viewEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="event-btn edit" onclick="editEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="event-btn delete" onclick="deleteEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>&copy; 2025 Joseph's Pot Admin Dashboard. All rights reserved | Developed By ERIBS tech</p>
            </div>
        </div>

        <!-- Add Event Button -->
        <button class="add-event-btn" id="addEventBtn">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <!-- Add/Edit Event Modal -->
    <div class="modal" id="addEventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Create New Event</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="eventForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="eventAction" name="action" value="create">
                    <input type="hidden" id="eventId" name="id" value="">
                    <input type="hidden" id="imageBase64" name="image_base64" value="">

                    <div class="form-group">
                        <label for="eventTitle">Event Title *</label>
                        <input type="text" id="eventTitle" name="title" placeholder="Enter event title" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="eventDate">Event Date *</label>
                            <input type="datetime-local" id="eventDate" name="event_date" required>
                        </div>
                        <div class="form-group">
                            <label for="eventLocation">Location</label>
                            <input type="text" id="eventLocation" name="location" placeholder="Enter event location">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="eventDescription">Description *</label>
                        <textarea id="eventDescription" name="description" placeholder="Enter event description" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="eventImage">Event Image</label>
                        <input type="text" id="eventImage" name="image_url" placeholder="Enter image URL (optional)">
                        <p class="image-url-hint">Enter a URL or upload an image below</p>

                        <div class="image-preview-container">
                            <label class="image-upload-btn">
                                <i class="fas fa-upload"></i>
                                Upload Image
                                <input type="file" id="imageUpload" accept="image/*">
                            </label>
                            <img id="imagePreview" class="image-preview" src="" alt="Image Preview">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="eventStatus">Status *</label>
                        <select id="eventStatus" name="status" required>
                            <option value="">Select Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-plus"></i> Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // PHP DATA FOR JAVASCRIPT
        // ============================================
        const previewEvents = <?php echo json_encode($preview_events); ?>;
        const allEvents = <?php echo json_encode($all_events); ?>;
        let isRotationEnabled = <?php echo $auto_rotate_js; ?>;

        // ============================================
        // EVENT PREVIEW FUNCTIONALITY
        // ============================================
        let currentPreviewIndex = 0;
        let previewCountdownInterval = null;
        let previewAutoRotateInterval = null;

        // Format date as "15 Aug"
        function formatShortDate(dateStr) {
            const options = {
                day: "numeric",
                month: "short"
            };
            return new Date(dateStr).toLocaleDateString("en-US", options);
        }

        // Get day name from date
        function getDayName(dateStr) {
            const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            return days[new Date(dateStr).getDay()];
        }

        // Update preview display
        function updatePreviewDisplay(index) {
            if (previewEvents.length === 0) {
                document.getElementById('previewEventTitle').textContent = "No Upcoming Events";
                document.getElementById('previewEventDesc').textContent = "Check back soon for upcoming events!";
                document.getElementById('previewEventImage').src = "../images/IM41.jpg";
                document.getElementById('previewEventDay').textContent = "--";
                document.getElementById('previewEventDate').textContent = "--";
                document.getElementById('previewPrevEventBtn').style.display = "none";
                document.getElementById('previewNextEventBtn').style.display = "none";
                document.getElementById('previewSliderDots').innerHTML = "";
                return;
            }

            if (previewCountdownInterval) {
                clearInterval(previewCountdownInterval);
            }

            const event = previewEvents[index];
            const eventDate = new Date(event.event_date);
            const shortDate = formatShortDate(event.event_date);

            document.getElementById('previewEventTitle').textContent = event.title;
            document.getElementById('previewEventDesc').textContent = event.description;

            // Handle image URL properly for preview
            let imageUrl = event.image_url;

            // Check if it's base64 data
            if (imageUrl && imageUrl.startsWith('data:image')) {
                // Don't use base64 in preview - use default or check for file
                imageUrl = "../images/IM41.jpg";
            }

            // If it's a relative path from uploads, adjust
            if (imageUrl && imageUrl.startsWith('uploads/')) {
                imageUrl = '../' + imageUrl;
            }

            document.getElementById('previewEventImage').src = imageUrl || "../images/IM41.jpg";
            document.getElementById('previewEventDay').textContent = getDayName(event.event_date);
            document.getElementById('previewEventDate').textContent = shortDate;

            updatePreviewCountdown(event.event_date);
            previewCountdownInterval = setInterval(() => updatePreviewCountdown(event.event_date), 1000);
            updatePreviewSliderDots();
        }

        // Update countdown timer
        function updatePreviewCountdown(eventDateStr) {
            const now = new Date();
            const targetDate = new Date(eventDateStr);
            const diff = targetDate - now;

            const daysElement = document.querySelector('.days');
            const hoursElement = document.querySelector('.hours');
            const minutesElement = document.querySelector('.minutes');
            const secondsElement = document.querySelector('.seconds');

            if (!daysElement || !hoursElement || !minutesElement || !secondsElement) {
                initializeCountdownElements();
                return;
            }

            if (diff <= 0) {
                daysElement.textContent = '00';
                hoursElement.textContent = '00';
                minutesElement.textContent = '00';
                secondsElement.textContent = '00';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const minutes = Math.floor((diff / (1000 * 60)) % 60);
            const seconds = Math.floor((diff / 1000) % 60);

            daysElement.textContent = days.toString().padStart(2, "0");
            hoursElement.textContent = hours.toString().padStart(2, "0");
            minutesElement.textContent = minutes.toString().padStart(2, "0");
            secondsElement.textContent = seconds.toString().padStart(2, "0");
        }

        // Initialize countdown elements
        function initializeCountdownElements() {
            const countdownContainer = document.getElementById('previewCountdown');
            if (!countdownContainer) return;

            if (previewCountdownInterval) {
                clearInterval(previewCountdownInterval);
            }

            countdownContainer.innerHTML = `
                <div class="preview-countdown-item">
                    <span class="days preview-countdown-value">00</span>
                    <span class="preview-countdown-label">Days</span>
                </div>
                <div class="preview-countdown-separator">:</div>
                <div class="preview-countdown-item">
                    <span class="hours preview-countdown-value">00</span>
                    <span class="preview-countdown-label">Hours</span>
                </div>
                <div class="preview-countdown-separator">:</div>
                <div class="preview-countdown-item">
                    <span class="minutes preview-countdown-value">00</span>
                    <span class="preview-countdown-label">Min</span>
                </div>
                <div class="preview-countdown-separator">:</div>
                <div class="preview-countdown-item">
                    <span class="seconds preview-countdown-value">00</span>
                    <span class="preview-countdown-label">Sec</span>
                </div>
            `;
        }

        // Update slider dots
        function updatePreviewSliderDots() {
            const dotsContainer = document.getElementById('previewSliderDots');
            if (!dotsContainer) return;

            dotsContainer.innerHTML = '';

            previewEvents.forEach((_, index) => {
                const dot = document.createElement('div');
                dot.className = `preview-slider-dot ${index === currentPreviewIndex ? 'active' : ''}`;
                dot.addEventListener('click', () => {
                    switchToSlide(index);
                });
                dotsContainer.appendChild(dot);
            });
        }

        // Switch to specific slide
        function switchToSlide(index) {
            if (previewEvents.length === 0) return;

            if (index < 0) index = previewEvents.length - 1;
            if (index >= previewEvents.length) index = 0;

            if (index === currentPreviewIndex) return;

            currentPreviewIndex = index;
            updatePreviewDisplay(currentPreviewIndex);

            if (isRotationEnabled) {
                resetPreviewAutoRotation();
            }
        }

        // Navigation functions
        function nextSlide() {
            if (previewEvents.length === 0) return;
            const nextIndex = (currentPreviewIndex + 1) % previewEvents.length;
            switchToSlide(nextIndex);
        }

        function prevSlide() {
            if (previewEvents.length === 0) return;
            const prevIndex = (currentPreviewIndex - 1 + previewEvents.length) % previewEvents.length;
            switchToSlide(prevIndex);
        }

        // ============================================
        // AUTO-ROTATION TOGGLE (SAVES TO DATABASE)
        // ============================================
        async function toggleRotation() {
            const newState = !isRotationEnabled;
            isRotationEnabled = newState;
            
            const rotationToggle = document.getElementById('rotationToggle');
            const rotationStatus = document.getElementById('rotationStatus');
            
            // Update UI immediately
            if (isRotationEnabled) {
                rotationToggle.classList.add('active');
                rotationToggle.querySelector('.toggle-icon').innerHTML = '<i class="fas fa-play-circle"></i>';
                rotationToggle.querySelector('.toggle-text').textContent = 'Auto Rotate: ON';
                rotationStatus.textContent = ' Auto-rotation is currently enabled.';
                rotationStatus.style.color = 'var(--success)';
                startPreviewAutoRotation();
            } else {
                rotationToggle.classList.remove('active');
                rotationToggle.querySelector('.toggle-icon').innerHTML = '<i class="fas fa-pause-circle"></i>';
                rotationToggle.querySelector('.toggle-text').textContent = 'Auto Rotate: OFF';
                rotationStatus.textContent = ' Auto-rotation is currently disabled.';
                rotationStatus.style.color = 'var(--warning)';
                stopPreviewAutoRotation();
            }
            
            // Save setting to database
            try {
                const formData = new FormData();
                formData.append('toggle_auto_rotate', newState ? '1' : '0');
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    console.error('Failed to save auto-rotation setting');
                }
            } catch (error) {
                console.error('Error saving auto-rotation setting:', error);
            }
        }

        // Rotation control functions
        function startPreviewAutoRotation() {
            if (!isRotationEnabled || previewEvents.length <= 1) return;
            stopPreviewAutoRotation();
            previewAutoRotateInterval = setInterval(nextSlide, 3000);
        }

        function stopPreviewAutoRotation() {
            if (previewAutoRotateInterval) {
                clearInterval(previewAutoRotateInterval);
                previewAutoRotateInterval = null;
            }
        }

        function resetPreviewAutoRotation() {
            stopPreviewAutoRotation();
            startPreviewAutoRotation();
        }

        // ============================================
        // IMAGE UPLOAD HANDLING
        // ============================================
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const imageUrlInput = document.getElementById('eventImage');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Show preview
                    preview.src = e.target.result;
                    preview.classList.add('visible');

                    // Add base64 data to form
                    addBase64ToForm(e.target.result);

                    // Clear URL input since we're using uploaded file
                    imageUrlInput.value = '';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function addBase64ToForm(base64Data) {
            // Remove existing hidden input
            const existingBase64 = document.getElementById('imageBase64');
            if (existingBase64) {
                existingBase64.remove();
            }

            // Add new hidden input
            const hiddenBase64Input = document.createElement('input');
            hiddenBase64Input.type = 'hidden';
            hiddenBase64Input.name = 'image_base64';
            hiddenBase64Input.id = 'imageBase64';
            hiddenBase64Input.value = base64Data;

            document.getElementById('eventForm').appendChild(hiddenBase64Input);
        }

        // Update the image URL input when manually entered
        function updateImageUrlInput() {
            const imageUrlInput = document.getElementById('eventImage');
            const hiddenBase64Input = document.getElementById('imageBase64');
            const preview = document.getElementById('imagePreview');

            if (imageUrlInput.value && imageUrlInput.value.trim() !== '') {
                // If URL is entered manually, clear base64 data
                if (hiddenBase64Input) {
                    hiddenBase64Input.remove();
                }

                // Show preview of URL
                preview.src = imageUrlInput.value;
                preview.classList.add('visible');
            } else {
                preview.src = '';
                preview.classList.remove('visible');
            }
        }

        // ============================================
        // EVENT MANAGEMENT FUNCTIONS
        // ============================================
        function searchEvents() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const eventCards = document.querySelectorAll('.event-card');

            eventCards.forEach(card => {
                const title = card.querySelector('.event-title').textContent.toLowerCase();
                const description = card.querySelector('.event-description').textContent.toLowerCase();

                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function viewEvent(eventId) {
            const event = allEvents.find(e => e.id == eventId);
            if (event) {
                const date = new Date(event.event_date);
                const formattedDate = date.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Get proper image URL for display
                let imageSrc = '../images/IM41.jpg';
                if (event.image_url) {
                    if (event.image_url.startsWith('data:image')) {
                        imageSrc = '../images/IM41.jpg';
                    } else if (event.image_url.startsWith('uploads/')) {
                        imageSrc = '../' + event.image_url;
                    } else {
                        imageSrc = event.image_url;
                    }
                }

                // Create a nicer modal view
                const modalContent = `
                    <div style="padding: 20px; max-width: 600px;">
                        <h2 style="color: var(--primary); margin-bottom: 15px;">${event.title}</h2>
                        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                            <div style="flex: 1;">
                                <p><strong>Date:</strong> ${formattedDate}</p>
                                <p><strong>Location:</strong> ${event.location || 'TBA'}</p>
                                <p><strong>Status:</strong> ${event.status}</p>
                                <p><strong>Created:</strong> ${new Date(event.created_at).toLocaleDateString()}</p>
                                <p><strong>Last Updated:</strong> ${new Date(event.updated_at).toLocaleDateString()}</p>
                            </div>
                            <div style="flex: 1;">
                                <img src="${imageSrc}" alt="${event.title}" style="width: 100%; border-radius: 8px;">
                            </div>
                        </div>
                        <div style="background: #f5f5f5; padding: 15px; border-radius: 8px;">
                            <h3 style="margin-bottom: 10px;">Description</h3>
                            <p>${event.description}</p>
                        </div>
                    </div>
                `;

                // Show in alert or create a custom modal
                alert(`Event Details:\n\n` +
                    `Title: ${event.title}\n` +
                    `Date: ${formattedDate}\n` +
                    `Location: ${event.location || 'TBA'}\n` +
                    `Status: ${event.status}\n` +
                    `Description: ${event.description}\n` +
                    `Created: ${new Date(event.created_at).toLocaleDateString()}\n` +
                    `Last Updated: ${new Date(event.updated_at).toLocaleDateString()}`);
            }
        }

        function editEvent(eventId) {
            const event = allEvents.find(e => e.id == eventId);
            if (event) {
                // Populate form
                document.getElementById('modalTitle').textContent = 'Edit Event';
                document.getElementById('eventAction').value = 'update';
                document.getElementById('eventId').value = event.id;
                document.getElementById('eventTitle').value = event.title;

                // Format date for datetime-local input
                const date = new Date(event.event_date);
                const formattedDate = date.toISOString().slice(0, 16);
                document.getElementById('eventDate').value = formattedDate;

                document.getElementById('eventLocation').value = event.location || '';
                document.getElementById('eventDescription').value = event.description;
                document.getElementById('eventImage').value = event.image_url || '';
                document.getElementById('eventStatus').value = event.status;

                // Show image preview if exists
                const preview = document.getElementById('imagePreview');
                if (event.image_url) {
                    let previewSrc = event.image_url;
                    // Handle base64 images
                    if (event.image_url.startsWith('data:image')) {
                        previewSrc = '../images/IM41.jpg';
                    } else if (event.image_url.startsWith('uploads/')) {
                        previewSrc = '../' + event.image_url;
                    }
                    preview.src = previewSrc;
                    preview.classList.add('visible');
                } else {
                    preview.src = '';
                    preview.classList.remove('visible');
                }

                // Clear any existing base64 data
                const existingBase64 = document.getElementById('imageBase64');
                if (existingBase64) {
                    existingBase64.value = '';
                }

                // Update submit button
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';

                // Show modal
                showModal();
            }
        }

        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = eventId;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // ============================================
        // MODAL FUNCTIONS
        // ============================================
        function showModal() {
            const modal = document.getElementById('addEventModal');
            modal.style.display = 'flex';

            // Set minimum date to today
            const now = new Date();
            const minDate = now.toISOString().slice(0, 16);
            document.getElementById('eventDate').min = minDate;
        }

        function closeModal() {
            const modal = document.getElementById('addEventModal');
            modal.style.display = 'none';

            // Reset form
            document.getElementById('eventForm').reset();
            document.getElementById('modalTitle').textContent = 'Create New Event';
            document.getElementById('eventAction').value = 'create';
            document.getElementById('eventId').value = '';
            document.getElementById('imageBase64').value = '';
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus"></i> Create Event';

            // Clear image preview
            document.getElementById('imagePreview').src = '';
            document.getElementById('imagePreview').classList.remove('visible');
        }

        // ============================================
        // FORM SUBMISSION HANDLING
        // ============================================
        function validateEventForm() {
            const title = document.getElementById('eventTitle').value.trim();
            const eventDate = document.getElementById('eventDate').value;
            const description = document.getElementById('eventDescription').value.trim();
            const status = document.getElementById('eventStatus').value;

            if (!title) {
                alert('Please enter an event title');
                return false;
            }

            if (!eventDate) {
                alert('Please select an event date');
                return false;
            }

            if (!description) {
                alert('Please enter an event description');
                return false;
            }

            if (!status) {
                alert('Please select an event status');
                return false;
            }

            return true;
        }

        // ============================================
        // INITIALIZATION
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Real-time clock
            function updateClock() {
                const now = new Date();
                let hours = now.getHours();
                let minutes = now.getMinutes();
                let seconds = now.getSeconds();
                const ampm = hours >= 12 ? 'PM' : 'AM';

                hours = hours % 12;
                hours = hours ? hours : 12;

                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;

                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const dateString = now.toLocaleDateString('en-US', options);

                document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
                document.getElementById('currentDate').textContent = dateString;
            }

            updateClock();
            setInterval(updateClock, 1000);

            // Initialize preview
            if (previewEvents.length > 0) {
                updatePreviewDisplay(currentPreviewIndex);
                if (isRotationEnabled && previewEvents.length > 1) {
                    startPreviewAutoRotation();
                }
            } else {
                updatePreviewDisplay(0);
            }

            // Event listeners for preview navigation
            document.getElementById('previewPrevEventBtn').addEventListener('click', function() {
                prevSlide();
                if (isRotationEnabled) {
                    resetPreviewAutoRotation();
                }
            });

            document.getElementById('previewNextEventBtn').addEventListener('click', function() {
                nextSlide();
                if (isRotationEnabled) {
                    resetPreviewAutoRotation();
                }
            });

            // Rotation toggle
            document.getElementById('rotationToggle').addEventListener('click', toggleRotation);

            // Image upload handling
            document.getElementById('imageUpload').addEventListener('change', function() {
                previewImage(this);
            });

            // Image URL input handling
            document.getElementById('eventImage').addEventListener('input', updateImageUrlInput);

            // Modal functionality
            document.getElementById('addEventBtn').addEventListener('click', showModal);
            document.getElementById('closeModal').addEventListener('click', closeModal);
            document.getElementById('cancelBtn').addEventListener('click', closeModal);

            // Form submission
            document.getElementById('eventForm').addEventListener('submit', function(e) {
                if (!validateEventForm()) {
                    e.preventDefault();
                    return false;
                }

                // If no image provided, ensure we don't send base64
                const imageUrl = document.getElementById('eventImage').value.trim();
                const base64Input = document.getElementById('imageBase64');

                if (!imageUrl && (!base64Input || !base64Input.value)) {
                    // No image provided, that's okay
                    if (!base64Input) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'image_base64';
                        hiddenInput.value = '';
                        this.appendChild(hiddenInput);
                    }
                }

                return true;
            });

            // Mobile sidebar
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });

            // Close sidebar on menu item click (mobile)
            const menuItems = document.querySelectorAll('.menu-item a');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.remove('active');
                        sidebarOverlay.classList.remove('active');
                    }
                });
            });

            // Logout confirmation
            window.confirmLogout = function() {
                return confirm('Are you sure you want to logout?');
            };

            // Scroll reveal
            function revealOnScroll() {
                const reveals = document.querySelectorAll('.reveal');
                for (let i = 0; i < reveals.length; i++) {
                    const windowHeight = window.innerHeight;
                    const elementTop = reveals[i].getBoundingClientRect().top;
                    const elementVisible = 150;

                    if (elementTop < windowHeight - elementVisible) {
                        reveals[i].classList.add('active');
                    }
                }
            }

            window.addEventListener('scroll', revealOnScroll);
            revealOnScroll();
        });
    </script>
</body>

</html>