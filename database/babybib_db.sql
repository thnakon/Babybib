-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 08, 2026 at 01:28 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `babybib_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'login_failed', 'Failed login attempt for: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-26 12:57:22'),
(2, NULL, 'login_failed', 'Failed login attempt for: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-26 12:57:23'),
(41, NULL, 'login_failed', 'Failed login attempt for: testadmin@babybib.com', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-28 09:14:19'),
(42, NULL, 'login_failed', 'Failed login attempt for: admin@babybib.com', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-28 09:14:30'),
(43, NULL, 'login_failed', 'Failed login attempt for: test@babybib.com', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-28 09:14:40'),
(44, NULL, 'login_failed', 'Failed login attempt for: test@babybib.com', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-28 09:15:06'),
(91, NULL, 'login_failed', 'Failed login attempt for: testtestuser', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-29 11:16:31'),
(110, NULL, 'register', 'New user registered', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-29 16:03:39'),
(111, NULL, 'login_failed', 'Failed login attempt for: Test', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-29 16:09:01'),
(113, NULL, 'login_failed', 'Failed login attempt for: Test', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-29 16:26:25'),
(131, NULL, 'login_failed', 'Failed login attempt for: warm', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-29 19:47:20'),
(132, NULL, 'login_failed', 'Failed login attempt for: warm', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-29 19:47:21'),
(155, NULL, 'login_failed', 'Failed login attempt for: usedcase', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:35:00'),
(156, NULL, 'login_failed', 'Failed login attempt for: usedcase', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:35:04'),
(157, NULL, 'login_failed', 'Failed login attempt for: usedcase', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:35:04'),
(158, NULL, 'login_failed', 'Failed login attempt for: usedcase', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:35:15'),
(159, NULL, 'login_failed', 'Failed login attempt for: usedcase', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:35:15'),
(162, NULL, 'login_failed', 'Failed login attempt for: usedcase', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:35:34'),
(163, NULL, 'login_failed', 'Failed login attempt for: usedcase', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:36:53'),
(164, NULL, 'login_failed', 'Failed login attempt for: usedcase', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:37:13'),
(165, NULL, 'login_failed', 'Failed login attempt for: usedcase', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:37:14'),
(174, NULL, 'login_failed', 'Failed login attempt for: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:44:41'),
(175, NULL, 'login_failed', 'Failed login attempt for: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:44:43'),
(176, NULL, 'login_failed', 'Failed login attempt for: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:46:03'),
(177, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:46:07'),
(178, 1, 'submit_feedback', 'Submitted feedback: อื่นๆ', 'feedback', 6, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:47:17'),
(182, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:52:46'),
(183, 1, 'update_user', 'Updated user #12', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:53:01'),
(184, 1, 'update_user', 'Updated user #12', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 06:53:04'),
(185, 1, 'admin_create_user', 'Created new user: usertest (#13)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 07:12:20'),
(186, 1, 'admin_update_user', 'Updated full details for user: usertest (#13)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 07:21:04'),
(187, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 07:26:35'),
(188, 1, 'admin_update_user', 'Updated full details for user: usertest (#13)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 07:28:56'),
(189, 1, 'delete_bibliography', 'Deleted 1 bibliographies', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 07:42:42'),
(191, 1, 'update_feedback', 'Updated feedback #6 to read', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:02:20'),
(200, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:16:34'),
(203, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:19:03'),
(204, 1, 'create_announcement', 'Created announcement: ทดสอบ', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:37:42'),
(207, 1, 'update_announcement', 'Updated announcement #1: Status only', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:38:48'),
(208, 1, 'update_announcement', 'Updated announcement #1: Status only', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:41:04'),
(209, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-30 08:43:28'),
(210, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-30 08:43:31'),
(213, 1, 'update_announcement', 'Updated announcement #1: Status only', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:44:02'),
(215, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:54:05'),
(221, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:57:03'),
(222, 1, 'submit_feedback', 'Submitted feedback: อื่นๆ', 'feedback', 7, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:59:21'),
(223, 1, 'update_feedback', 'Updated feedback #7 to read', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 08:59:28'),
(224, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 12:33:36'),
(227, 1, 'update_settings', 'Updated system settings', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 12:48:29'),
(228, 1, 'update_settings', 'Updated system settings', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 12:48:50'),
(229, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 13:18:12'),
(230, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 13:36:24'),
(232, 1, 'mark_notifications_read', 'Marked all notifications as read', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 13:46:40'),
(233, 1, 'mark_notifications_read', 'Marked all notifications as read', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 13:46:42'),
(235, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 14:11:17'),
(236, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 14:12:47'),
(237, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 14:14:41'),
(238, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 14:15:23'),
(252, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 14:48:44'),
(253, 1, 'update_profile', 'Updated profile information', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 14:56:42'),
(254, 1, 'update_profile', 'Updated profile information', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 14:56:59'),
(255, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 15:02:08'),
(256, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 15:06:25'),
(257, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-30 15:06:29'),
(264, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 03:57:55'),
(279, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 04:38:35'),
(280, 1, 'update_feedback', 'Updated feedback #8 to read', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 04:39:10'),
(281, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 04:47:20'),
(282, NULL, 'login_failed', 'Failed login attempt for: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-31 05:33:27'),
(287, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 06:00:18'),
(288, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 06:01:21'),
(291, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 06:04:00'),
(292, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-31 06:09:24'),
(293, 1, 'mark_notifications_read', 'Marked all notifications as read', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 06:12:04'),
(294, 1, 'update_announcement', 'Updated announcement #1: Status only', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 06:13:36'),
(297, 1, 'update_announcement', 'Updated announcement #1: Status only', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 06:16:43'),
(312, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 12:43:34'),
(313, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 13:04:54'),
(314, 1, 'submit_feedback', 'Submitted feedback: อื่นๆ', 'feedback', 10, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 13:06:34'),
(315, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 13:17:34'),
(323, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 14:58:02'),
(324, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 15:23:37'),
(325, 1, 'update_settings', 'Updated system settings', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 15:24:34'),
(326, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-31 15:27:27'),
(336, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:18:57'),
(337, 1, 'delete_user', 'Deleted user: eye', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:19:57'),
(338, 1, 'delete_user', 'Deleted user: test', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:20:01'),
(339, 1, 'delete_user', 'Deleted user: lasttest', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:20:26'),
(340, 1, 'delete_user', 'Deleted user: owen', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:20:35'),
(341, 1, 'delete_user', 'Deleted user: usertest', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:21:13'),
(342, 1, 'delete_user', 'Deleted user: itzwarm', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:21:20'),
(343, 1, 'delete_user', 'Deleted user: warmwarmqu', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:21:27'),
(344, 1, 'update_announcement', 'Updated announcement #1: เปิดให้บริการวันแรก', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:23:22'),
(345, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:23:28'),
(350, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:24:38'),
(351, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2026-01-01 03:30:12'),
(366, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-30 06:43:36'),
(367, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-30 06:45:49'),
(368, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-30 06:48:25'),
(369, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-30 06:49:01'),
(378, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-31 04:36:09'),
(379, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-31 04:39:16'),
(380, NULL, 'register', 'New user registered (pending verification)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-31 04:40:44'),
(381, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-31 04:42:16'),
(382, 1, 'update_settings', 'Updated system settings', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-31 04:42:26'),
(383, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-31 04:42:29'),
(387, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-31 06:03:39'),
(393, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-31 06:06:05'),
(412, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 05:09:57'),
(413, 1, 'delete_announcement', 'Deleted announcement #1', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 05:10:11'),
(414, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 05:10:49'),
(417, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 05:34:57'),
(420, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 05:36:08'),
(421, NULL, 'login_failed', 'Failed login attempt for: eye (attempt 1)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Safari/605.1.15', '2026-03-03 05:39:05'),
(422, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 05:39:15'),
(423, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 05:39:32'),
(425, 1, 'update_settings', 'Updated system settings', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 05:40:10'),
(426, 1, 'update_settings', 'Updated system settings', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 05:40:25'),
(427, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 06:23:32'),
(428, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 06:23:51'),
(429, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 06:23:56'),
(430, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 06:34:12'),
(431, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 06:34:55'),
(432, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 06:35:00'),
(433, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-03 06:37:21'),
(434, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 03:16:50'),
(435, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 03:16:56'),
(436, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 05:10:34'),
(437, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 05:10:40'),
(438, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 07:57:23'),
(439, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 07:57:34'),
(440, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 08:31:39'),
(441, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 08:31:50'),
(467, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 14:54:48'),
(468, 1, 'update_profile', 'Updated profile information', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 14:55:00'),
(469, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-04 14:55:04'),
(470, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-05 02:34:06'),
(471, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-05 02:34:47'),
(485, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-15 04:30:07'),
(486, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-17 04:13:33'),
(487, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 09:32:44'),
(488, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 09:32:58'),
(489, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 09:33:06'),
(490, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 14:38:55'),
(491, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 06:30:35'),
(492, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 09:50:47'),
(493, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 03:31:12'),
(494, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 03:31:20'),
(495, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 07:47:59'),
(496, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 08:17:59'),
(497, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-04 08:58:03'),
(498, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 06:42:46'),
(499, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 06:42:56'),
(500, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 06:44:00'),
(501, 12, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 06:46:19'),
(502, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 09:29:34'),
(503, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 09:42:42'),
(504, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 12:59:24'),
(505, 12, 'create_bibliography', 'Created bibliography ID: 451', 'bibliography', 451, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:00:16'),
(506, NULL, 'login_failed', 'Failed login attempt for: thnakon (attempt 1)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:06:10'),
(507, NULL, 'login_failed', 'Failed login attempt for: thnakon (attempt 2)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:06:26'),
(508, NULL, 'login_failed', 'Failed login attempt for: admin (attempt 3)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:06:39'),
(509, NULL, 'login_failed', 'Failed login attempt for: thnakon (attempt 4)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:06:53'),
(510, 12, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:08:11'),
(511, NULL, 'login_failed', 'Failed login attempt for: usedcaseusedcase (attempt 5)', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:09:30'),
(513, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:13:01'),
(514, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:47:36'),
(515, 12, 'delete_bibliography', 'Deleted 1 bibliographies', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 13:47:47'),
(516, 12, 'create_bibliography', 'Created bibliography ID: 452', 'bibliography', 452, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 14:04:44'),
(517, 12, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 14:05:24'),
(518, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 14:08:49'),
(519, 12, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 14:14:52'),
(520, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 14:15:00'),
(521, 1, 'delete_user', 'Deleted user: warm', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 14:15:54'),
(522, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 14:20:50'),
(523, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 14:21:01'),
(524, 12, 'create_bibliography', 'Created bibliography ID: 453', 'bibliography', 453, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 14:28:06'),
(525, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 05:26:56'),
(526, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 06:02:36'),
(527, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 07:31:22'),
(528, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 09:30:32'),
(529, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 10:13:50'),
(530, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 12:08:47'),
(531, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 12:29:22'),
(532, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 13:35:14'),
(533, 12, 'move_bibliography', 'Moved 1 bibliographies to project 73', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 14:14:23'),
(534, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 14:57:59'),
(535, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 04:11:25'),
(536, 12, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 04:11:35'),
(537, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 04:11:47'),
(538, 12, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 04:27:06'),
(539, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 04:27:11'),
(540, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 05:03:01'),
(541, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 06:14:05'),
(542, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 08:51:29'),
(543, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 10:23:24'),
(544, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 10:39:17'),
(545, 12, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-07 12:33:50'),
(546, NULL, 'register', 'New user registered', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:41:33'),
(547, NULL, 'register', 'New user registered', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:42:35'),
(548, NULL, 'register', 'New user registered', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:42:57'),
(549, NULL, 'login', 'User logged in', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:42:57'),
(550, NULL, 'create_project', 'Created project: Smoke Project', 'project', 81, '::1', 'curl/8.7.1', '2026-04-08 05:42:57'),
(551, NULL, 'update_project', 'Updated project: Smoke Project Updated', 'project', 81, '::1', 'curl/8.7.1', '2026-04-08 05:42:57'),
(552, NULL, 'support_report', 'Submitted support report #3', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:42:57'),
(553, NULL, 'delete_project', 'Deleted project: Smoke Project Updated', 'project', 81, '::1', 'curl/8.7.1', '2026-04-08 05:42:58'),
(554, NULL, 'register', 'New user registered', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:45:20'),
(555, NULL, 'login', 'User logged in', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:45:20'),
(556, NULL, 'create_project', 'Created project: Smoke Project', 'project', 82, '::1', 'curl/8.7.1', '2026-04-08 05:45:20'),
(557, NULL, 'update_project', 'Updated project: Smoke Project Updated', 'project', 82, '::1', 'curl/8.7.1', '2026-04-08 05:45:20'),
(558, NULL, 'support_report', 'Submitted support report #4', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:45:21'),
(559, NULL, 'delete_project', 'Deleted project: Smoke Project Updated', 'project', 82, '::1', 'curl/8.7.1', '2026-04-08 05:45:21'),
(560, NULL, 'reset_password', 'User reset password successfully', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:45:24'),
(561, NULL, 'login', 'User logged in', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:45:24'),
(562, NULL, 'login_failed', 'Failed login attempt for: admin@babybib.com (attempt 1)', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:45:25'),
(563, NULL, 'login_failed', 'Failed login attempt for: admin@babybib.com (attempt 2)', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:48:24'),
(564, NULL, 'login', 'User logged in', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:53:01'),
(565, NULL, 'create_announcement', 'Created announcement: Smoke announcement 1775627581', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:53:01'),
(566, NULL, 'delete_announcement', 'Deleted announcement #2', NULL, NULL, '::1', 'curl/8.7.1', '2026-04-08 05:53:01'),
(567, 1, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 06:09:52'),
(568, 1, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 06:22:20'),
(569, NULL, 'register', 'New user registered', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 06:25:02'),
(570, NULL, 'login', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 06:25:12'),
(571, NULL, 'logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-08 06:25:20');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `type` enum('feedback','user','system','announcement') NOT NULL DEFAULT 'system',
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title_th` varchar(255) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `content_th` text NOT NULL,
  `content_en` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bibliographies`
--

CREATE TABLE `bibliographies` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `resource_type_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `bibliography_text` text NOT NULL,
  `citation_parenthetical` text DEFAULT NULL,
  `citation_narrative` text DEFAULT NULL,
  `language` varchar(2) DEFAULT 'th',
  `author_sort_key` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `year_suffix` varchar(5) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bibliographies`
--

INSERT INTO `bibliographies` (`id`, `user_id`, `resource_type_id`, `project_id`, `data`, `bibliography_text`, `citation_parenthetical`, `citation_narrative`, `language`, `author_sort_key`, `year`, `year_suffix`, `created_at`, `updated_at`) VALUES
(40, NULL, 1, NULL, '{\"title\":\"การเขียนโปรแกรมเบื้องต้น\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"สมชาย\",\"middleName\":\"\",\"lastName\":\"ใจดี\",\"type\":\"normal\",\"display\":\"สมชาย ใจดี\"}],\"edition\":\"3\",\"publisher\":\"สำนักพิมพ์เชียงใหม่\"}', 'สมชาย ใจดี. (2567). <i>การเขียนโปรแกรมเบื้องต้น</i> (พิมพ์ครั้งที่ 3). สำนักพิมพ์เชียงใหม่.', '(สมชาย ใจดี, 2567)', 'สมชาย ใจดี (2567)', 'th', 'ใจดี', 2567, NULL, '2025-12-29 19:54:50', '2025-12-29 19:54:50'),
(42, 12, 1, 73, '{}', 'สมชาย ใจดี. (2560). <i>การพัฒนาระบบห้องสมุดดิจิทัล</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(สมชาย, 2560)', 'สมชาย (2560)', 'th', 'สมชาย', 2560, NULL, '2025-12-30 06:29:32', '2025-12-30 06:42:44'),
(43, 12, 1, 73, '{}', 'จิตรา สายธาร. (2562). <i>การพัฒนาระบบห้องสมุดดิจิทัล</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(จิตรา, 2562)', 'จิตรา (2562)', 'th', 'จิตรา', 2562, NULL, '2025-12-20 06:29:32', '2025-12-30 06:42:50'),
(44, 12, 1, 73, '{}', 'กมลา สุขใจ. (2564). <i>การพัฒนาระบบห้องสมุดดิจิทัล</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(กมลา, 2564)', 'กมลา (2564)', 'th', 'กมลา', 2564, NULL, '2025-12-10 06:29:32', '2025-12-30 06:43:03'),
(45, 12, 1, 44, '{}', 'สมชาย ใจดี. (2566). <i>การพัฒนาระบบห้องสมุดดิจิทัล</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(สมชาย, 2566)', 'สมชาย (2566)', 'th', 'สมชาย', 2566, NULL, '2025-11-30 06:29:32', '2025-12-30 06:29:32'),
(46, 12, 1, 54, '{}', 'จิตรา สายธาร. (2560). <i>การพัฒนาระบบห้องสมุดดิจิทัล</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(จิตรา, 2560)', 'จิตรา (2560)', 'th', 'จิตรา', 2560, NULL, '2025-11-20 06:29:32', '2025-12-30 06:29:32'),
(47, 12, 1, 73, '{}', 'สมหญิง รักเรียน. (2561). <i>การจัดการความรู้ในองค์กร</i>. โรงพิมพ์มหาวิทยาลัย.', '(สมหญิง, 2561)', 'สมหญิง (2561)', 'th', 'สมหญิง', 2561, NULL, '2025-12-29 06:29:32', '2025-12-30 06:42:44'),
(48, 12, 1, 73, '{}', 'ธนา บุญยิ่ง. (2563). <i>การจัดการความรู้ในองค์กร</i>. โรงพิมพ์มหาวิทยาลัย.', '(ธนา, 2563)', 'ธนา (2563)', 'th', 'ธนา', 2563, NULL, '2025-12-19 06:29:32', '2025-12-30 06:42:50'),
(49, 12, 1, 73, '{}', 'อรุณ ศรีสว่าง. (2565). <i>การจัดการความรู้ในองค์กร</i>. โรงพิมพ์มหาวิทยาลัย.', '(อรุณ, 2565)', 'อรุณ (2565)', 'th', 'อรุณ', 2565, NULL, '2025-12-09 06:29:32', '2025-12-30 06:43:03'),
(50, 12, 1, 45, '{}', 'สมหญิง รักเรียน. (2567). <i>การจัดการความรู้ในองค์กร</i>. โรงพิมพ์มหาวิทยาลัย.', '(สมหญิง, 2567)', 'สมหญิง (2567)', 'th', 'สมหญิง', 2567, NULL, '2025-11-29 06:29:32', '2025-12-30 06:29:32'),
(51, 12, 1, 55, '{}', 'ธนา บุญยิ่ง. (2561). <i>การจัดการความรู้ในองค์กร</i>. โรงพิมพ์มหาวิทยาลัย.', '(ธนา, 2561)', 'ธนา (2561)', 'th', 'ธนา', 2561, NULL, '2025-11-19 06:29:32', '2025-12-30 06:29:32'),
(52, 12, 1, 73, '{}', 'ประยุกต์ วิชาการ. (2562). <i>พฤติกรรมการแสวงหาสารสนเทศ</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(ประยุกต์, 2562)', 'ประยุกต์ (2562)', 'th', 'ประยุกต์', 2562, NULL, '2025-12-28 06:29:32', '2025-12-30 06:42:44'),
(53, 12, 1, 73, '{}', 'ศิริพร ใฝ่รู้. (2564). <i>พฤติกรรมการแสวงหาสารสนเทศ</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(ศิริพร, 2564)', 'ศิริพร (2564)', 'th', 'ศิริพร', 2564, NULL, '2025-12-18 06:29:32', '2025-12-30 06:42:57'),
(54, 12, 1, 73, '{}', 'มณี ทองคำ. (2566). <i>พฤติกรรมการแสวงหาสารสนเทศ</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(มณี, 2566)', 'มณี (2566)', 'th', 'มณี', 2566, NULL, '2025-12-08 06:29:32', '2025-12-30 06:43:03'),
(55, 12, 1, 46, '{}', 'ประยุกต์ วิชาการ. (2560). <i>พฤติกรรมการแสวงหาสารสนเทศ</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(ประยุกต์, 2560)', 'ประยุกต์ (2560)', 'th', 'ประยุกต์', 2560, NULL, '2025-11-28 06:29:32', '2025-12-30 06:29:32'),
(56, 12, 1, 56, '{}', 'ศิริพร ใฝ่รู้. (2562). <i>พฤติกรรมการแสวงหาสารสนเทศ</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(ศิริพร, 2562)', 'ศิริพร (2562)', 'th', 'ศิริพร', 2562, NULL, '2025-11-18 06:29:32', '2025-12-30 06:29:32'),
(57, 12, 1, 73, '{}', 'นภา แสงดาว. (2563). <i>การรู้สารสนเทศในยุคดิจิทัล</i>. ศูนย์หนังสือจุฬา.', '(นภา, 2563)', 'นภา (2563)', 'th', 'นภา', 2563, NULL, '2025-12-27 06:29:32', '2025-12-30 06:42:44'),
(58, 12, 1, 73, '{}', 'วรรณา หนังสือดี. (2565). <i>การรู้สารสนเทศในยุคดิจิทัล</i>. ศูนย์หนังสือจุฬา.', '(วรรณา, 2565)', 'วรรณา (2565)', 'th', 'วรรณา', 2565, NULL, '2025-12-17 06:29:32', '2025-12-30 06:42:57'),
(59, 12, 1, 73, '{}', 'สุรชัย เก่งมาก. (2567). <i>การรู้สารสนเทศในยุคดิจิทัล</i>. ศูนย์หนังสือจุฬา.', '(สุรชัย, 2567)', 'สุรชัย (2567)', 'th', 'สุรชัย', 2567, NULL, '2025-12-07 06:29:32', '2025-12-30 06:43:03'),
(60, 12, 1, 47, '{}', 'นภา แสงดาว. (2561). <i>การรู้สารสนเทศในยุคดิจิทัล</i>. ศูนย์หนังสือจุฬา.', '(นภา, 2561)', 'นภา (2561)', 'th', 'นภา', 2561, NULL, '2025-11-27 06:29:32', '2025-12-30 06:29:32'),
(61, 12, 1, 57, '{}', 'วรรณา หนังสือดี. (2563). <i>การรู้สารสนเทศในยุคดิจิทัล</i>. ศูนย์หนังสือจุฬา.', '(วรรณา, 2563)', 'วรรณา (2563)', 'th', 'วรรณา', 2563, NULL, '2025-11-17 06:29:32', '2025-12-30 06:29:32'),
(62, 12, 1, 73, '{}', 'วิชัย พัฒนา. (2564). <i>การอนุรักษ์เอกสารโบราณ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(วิชัย, 2564)', 'วิชัย (2564)', 'th', 'วิชัย', 2564, NULL, '2025-12-26 06:29:32', '2025-12-30 06:42:44'),
(63, 12, 1, 73, '{}', 'สุดา ห้องสมุด. (2566). <i>การอนุรักษ์เอกสารโบราณ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(สุดา, 2566)', 'สุดา (2566)', 'th', 'สุดา', 2566, NULL, '2025-12-16 06:29:32', '2025-12-30 06:42:57'),
(64, 12, 1, 68, '{}', 'พิมพ์ใจ รักษ์ไทย. (2560). <i>การอนุรักษ์เอกสารโบราณ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(พิมพ์ใจ, 2560)', 'พิมพ์ใจ (2560)', 'th', 'พิมพ์ใจ', 2560, NULL, '2025-12-06 06:29:32', '2025-12-30 06:29:32'),
(65, 12, 1, 48, '{}', 'วิชัย พัฒนา. (2562). <i>การอนุรักษ์เอกสารโบราณ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(วิชัย, 2562)', 'วิชัย (2562)', 'th', 'วิชัย', 2562, NULL, '2025-11-26 06:29:32', '2025-12-30 06:29:32'),
(66, 12, 1, 58, '{}', 'สุดา ห้องสมุด. (2564). <i>การอนุรักษ์เอกสารโบราณ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(สุดา, 2564)', 'สุดา (2564)', 'th', 'สุดา', 2564, NULL, '2025-11-16 06:29:32', '2025-12-30 06:29:32'),
(67, 12, 1, 73, '{}', 'กมลา สุขใจ. (2565). <i>เทคโนโลยีสารสนเทศสำหรับห้องสมุด</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(กมลา, 2565)', 'กมลา (2565)', 'th', 'กมลา', 2565, NULL, '2025-12-25 06:29:32', '2025-12-30 06:42:44'),
(68, 12, 1, 73, '{}', 'สมชาย ใจดี. (2567). <i>เทคโนโลยีสารสนเทศสำหรับห้องสมุด</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(สมชาย, 2567)', 'สมชาย (2567)', 'th', 'สมชาย', 2567, NULL, '2025-12-15 06:29:32', '2025-12-30 06:42:57'),
(69, 12, 1, 69, '{}', 'จิตรา สายธาร. (2561). <i>เทคโนโลยีสารสนเทศสำหรับห้องสมุด</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(จิตรา, 2561)', 'จิตรา (2561)', 'th', 'จิตรา', 2561, NULL, '2025-12-05 06:29:32', '2025-12-30 06:29:32'),
(70, 12, 1, 49, '{}', 'กมลา สุขใจ. (2563). <i>เทคโนโลยีสารสนเทศสำหรับห้องสมุด</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(กมลา, 2563)', 'กมลา (2563)', 'th', 'กมลา', 2563, NULL, '2025-11-25 06:29:32', '2025-12-30 06:29:32'),
(71, 12, 1, 59, '{}', 'สมชาย ใจดี. (2565). <i>เทคโนโลยีสารสนเทศสำหรับห้องสมุด</i>. สำนักพิมพ์จุฬาลงกรณ์.', '(สมชาย, 2565)', 'สมชาย (2565)', 'th', 'สมชาย', 2565, NULL, '2025-11-15 06:29:32', '2025-12-30 06:29:32'),
(72, 12, 1, 73, '{}', 'อรุณ ศรีสว่าง. (2566). <i>การจัดหมวดหมู่ทรัพยากรสารสนเทศ</i>. โรงพิมพ์มหาวิทยาลัย.', '(อรุณ, 2566)', 'อรุณ (2566)', 'th', 'อรุณ', 2566, NULL, '2025-12-24 06:29:32', '2025-12-30 06:42:50'),
(73, 12, 1, 73, '{}', 'สมหญิง รักเรียน. (2560). <i>การจัดหมวดหมู่ทรัพยากรสารสนเทศ</i>. โรงพิมพ์มหาวิทยาลัย.', '(สมหญิง, 2560)', 'สมหญิง (2560)', 'th', 'สมหญิง', 2560, NULL, '2025-12-14 06:29:32', '2025-12-30 06:42:57'),
(74, 12, 1, 70, '{}', 'ธนา บุญยิ่ง. (2562). <i>การจัดหมวดหมู่ทรัพยากรสารสนเทศ</i>. โรงพิมพ์มหาวิทยาลัย.', '(ธนา, 2562)', 'ธนา (2562)', 'th', 'ธนา', 2562, NULL, '2025-12-04 06:29:32', '2025-12-30 06:29:32'),
(75, 12, 1, 50, '{}', 'อรุณ ศรีสว่าง. (2564). <i>การจัดหมวดหมู่ทรัพยากรสารสนเทศ</i>. โรงพิมพ์มหาวิทยาลัย.', '(อรุณ, 2564)', 'อรุณ (2564)', 'th', 'อรุณ', 2564, NULL, '2025-11-24 06:29:32', '2025-12-30 06:29:32'),
(76, 12, 1, 60, '{}', 'สมหญิง รักเรียน. (2566). <i>การจัดหมวดหมู่ทรัพยากรสารสนเทศ</i>. โรงพิมพ์มหาวิทยาลัย.', '(สมหญิง, 2566)', 'สมหญิง (2566)', 'th', 'สมหญิง', 2566, NULL, '2025-11-14 06:29:32', '2025-12-30 06:29:32'),
(77, 12, 1, 73, '{}', 'มณี ทองคำ. (2567). <i>บริการตอบคำถามและช่วยค้นคว้า</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(มณี, 2567)', 'มณี (2567)', 'th', 'มณี', 2567, NULL, '2025-12-23 06:29:32', '2025-12-30 06:42:50'),
(78, 12, 1, 73, '{}', 'ประยุกต์ วิชาการ. (2561). <i>บริการตอบคำถามและช่วยค้นคว้า</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(ประยุกต์, 2561)', 'ประยุกต์ (2561)', 'th', 'ประยุกต์', 2561, NULL, '2025-12-13 06:29:32', '2025-12-30 06:42:57'),
(79, 12, 1, 71, '{}', 'ศิริพร ใฝ่รู้. (2563). <i>บริการตอบคำถามและช่วยค้นคว้า</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(ศิริพร, 2563)', 'ศิริพร (2563)', 'th', 'ศิริพร', 2563, NULL, '2025-12-03 06:29:32', '2025-12-30 06:29:32'),
(80, 12, 1, 51, '{}', 'มณี ทองคำ. (2565). <i>บริการตอบคำถามและช่วยค้นคว้า</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(มณี, 2565)', 'มณี (2565)', 'th', 'มณี', 2565, NULL, '2025-11-23 06:29:32', '2025-12-30 06:29:32'),
(81, 12, 1, 61, '{}', 'ประยุกต์ วิชาการ. (2567). <i>บริการตอบคำถามและช่วยค้นคว้า</i>. สำนักพิมพ์แห่งจุฬาฯ.', '(ประยุกต์, 2567)', 'ประยุกต์ (2567)', 'th', 'ประยุกต์', 2567, NULL, '2025-11-13 06:29:32', '2025-12-30 06:29:32'),
(82, 12, 1, 73, '{}', 'สุรชัย เก่งมาก. (2560). <i>การพัฒนาทรัพยากรห้องสมุด</i>. ศูนย์หนังสือจุฬา.', '(สุรชัย, 2560)', 'สุรชัย (2560)', 'th', 'สุรชัย', 2560, NULL, '2025-12-22 06:29:32', '2025-12-30 06:42:50'),
(83, 12, 1, 73, '{}', 'นภา แสงดาว. (2562). <i>การพัฒนาทรัพยากรห้องสมุด</i>. ศูนย์หนังสือจุฬา.', '(นภา, 2562)', 'นภา (2562)', 'th', 'นภา', 2562, NULL, '2025-12-12 06:29:32', '2025-12-30 06:43:03'),
(84, 12, 1, NULL, '{}', 'วรรณา หนังสือดี. (2564). <i>การพัฒนาทรัพยากรห้องสมุด</i>. ศูนย์หนังสือจุฬา.', '(วรรณา, 2564)', 'วรรณา (2564)', 'th', 'วรรณา', 2564, NULL, '2025-12-02 06:29:32', '2026-01-30 06:55:59'),
(85, 12, 1, 52, '{}', 'สุรชัย เก่งมาก. (2566). <i>การพัฒนาทรัพยากรห้องสมุด</i>. ศูนย์หนังสือจุฬา.', '(สุรชัย, 2566)', 'สุรชัย (2566)', 'th', 'สุรชัย', 2566, NULL, '2025-11-22 06:29:32', '2025-12-30 06:29:32'),
(86, 12, 1, 62, '{}', 'นภา แสงดาว. (2560). <i>การพัฒนาทรัพยากรห้องสมุด</i>. ศูนย์หนังสือจุฬา.', '(นภา, 2560)', 'นภา (2560)', 'th', 'นภา', 2560, NULL, '2025-11-12 06:29:32', '2025-12-30 06:29:32'),
(87, 12, 1, 73, '{}', 'พิมพ์ใจ รักษ์ไทย. (2561). <i>ระบบห้องสมุดอัตโนมัติ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(พิมพ์ใจ, 2561)', 'พิมพ์ใจ (2561)', 'th', 'พิมพ์ใจ', 2561, NULL, '2025-12-21 06:29:32', '2025-12-30 06:42:50'),
(88, 12, 1, 73, '{}', 'วิชัย พัฒนา. (2563). <i>ระบบห้องสมุดอัตโนมัติ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(วิชัย, 2563)', 'วิชัย (2563)', 'th', 'วิชัย', 2563, NULL, '2025-12-11 06:29:32', '2025-12-30 06:43:03'),
(89, 12, 1, 73, '{}', 'สุดา ห้องสมุด. (2565). <i>ระบบห้องสมุดอัตโนมัติ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(สุดา, 2565)', 'สุดา (2565)', 'th', 'สุดา', 2565, NULL, '2025-12-01 06:29:32', '2025-12-30 06:29:32'),
(90, 12, 1, 53, '{}', 'พิมพ์ใจ รักษ์ไทย. (2567). <i>ระบบห้องสมุดอัตโนมัติ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(พิมพ์ใจ, 2567)', 'พิมพ์ใจ (2567)', 'th', 'พิมพ์ใจ', 2567, NULL, '2025-11-21 06:29:32', '2025-12-30 06:29:32'),
(91, 12, 1, 63, '{}', 'วิชัย พัฒนา. (2561). <i>ระบบห้องสมุดอัตโนมัติ</i>. สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์.', '(วิชัย, 2561)', 'วิชัย (2561)', 'th', 'วิชัย', 2561, NULL, '2025-11-11 06:29:32', '2025-12-30 06:29:32'),
(105, 12, 2, 44, '{}', 'กานต์ สารสนเทศ. (2562). การประยุกต์ใช้ AI ในห้องสมุด. <i>วารสารบรรณารักษศาสตร์</i>, 10(1), 1-15.', '(กานต์, 2562)', 'กานต์ (2562)', 'th', 'กานต์', 2562, NULL, '2025-11-10 06:29:32', '2025-12-30 06:29:32'),
(106, 12, 2, 54, '{}', 'บัณฑิต การศึกษา. (2566). การประยุกต์ใช้ AI ในห้องสมุด. <i>วารสารบรรณารักษศาสตร์</i>, 20(3), 151-165.', '(บัณฑิต, 2566)', 'บัณฑิต (2566)', 'th', 'บัณฑิต', 2566, NULL, '2025-10-31 06:29:32', '2025-12-30 06:29:32'),
(107, 12, 2, 64, '{}', 'ฐิติมา ความรู้. (2564). การประยุกต์ใช้ AI ในห้องสมุด. <i>วารสารบรรณารักษศาสตร์</i>, 10(1), 301-315.', '(ฐิติมา, 2564)', 'ฐิติมา (2564)', 'th', 'ฐิติมา', 2564, NULL, '2025-10-21 06:29:32', '2025-12-30 06:29:32'),
(108, 12, 2, 44, '{}', 'กานต์ สารสนเทศ. (2562). การประยุกต์ใช้ AI ในห้องสมุด. <i>วารสารบรรณารักษศาสตร์</i>, 20(3), 451-465.', '(กานต์, 2562)', 'กานต์ (2562)', 'th', 'กานต์', 2562, NULL, '2025-10-11 06:29:32', '2025-12-30 06:29:32'),
(109, 12, 2, 54, '{}', 'บัณฑิต การศึกษา. (2566). การประยุกต์ใช้ AI ในห้องสมุด. <i>วารสารบรรณารักษศาสตร์</i>, 10(1), 601-615.', '(บัณฑิต, 2566)', 'บัณฑิต (2566)', 'th', 'บัณฑิต', 2566, NULL, '2025-10-01 06:29:32', '2025-12-30 06:29:32'),
(110, 12, 2, 45, '{}', 'ปิยะ ห้องสมุด. (2563). การพัฒนาระบบสืบค้นอัจฉริยะ. <i>วารสารสารสนเทศศาสตร์</i>, 11(2), 16-30.', '(ปิยะ, 2563)', 'ปิยะ (2563)', 'th', 'ปิยะ', 2563, NULL, '2025-11-09 06:29:32', '2025-12-30 06:29:32'),
(111, 12, 2, 55, '{}', 'ปรีชา สารนิเทศ. (2567). การพัฒนาระบบสืบค้นอัจฉริยะ. <i>วารสารสารสนเทศศาสตร์</i>, 21(4), 166-180.', '(ปรีชา, 2567)', 'ปรีชา (2567)', 'th', 'ปรีชา', 2567, NULL, '2025-10-30 06:29:32', '2025-12-30 06:29:32'),
(112, 12, 2, 65, '{}', 'ณัฐพล วิจัย. (2565). การพัฒนาระบบสืบค้นอัจฉริยะ. <i>วารสารสารสนเทศศาสตร์</i>, 11(2), 316-330.', '(ณัฐพล, 2565)', 'ณัฐพล (2565)', 'th', 'ณัฐพล', 2565, NULL, '2025-10-20 06:29:32', '2025-12-30 06:29:32'),
(113, 12, 2, 45, '{}', 'ปิยะ ห้องสมุด. (2563). การพัฒนาระบบสืบค้นอัจฉริยะ. <i>วารสารสารสนเทศศาสตร์</i>, 21(4), 466-480.', '(ปิยะ, 2563)', 'ปิยะ (2563)', 'th', 'ปิยะ', 2563, NULL, '2025-10-10 06:29:32', '2025-12-30 06:29:32'),
(114, 12, 2, 55, '{}', 'ปรีชา สารนิเทศ. (2567). การพัฒนาระบบสืบค้นอัจฉริยะ. <i>วารสารสารสนเทศศาสตร์</i>, 11(2), 616-630.', '(ปรีชา, 2567)', 'ปรีชา (2567)', 'th', 'ปรีชา', 2567, NULL, '2025-09-30 06:29:32', '2025-12-30 06:29:32'),
(115, 12, 2, 46, '{}', 'ชุติมา วิชาการ. (2564). พฤติกรรมผู้ใช้ห้องสมุดยุคใหม่. <i>วารสารห้องสมุด</i>, 12(3), 31-45.', '(ชุติมา, 2564)', 'ชุติมา (2564)', 'th', 'ชุติมา', 2564, NULL, '2025-11-08 06:29:32', '2025-12-30 06:29:32'),
(116, 12, 2, 56, '{}', 'ผกามาศ ห้องสมุด. (2562). พฤติกรรมผู้ใช้ห้องสมุดยุคใหม่. <i>วารสารห้องสมุด</i>, 22(1), 181-195.', '(ผกามาศ, 2562)', 'ผกามาศ (2562)', 'th', 'ผกามาศ', 2562, NULL, '2025-10-29 06:29:32', '2025-12-30 06:29:32'),
(117, 12, 2, 66, '{}', 'ตรีนุช สำนักพิมพ์. (2566). พฤติกรรมผู้ใช้ห้องสมุดยุคใหม่. <i>วารสารห้องสมุด</i>, 12(3), 331-345.', '(ตรีนุช, 2566)', 'ตรีนุช (2566)', 'th', 'ตรีนุช', 2566, NULL, '2025-10-19 06:29:32', '2025-12-30 06:29:32'),
(118, 12, 2, 46, '{}', 'ชุติมา วิชาการ. (2564). พฤติกรรมผู้ใช้ห้องสมุดยุคใหม่. <i>วารสารห้องสมุด</i>, 22(1), 481-495.', '(ชุติมา, 2564)', 'ชุติมา (2564)', 'th', 'ชุติมา', 2564, NULL, '2025-10-09 06:29:32', '2025-12-30 06:29:32'),
(119, 12, 2, 56, '{}', 'ผกามาศ ห้องสมุด. (2562). พฤติกรรมผู้ใช้ห้องสมุดยุคใหม่. <i>วารสารห้องสมุด</i>, 12(3), 631-645.', '(ผกามาศ, 2562)', 'ผกามาศ (2562)', 'th', 'ผกามาศ', 2562, NULL, '2025-09-29 06:29:32', '2025-12-30 06:29:32'),
(120, 12, 2, 47, '{}', 'ดวงใจ รักอ่าน. (2565). การจัดการข้อมูลวิจัย. <i>วารสารวิจัยสารสนเทศ</i>, 13(4), 46-60.', '(ดวงใจ, 2565)', 'ดวงใจ (2565)', 'th', 'ดวงใจ', 2565, NULL, '2025-11-07 06:29:32', '2025-12-30 06:29:32'),
(121, 12, 2, 57, '{}', 'พัชรี วารสาร. (2563). การจัดการข้อมูลวิจัย. <i>วารสารวิจัยสารสนเทศ</i>, 23(2), 196-210.', '(พัชรี, 2563)', 'พัชรี (2563)', 'th', 'พัชรี', 2563, NULL, '2025-10-28 06:29:32', '2025-12-30 06:29:32'),
(122, 12, 2, 67, '{}', 'ธีรยุทธ เทคโนโลยี. (2567). การจัดการข้อมูลวิจัย. <i>วารสารวิจัยสารสนเทศ</i>, 13(4), 346-360.', '(ธีรยุทธ, 2567)', 'ธีรยุทธ (2567)', 'th', 'ธีรยุทธ', 2567, NULL, '2025-10-18 06:29:32', '2025-12-30 06:29:32'),
(123, 12, 2, 47, '{}', 'ดวงใจ รักอ่าน. (2565). การจัดการข้อมูลวิจัย. <i>วารสารวิจัยสารสนเทศ</i>, 23(2), 496-510.', '(ดวงใจ, 2565)', 'ดวงใจ (2565)', 'th', 'ดวงใจ', 2565, NULL, '2025-10-08 06:29:32', '2025-12-30 06:29:32'),
(124, 12, 2, 57, '{}', 'พัชรี วารสาร. (2563). การจัดการข้อมูลวิจัย. <i>วารสารวิจัยสารสนเทศ</i>, 13(4), 646-660.', '(พัชรี, 2563)', 'พัชรี (2563)', 'th', 'พัชรี', 2563, NULL, '2025-09-28 06:29:32', '2025-12-30 06:29:32'),
(125, 12, 2, 48, '{}', 'เอกชัย พัฒนา. (2566). การอนุรักษ์สื่อดิจิทัล. <i>วารสารวิชาการ</i>, 14(1), 61-75.', '(เอกชัย, 2566)', 'เอกชัย (2566)', 'th', 'เอกชัย', 2566, NULL, '2025-11-06 06:29:32', '2025-12-30 06:29:32'),
(126, 12, 2, 58, '{}', 'มนตรี ข้อมูล. (2564). การอนุรักษ์สื่อดิจิทัล. <i>วารสารวิชาการ</i>, 24(3), 211-225.', '(มนตรี, 2564)', 'มนตรี (2564)', 'th', 'มนตรี', 2564, NULL, '2025-10-27 06:29:32', '2025-12-30 06:29:32'),
(127, 12, 2, 68, '{}', 'นวลจันทร์ ดิจิทัล. (2562). การอนุรักษ์สื่อดิจิทัล. <i>วารสารวิชาการ</i>, 14(1), 361-375.', '(นวลจันทร์, 2562)', 'นวลจันทร์ (2562)', 'th', 'นวลจันทร์', 2562, NULL, '2025-10-17 06:29:32', '2025-12-30 06:29:32'),
(128, 12, 2, 48, '{}', 'เอกชัย พัฒนา. (2566). การอนุรักษ์สื่อดิจิทัล. <i>วารสารวิชาการ</i>, 24(3), 511-525.', '(เอกชัย, 2566)', 'เอกชัย (2566)', 'th', 'เอกชัย', 2566, NULL, '2025-10-07 06:29:32', '2025-12-30 06:29:32'),
(129, 12, 2, 58, '{}', 'มนตรี ข้อมูล. (2564). การอนุรักษ์สื่อดิจิทัล. <i>วารสารวิชาการ</i>, 14(1), 661-675.', '(มนตรี, 2564)', 'มนตรี (2564)', 'th', 'มนตรี', 2564, NULL, '2025-09-27 06:29:32', '2025-12-30 06:29:32'),
(130, 12, 2, 49, '{}', 'ฐิติมา ความรู้. (2567). บริการห้องสมุดแบบออนไลน์. <i>วารสารบรรณารักษศาสตร์</i>, 15(2), 76-90.', '(ฐิติมา, 2567)', 'ฐิติมา (2567)', 'th', 'ฐิติมา', 2567, NULL, '2025-11-05 06:29:32', '2025-12-30 06:29:32'),
(131, 12, 2, 59, '{}', 'กานต์ สารสนเทศ. (2565). บริการห้องสมุดแบบออนไลน์. <i>วารสารบรรณารักษศาสตร์</i>, 25(4), 226-240.', '(กานต์, 2565)', 'กานต์ (2565)', 'th', 'กานต์', 2565, NULL, '2025-10-26 06:29:32', '2025-12-30 06:29:32'),
(132, 12, 2, 69, '{}', 'บัณฑิต การศึกษา. (2563). บริการห้องสมุดแบบออนไลน์. <i>วารสารบรรณารักษศาสตร์</i>, 15(2), 376-390.', '(บัณฑิต, 2563)', 'บัณฑิต (2563)', 'th', 'บัณฑิต', 2563, NULL, '2025-10-16 06:29:32', '2025-12-30 06:29:32'),
(133, 12, 2, 49, '{}', 'ฐิติมา ความรู้. (2567). บริการห้องสมุดแบบออนไลน์. <i>วารสารบรรณารักษศาสตร์</i>, 25(4), 526-540.', '(ฐิติมา, 2567)', 'ฐิติมา (2567)', 'th', 'ฐิติมา', 2567, NULL, '2025-10-06 06:29:32', '2025-12-30 06:29:32'),
(134, 12, 2, 59, '{}', 'กานต์ สารสนเทศ. (2565). บริการห้องสมุดแบบออนไลน์. <i>วารสารบรรณารักษศาสตร์</i>, 15(2), 676-690.', '(กานต์, 2565)', 'กานต์ (2565)', 'th', 'กานต์', 2565, NULL, '2025-09-26 06:29:32', '2025-12-30 06:29:32'),
(135, 12, 2, 50, '{}', 'ณัฐพล วิจัย. (2562). การประเมินคุณภาพวารสาร. <i>วารสารสารสนเทศศาสตร์</i>, 16(3), 91-105.', '(ณัฐพล, 2562)', 'ณัฐพล (2562)', 'th', 'ณัฐพล', 2562, NULL, '2025-11-04 06:29:32', '2025-12-30 06:29:32'),
(136, 12, 2, 60, '{}', 'ปิยะ ห้องสมุด. (2566). การประเมินคุณภาพวารสาร. <i>วารสารสารสนเทศศาสตร์</i>, 26(1), 241-255.', '(ปิยะ, 2566)', 'ปิยะ (2566)', 'th', 'ปิยะ', 2566, NULL, '2025-10-25 06:29:32', '2025-12-30 06:29:32'),
(137, 12, 2, 70, '{}', 'ปรีชา สารนิเทศ. (2564). การประเมินคุณภาพวารสาร. <i>วารสารสารสนเทศศาสตร์</i>, 16(3), 391-405.', '(ปรีชา, 2564)', 'ปรีชา (2564)', 'th', 'ปรีชา', 2564, NULL, '2025-10-15 06:29:32', '2025-12-30 06:29:32'),
(138, 12, 2, 50, '{}', 'ณัฐพล วิจัย. (2562). การประเมินคุณภาพวารสาร. <i>วารสารสารสนเทศศาสตร์</i>, 26(1), 541-555.', '(ณัฐพล, 2562)', 'ณัฐพล (2562)', 'th', 'ณัฐพล', 2562, NULL, '2025-10-05 06:29:32', '2025-12-30 06:29:32'),
(139, 12, 2, 60, '{}', 'ปิยะ ห้องสมุด. (2566). การประเมินคุณภาพวารสาร. <i>วารสารสารสนเทศศาสตร์</i>, 16(3), 691-705.', '(ปิยะ, 2566)', 'ปิยะ (2566)', 'th', 'ปิยะ', 2566, NULL, '2025-09-25 06:29:32', '2025-12-30 06:29:32'),
(140, 12, 2, 51, '{}', 'ตรีนุช สำนักพิมพ์. (2563). เครือข่ายห้องสมุดดิจิทัล. <i>วารสารห้องสมุด</i>, 17(4), 106-120.', '(ตรีนุช, 2563)', 'ตรีนุช (2563)', 'th', 'ตรีนุช', 2563, NULL, '2025-11-03 06:29:32', '2025-12-30 06:29:32'),
(141, 12, 2, 61, '{}', 'ชุติมา วิชาการ. (2567). เครือข่ายห้องสมุดดิจิทัล. <i>วารสารห้องสมุด</i>, 27(2), 256-270.', '(ชุติมา, 2567)', 'ชุติมา (2567)', 'th', 'ชุติมา', 2567, NULL, '2025-10-24 06:29:32', '2025-12-30 06:29:32'),
(142, 12, 2, 71, '{}', 'ผกามาศ ห้องสมุด. (2565). เครือข่ายห้องสมุดดิจิทัล. <i>วารสารห้องสมุด</i>, 17(4), 406-420.', '(ผกามาศ, 2565)', 'ผกามาศ (2565)', 'th', 'ผกามาศ', 2565, NULL, '2025-10-14 06:29:32', '2025-12-30 06:29:32'),
(143, 12, 2, 51, '{}', 'ตรีนุช สำนักพิมพ์. (2563). เครือข่ายห้องสมุดดิจิทัล. <i>วารสารห้องสมุด</i>, 27(2), 556-570.', '(ตรีนุช, 2563)', 'ตรีนุช (2563)', 'th', 'ตรีนุช', 2563, NULL, '2025-10-04 06:29:32', '2025-12-30 06:29:32'),
(144, 12, 2, 61, '{}', 'ชุติมา วิชาการ. (2567). เครือข่ายห้องสมุดดิจิทัล. <i>วารสารห้องสมุด</i>, 17(4), 706-720.', '(ชุติมา, 2567)', 'ชุติมา (2567)', 'th', 'ชุติมา', 2567, NULL, '2025-09-24 06:29:32', '2025-12-30 06:29:32'),
(145, 12, 2, 52, '{}', 'ธีรยุทธ เทคโนโลยี. (2564). การสร้างคลังข้อมูลสถาบัน. <i>วารสารวิจัยสารสนเทศ</i>, 18(1), 121-135.', '(ธีรยุทธ, 2564)', 'ธีรยุทธ (2564)', 'th', 'ธีรยุทธ', 2564, NULL, '2025-11-02 06:29:32', '2025-12-30 06:29:32'),
(146, 12, 2, 62, '{}', 'ดวงใจ รักอ่าน. (2562). การสร้างคลังข้อมูลสถาบัน. <i>วารสารวิจัยสารสนเทศ</i>, 28(3), 271-285.', '(ดวงใจ, 2562)', 'ดวงใจ (2562)', 'th', 'ดวงใจ', 2562, NULL, '2025-10-23 06:29:32', '2025-12-30 06:29:32'),
(147, 12, 2, NULL, '{}', 'พัชรี วารสาร. (2566). การสร้างคลังข้อมูลสถาบัน. <i>วารสารวิจัยสารสนเทศ</i>, 18(1), 421-435.', '(พัชรี, 2566)', 'พัชรี (2566)', 'th', 'พัชรี', 2566, NULL, '2025-10-13 06:29:32', '2026-01-30 06:55:59'),
(148, 12, 2, 52, '{}', 'ธีรยุทธ เทคโนโลยี. (2564). การสร้างคลังข้อมูลสถาบัน. <i>วารสารวิจัยสารสนเทศ</i>, 28(3), 571-585.', '(ธีรยุทธ, 2564)', 'ธีรยุทธ (2564)', 'th', 'ธีรยุทธ', 2564, NULL, '2025-10-03 06:29:32', '2025-12-30 06:29:32'),
(149, 12, 2, 62, '{}', 'ดวงใจ รักอ่าน. (2562). การสร้างคลังข้อมูลสถาบัน. <i>วารสารวิจัยสารสนเทศ</i>, 18(1), 721-735.', '(ดวงใจ, 2562)', 'ดวงใจ (2562)', 'th', 'ดวงใจ', 2562, NULL, '2025-09-23 06:29:32', '2025-12-30 06:29:32'),
(150, 12, 2, 53, '{}', 'นวลจันทร์ ดิจิทัล. (2565). นวัตกรรมห้องสมุด. <i>วารสารวิชาการ</i>, 19(2), 136-150.', '(นวลจันทร์, 2565)', 'นวลจันทร์ (2565)', 'th', 'นวลจันทร์', 2565, NULL, '2025-11-01 06:29:32', '2025-12-30 06:29:32'),
(151, 12, 2, 63, '{}', 'เอกชัย พัฒนา. (2563). นวัตกรรมห้องสมุด. <i>วารสารวิชาการ</i>, 29(4), 286-300.', '(เอกชัย, 2563)', 'เอกชัย (2563)', 'th', 'เอกชัย', 2563, NULL, '2025-10-22 06:29:32', '2025-12-30 06:29:32'),
(152, 12, 2, 73, '{}', 'มนตรี ข้อมูล. (2567). นวัตกรรมห้องสมุด. <i>วารสารวิชาการ</i>, 19(2), 436-450.', '(มนตรี, 2567)', 'มนตรี (2567)', 'th', 'มนตรี', 2567, NULL, '2025-10-12 06:29:32', '2025-12-30 06:29:32'),
(153, 12, 2, 53, '{}', 'นวลจันทร์ ดิจิทัล. (2565). นวัตกรรมห้องสมุด. <i>วารสารวิชาการ</i>, 29(4), 586-600.', '(นวลจันทร์, 2565)', 'นวลจันทร์ (2565)', 'th', 'นวลจันทร์', 2565, NULL, '2025-10-02 06:29:32', '2025-12-30 06:29:32'),
(154, 12, 2, 63, '{}', 'เอกชัย พัฒนา. (2563). นวัตกรรมห้องสมุด. <i>วารสารวิชาการ</i>, 19(2), 736-750.', '(เอกชัย, 2563)', 'เอกชัย (2563)', 'th', 'เอกชัย', 2563, NULL, '2025-09-22 06:29:32', '2025-12-30 06:29:32'),
(168, 12, 1, 44, '{}', 'Smith, J. (2018). <i>Digital library systems and innovations</i>. Springer.', '(Smith, 2018)', 'Smith (2018)', 'en', 'Smith', 2018, NULL, '2025-09-21 06:29:32', '2025-12-30 06:29:32'),
(169, 12, 1, 45, '{}', 'Johnson, M. (2019). <i>Information literacy in higher education</i>. Wiley.', '(Johnson, 2019)', 'Johnson (2019)', 'en', 'Johnson', 2019, NULL, '2025-09-20 06:29:32', '2025-12-30 06:29:32'),
(170, 12, 1, 46, '{}', 'Williams, K. (2020). <i>Knowledge management best practices</i>. Elsevier.', '(Williams, 2020)', 'Williams (2020)', 'en', 'Williams', 2020, NULL, '2025-09-19 06:29:32', '2025-12-30 06:29:32'),
(171, 12, 1, 47, '{}', 'Brown, R. (2021). <i>User experience design for libraries</i>. Cambridge University Press.', '(Brown, 2021)', 'Brown (2021)', 'en', 'Brown', 2021, NULL, '2025-09-18 06:29:32', '2025-12-30 06:29:32'),
(172, 12, 1, 48, '{}', 'Davis, S. (2022). <i>Metadata standards and applications</i>. Oxford University Press.', '(Davis, 2022)', 'Davis (2022)', 'en', 'Davis', 2022, NULL, '2025-09-17 06:29:32', '2025-12-30 06:29:32'),
(173, 12, 1, 49, '{}', 'Miller, A. (2023). <i>Open access publishing strategies</i>. Routledge.', '(Miller, 2023)', 'Miller (2023)', 'en', 'Miller', 2023, NULL, '2025-09-16 06:29:32', '2025-12-30 06:29:32'),
(174, 12, 1, 50, '{}', 'Wilson, T. (2024). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Wilson, 2024)', 'Wilson (2024)', 'en', 'Wilson', 2024, NULL, '2025-09-15 06:29:32', '2025-12-30 06:29:32'),
(175, 12, 1, 51, '{}', 'Moore, L. (2018). <i>Data curation and preservation</i>. MIT Press.', '(Moore, 2018)', 'Moore (2018)', 'en', 'Moore', 2018, NULL, '2025-09-14 06:29:32', '2025-12-30 06:29:32'),
(176, 12, 1, 52, '{}', 'Taylor, C. (2019). <i>Research data management handbook</i>. ALA Editions.', '(Taylor, 2019)', 'Taylor (2019)', 'en', 'Taylor', 2019, NULL, '2025-09-13 06:29:32', '2025-12-30 06:29:32'),
(177, 12, 1, 53, '{}', 'Anderson, P. (2020). <i>Scholarly communication trends</i>. Facet Publishing.', '(Anderson, 2020)', 'Anderson (2020)', 'en', 'Anderson', 2020, NULL, '2025-09-12 06:29:32', '2025-12-30 06:29:32'),
(178, 12, 1, 54, '{}', 'Thomas, E. (2021). <i>Digital library systems and innovations</i>. Springer.', '(Thomas, 2021)', 'Thomas (2021)', 'en', 'Thomas', 2021, NULL, '2025-09-11 06:29:32', '2025-12-30 06:29:32'),
(179, 12, 1, 55, '{}', 'Jackson, H. (2022). <i>Information literacy in higher education</i>. Wiley.', '(Jackson, 2022)', 'Jackson (2022)', 'en', 'Jackson', 2022, NULL, '2025-09-10 06:29:32', '2025-12-30 06:29:32'),
(180, 12, 1, 56, '{}', 'White, N. (2023). <i>Knowledge management best practices</i>. Elsevier.', '(White, 2023)', 'White (2023)', 'en', 'White', 2023, NULL, '2025-09-09 06:29:32', '2025-12-30 06:29:32'),
(181, 12, 1, 57, '{}', 'Harris, D. (2024). <i>User experience design for libraries</i>. Cambridge University Press.', '(Harris, 2024)', 'Harris (2024)', 'en', 'Harris', 2024, NULL, '2025-09-08 06:29:32', '2025-12-30 06:29:32'),
(182, 12, 1, 58, '{}', 'Martin, G. (2018). <i>Metadata standards and applications</i>. Oxford University Press.', '(Martin, 2018)', 'Martin (2018)', 'en', 'Martin', 2018, NULL, '2025-09-07 06:29:32', '2025-12-30 06:29:32'),
(183, 12, 1, 59, '{}', 'Smith, J. (2019). <i>Open access publishing strategies</i>. Routledge.', '(Smith, 2019)', 'Smith (2019)', 'en', 'Smith', 2019, NULL, '2025-09-06 06:29:32', '2025-12-30 06:29:32'),
(184, 12, 1, 60, '{}', 'Johnson, M. (2020). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Johnson, 2020)', 'Johnson (2020)', 'en', 'Johnson', 2020, NULL, '2025-09-05 06:29:32', '2025-12-30 06:29:32'),
(185, 12, 1, 61, '{}', 'Williams, K. (2021). <i>Data curation and preservation</i>. MIT Press.', '(Williams, 2021)', 'Williams (2021)', 'en', 'Williams', 2021, NULL, '2025-09-04 06:29:32', '2025-12-30 06:29:32'),
(186, 12, 1, 62, '{}', 'Brown, R. (2022). <i>Research data management handbook</i>. ALA Editions.', '(Brown, 2022)', 'Brown (2022)', 'en', 'Brown', 2022, NULL, '2025-09-03 06:29:32', '2025-12-30 06:29:32'),
(187, 12, 1, 63, '{}', 'Davis, S. (2023). <i>Scholarly communication trends</i>. Facet Publishing.', '(Davis, 2023)', 'Davis (2023)', 'en', 'Davis', 2023, NULL, '2025-09-02 06:29:32', '2025-12-30 06:29:32'),
(188, 12, 1, 64, '{}', 'Miller, A. (2024). <i>Digital library systems and innovations</i>. Springer.', '(Miller, 2024)', 'Miller (2024)', 'en', 'Miller', 2024, NULL, '2025-09-01 06:29:32', '2025-12-30 06:29:32'),
(189, 12, 1, 65, '{}', 'Wilson, T. (2018). <i>Information literacy in higher education</i>. Wiley.', '(Wilson, 2018)', 'Wilson (2018)', 'en', 'Wilson', 2018, NULL, '2025-08-31 06:29:32', '2025-12-30 06:29:32'),
(190, 12, 1, 66, '{}', 'Moore, L. (2019). <i>Knowledge management best practices</i>. Elsevier.', '(Moore, 2019)', 'Moore (2019)', 'en', 'Moore', 2019, NULL, '2025-08-30 06:29:32', '2025-12-30 06:29:32'),
(191, 12, 1, 67, '{}', 'Taylor, C. (2020). <i>User experience design for libraries</i>. Cambridge University Press.', '(Taylor, 2020)', 'Taylor (2020)', 'en', 'Taylor', 2020, NULL, '2025-08-29 06:29:32', '2025-12-30 06:29:32'),
(192, 12, 1, 68, '{}', 'Anderson, P. (2021). <i>Metadata standards and applications</i>. Oxford University Press.', '(Anderson, 2021)', 'Anderson (2021)', 'en', 'Anderson', 2021, NULL, '2025-08-28 06:29:32', '2025-12-30 06:29:32'),
(193, 12, 1, 69, '{}', 'Thomas, E. (2022). <i>Open access publishing strategies</i>. Routledge.', '(Thomas, 2022)', 'Thomas (2022)', 'en', 'Thomas', 2022, NULL, '2025-08-27 06:29:32', '2025-12-30 06:29:32'),
(194, 12, 1, 70, '{}', 'Jackson, H. (2023). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Jackson, 2023)', 'Jackson (2023)', 'en', 'Jackson', 2023, NULL, '2025-08-26 06:29:32', '2025-12-30 06:29:32'),
(195, 12, 1, 71, '{}', 'White, N. (2024). <i>Data curation and preservation</i>. MIT Press.', '(White, 2024)', 'White (2024)', 'en', 'White', 2024, NULL, '2025-08-25 06:29:32', '2025-12-30 06:29:32'),
(196, 12, 1, NULL, '{}', 'Harris, D. (2018). <i>Research data management handbook</i>. ALA Editions.', '(Harris, 2018)', 'Harris (2018)', 'en', 'Harris', 2018, NULL, '2025-08-24 06:29:32', '2026-01-30 06:55:59'),
(197, 12, 1, 73, '{}', 'Martin, G. (2019). <i>Scholarly communication trends</i>. Facet Publishing.', '(Martin, 2019)', 'Martin (2019)', 'en', 'Martin', 2019, NULL, '2025-08-23 06:29:32', '2025-12-30 06:29:32'),
(198, 12, 1, 44, '{}', 'Smith, J. (2020). <i>Digital library systems and innovations</i>. Springer.', '(Smith, 2020)', 'Smith (2020)', 'en', 'Smith', 2020, NULL, '2025-08-22 06:29:32', '2025-12-30 06:29:32'),
(199, 12, 1, 45, '{}', 'Johnson, M. (2021). <i>Information literacy in higher education</i>. Wiley.', '(Johnson, 2021)', 'Johnson (2021)', 'en', 'Johnson', 2021, NULL, '2025-08-21 06:29:32', '2025-12-30 06:29:32'),
(200, 12, 1, 46, '{}', 'Williams, K. (2022). <i>Knowledge management best practices</i>. Elsevier.', '(Williams, 2022)', 'Williams (2022)', 'en', 'Williams', 2022, NULL, '2025-08-20 06:29:32', '2025-12-30 06:29:32'),
(201, 12, 1, 47, '{}', 'Brown, R. (2023). <i>User experience design for libraries</i>. Cambridge University Press.', '(Brown, 2023)', 'Brown (2023)', 'en', 'Brown', 2023, NULL, '2025-08-19 06:29:32', '2025-12-30 06:29:32'),
(202, 12, 1, 48, '{}', 'Davis, S. (2024). <i>Metadata standards and applications</i>. Oxford University Press.', '(Davis, 2024)', 'Davis (2024)', 'en', 'Davis', 2024, NULL, '2025-08-18 06:29:32', '2025-12-30 06:29:32'),
(203, 12, 1, 49, '{}', 'Miller, A. (2018). <i>Open access publishing strategies</i>. Routledge.', '(Miller, 2018)', 'Miller (2018)', 'en', 'Miller', 2018, NULL, '2025-08-17 06:29:32', '2025-12-30 06:29:32'),
(204, 12, 1, 50, '{}', 'Wilson, T. (2019). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Wilson, 2019)', 'Wilson (2019)', 'en', 'Wilson', 2019, NULL, '2025-08-16 06:29:32', '2025-12-30 06:29:32'),
(205, 12, 1, 51, '{}', 'Moore, L. (2020). <i>Data curation and preservation</i>. MIT Press.', '(Moore, 2020)', 'Moore (2020)', 'en', 'Moore', 2020, NULL, '2025-08-15 06:29:32', '2025-12-30 06:29:32'),
(206, 12, 1, 52, '{}', 'Taylor, C. (2021). <i>Research data management handbook</i>. ALA Editions.', '(Taylor, 2021)', 'Taylor (2021)', 'en', 'Taylor', 2021, NULL, '2025-08-14 06:29:32', '2025-12-30 06:29:32'),
(207, 12, 1, 53, '{}', 'Anderson, P. (2022). <i>Scholarly communication trends</i>. Facet Publishing.', '(Anderson, 2022)', 'Anderson (2022)', 'en', 'Anderson', 2022, NULL, '2025-08-13 06:29:32', '2025-12-30 06:29:32'),
(208, 12, 1, 54, '{}', 'Thomas, E. (2023). <i>Digital library systems and innovations</i>. Springer.', '(Thomas, 2023)', 'Thomas (2023)', 'en', 'Thomas', 2023, NULL, '2025-08-12 06:29:32', '2025-12-30 06:29:32'),
(209, 12, 1, 55, '{}', 'Jackson, H. (2024). <i>Information literacy in higher education</i>. Wiley.', '(Jackson, 2024)', 'Jackson (2024)', 'en', 'Jackson', 2024, NULL, '2025-08-11 06:29:32', '2025-12-30 06:29:32'),
(210, 12, 1, 56, '{}', 'White, N. (2018). <i>Knowledge management best practices</i>. Elsevier.', '(White, 2018)', 'White (2018)', 'en', 'White', 2018, NULL, '2025-08-10 06:29:32', '2025-12-30 06:29:32'),
(211, 12, 1, 57, '{}', 'Harris, D. (2019). <i>User experience design for libraries</i>. Cambridge University Press.', '(Harris, 2019)', 'Harris (2019)', 'en', 'Harris', 2019, NULL, '2025-08-09 06:29:32', '2025-12-30 06:29:32'),
(212, 12, 1, 58, '{}', 'Martin, G. (2020). <i>Metadata standards and applications</i>. Oxford University Press.', '(Martin, 2020)', 'Martin (2020)', 'en', 'Martin', 2020, NULL, '2025-08-08 06:29:32', '2025-12-30 06:29:32'),
(213, 12, 1, 59, '{}', 'Smith, J. (2021). <i>Open access publishing strategies</i>. Routledge.', '(Smith, 2021)', 'Smith (2021)', 'en', 'Smith', 2021, NULL, '2025-08-07 06:29:32', '2025-12-30 06:29:32'),
(214, 12, 1, 60, '{}', 'Johnson, M. (2022). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Johnson, 2022)', 'Johnson (2022)', 'en', 'Johnson', 2022, NULL, '2025-08-06 06:29:32', '2025-12-30 06:29:32'),
(215, 12, 1, 61, '{}', 'Williams, K. (2023). <i>Data curation and preservation</i>. MIT Press.', '(Williams, 2023)', 'Williams (2023)', 'en', 'Williams', 2023, NULL, '2025-08-05 06:29:32', '2025-12-30 06:29:32'),
(216, 12, 1, 62, '{}', 'Brown, R. (2024). <i>Research data management handbook</i>. ALA Editions.', '(Brown, 2024)', 'Brown (2024)', 'en', 'Brown', 2024, NULL, '2025-08-04 06:29:32', '2025-12-30 06:29:32'),
(217, 12, 1, 63, '{}', 'Davis, S. (2018). <i>Scholarly communication trends</i>. Facet Publishing.', '(Davis, 2018)', 'Davis (2018)', 'en', 'Davis', 2018, NULL, '2025-08-03 06:29:32', '2025-12-30 06:29:32'),
(218, 12, 1, 64, '{}', 'Miller, A. (2019). <i>Digital library systems and innovations</i>. Springer.', '(Miller, 2019)', 'Miller (2019)', 'en', 'Miller', 2019, NULL, '2025-08-02 06:29:32', '2025-12-30 06:29:32'),
(219, 12, 1, 65, '{}', 'Wilson, T. (2020). <i>Information literacy in higher education</i>. Wiley.', '(Wilson, 2020)', 'Wilson (2020)', 'en', 'Wilson', 2020, NULL, '2025-08-01 06:29:32', '2025-12-30 06:29:32'),
(220, 12, 1, 66, '{}', 'Moore, L. (2021). <i>Knowledge management best practices</i>. Elsevier.', '(Moore, 2021)', 'Moore (2021)', 'en', 'Moore', 2021, NULL, '2025-07-31 06:29:32', '2025-12-30 06:29:32'),
(221, 12, 1, 67, '{}', 'Taylor, C. (2022). <i>User experience design for libraries</i>. Cambridge University Press.', '(Taylor, 2022)', 'Taylor (2022)', 'en', 'Taylor', 2022, NULL, '2025-07-30 06:29:32', '2025-12-30 06:29:32'),
(222, 12, 1, 68, '{}', 'Anderson, P. (2023). <i>Metadata standards and applications</i>. Oxford University Press.', '(Anderson, 2023)', 'Anderson (2023)', 'en', 'Anderson', 2023, NULL, '2025-07-29 06:29:32', '2025-12-30 06:29:32'),
(223, 12, 1, 69, '{}', 'Thomas, E. (2024). <i>Open access publishing strategies</i>. Routledge.', '(Thomas, 2024)', 'Thomas (2024)', 'en', 'Thomas', 2024, NULL, '2025-07-28 06:29:32', '2025-12-30 06:29:32'),
(224, 12, 1, 70, '{}', 'Jackson, H. (2018). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Jackson, 2018)', 'Jackson (2018)', 'en', 'Jackson', 2018, NULL, '2025-07-27 06:29:32', '2025-12-30 06:29:32'),
(225, 12, 1, 71, '{}', 'White, N. (2019). <i>Data curation and preservation</i>. MIT Press.', '(White, 2019)', 'White (2019)', 'en', 'White', 2019, NULL, '2025-07-26 06:29:32', '2025-12-30 06:29:32'),
(226, 12, 1, NULL, '{}', 'Harris, D. (2020). <i>Research data management handbook</i>. ALA Editions.', '(Harris, 2020)', 'Harris (2020)', 'en', 'Harris', 2020, NULL, '2025-07-25 06:29:32', '2026-01-30 06:55:59'),
(227, 12, 1, 73, '{}', 'Martin, G. (2021). <i>Scholarly communication trends</i>. Facet Publishing.', '(Martin, 2021)', 'Martin (2021)', 'en', 'Martin', 2021, NULL, '2025-07-24 06:29:32', '2025-12-30 06:29:32'),
(228, 12, 1, 44, '{}', 'Smith, J. (2022). <i>Digital library systems and innovations</i>. Springer.', '(Smith, 2022)', 'Smith (2022)', 'en', 'Smith', 2022, NULL, '2025-07-23 06:29:32', '2025-12-30 06:29:32'),
(229, 12, 1, 45, '{}', 'Johnson, M. (2023). <i>Information literacy in higher education</i>. Wiley.', '(Johnson, 2023)', 'Johnson (2023)', 'en', 'Johnson', 2023, NULL, '2025-07-22 06:29:32', '2025-12-30 06:29:32'),
(230, 12, 1, 46, '{}', 'Williams, K. (2024). <i>Knowledge management best practices</i>. Elsevier.', '(Williams, 2024)', 'Williams (2024)', 'en', 'Williams', 2024, NULL, '2025-07-21 06:29:32', '2025-12-30 06:29:32'),
(231, 12, 1, 47, '{}', 'Brown, R. (2018). <i>User experience design for libraries</i>. Cambridge University Press.', '(Brown, 2018)', 'Brown (2018)', 'en', 'Brown', 2018, NULL, '2025-07-20 06:29:32', '2025-12-30 06:29:32'),
(232, 12, 1, 48, '{}', 'Davis, S. (2019). <i>Metadata standards and applications</i>. Oxford University Press.', '(Davis, 2019)', 'Davis (2019)', 'en', 'Davis', 2019, NULL, '2025-07-19 06:29:32', '2025-12-30 06:29:32'),
(233, 12, 1, 49, '{}', 'Miller, A. (2020). <i>Open access publishing strategies</i>. Routledge.', '(Miller, 2020)', 'Miller (2020)', 'en', 'Miller', 2020, NULL, '2025-07-18 06:29:32', '2025-12-30 06:29:32'),
(234, 12, 1, 50, '{}', 'Wilson, T. (2021). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Wilson, 2021)', 'Wilson (2021)', 'en', 'Wilson', 2021, NULL, '2025-07-17 06:29:32', '2025-12-30 06:29:32'),
(235, 12, 1, 51, '{}', 'Moore, L. (2022). <i>Data curation and preservation</i>. MIT Press.', '(Moore, 2022)', 'Moore (2022)', 'en', 'Moore', 2022, NULL, '2025-07-16 06:29:32', '2025-12-30 06:29:32'),
(236, 12, 1, 52, '{}', 'Taylor, C. (2023). <i>Research data management handbook</i>. ALA Editions.', '(Taylor, 2023)', 'Taylor (2023)', 'en', 'Taylor', 2023, NULL, '2025-07-15 06:29:32', '2025-12-30 06:29:32'),
(237, 12, 1, 53, '{}', 'Anderson, P. (2024). <i>Scholarly communication trends</i>. Facet Publishing.', '(Anderson, 2024)', 'Anderson (2024)', 'en', 'Anderson', 2024, NULL, '2025-07-14 06:29:32', '2025-12-30 06:29:32'),
(238, 12, 1, 54, '{}', 'Thomas, E. (2018). <i>Digital library systems and innovations</i>. Springer.', '(Thomas, 2018)', 'Thomas (2018)', 'en', 'Thomas', 2018, NULL, '2025-07-13 06:29:32', '2025-12-30 06:29:32'),
(239, 12, 1, 55, '{}', 'Jackson, H. (2019). <i>Information literacy in higher education</i>. Wiley.', '(Jackson, 2019)', 'Jackson (2019)', 'en', 'Jackson', 2019, NULL, '2025-07-12 06:29:32', '2025-12-30 06:29:32'),
(240, 12, 1, 56, '{}', 'White, N. (2020). <i>Knowledge management best practices</i>. Elsevier.', '(White, 2020)', 'White (2020)', 'en', 'White', 2020, NULL, '2025-07-11 06:29:32', '2025-12-30 06:29:32'),
(241, 12, 1, 57, '{}', 'Harris, D. (2021). <i>User experience design for libraries</i>. Cambridge University Press.', '(Harris, 2021)', 'Harris (2021)', 'en', 'Harris', 2021, NULL, '2025-07-10 06:29:32', '2025-12-30 06:29:32'),
(242, 12, 1, 58, '{}', 'Martin, G. (2022). <i>Metadata standards and applications</i>. Oxford University Press.', '(Martin, 2022)', 'Martin (2022)', 'en', 'Martin', 2022, NULL, '2025-07-09 06:29:32', '2025-12-30 06:29:32'),
(243, 12, 1, 59, '{}', 'Smith, J. (2023). <i>Open access publishing strategies</i>. Routledge.', '(Smith, 2023)', 'Smith (2023)', 'en', 'Smith', 2023, NULL, '2025-07-08 06:29:32', '2025-12-30 06:29:32'),
(244, 12, 1, 60, '{}', 'Johnson, M. (2024). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Johnson, 2024)', 'Johnson (2024)', 'en', 'Johnson', 2024, NULL, '2025-07-07 06:29:32', '2025-12-30 06:29:32'),
(245, 12, 1, 61, '{}', 'Williams, K. (2018). <i>Data curation and preservation</i>. MIT Press.', '(Williams, 2018)', 'Williams (2018)', 'en', 'Williams', 2018, NULL, '2025-07-06 06:29:32', '2025-12-30 06:29:32'),
(246, 12, 1, 62, '{}', 'Brown, R. (2019). <i>Research data management handbook</i>. ALA Editions.', '(Brown, 2019)', 'Brown (2019)', 'en', 'Brown', 2019, NULL, '2025-07-05 06:29:32', '2025-12-30 06:29:32'),
(247, 12, 1, 63, '{}', 'Davis, S. (2020). <i>Scholarly communication trends</i>. Facet Publishing.', '(Davis, 2020)', 'Davis (2020)', 'en', 'Davis', 2020, NULL, '2025-07-04 06:29:32', '2025-12-30 06:29:32'),
(248, 12, 1, 64, '{}', 'Miller, A. (2021). <i>Digital library systems and innovations</i>. Springer.', '(Miller, 2021)', 'Miller (2021)', 'en', 'Miller', 2021, NULL, '2025-07-03 06:29:32', '2025-12-30 06:29:32'),
(249, 12, 1, 65, '{}', 'Wilson, T. (2022). <i>Information literacy in higher education</i>. Wiley.', '(Wilson, 2022)', 'Wilson (2022)', 'en', 'Wilson', 2022, NULL, '2025-07-02 06:29:32', '2025-12-30 06:29:32'),
(250, 12, 1, 66, '{}', 'Moore, L. (2023). <i>Knowledge management best practices</i>. Elsevier.', '(Moore, 2023)', 'Moore (2023)', 'en', 'Moore', 2023, NULL, '2025-07-01 06:29:32', '2025-12-30 06:29:32'),
(251, 12, 1, 67, '{}', 'Taylor, C. (2024). <i>User experience design for libraries</i>. Cambridge University Press.', '(Taylor, 2024)', 'Taylor (2024)', 'en', 'Taylor', 2024, NULL, '2025-06-30 06:29:32', '2025-12-30 06:29:32'),
(252, 12, 1, 68, '{}', 'Anderson, P. (2018). <i>Metadata standards and applications</i>. Oxford University Press.', '(Anderson, 2018)', 'Anderson (2018)', 'en', 'Anderson', 2018, NULL, '2025-06-29 06:29:32', '2025-12-30 06:29:32'),
(253, 12, 1, 69, '{}', 'Thomas, E. (2019). <i>Open access publishing strategies</i>. Routledge.', '(Thomas, 2019)', 'Thomas (2019)', 'en', 'Thomas', 2019, NULL, '2025-06-28 06:29:32', '2025-12-30 06:29:32'),
(254, 12, 1, 70, '{}', 'Jackson, H. (2020). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Jackson, 2020)', 'Jackson (2020)', 'en', 'Jackson', 2020, NULL, '2025-06-27 06:29:32', '2025-12-30 06:29:32'),
(255, 12, 1, 71, '{}', 'White, N. (2021). <i>Data curation and preservation</i>. MIT Press.', '(White, 2021)', 'White (2021)', 'en', 'White', 2021, NULL, '2025-06-26 06:29:32', '2025-12-30 06:29:32'),
(256, 12, 1, NULL, '{}', 'Harris, D. (2022). <i>Research data management handbook</i>. ALA Editions.', '(Harris, 2022)', 'Harris (2022)', 'en', 'Harris', 2022, NULL, '2025-06-25 06:29:32', '2026-01-30 06:55:59'),
(257, 12, 1, 73, '{}', 'Martin, G. (2023). <i>Scholarly communication trends</i>. Facet Publishing.', '(Martin, 2023)', 'Martin (2023)', 'en', 'Martin', 2023, NULL, '2025-06-24 06:29:32', '2025-12-30 06:29:32'),
(258, 12, 1, 44, '{}', 'Smith, J. (2024). <i>Digital library systems and innovations</i>. Springer.', '(Smith, 2024)', 'Smith (2024)', 'en', 'Smith', 2024, NULL, '2025-06-23 06:29:32', '2025-12-30 06:29:32'),
(259, 12, 1, 45, '{}', 'Johnson, M. (2018). <i>Information literacy in higher education</i>. Wiley.', '(Johnson, 2018)', 'Johnson (2018)', 'en', 'Johnson', 2018, NULL, '2025-06-22 06:29:32', '2025-12-30 06:29:32'),
(260, 12, 1, 46, '{}', 'Williams, K. (2019). <i>Knowledge management best practices</i>. Elsevier.', '(Williams, 2019)', 'Williams (2019)', 'en', 'Williams', 2019, NULL, '2025-06-21 06:29:32', '2025-12-30 06:29:32'),
(261, 12, 1, 47, '{}', 'Brown, R. (2020). <i>User experience design for libraries</i>. Cambridge University Press.', '(Brown, 2020)', 'Brown (2020)', 'en', 'Brown', 2020, NULL, '2025-06-20 06:29:32', '2025-12-30 06:29:32'),
(262, 12, 1, 48, '{}', 'Davis, S. (2021). <i>Metadata standards and applications</i>. Oxford University Press.', '(Davis, 2021)', 'Davis (2021)', 'en', 'Davis', 2021, NULL, '2025-06-19 06:29:32', '2025-12-30 06:29:32'),
(263, 12, 1, 49, '{}', 'Miller, A. (2022). <i>Open access publishing strategies</i>. Routledge.', '(Miller, 2022)', 'Miller (2022)', 'en', 'Miller', 2022, NULL, '2025-06-18 06:29:32', '2025-12-30 06:29:32'),
(264, 12, 1, 50, '{}', 'Wilson, T. (2023). <i>Bibliometric analysis methods</i>. SAGE Publications.', '(Wilson, 2023)', 'Wilson (2023)', 'en', 'Wilson', 2023, NULL, '2025-06-17 06:29:32', '2025-12-30 06:29:32'),
(265, 12, 1, 51, '{}', 'Moore, L. (2024). <i>Data curation and preservation</i>. MIT Press.', '(Moore, 2024)', 'Moore (2024)', 'en', 'Moore', 2024, NULL, '2025-06-16 06:29:32', '2025-12-30 06:29:32'),
(266, 12, 1, 52, '{}', 'Taylor, C. (2018). <i>Research data management handbook</i>. ALA Editions.', '(Taylor, 2018)', 'Taylor (2018)', 'en', 'Taylor', 2018, NULL, '2025-06-15 06:29:32', '2025-12-30 06:29:32'),
(267, 12, 1, 53, '{}', 'Anderson, P. (2019). <i>Scholarly communication trends</i>. Facet Publishing.', '(Anderson, 2019)', 'Anderson (2019)', 'en', 'Anderson', 2019, NULL, '2025-06-14 06:29:32', '2025-12-30 06:29:32'),
(295, 12, 2, 44, '{}', 'Garcia, L. (2019). Artificial intelligence in library services. <i>Journal of Academic Librarianship</i>, 40(1), 100-115. https://doi.org/10.1000/example0', '(Garcia, 2019)', 'Garcia (2019)', 'en', 'Garcia', 2019, NULL, '2025-06-13 06:29:32', '2025-12-30 06:29:32'),
(296, 12, 2, 45, '{}', 'Martinez, R. (2020). Machine learning for information retrieval. <i>Library Quarterly</i>, 41(2), 110-125. https://doi.org/10.1000/example1', '(Martinez, 2020)', 'Martinez (2020)', 'en', 'Martinez', 2020, NULL, '2025-06-12 06:29:32', '2025-12-30 06:29:32'),
(297, 12, 2, 46, '{}', 'Robinson, K. (2021). Digital transformation in academic libraries. <i>Journal of Documentation</i>, 42(3), 120-135. https://doi.org/10.1000/example2', '(Robinson, 2021)', 'Robinson (2021)', 'en', 'Robinson', 2021, NULL, '2025-06-11 06:29:32', '2025-12-30 06:29:32'),
(298, 12, 2, 47, '{}', 'Clark, J. (2022). Cloud computing for library systems. <i>Information Processing and Management</i>, 43(4), 130-145. https://doi.org/10.1000/example3', '(Clark, 2022)', 'Clark (2022)', 'en', 'Clark', 2022, NULL, '2025-06-10 06:29:32', '2025-12-30 06:29:32'),
(299, 12, 2, 48, '{}', 'Rodriguez, M. (2023). Big data analytics in libraries. <i>College and Research Libraries</i>, 44(1), 140-155. https://doi.org/10.1000/example4', '(Rodriguez, 2023)', 'Rodriguez (2023)', 'en', 'Rodriguez', 2023, NULL, '2025-06-09 06:29:32', '2025-12-30 06:29:32'),
(300, 12, 2, 49, '{}', 'Lewis, S. (2024). Social media marketing for libraries. <i>Journal of Academic Librarianship</i>, 45(2), 150-165. https://doi.org/10.1000/example5', '(Lewis, 2024)', 'Lewis (2024)', 'en', 'Lewis', 2024, NULL, '2025-06-08 06:29:32', '2025-12-30 06:29:32'),
(301, 12, 2, 50, '{}', 'Lee, A. (2019). Virtual reality in library instruction. <i>Library Quarterly</i>, 46(3), 160-175. https://doi.org/10.1000/example6', '(Lee, 2019)', 'Lee (2019)', 'en', 'Lee', 2019, NULL, '2025-06-07 06:29:32', '2025-12-30 06:29:32'),
(302, 12, 2, 51, '{}', 'Walker, T. (2020). Blockchain for digital preservation. <i>Journal of Documentation</i>, 47(4), 170-185. https://doi.org/10.1000/example7', '(Walker, 2020)', 'Walker (2020)', 'en', 'Walker', 2020, NULL, '2025-06-06 06:29:32', '2025-12-30 06:29:32'),
(303, 12, 2, 52, '{}', 'Hall, C. (2021). Internet of Things in smart libraries. <i>Information Processing and Management</i>, 48(1), 180-195. https://doi.org/10.1000/example8', '(Hall, 2021)', 'Hall (2021)', 'en', 'Hall', 2021, NULL, '2025-06-05 06:29:32', '2025-12-30 06:29:32'),
(304, 12, 2, 53, '{}', 'Allen, P. (2022). Cybersecurity for library systems. <i>College and Research Libraries</i>, 49(2), 190-205. https://doi.org/10.1000/example9', '(Allen, 2022)', 'Allen (2022)', 'en', 'Allen', 2022, NULL, '2025-06-04 06:29:32', '2025-12-30 06:29:32'),
(305, 12, 2, 54, '{}', 'Young, E. (2023). Artificial intelligence in library services. <i>Journal of Academic Librarianship</i>, 50(3), 200-215. https://doi.org/10.1000/example10', '(Young, 2023)', 'Young (2023)', 'en', 'Young', 2023, NULL, '2025-06-03 06:29:32', '2025-12-30 06:29:32'),
(306, 12, 2, 55, '{}', 'King, H. (2024). Machine learning for information retrieval. <i>Library Quarterly</i>, 51(4), 210-225. https://doi.org/10.1000/example11', '(King, 2024)', 'King (2024)', 'en', 'King', 2024, NULL, '2025-06-02 06:29:32', '2025-12-30 06:29:32'),
(307, 12, 2, 56, '{}', 'Wright, N. (2019). Digital transformation in academic libraries. <i>Journal of Documentation</i>, 52(1), 220-235. https://doi.org/10.1000/example12', '(Wright, 2019)', 'Wright (2019)', 'en', 'Wright', 2019, NULL, '2025-06-01 06:29:32', '2025-12-30 06:29:32'),
(308, 12, 2, 57, '{}', 'Scott, D. (2020). Cloud computing for library systems. <i>Information Processing and Management</i>, 53(2), 230-245. https://doi.org/10.1000/example13', '(Scott, 2020)', 'Scott (2020)', 'en', 'Scott', 2020, NULL, '2025-05-31 06:29:32', '2025-12-30 06:29:32'),
(309, 12, 2, 58, '{}', 'Green, G. (2021). Big data analytics in libraries. <i>College and Research Libraries</i>, 54(3), 240-255. https://doi.org/10.1000/example14', '(Green, 2021)', 'Green (2021)', 'en', 'Green', 2021, NULL, '2025-05-30 06:29:32', '2025-12-30 06:29:32'),
(310, 12, 2, 59, '{}', 'Garcia, L. (2022). Social media marketing for libraries. <i>Journal of Academic Librarianship</i>, 55(4), 250-265. https://doi.org/10.1000/example15', '(Garcia, 2022)', 'Garcia (2022)', 'en', 'Garcia', 2022, NULL, '2025-05-29 06:29:32', '2025-12-30 06:29:32'),
(311, 12, 2, 60, '{}', 'Martinez, R. (2023). Virtual reality in library instruction. <i>Library Quarterly</i>, 56(1), 260-275. https://doi.org/10.1000/example16', '(Martinez, 2023)', 'Martinez (2023)', 'en', 'Martinez', 2023, NULL, '2025-05-28 06:29:32', '2025-12-30 06:29:32'),
(312, 12, 2, 61, '{}', 'Robinson, K. (2024). Blockchain for digital preservation. <i>Journal of Documentation</i>, 57(2), 270-285. https://doi.org/10.1000/example17', '(Robinson, 2024)', 'Robinson (2024)', 'en', 'Robinson', 2024, NULL, '2025-05-27 06:29:32', '2025-12-30 06:29:32'),
(313, 12, 2, 62, '{}', 'Clark, J. (2019). Internet of Things in smart libraries. <i>Information Processing and Management</i>, 58(3), 280-295. https://doi.org/10.1000/example18', '(Clark, 2019)', 'Clark (2019)', 'en', 'Clark', 2019, NULL, '2025-05-26 06:29:32', '2025-12-30 06:29:32'),
(314, 12, 2, 63, '{}', 'Rodriguez, M. (2020). Cybersecurity for library systems. <i>College and Research Libraries</i>, 59(4), 290-305. https://doi.org/10.1000/example19', '(Rodriguez, 2020)', 'Rodriguez (2020)', 'en', 'Rodriguez', 2020, NULL, '2025-05-25 06:29:32', '2025-12-30 06:29:32'),
(315, 12, 2, 64, '{}', 'Lewis, S. (2021). Artificial intelligence in library services. <i>Journal of Academic Librarianship</i>, 40(1), 300-315. https://doi.org/10.1000/example20', '(Lewis, 2021)', 'Lewis (2021)', 'en', 'Lewis', 2021, NULL, '2025-05-24 06:29:32', '2025-12-30 06:29:32'),
(316, 12, 2, 65, '{}', 'Lee, A. (2022). Machine learning for information retrieval. <i>Library Quarterly</i>, 41(2), 310-325. https://doi.org/10.1000/example21', '(Lee, 2022)', 'Lee (2022)', 'en', 'Lee', 2022, NULL, '2025-05-23 06:29:32', '2025-12-30 06:29:32');
INSERT INTO `bibliographies` (`id`, `user_id`, `resource_type_id`, `project_id`, `data`, `bibliography_text`, `citation_parenthetical`, `citation_narrative`, `language`, `author_sort_key`, `year`, `year_suffix`, `created_at`, `updated_at`) VALUES
(317, 12, 2, 66, '{}', 'Walker, T. (2023). Digital transformation in academic libraries. <i>Journal of Documentation</i>, 42(3), 320-335. https://doi.org/10.1000/example22', '(Walker, 2023)', 'Walker (2023)', 'en', 'Walker', 2023, NULL, '2025-05-22 06:29:32', '2025-12-30 06:29:32'),
(318, 12, 2, 67, '{}', 'Hall, C. (2024). Cloud computing for library systems. <i>Information Processing and Management</i>, 43(4), 330-345. https://doi.org/10.1000/example23', '(Hall, 2024)', 'Hall (2024)', 'en', 'Hall', 2024, NULL, '2025-05-21 06:29:32', '2025-12-30 06:29:32'),
(319, 12, 2, 68, '{}', 'Allen, P. (2019). Big data analytics in libraries. <i>College and Research Libraries</i>, 44(1), 340-355. https://doi.org/10.1000/example24', '(Allen, 2019)', 'Allen (2019)', 'en', 'Allen', 2019, NULL, '2025-05-20 06:29:32', '2025-12-30 06:29:32'),
(320, 12, 2, 69, '{}', 'Young, E. (2020). Social media marketing for libraries. <i>Journal of Academic Librarianship</i>, 45(2), 350-365. https://doi.org/10.1000/example25', '(Young, 2020)', 'Young (2020)', 'en', 'Young', 2020, NULL, '2025-05-19 06:29:32', '2025-12-30 06:29:32'),
(321, 12, 2, 70, '{}', 'King, H. (2021). Virtual reality in library instruction. <i>Library Quarterly</i>, 46(3), 360-375. https://doi.org/10.1000/example26', '(King, 2021)', 'King (2021)', 'en', 'King', 2021, NULL, '2025-05-18 06:29:32', '2025-12-30 06:29:32'),
(322, 12, 2, 71, '{}', 'Wright, N. (2022). Blockchain for digital preservation. <i>Journal of Documentation</i>, 47(4), 370-385. https://doi.org/10.1000/example27', '(Wright, 2022)', 'Wright (2022)', 'en', 'Wright', 2022, NULL, '2025-05-17 06:29:32', '2025-12-30 06:29:32'),
(323, 12, 2, NULL, '{}', 'Scott, D. (2023). Internet of Things in smart libraries. <i>Information Processing and Management</i>, 48(1), 380-395. https://doi.org/10.1000/example28', '(Scott, 2023)', 'Scott (2023)', 'en', 'Scott', 2023, NULL, '2025-05-16 06:29:32', '2026-01-30 06:55:59'),
(324, 12, 2, 73, '{}', 'Green, G. (2024). Cybersecurity for library systems. <i>College and Research Libraries</i>, 49(2), 390-405. https://doi.org/10.1000/example29', '(Green, 2024)', 'Green (2024)', 'en', 'Green', 2024, NULL, '2025-05-15 06:29:32', '2025-12-30 06:29:32'),
(325, 12, 2, 44, '{}', 'Garcia, L. (2019). Artificial intelligence in library services. <i>Journal of Academic Librarianship</i>, 50(3), 400-415. https://doi.org/10.1000/example30', '(Garcia, 2019)', 'Garcia (2019)', 'en', 'Garcia', 2019, NULL, '2025-05-14 06:29:32', '2025-12-30 06:29:32'),
(326, 12, 2, 45, '{}', 'Martinez, R. (2020). Machine learning for information retrieval. <i>Library Quarterly</i>, 51(4), 410-425. https://doi.org/10.1000/example31', '(Martinez, 2020)', 'Martinez (2020)', 'en', 'Martinez', 2020, NULL, '2025-05-13 06:29:32', '2025-12-30 06:29:32'),
(327, 12, 2, 46, '{}', 'Robinson, K. (2021). Digital transformation in academic libraries. <i>Journal of Documentation</i>, 52(1), 420-435. https://doi.org/10.1000/example32', '(Robinson, 2021)', 'Robinson (2021)', 'en', 'Robinson', 2021, NULL, '2025-05-12 06:29:32', '2025-12-30 06:29:32'),
(328, 12, 2, 47, '{}', 'Clark, J. (2022). Cloud computing for library systems. <i>Information Processing and Management</i>, 53(2), 430-445. https://doi.org/10.1000/example33', '(Clark, 2022)', 'Clark (2022)', 'en', 'Clark', 2022, NULL, '2025-05-11 06:29:32', '2025-12-30 06:29:32'),
(329, 12, 2, 48, '{}', 'Rodriguez, M. (2023). Big data analytics in libraries. <i>College and Research Libraries</i>, 54(3), 440-455. https://doi.org/10.1000/example34', '(Rodriguez, 2023)', 'Rodriguez (2023)', 'en', 'Rodriguez', 2023, NULL, '2025-05-10 06:29:32', '2025-12-30 06:29:32'),
(330, 12, 2, 49, '{}', 'Lewis, S. (2024). Social media marketing for libraries. <i>Journal of Academic Librarianship</i>, 55(4), 450-465. https://doi.org/10.1000/example35', '(Lewis, 2024)', 'Lewis (2024)', 'en', 'Lewis', 2024, NULL, '2025-05-09 06:29:32', '2025-12-30 06:29:32'),
(331, 12, 2, 50, '{}', 'Lee, A. (2019). Virtual reality in library instruction. <i>Library Quarterly</i>, 56(1), 460-475. https://doi.org/10.1000/example36', '(Lee, 2019)', 'Lee (2019)', 'en', 'Lee', 2019, NULL, '2025-05-08 06:29:32', '2025-12-30 06:29:32'),
(332, 12, 2, 51, '{}', 'Walker, T. (2020). Blockchain for digital preservation. <i>Journal of Documentation</i>, 57(2), 470-485. https://doi.org/10.1000/example37', '(Walker, 2020)', 'Walker (2020)', 'en', 'Walker', 2020, NULL, '2025-05-07 06:29:32', '2025-12-30 06:29:32'),
(333, 12, 2, 52, '{}', 'Hall, C. (2021). Internet of Things in smart libraries. <i>Information Processing and Management</i>, 58(3), 480-495. https://doi.org/10.1000/example38', '(Hall, 2021)', 'Hall (2021)', 'en', 'Hall', 2021, NULL, '2025-05-06 06:29:32', '2025-12-30 06:29:32'),
(334, 12, 2, 53, '{}', 'Allen, P. (2022). Cybersecurity for library systems. <i>College and Research Libraries</i>, 59(4), 490-505. https://doi.org/10.1000/example39', '(Allen, 2022)', 'Allen (2022)', 'en', 'Allen', 2022, NULL, '2025-05-05 06:29:32', '2025-12-30 06:29:32'),
(335, 12, 2, 54, '{}', 'Young, E. (2023). Artificial intelligence in library services. <i>Journal of Academic Librarianship</i>, 40(1), 500-515. https://doi.org/10.1000/example40', '(Young, 2023)', 'Young (2023)', 'en', 'Young', 2023, NULL, '2025-05-04 06:29:32', '2025-12-30 06:29:32'),
(336, 12, 2, 55, '{}', 'King, H. (2024). Machine learning for information retrieval. <i>Library Quarterly</i>, 41(2), 510-525. https://doi.org/10.1000/example41', '(King, 2024)', 'King (2024)', 'en', 'King', 2024, NULL, '2025-05-03 06:29:32', '2025-12-30 06:29:32'),
(337, 12, 2, 56, '{}', 'Wright, N. (2019). Digital transformation in academic libraries. <i>Journal of Documentation</i>, 42(3), 520-535. https://doi.org/10.1000/example42', '(Wright, 2019)', 'Wright (2019)', 'en', 'Wright', 2019, NULL, '2025-05-02 06:29:32', '2025-12-30 06:29:32'),
(338, 12, 2, 57, '{}', 'Scott, D. (2020). Cloud computing for library systems. <i>Information Processing and Management</i>, 43(4), 530-545. https://doi.org/10.1000/example43', '(Scott, 2020)', 'Scott (2020)', 'en', 'Scott', 2020, NULL, '2025-05-01 06:29:32', '2025-12-30 06:29:32'),
(339, 12, 2, 58, '{}', 'Green, G. (2021). Big data analytics in libraries. <i>College and Research Libraries</i>, 44(1), 540-555. https://doi.org/10.1000/example44', '(Green, 2021)', 'Green (2021)', 'en', 'Green', 2021, NULL, '2025-04-30 06:29:32', '2025-12-30 06:29:32'),
(340, 12, 2, 59, '{}', 'Garcia, L. (2022). Social media marketing for libraries. <i>Journal of Academic Librarianship</i>, 45(2), 550-565. https://doi.org/10.1000/example45', '(Garcia, 2022)', 'Garcia (2022)', 'en', 'Garcia', 2022, NULL, '2025-04-29 06:29:32', '2025-12-30 06:29:32'),
(341, 12, 2, 60, '{}', 'Martinez, R. (2023). Virtual reality in library instruction. <i>Library Quarterly</i>, 46(3), 560-575. https://doi.org/10.1000/example46', '(Martinez, 2023)', 'Martinez (2023)', 'en', 'Martinez', 2023, NULL, '2025-04-28 06:29:32', '2025-12-30 06:29:32'),
(342, 12, 2, 61, '{}', 'Robinson, K. (2024). Blockchain for digital preservation. <i>Journal of Documentation</i>, 47(4), 570-585. https://doi.org/10.1000/example47', '(Robinson, 2024)', 'Robinson (2024)', 'en', 'Robinson', 2024, NULL, '2025-04-27 06:29:32', '2025-12-30 06:29:32'),
(343, 12, 2, 62, '{}', 'Clark, J. (2019). Internet of Things in smart libraries. <i>Information Processing and Management</i>, 48(1), 580-595. https://doi.org/10.1000/example48', '(Clark, 2019)', 'Clark (2019)', 'en', 'Clark', 2019, NULL, '2025-04-26 06:29:32', '2025-12-30 06:29:32'),
(344, 12, 2, 63, '{}', 'Rodriguez, M. (2020). Cybersecurity for library systems. <i>College and Research Libraries</i>, 49(2), 590-605. https://doi.org/10.1000/example49', '(Rodriguez, 2020)', 'Rodriguez (2020)', 'en', 'Rodriguez', 2020, NULL, '2025-04-25 06:29:32', '2025-12-30 06:29:32'),
(345, 12, 2, 64, '{}', 'Lewis, S. (2021). Artificial intelligence in library services. <i>Journal of Academic Librarianship</i>, 50(3), 600-615. https://doi.org/10.1000/example50', '(Lewis, 2021)', 'Lewis (2021)', 'en', 'Lewis', 2021, NULL, '2025-04-24 06:29:32', '2025-12-30 06:29:32'),
(346, 12, 2, 65, '{}', 'Lee, A. (2022). Machine learning for information retrieval. <i>Library Quarterly</i>, 51(4), 610-625. https://doi.org/10.1000/example51', '(Lee, 2022)', 'Lee (2022)', 'en', 'Lee', 2022, NULL, '2025-04-23 06:29:32', '2025-12-30 06:29:32'),
(347, 12, 2, 66, '{}', 'Walker, T. (2023). Digital transformation in academic libraries. <i>Journal of Documentation</i>, 52(1), 620-635. https://doi.org/10.1000/example52', '(Walker, 2023)', 'Walker (2023)', 'en', 'Walker', 2023, NULL, '2025-04-22 06:29:32', '2025-12-30 06:29:32'),
(348, 12, 2, 67, '{}', 'Hall, C. (2024). Cloud computing for library systems. <i>Information Processing and Management</i>, 53(2), 630-645. https://doi.org/10.1000/example53', '(Hall, 2024)', 'Hall (2024)', 'en', 'Hall', 2024, NULL, '2025-04-21 06:29:32', '2025-12-30 06:29:32'),
(349, 12, 2, 68, '{}', 'Allen, P. (2019). Big data analytics in libraries. <i>College and Research Libraries</i>, 54(3), 640-655. https://doi.org/10.1000/example54', '(Allen, 2019)', 'Allen (2019)', 'en', 'Allen', 2019, NULL, '2025-04-20 06:29:32', '2025-12-30 06:29:32'),
(350, 12, 2, 69, '{}', 'Young, E. (2020). Social media marketing for libraries. <i>Journal of Academic Librarianship</i>, 55(4), 650-665. https://doi.org/10.1000/example55', '(Young, 2020)', 'Young (2020)', 'en', 'Young', 2020, NULL, '2025-04-19 06:29:32', '2025-12-30 06:29:32'),
(351, 12, 2, 70, '{}', 'King, H. (2021). Virtual reality in library instruction. <i>Library Quarterly</i>, 56(1), 660-675. https://doi.org/10.1000/example56', '(King, 2021)', 'King (2021)', 'en', 'King', 2021, NULL, '2025-04-18 06:29:32', '2025-12-30 06:29:32'),
(352, 12, 2, 71, '{}', 'Wright, N. (2022). Blockchain for digital preservation. <i>Journal of Documentation</i>, 57(2), 670-685. https://doi.org/10.1000/example57', '(Wright, 2022)', 'Wright (2022)', 'en', 'Wright', 2022, NULL, '2025-04-17 06:29:32', '2025-12-30 06:29:32'),
(353, 12, 2, NULL, '{}', 'Scott, D. (2023). Internet of Things in smart libraries. <i>Information Processing and Management</i>, 58(3), 680-695. https://doi.org/10.1000/example58', '(Scott, 2023)', 'Scott (2023)', 'en', 'Scott', 2023, NULL, '2025-04-16 06:29:32', '2026-01-30 06:55:59'),
(354, 12, 2, 73, '{}', 'Green, G. (2024). Cybersecurity for library systems. <i>College and Research Libraries</i>, 59(4), 690-705. https://doi.org/10.1000/example59', '(Green, 2024)', 'Green (2024)', 'en', 'Green', 2024, NULL, '2025-04-15 06:29:32', '2025-12-30 06:29:32'),
(355, 12, 2, 44, '{}', 'Garcia, L. (2019). Artificial intelligence in library services. <i>Journal of Academic Librarianship</i>, 40(1), 700-715. https://doi.org/10.1000/example60', '(Garcia, 2019)', 'Garcia (2019)', 'en', 'Garcia', 2019, NULL, '2025-04-14 06:29:32', '2025-12-30 06:29:32'),
(356, 12, 2, 45, '{}', 'Martinez, R. (2020). Machine learning for information retrieval. <i>Library Quarterly</i>, 41(2), 710-725. https://doi.org/10.1000/example61', '(Martinez, 2020)', 'Martinez (2020)', 'en', 'Martinez', 2020, NULL, '2025-04-13 06:29:32', '2025-12-30 06:29:32'),
(357, 12, 2, 46, '{}', 'Robinson, K. (2021). Digital transformation in academic libraries. <i>Journal of Documentation</i>, 42(3), 720-735. https://doi.org/10.1000/example62', '(Robinson, 2021)', 'Robinson (2021)', 'en', 'Robinson', 2021, NULL, '2025-04-12 06:29:32', '2025-12-30 06:29:32'),
(358, 12, 2, 47, '{}', 'Clark, J. (2022). Cloud computing for library systems. <i>Information Processing and Management</i>, 43(4), 730-745. https://doi.org/10.1000/example63', '(Clark, 2022)', 'Clark (2022)', 'en', 'Clark', 2022, NULL, '2025-04-11 06:29:32', '2025-12-30 06:29:32'),
(359, 12, 2, 48, '{}', 'Rodriguez, M. (2023). Big data analytics in libraries. <i>College and Research Libraries</i>, 44(1), 740-755. https://doi.org/10.1000/example64', '(Rodriguez, 2023)', 'Rodriguez (2023)', 'en', 'Rodriguez', 2023, NULL, '2025-04-10 06:29:32', '2025-12-30 06:29:32'),
(360, 12, 2, 49, '{}', 'Lewis, S. (2024). Social media marketing for libraries. <i>Journal of Academic Librarianship</i>, 45(2), 750-765. https://doi.org/10.1000/example65', '(Lewis, 2024)', 'Lewis (2024)', 'en', 'Lewis', 2024, NULL, '2025-04-09 06:29:32', '2025-12-30 06:29:32'),
(361, 12, 2, 50, '{}', 'Lee, A. (2019). Virtual reality in library instruction. <i>Library Quarterly</i>, 46(3), 760-775. https://doi.org/10.1000/example66', '(Lee, 2019)', 'Lee (2019)', 'en', 'Lee', 2019, NULL, '2025-04-08 06:29:32', '2025-12-30 06:29:32'),
(362, 12, 2, 51, '{}', 'Walker, T. (2020). Blockchain for digital preservation. <i>Journal of Documentation</i>, 47(4), 770-785. https://doi.org/10.1000/example67', '(Walker, 2020)', 'Walker (2020)', 'en', 'Walker', 2020, NULL, '2025-04-07 06:29:32', '2025-12-30 06:29:32'),
(363, 12, 2, 52, '{}', 'Hall, C. (2021). Internet of Things in smart libraries. <i>Information Processing and Management</i>, 48(1), 780-795. https://doi.org/10.1000/example68', '(Hall, 2021)', 'Hall (2021)', 'en', 'Hall', 2021, NULL, '2025-04-06 06:29:32', '2025-12-30 06:29:32'),
(364, 12, 2, 53, '{}', 'Allen, P. (2022). Cybersecurity for library systems. <i>College and Research Libraries</i>, 49(2), 790-805. https://doi.org/10.1000/example69', '(Allen, 2022)', 'Allen (2022)', 'en', 'Allen', 2022, NULL, '2025-04-05 06:29:32', '2025-12-30 06:29:32'),
(365, 12, 2, 54, '{}', 'Young, E. (2023). Artificial intelligence in library services. <i>Journal of Academic Librarianship</i>, 50(3), 800-815. https://doi.org/10.1000/example70', '(Young, 2023)', 'Young (2023)', 'en', 'Young', 2023, NULL, '2025-04-04 06:29:32', '2025-12-30 06:29:32'),
(366, 12, 2, 55, '{}', 'King, H. (2024). Machine learning for information retrieval. <i>Library Quarterly</i>, 51(4), 810-825. https://doi.org/10.1000/example71', '(King, 2024)', 'King (2024)', 'en', 'King', 2024, NULL, '2025-04-03 06:29:32', '2025-12-30 06:29:32'),
(367, 12, 2, 56, '{}', 'Wright, N. (2019). Digital transformation in academic libraries. <i>Journal of Documentation</i>, 52(1), 820-835. https://doi.org/10.1000/example72', '(Wright, 2019)', 'Wright (2019)', 'en', 'Wright', 2019, NULL, '2025-04-02 06:29:32', '2025-12-30 06:29:32'),
(386, 12, 2, 45, '{}', 'Martinez, R. (2020). Machine learning for information retrieval. <i>Library Quarterly</i>, 51(4), 1010-1025. https://doi.org/10.1000/example91', '(Martinez, 2020)', 'Martinez (2020)', 'en', 'Martinez', 2020, NULL, '2025-03-14 06:29:32', '2025-12-30 06:29:32'),
(387, 12, 2, 46, '{}', 'Robinson, K. (2021). Digital transformation in academic libraries. <i>Journal of Documentation</i>, 52(1), 1020-1035. https://doi.org/10.1000/example92', '(Robinson, 2021)', 'Robinson (2021)', 'en', 'Robinson', 2021, NULL, '2025-03-13 06:29:32', '2025-12-30 06:29:32'),
(388, 12, 2, 47, '{}', 'Clark, J. (2022). Cloud computing for library systems. <i>Information Processing and Management</i>, 53(2), 1030-1045. https://doi.org/10.1000/example93', '(Clark, 2022)', 'Clark (2022)', 'en', 'Clark', 2022, NULL, '2025-03-12 06:29:32', '2025-12-30 06:29:32'),
(422, 12, 1, 73, '{\"title\":\"การเขียนโปรแกรมเบื้องต้น\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"สมชาย\",\"middleName\":\"\",\"lastName\":\"ใจดี\",\"type\":\"normal\",\"display\":\"สมชาย ใจดี\"}],\"edition\":\"3\",\"publisher\":\"สำนักพิมพ์เชียงใหม่\"}', 'สมชาย ใจดี. (2567). <i>การเขียนโปรแกรมเบื้องต้น</i> (พิมพ์ครั้งที่ 3). สำนักพิมพ์เชียงใหม่.', '(สมชาย ใจดี, 2567)', 'สมชาย ใจดี (2567)', 'th', 'ใจดี', 2567, NULL, '2025-12-30 06:43:55', '2025-12-30 06:44:03'),
(431, 12, 6, NULL, '{\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"Terry L.\",\"middleName\":\"\",\"lastName\":\"Root\",\"type\":\"normal\",\"display\":\"Terry L. Root\"},{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"Jeff\",\"middleName\":\"\",\"lastName\":\"Price\",\"type\":\"normal\",\"display\":\"Jeff Price\"},{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"Kimberly R.\",\"middleName\":\"\",\"lastName\":\"Hall\",\"type\":\"normal\",\"display\":\"Kimberly R. Hall\"},{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"Stephen H.\",\"middleName\":\"\",\"lastName\":\"Schneider\",\"type\":\"normal\",\"display\":\"Stephen H. Schneider\"},{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"Cynthia\",\"middleName\":\"\",\"lastName\":\"Rosenzweig\",\"type\":\"normal\",\"display\":\"Cynthia Rosenzweig\"},{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J. Alan\",\"middleName\":\"\",\"lastName\":\"Pounds\",\"type\":\"normal\",\"display\":\"J. Alan Pounds\"}],\"journal_name\":\"Nature\",\"volume\":\"2\",\"issue\":\"1\",\"pages\":\"350\"}', 'Root, T., Price, J., Hall, K., Schneider, S., Rosenzweig, C., &amp; Pounds, J. (2003). Test. <i>Nature</i>, <i>2</i>(1), 350.', '(Root et al., 2003)', 'Root et al. (2003)', 'en', 'Root', 2003, NULL, '2026-01-30 06:58:27', '2026-01-30 06:58:27'),
(432, NULL, 1, NULL, '{\"title\":\"Atlantic Ocean\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"Thanakon\",\"middleName\":\"\",\"lastName\":\"dungkumwattanasiri\",\"type\":\"normal\",\"display\":\"Thanakon dungkumwattanasiri\"}],\"edition\":\"2\",\"publisher\":\"Chiang Mai Press\"}', 'Dungkumwattanasiri, T. (2025). <i>Atlantic Ocean	</i> (2nd ed.). Chiang Mai Press.', '(dungkumwattanasiri, 2025)', 'dungkumwattanasiri (2025)', 'en', 'dungkumwattanasiri', 2025, NULL, '2026-02-05 04:13:11', '2026-02-05 04:16:46'),
(433, NULL, 1, NULL, '{\"title\":\"The Great Gatsby\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"Thanakon\",\"middleName\":\"\",\"lastName\":\"dungkumwattanasiri\",\"type\":\"normal\",\"display\":\"Thanakon dungkumwattanasiri\"}],\"edition\":\"2\",\"publisher\":\"Chiang Mai Press\"}', 'Dungkumwattanasiri, T. (2025b). <i>The Great Gatsby</i> (2nd ed.). Chiang Mai Press.', '(dungkumwattanasiri, 2025)', 'dungkumwattanasiri (2025b)', 'en', 'dungkumwattanasiri', 2025, 'b', '2026-02-05 04:13:40', '2026-02-05 04:16:46'),
(434, NULL, 1, NULL, '{\"title\":\"An Inconvenient Truth\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"Thanakon\",\"middleName\":\"\",\"lastName\":\"dungkumwattanasiri\",\"type\":\"normal\",\"display\":\"Thanakon dungkumwattanasiri\"}],\"edition\":\"2\",\"publisher\":\"Chiang Mai Press\"}', 'Dungkumwattanasiri, T. (2025c). <i>An Inconvenient Truth</i> (2nd ed.). Chiang Mai Press.', '(dungkumwattanasiri, 2025)', 'dungkumwattanasiri (2025c)', 'en', 'dungkumwattanasiri', 2025, 'c', '2026-02-05 04:14:01', '2026-02-05 04:16:46'),
(435, NULL, 1, NULL, '{\"title\":\"A Tale of Two Cities\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"Thanakon\",\"middleName\":\"\",\"lastName\":\"dungkumwattanasiri\",\"type\":\"normal\",\"display\":\"Thanakon dungkumwattanasiri\"}],\"edition\":\"2\",\"publisher\":\"Chiang Mai Press\"}', 'Dungkumwattanasiri, T. (2025d). <i>A Tale of Two Cities</i> (2nd ed.). Chiang Mai Press.', '(dungkumwattanasiri, 2025)', 'dungkumwattanasiri (2025d)', 'en', 'dungkumwattanasiri', 2025, 'd', '2026-02-05 04:14:27', '2026-02-05 04:16:46'),
(436, NULL, 1, NULL, '{\"title\":\"ำไดไดำด\",\"authors\":[],\"edition\":\"2\",\"publisher\":\"เชียงหใม่\"}', '<i>ำไดไดำด</i>. (2467).  (พิมพ์ครั้งที่ 2). เชียงหใม่.', '(ำไดไดำด, 2467)', 'ำไดไดำด (2467)', 'th', 'ำไดไดำด', 2467, NULL, '2026-02-05 04:20:38', '2026-02-05 04:20:38'),
(437, 12, 1, 80, '{\"title\":\"Harry Potter and the Philosopher&#039;s Stone\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J. K.\",\"middleName\":\"\",\"lastName\":\"Rowling\",\"type\":\"normal\",\"display\":\"J. K. Rowling\"}],\"edition\":\"\",\"publisher\":\"Mun-hag Sucheob Publishing Company\"}', 'Rowling, J. (1997). <i>Harry Potter and the Philosopher\'s Stone</i>. Mun-hag Sucheob Publishing Company.', '(Rowling, 1997)', 'Rowling (1997)', 'en', 'Rowling', 1997, NULL, '2026-03-04 08:44:59', '2026-03-04 08:45:52'),
(438, 12, 1, 80, '{\"title\":\"Harry Potter and the Deathly Hallows\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J.\",\"middleName\":\"K.\",\"lastName\":\"Rowling\",\"type\":\"normal\",\"display\":\"J. Rowling\"}],\"edition\":\"\",\"publisher\":\"Arthur A. Levine Books an imprint of Scholastic Inc.\"}', 'Rowling, J. K. (2007). <i>Harry Potter and the Deathly Hallows</i>. Arthur A. Levine Books an imprint of Scholastic Inc.', '(Rowling, 2007)', 'Rowling (2007)', 'en', 'Rowling', 2007, NULL, '2026-03-04 09:55:08', '2026-03-04 09:55:16'),
(439, 12, 1, 80, '{\"title\":\"Harry Potter and the Prisoner of Azkaban\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J.\",\"middleName\":\"K.\",\"lastName\":\"Rowling\",\"type\":\"normal\",\"display\":\"J. Rowling\"}],\"edition\":\"\",\"publisher\":\"RocCo\"}', 'Rowling, J. K. (1999). <i>Harry Potter and the Prisoner of Azkaban</i>. RocCo.', '(Rowling, 1999)', 'Rowling (1999)', 'en', 'Rowling', 1999, NULL, '2026-03-04 09:55:38', '2026-03-04 09:55:45'),
(440, 12, 1, 80, '{\"title\":\"Harry Potter and the Goblet of Fire\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J.\",\"middleName\":\"K.\",\"lastName\":\"Rowling\",\"type\":\"normal\",\"display\":\"J. Rowling\"}],\"edition\":\"\",\"publisher\":\"Albatros Praha\"}', 'Rowling, J. K. (2000). <i>Harry Potter and the Goblet of Fire</i>. Albatros Praha.', '(Rowling, 2000)', 'Rowling (2000)', 'en', 'Rowling', 2000, NULL, '2026-03-04 13:37:40', '2026-03-04 14:28:14'),
(441, 12, 1, 80, '{\"title\":\"Harry Potter and the Order of the Phoenix\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J.\",\"middleName\":\"K.\",\"lastName\":\"Rowling\",\"type\":\"normal\",\"display\":\"J. Rowling\"}],\"edition\":\"\",\"publisher\":\"Editions Gallimard\"}', 'Rowling, J. K. (2003). <i>Harry Potter and the Order of the Phoenix</i>. Editions Gallimard.', '(Rowling, 2003)', 'Rowling (2003)', 'en', 'Rowling', 2003, NULL, '2026-03-04 13:38:03', '2026-03-04 14:28:14'),
(442, 12, 1, 80, '{\"title\":\"Harry Potter and the Half-Blood Prince\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J.\",\"middleName\":\"K.\",\"lastName\":\"Rowling\",\"type\":\"normal\",\"display\":\"J. Rowling\"}],\"edition\":\"\",\"publisher\":\"Gallimard Jeunesse\"}', 'Rowling, J. K. (2005). <i>Harry Potter and the Half-Blood Prince</i>. Gallimard Jeunesse.', '(Rowling, 2005)', 'Rowling (2005)', 'en', 'Rowling', 2005, NULL, '2026-03-04 13:38:20', '2026-03-04 14:28:14'),
(443, 12, 1, 80, '{\"title\":\"Harry Potter and the Chamber of Secrets\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J.\",\"middleName\":\"K.\",\"lastName\":\"Rowling\",\"type\":\"normal\",\"display\":\"J. Rowling\"}],\"edition\":\"\",\"publisher\":\"Kitābsarā-yi Tandīs\"}', 'Rowling, J. K. (1998). <i>Harry Potter and the Chamber of Secrets</i>. Kitābsarā-yi Tandīs.', '(Rowling, 1998)', 'Rowling (1998)', 'en', 'Rowling', 1998, NULL, '2026-03-04 13:38:45', '2026-03-04 14:28:14'),
(444, 12, 1, 80, '{\"title\":\"Harry Potter (series) 1-7\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J.\",\"middleName\":\"K.\",\"lastName\":\"Rowling\",\"type\":\"normal\",\"display\":\"J. Rowling\"}],\"edition\":\"\",\"publisher\":\"Educa Books\"}', 'Rowling, J. K. (1999b). <i>Harry Potter (series) 1-7</i>. Educa Books.', '(Rowling, 1999)', 'Rowling (1999b)', 'en', 'Rowling', 1999, 'b', '2026-03-04 13:39:09', '2026-03-04 14:28:14'),
(445, 12, 1, 80, '{\"title\":\"พุทธศาสนา กับ วิทยาศาสตร์\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"ประจวบ\",\"middleName\":\"\",\"lastName\":\"โภคทรัพย์\",\"type\":\"normal\",\"display\":\"ประจวบ โภคทรัพย์\"}],\"edition\":\"\",\"publisher\":\"โรงพิมพ์ชวนพิมพ์\"}', 'ประจวบ โภคทรัพย์. (2508). <i>พุทธศาสนา กับ วิทยาศาสตร์</i>. โรงพิมพ์ชวนพิมพ์.', '(ประจวบ โภคทรัพย์, 2508)', 'ประจวบ โภคทรัพย์ (2508)', 'th', 'โภคทรัพย์', 2508, NULL, '2026-03-05 03:03:38', '2026-03-05 03:03:53'),
(446, 12, 1, 80, '{\"title\":\"ความมหัศจรรย์ของธรรมชาติ\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"น.อ.\",\"middleName\":\"สนิท\",\"lastName\":\"เฟื่องระบิล\",\"type\":\"normal\",\"display\":\"น.อ. เฟื่องระบิล\"}],\"edition\":\"\",\"publisher\":\"โรงพิมพ์กรมสารบรรณทหารเรือ\"}', 'น.อ. เฟื่องระบิล. (2514). <i>ความมหัศจรรย์ของธรรมชาติ</i>. โรงพิมพ์กรมสารบรรณทหารเรือ.', '(น.อ. เฟื่องระบิล, 2514)', 'น.อ. เฟื่องระบิล (2514)', 'th', 'เฟื่องระบิล', 2514, NULL, '2026-03-05 03:04:13', '2026-03-05 03:04:17'),
(447, 12, 1, 80, '{\"title\":\"การศึกษาเทศบาล\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"ศิลป์ชัย\",\"middleName\":\"\",\"lastName\":\"ศิรินาม\",\"type\":\"normal\",\"display\":\"ศิลป์ชัย ศิรินาม\"}],\"edition\":\"\",\"publisher\":\"บริษัทสหสยามพัฒนาจำกัด\"}', 'ศิลป์ชัย ศิรินาม. (2514). <i>การศึกษาเทศบาล</i>. บริษัทสหสยามพัฒนาจำกัด.', '(ศิลป์ชัย ศิรินาม, 2514)', 'ศิลป์ชัย ศิรินาม (2514)', 'th', 'ศิรินาม', 2514, NULL, '2026-03-05 03:04:31', '2026-03-05 03:04:37'),
(448, 12, 1, 80, '{\"title\":\"คู่มือครูเล่มเล็ก\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"ม.ล.\",\"middleName\":\"ปิ่น\",\"lastName\":\"มาลากุล\",\"type\":\"normal\",\"display\":\"ม.ล. มาลากุล\"}],\"edition\":\"\",\"publisher\":\"แผนกการพิมพ์ โรงเรียนสตรีเนติศึกษา\"}', 'ม.ล. มาลากุล. (2502). <i>คู่มือครูเล่มเล็ก</i>. แผนกการพิมพ์ โรงเรียนสตรีเนติศึกษา.', '(ม.ล. มาลากุล, 2502)', 'ม.ล. มาลากุล (2502)', 'th', 'มาลากุล', 2502, NULL, '2026-03-05 03:04:55', '2026-03-05 03:05:12'),
(449, 12, 1, 80, '{\"title\":\"บรรพชาวงษ์ และ อุดมคติแห่งพระพุทธศาสนา\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"วรนิจ\",\"middleName\":\"(ดอกไม้)\",\"lastName\":\"หวลบุตตา\",\"type\":\"normal\",\"display\":\"วรนิจ หวลบุตตา\"}],\"edition\":\"\",\"publisher\":\"โรงพิมพ์มิตรไทย\"}', 'วรนิจ หวลบุตตา. (2508). <i>บรรพชาวงษ์ และ อุดมคติแห่งพระพุทธศาสนา</i>. โรงพิมพ์มิตรไทย.', '(วรนิจ หวลบุตตา, 2508)', 'วรนิจ หวลบุตตา (2508)', 'th', 'หวลบุตตา', 2508, NULL, '2026-03-05 03:05:55', '2026-03-05 03:06:01'),
(450, 12, 6, 80, '{\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"จิตรา\",\"middleName\":\"\",\"lastName\":\"ดุษฎีเมธา\",\"type\":\"normal\",\"display\":\"จิตรา ดุษฎีเมธา\"},{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"สุเมษย์\",\"middleName\":\"\",\"lastName\":\"หนกหลัง\",\"type\":\"normal\",\"display\":\"สุเมษย์ หนกหลัง\"},{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"รัชวดี\",\"middleName\":\"\",\"lastName\":\"จันทร์ฟอง\",\"type\":\"normal\",\"display\":\"รัชวดี จันทร์ฟอง\"},{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"สุธี\",\"middleName\":\"\",\"lastName\":\"สุนทรชัย\",\"type\":\"normal\",\"display\":\"สุธี สุนทรชัย\"}],\"journal_name\":\"Abcjournal\",\"volume\":\"2\",\"issue\":\"2\",\"pages\":\"31-46\"}', 'จิตรา ดุษฎีเมธา, สุเมษย์ หนกหลัง, รัชวดี จันทร์ฟอง และ สุธี สุนทรชัย. (2566). การพัฒนารูปแบบการสร้างความสุขของผู้สูงอายุด้วยสยามหัวเราะบำบัดผ่านการวิจัยปฏิบัติการแบบมีส่วนร่วมโดยใช้ชุมชนเป็นฐาน: กรณีศึกษา ชุมชนบ้านท่าช้าง อำเภอวัฒนานคร จังหวัดสระแก้ว. <i>Abcjournal</i>, <i>2</i>(2), 31-46.', '(จิตรา ดุษฎีเมธา และคณะ, 2566)', 'จิตรา ดุษฎีเมธา และคณะ (2566)', 'th', 'ดุษฎีเมธา', 2566, NULL, '2026-03-05 03:10:33', '2026-03-05 03:10:42'),
(452, 12, 1, NULL, '{\"title\":\"Harry Potter and the Philosopher&#039;s Stone\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"J.\",\"middleName\":\"K.\",\"lastName\":\"Rowling\",\"type\":\"normal\",\"display\":\"J. Rowling\"}],\"edition\":\"\",\"publisher\":\"salamandra\"}', 'Rowling, J. K. (2540). <i>Harry Potter and the Philosopher\'s Stone</i>. salamandra.', '(Rowling, 1997)', 'Rowling (1997)', 'th', 'Rowling', 2540, NULL, '2026-04-05 14:04:44', '2026-04-05 14:04:44'),
(453, 12, 1, 73, '{\"title\":\"Charlotte&#039;s Web\",\"authors\":[{\"condition\":\"0\",\"conditionValue\":\"\",\"firstName\":\"E.\",\"middleName\":\"B.\",\"lastName\":\"White\",\"type\":\"normal\",\"display\":\"E. White\"}],\"edition\":\"\",\"publisher\":\"Puffin\"}', 'White, E. B. (2495). <i>Charlotte\'s Web</i>. Puffin.', '(White, 1952)', 'White (1952)', 'th', 'White', 2495, NULL, '2026-04-05 14:28:06', '2026-04-06 14:14:23');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `email`, `code`, `expires_at`, `used`, `verified_at`, `created_at`) VALUES
(1, 15, 'ford@babybib.com', '968176', '2025-12-31 11:07:43', 0, '2025-12-31 10:53:04', '2025-12-31 03:52:43'),
(2, 14, 'owen@babybib.com', '285179', '2025-12-31 11:08:11', 0, NULL, '2025-12-31 03:53:11'),
(3, 14, 'owen@babybib.com', '773434', '2025-12-31 11:08:19', 0, NULL, '2025-12-31 03:53:19'),
(4, 2, 'test@gmail.com', '508790', '2025-12-31 11:10:47', 0, NULL, '2025-12-31 03:55:47'),
(5, 16, 'realwarm001@gmail.com', '683452', '2025-12-31 11:25:11', 0, '2025-12-31 11:11:03', '2025-12-31 04:10:11'),
(6, 17, 'thnakon.d@gmail.com', '900785', '2025-12-31 13:09:18', 0, '2025-12-31 12:54:59', '2025-12-31 05:54:18'),
(7, 18, 'thanakonyou001@gmail.com', '331290', '2026-01-01 10:30:52', 0, '2026-01-01 10:16:07', '2026-01-01 03:15:52'),
(8, 19, 'Honda@gmail.com', '331919', '2026-01-31 11:55:44', 0, NULL, '2026-01-31 04:40:44');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','read','resolved') DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `name`, `email`, `subject`, `message`, `status`, `admin_response`, `responded_at`, `created_at`) VALUES
(1, NULL, NULL, NULL, 'อื่นๆ', 'ทดสอบ', 'read', NULL, NULL, '2025-12-28 16:14:48'),
(2, NULL, NULL, NULL, 'แจ้งปัญหา', 'ทดสอบ', 'read', NULL, NULL, '2025-12-29 04:02:41'),
(3, NULL, NULL, NULL, 'อื่นๆ', 'อื่นๆ ทดสอบ', 'read', NULL, NULL, '2025-12-29 08:07:02'),
(4, NULL, NULL, NULL, 'อื่นๆ', 'ทดสอบจาก Safiri', 'read', NULL, NULL, '2025-12-29 16:31:39'),
(5, NULL, NULL, NULL, 'อื่นๆ', 'ทดสอบจากวอร์ม', 'read', NULL, NULL, '2025-12-29 19:55:45'),
(6, 1, NULL, NULL, 'อื่นๆ', 'ทดสอบจาก admin', 'read', NULL, NULL, '2025-12-30 06:47:17'),
(7, 1, NULL, NULL, 'อื่นๆ', 'ทดสอบจาก ADMIN', 'read', NULL, NULL, '2025-12-30 08:59:21'),
(9, NULL, NULL, NULL, 'อื่นๆ', 'ส่งมาจาก Safari แบบ Guest', 'read', NULL, NULL, '2025-12-31 06:04:56'),
(10, 1, NULL, NULL, 'อื่นๆ', 'TEST admin', 'pending', NULL, NULL, '2025-12-31 13:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_visits`
--

