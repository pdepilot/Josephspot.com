<?php
// admin/api/mark-all-notifications-read.php
// Mark all notifications as read for current admin

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

require_once __DIR__ . '/../../db_connection.php';

$conn = new mysqli('localhost', 'root', '', 'joseph_pot_admin');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
$conn->set_charset("utf8mb4");

// Mark all as read for this admin
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE admin_id = ? AND is_read = 0");
$stmt->bind_param("i", $admin_id);
$stmt->execute();

$affected = $stmt->affected_rows;

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'message' => 'All notifications marked as read',
    'affected' => $affected
]);
?>

