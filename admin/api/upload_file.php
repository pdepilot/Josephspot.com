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

// Validate file type
$file_type = isset($_POST['file_type']) ? trim($_POST['file_type']) : '';
if (!in_array($file_type, ['logo', 'favicon'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

// Handle file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['file'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon'];
$max_size = $file_type === 'favicon' ? 102400 : 2097152; // 100KB for favicon, 2MB for logo

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images are allowed.']);
    exit;
}

// Validate file size
if ($file['size'] > $max_size) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size: ' . ($max_size / 1024) . 'KB']);
    exit;
}

// Create upload directory (relative to project root, not admin directory)
$upload_dir = __DIR__ . '/../../uploads/settings/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = $file_type . '_' . time() . '_' . uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;
$relative_path = 'uploads/settings/' . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    exit;
}

// Delete old file if exists
try {
    $old_path = $pdo->prepare("SELECT setting_value FROM appearance_settings WHERE setting_key = ?");
    $old_path->execute([$file_type . '_path']);
    $old_file = $old_path->fetch();
    
    if ($old_file && !empty($old_file['setting_value'])) {
        // Try root/uploads/settings/ first
        $old_file_path = __DIR__ . '/../../' . $old_file['setting_value'];
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        } else {
            // Try admin/uploads/settings/ as fallback
            $old_file_path = __DIR__ . '/../uploads/settings/' . basename($old_file['setting_value']);
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }
    }
} catch(PDOException $e) {
    // Continue even if old file deletion fails
}

// Save to database
try {
    $sql = "INSERT INTO appearance_settings (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$file_type . '_path', $relative_path]);
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'file_path' => '../' . $relative_path
    ]);
} catch(PDOException $e) {
    // Delete uploaded file if database save fails
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