CREATE TABLE `page_visits` (
  `id` int(11) UNSIGNED NOT NULL,
  `visit_date` date NOT NULL,
  `visit_count` int(11) UNSIGNED DEFAULT 1,
  `unique_visitors` int(11) UNSIGNED DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_visits`
--

INSERT INTO `page_visits` (`id`, `visit_date`, `visit_count`, `unique_visitors`, `created_at`, `updated_at`) VALUES
(1, '2025-12-29', 8, 8, '2025-12-29 16:26:59', '2025-12-29 16:56:23'),
(9, '2025-12-30', 39, 37, '2025-12-29 17:00:13', '2025-12-30 15:06:41'),
(48, '2025-12-31', 24, 23, '2025-12-31 03:36:49', '2025-12-31 15:27:29'),
(72, '2026-01-01', 8, 6, '2026-01-01 02:58:44', '2026-01-01 03:30:51'),
(80, '2026-01-27', 3, 3, '2026-01-27 13:47:47', '2026-01-27 15:03:18'),
(83, '2026-01-28', 10, 9, '2026-01-28 01:18:32', '2026-01-28 03:42:07'),
(93, '2026-01-30', 10, 9, '2026-01-30 06:34:24', '2026-01-30 11:24:23'),
(103, '2026-01-31', 8, 7, '2026-01-31 04:26:04', '2026-01-31 06:06:07'),
(111, '2026-02-02', 2, 1, '2026-02-02 04:16:07', '2026-02-02 04:16:33'),
(113, '2026-02-05', 2, 2, '2026-02-05 04:01:51', '2026-02-05 04:23:06'),
(115, '2026-02-06', 3, 2, '2026-02-06 04:31:34', '2026-02-06 06:26:33'),
(118, '2026-03-03', 9, 9, '2026-03-03 05:08:51', '2026-03-03 06:37:22'),
(127, '2026-03-04', 13, 12, '2026-03-04 02:42:19', '2026-03-04 14:55:05'),
(140, '2026-03-05', 4, 3, '2026-03-05 02:32:40', '2026-03-05 03:19:36'),
(144, '2026-03-07', 1, 0, '2026-03-07 10:15:46', '2026-03-07 10:15:46'),
(145, '2026-03-08', 4, 3, '2026-03-08 02:57:26', '2026-03-08 09:19:44'),
(149, '2026-03-09', 1, 0, '2026-03-09 04:21:26', '2026-03-09 04:21:26'),
(150, '2026-03-15', 1, 1, '2026-03-15 04:30:00', '2026-03-15 04:30:00'),
(151, '2026-03-18', 4, 4, '2026-03-18 09:59:34', '2026-03-18 14:14:12'),
(155, '2026-03-19', 1, 0, '2026-03-19 07:01:18', '2026-03-19 07:01:18'),
(156, '2026-03-31', 1, 1, '2026-03-31 02:12:32', '2026-03-31 02:12:32'),
(157, '2026-04-01', 1, 0, '2026-04-01 03:53:59', '2026-04-01 03:53:59'),
(158, '2026-04-02', 3, 2, '2026-04-02 09:05:10', '2026-04-02 11:51:55'),
(161, '2026-04-03', 1, 1, '2026-04-03 06:30:36', '2026-04-03 06:30:36'),
(162, '2026-04-04', 3, 3, '2026-04-04 03:31:21', '2026-04-04 09:19:39'),
(165, '2026-04-05', 10, 9, '2026-04-05 05:00:38', '2026-04-05 14:20:51'),
(175, '2026-04-06', 9, 9, '2026-04-06 04:33:39', '2026-04-06 14:58:00'),
(184, '2026-04-07', 10, 10, '2026-04-07 04:11:26', '2026-04-07 12:33:51'),
(194, '2026-04-08', 4, 4, '2026-04-08 04:59:56', '2026-04-08 06:25:21');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(6) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `email`, `code`, `token`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 16, 'realwarm001@gmail.com', '882747', '4a454451d2e9d3108b43c24d274728a6f4bf09e3fbad85d99d6cdae72c33f1e5', '2025-12-31 11:45:02', '2025-12-31 11:30:48', '2025-12-31 04:30:02'),
(2, 20, 'honda@gmail.com', '732178', 'ae57122cba4a028035a0b490aaeeb8edc2fbe967f2f51e702dad0a819221a204', '2026-01-31 13:20:56', NULL, '2026-01-31 06:05:56');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#8B5CF6',
  `bibliography_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `name`, `description`, `color`, `bibliography_count`, `created_at`, `updated_at`) VALUES
