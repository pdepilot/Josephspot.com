-- Analytics Database Schema
-- Run this SQL to create the page_views table for tracking website analytics

CREATE TABLE IF NOT EXISTS `page_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(500) NOT NULL,
  `page_title` varchar(255) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet','bot','unknown') DEFAULT 'unknown',
  `browser` varchar(100) DEFAULT NULL,
  `browser_version` varchar(50) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `session_id` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_page_url` (`page_url`(255)),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_country` (`country`),
  KEY `idx_device_type` (`device_type`),
  KEY `idx_browser` (`browser`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_referrer` (`referrer`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: Functional indexes (DATE, HOUR) are not supported in MariaDB/MySQL < 8.0
-- Time-based queries will use the idx_created_at index efficiently
