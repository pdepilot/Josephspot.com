<?php
// Start output buffering FIRST to catch any errors
ob_start();

// Suppress any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

// Database connection - create connection directly to avoid die() issues
try {
    $host = 'localhost';
    $dbname = 'joseph_pot_admin';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Debug: Log all POST data
error_log("POST Data: " . print_r($_POST, true));

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';

// Debug: Log received values
error_log("Received - Title: '$title', Description: '$description', Category: '$category'");

// Validate required fields
if (empty($title) || empty($category)) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Title and category are required']);
    exit;
}

// Validate category
$allowedCategories = ['food', 'event', 'videos', 'drinks'];
if (!in_array($category, $allowedCategories)) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
    exit;
}

// Handle file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

// Create upload directories if they don't exist
$uploadDir = '../uploads/gallery/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Determine file type
$fileType = 'image';
$allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
$allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'];

$uploadedFileType = $_FILES['file']['type'];
$originalFileName = basename($_FILES['file']['name']);

if (in_array(strtolower($uploadedFileType), $allowedImageTypes)) {
    $fileType = 'image';
} elseif (in_array(strtolower($uploadedFileType), $allowedVideoTypes)) {
    $fileType = 'video';
} else {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload images (JPEG, PNG, GIF, WebP) or videos (MP4, WebM, OGG)']);
    exit;
}

// Generate unique filename
$fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
$fileName = time() . '_' . uniqid() . '.' . $fileExtension;
$filePath = $uploadDir . $fileName;
$relativePath = 'uploads/gallery/' . $fileName;

// Move uploaded file
if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Failed to upload file. Please check directory permissions.']);
    exit;
}

try {
    // Insert into database
    $sql = "INSERT INTO gallery (title, description, file_path, file_type, category, status, upload_date, sort_order) 
            VALUES (?, ?, ?, ?, ?, 'active', NOW(), 0)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, $relativePath, $fileType, $category]);
    
    $insertId = $pdo->lastInsertId();
    
    ob_clean(); // Clear any output before JSON
    echo json_encode([
        'success' => true,
        'message' => 'Gallery item uploaded successfully',
        'id' => $insertId,
        'file_url' => '../' . $relativePath,
        'file_type' => $fileType,
        'category' => $category
    ]);
} catch(PDOException $e) {
    // If there's an error, delete the uploaded file
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>