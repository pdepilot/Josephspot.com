-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 12:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `joseph_pot_admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','manager','content_manager','support') DEFAULT 'manager',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', 'admin@josephspot.com', '$2y$10$TSodindX8SzS4ptkt5mvT.5EK8dS.EstBskNwerjW9CNlyNbkQ1he', 'super_admin', 1, '2025-12-17 13:09:01');

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_history`
--

CREATE TABLE `admin_login_history` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `login_at` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_review_actions`
--

CREATE TABLE `admin_review_actions` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` enum('approve','reject','reply','delete') NOT NULL,
  `action_details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_review_actions`
--

INSERT INTO `admin_review_actions` (`id`, `review_id`, `admin_id`, `action`, `action_details`, `created_at`) VALUES
(6, 4, 1, 'approve', 'Review approved', '2025-12-14 22:33:59'),
(8, 5, 1, 'approve', 'Review approved', '2025-12-14 22:51:23'),
(9, 6, 1, 'approve', 'Review approved', '2025-12-14 22:52:11'),
(10, 5, 1, 'reply', 'Admin replied to review', '2025-12-14 23:04:42'),
(12, 10, 1, 'approve', 'Review approved', '2025-12-16 09:41:21');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$YourHashedPasswordHere', 'Admin Joseph', 'admin@josephspot.com', 'super_admin', NULL, '2025-12-15 14:42:30', '2025-12-15 14:42:30');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied','archived') DEFAULT 'unread',
  `ip_address` varchar(45) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `ip_address`, `country`, `user_agent`, `created_at`, `updated_at`) VALUES
