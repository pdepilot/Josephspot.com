<?php
// admin/api/get-contact-notifications.php
// Fetch contact message notifications

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

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = max(1, min(100, $limit));

// Fetch recent unread contact messages
$stmt = $conn->prepare("
    SELECT 
        id,
        name,
        email,
        subject,
        message,
        status,
        created_at
    FROM contact_messages
    WHERE status = 'unread'
    ORDER BY created_at DESC
    LIMIT ?
");
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => (int)$row['id'],
        'name' => htmlspecialchars($row['name']),
        'email' => htmlspecialchars($row['email']),
        'subject' => htmlspecialchars($row['subject']),
        'message' => htmlspecialchars(substr($row['message'], 0, 100)),
        'is_read' => $row['status'] !== 'unread',
        'created_at' => $row['created_at']
    ];
}

$stmt->close();

// Get unread count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
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

