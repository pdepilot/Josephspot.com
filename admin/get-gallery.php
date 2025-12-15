<?php
session_start();
require_once 'db-connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Debug: Log that we're fetching data
error_log("Fetching gallery items from database");

$sql = "SELECT * FROM gallery ORDER BY sort_order ASC, upload_date DESC";
$result = $conn->query($sql);

$galleryItems = [];
$categoriesFound = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
    
    // Debug: Log what categories were found
    error_log("Categories found in database: " . implode(', ', array_unique($categoriesFound)));
} else {
    error_log("No gallery items found or query failed");
}

echo json_encode([
    'success' => true, 
    'data' => $galleryItems,
    'debug' => [
        'total_items' => count($galleryItems),
        'categories_found' => array_unique($categoriesFound)
    ]
]);
$conn->close();
?>