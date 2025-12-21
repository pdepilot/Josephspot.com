<?php
// admin/api/get-order-status.php
// Order status endpoint - returns counts and percentages

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../db_connection.php';

$conn = new mysqli('localhost', 'root', '', 'joseph_pot_admin');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
$conn->set_charset("utf8mb4");

// Get counts for each status
$statuses = ['completed', 'pending', 'cancelled'];
$status_data = [];
$total = 0;

foreach ($statuses as $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE order_status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = (int)$row['count'];
    $status_data[$status] = $count;
    $total += $count;
    $stmt->close();
}

// Calculate percentages
$response_data = [];
foreach ($statuses as $status) {
    $count = $status_data[$status];
    $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
    $response_data[] = [
        'status' => $status,
        'count' => $count,
        'percentage' => $percentage
    ];
}

$conn->close();

echo json_encode([
    'success' => true,
    'total' => $total,
    'data' => $response_data
]);
?>

