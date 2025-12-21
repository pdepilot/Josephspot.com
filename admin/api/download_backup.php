<?php
session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized');
}

// CSRF validation
if (!isset($_GET['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
    die('Invalid CSRF token');
}

$filename = isset($_GET['file']) ? basename($_GET['file']) : '';

if (empty($filename)) {
    die('Invalid filename');
}

// Database connection to get backup path
try {
    $host = 'localhost';
    $dbname = 'joseph_pot_admin';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT meta_value FROM admin_settings_meta WHERE meta_key = ? AND meta_type = 'backup'");
    $stmt->execute([$filename]);
    $backup = $stmt->fetch();
    
    if (!$backup) {
        die('Backup not found');
    }
    
    $meta = json_decode($backup['meta_value'], true);
    $file_path = isset($meta['file_path']) ? $meta['file_path'] : '../backups/' . $filename;
    
    if (!file_exists($file_path)) {
        die('Backup file not found');
    }
    
    // Set headers for download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Output file
    readfile($file_path);
    exit;
    
} catch(PDOException $e) {
    die('Error: ' . $e->getMessage());
}
?>

