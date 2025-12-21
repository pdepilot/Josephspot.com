<?php
// admin/api/mark-notification-read.php
// Mark single notification as read

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Get notification ID
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['notification_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Notification ID required']);
    exit;
}

$notification_id = (int)$input['notification_id'];

require_once __DIR__ . '/../../db_connection.php';

$conn = new mysqli('localhost', 'root', '', 'joseph_pot_admin');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
$conn->set_charset("utf8mb4");

// Mark as read (ensure it belongs to this admin)
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND admin_id = ?");
$stmt->bind_param("ii", $notification_id, $admin_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Notification not found or already read']);
}

$stmt->close();
$conn->close();
?>