(44, 12, 'วิจัยด้านบรรณารักษศาสตร์', 'โครงการวิจัยเกี่ยวกับการจัดการห้องสมุด', '#8B5CF6', 11, '2025-12-30 06:29:32', '2026-03-04 09:51:44'),
(45, 12, 'การศึกษาสารสนเทศศาสตร์', 'รวบรวมงานวิจัยด้านสารสนเทศ', '#10B981', 12, '2025-12-30 06:29:32', '2025-12-30 06:29:32'),
(46, 12, 'เทคโนโลยีสารสนเทศ', 'งานวิจัยด้าน IT และ Digital Library', '#3B82F6', 11, '2025-12-30 06:29:32', '2026-04-05 13:47:47'),
(47, 12, 'การจัดการความรู้', 'Knowledge Management Research', '#F59E0B', 12, '2025-12-30 06:29:32', '2025-12-30 06:29:32'),
(48, 12, 'ห้องสมุดดิจิทัล', 'Digital Library Projects', '#EF4444', 11, '2025-12-30 06:29:32', '2025-12-30 06:42:37'),
(49, 12, 'การอนุรักษ์เอกสาร', 'Document Preservation Study', '#EC4899', 11, '2025-12-30 06:29:32', '2025-12-30 06:42:37'),
(50, 12, 'บริการสารสนเทศ', 'Information Services Research', '#6366F1', 11, '2025-12-30 06:29:32', '2025-12-30 06:42:37'),
(51, 12, 'การวิจัยผู้ใช้', 'User Studies and UX Research', '#14B8A6', 11, '2025-12-30 06:29:32', '2025-12-30 06:42:37'),
(52, 12, 'Metadata Standards', 'Dublin Core and MARC21 Studies', '#8B5CF6', 11, '2025-12-30 06:29:32', '2025-12-30 06:42:37'),
(53, 12, 'Open Access', 'Open Access Movement Research', '#22C55E', 11, '2025-12-30 06:29:32', '2025-12-30 06:42:37'),
(54, 12, 'การจัดหมวดหมู่', 'Classification and Taxonomy', '#F97316', 10, '2025-12-30 06:29:32', '2025-12-30 06:29:32'),
(55, 12, 'Bibliometrics', 'Citation Analysis Research', '#A855F7', 10, '2025-12-30 06:29:32', '2025-12-30 06:29:32'),
(56, 12, 'การรู้สารสนเทศ', 'Information Literacy Studies', '#06B6D4', 10, '2025-12-30 06:29:32', '2025-12-30 06:29:32'),
(57, 12, 'Archives Management', 'จดหมายเหตุและการจัดการ', '#84CC16', 9, '2025-12-30 06:29:32', '2026-03-04 09:51:53'),
(58, 12, 'Data Science', 'Data Analytics in Libraries', '#F43F5E', 9, '2025-12-30 06:29:32', '2026-03-04 09:51:53'),
(59, 12, 'Academic Writing', 'การเขียนเชิงวิชาการ', '#0EA5E9', 9, '2025-12-30 06:29:32', '2026-03-04 09:51:53'),
(60, 12, 'Research Methods', 'ระเบียบวิธีวิจัย', '#7C3AED', 9, '2025-12-30 06:29:32', '2026-03-04 09:51:53'),
(61, 12, 'Thesis Project', 'วิทยานิพนธ์', '#10B981', 9, '2025-12-30 06:29:32', '2026-03-04 09:51:53'),
(62, 12, 'Special Collections', 'หนังสือหายาก', '#D946EF', 9, '2025-12-30 06:29:32', '2026-03-04 09:51:53'),
(63, 12, 'E-Resources', 'ทรัพยากรอิเล็กทรอนิกส์', '#2563EB', 9, '2025-12-30 06:29:32', '2026-03-04 09:51:37'),
(64, 12, 'Library Management', 'การบริหารห้องสมุด', '#059669', 7, '2025-12-30 06:29:32', '2026-03-04 09:51:37'),
(65, 12, 'Cataloging', 'การทำรายการ', '#DC2626', 7, '2025-12-30 06:29:32', '2026-03-04 09:51:37'),
(66, 12, 'Reference Services', 'บริการตอบคำถาม', '#7C3AED', 7, '2025-12-30 06:29:32', '2026-03-04 09:51:37'),
(67, 12, 'Collection Development', 'การพัฒนาทรัพยากร', '#EA580C', 7, '2025-12-30 06:29:32', '2026-03-04 09:51:37'),
(68, 12, 'Copyright Issues', 'ลิขสิทธิ์และกฎหมาย', '#4F46E5', 7, '2025-12-30 06:29:32', '2026-03-04 09:51:37'),
(69, 12, 'Library Automation', 'ระบบห้องสมุดอัตโนมัติ', '#0D9488', 7, '2025-12-30 06:29:32', '2026-03-04 09:51:44'),
(70, 12, 'Information Behavior', 'พฤติกรรมสารสนเทศ', '#C026D3', 7, '2025-12-30 06:29:32', '2026-03-04 09:51:44'),
(71, 12, 'Scholarly Communication', 'การสื่อสารทางวิชาการ', '#0891B2', 7, '2025-12-30 06:29:32', '2026-03-04 09:51:44'),
(73, 12, 'AI in Libraries', 'ปัญญาประดิษฐ์ในห้องสมุด', '#6D28D9', 32, '2025-12-30 06:29:32', '2026-03-04 09:51:44'),
(80, 12, 'Harry potter', 'Harry potter', '#F59E0B', 14, '2026-03-04 08:45:32', '2026-03-05 03:10:42');

