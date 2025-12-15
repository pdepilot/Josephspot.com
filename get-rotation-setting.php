<?php
// get-rotation-setting.php
session_start();
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'joseph_pot_admin';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Fetch auto-rotation setting
$sql = "SELECT setting_value FROM site_settings WHERE setting_key = 'events_auto_rotate'";
$result = $conn->query($sql);

$auto_rotate = true; // Default to true
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $auto_rotate = ($row['setting_value'] == '1');
}

$conn->close();

// Return as JSON
echo json_encode([
    'auto_rotate' => $auto_rotate,
    'timestamp' => time()
]);
?>