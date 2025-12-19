<?php
// submit-order.php
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'joseph_pot_admin';
$username = 'root';
$password = '';

// Get form data
$data = json_decode(file_get_contents('php://input'), true);

// If no JSON data, check POST data
if (empty($data)) {
    $data = $_POST;
}

// Validate required fields
$required = ['customerName', 'customerEmail', 'customerPhone', 'customerAddress', 'items', 'subtotal', 'total', 'paymentMethod'];
foreach ($required as $field) {
    if (empty($data[$field]) && $field !== 'items') {
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: $field"
        ]);
        exit;
    }
}

// Validate items array
if (empty($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
    echo json_encode([
        'success' => false,
        'message' => "Order must contain at least one item"
    ]);
    exit;
}

// Sanitize data
$customerName = htmlspecialchars($data['customerName']);
$customerEmail = filter_var($data['customerEmail'], FILTER_SANITIZE_EMAIL);
$customerPhone = htmlspecialchars($data['customerPhone']);
$customerState = isset($data['customerState']) ? htmlspecialchars($data['customerState']) : null;
$deliveryAddress = htmlspecialchars($data['customerAddress']);
$deliveryInstructions = isset($data['deliveryNotes']) ? htmlspecialchars($data['deliveryNotes']) : null;
$subtotal = floatval($data['subtotal']);
$deliveryFee = isset($data['deliveryFee']) ? floatval($data['deliveryFee']) : 1500.00;
$totalAmount = floatval($data['total']);
$paymentMethod = htmlspecialchars($data['paymentMethod']);
$paymentProof = isset($data['proofOfPayment']) && !empty($data['proofOfPayment']) ? $data['proofOfPayment'] : null;
$orderStatus = isset($data['status']) ? htmlspecialchars($data['status']) : 'pending';
$paymentStatus = ($paymentMethod === 'cod') ? 'pending' : (($paymentMethod === 'bank' && $paymentProof) ? 'pending' : 'pending');
$items = $data['items'];

// Generate unique order ID
$orderId = 'JP' . strtoupper(substr(uniqid(), -8));

// Save to database
$dbSuccess = false;
$insertedOrderId = null;
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

    // Start transaction
    $pdo->beginTransaction();

    // Insert order
    $sql = "INSERT INTO orders (
        order_id, customer_name, customer_email, customer_phone, customer_state,
        delivery_address, delivery_instructions, subtotal, delivery_fee, total_amount,
        payment_method, payment_status, order_status, payment_proof
    ) VALUES (
        :order_id, :customer_name, :customer_email, :customer_phone, :customer_state,
        :delivery_address, :delivery_instructions, :subtotal, :delivery_fee, :total_amount,
        :payment_method, :payment_status, :order_status, :payment_proof
    )";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':order_id', $orderId);
    $stmt->bindParam(':customer_name', $customerName);
    $stmt->bindParam(':customer_email', $customerEmail);
    $stmt->bindParam(':customer_phone', $customerPhone);
    $stmt->bindParam(':customer_state', $customerState);
    $stmt->bindParam(':delivery_address', $deliveryAddress);
    $stmt->bindParam(':delivery_instructions', $deliveryInstructions);
    $stmt->bindParam(':subtotal', $subtotal);
    $stmt->bindParam(':delivery_fee', $deliveryFee);
    $stmt->bindParam(':total_amount', $totalAmount);
    $stmt->bindParam(':payment_method', $paymentMethod);
    $stmt->bindParam(':payment_status', $paymentStatus);
    $stmt->bindParam(':order_status', $orderStatus);
    $stmt->bindParam(':payment_proof', $paymentProof);

    // Execute the statement
    $stmt->execute();
    $insertedOrderId = $pdo->lastInsertId();

    // Insert order items
    $itemsSql = "INSERT INTO order_items (order_id, item_name, item_price, quantity) VALUES (:order_id, :item_name, :item_price, :quantity)";
    $itemsStmt = $pdo->prepare($itemsSql);

    foreach ($items as $item) {
        $itemName = htmlspecialchars($item['title'] ?? $item['name'] ?? 'Unknown Item');
        $itemPrice = floatval($item['price'] ?? 0);
        $quantity = intval($item['quantity'] ?? 1);

        $itemsStmt->bindParam(':order_id', $orderId);
        $itemsStmt->bindParam(':item_name', $itemName);
        $itemsStmt->bindParam(':item_price', $itemPrice);
        $itemsStmt->bindParam(':quantity', $quantity);
        $itemsStmt->execute();
    }

    // Commit transaction
    $pdo->commit();
    $dbSuccess = true;
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database Error: " . $e->getMessage());
}

if ($dbSuccess) {
    echo json_encode([
        'success' => true,
        'message' => 'Order submitted successfully!',
        'order_id' => $orderId,
        'db_id' => $insertedOrderId
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save order. Please try again or contact us directly.'
    ]);
}