-- --------------------------------------------------------

--
-- Table structure for table `resource_types`
--

CREATE TABLE `resource_types` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name_th` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-file',
  `fields_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`fields_config`)),
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resource_types`
--

INSERT INTO `resource_types` (`id`, `code`, `name_th`, `name_en`, `category`, `icon`, `fields_config`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'book', 'หนังสือ', 'Book', 'books', 'fa-book', '{\"fields\":[\"authors\",\"year\",\"title\",\"edition\",\"publisher\",\"place\"]}', 1, 1, '2025-12-26 12:50:18'),
(2, 'book_series', 'หนังสือชุดหลายเล่มจบ', 'Book Series', 'books', 'fa-book-open', '{\"fields\":[\"authors\",\"year\",\"title\",\"volume\",\"edition\",\"publisher\",\"place\"]}', 2, 1, '2025-12-26 12:50:18'),
(3, 'book_chapter', 'บทความในหนังสือ', 'Book Chapter', 'books', 'fa-book-open', '{\"fields\":[\"authors\",\"year\",\"chapter_title\",\"editors\",\"book_title\",\"pages\",\"publisher\",\"place\"]}', 3, 1, '2025-12-26 12:50:18'),
(4, 'ebook_doi', 'หนังสืออิเล็กทรอนิกส์ (มี DOI)', 'E-Book with DOI', 'books', 'fa-tablet-screen-button', '{\"fields\":[\"authors\",\"year\",\"title\",\"edition\",\"publisher\",\"doi\"]}', 4, 1, '2025-12-26 12:50:18'),
(5, 'ebook_no_doi', 'หนังสืออิเล็กทรอนิกส์ (ไม่มี DOI)', 'E-Book without DOI', 'books', 'fa-tablet', '{\"fields\":[\"authors\",\"year\",\"title\",\"edition\",\"publisher\",\"url\"]}', 5, 1, '2025-12-26 12:50:18'),
(6, 'journal_article', 'บทความวารสาร', 'Journal Article', 'journals', 'fa-newspaper', '{\"fields\":[\"authors\",\"year\",\"article_title\",\"journal_name\",\"volume\",\"issue\",\"pages\"]}', 10, 1, '2025-12-26 12:50:18'),
(7, 'ejournal_doi', 'บทความวารสารอิเล็กทรอนิกส์ (มี DOI)', 'Electronic Journal Article with DOI', 'journals', 'fa-file-lines', '{\"fields\":[\"authors\",\"year\",\"article_title\",\"journal_name\",\"volume\",\"issue\",\"pages\",\"doi\"]}', 11, 1, '2025-12-26 12:50:18'),
(8, 'ejournal_no_doi', 'บทความวารสารอิเล็กทรอนิกส์ (ไม่มี DOI)', 'Electronic Journal Article without DOI', 'journals', 'fa-file', '{\"fields\":[\"authors\",\"year\",\"article_title\",\"journal_name\",\"volume\",\"issue\",\"pages\",\"url\"]}', 12, 1, '2025-12-26 12:50:18'),
(9, 'ejournal_print', 'วารสารอิเล็กทรอนิกส์ (แบบมีฉบับพิมพ์)', 'Electronic Journal (Print Version Available)', 'journals', 'fa-copy', '{\"fields\":[\"authors\",\"year\",\"article_title\",\"journal_name\",\"volume\",\"issue\",\"pages\",\"url\"]}', 13, 1, '2025-12-26 12:50:18'),
(10, 'ejournal_only', 'วารสารอิเล็กทรอนิกส์ (แบบไม่มีฉบับพิมพ์)', 'Electronic Journal Only', 'journals', 'fa-globe', '{\"fields\":[\"authors\",\"year\",\"article_title\",\"journal_name\",\"volume\",\"issue\",\"url\"]}', 14, 1, '2025-12-26 12:50:18'),
(11, 'dictionary', 'พจนานุกรม', 'Dictionary', 'reference', 'fa-spell-check', '{\"fields\":[\"title\",\"year\",\"edition\",\"publisher\",\"place\"]}', 20, 1, '2025-12-26 12:50:18'),
(12, 'dictionary_online', 'พจนานุกรมออนไลน์', 'Online Dictionary', 'reference', 'fa-magnifying-glass', '{\"fields\":[\"entry_word\",\"year\",\"dictionary_name\",\"url\",\"accessed_date\"]}', 21, 1, '2025-12-26 12:50:18'),
(13, 'encyclopedia', 'สารานุกรม', 'Encyclopedia', 'reference', 'fa-book-atlas', '{\"fields\":[\"authors\",\"year\",\"entry_title\",\"encyclopedia_name\",\"volume\",\"pages\",\"publisher\",\"place\"]}', 22, 1, '2025-12-26 12:50:18'),
(14, 'encyclopedia_online', 'สารานุกรมออนไลน์', 'Online Encyclopedia', 'reference', 'fa-earth-americas', '{\"fields\":[\"authors\",\"year\",\"entry_title\",\"encyclopedia_name\",\"url\",\"accessed_date\"]}', 23, 1, '2025-12-26 12:50:18'),
(15, 'newspaper_print', 'หนังสือพิมพ์แบบรูปเล่ม', 'Print Newspaper', 'newspapers', 'fa-newspaper', '{\"fields\":[\"authors\",\"year\",\"month\",\"day\",\"article_title\",\"newspaper_name\",\"pages\"]}', 30, 1, '2025-12-26 12:50:18'),
(16, 'newspaper_online', 'หนังสือพิมพ์ออนไลน์', 'Online Newspaper', 'newspapers', 'fa-rss', '{\"fields\":[\"authors\",\"year\",\"month\",\"day\",\"article_title\",\"newspaper_name\",\"url\"]}', 31, 1, '2025-12-26 12:50:18'),
(17, 'report', 'รายงาน', 'Report', 'reports', 'fa-file-contract', '{\"fields\":[\"authors\",\"year\",\"title\",\"report_number\",\"institution\",\"place\"]}', 40, 1, '2025-12-26 12:50:18'),
(18, 'research_report', 'รายงานการวิจัย', 'Research Report', 'reports', 'fa-flask', '{\"fields\":[\"authors\",\"year\",\"title\",\"institution\",\"place\"]}', 41, 1, '2025-12-26 12:50:18'),
(19, 'government_report', 'รายงานที่จัดทำโดยหน่วยงานราชการหรือองค์กรอื่น', 'Government/Organization Report', 'reports', 'fa-landmark', '{\"fields\":[\"organization\",\"year\",\"title\",\"report_number\",\"url\"]}', 42, 1, '2025-12-26 12:50:18'),
(20, 'institutional_report', 'รายงานที่จัดทำโดยบุคคลที่สังกัดหน่วยงาน', 'Institutional Author Report', 'reports', 'fa-building', '{\"fields\":[\"authors\",\"year\",\"title\",\"institution\",\"url\"]}', 43, 1, '2025-12-26 12:50:18'),
(21, 'conference_proceeding', 'เอกสารการประชุมทางวิชาการ (ที่มี Proceeding)', 'Conference Proceeding (Published)', 'conferences', 'fa-users', '{\"fields\":[\"authors\",\"year\",\"paper_title\",\"editors\",\"proceeding_title\",\"pages\",\"publisher\",\"place\"]}', 50, 1, '2025-12-26 12:50:18'),
(22, 'conference_no_proceeding', 'เอกสารการประชุมทางวิชาการ (ที่ไม่มี Proceeding)', 'Conference Paper (Unpublished)', 'conferences', 'fa-user-group', '{\"fields\":[\"authors\",\"year\",\"month\",\"paper_title\",\"conference_name\",\"location\"]}', 51, 1, '2025-12-26 12:50:18'),
(23, 'conference_presentation', 'การนำเสนองานวิจัยหรือโปสเตอร์ในงานประชุมวิชาการ', 'Conference Presentation/Poster', 'conferences', 'fa-chalkboard-user', '{\"fields\":[\"authors\",\"year\",\"month\",\"presentation_title\",\"presentation_type\",\"conference_name\",\"location\"]}', 52, 1, '2025-12-26 12:50:18'),
(24, 'thesis_unpublished', 'วิทยานิพนธ์ (ที่ไม่ได้ตีพิมพ์)', 'Unpublished Thesis/Dissertation', 'theses', 'fa-graduation-cap', '{\"fields\":[\"authors\",\"year\",\"title\",\"degree_type\",\"institution\",\"place\"]}', 60, 1, '2025-12-26 12:50:18'),
(25, 'thesis_website', 'วิทยานิพนธ์จากเว็บไซต์', 'Thesis from Website', 'theses', 'fa-globe', '{\"fields\":[\"authors\",\"year\",\"title\",\"degree_type\",\"institution\",\"url\"]}', 61, 1, '2025-12-26 12:50:18'),
(26, 'thesis_database', 'วิทยานิพนธ์จากฐานข้อมูลเชิงพาณิชย์', 'Thesis from Database', 'theses', 'fa-database', '{\"fields\":[\"authors\",\"year\",\"title\",\"degree_type\",\"institution\",\"database_name\",\"accession_number\"]}', 62, 1, '2025-12-26 12:50:18'),
(27, 'webpage', 'เอกสารอิเล็กทรอนิกส์ (เว็บเพจ)', 'Web Page/Electronic Document', 'online', 'fa-globe', '{\"fields\":[\"authors\",\"year\",\"month\",\"day\",\"page_title\",\"website_name\",\"url\"]}', 70, 1, '2025-12-26 12:50:18'),
(28, 'social_media', 'สื่อออนไลน์ (วิดีโอออนไลน์ บทความในโซเชียลมีเดีย)', 'Social Media/Online Media', 'online', 'fa-share-nodes', '{\"fields\":[\"authors\",\"year\",\"month\",\"day\",\"content_title\",\"platform\",\"url\"]}', 71, 1, '2025-12-26 12:50:18'),
(29, 'royal_gazette', 'ราชกิจจานุเบกษาออนไลน์', 'Royal Thai Government Gazette Online', 'online', 'fa-scroll', '{\"fields\":[\"title\",\"year\",\"volume\",\"section\",\"pages\",\"url\"]}', 72, 1, '2025-12-26 12:50:18'),
(30, 'patent_online', 'สิทธิบัตรออนไลน์', 'Online Patent', 'online', 'fa-certificate', '{\"fields\":[\"inventors\",\"year\",\"patent_title\",\"patent_number\",\"patent_office\",\"url\"]}', 73, 1, '2025-12-26 12:50:18'),
(31, 'personal_communication', 'การติดต่อสื่อสารส่วนบุคคล', 'Personal Communication', 'online', 'fa-envelope', '{\"fields\":[\"communicator_name\",\"year\",\"month\",\"day\",\"communication_type\"]}', 74, 1, '2025-12-26 12:50:18'),
(32, 'infographic', 'อินโฟกราฟิก (Infographic)', 'Infographic', 'media', 'fa-chart-pie', '{\"fields\":[\"authors\",\"year\",\"title\",\"website_name\",\"url\"]}', 80, 1, '2025-12-26 12:50:18'),
(33, 'slides_online', 'การนำเสนอด้วยสไลด์และเอกสารการสอนออนไลน์', 'Online Slides/Teaching Materials', 'media', 'fa-file-powerpoint', '{\"fields\":[\"authors\",\"year\",\"title\",\"platform\",\"url\"]}', 81, 1, '2025-12-26 12:50:18'),
(34, 'webinar', 'สัมมนาออนไลน์ (Webinar)', 'Webinar', 'media', 'fa-video', '{\"fields\":[\"presenters\",\"year\",\"month\",\"day\",\"webinar_title\",\"organization\",\"url\"]}', 82, 1, '2025-12-26 12:50:18'),
(35, 'youtube_video', 'วิดีโอใน Youtube หรือวิดีโอออนไลน์ต่าง ๆ', 'YouTube/Online Video', 'media', 'fa-youtube', '{\"fields\":[\"channel_name\",\"year\",\"month\",\"day\",\"video_title\",\"url\"]}', 83, 1, '2025-12-26 12:50:18'),
(36, 'podcast', 'พอดแคสต์ภาพและเสียง (แบบจบในตอน)', 'Podcast Episode (Single)', 'media', 'fa-podcast', '{\"fields\":[\"host\",\"year\",\"month\",\"day\",\"episode_title\",\"podcast_name\",\"url\"]}', 84, 1, '2025-12-26 12:50:18'),
(37, 'ai_generated', 'AI (เนื้อหาที่สร้างโดย AI)', 'AI Generated Content', 'others', 'fa-robot', '{\"fields\":[\"ai_name\",\"year\",\"month\",\"day\",\"prompt_description\",\"version\",\"url\"]}', 90, 1, '2025-12-26 12:50:18'),
(38, 'podcast_series', 'พอดแคสต์ภาพและเสียง (แบบหลายตอน)', 'Podcast Series (Multiple Episodes)', 'media', 'fa-podcast', '{\"fields\":[\"host\",\"year_start\",\"year_end\",\"podcast_name\",\"producer\",\"url\"]}', 85, 1, '2026-03-04 09:13:48');

-- --------------------------------------------------------

--
-- Table structure for table `site_visits`
--

CREATE TABLE `site_visits` (
  `id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_visits`
--

INSERT INTO `site_visits` (`id`, `visit_date`, `visit_count`) VALUES
(1, '2025-12-28', 12);

-- --------------------------------------------------------

--
-- Table structure for table `support_reports`
--

CREATE TABLE `support_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `issue_type` enum('bug','feature','help','other') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','resolved','closed') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_reports`
--

