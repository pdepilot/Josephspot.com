<?php
// submit-reservation.php
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
$required = ['name', 'email', 'phone', 'date', 'time', 'guests', 'purpose'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: $field"
        ]);
        exit;
    }
}

// Sanitize data
$name = htmlspecialchars($data['name']);
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars($data['phone']);
$date = $data['date'];
$time = $data['time'];
$guests = intval($data['guests']);
$purpose = htmlspecialchars($data['purpose']);
$message = isset($data['message']) ? htmlspecialchars($data['message']) : '';

// Also forward to Formspree for email notifications
$formspreeSuccess = false;
try {
    // Forward to Formspree
    $formspreeData = http_build_query([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'date' => $date,
        'time' => $time,
        'guests' => $guests,
        'purpose' => $purpose,
        'message' => $message,
        '_subject' => 'New Reservation from ' . $name
    ]);

    $formspreeContext = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => $formspreeData
        ]
    ]);

    $formspreeResponse = @file_get_contents('https://formspree.io/f/xzzaozla', false, $formspreeContext);
    $formspreeSuccess = $formspreeResponse !== false;
} catch (Exception $e) {
    // Silently fail Formspree submission, but continue with database save
    error_log("Formspree Error: " . $e->getMessage());
}

// Save to database
$dbSuccess = false;
$reservationId = null;
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

    // Prepare SQL statement
    $sql = "INSERT INTO reservations (
        customer_name, customer_email, customer_phone,
        reservation_date, reservation_time, party_size,
        purpose, special_requests, status
    ) VALUES (
        :name, :email, :phone, :date, :time, :guests,
        :purpose, :message, 'pending'
    )";

    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':time', $time);
    $stmt->bindParam(':guests', $guests);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->bindParam(':message', $message);

    // Execute the statement
    $stmt->execute();
    $reservationId = $pdo->lastInsertId();
    $dbSuccess = true;

    // Create notification for new reservation
    try {
        require_once 'admin/includes/notification_helper.php';
        $notif_conn = new mysqli('localhost', 'root', '', 'joseph_pot_admin');
        if (!$notif_conn->connect_error) {
            $notif_conn->set_charset("utf8mb4");
            createNotification(
                $notif_conn,
                null, // null = notify all admins
                'reservation',
                'New Reservation',
                $name . ' reserved a table for ' . $guests . ' people on ' . $date . ' at ' . $time,
                $reservationId
            );
            $notif_conn->close();
        }
    } catch (Exception $e) {
        // Silently fail notification creation
        error_log('Notification error: ' . $e->getMessage());
    }

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
}

if ($dbSuccess) {
    echo json_encode([
        'success' => true,
        'message' => 'Reservation submitted successfully! ' . 
                    ($formspreeSuccess ? 'Confirmation email sent.' : ''),
        'reservation_id' => $reservationId,
        'formspree_sent' => $formspreeSuccess
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save reservation. Please try again or contact us directly.'
    ]);
}
?>