<?php
// admin/api/get-notifications.php
// Fetch user notifications

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

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$limit = max(1, min(100, $limit));

// Fetch notifications for this admin
$stmt = $conn->prepare("
    SELECT 
        id,
        title,
        message,
        type,
        is_read,
        reference_id,
        created_at
    FROM notifications
    WHERE admin_id = ?
    ORDER BY created_at DESC
    LIMIT ?
");
$stmt->bind_param("ii", $admin_id, $limit);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => (int)$row['id'],
        'title' => htmlspecialchars($row['title']),
        'message' => htmlspecialchars($row['message']),
        'type' => $row['type'],
        'is_read' => (bool)$row['is_read'],
        'reference_id' => $row['reference_id'] ? (int)$row['reference_id'] : null,
        'created_at' => $row['created_at']
    ];
}

$stmt->close();

// Get unread count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE admin_id = ? AND is_read = 0");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$unread_row = $result->fetch_assoc();
$unread_count = (int)$unread_row['count'];
$stmt->close();

$conn->close();

echo json_encode([
    'success' => true,
    'unread_count' => $unread_count,
    'data' => $notifications
]);
?>

