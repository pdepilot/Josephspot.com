<?php
/**
 * Restaurant Information Helper
 * 
 * Loads restaurant information from database for use in frontend pages.
 * This file should be included after db_connection.php or appearance_settings.php
 * (which provides the $pdo connection).
 */

// Check if $pdo is available
if (!isset($pdo)) {
    // Try to load db_connection if not already loaded
    if (file_exists(__DIR__ . '/../db_connection.php')) {
        require_once __DIR__ . '/../db_connection.php';
    } else {
        // Fallback: create connection
        try {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=joseph_pot_admin;charset=utf8mb4",
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            error_log('Error creating database connection in restaurant_info.php: ' . $e->getMessage());
            $pdo = null;
        }
    }
}

// Default restaurant info
$restaurant_info = [
    'restaurant_name' => "Joseph's Pot",
    'restaurant_phone' => '',
    'restaurant_email' => '',
    'restaurant_address' => '',
    'opening_hours' => '',
    'closing_hours' => '',
    'description' => ''
];

// Load restaurant info from database if $pdo is available
if ($pdo !== null) {
    try {
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM restaurant_settings");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $key = $row['setting_key'];
            if ($key === 'restaurant_name') {
                $restaurant_info['restaurant_name'] = $row['setting_value'];
            } elseif ($key === 'restaurant_phone') {
                $restaurant_info['restaurant_phone'] = $row['setting_value'];
            } elseif ($key === 'restaurant_email') {
                $restaurant_info['restaurant_email'] = $row['setting_value'];
            } elseif ($key === 'restaurant_address') {
                $restaurant_info['restaurant_address'] = $row['setting_value'];
            } elseif ($key === 'opening_hours') {
                $restaurant_info['opening_hours'] = $row['setting_value'];
            } elseif ($key === 'closing_hours') {
                $restaurant_info['closing_hours'] = $row['setting_value'];
            } elseif ($key === 'restaurant_description') {
                $restaurant_info['description'] = $row['setting_value'];
            }
        }
    } catch (PDOException $e) {
        error_log('Error fetching restaurant info: ' . $e->getMessage());
        // Use defaults already set above
    }
}
?>

