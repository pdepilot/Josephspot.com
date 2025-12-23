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
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate ID
if (!isset($_POST['id'])) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

if ($id === false || $id <= 0) {
    ob_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

try {
    // First get file paths
    $stmt = $pdo->prepare("SELECT file_path, thumbnail_path FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    
    if ($row) {
        // Delete files from server
        if (!empty($row['file_path']) && file_exists('../' . $row['file_path'])) {
            unlink('../' . $row['file_path']);
        }
        if (!empty($row['thumbnail_path']) && file_exists('../' . $row['thumbnail_path'])) {
            unlink('../' . $row['thumbnail_path']);
        }
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        ob_clean(); // Clear any output before JSON
        echo json_encode(['success' => true, 'message' => 'Gallery item deleted successfully']);
    } else {
        ob_clean(); // Clear any output
        echo json_encode(['success' => false, 'message' => 'Item not found or already deleted']);
    }
    
} catch(PDOException $e) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