INSERT INTO `support_reports` (`id`, `user_id`, `issue_type`, `subject`, `description`, `status`, `admin_notes`, `created_at`, `updated_at`, `resolved_at`) VALUES
(1, 7, 'other', 'ลืมรหัสผ่าน', 'ลืมรหัสผ่านที่แก้ไขล่าสุด', 'pending', NULL, '2025-12-29 20:03:24', '2025-12-29 20:03:24', NULL),
(2, 4, 'feature', 'ทดสอบแจ้งเตือน admin', 'ทดสอบแจ้งเตือน admin', 'pending', NULL, '2025-12-30 13:40:35', '2025-12-30 13:40:35', NULL),
(3, 24, 'bug', 'Smoke report', 'support flow smoke test', 'pending', NULL, '2026-04-08 05:42:57', '2026-04-08 05:42:57', NULL),
(4, 25, 'bug', 'Smoke report', 'support flow smoke test', 'pending', NULL, '2026-04-08 05:45:21', '2026-04-08 05:45:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Babybib', 'Website name', '2025-12-30 08:09:17'),
(2, 'max_bibliographies_per_user', '300', 'Maximum bibliographies per user', '2026-03-03 05:40:25'),
(3, 'max_projects_per_user', '30', 'Maximum projects per user', '2026-03-03 05:40:25'),
(4, 'maintenance_mode', '0', 'Maintenance mode status', '2026-03-03 05:40:25'),
(5, 'default_language', 'th', 'Default language', '2025-12-26 12:50:01'),
(6, 'provinces', '[\"กรุงเทพมหานคร\",\"กระบี่\",\"กาญจนบุรี\",\"กาฬสินธุ์\",\"กำแพงเพชร\",\"ขอนแก่น\",\"จันทบุรี\",\"ฉะเชิงเทรา\",\"ชลบุรี\",\"ชัยนาท\",\"ชัยภูมิ\",\"ชุมพร\",\"เชียงราย\",\"เชียงใหม่\",\"ตรัง\",\"ตราด\",\"ตาก\",\"นครนายก\",\"นครปฐม\",\"นครพนม\",\"นครราชสีมา\",\"นครศรีธรรมราช\",\"นครสวรรค์\",\"นนทบุรี\",\"นราธิวาส\",\"น่าน\",\"บึงกาฬ\",\"บุรีรัมย์\",\"ปทุมธานี\",\"ประจวบคีรีขันธ์\",\"ปราจีนบุรี\",\"ปัตตานี\",\"พระนครศรีอยุธยา\",\"พังงา\",\"พัทลุง\",\"พิจิตร\",\"พิษณุโลก\",\"เพชรบุรี\",\"เพชรบูรณ์\",\"แพร่\",\"พะเยา\",\"ภูเก็ต\",\"มหาสารคาม\",\"มุกดาหาร\",\"แม่ฮ่องสอน\",\"ยโสธร\",\"ยะลา\",\"ร้อยเอ็ด\",\"ระนอง\",\"ระยอง\",\"ราชบุรี\",\"ลพบุรี\",\"ลำปาง\",\"ลำพูน\",\"เลย\",\"ศรีสะเกษ\",\"สกลนคร\",\"สงขลา\",\"สตูล\",\"สมุทรปราการ\",\"สมุทรสงคราม\",\"สมุทรสาคร\",\"สระแก้ว\",\"สระบุรี\",\"สิงห์บุรี\",\"สุโขทัย\",\"สุพรรณบุรี\",\"สุราษฎร์ธานี\",\"สุรินทร์\",\"หนองคาย\",\"หนองบัวลำภู\",\"อ่างทอง\",\"อุดรธานี\",\"อุทัยธานี\",\"อุตรดิตถ์\",\"อุบลราชธานี\",\"อำนาจเจริญ\"]', 'Thai provinces list', '2025-12-26 12:50:01'),
(7, 'site_title', 'Babybib', NULL, '2026-03-03 05:40:25'),
(8, 'site_description', '', NULL, '2026-03-03 05:40:25'),
(9, 'contact_email', 'thanayok@gmail.com', NULL, '2026-03-03 05:40:25'),
(10, 'max_bibs_per_user', '300', NULL, '2026-03-03 05:40:25'),
(11, 'bib_lifetime_days', '730', NULL, '2026-03-03 05:40:25'),
(12, 'allow_registration', '1', NULL, '2026-03-03 05:40:25'),
(13, 'smtp_username', 'realwarm001@gmail.com', NULL, '2026-03-03 05:40:25'),
(14, 'smtp_password', 'wsob tsis onml khlt', NULL, '2026-03-03 05:40:25'),
(15, 'email_verification_enabled', '0', NULL, '2026-01-31 04:42:26'),
(16, 'support_email', 'support.local@babybib.com', NULL, '2026-03-03 05:40:25'),
(17, 'support_phone', '064-2475-959', NULL, '2026-03-03 05:40:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `org_type` enum('university','high_school','opportunity_school','primary_school','government','private_company','personal','other') DEFAULT 'personal',
  `org_name` varchar(255) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `is_lis_cmu` tinyint(1) DEFAULT 0,
  `student_id` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `language` varchar(2) DEFAULT 'th',
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `bibliography_count` int(11) DEFAULT 0,
  `project_count` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `surname`, `email`, `password`, `org_type`, `org_name`, `province`, `is_lis_cmu`, `student_id`, `role`, `token`, `token_expiry`, `reset_token`, `reset_token_expires`, `language`, `is_active`, `is_verified`, `bibliography_count`, `project_count`, `last_login`, `created_at`, `updated_at`, `profile_picture`) VALUES
