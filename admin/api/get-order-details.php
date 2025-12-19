<?php
/**
 * API Endpoint: Get Single Order Details
 * Returns detailed information about a specific order
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
    // Get order_id from query parameter
    if (!isset($_GET['order_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        exit;
    }

    $orderId = trim($_GET['order_id']);

    // Sanitize order_id (alphanumeric and dash only)
    if (!preg_match('/^[A-Z0-9\-]+$/i', $orderId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid order ID format']);
        exit;
    }

    // Fetch order details
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
            WHERE o.order_id = :order_id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':order_id' => $orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Fetch order items
    $itemsSql = "SELECT 
                    item_name,
                    item_price,
                    quantity
                 FROM order_items
                 WHERE order_id = :order_id";
    $itemsStmt = $pdo->prepare($itemsSql);
    $itemsStmt->execute([':order_id' => $orderId]);
    $order['items'] = $itemsStmt->fetchAll();

    echo json_encode([
        'success' => true,
        'order' => $order
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

