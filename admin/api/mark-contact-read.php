<?php
// admin/api/mark-contact-read.php
// Mark a contact message as read

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$message_id = isset($data['message_id']) ? (int)$data['message_id'] : 0;

if ($message_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'joseph_pot_admin');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
$conn->set_charset("utf8mb4");

$stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
$stmt->bind_param("i", $message_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message marked as read']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update message']);
}

$stmt->close();
$conn->close();
?>

