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

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($action === 'create_backup') {
    try {
        // Collect all settings
        $backup_data = [];
        $tables = ['general_settings', 'restaurant_settings', 'notification_settings', 'security_settings', 'appearance_settings'];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM `{$table}`");
            $backup_data[$table] = $stmt->fetchAll();
        }
        
        // Create backup directory
        $backup_dir = '../backups/';
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Generate backup filename
        $backup_filename = 'settings_backup_' . date('Y-m-d_His') . '.json';
        $backup_path = $backup_dir . $backup_filename;
        
        // Save backup file
        file_put_contents($backup_path, json_encode($backup_data, JSON_PRETTY_PRINT));
        
        // Save backup metadata to database
        $meta_data = [
            'filename' => $backup_filename,
            'file_path' => $backup_path,
            'size' => filesize($backup_path),
            'created_by' => $_SESSION['admin_id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $sql = "INSERT INTO admin_settings_meta (meta_key, meta_value, meta_type) VALUES (?, ?, 'backup')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$backup_filename, json_encode($meta_data)]);
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Backup created successfully',
            'filename' => $backup_filename
        ]);
        
    } catch(Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Error creating backup: ' . $e->getMessage()]);
    }
    
} elseif ($action === 'restore_defaults') {
    try {
        $pdo->beginTransaction();
        
        // Restore default values
        $defaults = [
            'general_settings' => [
                'site_name' => "Joseph's Pot",
                'site_description' => "Authentic Nigerian cuisine restaurant offering traditional dishes in a warm and welcoming atmosphere.",
                'currency' => 'NGN',
                'timezone' => 'Africa/Lagos',
                'date_format' => 'DD/MM/YYYY',
                'maintenance_mode' => '0'
            ],
            'restaurant_settings' => [
                'restaurant_name' => "Joseph's Pot",
                'restaurant_tagline' => 'Authentic Nigerian Cuisine',
                'restaurant_address' => '123 Food Street, Victoria Island, Lagos, Nigeria',
                'restaurant_phone' => '+234 801 234 5678',
                'restaurant_email' => 'info@josephspot.com',
                'opening_hours' => "Monday - Friday: 8:00 AM - 10:00 PM\nSaturday - Sunday: 9:00 AM - 11:00 PM"
            ],
            'security_settings' => [
                'password_min_length' => '8',
                'password_require_uppercase' => '1',
                'password_require_lowercase' => '1',
                'password_require_numbers' => '1',
                'password_require_special' => '0',
                'session_timeout' => '30',
                'login_attempts' => '5',
                'two_factor_auth' => '0'
            ],
            'appearance_settings' => [
                'theme' => 'warm_brown',
                'primary_color' => '#8b4513',
                'logo_path' => '',
                'favicon_path' => ''
            ]
        ];
        
        foreach ($defaults as $table => $settings) {
            foreach ($settings as $key => $value) {
                $sql = "UPDATE `{$table}` SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$value, $key]);
            }
        }
        
        $pdo->commit();
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Settings restored to defaults']);
        
    } catch(Exception $e) {
        $pdo->rollBack();
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Error restoring defaults: ' . $e->getMessage()]);
    }
    
} elseif ($action === 'delete_backup') {
    $filename = isset($_POST['filename']) ? trim($_POST['filename']) : '';
    
    if (empty($filename)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid filename']);
        exit;
    }
    
    try {
        // Get backup metadata
        $stmt = $pdo->prepare("SELECT meta_value FROM admin_settings_meta WHERE meta_key = ? AND meta_type = 'backup'");
        $stmt->execute([$filename]);
        $backup = $stmt->fetch();
        
        if ($backup) {
            $meta = json_decode($backup['meta_value'], true);
            $file_path = isset($meta['file_path']) ? $meta['file_path'] : '../backups/' . $filename;
            
            // Delete file if exists
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM admin_settings_meta WHERE meta_key = ? AND meta_type = 'backup'");
        $stmt->execute([$filename]);
        
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Backup deleted successfully']);
        
    } catch(Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Error deleting backup: ' . $e->getMessage()]);
    }
    
} else {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>

