<?php
// Script to create all settings tables
// Run this file once to create the tables

// Database connection
$host = 'localhost';
$dbname = 'joseph_pot_admin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create general_settings table
    $sql = "CREATE TABLE IF NOT EXISTS `general_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $pdo->exec($sql);
    echo "✓ general_settings table created\n";
    
    // Create restaurant_settings table
    $sql = "CREATE TABLE IF NOT EXISTS `restaurant_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $pdo->exec($sql);
    echo "✓ restaurant_settings table created\n";
    
    // Create notification_settings table
    $sql = "CREATE TABLE IF NOT EXISTS `notification_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text DEFAULT NULL,
        `admin_id` int(11) DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_setting` (`setting_key`, `admin_id`),
        KEY `admin_id` (`admin_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $pdo->exec($sql);
    echo "✓ notification_settings table created\n";
    
    // Create security_settings table
    $sql = "CREATE TABLE IF NOT EXISTS `security_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $pdo->exec($sql);
    echo "✓ security_settings table created\n";
    
    // Create appearance_settings table
    $sql = "CREATE TABLE IF NOT EXISTS `appearance_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $pdo->exec($sql);
    echo "✓ appearance_settings table created\n";
    
    // Create admin_settings_meta table (for backup metadata, etc.)
    $sql = "CREATE TABLE IF NOT EXISTS `admin_settings_meta` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `meta_key` varchar(100) NOT NULL,
        `meta_value` text DEFAULT NULL,
        `meta_type` varchar(50) DEFAULT 'backup',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `meta_key` (`meta_key`),
        KEY `meta_type` (`meta_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $pdo->exec($sql);
    echo "✓ admin_settings_meta table created\n";
    
    // Insert default values
    $defaults = [
        'general_settings' => [
            'site_name' => "Joseph's Pot",
            'site_description' => "Authentic Nigerian cuisine restaurant offering traditional dishes in a warm and welcoming atmosphere.",
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
            'date_format' => 'DD/MM/YYYY',
            'maintenance_mode' => '0'
        ],
        'restaurant_settings' => [
            'restaurant_name' => "Joseph's Pot",
            'restaurant_tagline' => 'Authentic Nigerian Cuisine',
            'restaurant_address' => '123 Food Street, Victoria Island, Lagos, Nigeria',
            'restaurant_phone' => '+234 801 234 5678',
            'restaurant_email' => 'info@josephspot.com',
            'opening_hours' => "Monday - Friday: 8:00 AM - 10:00 PM\nSaturday - Sunday: 9:00 AM - 11:00 PM"
        ],
        'notification_settings' => [
            'email_orders' => '1',
            'email_reservations' => '1',
            'email_reviews' => '0',
            'email_promotions' => '1',
            'push_orders' => '1',
            'push_reservations' => '0',
            'push_low_stock' => '1',
            'notification_sound' => 'default'
        ],
        'security_settings' => [
            'password_min_length' => '8',
            'password_require_uppercase' => '1',
            'password_require_lowercase' => '1',
            'password_require_numbers' => '1',
            'password_require_special' => '0',
            'session_timeout' => '30',
            'login_attempts' => '5',
            'two_factor_auth' => '0'
        ],
        'appearance_settings' => [
            'theme' => 'warm_brown',
            'primary_color' => '#8b4513',
            'logo_path' => '',
            'favicon_path' => ''
        ]
    ];
    
    foreach ($defaults as $table => $settings) {
        foreach ($settings as $key => $value) {
            $sql = "INSERT IGNORE INTO `{$table}` (`setting_key`, `setting_value`) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$key, $value]);
        }
    }
    
    echo "\n✓ Default settings inserted\n";
    echo "\nAll tables created successfully!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

