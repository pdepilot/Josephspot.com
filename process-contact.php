<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'joseph_pot_admin';

// Log the request (for debugging)
error_log("=== PROCESS-CONTACT.PHP CALLED ===");
error_log("REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("POST DATA: " . print_r($_POST, true));
error_log("GET DATA: " . print_r($_GET, true));

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Log received data
error_log("Name: $name");
error_log("Email: $email");
error_log("Phone: $phone");
error_log("Subject: $subject");
error_log("Message length: " . strlen($message));

// Validate required fields
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    error_log("Validation failed: Missing required fields");
    echo json_encode([
        'success' => false, 
        'error' => 'Missing required fields',
        'received' => ['name' => $name, 'email' => $email, 'subject' => $subject]
    ]);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Validation failed: Invalid email format");
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

// Get additional information
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$country = 'Unknown';

// Note: GeoIP functionality requires the GeoIP PHP extension
// For now, country is set to 'Unknown'

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

error_log("Database connection successful");

// Prepare SQL statement
$sql = "INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, country, user_agent, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'unread')";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    error_log("Prepare statement failed: " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    $conn->close();
    exit;
}

// Bind parameters
$stmt->bind_param("ssssssss", 
    $name,
    $email,
    $phone,
    $subject,
    $message,
    $ip_address,
    $country,
    $user_agent
);

// Execute statement
if ($stmt->execute()) {
    $message_id = $stmt->insert_id;
    error_log("Message saved successfully! ID: $message_id");
    
    echo json_encode([
        'success' => true, 
        'message_id' => $message_id,
        'message' => 'Message saved successfully',
        'data' => [
            'name' => $name,
            'email' => $email,
            'subject' => $subject
        ]
    ]);
} else {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['success' => false, 'error' => 'Save failed: ' . $stmt->error]);
}

// Close connections
$stmt->close();
$conn->close();

error_log("=== PROCESS-CONTACT.PHP FINISHED ===");
?>