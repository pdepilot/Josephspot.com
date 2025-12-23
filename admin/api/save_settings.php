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
$data_raw = isset($_POST['data']) ? $_POST['data'] : '';

// Decode JSON string if it's a string, otherwise use as-is
if (is_string($data_raw) && !empty($data_raw)) {
    $data = json_decode($data_raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // If JSON decode fails, try using as array
        $data = [];
    }
} else {
    $data = is_array($data_raw) ? $data_raw : [];
}

if (empty($section) || empty($data) || !is_array($data)) {
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
        
        // Validate security settings if section is security
        if ($section === 'security') {
            // Validate password minimum length
            if (isset($data['password_min_length'])) {
                $password_min_length = intval($data['password_min_length']);
                if ($password_min_length < 6 || $password_min_length > 20) {
                    $pdo->rollBack();
                    ob_clean();
                    echo json_encode(['success' => false, 'message' => 'Password minimum length must be between 6 and 20']);
                    exit;
                }
            }
            
            // Validate session timeout
            if (isset($data['session_timeout'])) {
                $session_timeout = intval($data['session_timeout']);
                if ($session_timeout < 5 || $session_timeout > 240) {
                    $pdo->rollBack();
                    ob_clean();
                    echo json_encode(['success' => false, 'message' => 'Session timeout must be between 5 and 240 minutes']);
                    exit;
                }
            }
            
            // Validate login attempts
            if (isset($data['login_attempts'])) {
                $login_attempts = intval($data['login_attempts']);
                if ($login_attempts < 3 || $login_attempts > 10) {
                    $pdo->rollBack();
                    ob_clean();
                    echo json_encode(['success' => false, 'message' => 'Max login attempts must be between 3 and 10']);
                    exit;
                }
            }
        }
        
        // Validate backup settings if present (in general section)
        if ($section === 'general' && isset($data['backup_retention'])) {
            $backup_retention = intval($data['backup_retention']);
            if ($backup_retention < 7 || $backup_retention > 365) {
                $pdo->rollBack();
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Backup retention must be between 7 and 365 days']);
                exit;
            }
        }
        
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

