-- Create food_menu_manager table for menu management
CREATE TABLE IF NOT EXISTS `food_menu_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `display_price` varchar(50) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `is_special` tinyint(1) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_is_available` (`is_available`),
  KEY `idx_is_special` (`is_special`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

