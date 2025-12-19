<?php
require_once __DIR__ . '/includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

$orderId = $_GET['id'];

try {
    // Get order details
    $orderSql = "SELECT * FROM orders WHERE id = ?";
    $orderStmt = $pdo->prepare($orderSql);
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Get order items
    $itemsSql = "SELECT * FROM order_items WHERE order_id = ?";
    $itemsStmt = $pdo->prepare($itemsSql);
    $itemsStmt->execute([$order['order_id']]);
    $items = $itemsStmt->fetchAll();

    $order['items'] = $items;

    echo json_encode(['success' => true, 'data' => $order]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>