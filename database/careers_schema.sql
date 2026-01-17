-- Careers System Database Schema
-- Database: joseph_pot_admin

-- Jobs Table
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `department` VARCHAR(100) NOT NULL,
    `job_type` ENUM('Full Time', 'Part Time', 'Contract', 'Internship') NOT NULL DEFAULT 'Full Time',
    `location` VARCHAR(255) NOT NULL,
    `salary_range` VARCHAR(100) DEFAULT NULL,
    `description` TEXT NOT NULL,
    `requirements` TEXT NOT NULL,
    `responsibilities` TEXT NOT NULL,
    `benefits` TEXT DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'draft', 'closed') NOT NULL DEFAULT 'draft',
    `application_deadline` DATE DEFAULT NULL,
    `positions_available` INT(11) DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_department` (`department`),
    INDEX `idx_deadline` (`application_deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Applications Table
CREATE TABLE IF NOT EXISTS `applications` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `job_id` INT(11) NOT NULL,
    `applicant_name` VARCHAR(255) NOT NULL,
    `applicant_email` VARCHAR(255) NOT NULL,
    `applicant_phone` VARCHAR(20) NOT NULL,
    `cover_letter` TEXT DEFAULT NULL,
    `resume_path` VARCHAR(500) DEFAULT NULL,
    `years_experience` INT(11) DEFAULT 0,
    `current_position` VARCHAR(255) DEFAULT NULL,
    `current_company` VARCHAR(255) DEFAULT NULL,
    `expected_salary` VARCHAR(100) DEFAULT NULL,
    `notice_period` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('pending', 'reviewed', 'shortlisted', 'interview', 'rejected', 'hired') NOT NULL DEFAULT 'pending',
    `applied_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_job_id` (`job_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_applied_date` (`applied_date`),
    INDEX `idx_email` (`applicant_email`),
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Application Status History Table
CREATE TABLE IF NOT EXISTS `application_status_history` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `application_id` INT(11) NOT NULL,
    `old_status` VARCHAR(50) DEFAULT NULL,
    `new_status` VARCHAR(50) NOT NULL,
    `changed_by` INT(11) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `change_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_application_id` (`application_id`),
    INDEX `idx_change_date` (`change_date`),
    FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Career Notifications Table
CREATE TABLE IF NOT EXISTS `career_notifications` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `type` ENUM('new_application', 'application_status_change', 'job_deadline', 'interview_scheduled') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `recipient_id` INT(11) DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_recipient` (`recipient_id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_type` (`type`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`recipient_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
