<?php
/**
 * API Endpoint: Get All Orders
 * Returns orders with their items, filtered by status and date if provided
 */

session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../db_config.php';

try {
    // Get filter parameters
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $dateFilter = isset($_GET['date']) ? $_GET['date'] : 'all';
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Build WHERE clause
    $whereConditions = [];
    $params = [];

    // Status filter
    if ($statusFilter !== 'all') {
        $whereConditions[] = "o.order_status = :status";
        $params[':status'] = $statusFilter;
    }

    // Date filter
    if ($dateFilter !== 'all') {
        $now = new DateTime();
        switch ($dateFilter) {
            case 'today':
                $startDate = $now->format('Y-m-d 00:00:00');
                $whereConditions[] = "o.created_at >= :start_date";
                $params[':start_date'] = $startDate;
                break;
            case 'week':
                $startDate = $now->modify('-7 days')->format('Y-m-d 00:00:00');
                $whereConditions[] = "o.created_at >= :start_date";
                $params[':start_date'] = $startDate;
                break;
            case 'month':
                $startDate = $now->modify('-1 month')->format('Y-m-d 00:00:00');
                $whereConditions[] = "o.created_at >= :start_date";
                $params[':start_date'] = $startDate;
                break;
        }
    }

    // Search filter
    if (!empty($searchTerm)) {
        $whereConditions[] = "(o.order_id LIKE :search OR o.customer_name LIKE :search OR o.customer_email LIKE :search OR o.customer_phone LIKE :search)";
        $params[':search'] = "%{$searchTerm}%";
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Fetch orders
    $sql = "SELECT 
                o.id,
                o.order_id,
                o.customer_name,
                o.customer_email,
                o.customer_phone,
                o.customer_state,
                o.delivery_address,
                o.delivery_instructions,
                o.subtotal,
                o.delivery_fee,
                o.total_amount,
                o.payment_method,
                o.payment_status,
                o.order_status,
                o.payment_proof,
                o.payment_reference,
                o.notes,
                o.created_at,
                o.updated_at
            FROM orders o
            {$whereClause}
            ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    // Fetch order items for each order
    foreach ($orders as &$order) {
        $itemsSql = "SELECT 
                        item_name,
                        item_price,
                        quantity
                     FROM order_items
                     WHERE order_id = :order_id";
        $itemsStmt = $pdo->prepare($itemsSql);
        $itemsStmt->execute([':order_id' => $order['order_id']]);
        $order['items'] = $itemsStmt->fetchAll();
    }

    // Get statistics
    $statsSql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN order_status = 'completed' THEN total_amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_orders
                 FROM orders";
    $statsStmt = $pdo->query($statsSql);
    $stats = $statsStmt->fetch();

    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'stats' => [
            'total_orders' => (int)$stats['total_orders'],
            'pending_orders' => (int)$stats['pending_orders'],
            'total_revenue' => (float)$stats['total_revenue'],
            'today_orders' => (int)$stats['today_orders']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

