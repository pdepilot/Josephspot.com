<?php
// fetch-events.php - UPDATED VERSION
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

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
$auto_rotate = true;
$settings_sql = "SELECT setting_value FROM site_settings WHERE setting_key = 'events_auto_rotate'";
$settings_result = $conn->query($settings_sql);
if ($settings_result && $settings_result->num_rows > 0) {
    $setting_row = $settings_result->fetch_assoc();
    $auto_rotate = ($setting_row['setting_value'] == '1');
}

// Fetch upcoming and ongoing events
$sql = "SELECT * FROM events 
        WHERE (status = 'upcoming' OR status = 'ongoing') 
        AND event_date >= CURDATE()
        ORDER BY event_date ASC 
        LIMIT 5";

$result = $conn->query($sql);
$events = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Handle image URL
        $image_url = $row['image_url'];
        
        if (strpos($image_url, 'data:image') === 0 || strlen($image_url) > 1000) {
            $image_url = './images/IM41.jpg';
        }
        
        if ($image_url && strpos($image_url, 'uploads/') === 0) {
            $image_url = './' . $image_url;
        }
        
        $events[] = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'description' => htmlspecialchars($row['description']),
            'event_date' => $row['event_date'],
            'location' => htmlspecialchars($row['location']),
            'image_url' => $image_url ?: './images/IM41.jpg',
            'status' => $row['status']
        ];
    }
}

// If no events, show default
if (empty($events)) {
    $events[] = [
        'title' => 'No Upcoming Events',
        'description' => 'Check back soon for upcoming culinary experiences!',
        'event_date' => date('Y-m-d H:i:s', strtotime('+30 days')),
        'location' => 'Joseph\'s Pot Restaurant',
        'image_url' => './images/IM41.jpg',
        'status' => 'upcoming'
    ];
}

$conn->close();

// Return events AND settings
echo json_encode([
    'events' => $events,
    'settings' => [
        'auto_rotate' => $auto_rotate,
        'interval' => 10000 // 10 seconds
    ],
    'timestamp' => time()
]);
?>