(13, 'Princeton Echefu', 'pdepilot@yahoo.com', '0582024389', 'General Inquiry', 'how are you', 'read', '::1', 'Unknown', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 22:01:30', '2025-12-11 22:02:25'),
(14, 'Bright alfred', 'pechefu@gmail.com', '08054905164', 'Feedback', 'If you are open to supervising a Master’s student, I would be truly honored to work under your mentorship. I would also like to apply for the QAAFI HDR scholarship and would appreciate your guidance in structuring my application. I can send my CV, transcripts, and copies of my publications for your review.\n\nThank you very much for your time and consideration.', 'read', '::1', 'Unknown', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 22:08:05', '2025-12-11 22:08:34'),
(15, 'Emeka philip', 'priceless@yahoo.com', '08122389079', 'General Inquiry', 'Proposed Research Idea:\nI propose a project to quantify the regeneration capacity and cytokinin responsiveness across different breeding lines, genotype them using SNP arrays, and build genomic prediction models that include regeneration-related traits as predictive features. This could help identify breeding lines that are both transformation-compatible and high-yielding, accelerating the integration of genomic technologies in breeding pipelines.', 'read', '::1', 'Unknown', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 22:26:44', '2025-12-12 22:45:02'),
(16, 'juliet eribe', 'juli@gmail.com', '08123567890', 'Custom Order', 'i want to make an order', 'read', '::1', 'Unknown', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-11 23:45:29', '2025-12-12 22:44:50'),
(17, 'Eberechi Akwari', 'eberer@yahoo.com', '08031901791', 'Events Inquiry', 'i want enquire about table reservations', 'read', '::1', 'Unknown', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 22:16:35', '2025-12-12 22:42:09'),
(18, 'Chijioke N P Echefu', 'pechefu@gmail.com', '0569003656', 'Reservation', 'Opens your email client with pre-filled message\n\nChoose from templates for quick responses\n\nMessage status updates to \"Replied\"', 'unread', '::1', 'Unknown', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 23:17:58', '2025-12-12 23:17:58'),
(19, 'ozioma bright', 'oz@gmail.com', '0705678945', 'Other', 'call me to discuss issues', 'read', '::1', 'Unknown', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-13 09:53:21', '2025-12-13 10:00:25'),
(20, 'samson', 'sam@gmail.com', '080223456789', 'Events Inquiry', 'i to my reservation', 'read', '::1', 'Unknown', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 11:58:34', '2025-12-16 13:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `total_orders` int(11) DEFAULT 0,
  `total_spent` decimal(10,2) DEFAULT 0.00,
  `last_order_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `location`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(4, 'celebetrate', 'the dop wdij', '2025-12-19 21:07:00', '', 'uploads/event_1765731904_7972.jpg', 'upcoming', '2025-12-14 17:05:04', '2025-12-14 17:05:04'),
(5, 'birthday celebration', 'Happy birthday celebration for our one and only @cruisewithjoe', '2026-01-31 12:09:00', 'owerri', 'uploads/event_1765732078_9364.jpg', 'upcoming', '2025-12-14 17:07:58', '2025-12-14 17:07:58'),
(6, 'Wedding celebrations', 'upcoming weddings', '2026-02-07 20:40:00', 'owerri', 'uploads/event_1765740946_3307.jpg', 'upcoming', '2025-12-14 19:35:46', '2025-12-14 19:35:46'),
(7, 'monthly wedding', 'a big weddings', '2025-12-19 05:09:00', 'owerri', 'uploads/event_1765764623_7464.jpg', 'upcoming', '2025-12-15 02:10:23', '2025-12-15 02:10:23');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'food',
  `file_type` enum('image','video') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `description`, `category`, `file_type`, `file_path`, `thumbnail_path`, `upload_date`, `status`, `sort_order`, `is_featured`) VALUES
(3, 'Signature Dish', 'Our chef\'s special creation with fresh ingredients', 'food', 'image', 'uploads/gallery/1765637594_IM1.jpg', NULL, '2025-12-13 14:53:14', 'active', 0, 0),
(4, 'Spicy Delight', 'A perfect blend of spices and flavors', 'food', 'image', 'uploads/gallery/1765637650_IM2.jpg', NULL, '2025-12-13 14:54:10', 'active', 0, 0),
(5, 'Jollof Special ', 'Fresh catch prepared with traditional recipes', 'food', 'image', 'uploads/gallery/1765637721_IM3.jpg', NULL, '2025-12-13 14:55:21', 'active', 0, 0),
(6, 'Special Breakfast', 'Custard, fish, akara, eveporated, plantain', 'food', 'image', 'uploads/gallery/1765637830_IM4.jpg', NULL, '2025-12-13 14:57:10', 'active', 0, 0),
(7, 'Special Breakfast', 'Custard, fish, akara, eveporated, plantain', 'food', 'image', 'uploads/gallery/1765637830_IM4.jpg', NULL, '2025-12-13 14:57:10', 'active', 0, 0),
(8, 'Freshly made breakfast', 'morning breakfast with all the goodies', 'food', 'image', 'uploads/gallery/1765637944_IM5.jpg', NULL, '2025-12-13 14:59:04', 'active', 0, 0),
(9, 'Joes Secrete', 'Special full chicken grilled with love unto tenderness', 'food', 'image', 'uploads/gallery/1765638036_IM6.jpg', NULL, '2025-12-13 15:00:36', 'active', 0, 0),
(10, 'Nkwobi', 'Cowtail Nkwobi made with special interest.', 'food', 'image', 'uploads/gallery/1765638122_IM7.jpg', NULL, '2025-12-13 15:02:02', 'active', 0, 0),
(11, 'Peppered beef tenderlon', 'Beef sauced with the best ingredient', 'food', 'image', 'uploads/gallery/1765638265_IM8.jpg', NULL, '2025-12-13 15:04:25', 'active', 0, 0),
(12, 'Ofe-owerri', 'greatly flavored soup', 'food', 'image', 'uploads/gallery/1765638393_IM9.jpg', NULL, '2025-12-13 15:06:33', 'active', 0, 0),
(13, 'Jollof rice with fish', 'Jollof rice served with chicken fish and snail', 'food', 'image', 'uploads/gallery/1765638509_IM10.jpg', NULL, '2025-12-13 15:08:29', 'active', 0, 0),
(14, 'Comfort Food', 'Hearty meals that feel like home', 'food', 'image', 'uploads/gallery/1765638625_IM11.jpg', NULL, '2025-12-13 15:10:25', 'active', 0, 0),
(15, 'Flame Grilled', 'Charcoal grilled specialties with smoky flavors', 'food', 'image', 'uploads/gallery/1765638690_IM12.jpg', NULL, '2025-12-13 15:11:30', 'active', 0, 0),
(16, 'Abacha', 'Locally sourced ingredients for maximum freshness', 'food', 'image', 'uploads/gallery/1765638786_IM13.jpg', NULL, '2025-12-13 15:13:06', 'active', 0, 0),
(17, 'sauted meat', 'hearty made from the freshest sauced beef', 'food', 'image', 'uploads/gallery/1765638896_IM14.jpg', NULL, '2025-12-13 15:14:56', 'active', 0, 0),
(18, 'Goat meat Nkwobi', 'Specially fresh goat meet nkwobi special', 'food', 'image', 'uploads/gallery/1765638986_IM15.jpg', NULL, '2025-12-13 15:16:26', 'active', 0, 0),
(19, 'Nkwobi with Ugba', 'Authentic Nkwobi with Ugba special', 'food', 'image', 'uploads/gallery/1765639155_IM16.jpg', NULL, '2025-12-13 15:19:15', 'active', 0, 0),
(20, 'Abacha with kpomo', 'Locally sauced cassava', 'food', 'image', 'uploads/gallery/1765639454_IM17.jpg', NULL, '2025-12-13 15:24:14', 'active', 0, 0),
(21, 'Soup Special', 'Hearty soups made from scratch daily', 'food', 'image', 'uploads/gallery/1765639516_IM18.jpg', NULL, '2025-12-13 15:25:16', 'active', 0, 0),
(22, 'Italian Favorites', 'Authentic Italian dishes with a modern twist', 'food', 'image', 'uploads/gallery/1765639606_IM19.jpg', NULL, '2025-12-13 15:26:46', 'active', 0, 0),
(23, 'Jitazzi', 'yam and fish', 'food', 'image', 'uploads/gallery/1765639698_IM21.jpg', NULL, '2025-12-13 15:28:18', 'active', 0, 0),
(24, 'Burger Bliss', 'Gourmet burgers with premium ingredients', 'food', 'image', 'uploads/gallery/1765639770_IM25.jpg', NULL, '2025-12-13 15:29:30', 'active', 0, 0),
(25, 'Native Rice', 'Locally made rice with native ingredients', 'food', 'image', 'uploads/gallery/1765639854_IM27.jpg', NULL, '2025-12-13 15:30:54', 'active', 0, 0),
(26, 'Garri', 'locally dried cassava smoothly made', 'food', 'image', 'uploads/gallery/1765639916_garri.JPG', NULL, '2025-12-13 15:31:56', 'active', 0, 0),
(27, 'Freshly pounded fufu', 'made freshly pounded fufu with love', 'food', 'image', 'uploads/gallery/1765639980_fufu.jpg', NULL, '2025-12-13 15:33:00', 'active', 0, 0),
(28, 'Egusi soup', 'Fresh melon soup served with fresh fufu', 'food', 'image', 'uploads/gallery/1765640047_egusi.jpg', NULL, '2025-12-13 15:34:07', 'active', 0, 0),
(29, 'Fresh palm-wine', 'Freshly taped palm-wine served very chilled', 'food', 'image', 'uploads/gallery/1765640153_IM30.jpg', NULL, '2025-12-13 15:35:53', 'active', 0, 0),
(30, 'Seafood Platter', 'A variety of fresh seafood delicacies', 'food', 'image', 'uploads/gallery/1765640245_IM35.jpg', NULL, '2025-12-13 15:37:25', 'active', 0, 0),
(31, 'Chips and chicken', 'Crisp greens with house-made dressings', 'food', 'image', 'uploads/gallery/1765640321_IM42.jpg', NULL, '2025-12-13 15:38:41', 'active', 0, 0),
(32, 'Plantain and egg', 'A plate of plantain, eggs, and chicken', 'food', 'image', 'uploads/gallery/1765640430_image7.jpg', NULL, '2025-12-13 15:40:30', 'active', 0, 0),
(33, 'Indomie', 'Indomie made with great vegetables and eggs', 'food', 'image', 'uploads/gallery/1765640569_indomie-with egg.jpg', NULL, '2025-12-13 15:42:49', 'active', 0, 0),
(34, 'Plantain and egg with chicken', 'Plantain and egg with chicken, served with love.', 'food', 'image', 'uploads/gallery/1765640652_image9.jpg', NULL, '2025-12-13 15:44:12', 'active', 0, 0),
(35, 'Plantain and turkey', 'Turkey and plantain with white rice.', 'food', 'image', 'uploads/gallery/1765640844_image14.jpg', NULL, '2025-12-13 15:47:24', 'active', 0, 0),
(36, 'Oat', 'Swallow made out of oat', 'food', 'image', 'uploads/gallery/1765641011_oat.jpg', NULL, '2025-12-13 15:50:11', 'active', 0, 0),
(37, 'Noodles', 'Noodles wey dey bust belle', 'food', 'image', 'uploads/gallery/1765641135_image26.jpg', NULL, '2025-12-13 15:52:15', 'active', 0, 0),
(38, 'Surprise', 'She was surprised', 'event', 'image', 'uploads/gallery/1765641263_instagram image3.jpg', NULL, '2025-12-13 15:54:23', 'active', 0, 0),
(39, 'Restaurant', 'The serenity of our restaurant', 'event', 'image', 'uploads/gallery/1765641362_image8.jpg', NULL, '2025-12-13 15:56:02', 'active', 0, 0),
(40, 'Another', 'Our Environment', 'event', 'image', 'uploads/gallery/1765641439_instagram image4.jpg', NULL, '2025-12-13 15:57:19', 'active', 0, 0),
(41, 'A wedding ', 'a coup had their wedding reception', 'event', 'image', 'uploads/gallery/1765641680_weding1.jpeg', NULL, '2025-12-13 16:01:20', 'active', 0, 0),
(42, 'our staff', 'Our staff that will treat you with love.', 'event', 'image', 'uploads/gallery/1765641750_IM41.jpg', NULL, '2025-12-13 16:02:30', 'active', 0, 0),
(43, 'Kanayo  O Kanayo', 'with @cruisewithjoes', 'videos', 'video', 'uploads/gallery/videos/1765644130_693d976265798.mp4', NULL, '2025-12-13 16:42:10', 'active', 0, 0),
(44, 'enjoyment', 'food galore', 'videos', 'video', 'uploads/gallery/videos/1765644255_693d97dfb0236.mp4', NULL, '2025-12-13 16:44:15', 'active', 0, 0),
(45, 'Chi exotic', 'cold exotic drink', '', 'image', 'uploads/gallery/images/1765644344_693d983813254.jpg', NULL, '2025-12-13 16:45:44', 'active', 0, 0),
(46, 'enjoying', 'Enjoying Ofe-owerri', 'videos', 'video', 'uploads/gallery/videos/1765644441_693d989924f2d.mp4', NULL, '2025-12-13 16:47:21', 'active', 0, 0),
(47, 'Breakfast', 'Our most beautiful breakfast', 'videos', 'video', 'uploads/gallery/videos/1765644559_693d990f38a7c.mp4', NULL, '2025-12-13 16:49:19', 'active', 0, 0),
(48, 'food', 'enjoy', 'videos', 'video', 'uploads/gallery/videos/1765644610_693d9942478e3.mp4', NULL, '2025-12-13 16:50:10', 'active', 0, 0),
(49, 'Opeyemi Enjoying', 'The most popular food critic enjoying ofe-owerri', 'videos', 'video', 'uploads/gallery/videos/1765644697_693d99997f88c.mp4', NULL, '2025-12-13 16:51:37', 'active', 0, 0),
(50, '@cruisewithjoe', 'Event at owerri', 'videos', 'video', 'uploads/gallery/videos/1765644985_693d9ab987125.mp4', NULL, '2025-12-13 16:56:26', 'active', 0, 0),
(51, 'Abacha', 'Abacha served that will keep you wondering', 'videos', 'video', 'uploads/gallery/videos/1765645052_693d9afc22972.mp4', NULL, '2025-12-13 16:57:32', 'active', 0, 0),
(52, 'Signature Dish', 'check it out', 'videos', 'video', 'uploads/gallery/videos/1765645313_693d9c0165ba1.mp4', NULL, '2025-12-13 17:01:53', 'active', 0, 0),
(53, 'Spicy Delight', 'watch', 'videos', 'video', 'uploads/gallery/videos/1765645378_693d9c425f927.mp4', NULL, '2025-12-13 17:02:58', 'active', 0, 0),
(54, 'Chi exotic', 'exotic served cold', 'drinks', 'image', 'uploads/gallery/1765646872_693da2180334e.jpg', NULL, '2025-12-13 17:27:52', 'active', 0, 0),
(55, 'Coca-Cola', 'Coca-cola served cold', 'drinks', 'image', 'uploads/gallery/1765647136_693da320af572.jpg', NULL, '2025-12-13 17:32:16', 'active', 0, 0),
(56, 'Desperado', 'Desperado Drinks enjoyed cold', 'drinks', 'image', 'uploads/gallery/1765647258_693da39ae55f3.jpg', NULL, '2025-12-13 17:34:18', 'active', 0, 0),
(57, 'Fayrouz', 'Fayrouz cold and enjoyed', 'drinks', 'image', 'uploads/gallery/1765647355_693da3fb14430.webp', NULL, '2025-12-13 17:35:55', 'active', 0, 0),
(58, 'Flyingfish', 'Flyingfish served in cold ice', 'drinks', 'image', 'uploads/gallery/1765647436_693da44c425af.jpg', NULL, '2025-12-13 17:37:16', 'active', 0, 0),
(59, 'Guinness', 'Guinness Stout drink', 'drinks', 'image', 'uploads/gallery/1765647528_693da4a82178c.jpg', NULL, '2025-12-13 17:38:48', 'active', 0, 0),
(60, 'Heineken', 'Heineken served in cold ice', 'drinks', 'image', 'uploads/gallery/1765647601_693da4f17271c.jpg', NULL, '2025-12-13 17:40:01', 'active', 0, 0),
(61, 'Hero', 'Hero served in cold ice', 'drinks', 'image', 'uploads/gallery/1765647660_693da52ca5a2b.jpg', NULL, '2025-12-13 17:41:00', 'active', 0, 0),
(62, 'Hollandia yoghurt', 'Cold hollandia yoghurt', 'drinks', 'image', 'uploads/gallery/1765647741_693da57d29ba4.jpg', NULL, '2025-12-13 17:42:21', 'active', 0, 0),
(63, 'Eva', 'Eva bottle water', 'drinks', 'image', 'uploads/gallery/1765647797_693da5b5d7d8c.png', NULL, '2025-12-13 17:43:17', 'active', 0, 0),
(64, 'Star radler', 'Cold star radler', 'drinks', 'image', 'uploads/gallery/1765647867_693da5fb7a61f.png', NULL, '2025-12-13 17:44:27', 'active', 0, 0),
(65, 'Palm-wine', 'Our palm-wine is freshly tapped', 'drinks', 'image', 'uploads/gallery/1765647969_693da661733e2.jpg', NULL, '2025-12-13 17:46:09', 'active', 0, 0),
(67, 'Malt', 'Malta Guinness, Vita Malt, Fayrouz and Barbican', 'drinks', 'image', 'uploads/gallery/1765805052_69400bfc26c1b.png', NULL, '2025-12-15 13:24:12', 'active', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `login_activity`
--

CREATE TABLE `login_activity` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('success','failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_activity`
--

INSERT INTO `login_activity` (`id`, `admin_id`, `username`, `ip_address`, `user_agent`, `device_type`, `browser`, `platform`, `country`, `city`, `region`, `latitude`, `longitude`, `login_time`, `status`) VALUES
(5, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-20 19:09:56', 'success'),
(6, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-20 20:30:41', 'success'),
(17, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-21 11:45:40', 'success'),
(18, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-21 11:55:24', 'success'),
(19, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-21 23:07:27', 'success'),
(20, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-23 11:12:46', 'success'),
(21, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-23 12:42:19', 'success'),
(24, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-24 08:20:39', 'success'),
(25, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-26 18:44:45', 'success'),
(26, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-26 18:46:35', 'success'),
(27, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-26 21:10:39', 'success'),
(28, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-29 01:17:12', 'success'),
(29, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-11-30 20:38:53', 'success'),
(30, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-01 08:01:57', 'success'),
(31, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-01 09:47:01', 'success'),
(32, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-01 10:57:32', 'success'),
(33, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-01 14:38:15', 'success'),
(34, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-01 14:45:38', 'success'),
(35, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-02 08:15:13', 'success'),
(36, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-03 13:24:58', 'success'),
(37, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-04 11:03:20', 'success'),
(38, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-05 13:54:34', 'success'),
(39, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-05 21:45:03', 'success'),
(40, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-06 08:43:20', 'success'),
(41, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-09 09:25:50', 'success'),
(42, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-09 13:58:27', 'success'),
(43, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-09 15:14:14', 'success'),
(44, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-09 19:03:59', 'success'),
(45, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-10 09:10:37', 'success'),
(46, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-10 14:11:23', 'success'),
(47, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-10 14:58:01', 'success'),
(48, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-10 18:12:51', 'success'),
(49, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 10:07:58', 'success'),
(50, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 11:34:11', 'success'),
(51, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 11:42:14', 'success'),
(52, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 11:43:19', 'success'),
(53, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 11:57:26', 'success'),
(54, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 12:58:12', 'success'),
(55, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 13:39:51', 'success'),
(56, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 20:29:33', 'success'),
(57, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 22:31:02', 'success'),
(58, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 23:24:27', 'success'),
(59, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 23:27:29', 'success'),
(60, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-11 23:40:41', 'success'),
(61, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-12 00:07:42', 'success'),
(62, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-12 19:55:18', 'success'),
(63, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-12 22:06:18', 'success'),
(64, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-13 20:09:53', 'success'),
(65, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-13 21:43:50', 'success'),
(66, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-14 13:15:20', 'success'),
(67, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-15 10:12:12', 'success'),
(68, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-16 15:21:43', 'success'),
(69, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-16 17:45:22', 'success'),
(70, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-16 21:28:27', 'success'),
(71, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-17 09:39:52', 'success'),
(72, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-17 14:47:48', 'success'),
(73, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-17 14:48:30', 'success'),
(74, 1, 'admin', '127.0.0.1 (localhost)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Desktop', 'Chrome', 'Windows', 'Local', 'Local Network', 'Internal', NULL, NULL, '2025-12-17 21:06:00', 'success');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `type` enum('review','order','reservation','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `admin_id`, `type`, `title`, `message`, `is_read`, `reference_id`, `created_at`) VALUES
(1, 1, '', 'Test Notification', 'This is a manual test notification', 0, 999, '2025-12-03 22:58:08'),
(2, 1, 'review', 'Review Approved', 'Review #10 by if etarrg has been approved', 0, 10, '2025-12-03 22:58:09'),
(3, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:01:23'),
(4, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:01:23'),
(5, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:01:23'),
(6, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:01:23'),
(7, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:01:23'),
(8, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:01:27'),
(9, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:01:27'),
(10, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:01:27'),
(11, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:01:27'),
(12, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:01:27'),
(13, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:01:31'),
(14, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:01:31'),
(15, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:01:31'),
(16, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:01:31'),
(17, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:01:31'),
(18, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:01:35'),
(19, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:01:35'),
(20, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:01:35'),
(21, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:01:35'),
(22, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:01:35'),
(23, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:01:38'),
(24, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:01:38'),
(25, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:01:39'),
(26, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:01:39'),
(27, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:01:39'),
(28, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:01:42'),
(29, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:01:42'),
(30, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:01:43'),
(31, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:01:43'),
(32, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:01:43'),
(33, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:01:47'),
(34, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:01:47'),
(35, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:01:47'),
(36, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:01:47'),
(37, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:01:47'),
(38, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:01:56'),
(39, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:01:56'),
(40, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:01:56'),
(41, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:01:56'),
(42, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:01:56'),
(43, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:02:01'),
(44, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:02:01'),
(45, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:02:01'),
(46, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:02:01'),
(47, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:02:01'),
(48, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:02:04'),
(49, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:02:04'),
(50, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:02:05'),
(51, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:02:05'),
(52, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:02:05'),
(53, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:02:09'),
(54, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:02:09'),
(55, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:02:09'),
(56, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:02:09'),
(57, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:02:09'),
(58, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:02:42'),
(59, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:02:42'),
(60, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:02:43'),
(61, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:02:43'),
(62, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:02:43'),
(63, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:02:46'),
(64, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:02:46'),
(65, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:02:47'),
(66, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:02:47'),
(67, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:02:47'),
(68, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:02:50'),
(69, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:02:51'),
(70, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:02:51'),
(71, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:02:51'),
(72, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:02:51'),
(73, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:02:55'),
(74, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:02:56'),
(75, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:02:56'),
(76, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:02:56'),
(77, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:02:56'),
(78, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:02:59'),
(79, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:02:59'),
(80, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:03:00'),
(81, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:03:00'),
(82, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:03:00'),
(83, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:03:15'),
(84, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:03:15'),
(85, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:03:15'),
(86, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:03:15'),
(87, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:03:15'),
(88, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:03:30'),
(89, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:03:31'),
(90, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:03:31'),
(91, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:03:31'),
(92, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:03:31'),
(93, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:03:38'),
(94, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:03:39'),
(95, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:03:39'),
(96, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:03:39'),
(97, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:03:39'),
(98, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:03:46'),
(99, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:03:46'),
(100, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:03:46'),
(101, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:03:46'),
(102, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:03:46'),
(103, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:03:56'),
(104, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:03:56'),
(105, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:03:56'),
(106, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:03:56'),
(107, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:03:56'),
(108, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:04:05'),
(109, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:04:05'),
(110, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:04:05'),
(111, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:04:05'),
(112, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:04:05'),
(113, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:04:38'),
(114, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:04:38'),
(115, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:04:39'),
(116, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:04:40'),
(117, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:04:40'),
(118, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:04:45'),
(119, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:04:45'),
(120, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:04:45'),
(121, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:04:45'),
(122, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:04:45'),
(123, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:05:03'),
(124, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:05:04'),
(125, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:05:04'),
(126, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:05:04'),
(127, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:05:04'),
(128, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:05:10'),
(129, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:05:10'),
(130, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:05:10'),
(131, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:05:10'),
(132, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:05:10'),
(133, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:05:15'),
(134, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:05:15'),
(135, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:05:15'),
(136, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:05:15'),
(137, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:05:15'),
(138, 1, 'review', 'Review Test', 'Testing review notification', 0, 999, '2025-12-03 23:05:19'),
(139, 1, 'order', 'Order Test', 'Testing order notification', 0, 999, '2025-12-03 23:05:23'),
(140, 1, 'reservation', 'Reservation Test', 'Testing reservation notification', 0, 999, '2025-12-03 23:05:27'),
(141, 1, 'system', 'System Test', 'Testing system notification', 0, 999, '2025-12-03 23:05:28'),
(142, 1, '', 'Invalid Test', 'Testing invalid type', 0, 999, '2025-12-03 23:05:29');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_state` varchar(50) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 1500.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cod','bank','paystack','flutterwave') NOT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `order_status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `payment_proof` text DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `party_size` int(11) NOT NULL,
  `purpose` enum('Dining In','Special Event','Catering','Takeaway') NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `customer_name`, `customer_email`, `customer_phone`, `reservation_date`, `reservation_time`, `party_size`, `purpose`, `special_requests`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Princeton Echefu', 'pdepilot@yahoo.com', '0582024389', '2025-12-30', '11:00:00', 4, 'Special Event', 'special events', 'confirmed', '2025-12-16 14:08:19', '2025-12-16 14:21:36'),
(2, 'samson', 'sam@gmail.com', '080234567890', '2025-12-16', '21:00:00', 8, 'Dining In', 'we want dinning experiences', 'pending', '2025-12-16 14:10:11', '2025-12-16 14:10:11');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `rating` int(1) NOT NULL DEFAULT 5,
  `review_text` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `order_id` varchar(50) DEFAULT NULL,
  `menu_items` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `admin_reply` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `name`, `email`, `rating`, `review_text`, `image_url`, `status`, `order_id`, `menu_items`, `created_at`, `approved_at`, `admin_reply`) VALUES
(4, 'Olivia', '', 5, 'dlete the reviuews', 'uploads/reviews/review_1765751619_8264.jpeg', 'approved', NULL, NULL, '2025-12-14 22:33:39', '2025-12-14 22:33:59', NULL),
(5, 'if etarrg', '', 5, 'i will delet this later', 'uploads/reviews/review_1765751912_4445.jpg', 'approved', NULL, NULL, '2025-12-14 22:38:32', '2025-12-14 22:51:23', 'the asiidid'),
(6, 'Priceless defender', '', 5, 'hytelkjo xr jkhidrn hoidfn', 'uploads/reviews/review_1765752649_8283.jpg', 'approved', NULL, NULL, '2025-12-14 22:50:49', '2025-12-14 22:52:11', NULL),
(8, 'car', '', 4, 'my food and car', 'uploads/reviews/review_1765765927_1150.jpg', 'pending', NULL, NULL, '2025-12-15 02:32:07', NULL, NULL),
(9, 'Fred', '', 1, 'dont smoke again', 'uploads/reviews/review_1765797531_5487.jpg', 'pending', NULL, NULL, '2025-12-15 11:18:51', NULL, NULL),
(10, 'army', '', 2, 'jhhsxoik-pax asx IOASJX DSCIOKNDSCOSDC', 'uploads/reviews/review_1765839235_6138.jpg', 'approved', NULL, NULL, '2025-12-15 22:53:55', '2025-12-16 09:41:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `review_statistics`
--

CREATE TABLE `review_statistics` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `total_reviews` int(11) DEFAULT 0,
  `avg_rating` decimal(3,2) DEFAULT 0.00,
  `pending_count` int(11) DEFAULT 0,
  `approved_count` int(11) DEFAULT 0,
  `rejected_count` int(11) DEFAULT 0,
  `positive_count` int(11) DEFAULT 0,
  `negative_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_statistics`
--

INSERT INTO `review_statistics` (`id`, `date`, `total_reviews`, `avg_rating`, `pending_count`, `approved_count`, `rejected_count`, `positive_count`, `negative_count`) VALUES
(1, '2025-12-15', 7, 3.29, 3, 4, 0, 4, 3),
(2, '2025-12-16', 6, 3.67, 2, 4, 0, 4, 2),
(3, '2025-12-17', 6, 3.67, 2, 4, 0, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'events_auto_rotate', '1', '2025-12-14 18:18:12', '2025-12-15 02:51:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_login_at` (`login_at`);

--
-- Indexes for table `admin_review_actions`
--
ALTER TABLE `admin_review_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_id_idx` (`review_id`),
  ADD KEY `admin_id_idx` (`admin_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_activity`
--
ALTER TABLE `login_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_login_time` (`login_time`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_country` (`country`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_read` (`admin_id`,`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`reservation_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`customer_email`),
  ADD KEY `idx_reservation_date` (`reservation_date`),
  ADD KEY `idx_reservation_status` (`status`),
  ADD KEY `idx_customer_email` (`customer_email`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status_idx` (`status`),
  ADD KEY `rating_idx` (`rating`),
  ADD KEY `created_at_idx` (`created_at`);

--
-- Indexes for table `review_statistics`
--
ALTER TABLE `review_statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date_unique` (`date`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_review_actions`
--
ALTER TABLE `admin_review_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `login_activity`
--
ALTER TABLE `login_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `review_statistics`
--
ALTER TABLE `review_statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  ADD CONSTRAINT `admin_login_history_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_review_actions`
--
ALTER TABLE `admin_review_actions`
  ADD CONSTRAINT `admin_review_actions_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_activity`
--
ALTER TABLE `login_activity`
  ADD CONSTRAINT `login_activity_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
