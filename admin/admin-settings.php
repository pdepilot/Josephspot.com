<?php
// admin-settings.php
// Central authentication and permission check
require_once 'admin-auth.php';
checkPageAccess(); // This checks authentication and permission for current page

// Database Configuration (only define if not already defined by admin-auth.php)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'joseph_pot_admin');
}

// PDO Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user is logged in (legacy function - now uses admin-auth.php)
function isLoggedIn()
{
    return isAdminLoggedIn();
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
        // Use admin_users table (new system) instead of admins table
        $stmt = $pdo->query("SELECT id, username, email, full_name, role, created_at FROM admin_users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log('Error fetching admins: ' . $e->getMessage());
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

// ============================================================================
// BACKEND HANDLERS - Admin Settings Form Processing
// ============================================================================

// Handle POST requests for admin settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    // Ensure user is logged in
    if (!isLoggedIn()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $action = $_POST['action'];
    $response = ['success' => false, 'message' => 'Unknown action'];

    try {
        switch ($action) {
            case 'update_restaurant_info':
                $response = admin_update_restaurant_info($pdo);
                break;
            case 'update_notifications':
                $response = admin_update_notifications($pdo, $_SESSION['admin_id']);
                break;
            case 'update_security':
                $response = admin_update_security($pdo, $_SESSION['admin_id']);
                break;
            case 'change_password':
                $response = admin_change_password($pdo, $_SESSION['admin_id']);
                break;
            case 'add_user':
                $response = admin_add_user($pdo, $_SESSION['admin_id']);
                break;
            case 'toggle_user_status':
                $response = admin_toggle_user_status($pdo, $_SESSION['admin_id']);
                break;
            case 'reset_user_password':
                $response = admin_reset_user_password($pdo, $_SESSION['admin_id']);
                break;
            case 'delete_user':
                $response = admin_delete_user($pdo, $_SESSION['admin_id']);
                break;
            case 'create_backup':
                $response = admin_create_backup($pdo);
                break;
            case 'restore_backup':
                $response = admin_restore_backup($pdo);
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

// ============================================================================
// ADMIN SETTINGS FUNCTIONS
// ============================================================================

/**
 * Update restaurant information
 */
function admin_update_restaurant_info($pdo) {
    try {
        $restaurant_name = isset($_POST['restaurant_name']) ? trim($_POST['restaurant_name']) : '';
        $restaurant_tagline = isset($_POST['restaurant_tagline']) ? trim($_POST['restaurant_tagline']) : '';
        $restaurant_address = isset($_POST['restaurant_address']) ? trim($_POST['restaurant_address']) : '';
        $restaurant_phone = isset($_POST['restaurant_phone']) ? trim($_POST['restaurant_phone']) : '';
        $restaurant_email = isset($_POST['restaurant_email']) ? trim($_POST['restaurant_email']) : '';
        $opening_hours = isset($_POST['opening_hours']) ? trim($_POST['opening_hours']) : '';
        $logo_path = isset($_POST['logo_path']) ? trim($_POST['logo_path']) : '';

        if (empty($restaurant_name)) {
            return ['success' => false, 'message' => 'Restaurant name is required'];
        }

        if (!empty($restaurant_email) && !filter_var($restaurant_email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }

        $pdo->beginTransaction();

        set_setting($pdo, 'restaurant', 'restaurant_name', $restaurant_name);
        set_setting($pdo, 'restaurant', 'restaurant_tagline', $restaurant_tagline);
        set_setting($pdo, 'restaurant', 'restaurant_address', $restaurant_address);
        set_setting($pdo, 'restaurant', 'restaurant_phone', $restaurant_phone);
        set_setting($pdo, 'restaurant', 'restaurant_email', $restaurant_email);
        set_setting($pdo, 'restaurant', 'opening_hours', $opening_hours);
        
        if (!empty($logo_path)) {
            set_setting($pdo, 'restaurant', 'logo_path', $logo_path);
        }

        $pdo->commit();

        return ['success' => true, 'message' => 'Restaurant information updated successfully'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update notification settings
 */
function admin_update_notifications($pdo, $admin_id) {
    try {
        $email_orders = isset($_POST['email_orders']) && ($_POST['email_orders'] === '1' || $_POST['email_orders'] === 'true' || $_POST['email_orders'] === true) ? '1' : '0';
        $email_reservations = isset($_POST['email_reservations']) && ($_POST['email_reservations'] === '1' || $_POST['email_reservations'] === 'true' || $_POST['email_reservations'] === true) ? '1' : '0';
        $email_reviews = isset($_POST['email_reviews']) && ($_POST['email_reviews'] === '1' || $_POST['email_reviews'] === 'true' || $_POST['email_reviews'] === true) ? '1' : '0';
        $email_promotions = isset($_POST['email_promotions']) && ($_POST['email_promotions'] === '1' || $_POST['email_promotions'] === 'true' || $_POST['email_promotions'] === true) ? '1' : '0';
        $push_orders = isset($_POST['push_orders']) && ($_POST['push_orders'] === '1' || $_POST['push_orders'] === 'true' || $_POST['push_orders'] === true) ? '1' : '0';
        $push_reservations = isset($_POST['push_reservations']) && ($_POST['push_reservations'] === '1' || $_POST['push_reservations'] === 'true' || $_POST['push_reservations'] === true) ? '1' : '0';
        $push_low_stock = isset($_POST['push_low_stock']) && ($_POST['push_low_stock'] === '1' || $_POST['push_low_stock'] === 'true' || $_POST['push_low_stock'] === true) ? '1' : '0';
        $notification_sound = isset($_POST['notification_sound']) ? trim($_POST['notification_sound']) : 'default';

        $pdo->beginTransaction();

        set_notification_setting($pdo, 'email_orders', $email_orders, $admin_id);
        set_notification_setting($pdo, 'email_reservations', $email_reservations, $admin_id);
        set_notification_setting($pdo, 'email_reviews', $email_reviews, $admin_id);
        set_notification_setting($pdo, 'email_promotions', $email_promotions, $admin_id);
        set_notification_setting($pdo, 'push_orders', $push_orders, $admin_id);
        set_notification_setting($pdo, 'push_reservations', $push_reservations, $admin_id);
        set_notification_setting($pdo, 'push_low_stock', $push_low_stock, $admin_id);
        set_notification_setting($pdo, 'notification_sound', $notification_sound, $admin_id);

        $pdo->commit();

        return ['success' => true, 'message' => 'Notification settings updated successfully'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update security settings
 */
function admin_update_security($pdo, $admin_id) {
    try {
        $password_min_length = isset($_POST['password_min_length']) ? intval($_POST['password_min_length']) : 8;
        $password_require_uppercase = isset($_POST['password_require_uppercase']) && ($_POST['password_require_uppercase'] === '1' || $_POST['password_require_uppercase'] === 'true') ? '1' : '0';
        $password_require_lowercase = isset($_POST['password_require_lowercase']) && ($_POST['password_require_lowercase'] === '1' || $_POST['password_require_lowercase'] === 'true') ? '1' : '0';
        $password_require_numbers = isset($_POST['password_require_numbers']) && ($_POST['password_require_numbers'] === '1' || $_POST['password_require_numbers'] === 'true') ? '1' : '0';
        $password_require_special = isset($_POST['password_require_special']) && ($_POST['password_require_special'] === '1' || $_POST['password_require_special'] === 'true') ? '1' : '0';
        $session_timeout = isset($_POST['session_timeout']) ? intval($_POST['session_timeout']) : 30;
        $two_factor_auth = isset($_POST['two_factor_auth']) && ($_POST['two_factor_auth'] === '1' || $_POST['two_factor_auth'] === 'true') ? '1' : '0';

        if ($password_min_length < 6 || $password_min_length > 20) {
            return ['success' => false, 'message' => 'Password minimum length must be between 6 and 20'];
        }

        if ($session_timeout < 5 || $session_timeout > 240) {
            return ['success' => false, 'message' => 'Session timeout must be between 5 and 240 minutes'];
        }

        $pdo->beginTransaction();

        set_setting($pdo, 'security', 'password_min_length', strval($password_min_length));
        set_setting($pdo, 'security', 'password_require_uppercase', $password_require_uppercase);
        set_setting($pdo, 'security', 'password_require_lowercase', $password_require_lowercase);
        set_setting($pdo, 'security', 'password_require_numbers', $password_require_numbers);
        set_setting($pdo, 'security', 'password_require_special', $password_require_special);
        set_setting($pdo, 'security', 'session_timeout', strval($session_timeout));
        set_setting($pdo, 'security', 'two_factor_auth', $two_factor_auth);

        // Log security settings update
        admin_log_security_action($pdo, $admin_id, 'security_settings_updated', 'Security settings updated');

        $pdo->commit();

        return ['success' => true, 'message' => 'Security settings updated successfully'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Change admin password
 */
function admin_change_password($pdo, $admin_id) {
    try {
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            return ['success' => false, 'message' => 'All password fields are required'];
        }

        if ($new_password !== $confirm_password) {
            return ['success' => false, 'message' => 'New passwords do not match'];
        }

        // Get current admin data
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();

        if (!$admin) {
            return ['success' => false, 'message' => 'Admin not found'];
        }

        // Verify current password
        if (!password_verify($current_password, $admin['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Validate new password
        $min_length = get_setting($pdo, 'security', 'password_min_length', '8');
        if (strlen($new_password) < intval($min_length)) {
            return ['success' => false, 'message' => 'Password must be at least ' . $min_length . ' characters long'];
        }

        // Validate password requirements
        $require_uppercase = get_setting($pdo, 'security', 'password_require_uppercase', '1') === '1';
        $require_lowercase = get_setting($pdo, 'security', 'password_require_lowercase', '1') === '1';
        $require_numbers = get_setting($pdo, 'security', 'password_require_numbers', '1') === '1';
        $require_special = get_setting($pdo, 'security', 'password_require_special', '0') === '1';

        if ($require_uppercase && !preg_match('/[A-Z]/', $new_password)) {
            return ['success' => false, 'message' => 'Password must contain at least one uppercase letter'];
        }
        if ($require_lowercase && !preg_match('/[a-z]/', $new_password)) {
            return ['success' => false, 'message' => 'Password must contain at least one lowercase letter'];
        }
        if ($require_numbers && !preg_match('/[0-9]/', $new_password)) {
            return ['success' => false, 'message' => 'Password must contain at least one number'];
        }
        if ($require_special && !preg_match('/[^A-Za-z0-9]/', $new_password)) {
            return ['success' => false, 'message' => 'Password must contain at least one special character'];
        }

        // Hash new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $pdo->beginTransaction();

        // Update password
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$new_password_hash, $admin_id]);

        // Log password change
        admin_log_security_action($pdo, $admin_id, 'password_changed', 'Admin password changed');

        $pdo->commit();

        return ['success' => true, 'message' => 'Password changed successfully'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Map role from form value to database value
 */
function mapRoleToDBValue($role) {
    // If already in display format, return as is
    $valid_roles = ['Super Admin', 'Manager', 'Content Manager', 'Support', 'Admin'];
    if (in_array($role, $valid_roles)) {
        return $role; // Store exact name
    }
    
    // Map form values and legacy values to database format
    $roleMap = [
        // Form values (from dropdown)
        'super_admin' => 'Super Admin',
        'manager' => 'Manager',  // Form sends 'manager', not 'admin'
        'content_manager' => 'Content Manager',  // Form sends 'content_manager', not 'content_editor'
        'support' => 'Support',  // Form sends 'support', not 'moderator'
        
        // Legacy mapping for backward compatibility
        'admin' => 'Manager',
        'moderator' => 'Support',
        'content_editor' => 'Content Manager'
    ];
    
    // Normalize the role value (trim and lowercase for comparison)
    $role_normalized = strtolower(trim($role));
    
    return isset($roleMap[$role_normalized]) ? $roleMap[$role_normalized] : 'Manager';
}

/**
 * Add new admin user
 */
function admin_add_user($pdo, $current_admin_id) {
    try {
        // Check if current user is super_admin (check admin_users table)
        $stmt = $pdo->prepare("SELECT role FROM admin_users WHERE id = ?");
        $stmt->execute([$current_admin_id]);
        $current_admin = $stmt->fetch();

        if (!$current_admin || ($current_admin['role'] !== 'super_admin' && $current_admin['role'] !== 'Super Admin')) {
            return ['success' => false, 'message' => 'Only super admins can add users'];
        }

        // Get form data - handle both 'name' and 'username' fields
        $name = isset($_POST['name']) ? trim($_POST['name']) : (isset($_POST['username']) ? trim($_POST['username']) : '');
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? trim($_POST['role']) : 'manager';

        // Debug: Log POST data
        error_log("DEBUG admin_add_user POST data: " . print_r(['role' => $role, 'name' => $name, 'email' => $email], true));

        if (empty($name) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Name, email, and password are required'];
        }
        
        if (empty($role)) {
            return ['success' => false, 'message' => 'Role is required'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }

        // Generate username from name if not provided separately
        $username = strtolower(str_replace(' ', '_', $name));
        
        // Map role to database format (Super Admin, Manager, Content Manager, Support)
        $dbRole = mapRoleToDBValue($role);
        
        // Debug: Log role mapping
        error_log("DEBUG admin_add_user: Form role='$role', Mapped role='$dbRole'");
        
        // Validate role is one of the allowed values
        $allowed_roles = ['Super Admin', 'Manager', 'Content Manager', 'Support', 'Admin'];
        if (!in_array($dbRole, $allowed_roles)) {
            error_log("DEBUG admin_add_user: Invalid role '$dbRole' not in allowed list");
            return ['success' => false, 'message' => 'Invalid role selected: ' . $dbRole];
        }

        // Validate password
        $min_length = get_setting($pdo, 'security', 'password_min_length', '8');
        if (strlen($password) < intval($min_length)) {
            return ['success' => false, 'message' => 'Password must be at least ' . $min_length . ' characters long'];
        }

        // Check if username already exists in admin_users table
        $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Check if email already exists in admin_users table
        $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $pdo->beginTransaction();

        // Insert new admin into admin_users table with mapped role
        // Debug: Log the values being inserted
        error_log("DEBUG admin_add_user: Inserting admin with role='$dbRole' (original role from form='$role')");
        error_log("DEBUG admin_add_user: username='$username', email='$email', full_name='$name'");
        
        // Prepare INSERT statement - check which status column exists
        // First check if 'status' column exists (preferred)
        $testStmt = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'status'");
        $hasStatus = $testStmt && $testStmt->rowCount() > 0;
        
        if ($hasStatus) {
            $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, ?, 'active')";
        } else {
            // Check if is_active exists
            $testStmt2 = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'is_active'");
            $hasIsActive = $testStmt2 && $testStmt2->rowCount() > 0;
            
            if ($hasIsActive) {
                $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)";
            } else {
                // No status column, just insert without it
                $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)";
            }
        }
        
        error_log("DEBUG admin_add_user: Using SQL: $insertSql");
        error_log("DEBUG admin_add_user: Parameters: username='$username', email='$email', name='$name', role='$dbRole'");
        
        $stmt = $pdo->prepare($insertSql);
        
        if (!$stmt) {
            $pdo->rollBack();
            $errorInfo = $pdo->errorInfo();
            error_log("DEBUG admin_add_user: Prepare failed: " . print_r($errorInfo, true));
            return ['success' => false, 'message' => 'Database prepare error: ' . ($errorInfo[2] ?? 'Unknown error')];
        }
        
        // Execute with parameters - make sure dbRole is not empty
        if (empty($dbRole)) {
            $pdo->rollBack();
            error_log("ERROR admin_add_user: dbRole is empty! Original role was: '$role'");
            return ['success' => false, 'message' => 'Role cannot be empty. Please select a valid role.'];
        }
        
        $result = $stmt->execute([$username, $email, $password_hash, $name, $dbRole]);
        
        if (!$result) {
            $pdo->rollBack();
            $errorInfo = $stmt->errorInfo();
            error_log("DEBUG admin_add_user: SQL execution failed: " . print_r($errorInfo, true));
            return ['success' => false, 'message' => 'Database error: ' . ($errorInfo[2] ?? 'Unknown error')];
        }
        
        $adminId = $pdo->lastInsertId();
        error_log("DEBUG admin_add_user: Successfully inserted admin with ID=$adminId, role='$dbRole'");
        
        // Verify the role was actually saved to the database
        $verifyStmt = $pdo->prepare("SELECT role FROM admin_users WHERE id = ?");
        $verifyStmt->execute([$adminId]);
        $savedAdmin = $verifyStmt->fetch();
        if ($savedAdmin) {
            error_log("DEBUG admin_add_user: Verified saved role='{$savedAdmin['role']}' for admin ID=$adminId");
            if ($savedAdmin['role'] !== $dbRole) {
                error_log("ERROR admin_add_user: Role mismatch! Expected '$dbRole', but saved '{$savedAdmin['role']}'");
                $pdo->rollBack();
                return ['success' => false, 'message' => 'Role was not saved correctly. Expected: ' . $dbRole . ', Saved: ' . $savedAdmin['role']];
            }
        } else {
            error_log("ERROR admin_add_user: Could not verify saved admin with ID=$adminId");
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Failed to verify admin was saved'];
        }

        // Log user creation (if security_logs table exists)
        try {
            admin_log_security_action($pdo, $current_admin_id, 'user_created', 'New admin user created: ' . $username);
        } catch (Exception $e) {
            // Ignore logging errors
        }

        $pdo->commit();

        return ['success' => true, 'message' => 'Admin user added successfully', 'admin_id' => $adminId];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Check for duplicate email error
        if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        error_log('Error adding admin user: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Toggle user active/inactive status
 */
function admin_toggle_user_status($pdo, $current_admin_id) {
    try {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $is_active = isset($_POST['is_active']) && ($_POST['is_active'] === '1' || $_POST['is_active'] === 'true' || $_POST['is_active'] === true) ? 1 : 0;

        if ($user_id <= 0) {
            return ['success' => false, 'message' => 'Invalid user ID'];
        }

        // Prevent self-deactivation
        if ($user_id == $current_admin_id && $is_active == 0) {
            return ['success' => false, 'message' => 'You cannot deactivate your own account'];
        }

        // Get user info for logging
        $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $pdo->beginTransaction();

        // Update status
        $stmt = $pdo->prepare("UPDATE admins SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $user_id]);

        // Log action
        $action = $is_active ? 'user_activated' : 'user_deactivated';
        $message = 'User ' . ($is_active ? 'activated' : 'deactivated') . ': ' . $user['username'];
        admin_log_security_action($pdo, $current_admin_id, $action, $message);

        $pdo->commit();

        return ['success' => true, 'message' => 'User status updated successfully'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Reset user password
 */
function admin_reset_user_password($pdo, $current_admin_id) {
    try {
        // Check if current user is super_admin
        $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
        $stmt->execute([$current_admin_id]);
        $current_admin = $stmt->fetch();

        if (!$current_admin || $current_admin['role'] !== 'super_admin') {
            return ['success' => false, 'message' => 'Only super admins can reset passwords'];
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';

        if ($user_id <= 0 || empty($new_password)) {
            return ['success' => false, 'message' => 'User ID and new password are required'];
        }

        // Validate password
        $min_length = get_setting($pdo, 'security', 'password_min_length', '8');
        if (strlen($new_password) < intval($min_length)) {
            return ['success' => false, 'message' => 'Password must be at least ' . $min_length . ' characters long'];
        }

        // Get user info
        $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Hash new password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $pdo->beginTransaction();

        // Update password
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$password_hash, $user_id]);

        // Log password reset
        admin_log_security_action($pdo, $current_admin_id, 'password_reset', 'Password reset for user: ' . $user['username']);

        $pdo->commit();

        return ['success' => true, 'message' => 'Password reset successfully'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Delete user (prevent self-deletion)
 */
function admin_delete_user($pdo, $current_admin_id) {
    try {
        // Check if current user is super_admin
        $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
        $stmt->execute([$current_admin_id]);
        $current_admin = $stmt->fetch();

        if (!$current_admin || $current_admin['role'] !== 'super_admin') {
            return ['success' => false, 'message' => 'Only super admins can delete users'];
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

        if ($user_id <= 0) {
            return ['success' => false, 'message' => 'Invalid user ID'];
        }

        // Prevent self-deletion
        if ($user_id == $current_admin_id) {
            return ['success' => false, 'message' => 'You cannot delete your own account'];
        }

        // Get user info for logging
        $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $pdo->beginTransaction();

        // Delete user
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$user_id]);

        // Log deletion
        admin_log_security_action($pdo, $current_admin_id, 'user_deleted', 'User deleted: ' . $user['username']);

        $pdo->commit();

        return ['success' => true, 'message' => 'User deleted successfully'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Create database backup
 */
function admin_create_backup($pdo) {
    try {
        // Check if backup directory exists or create it
        $backup_dir = __DIR__ . '/../backups';
        if (!is_dir($backup_dir)) {
            if (!mkdir($backup_dir, 0755, true)) {
                return ['success' => false, 'message' => 'Cannot create backup directory'];
            }
        }

        // Check if directory is writable
        if (!is_writable($backup_dir)) {
            return ['success' => false, 'message' => 'Backup directory is not writable'];
        }

        // Generate backup filename
        $timestamp = date('Y-m-d_H-i-s');
        $backup_file = $backup_dir . '/backup_' . $timestamp . '.sql';

        // Get all tables
        $tables = [];
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        if (empty($tables)) {
            return ['success' => false, 'message' => 'No tables found to backup'];
        }

        // Start backup file
        $output = "-- Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: " . DB_NAME . "\n\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET time_zone = \"+00:00\";\n\n";

        // Backup each table
        foreach ($tables as $table) {
            // Get table structure
            $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $create_table = $stmt->fetch(PDO::FETCH_ASSOC);
            $output .= "\n-- Table structure for table `{$table}`\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $create_table['Create Table'] . ";\n\n";

            // Get table data
            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $output .= "-- Dumping data for table `{$table}`\n";
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = $pdo->quote($value);
                        }
                    }
                    $output .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }

        // Write backup file
        if (file_put_contents($backup_file, $output) === false) {
            return ['success' => false, 'message' => 'Failed to write backup file'];
        }

        // Store backup metadata in database (if table exists)
        // TODO: Adjust table name based on actual backup metadata table structure
        try {
            $stmt = $pdo->prepare("INSERT INTO admin_settings_meta (meta_key, meta_value, meta_type, created_at) VALUES (?, ?, 'backup', NOW())");
            $meta_value = json_encode([
                'filename' => basename($backup_file),
                'size' => filesize($backup_file),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $stmt->execute([basename($backup_file), $meta_value]);
        } catch (PDOException $e) {
            // If table doesn't exist, just continue - backup file was created successfully
            error_log("Backup metadata table not found: " . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Backup created successfully', 'filename' => basename($backup_file)];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Backup error: ' . $e->getMessage()];
    }
}

/**
 * Restore database backup
 */
function admin_restore_backup($pdo) {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No backup file uploaded or upload error'];
        }

        $uploaded_file = $_FILES['backup_file']['tmp_name'];
        $filename = $_FILES['backup_file']['name'];

        // Validate file type
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($file_ext !== 'sql') {
            return ['success' => false, 'message' => 'Invalid file type. Only .sql files are allowed'];
        }

        // Validate file size (max 50MB)
        if ($_FILES['backup_file']['size'] > 50 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File size exceeds 50MB limit'];
        }

        // Read backup file
        $sql_content = file_get_contents($uploaded_file);
        if ($sql_content === false) {
            return ['success' => false, 'message' => 'Failed to read backup file'];
        }

        // Security check: Prevent execution of dangerous operations
        $dangerous_patterns = [
            '/DROP\s+DATABASE/i',
            '/CREATE\s+DATABASE/i',
            '/USE\s+\w+/i',
        ];
        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $sql_content)) {
                return ['success' => false, 'message' => 'Backup file contains unsafe SQL operations'];
            }
        }

        // Split SQL statements
        $statements = array_filter(array_map('trim', explode(';', $sql_content)));

        $pdo->beginTransaction();

        try {
            // Execute each SQL statement
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $pdo->exec($statement);
                }
            }

            $pdo->commit();

            // Log restore action
            admin_log_security_action($pdo, $_SESSION['admin_id'], 'backup_restored', 'Backup restored: ' . $filename);

            return ['success' => true, 'message' => 'Backup restored successfully'];
        } catch (PDOException $e) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Restore error: ' . $e->getMessage()];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Restore error: ' . $e->getMessage()];
    }
}

/**
 * Log security-related actions
 * TODO: Adjust table name and structure based on actual security logs table
 */
function admin_log_security_action($pdo, $admin_id, $action_type, $description) {
    try {
        // Try to log to security_logs table if it exists
        $stmt = $pdo->prepare("
            INSERT INTO security_logs (admin_id, action_type, description, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $stmt->execute([$admin_id, $action_type, $description, $ip_address, $user_agent]);
    } catch (PDOException $e) {
        // If table doesn't exist, just log to error log
        error_log("Security log entry failed (table may not exist): " . $e->getMessage() . " - Action: {$action_type}, Admin ID: {$admin_id}, Description: {$description}");
    }
}

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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .modal.active {
            display: flex;
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
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray);
        }

        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
            transition: var(--transition);
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            color: var(--primary);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 0;
        }

        .modal-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--gray);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
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
                                        // Use full_name if available, otherwise use username
                                        $display_name = !empty($admin['full_name']) ? $admin['full_name'] : $admin['username'];
                                        $admin_initials = strtoupper(substr($display_name, 0, 2));
                                        
                                        // Roles are now stored in display format (Super Admin, Manager, etc.)
                                        // Just use them directly, with fallback formatting for legacy data
                                        $admin_role = $admin['role'] ?? 'Manager';
                                        if (strpos($admin_role, '_') !== false) {
                                            // Legacy format (super_admin) - convert to display format
                                            $admin_role = ucwords(str_replace('_', ' ', $admin_role));
                                        }
                                    ?>
                                    <li class="admin-item">
                                        <div class="admin-avatar-sm"><?php echo htmlspecialchars($admin_initials); ?></div>
                                        <div class="admin-details-sm">
                                            <h4><?php echo htmlspecialchars($display_name); ?></h4>
                                            <p><?php echo htmlspecialchars($admin['email']); ?></p>
                                        </div>
                                        <div class="admin-role"><?php echo htmlspecialchars($admin_role); ?></div>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- Add Admin Inline Form (Hidden by default) -->
                        <div id="addAdminFormContainer" style="display: none; margin-top: 20px; padding: 20px; background: var(--gray); border-radius: 8px; border: 1px solid var(--gray-dark);">
                            <h4 style="margin-top: 0; margin-bottom: 20px; color: var(--primary);">
                                <i class="fas fa-user-plus"></i> Add New Administrator
                            </h4>
                            <form id="addAdminFormInline" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="action" value="add_user">
                                
                                <div class="form-group">
                                    <label for="addAdminName">Admin Name *</label>
                                    <input type="text" id="addAdminName" name="username" required>
                                </div>

                                <div class="form-group">
                                    <label for="addAdminEmail">Admin Email *</label>
                                    <input type="email" id="addAdminEmail" name="email" required>
                                </div>

                                <div class="form-group">
                                    <label for="addAdminPassword">Password *</label>
                                    <input type="password" id="addAdminPassword" name="password" required>
                                </div>

                                <div class="form-group">
                                    <label for="addAdminRole">Role *</label>
                                    <select id="addAdminRole" name="role" required>
                                        <option value="manager" selected>Admin</option>
                                        <?php if ($is_super_admin): ?>
                                        <option value="super_admin">Super Admin</option>
                                        <?php endif; ?>
                                        <option value="content_manager">Content Manager</option>
                                        <option value="support">Support</option>
                                    </select>
                                </div>

                                <div class="settings-actions" style="margin-top: 20px;">
                                    <button type="button" class="btn btn-secondary" id="cancelAddAdminForm">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create Admin
                                    </button>
                                </div>
                            </form>
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
                    
                    // Get values for validation
                    const passwordMinLength = parseInt(document.getElementById('passwordMinLength').value) || 8;
                    const sessionTimeout = parseInt(document.getElementById('sessionTimeout').value) || 30;
                    const loginAttempts = parseInt(document.getElementById('loginAttempts').value) || 5;
                    
                    // Validate password minimum length
                    if (isNaN(passwordMinLength) || passwordMinLength < 6 || passwordMinLength > 20) {
                        showNotification('Password minimum length must be between 6 and 20', 'error');
                        return;
                    }
                    
                    // Validate session timeout
                    if (isNaN(sessionTimeout) || sessionTimeout < 5 || sessionTimeout > 240) {
                        showNotification('Session timeout must be between 5 and 240 minutes', 'error');
                        return;
                    }
                    
                    // Validate login attempts
                    if (isNaN(loginAttempts) || loginAttempts < 3 || loginAttempts > 10) {
                        showNotification('Max login attempts must be between 3 and 10', 'error');
                        return;
                    }
                    
                    data = {
                        password_min_length: passwordMinLength.toString(),
                        password_require_uppercase: document.getElementById('passwordRequireUppercase').checked ? '1' : '0',
                        password_require_lowercase: document.getElementById('passwordRequireLowercase').checked ? '1' : '0',
                        password_require_numbers: document.getElementById('passwordRequireNumbers').checked ? '1' : '0',
                        password_require_special: document.getElementById('passwordRequireSpecial').checked ? '1' : '0',
                        session_timeout: sessionTimeout.toString(),
                        login_attempts: loginAttempts.toString(),
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
                    
                    // Validate backup retention
                    const backupRetention = parseInt(document.getElementById('backupRetention').value) || 30;
                    if (isNaN(backupRetention) || backupRetention < 7 || backupRetention > 365) {
                        showNotification('Backup retention must be between 7 and 365 days', 'error');
                        return;
                    }
                    
                    data = {
                        auto_backup: document.getElementById('autoBackup').checked ? '1' : '0',
                        backup_frequency: document.getElementById('backupFrequency').value,
                        backup_retention: backupRetention.toString()
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
                        // Use SweetAlert2 for notifications, security, and backup sections
                        if (sectionName === 'notifications' || sectionName === 'security' || sectionName === 'general') {
                            // Check if this is backup settings (general section with backup fields)
                            const isBackupSettings = section.id === 'backup-settings';
                            
                            // Check if SweetAlert2 is loaded
                            if (typeof Swal !== 'undefined') {
                                let messageText = 'Settings saved successfully!';
                                if (sectionName === 'security') {
                                    messageText = 'Security settings have been saved.';
                                } else if (sectionName === 'notifications') {
                                    messageText = 'Notification settings have been saved.';
                                } else if (isBackupSettings) {
                                    messageText = 'Backup settings have been saved.';
                                }
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Saved successfully',
                                    text: messageText,
                                    timer: 1500,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    toast: true,
                                    position: 'top-end'
                                });
                            } else {
                                // Fallback to default notification
                                showNotification('Settings saved successfully!', 'success');
                            }
                        } else {
                            showNotification('Settings saved successfully!', 'success');
                        }
                        
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
                // Use SweetAlert for confirmation if available
                let shouldProceed = false;
                if (typeof Swal !== 'undefined') {
                    const result = await Swal.fire({
                        title: 'Create Backup?',
                        text: 'This will create a backup of all settings. This may take a few moments.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Create Backup',
                        cancelButtonText: 'Cancel'
                    });
                    shouldProceed = result.isConfirmed;
                } else {
                    shouldProceed = confirm('Create a backup of all settings? This may take a few moments.');
                }
                
                if (!shouldProceed) {
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
                        // Use SweetAlert for success if available
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Backup Created',
                                text: 'Backup created successfully!',
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            showNotification('Backup created successfully!', 'success');
                            setTimeout(() => location.reload(), 1500);
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Error creating backup'
                            });
                        } else {
                            showNotification(result.message || 'Error creating backup', 'error');
                        }
                    }
                } catch (error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error creating backup. Please try again.'
                        });
                    } else {
                        showNotification('Error creating backup. Please try again.', 'error');
                    }
                } finally {
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            });
        }

        // Add Admin Modal (only for super admin)
        const addAdminModal = document.getElementById('addAdminModal');
        const closeAddAdminModal = document.getElementById('closeAddAdminModal');
        const cancelAddAdmin = document.getElementById('cancelAddAdmin');
        const addAdminForm = document.getElementById('addAdminForm');
        
        if (addAdminBtn) {
            <?php if ($is_super_admin): ?>
            addAdminBtn.addEventListener('click', function() {
                if (addAdminModal) {
                    addAdminModal.classList.add('active');
                    // Reset form
                    if (addAdminForm) {
                        addAdminForm.reset();
                    }
                }
            });
            <?php else: ?>
            addAdminBtn.style.display = 'none';
            <?php endif; ?>
        }

        // Close modal handlers
        function closeAddAdminModalFunc() {
            if (addAdminModal) {
                addAdminModal.classList.remove('active');
            }
        }

        if (closeAddAdminModal) {
            closeAddAdminModal.addEventListener('click', closeAddAdminModalFunc);
        }

        if (cancelAddAdmin) {
            cancelAddAdmin.addEventListener('click', closeAddAdminModalFunc);
        }

        // Close modal when clicking outside
        if (addAdminModal) {
            addAdminModal.addEventListener('click', function(e) {
                if (e.target === addAdminModal) {
                    closeAddAdminModalFunc();
                }
            });
        }

        // Handle form submission
        if (addAdminForm) {
            addAdminForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(addAdminForm);
                const submitBtn = addAdminForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                
                try {
                    const response = await fetch('admin-settings.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showNotification(result.message || 'Admin created successfully!', 'success');
                        closeAddAdminModalFunc();
                        // Reload page to show new admin
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(result.message || 'Error creating admin', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                } catch (error) {
                    showNotification('Error creating admin. Please try again.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
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
                const backupItem = e.target.closest('.admin-item');
                
                // Use SweetAlert for confirmation if available
                let shouldDelete = false;
                if (typeof Swal !== 'undefined') {
                    const result = await Swal.fire({
                        title: 'Delete Backup?',
                        text: 'Are you sure you want to delete this backup? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel'
                    });
                    shouldDelete = result.isConfirmed;
                } else {
                    shouldDelete = confirm('Are you sure you want to delete this backup?');
                }
                
                if (!shouldDelete) {
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
                        // Use SweetAlert for success if available
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: 'Backup deleted successfully!',
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        } else {
                            showNotification('Backup deleted successfully!', 'success');
                        }
                        backupItem.remove();
                        
                        // If no backups left, show message
                        const backupList = document.getElementById('backupList');
                        if (backupList && backupList.querySelectorAll('.admin-item').length === 0) {
                            backupList.innerHTML = '<li class="admin-item"><div class="admin-details-sm"><p>No backups found. Create your first backup.</p></div></li>';
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Error deleting backup'
                            });
                        } else {
                            showNotification(result.message || 'Error deleting backup', 'error');
                        }
                    }
                } catch (error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error deleting backup. Please try again.'
                        });
                    } else {
                        showNotification('Error deleting backup. Please try again.', 'error');
                    }
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

        // Inline Add Admin Form Toggle Functionality
        function toggleAddAdminForm() {
            const formContainer = document.getElementById('addAdminFormContainer');
            if (formContainer) {
                const isHidden = formContainer.style.display === 'none';
                formContainer.style.display = isHidden ? 'block' : 'none';
                
                // Reset form when hiding
                if (!isHidden) {
                    const inlineForm = document.getElementById('addAdminFormInline');
                    if (inlineForm) {
                        inlineForm.reset();
                    }
                }
            }
        }

        // Wire up Add Admin button to toggle inline form
        const addAdminBtnInline = document.getElementById('addAdminBtn');
        if (addAdminBtnInline) {
            addAdminBtnInline.addEventListener('click', function(e) {
                // Toggle the inline form
                toggleAddAdminForm();
            });
        }

        // Handle cancel button for inline form
        const cancelAddAdminFormBtn = document.getElementById('cancelAddAdminForm');
        if (cancelAddAdminFormBtn) {
            cancelAddAdminFormBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleAddAdminForm();
            });
        }

        // Handle inline form submission
        const inlineAddAdminForm = document.getElementById('addAdminFormInline');
        if (inlineAddAdminForm) {
            inlineAddAdminForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                }
                
                try {
                    const response = await fetch('admin-settings.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        if (typeof showNotification === 'function') {
                            showNotification(result.message || 'Admin created successfully!', 'success');
                        } else {
                            alert(result.message || 'Admin created successfully!');
                        }
                        toggleAddAdminForm();
                        // Reload page to show new admin
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        if (typeof showNotification === 'function') {
                            showNotification(result.message || 'Error creating admin', 'error');
                        } else {
                            alert(result.message || 'Error creating admin');
                        }
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }
                } catch (error) {
                    if (typeof showNotification === 'function') {
                        showNotification('Error creating admin. Please try again.', 'error');
                    } else {
                        alert('Error creating admin. Please try again.');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }
            });
        }
    </script>

    <!-- Add Admin Modal -->
    <div class="modal" id="addAdminModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Add New Administrator</h3>
                <button class="close-modal" id="closeAddAdminModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addAdminForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="form-group">
                        <label for="newAdminUsername">Username *</label>
                        <input type="text" id="newAdminUsername" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="newAdminEmail">Email Address *</label>
                        <input type="email" id="newAdminEmail" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="newAdminPassword">Password *</label>
                        <input type="password" id="newAdminPassword" name="password" required>
                        <small style="display: block; margin-top: 5px; color: var(--text-light);">
                            Minimum <?php echo htmlspecialchars(get_setting($pdo, 'security', 'password_min_length', '8')); ?> characters
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="newAdminRole">Role *</label>
                        <select id="newAdminRole" name="role" required>
                            <option value="manager">Manager</option>
                            <option value="content_manager">Content Manager</option>
                            <option value="support">Support</option>
                            <?php if ($is_super_admin): ?>
                            <option value="super_admin">Super Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelAddAdmin">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
// ============================================================================
// RESTAURANT INFO HELPER FUNCTIONS
// ============================================================================

/**
 * Save restaurant information to database
 * Admin-only function for saving restaurant info from form submission
 * 
 * @param PDO $pdo Database connection
 * @param array $data Associative array of restaurant data (optional, uses $_POST if not provided)
 * @return array Result array with 'success' boolean and 'message' string
 */
function admin_save_restaurant_info($pdo, $data = null) {
    try {
        // Use provided data or $_POST
        if ($data === null) {
            $data = $_POST;
        }
        
        // Sanitize and validate inputs
        $restaurant_name = isset($data['restaurant_name']) ? trim($data['restaurant_name']) : '';
        $restaurant_phone = isset($data['restaurant_phone']) ? trim($data['restaurant_phone']) : '';
        $restaurant_email = isset($data['restaurant_email']) ? trim($data['restaurant_email']) : '';
        $restaurant_address = isset($data['restaurant_address']) ? trim($data['restaurant_address']) : '';
        $opening_hours = isset($data['opening_hours']) ? trim($data['opening_hours']) : '';
        $closing_hours = isset($data['closing_hours']) ? trim($data['closing_hours']) : '';
        $description = isset($data['restaurant_description']) ? trim($data['restaurant_description']) : (isset($data['description']) ? trim($data['description']) : '');
        
        // Validate required fields
        if (empty($restaurant_name)) {
            return ['success' => false, 'message' => 'Restaurant name is required'];
        }
        
        // Validate email format if provided
        if (!empty($restaurant_email) && !filter_var($restaurant_email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Save all fields using set_setting (handles INSERT/UPDATE automatically)
        set_setting($pdo, 'restaurant', 'restaurant_name', $restaurant_name);
        set_setting($pdo, 'restaurant', 'restaurant_phone', $restaurant_phone);
        set_setting($pdo, 'restaurant', 'restaurant_email', $restaurant_email);
        set_setting($pdo, 'restaurant', 'restaurant_address', $restaurant_address);
        set_setting($pdo, 'restaurant', 'opening_hours', $opening_hours);
        
        // Save optional fields only if they exist in the data
        if ($closing_hours !== '') {
            set_setting($pdo, 'restaurant', 'closing_hours', $closing_hours);
        }
        if ($description !== '') {
            set_setting($pdo, 'restaurant', 'restaurant_description', $description);
        }
        
        // Commit transaction
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Restaurant information saved successfully'];
        
    } catch (PDOException $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Log error without exposing to user
        error_log('Error saving restaurant info: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save restaurant information'];
    } catch (Exception $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Log error without exposing to user
        error_log('Error saving restaurant info: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save restaurant information'];
    }
}

/**
 * Get restaurant information from database
 * Frontend-safe function that can be included/required by frontend files
 * Returns associative array of restaurant info with safe defaults
 * 
 * @param PDO|null $pdo Optional PDO connection. If not provided, creates its own
 * @return array Associative array with restaurant information
 */
function get_restaurant_info($pdo = null) {
    // Default return values
    $default_info = [
        'restaurant_name' => "Joseph's Pot",
        'restaurant_phone' => '',
        'restaurant_email' => '',
        'restaurant_address' => '',
        'opening_hours' => '',
        'closing_hours' => '',
        'description' => ''
    ];
    
    // Create PDO connection if not provided (frontend-safe)
    $own_connection = false;
    if ($pdo === null) {
        // Check if database constants are defined
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            // Constants not defined - return defaults
            error_log('Database constants not defined in get_restaurant_info');
            return $default_info;
        }
        
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            $own_connection = true;
        } catch (PDOException $e) {
            // Fail safely - return defaults
            error_log('Error connecting to database in get_restaurant_info: ' . $e->getMessage());
            return $default_info;
        }
    }
    
    // Fetch restaurant info using get_setting if available, otherwise query directly
    $restaurant_info = [];
    
    try {
        // Try using get_setting function if it exists
        if (function_exists('get_setting')) {
            $restaurant_info = [
                'restaurant_name' => get_setting($pdo, 'restaurant', 'restaurant_name', "Joseph's Pot"),
                'restaurant_phone' => get_setting($pdo, 'restaurant', 'restaurant_phone', ''),
                'restaurant_email' => get_setting($pdo, 'restaurant', 'restaurant_email', ''),
                'restaurant_address' => get_setting($pdo, 'restaurant', 'restaurant_address', ''),
                'opening_hours' => get_setting($pdo, 'restaurant', 'opening_hours', ''),
                'closing_hours' => get_setting($pdo, 'restaurant', 'closing_hours', ''),
                'description' => get_setting($pdo, 'restaurant', 'restaurant_description', '')
            ];
        } else {
            // Fallback: query database directly
            $settings = [];
            $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM restaurant_settings");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            foreach ($results as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            $restaurant_info = [
                'restaurant_name' => isset($settings['restaurant_name']) ? $settings['restaurant_name'] : "Joseph's Pot",
                'restaurant_phone' => isset($settings['restaurant_phone']) ? $settings['restaurant_phone'] : '',
                'restaurant_email' => isset($settings['restaurant_email']) ? $settings['restaurant_email'] : '',
                'restaurant_address' => isset($settings['restaurant_address']) ? $settings['restaurant_address'] : '',
                'opening_hours' => isset($settings['opening_hours']) ? $settings['opening_hours'] : '',
                'closing_hours' => isset($settings['closing_hours']) ? $settings['closing_hours'] : '',
                'description' => isset($settings['restaurant_description']) ? $settings['restaurant_description'] : ''
            ];
        }
    } catch (PDOException $e) {
        // Fail safely - return defaults
        error_log('Error fetching restaurant info: ' . $e->getMessage());
        $restaurant_info = $default_info;
    } catch (Exception $e) {
        // Fail safely - return defaults
        error_log('Error fetching restaurant info: ' . $e->getMessage());
        $restaurant_info = $default_info;
    }
    
    // Close connection if we created it
    if ($own_connection) {
        $pdo = null;
    }
    
    return $restaurant_info;
}

/**
 * Save notification settings
 * Helper function for saving notification preferences
 * 
 * @param PDO $pdo Database connection
 * @param int $admin_id Admin user ID
 * @param array $data Optional data array (defaults to $_POST)
 * @return array Success/error response
 */
function admin_save_notification_settings($pdo, $admin_id, $data = null) {
    try {
        if ($data === null) {
            $data = $_POST;
        }
        
        // Convert checkbox values to 1 or 0
        $email_orders = isset($data['email_orders']) && ($data['email_orders'] === '1' || $data['email_orders'] === 'true' || $data['email_orders'] === true) ? '1' : '0';
        $email_reservations = isset($data['email_reservations']) && ($data['email_reservations'] === '1' || $data['email_reservations'] === 'true' || $data['email_reservations'] === true) ? '1' : '0';
        $email_reviews = isset($data['email_reviews']) && ($data['email_reviews'] === '1' || $data['email_reviews'] === 'true' || $data['email_reviews'] === true) ? '1' : '0';
        $email_promotions = isset($data['email_promotions']) && ($data['email_promotions'] === '1' || $data['email_promotions'] === 'true' || $data['email_promotions'] === true) ? '1' : '0';
        $push_orders = isset($data['push_orders']) && ($data['push_orders'] === '1' || $data['push_orders'] === 'true' || $data['push_orders'] === true) ? '1' : '0';
        $push_reservations = isset($data['push_reservations']) && ($data['push_reservations'] === '1' || $data['push_reservations'] === 'true' || $data['push_reservations'] === true) ? '1' : '0';
        $push_low_stock = isset($data['push_low_stock']) && ($data['push_low_stock'] === '1' || $data['push_low_stock'] === 'true' || $data['push_low_stock'] === true) ? '1' : '0';
        
        // Sanitize notification sound
        $notification_sound = isset($data['notification_sound']) ? trim($data['notification_sound']) : 'default';
        $allowed_sounds = ['default', 'chime', 'bell', 'ding', 'none'];
        if (!in_array($notification_sound, $allowed_sounds)) {
            $notification_sound = 'default';
        }
        
        $pdo->beginTransaction();
        
        set_notification_setting($pdo, 'email_orders', $email_orders, $admin_id);
        set_notification_setting($pdo, 'email_reservations', $email_reservations, $admin_id);
        set_notification_setting($pdo, 'email_reviews', $email_reviews, $admin_id);
        set_notification_setting($pdo, 'email_promotions', $email_promotions, $admin_id);
        set_notification_setting($pdo, 'push_orders', $push_orders, $admin_id);
        set_notification_setting($pdo, 'push_reservations', $push_reservations, $admin_id);
        set_notification_setting($pdo, 'push_low_stock', $push_low_stock, $admin_id);
        set_notification_setting($pdo, 'notification_sound', $notification_sound, $admin_id);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Notification settings saved successfully'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error saving notification settings: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save notification settings'];
    }
}

/**
 * Get notification settings
 * Helper function for fetching notification preferences
 * 
 * @param PDO $pdo Database connection
 * @param int $admin_id Admin user ID (optional, uses session if not provided)
 * @return array Associative array of notification settings
 */
function admin_get_notification_settings($pdo, $admin_id = null) {
    try {
        if ($admin_id === null && isset($_SESSION['admin_id'])) {
            $admin_id = $_SESSION['admin_id'];
        }
        
        $settings = [
            'email_orders' => get_notification_setting($pdo, 'email_orders', $admin_id, '1'),
            'email_reservations' => get_notification_setting($pdo, 'email_reservations', $admin_id, '1'),
            'email_reviews' => get_notification_setting($pdo, 'email_reviews', $admin_id, '0'),
            'email_promotions' => get_notification_setting($pdo, 'email_promotions', $admin_id, '1'),
            'push_orders' => get_notification_setting($pdo, 'push_orders', $admin_id, '1'),
            'push_reservations' => get_notification_setting($pdo, 'push_reservations', $admin_id, '0'),
            'push_low_stock' => get_notification_setting($pdo, 'push_low_stock', $admin_id, '1'),
            'notification_sound' => get_notification_setting($pdo, 'notification_sound', $admin_id, 'default')
        ];
        
        return $settings;
    } catch (PDOException $e) {
        error_log('Error fetching notification settings: ' . $e->getMessage());
        // Return safe defaults
        return [
            'email_orders' => '1',
            'email_reservations' => '1',
            'email_reviews' => '0',
            'email_promotions' => '1',
            'push_orders' => '1',
            'push_reservations' => '0',
            'push_low_stock' => '1',
            'notification_sound' => 'default'
        ];
    }
}

/**
 * Check if a notification type is enabled
 * Helper function for triggers to check if notifications should be sent
 * 
 * USAGE IN TRIGGERS:
 * 
 * 1. When a new order is created (submit-order.php, order creation logic):
 *    - Check email: if (notifications_enabled('email_orders')) { send email }
 *    - Check push: if (notifications_enabled('push_orders')) { createNotification(...) }
 * 
 * 2. When a new reservation is created (submit-reservation.php, admin-reservation.php):
 *    - Check email: if (notifications_enabled('email_reservations')) { send email }
 *    - Check push: if (notifications_enabled('push_reservations')) { createNotification(...) }
 * 
 * 3. When a review is submitted:
 *    - Check email: if (notifications_enabled('email_reviews')) { send email }
 * 
 * 4. When stock is low (inventory management):
 *    - Check push: if (notifications_enabled('push_low_stock')) { createNotification(...) }
 * 
 * @param string $notification_type Type of notification (e.g., 'email_orders', 'push_orders', etc.)
 * @param PDO $pdo Database connection (optional, will create if needed)
 * @param int $admin_id Admin user ID (optional, checks global settings if null)
 * @return bool True if enabled, false otherwise
 */
function notifications_enabled($notification_type, $pdo = null, $admin_id = null) {
    try {
        $own_connection = false;
        
        // Create connection if not provided
        if ($pdo === null) {
            if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $own_connection = true;
            } else {
                error_log('notifications_enabled: Database constants not defined');
                return false;
            }
        }
        
        $value = get_notification_setting($pdo, $notification_type, $admin_id, '0');
        
        // Close connection if we created it
        if ($own_connection) {
            $pdo = null;
        }
        
        return $value === '1';
    } catch (Exception $e) {
        error_log('Error checking notification setting: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update security settings with validation
 * Helper function for saving security preferences with proper validation
 * 
 * @param PDO $pdo Database connection
 * @param array $data Optional data array (defaults to $_POST)
 * @return array Success/error response
 */
function admin_update_security_settings($pdo, $data = null) {
    try {
        if ($data === null) {
            $data = $_POST;
        }
        
        // Get and validate password minimum length
        $password_min_length = isset($data['password_min_length']) ? intval($data['password_min_length']) : 8;
        if ($password_min_length < 6 || $password_min_length > 20) {
            return ['success' => false, 'message' => 'Password minimum length must be between 6 and 20'];
        }
        
        // Get and validate password requirements (checkboxes)
        $password_require_uppercase = isset($data['password_require_uppercase']) && ($data['password_require_uppercase'] === '1' || $data['password_require_uppercase'] === 'true' || $data['password_require_uppercase'] === true) ? '1' : '0';
        $password_require_lowercase = isset($data['password_require_lowercase']) && ($data['password_require_lowercase'] === '1' || $data['password_require_lowercase'] === 'true' || $data['password_require_lowercase'] === true) ? '1' : '0';
        $password_require_numbers = isset($data['password_require_numbers']) && ($data['password_require_numbers'] === '1' || $data['password_require_numbers'] === 'true' || $data['password_require_numbers'] === true) ? '1' : '0';
        $password_require_special = isset($data['password_require_special']) && ($data['password_require_special'] === '1' || $data['password_require_special'] === 'true' || $data['password_require_special'] === true) ? '1' : '0';
        
        // Get and validate session timeout
        $session_timeout = isset($data['session_timeout']) ? intval($data['session_timeout']) : 30;
        if ($session_timeout < 5 || $session_timeout > 240) {
            return ['success' => false, 'message' => 'Session timeout must be between 5 and 240 minutes'];
        }
        
        // Get and validate login attempts
        $login_attempts = isset($data['login_attempts']) ? intval($data['login_attempts']) : 5;
        if ($login_attempts < 3 || $login_attempts > 10) {
            return ['success' => false, 'message' => 'Max login attempts must be between 3 and 10'];
        }
        
        // Get two-factor authentication toggle
        $two_factor_auth = isset($data['two_factor_auth']) && ($data['two_factor_auth'] === '1' || $data['two_factor_auth'] === 'true' || $data['two_factor_auth'] === true) ? '1' : '0';
        
        $pdo->beginTransaction();
        
        set_setting($pdo, 'security', 'password_min_length', strval($password_min_length));
        set_setting($pdo, 'security', 'password_require_uppercase', $password_require_uppercase);
        set_setting($pdo, 'security', 'password_require_lowercase', $password_require_lowercase);
        set_setting($pdo, 'security', 'password_require_numbers', $password_require_numbers);
        set_setting($pdo, 'security', 'password_require_special', $password_require_special);
        set_setting($pdo, 'security', 'session_timeout', strval($session_timeout));
        set_setting($pdo, 'security', 'login_attempts', strval($login_attempts));
        set_setting($pdo, 'security', 'two_factor_auth', $two_factor_auth);
        
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Security settings saved successfully'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error saving security settings: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save security settings'];
    }
}

/**
 * Delete backup file and metadata
 * Helper function for deleting backups
 * 
 * @param PDO $pdo Database connection
 * @param string $filename Backup filename to delete
 * @return array Success/error response
 */
function admin_delete_backup($pdo, $filename) {
    try {
        if (empty($filename)) {
            return ['success' => false, 'message' => 'Invalid filename'];
        }
        
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        
        // Get backup metadata
        $stmt = $pdo->prepare("SELECT meta_value FROM admin_settings_meta WHERE meta_key = ? AND meta_type = 'backup'");
        $stmt->execute([$filename]);
        $backup = $stmt->fetch();
        
        if (!$backup) {
            return ['success' => false, 'message' => 'Backup not found'];
        }
        
        $meta = json_decode($backup['meta_value'], true);
        $file_path = isset($meta['file_path']) ? $meta['file_path'] : __DIR__ . '/../backups/' . $filename;
        
        // Ensure file path is within backup directory (security check)
        $backup_dir = realpath(__DIR__ . '/../backups');
        $real_file_path = realpath($file_path);
        
        if ($real_file_path && strpos($real_file_path, $backup_dir) === 0) {
            // Delete file if exists
            if (file_exists($real_file_path)) {
                if (!unlink($real_file_path)) {
                    error_log('Failed to delete backup file: ' . $real_file_path);
                }
            }
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM admin_settings_meta WHERE meta_key = ? AND meta_type = 'backup'");
        $stmt->execute([$filename]);
        
        return ['success' => true, 'message' => 'Backup deleted successfully'];
    } catch (PDOException $e) {
        error_log('Error deleting backup: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete backup'];
    }
}
?>

</body>
</html>