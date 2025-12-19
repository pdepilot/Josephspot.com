<?php
/**
 * Submit Order Endpoint
 * Handles order submission from order-online.php
 */

header('Content-Type: application/json');

// Database configuration
require_once 'admin/includes/db_connection.php';

// Create order_items table if it doesn't exist
try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'order_items'");
    if ($checkTable->rowCount() == 0) {
        $createTableSql = "CREATE TABLE IF NOT EXISTS `order_items` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` varchar(50) NOT NULL,
            `item_name` varchar(200) NOT NULL,
            `item_price` decimal(10,2) NOT NULL,
            `quantity` int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `order_id` (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $pdo->exec($createTableSql);
        error_log('Created order_items table');
    }
    
    // Also check and create orders table if it doesn't exist
    $checkOrdersTable = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($checkOrdersTable->rowCount() == 0) {
        $createOrdersTableSql = "CREATE TABLE IF NOT EXISTS `orders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` varchar(20) NOT NULL,
            `customer_name` varchar(100) NOT NULL,
            `customer_email` varchar(100) NOT NULL,
            `customer_phone` varchar(20) NOT NULL,
            `customer_state` varchar(50) DEFAULT NULL,
            `delivery_address` text NOT NULL,
            `delivery_instructions` text DEFAULT NULL,
            `subtotal` decimal(10,2) NOT NULL,
            `delivery_fee` decimal(10,2) DEFAULT 1500.00,
            `total_amount` decimal(10,2) NOT NULL,
            `payment_method` enum('cod','bank','paystack','flutterwave') NOT NULL,
            `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
            `order_status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
            `payment_proof` text DEFAULT NULL,
            `payment_reference` varchar(100) DEFAULT NULL,
            `notes` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `order_id` (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $pdo->exec($createOrdersTableSql);
        error_log('Created orders table');
    }
} catch (PDOException $e) {
    error_log('Error checking/creating tables: ' . $e->getMessage());
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $requiredFields = ['customerName', 'customerEmail', 'customerPhone', 'customerState', 'deliveryAddress', 'items', 'subtotal', 'totalAmount', 'paymentMethod'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: {$field}"]);
            exit;
        }
    }

    // Sanitize input
    $customerName = trim($input['customerName']);
    $customerEmail = filter_var(trim($input['customerEmail']), FILTER_SANITIZE_EMAIL);
    $customerPhone = preg_replace('/[^0-9+]/', '', trim($input['customerPhone']));
    $customerState = trim($input['customerState']);
    $deliveryAddress = trim($input['deliveryAddress']);
    $deliveryInstructions = isset($input['deliveryInstructions']) ? trim($input['deliveryInstructions']) : null;
    $subtotal = floatval($input['subtotal']);
    $deliveryFee = isset($input['deliveryFee']) ? floatval($input['deliveryFee']) : 1500.00;
    $totalAmount = floatval($input['totalAmount']);
    $paymentMethod = trim($input['paymentMethod']);
    $paymentProof = isset($input['paymentProof']) ? $input['paymentProof'] : null;
    $items = $input['items'];

    // Validate payment method
    $allowedPaymentMethods = ['cod', 'bank', 'paystack', 'flutterwave'];
    if (!in_array($paymentMethod, $allowedPaymentMethods)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid payment method']);
        exit;
    }

    // Validate email
    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    // Validate items
    if (!is_array($items) || empty($items)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order must contain at least one item']);
        exit;
    }

    // Generate unique order ID
    $orderId = 'GD' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    // Check if order ID already exists (unlikely but possible)
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_id = :order_id");
    $checkStmt->execute([':order_id' => $orderId]);
    $exists = $checkStmt->fetchColumn();
    
    // If exists, generate new one
    while ($exists > 0) {
        $orderId = 'GD' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $checkStmt->execute([':order_id' => $orderId]);
        $exists = $checkStmt->fetchColumn();
    }

    // Determine payment status
    $paymentStatus = 'pending';
    if ($paymentMethod === 'paystack' || $paymentMethod === 'flutterwave') {
        // For online payments, check if payment was successful
        $paymentStatus = isset($input['paymentStatus']) && $input['paymentStatus'] === 'completed' ? 'completed' : 'pending';
    } elseif ($paymentMethod === 'bank' && $paymentProof) {
        // Bank transfer with proof - still pending until verified
        $paymentStatus = 'pending';
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Insert order
        $orderSql = "INSERT INTO orders (
            order_id, customer_name, customer_email, customer_phone, customer_state,
            delivery_address, delivery_instructions, subtotal, delivery_fee, total_amount,
            payment_method, payment_status, order_status, payment_proof, created_at
        ) VALUES (
            :order_id, :customer_name, :customer_email, :customer_phone, :customer_state,
            :delivery_address, :delivery_instructions, :subtotal, :delivery_fee, :total_amount,
            :payment_method, :payment_status, :order_status, :payment_proof, NOW()
        )";

        $orderStmt = $pdo->prepare($orderSql);
        $orderStmt->execute([
            ':order_id' => $orderId,
            ':customer_name' => $customerName,
            ':customer_email' => $customerEmail,
            ':customer_phone' => $customerPhone,
            ':customer_state' => $customerState,
            ':delivery_address' => $deliveryAddress,
            ':delivery_instructions' => $deliveryInstructions,
            ':subtotal' => $subtotal,
            ':delivery_fee' => $deliveryFee,
            ':total_amount' => $totalAmount,
            ':payment_method' => $paymentMethod,
            ':payment_status' => $paymentStatus,
            ':order_status' => 'pending',
            ':payment_proof' => $paymentProof
        ]);

        // Insert order items
        $itemSql = "INSERT INTO order_items (order_id, item_name, item_price, quantity) VALUES (:order_id, :item_name, :item_price, :quantity)";
        $itemStmt = $pdo->prepare($itemSql);

        foreach ($items as $item) {
            if (!isset($item['name']) || !isset($item['price']) || !isset($item['quantity'])) {
                throw new Exception('Invalid item data');
            }
            
            $itemStmt->execute([
                ':order_id' => $orderId,
                ':item_name' => trim($item['name']),
                ':item_price' => floatval($item['price']),
                ':quantity' => intval($item['quantity'])
            ]);
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order submitted successfully',
            'order_id' => $orderId,
            'order' => [
                'id' => $orderId,
                'customerName' => $customerName,
                'customerEmail' => $customerEmail,
                'customerPhone' => $customerPhone,
                'total' => $totalAmount,
                'status' => 'pending',
                'paymentMethod' => $paymentMethod
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    error_log('Order submission error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    error_log('Order submission error: ' . $e->getMessage());
}
?>

