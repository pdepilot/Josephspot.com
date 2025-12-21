<?php
// admin/api/get-revenue.php
// Revenue endpoint - returns revenue grouped by date

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

// Get date range (default: 7 days)
$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
$days = max(1, min(365, $days)); // Limit between 1 and 365 days

// Calculate date range
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime("-$days days"));

// Query revenue grouped by date
$stmt = $conn->prepare("
    SELECT 
        DATE(created_at) as date,
        SUM(total_amount) as revenue,
        COUNT(*) as order_count
    FROM orders 
    WHERE order_status = 'completed' 
    AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$revenue_data = [];
while ($row = $result->fetch_assoc()) {
    $revenue_data[] = [
        'date' => $row['date'],
        'revenue' => (float)$row['revenue'],
        'order_count' => (int)$row['order_count']
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'period' => [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'days' => $days
    ],
    'data' => $revenue_data
]);
?>

