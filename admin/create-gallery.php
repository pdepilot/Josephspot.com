<?php
session_start();
require_once 'db-connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Debug: Log all POST data
error_log("POST Data: " . print_r($_POST, true));

// Get form data
$title = isset($_POST['title']) ? $conn->real_escape_string(trim($_POST['title'])) : '';
$description = isset($_POST['description']) ? $conn->real_escape_string(trim($_POST['description'])) : '';
$category = isset($_POST['category']) ? $conn->real_escape_string(trim($_POST['category'])) : '';

// Debug: Log received values
error_log("Received - Title: '$title', Description: '$description', Category: '$category'");

// Validate required fields
if (empty($title) || empty($category)) {
    error_log("Validation failed: Title or Category is empty");
    echo json_encode(['success' => false, 'message' => 'Title and category are required']);
    exit;
}

// Validate category
$allowedCategories = ['food', 'event', 'videos', 'drinks'];
if (!in_array($category, $allowedCategories)) {
    error_log("Invalid category: $category");
    echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
    exit;
}

// Handle file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    error_log("File upload error: " . $_FILES['file']['error']);
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
    error_log("Invalid file type: $uploadedFileType");
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
    error_log("Failed to move uploaded file to: $filePath");
    echo json_encode(['success' => false, 'message' => 'Failed to upload file. Please check directory permissions.']);
    exit;
}

// Debug: Log database insert details
error_log("Inserting into database - Title: '$title', Category: '$category', File Type: '$fileType', Path: '$relativePath'");

// Insert into database
$sql = "INSERT INTO gallery (title, description, file_path, file_type, category, status, upload_date, sort_order) 
        VALUES (?, ?, ?, ?, ?, 'active', NOW(), 0)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sssss", $title, $description, $relativePath, $fileType, $category);

if ($stmt->execute()) {
    $insertId = $stmt->insert_id;
    error_log("Insert successful. ID: $insertId");
    
    echo json_encode([
        'success' => true,
        'message' => 'Gallery item uploaded successfully',
        'id' => $insertId,
        'file_url' => '../' . $relativePath,
        'file_type' => $fileType,
        'category' => $category
    ]);
} else {
    error_log("Execute failed: " . $stmt->error);
    // If there's an error, delete the uploaded file
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>