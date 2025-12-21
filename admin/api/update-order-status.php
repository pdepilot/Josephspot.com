<?php
/**
 * API Endpoint: Update Order Status
 * Updates the status of an order (pending, confirmed/processing, completed, cancelled)
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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($input['order_id']) || !isset($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $orderId = trim($input['order_id']);
    $newStatus = trim($input['status']);

    // Sanitize and validate status
    $allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (!in_array($newStatus, $allowedStatuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    // Sanitize order_id (alphanumeric and dash only)
    if (!preg_match('/^[A-Z0-9\-]+$/i', $orderId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid order ID format']);
        exit;
    }

    // Check if order exists
    $checkSql = "SELECT id, order_status FROM orders WHERE order_id = :order_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':order_id' => $orderId]);
    $order = $checkStmt->fetch();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Update order status
    $updateSql = "UPDATE orders 
                  SET order_status = :status, 
                      updated_at = NOW()
                  WHERE order_id = :order_id";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        ':status' => $newStatus,
        ':order_id' => $orderId
    ]);

    // Create notification for order status change
    try {
        require_once '../includes/notification_helper.php';
        $notif_conn = new mysqli('localhost', 'root', '', 'joseph_pot_admin');
        if (!$notif_conn->connect_error) {
            $notif_conn->set_charset("utf8mb4");
            // Get order details for notification
            $orderDetailSql = "SELECT customer_name, total_amount FROM orders WHERE order_id = ?";
            $orderStmt = $notif_conn->prepare($orderDetailSql);
            $orderStmt->bind_param("s", $orderId);
            $orderStmt->execute();
            $orderResult = $orderStmt->get_result();
            if ($orderRow = $orderResult->fetch_assoc()) {
                createNotification(
                    $notif_conn,
                    null, // notify all admins
                    'order',
                    'Order Status Changed',
                    'Order #' . $orderId . ' status changed from ' . $order['order_status'] . ' to ' . $newStatus,
                    $order['id']
                );
            }
            $orderStmt->close();
            $notif_conn->close();
        }
    } catch (Exception $e) {
        // Silently fail notification creation
        error_log('Notification error: ' . $e->getMessage());
    }

    // Log the status change (optional - you can create an order_history table if needed)
    // For now, we'll just return success

    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'order_id' => $orderId,
        'old_status' => $order['order_status'],
        'new_status' => $newStatus
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

