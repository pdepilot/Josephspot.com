<?php
// admin/api/mark-all-contact-read.php
// Mark all contact messages as read

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'joseph_pot_admin');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
$conn->set_charset("utf8mb4");

$stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE status = 'unread'");

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'All messages marked as read']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update messages']);
}

$stmt->close();
$conn->close();
?>

