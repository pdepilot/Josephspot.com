<?php
// config/smtp_config.php

// SMTP Configuration for Joseph's Pot
define('SMTP_ENABLED', true);
define('SMTP_DEBUG', 0); // 0 = off, 1 = client messages, 2 = client and server messages

// SMTP Server Settings
define('SMTP_HOST', 'smtp.gmail.com'); // or your SMTP server
define('SMTP_USER', 'your.email@gmail.com');
define('SMTP_PASS', 'your-app-password-here'); // Use App Password for Gmail
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // ssl or tls

// Email Settings
define('COMPANY_EMAIL', 'reservations@josephspot.com');
define('COMPANY_NAME', 'Joseph\'s Pot Restaurant');
define('NOREPLY_EMAIL', 'noreply@josephspot.com');
define('NOREPLY_NAME', 'Joseph\'s Pot Reservations');

// Website Information
define('SITE_URL', 'https://josephspot.com');
define('SITE_NAME', 'Joseph\'s Pot');

// Backup settings (if SMTP fails)
define('USE_BACKUP_MAIL', true);
define('BACKUP_FROM_EMAIL', 'noreply@josephspot.com');

// Test mode - when enabled, all emails go to TEST_EMAIL
define('TEST_MODE', false);
define('TEST_EMAIL', 'test@josephspot.com');

// Email templates
define('EMAIL_HEADER_COLOR', '#8b4513');
define('EMAIL_BACKGROUND_COLOR', '#f8f5f0');
define('EMAIL_TEXT_COLOR', '#333333');

// Reservation settings
define('RESERVATION_CONFIRMATION_HOURS', 24);
define('AUTO_REMINDER_HOURS', 2);
?>