(1, 'admin', 'System', 'Administrator', 'admin@babybib.com', '$2y$12$j1Wk0ti7UkZYEJT0yEFXe.gfyzJ.ktsfiBWjhyGIAu84KEpQlz9.a', 'university', 'มหาวิทยาลัยเชียงใหม่', 'กรุงเทพมหานคร', 0, NULL, 'admin', '644d1272-e259-11f0-a886-9a8017e68ece', NULL, NULL, NULL, 'th', 1, 1, 0, 0, '2026-04-08 13:09:52', '2025-12-26 12:50:01', '2026-04-08 06:09:52', 'avatar_1_1767106611.png'),
(12, 'usedcase', 'ทดสอบ', 'ระบบ', 'usedcase@example.com', '$2y$12$tGy01BcmwyxDG3AiRYICHuHg01lbE/xMPkgwyX0fa7773LT/jpC5W', 'university', 'มหาวิทยาลัยเชียงใหม่', 'เชียงใหม่', 1, '650110xxx', 'user', NULL, NULL, NULL, NULL, 'th', 1, 1, 294, 30, '2026-04-07 19:33:50', '2025-12-30 06:29:32', '2026-04-07 12:33:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_ratings`
--

CREATE TABLE `user_ratings` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'NULL for guest users',
  `rating` tinyint(1) NOT NULL COMMENT 'Rating 1-5 stars',
  `page_url` varchar(255) DEFAULT NULL COMMENT 'Page where rating was given',
  `user_agent` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_ratings`
--

INSERT INTO `user_ratings` (`id`, `user_id`, `rating`, `page_url`, `user_agent`, `ip_address`, `session_id`, `created_at`) VALUES
(1, NULL, 5, '/babybib_db/', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '3l2l4l2q0mli7gh7duntq75ink', '2025-12-29 16:24:43'),
(2, NULL, 3, '/babybib_db/', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Safari/605.1.15', '::1', 'tbmat6qgctaumdgimkc32m1ffu', '2025-12-29 16:31:21'),
(3, NULL, 3, '/babybib_db/summary.php', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', 'g1vieasah4l17gpto4duhm5sdo', '2025-12-29 16:37:26'),
(4, 7, 5, '/babybib_db/users/dashboard.php', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', 'chfdusbnvudjgrtl8jjbhff6gs', '2025-12-29 19:54:17'),
(5, 1, 5, '/babybib_db/admin/feedback.php', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', 'vs6tqm4kpojbnmo1t5hf7lfgt1', '2025-12-30 08:00:00'),
(6, 2, 4, '/babybib_db/users/dashboard.php', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '::1', '91cg96cjdfgccmr0h5pjgfqcef', '2025-12-30 08:56:22'),
(7, 17, 5, '/babybib_db/users/dashboard.php', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '::1', 'q3nldf76hge36sioes48d7g714', '2025-12-31 05:55:33'),
(8, 4, 5, '/babybib_db/users/dashboard', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Safari/605.1.15', '::1', 'i0svjdpev49gndeeksnhshlqbr', '2025-12-31 13:19:54'),
(9, NULL, 1, '/babybib_db/generate', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '::1', 'id26bc34ghvu58n3oplmro4ln6', '2026-01-30 10:19:13'),
(10, NULL, 1, '/babybib_db/', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', 'bhgk7rig48p10qcq47q77io176', '2026-01-30 10:29:05'),
(11, NULL, 2, '/babybib_db/generate', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Safari/605.1.15', '::1', '6ksdq6qs3roibmh1pgfk7dr9on', '2026-01-30 10:41:56'),
(12, NULL, 5, '/babybib_db/', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '::1', 'ip58mun80nl61qffk46f9fa1h1', '2026-01-30 11:13:07'),
(13, 20, 5, '/babybib_db/users/dashboard', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Safari/605.1.15', '::1', 'l8gvb1ttchdas6ubs0a7hg7osg', '2026-01-31 06:05:10'),
(14, NULL, 5, '/babybib_db/', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', 'eeojbgaobquuuahj9rrusl82hk', '2026-03-03 09:51:27'),
(15, NULL, 3, '/babybib_db/', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', 'eeojbgaobquuuahj9rrusl82hk', '2026-03-03 13:49:49'),
(16, NULL, 5, '/babybib_db/', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', 'eeojbgaobquuuahj9rrusl82hk', '2026-03-04 03:13:29'),
(17, NULL, 1, '/babybib_db/', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', 'mk8r8j8gg8s8b9pgoqlii1jd00', '2026-03-04 03:50:26'),
(18, NULL, 5, '/babybib_db/help-publisher', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', 'mk8r8j8gg8s8b9pgoqlii1jd00', '2026-03-04 05:07:33'),
(19, 12, 5, '/babybib_db/users/report-template', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '::1', 'kk60qjpsaeag2fc6nm2d8ept3s', '2026-04-07 05:33:30'),
(20, 24, 5, '/smoke-test', 'curl/8.7.1', '::1', '0gdb4nk24q7nnfmj1s34eilu9g', '2026-04-08 05:42:57'),
(21, 25, 5, '/smoke-test', 'curl/8.7.1', '::1', 'eilaf1ftop0jlo789oe45v7q7s', '2026-04-08 05:45:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `bibliographies`
--
ALTER TABLE `bibliographies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resource_type_id` (`resource_type_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_code` (`code`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip_address` (`ip_address`);

--
-- Indexes for table `page_visits`
--
ALTER TABLE `page_visits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_visit_date` (`visit_date`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resource_types`
--
ALTER TABLE `resource_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `site_visits`
--
ALTER TABLE `site_visits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date` (`visit_date`);

--
-- Indexes for table `support_reports`
--
ALTER TABLE `support_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_ratings`
--
ALTER TABLE `user_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=572;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bibliographies`
--
ALTER TABLE `bibliographies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=454;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `page_visits`
--
ALTER TABLE `page_visits`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `resource_types`
--
ALTER TABLE `resource_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `site_visits`
--
ALTER TABLE `site_visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `support_reports`
--
ALTER TABLE `support_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `user_ratings`
--
ALTER TABLE `user_ratings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bibliographies`
--
ALTER TABLE `bibliographies`
  ADD CONSTRAINT `bibliographies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bibliographies_ibfk_2` FOREIGN KEY (`resource_type_id`) REFERENCES `resource_types` (`id`),
  ADD CONSTRAINT `bibliographies_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
