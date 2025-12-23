<?php
/**
 * API endpoint to fetch restaurant information
 * Frontend-safe endpoint that can be called from any page
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database Configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'joseph_pot_admin');
}

// Get restaurant info helper function
function get_restaurant_setting($pdo, $key, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM restaurant_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        error_log('Error fetching restaurant setting ' . $key . ': ' . $e->getMessage());
        return $default;
    }
}

// Default restaurant info
$default_info = [
    'restaurant_name' => "Joseph's Pot",
    'restaurant_phone' => '',
    'restaurant_email' => '',
    'restaurant_address' => '',
    'opening_hours' => '',
    'closing_hours' => '',
    'description' => ''
];

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Fetch restaurant info
    $restaurant_info = [
        'restaurant_name' => get_restaurant_setting($pdo, 'restaurant_name', $default_info['restaurant_name']),
        'restaurant_phone' => get_restaurant_setting($pdo, 'restaurant_phone', $default_info['restaurant_phone']),
        'restaurant_email' => get_restaurant_setting($pdo, 'restaurant_email', $default_info['restaurant_email']),
        'restaurant_address' => get_restaurant_setting($pdo, 'restaurant_address', $default_info['restaurant_address']),
        'opening_hours' => get_restaurant_setting($pdo, 'opening_hours', $default_info['opening_hours']),
        'closing_hours' => get_restaurant_setting($pdo, 'closing_hours', $default_info['closing_hours']),
        'description' => get_restaurant_setting($pdo, 'restaurant_description', $default_info['description'])
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $restaurant_info
    ]);
    
} catch (PDOException $e) {
    // Fail safely - return defaults
    error_log('Error connecting to database in get-restaurant-info.php: ' . $e->getMessage());
    echo json_encode([
        'success' => true,
        'data' => $default_info
    ]);
} catch (Exception $e) {
    // Fail safely - return defaults
    error_log('Error in get-restaurant-info.php: ' . $e->getMessage());
    echo json_encode([
        'success' => true,
        'data' => $default_info
    ]);
}
?>

