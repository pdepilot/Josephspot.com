<?php
// Start session to get admin info
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

// Database Configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'joseph_pot_admin');
}

// Get database connection
function getUnauthDBConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                return null;
            }
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            return null;
        }
    }
    return $conn;
}

// Get current admin role
$admin_role = null;
$admin_name = 'Admin';
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role'])) {
    $admin_role = $_SESSION['admin_role'];
    $admin_name = isset($_SESSION['admin_full_name']) ? $_SESSION['admin_full_name'] : (isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin');
}

// Map role display names to database values
$role_map = [
    'Super Admin' => 'super_admin',
    'Manager' => 'manager',
    'Content Manager' => 'content_editor',
    'Support' => 'support',
    'Admin' => 'admin'
];

$db_role = isset($role_map[$admin_role]) ? $role_map[$admin_role] : $admin_role;

// Get authorized pages for current admin
$authorized_pages = [];
if ($admin_role && $db_role) {
    try {
        $conn = getUnauthDBConnection();
        if ($conn) {
            // Check if admin_permissions table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'admin_permissions'");
            if ($table_check && $table_check->num_rows > 0) {
                // Get all modules where admin has view permission
                // Use the admin-auth.php function if available
                if (file_exists(__DIR__ . '/admin-auth.php')) {
                    require_once __DIR__ . '/admin-auth.php';
                }
                
                $stmt = $conn->prepare("SELECT DISTINCT module FROM admin_permissions WHERE role = ? AND (permission = 'view' OR permission = 'all')");
                if ($stmt) {
                    $stmt->bind_param("s", $db_role);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $authorized_pages[] = $row['module'];
                    }
                    $stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        // Silently fail
    }
}

// If Super Admin, show all pages
if ($admin_role === 'Super Admin' || $admin_role === 'super_admin') {
    $authorized_pages = ['dashboard', 'orders', 'reservations', 'menu_management', 'contact_messages', 'reviews', 'events', 'gallery', 'settings', 'admin_management', 'order_online_menu', 'customers'];
}

// Map modules to page names and URLs
$module_to_page = [
    'dashboard' => ['name' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fa-home', 'category' => 'Main'],
    'orders' => ['name' => 'Orders', 'url' => 'admin-orders.php', 'icon' => 'fa-shopping-cart', 'category' => 'Main'],
    'reservations' => ['name' => 'Reservations', 'url' => 'admin-reservation.php', 'icon' => 'fa-calendar-alt', 'category' => 'Main'],
    'menu_management' => ['name' => 'Menu Management', 'url' => 'admin-menu-management.php', 'icon' => 'fa-utensils', 'category' => 'Main'],
    'contact_messages' => ['name' => 'Contact Messages', 'url' => 'admin-contact-messages.php', 'icon' => 'fa-envelope', 'category' => 'Main'],
    'order_online_menu' => ['name' => 'Order Online Menu', 'url' => 'admin-order-online-menu.php', 'icon' => 'fa-car', 'category' => 'Main'],
    'reviews' => ['name' => 'Reviews', 'url' => 'admin-reviews.php', 'icon' => 'fa-star', 'category' => 'Content'],
    'events' => ['name' => 'Events', 'url' => 'admin-events.php', 'icon' => 'fa-calendar', 'category' => 'Content'],
    'gallery' => ['name' => 'Gallery', 'url' => 'admin-gallery.php', 'icon' => 'fa-image', 'category' => 'Content'],
    'settings' => ['name' => 'Settings', 'url' => 'admin-settings.php', 'icon' => 'fa-cog', 'category' => 'Account'],
    'admin_management' => ['name' => 'Admin Management', 'url' => 'dashboard.php#admin-management', 'icon' => 'fa-user-plus', 'category' => 'Account'],
    'customers' => ['name' => 'Customers', 'url' => 'admin-customers.php', 'icon' => 'fa-users', 'category' => 'Main'],
];

// Group authorized pages by category
$pages_by_category = [];
foreach ($authorized_pages as $module) {
    if (isset($module_to_page[$module])) {
        $category = $module_to_page[$module]['category'];
        if (!isset($pages_by_category[$category])) {
            $pages_by_category[$category] = [];
        }
        $pages_by_category[$category][] = $module_to_page[$module];
    }
}

$module = isset($_GET['module']) ? htmlspecialchars($_GET['module']) : '';
$module_display = '';
if ($module && isset($module_to_page[$module])) {
    $module_display = $module_to_page[$module]['name'];
} elseif ($module) {
    $module_display = ucwords(str_replace('_', ' ', $module));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Joseph's Pot Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .unauthorized-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            max-width: 900px;
            margin: 0 auto;
        }

        .header-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .icon-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(244, 67, 54, 0.3);
        }

        .icon-container i {
            font-size: 60px;
            color: white;
        }

        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        h2 {
            color: #666;
            font-size: 1.5rem;
            margin-bottom: 20px;
            font-weight: 500;
        }

        p {
            color: #888;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .role-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 30px;
            color: #1565c0;
            text-align: center;
        }

        .module-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 30px;
            color: #856404;
            text-align: center;
        }

        .authorized-pages-section {
            margin-top: 40px;
        }

        .authorized-pages-section h3 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .category-group {
            margin-bottom: 30px;
        }

        .category-title {
            color: #8b4513;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #8b4513;
        }

        .pages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .page-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: #8b4513;
            color: white;
            border-color: #8b4513;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.3);
        }

        .page-link i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .btn {
            padding: 15px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8b4513 0%, #a0522d 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 69, 19, 0.4);
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            border: 2px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
            border-color: #bbb;
        }

        .no-pages {
            text-align: center;
            color: #999;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        @media (max-width: 600px) {
            .unauthorized-container {
                padding: 40px 20px;
            }

            h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 1.2rem;
            }

            .pages-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="unauthorized-container">
        <div class="header-section">
            <div class="icon-container">
            <i class="fas fa-lock"></i>
        </div>
            <h1>403</h1>
            <h2>Access Denied</h2>
            <p>Sorry, you don't have permission to access this page.</p>
            
            <?php if ($admin_role): ?>
            <div class="role-info">
                <i class="fas fa-user-shield"></i> <strong>Your Role:</strong> <?php echo htmlspecialchars($admin_role); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($module_display): ?>
            <div class="module-info">
                <i class="fas fa-info-circle"></i> You attempted to access: <strong><?php echo $module_display; ?></strong>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($pages_by_category)): ?>
        <div class="authorized-pages-section">
            <h3>Pages You Can Access</h3>
            
            <?php 
            $category_order = ['Main', 'Content', 'Account'];
            foreach ($category_order as $cat): 
                if (isset($pages_by_category[$cat])): 
            ?>
            <div class="category-group">
                <div class="category-title"><?php echo $cat; ?></div>
                <div class="pages-grid">
                    <?php foreach ($pages_by_category[$cat] as $page): ?>
                    <a href="<?php echo htmlspecialchars($page['url']); ?>" class="page-link">
                        <i class="fas <?php echo htmlspecialchars($page['icon']); ?>"></i>
                        <span><?php echo htmlspecialchars($page['name']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
            <?php else: ?>
        <div class="no-pages">
            <p>No authorized pages found. Please contact your administrator.</p>
        </div>
            <?php endif; ?>
        
        <div class="button-group">
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Go to Dashboard
                </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </a>
        </div>
    </div>
</body>
</html>
