<?php
// admin/api/get-orders.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Database configuration
$host = 'localhost';
$dbname = 'joseph_pot_admin';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Get filter parameter
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    // Build query - simplified without GROUP BY since we fetch items separately
    $sql = "SELECT o.* FROM orders o WHERE 1=1";

    $params = [];

    // Apply filter
    if ($filter === 'pending') {
        $sql .= " AND o.order_status = 'pending'";
    } elseif ($filter === 'completed') {
        $sql .= " AND o.order_status = 'completed'";
    } elseif ($filter === 'cancelled') {
        $sql .= " AND o.order_status = 'cancelled'";
    } elseif ($filter === 'payment-pending') {
        $sql .= " AND o.payment_status = 'pending' AND o.payment_method = 'bank'";
    }

    $sql .= " ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll();

    // Get order items for each order
    foreach ($orders as &$order) {
        $itemsSql = "SELECT item_name, item_price, quantity FROM order_items WHERE order_id = :order_id";
        $itemsStmt = $pdo->prepare($itemsSql);
        $itemsStmt->bindParam(':order_id', $order['order_id']);
        $itemsStmt->execute();
        $order['items'] = $itemsStmt->fetchAll();
    }
    
    // Unset reference to avoid issues
    unset($order);

    // Debug output (remove in production)
    error_log("Fetched " . count($orders) . " orders with filter: " . $filter);

    echo json_encode([
        'success' => true,
        'orders' => $orders ? $orders : [],
        'count' => count($orders),
        'debug' => [
            'filter' => $filter,
            'orders_count' => count($orders)
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch orders',
        'error' => $e->getMessage()
    ]);
}
?>

