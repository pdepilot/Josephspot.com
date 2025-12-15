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

// Get form data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? $conn->real_escape_string(trim($_POST['title'])) : '';
$description = isset($_POST['description']) ? $conn->real_escape_string(trim($_POST['description'])) : '';
$category = isset($_POST['category']) ? $conn->real_escape_string(trim($_POST['category'])) : '';

error_log("Update request - ID: $id, Title: '$title', Category: '$category'");

if ($id <= 0 || empty($title) || empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Validate category
$allowedCategories = ['food', 'event', 'videos', 'drinks'];
if (!in_array($category, $allowedCategories)) {
    echo json_encode(['success' => false, 'message' => 'Invalid category']);
    exit;
}

$sql = "UPDATE gallery SET 
        title = ?,
        description = ?,
        category = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sssi", $title, $description, $category, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Gallery item updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>