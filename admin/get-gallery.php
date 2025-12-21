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

try {
    $sql = "SELECT * FROM gallery ORDER BY sort_order ASC, upload_date DESC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();

    $galleryItems = [];
    $categoriesFound = [];

    foreach ($rows as $row) {
        $categoriesFound[] = $row['category'];
        
        $galleryItems[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'file_path' => $row['file_path'],
            'file_url' => '../' . $row['file_path'],
            'thumbnail_url' => '../' . $row['file_path'],
            'file_type' => $row['file_type'],
            'category' => $row['category'],
            'status' => $row['status'],
            'upload_date' => $row['upload_date']
        ];
    }

    ob_clean(); // Clear any output before JSON
    echo json_encode([
        'success' => true, 
        'data' => $galleryItems,
        'debug' => [
            'total_items' => count($galleryItems),
            'categories_found' => array_unique($categoriesFound)
        ]
    ]);
} catch(PDOException $e) {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>