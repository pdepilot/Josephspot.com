<?php
// admin/api/get-activity-feed.php
// Unified activity feed endpoint - merges orders, reservations, payments, reviews

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

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = max(1, min(50, $limit)); // Limit between 1 and 50

$activities = [];

// Get orders
$stmt = $conn->prepare("
    SELECT 
        'order' as type,
        order_id as reference_id,
        customer_name,
        total_amount,
        order_status,
        created_at as timestamp
    FROM orders
    ORDER BY created_at DESC
    LIMIT ?
");
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $activities[] = [
        'type' => 'order',
        'reference_id' => $row['reference_id'],
        'title' => 'New Order',
        'message' => htmlspecialchars($row['customer_name']) . ' placed order #' . htmlspecialchars($row['reference_id']),
        'status' => $row['order_status'],
        'amount' => (float)$row['total_amount'],
        'timestamp' => $row['timestamp']
    ];
}
$stmt->close();

// Get reservations
$stmt = $conn->prepare("
    SELECT 
        'reservation' as type,
        id as reference_id,
        customer_name,
        party_size,
        reservation_date,
        reservation_time,
        status,
        created_at as timestamp
    FROM reservations
    ORDER BY created_at DESC
    LIMIT ?
");
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $activities[] = [
        'type' => 'reservation',
        'reference_id' => $row['reference_id'],
        'title' => 'Table Reservation',
        'message' => htmlspecialchars($row['customer_name']) . ' reserved a table for ' . $row['party_size'] . ' people',
        'status' => $row['status'],
        'reservation_date' => $row['reservation_date'],
        'reservation_time' => $row['reservation_time'],
        'timestamp' => $row['timestamp']
    ];
}
$stmt->close();

// Get payments (from orders with completed payment_status)
$stmt = $conn->prepare("
    SELECT 
        'payment' as type,
        order_id as reference_id,
        customer_name,
        total_amount,
        payment_method,
        created_at as timestamp
    FROM orders
    WHERE payment_status = 'completed'
    ORDER BY created_at DESC
    LIMIT ?
");
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $activities[] = [
        'type' => 'payment',
        'reference_id' => $row['reference_id'],
        'title' => 'Payment Received',
        'message' => '₦' . number_format($row['total_amount'], 2) . ' payment for Order #' . htmlspecialchars($row['reference_id']),
        'amount' => (float)$row['total_amount'],
        'payment_method' => $row['payment_method'],
        'timestamp' => $row['timestamp']
    ];
}
$stmt->close();

// Get reviews
$review_table_check = $conn->query("SHOW TABLES LIKE 'reviews'");
if ($review_table_check->num_rows > 0) {
    $stmt = $conn->prepare("
        SELECT 
            'review' as type,
            id as reference_id,
            name,
            rating,
            review_text,
            status,
            created_at as timestamp
        FROM reviews
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'review',
            'reference_id' => $row['reference_id'],
            'title' => 'New Review',
            'message' => htmlspecialchars($row['name']) . ' rated ' . $row['rating'] . ' stars',
            'rating' => (int)$row['rating'],
            'status' => $row['status'],
            'timestamp' => $row['timestamp']
        ];
    }
    $stmt->close();
}

// Sort by timestamp (latest first)
usort($activities, function($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});

// Limit to requested number
$activities = array_slice($activities, 0, $limit);

$conn->close();

echo json_encode([
    'success' => true,
    'data' => $activities
]);
?>

