<?php
// admin/api/update-order.php
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'joseph_pot_admin';
$username = 'root';
$password = '';

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    $data = $_POST;
}

// Validate required fields
if (empty($data['order_id']) || empty($data['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: order_id and action'
    ]);
    exit;
}

$orderId = htmlspecialchars($data['order_id']);
$action = htmlspecialchars($data['action']);

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

    $sql = "";
    $params = [];

    switch ($action) {
        case 'update_status':
            if (empty($data['status'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Status is required'
                ]);
                exit;
            }
            $status = htmlspecialchars($data['status']);
            $sql = "UPDATE orders SET order_status = :status WHERE order_id = :order_id";
            $params = [':status' => $status, ':order_id' => $orderId];
            break;

        case 'update_payment_status':
            if (empty($data['payment_status'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Payment status is required'
                ]);
                exit;
            }
            $paymentStatus = htmlspecialchars($data['payment_status']);
            $sql = "UPDATE orders SET payment_status = :payment_status WHERE order_id = :order_id";
            $params = [':payment_status' => $paymentStatus, ':order_id' => $orderId];
            break;

        case 'delete':
            // Delete order items first (due to foreign key constraint)
            $deleteItemsSql = "DELETE FROM order_items WHERE order_id = :order_id";
            $deleteItemsStmt = $pdo->prepare($deleteItemsSql);
            $deleteItemsStmt->bindParam(':order_id', $orderId);
            $deleteItemsStmt->execute();

            // Delete order
            $sql = "DELETE FROM orders WHERE order_id = :order_id";
            $params = [':order_id' => $orderId];
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            exit;
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully'
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update order',
        'error' => $e->getMessage()
    ]);
}
?>

