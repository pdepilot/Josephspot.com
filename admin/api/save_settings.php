<?php
// Start output buffering FIRST to catch any errors
ob_start();

// Suppress any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

// Database connection
try {
    $host = 'localhost';
    $dbname = 'joseph_pot_admin';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// CSRF validation
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Get section and data
$section = isset($_POST['section']) ? trim($_POST['section']) : '';
$data = isset($_POST['data']) ? $_POST['data'] : [];

if (empty($section) || empty($data)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Validate section
$allowed_sections = ['general', 'restaurant', 'notifications', 'security', 'appearance'];
if (!in_array($section, $allowed_sections)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid section']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    if ($section === 'notifications') {
        // Notification settings can be per admin
        $admin_id = $_SESSION['admin_id'];
        foreach ($data as $key => $value) {
            $value = is_bool($value) || $value === 'true' || $value === '1' ? '1' : ($value === 'false' || $value === '0' ? '0' : $value);
            $sql = "INSERT INTO notification_settings (setting_key, setting_value, admin_id) VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$key, $value, $admin_id]);
        }
    } else {
        // Other settings are global
        $table = $section . '_settings';
        foreach ($data as $key => $value) {
            // Normalize boolean values
            if (is_bool($value) || $value === 'true' || $value === '1') {
                $value = '1';
            } elseif ($value === 'false' || $value === '0') {
                $value = '0';
            }
            
            // Sanitize string values
            $value = trim($value);
            
            $sql = "INSERT INTO `{$table}` (setting_key, setting_value) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$key, $value]);
        }
    }
    
    $pdo->commit();
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
    
} catch(PDOException $e) {
    $pdo->rollBack();
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

