<?php
// includes/get_notifications.php
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "joseph_pot_admin";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

// Get pending orders as notifications
$query = "SELECT order_id, customer_name, order_status, order_date, 
          CONCAT('New Order #', order_id) as title,
          CONCAT(customer_name, ' placed a new order') as message,
          order_date as created_at,
          0 as is_read
          FROM orders 
          WHERE order_status = 'pending'
          ORDER BY order_date DESC 
          LIMIT 10";

$result = $conn->query($query);
$notifications = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'notifications' => $notifications
]);

$conn->close();
?>