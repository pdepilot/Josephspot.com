<?php
// admin/api/get-top-menu-items.php
// Top menu items endpoint - top 5 by quantity sold, revenue as tie-breaker

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

// Get top 5 items by quantity, with revenue as tie-breaker
$stmt = $conn->prepare("
    SELECT 
        oi.item_name,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.item_price * oi.quantity) as total_revenue,
        COUNT(DISTINCT oi.order_id) as order_count
    FROM order_items oi
    INNER JOIN orders o ON oi.order_id = o.order_id
    WHERE o.order_status IN ('completed', 'processing')
    GROUP BY oi.item_name
    ORDER BY total_quantity DESC, total_revenue DESC
    LIMIT 5
");

$stmt->execute();
$result = $stmt->get_result();

$top_items = [];
while ($row = $result->fetch_assoc()) {
    $top_items[] = [
        'item_name' => htmlspecialchars($row['item_name']),
        'total_quantity' => (int)$row['total_quantity'],
        'total_revenue' => (float)$row['total_revenue'],
        'order_count' => (int)$row['order_count']
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'data' => $top_items
]);
?>

