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

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';

error_log("Update request - ID: $id, Title: '$title', Category: '$category'");

// Validate required fields
if ($id <= 0 || empty($title) || empty($category)) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Validate category
$allowedCategories = ['food', 'event', 'videos', 'drinks'];
if (!in_array($category, $allowedCategories)) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Invalid category']);
    exit;
}

try {
    $sql = "UPDATE gallery SET 
            title = ?,
            description = ?,
            category = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, $category, $id]);
    
    if ($stmt->rowCount() > 0) {
        ob_clean(); // Clear any output before JSON
        echo json_encode(['success' => true, 'message' => 'Gallery item updated successfully']);
    } else {
        ob_clean(); // Clear any output
        echo json_encode(['success' => false, 'message' => 'No changes made or item not found']);
    }
} catch(PDOException $e) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
