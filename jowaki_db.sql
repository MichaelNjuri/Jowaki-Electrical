-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 10, 2025 at 09:21 PM
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
-- Database: `jowaki_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','manager') NOT NULL DEFAULT 'manager',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `first_name`, `last_name`, `role`, `is_active`, `failed_attempts`, `locked_until`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin@jowaki.com', '$argon2id$v=19$m=65536,t=4,p=3$WUhLQWdrNUJQeWI0VHJZaQ$5gYepcj42ZgOQxoRgVDwQ7IgiN5JsSxZYf1GBvmF7NM', 'Super', 'Admin', 'super_admin', 1, 0, NULL, '2025-08-04 10:37:58', '2025-08-04 10:16:37', '2025-08-04 10:37:58');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity`
--

CREATE TABLE `admin_activity` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_activity`
--

INSERT INTO `admin_activity` (`id`, `admin_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'test_action', 'Test activity logging', '2025-08-10 16:46:17'),
(2, 1, 'confirm_order', '{\"order_id\":19,\"previous_status\":\"pending\",\"new_status\":\"confirmed\",\"notes\":\"\"}', '2025-08-10 16:49:06'),
(3, 1, 'confirm_order', '{\"order_id\":19,\"previous_status\":\"pending\",\"new_status\":\"confirmed\",\"notes\":\"\"}', '2025-08-10 16:51:56'),
(4, 1, 'confirm_order', '{\"order_id\":19,\"previous_status\":\"pending\",\"new_status\":\"confirmed\",\"notes\":\"\"}', '2025-08-10 16:55:39'),
(5, 1, 'confirm_order', '{\"order_id\":19,\"previous_status\":\"pending\",\"new_status\":\"confirmed\",\"notes\":\"\"}', '2025-08-10 16:59:41'),
(6, 1, 'confirm_order', '{\"order_id\":19,\"previous_status\":\"pending\",\"new_status\":\"confirmed\",\"notes\":\"\"}', '2025-08-10 17:03:26');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `admin_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 0, 'update_settings', '{\"tax_rate\":\"16\",\"standard_delivery_fee\":\"0\",\"express_delivery_fee\":\"500\",\"store_name\":\"Jowaki Electrical Services\",\"store_email\":\"jowakielectricalsrvs@gmail.com\",\"store_phone\":\"+254721442248\",\"store_address\":\"\"}', '::1', NULL, '2025-08-03 10:32:20'),
(2, 0, 'update_settings', '{\"enable_login_notifications\":\"1\",\"enable_audit_log\":\"1\"}', '::1', NULL, '2025-08-03 10:32:35'),
(3, 0, 'update_product', '{\"product_id\":8,\"name\":\"Chain link\",\"category\":\"Solar\",\"price\":5000,\"stock\":90,\"status\":\"active\"}', '::1', NULL, '2025-08-03 10:49:41'),
(4, 0, 'update_product', '{\"product_id\":8,\"name\":\"Chain link\",\"category\":\"Solar\",\"price\":5000,\"stock\":40,\"status\":\"active\"}', '::1', NULL, '2025-08-03 11:31:11'),
(5, 0, 'update_product', '{\"product_id\":8,\"name\":\"Chain link\",\"category\":\"Solar\",\"price\":5000,\"stock\":20,\"status\":\"active\"}', '::1', NULL, '2025-08-03 13:56:30'),
(6, 0, 'update_settings', '{\"tax_rate\":\"16\",\"standard_delivery_fee\":\"0\",\"express_delivery_fee\":\"500\",\"store_name\":\"Jowaki Electrical Services\",\"store_email\":\"jowakielectricalsrvs@gmail.com\",\"store_phone\":\"0743125249\",\"store_address\":\"jowakielectricalsrvs@gmail.com\\nGaborone Road,Nairobi\\nOne stop shop for cctv,solar and more\"}', '::1', NULL, '2025-08-05 15:36:23'),
(7, 0, 'update_settings', '{\"enable_mpesa\":\"1\",\"mpesa_business_number\":\"0743125249\",\"enable_card\":\"1\",\"enable_whatsapp\":\"1\",\"whatsapp_number\":\"0743125249\"}', '::1', NULL, '2025-08-05 15:37:04'),
(8, 0, 'update_settings', '{\"enable_standard_delivery\":\"1\",\"standard_delivery_time\":\"3-5 business days\",\"enable_express_delivery\":\"1\",\"express_delivery_time\":\"1-2 business days\",\"enable_pickup\":\"1\",\"pickup_location\":\"jowakielectricalsrvs@gmail.com\\nGaborone Road,Nairobi\\nOne stop shop for cctv,solar and more\"}', '::1', NULL, '2025-08-05 15:37:27'),
(9, 0, 'update_settings', '{\"tax_rate\":\"15\",\"standard_delivery_fee\":\"0\",\"express_delivery_fee\":\"500\",\"store_name\":\"Jowaki Electrical Services\",\"store_email\":\"info@jowaki.com\",\"store_phone\":\"+254721442248\",\"store_address\":\"\"}', '::1', NULL, '2025-08-05 15:45:46'),
(10, 0, 'update_settings', '{\"tax_rate\":\"14\",\"standard_delivery_fee\":\"0\",\"express_delivery_fee\":\"40\",\"store_name\":\"Jowaki Electrical Services\",\"store_email\":\"info@jowaki.com\",\"store_phone\":\"+254721442248\",\"store_address\":\"\"}', '::1', NULL, '2025-08-05 17:00:58'),
(11, 0, 'update_settings', '{\"tax_rate\":\"14\",\"standard_delivery_fee\":\"0\",\"express_delivery_fee\":\"40\",\"store_name\":\"Jowaki Electrical Services\",\"store_email\":\"info@jowaki.com\",\"store_phone\":\"+254721442248\",\"store_address\":\"\"}', '::1', NULL, '2025-08-05 17:00:58'),
(12, 0, 'update_settings', '{\"tax_rate\":\"17\",\"standard_delivery_fee\":\"0\",\"express_delivery_fee\":\"550\",\"store_name\":\"Jowaki Electrical Services\",\"store_email\":\"info@jowaki.com\",\"store_phone\":\"+254721442248\",\"store_address\":\"\"}', '::1', NULL, '2025-08-05 17:01:20'),
(13, 0, 'update_settings', '{\"tax_rate\":\"17\",\"standard_delivery_fee\":\"0\",\"express_delivery_fee\":\"550\",\"store_name\":\"Jowaki Electrical Services\",\"store_email\":\"info@jowaki.com\",\"store_phone\":\"+254721442248\",\"store_address\":\"\"}', '::1', NULL, '2025-08-05 17:01:20'),
(14, 0, 'update_settings', '{\"tax_rate\":\"19\",\"standard_delivery_fee\":\"0\",\"express_delivery_fee\":\"550\",\"store_name\":\"Jowaki Electrical Services\",\"store_email\":\"jowakielectricalsrvs@gmail.com\",\"store_phone\":\"+254721442248\",\"store_address\":\"Gaborone Road,Nairobi\"}', '::1', NULL, '2025-08-05 17:41:39'),
(15, 0, 'update_product', '{\"product_id\":8,\"name\":\"Chain link\",\"category\":\"Security Equipment\",\"price\":5000,\"stock\":20,\"status\":\"active\"}', '::1', NULL, '2025-08-06 18:39:04'),
(16, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:09:59'),
(17, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:11:39'),
(18, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:11:48'),
(19, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:11:49'),
(20, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:10'),
(21, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:12'),
(22, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:13'),
(23, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:13'),
(24, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:14'),
(25, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:14'),
(26, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:14'),
(27, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:14'),
(28, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:30'),
(29, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:15:31'),
(30, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:16:27'),
(31, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:16:30'),
(32, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:17:27'),
(33, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:17:34'),
(34, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:17:35'),
(35, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:19:22'),
(36, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:19:23'),
(37, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:19:24'),
(38, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-09 14:19:26'),
(39, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:18:03'),
(40, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:20:33'),
(41, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:20:36'),
(42, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:24:30'),
(43, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:24:37'),
(44, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:24:48'),
(45, 1, 'Login', 'Admin logged in successfully', '::1', '', '2025-08-10 09:25:53'),
(46, 1, 'Login', 'Admin logged in successfully', '::1', '', '2025-08-10 09:25:53'),
(47, 1, 'Login', 'Admin logged in successfully', '::1', 'curl/8.14.1', '2025-08-10 09:25:54'),
(48, 1, 'Login', 'Admin logged in successfully', '::1', '', '2025-08-10 09:26:40'),
(49, 1, 'Login', 'Admin logged in successfully', '::1', '', '2025-08-10 09:26:40'),
(50, 1, 'Login', 'Admin logged in successfully', '::1', 'curl/8.14.1', '2025-08-10 09:26:40'),
(51, 1, 'Login', 'Admin logged in successfully', '::1', '', '2025-08-10 09:27:20'),
(52, 1, 'Login', 'Admin logged in successfully', '::1', '', '2025-08-10 09:27:21'),
(53, 1, 'Login', 'Admin logged in successfully', '::1', 'curl/8.14.1', '2025-08-10 09:27:21'),
(54, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:28:28'),
(55, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:28:38'),
(56, 1, 'View Contact Messages', 'Viewed contact messages in admin dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:28:41'),
(57, 1, 'View Contact Messages', 'Viewed contact messages in admin dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:32:50'),
(58, 1, 'View Contact Messages', 'Viewed contact messages in admin dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:33:40'),
(59, 1, 'View Contact Messages', 'Viewed contact messages in admin dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:33:43'),
(60, 1, 'View Contact Messages', 'Viewed contact messages in admin dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:33:45'),
(61, 1, 'View Contact Messages', 'Viewed contact messages in admin dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:33:48'),
(62, 1, 'View Admins', 'Viewed admin management page', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 09:52:32'),
(63, 1, 'Create Admin', 'Created admin user: admin2 (Mike N)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 10:05:25'),
(64, 1, 'Create Admin', 'Created admin user: testadmin (Test Admin)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 10:06:27'),
(65, 4, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 10:10:41'),
(66, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 10:41:51'),
(67, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 11:28:49'),
(68, 1, 'Deactivate Admin', 'Admin ID: 5 status changed to Inactive', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 11:52:44'),
(69, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 12:06:12'),
(70, 4, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 12:09:03'),
(71, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 12:50:55'),
(72, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 16:00:41'),
(73, 1, 'Login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-10 16:34:34'),
(74, 0, 'update_settings', '{\"enable_login_notifications\":\"1\",\"enable_audit_log\":\"1\"}', '::1', NULL, '2025-08-10 16:56:48'),
(75, 0, 'update_settings', '{\"enable_2fa\":\"0\",\"enable_login_notifications\":\"1\",\"enable_audit_log\":\"1\"}', '::1', NULL, '2025-08-10 16:56:48'),
(76, 0, 'update_settings', '{\"enable_2fa\":\"0\",\"enable_login_notifications\":\"1\",\"enable_audit_log\":\"1\"}', '::1', NULL, '2025-08-10 16:56:48');

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `granted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `granted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `admin_id`, `permission`, `granted_at`, `granted_by`) VALUES
(1, 1, 'manage_admins', '2025-08-04 10:16:37', NULL),
(2, 1, 'manage_products', '2025-08-04 10:16:37', NULL),
(3, 1, 'manage_orders', '2025-08-04 10:16:37', NULL),
(4, 1, 'manage_customers', '2025-08-04 10:16:37', NULL),
(5, 1, 'view_analytics', '2025-08-04 10:16:37', NULL),
(6, 1, 'manage_settings', '2025-08-04 10:16:37', NULL),
(7, 1, 'view_reports', '2025-08-04 10:16:37', NULL),
(8, 1, 'delete_data', '2025-08-04 10:16:37', NULL),
(9, 1, 'manage_inventory', '2025-08-04 10:16:37', NULL),
(10, 1, 'manage_categories', '2025-08-04 10:16:37', NULL),
(11, 1, 'view_logs', '2025-08-04 10:16:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_roles`
--

INSERT INTO `admin_roles` (`id`, `role_name`, `role_description`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Full system access including admin management', '{\"dashboard\":true,\"products\":true,\"orders\":true,\"customers\":true,\"categories\":true,\"analytics\":true,\"settings\":true,\"admin_management\":true,\"create_admins\":true,\"delete_admins\":true,\"view_logs\":true,\"backup\":true}', '2025-08-09 13:09:26', '2025-08-09 13:09:26'),
(2, 'Admin', 'Full access to all features except admin management', '{\"dashboard\":true,\"products\":true,\"orders\":true,\"customers\":true,\"categories\":true,\"analytics\":true,\"settings\":true,\"admin_management\":false,\"create_admins\":false,\"delete_admins\":false,\"view_logs\":true,\"backup\":true}', '2025-08-09 13:09:26', '2025-08-09 13:09:26'),
(3, 'Manager', 'Limited access to core business functions', '{\"dashboard\":true,\"products\":true,\"orders\":true,\"customers\":true,\"categories\":false,\"analytics\":true,\"settings\":false,\"admin_management\":false,\"create_admins\":false,\"delete_admins\":false,\"view_logs\":false,\"backup\":false}', '2025-08-09 13:09:26', '2025-08-09 13:09:26');

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_super_admin` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role_id`, `is_active`, `is_super_admin`, `last_login`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@jowaki.com', '$2y$10$pdlhS9TrkfgsAyDdeUpcFeBxdUEpSL.RFGU2O4Wc38HG4VRTe/ELy', 'Super', 'Admin', 1, 1, 1, '2025-08-10 16:34:34', NULL, '2025-08-09 13:09:26', '2025-08-10 16:34:34'),
(4, 'admin2', 'kibukush@gmail.com', '$2y$10$TYiryg9Bupkx4j8O7eX67e.yz6g1Ltzt7eIqQ7yiLDFgcRcEjB5zS', 'Mike', 'N', 2, 1, 0, '2025-08-10 12:09:03', NULL, '2025-08-10 10:05:25', '2025-08-10 12:09:03'),
(5, 'testadmin', 'testadmin@example.com', '$2y$10$M9wBy9ExoZ5BJU4eO.PxBe1EmnPKvmSjlhFlxlyNQ68iATVUC1o1C', 'Test', 'Admin', 2, 0, 0, NULL, NULL, '2025-08-10 10:06:27', '2025-08-10 11:52:44');

-- --------------------------------------------------------

--
-- Table structure for table `carousel_banners`
--

CREATE TABLE `carousel_banners` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `subcategory` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category`, `subcategory`) VALUES
(1, 'CCTV Systems', 'Security Cameras'),
(3, 'Solar', 'Panels'),
(4, 'Solar', 'Inverters');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `submitted_at`, `ip_address`, `is_read`, `created_at`) VALUES
(1, 'Michael', 'kibukush@gmail.com', '', 'price estimation', 'testing estimation', '2025-08-08 08:07:53', '::1', 0, '2025-08-08 06:07:55'),
(2, 'Test User', 'test@example.com', '', 'Test Message', 'This is a test message to verify the contact form is working properly.', '2025-08-08 08:13:35', '::1', 1, '2025-08-08 06:13:37');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`customer_info`)),
  `cart` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`cart`)),
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT 0.00,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `delivery_method` varchar(50) NOT NULL,
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_info`, `cart`, `subtotal`, `tax`, `delivery_fee`, `total`, `delivery_method`, `delivery_address`, `payment_method`, `total_price`, `status`, `order_date`, `confirmed_at`, `user_id`, `updated_at`) VALUES
(1, '{\"firstName\":\"Michael\",\"lastName\":\"Njuri\",\"email\":\"michaelnjuri4@gmail.com\",\"phone\":\"0758744739\",\"address\":\"Gikambura\",\"city\":\"Kikuyu\",\"postalCode\":\"00902\",\"deliveryMethod\":\"pickup\",\"deliveryAddress\":\"Gikambura\",\"paymentMethod\":\"mpesa\"}', '[{\"id\":3,\"name\":\"Fence\",\"price\":50000,\"quantity\":1,\"image\":\"uploads\\/IMG_7.jpg\"}]', 50000.00, 8000.00, 0.00, 58000.00, 'pickup', 'Gikambura', 'mpesa', NULL, 'delivered', '2025-07-18 14:44:00', NULL, 2, '2025-08-06 18:36:23'),
(2, '{\"firstName\":\"John\",\"lastName\":\"Kinuthia\",\"email\":\"jowakielectrical@gmail.com\",\"phone\":\"0758744739\",\"address\":\"good\",\"city\":\"here\",\"postalCode\":\"00902\",\"deliveryMethod\":\"express\",\"deliveryAddress\":\"good\",\"paymentMethod\":\"mpesa\"}', '[{\"id\":3,\"name\":\"Fence\",\"price\":50000,\"quantity\":1,\"image\":\"uploads\\/IMG_7.jpg\"}]', 50000.00, 8000.00, 500.00, 58500.00, 'express', 'good', 'mpesa', NULL, 'delivered', '2025-07-23 20:38:19', NULL, 2, NULL),
(3, '{\"firstName\":\"Michael\",\"lastName\":\"Kinuthia\",\"email\":\"michaelnjuri4@gmail.comTesting2\",\"phone\":\"0758744739\",\"address\":\"kenya\",\"city\":\"kikuyu\",\"postalCode\":\"00902\",\"deliveryMethod\":\"pickup\",\"deliveryAddress\":\"kenya\",\"paymentMethod\":\"mpesa\"}', '[{\"id\":3,\"name\":\"Fence\",\"price\":50000,\"quantity\":1,\"image\":\"uploads\\/IMG_7.jpg\"}]', 50000.00, 8000.00, 0.00, 58000.00, 'pickup', 'kenya', 'mpesa', NULL, 'delivered', '2025-07-27 05:23:11', NULL, 3, NULL),
(4, '{\"firstName\":\"John\",\"lastName\":\"Kinuthia\",\"email\":\"kibukush@gmail.com\",\"phone\":\"0721442248\",\"address\":\"742 kikuyu\",\"city\":\"central\",\"postalCode\":\"00902\"}', '[{\"id\":6,\"name\":\"Fence\",\"price\":4500,\"quantity\":1,\"image\":\"uploads\\/IMG_1.jpg\",\"features\":[]},{\"id\":8,\"name\":\"Chain link\",\"price\":400,\"quantity\":1,\"image\":\"uploads\\/product_688dee51cadda3.19607444.jpg\",\"features\":[]},{\"id\":3,\"name\":\"Fence\",\"price\":50000,\"quantity\":1,\"image\":\"uploads\\/IMG_7.jpg\",\"features\":[]}]', 54900.00, 8784.00, 0.00, 63684.00, 'Standard Delivery', '742 kikuyu, central, 00902', 'Cash on Delivery', NULL, 'processing', '2025-08-03 11:25:52', NULL, 4, NULL),
(5, '{\"firstName\":\"Michael\",\"lastName\":\"Njuri\",\"email\":\"jowakielectrical@gmail.com\",\"phone\":\"0721442248\",\"address\":\"742 kikuyu\",\"city\":\"central\",\"postalCode\":\"00902\"}', '[{\"id\":3,\"name\":\"Fence\",\"price\":50000,\"quantity\":1,\"image\":\"uploads\\/IMG_7.jpg\",\"features\":[]}]', 50000.00, 8000.00, 0.00, 58000.00, 'Standard Delivery', '742 kikuyu, central, 00902', 'Cash on Delivery', NULL, 'processing', '2025-08-03 18:13:02', NULL, 1, '2025-08-08 07:12:54'),
(19, '{\"firstName\":\"Michael\",\"lastName\":\"Njuri\",\"email\":\"gamexprience2@gmail.com\",\"phone\":\"0721442248\",\"address\":\"742 kikuyu\",\"city\":\"Kikuyu\",\"postalCode\":\"00902\"}', '[{\"id\":3,\"name\":\"Electric Security Fence\",\"price\":50000,\"quantity\":1,\"image\":\"uploads\\/IMG_7.jpg\",\"features\":[]},{\"id\":6,\"name\":\"CCTV Security Camera System\",\"price\":4500,\"quantity\":1,\"image\":\"uploads\\/IMG_1.jpg\",\"features\":[]}]', 54500.00, 10355.00, 550.00, 65405.00, 'express', '742 kikuyu, Kikuyu, 00902', 'mpesa', NULL, 'shipped', '2025-08-10 12:35:21', NULL, 5, '2025-08-10 17:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`) VALUES
(1, 2, 3, 'Fence', 1, 50000.00),
(2, 3, 3, 'Fence', 1, 50000.00),
(3, 4, 6, NULL, 1, 4500.00),
(4, 4, 8, NULL, 1, 400.00),
(5, 4, 3, NULL, 1, 50000.00),
(6, 5, 3, NULL, 1, 50000.00),
(20, 19, 3, NULL, 1, 50000.00),
(21, 19, 6, NULL, 1, 4500.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_logs`
--

CREATE TABLE `order_status_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_logs`
--

INSERT INTO `order_status_logs` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`) VALUES
(1, 4, 'processing', '', 'admin', '2025-08-03 19:25:51'),
(2, 5, 'processing', 'Order confirmed and moved to processing', 'admin', '2025-08-04 08:33:54');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10,
  `description` text DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `warranty_months` int(11) DEFAULT NULL,
  `image_paths` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `brand`, `price`, `discount_price`, `stock`, `low_stock_threshold`, `description`, `specifications`, `weight_kg`, `warranty_months`, `image_paths`, `is_featured`, `is_active`, `created_at`) VALUES
(3, 'Electric Security Fence', 'FENCE', 'Jowaki Security', 50000.00, 0.00, 19, 10, 'Advanced electric security fence system with high-voltage protection. Features motion sensors, alarm integration, and remote monitoring capabilities. Ideal for perimeter security and property protection.', '{\"Voltage\":\"8,000V\",\"Range\":\"Up to 1000 meters\",\"Power Source\":\"Solar/Battery backup\",\"Alarm Integration\":\"Yes\",\"Remote Monitoring\":\"Mobile app compatible\",\"Weather Resistance\":\"IP65 rated\"}', 0.00, 36, 'uploads/IMG_7.jpg', 1, 1, '2025-07-13 14:33:45'),
(6, 'CCTV Security Camera System', 'CAMERA', 'Jowaki Security', 5000.00, 4500.00, 20, 10, 'Professional CCTV camera system with night vision, motion detection, and remote viewing. Includes 4K cameras, DVR recording, and mobile app access. Perfect for home and business security.', '{\"Resolution\":\"4K Ultra HD\",\"Night Vision\":\"Up to 100ft\",\"Motion Detection\":\"Yes\",\"Storage\":\"1TB DVR included\",\"Remote Access\":\"Mobile app\",\"Weatherproof\":\"IP66 rated\"}', 0.00, 24, 'uploads/IMG_1.jpg', 1, 1, '2025-07-16 19:07:21'),
(8, 'Chain Link Fencing System', 'FENCE', 'Jowaki Security', 5000.00, 400.00, 20, 10, 'High-quality chain link fencing system for security and boundary protection. Features galvanized steel construction, weather-resistant coating, and easy installation. Perfect for residential, commercial, and industrial applications.', '{\"Material\":\"Galvanized Steel\",\"Height\":\"6 feet\",\"Mesh Size\":\"2 inch\",\"Wire Gauge\":\"11 gauge\",\"Coating\":\"Weather-resistant\",\"Installation\":\"Easy setup with included hardware\"}', 49.88, 24, 'uploads/product_688dee51cadda3.19607444.jpg', 0, 1, '2025-08-02 10:54:09');

-- --------------------------------------------------------

--
-- Table structure for table `stock_alerts`
--

CREATE TABLE `stock_alerts` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `alert_type` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_alerts`
--

INSERT INTO `stock_alerts` (`id`, `product_id`, `alert_type`, `message`, `created_at`) VALUES
(1, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:25:22'),
(2, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:25:25'),
(3, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:25:53'),
(4, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:25:54'),
(5, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:25:54'),
(6, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:25:55'),
(7, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:25:55'),
(8, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:26:01'),
(9, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:26:02'),
(10, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:27:56'),
(11, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:27:57'),
(12, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:27:57'),
(13, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:27:58'),
(14, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:30:34'),
(15, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 10:39:58'),
(16, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 11:25:04'),
(17, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 11:25:05'),
(18, 8, 'out_of_stock', 'Product Chain link is out of stock', '2025-08-03 12:50:23');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `old_stock` int(11) DEFAULT NULL,
  `new_stock` int(11) DEFAULT NULL,
  `quantity_changed` int(11) DEFAULT NULL,
  `operation` varchar(20) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `old_stock`, `new_stock`, `quantity_changed`, `operation`, `reason`, `updated_by`, `created_at`) VALUES
(1, 8, 30, 0, 30, 'set', '0', 'admin', '2025-08-03 10:25:22'),
(2, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:25:24'),
(3, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:25:53'),
(4, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:25:54'),
(5, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:25:54'),
(6, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:25:55'),
(7, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:25:55'),
(8, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:26:01'),
(9, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:26:02'),
(10, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:27:56'),
(11, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:27:57'),
(12, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:27:57'),
(13, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:27:58'),
(14, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:30:34'),
(15, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 10:39:58'),
(16, 8, 90, 0, 90, 'set', '0', 'admin', '2025-08-03 11:25:04'),
(17, 8, 0, 0, 0, 'set', '0', 'admin', '2025-08-03 11:25:05'),
(18, 8, 40, 0, 40, 'set', '0', 'admin', '2025-08-03 12:50:23');

-- --------------------------------------------------------

--
-- Table structure for table `store_categories`
--

CREATE TABLE `store_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `image_url` text DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT 'fas fa-box',
  `icon` varchar(100) NOT NULL,
  `filter_value` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store_categories`
--

INSERT INTO `store_categories` (`id`, `name`, `display_name`, `image_url`, `icon_class`, `icon`, `filter_value`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(46, 'ACCESS', 'Access Control', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-key', '', 'ACCESS', 1, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(47, 'CAMERA', 'CCTV Cameras', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-video', '', 'CAMERA', 2, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(48, 'FENCE', 'Electric Fencing', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-shield-alt', '', 'FENCE', 3, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(49, 'FIRE', 'Fire Systems', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-fire-extinguisher', '', 'FIRE', 4, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(50, 'GATE', 'Automated Gates', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-door-open', '', 'GATE', 5, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(51, 'ALARM', 'Alarm Systems', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-bell', '', 'alarm', 6, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(52, 'BATTERY', 'Batteries', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-battery-full', '', 'BATTERY', 7, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(53, 'CABLE', 'Cables', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-plug', '', 'CABLE', 8, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(54, 'DETECTOR', 'Detectors', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-search', '', 'DETECTOR', 9, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(55, 'PSU', 'Power Supplies', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-bolt', '', 'PSU', 10, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(56, 'PANEL', 'Control Panels', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-cogs', '', 'PANEL', 11, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(57, 'SIREN', 'Sirens', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-volume-up', '', 'SIREN', 12, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(58, 'STROBE', 'Strobe Lights', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-lightbulb', '', 'STROBE', 13, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(59, 'BUTTON', 'Buttons & Switches', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-hand-pointer', '', 'BUTTON', 14, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(60, 'CARD', 'Cards & Readers', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-credit-card', '', 'CARD', 15, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(61, 'ADAPTER', 'Adapters', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-exchange-alt', '', 'ADAPTER', 16, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(62, 'NETWORK', 'Network Equipment', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-network-wired', '', 'NETWORK', 17, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(63, 'PHONE', 'Video Phones', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-phone', '', 'PHONE', 18, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(64, 'BREAKGLASS', 'Break Glass', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-exclamation-triangle', '', 'BREAKGLASS', 19, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(65, 'KEYSWITCH', 'Key Switches', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-key', '', 'KEYSWITCH', 20, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(66, 'LADDER', 'Ladders', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-climbing', '', 'LADDER', 21, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(67, 'MAGNET', 'Magnets', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-magnet', '', 'MAGNET', 22, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(68, 'SWITCHES', 'Switches', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-toggle-on', '', 'SWITCHES', 23, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(69, 'SENSOR', 'Sensors', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-radar', '', 'SENSOR', 24, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(70, 'GUARD', 'Guard Tour', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-user-shield', '', 'GUARD', 25, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(71, 'GENERALS', 'General Items', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-tools', '', 'GENERALS', 26, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(72, 'SERVICES', 'Services', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-concierge-bell', '', 'SERVICES', 27, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(73, 'Repairs', 'Repairs', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-wrench', '', 'Repairs', 28, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(74, 'TV', 'TVs & Monitors', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-tv', '', 'TV', 29, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(75, 'VIDEO PHONE', 'Video Phones', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-video', '', 'VIDEO PHONE', 30, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(76, 'HIK', 'Hikvision', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-camera', '', 'HIK', 31, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(77, 'TIANDY', 'Tiandy', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-camera', '', 'TIANDY', 32, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(78, 'SHERLOTRONICS', 'Sherlotronics', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-broadcast-tower', '', 'SHERLOTRONICS', 33, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(79, 'GARRET', 'Garrett', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-search', '', 'GARRET', 34, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(80, 'Readers', 'Readers', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-id-card', '', 'Readers', 35, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(81, 'ENERGIZER', 'Energizers', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-bolt', '', 'ENERGIZER', 36, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(82, 'BALANCES', 'Balances', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-balance-scale', '', 'BALANCES', 37, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(83, 'OTHERS', 'Other Items', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&h=200&fit=crop&crop=center', 'fas fa-box', '', 'OTHERS', 38, 1, '2025-08-07 08:32:40', '2025-08-07 08:32:40'),
(84, 'CCTV', 'CCTV', 'Uploads/categories/cctv_1754572180.jpg', 'fas fa-box', '', '1', 1, 1, '2025-08-07 13:09:40', '2025-08-07 13:09:40');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'enable_login_notifications', '1', '2025-08-03 10:12:39', '2025-08-03 10:12:39'),
(2, 'enable_audit_log', '1', '2025-08-03 10:12:39', '2025-08-03 10:12:39'),
(5, 'tax_rate', '19', '2025-08-03 10:27:16', '2025-08-05 17:41:39'),
(6, 'standard_delivery_fee', '0', '2025-08-03 10:27:16', '2025-08-03 10:27:16'),
(7, 'express_delivery_fee', '550', '2025-08-03 10:27:16', '2025-08-05 17:01:20'),
(8, 'store_name', 'Jowaki Electrical Services', '2025-08-03 10:27:16', '2025-08-03 10:27:16'),
(9, 'store_email', 'jowakielectricalsrvs@gmail.com', '2025-08-03 10:27:16', '2025-08-05 17:41:39'),
(10, 'store_phone', '+254721442248', '2025-08-03 10:27:16', '2025-08-05 15:45:46'),
(11, 'store_address', 'Gaborone Road,Nairobi', '2025-08-03 10:27:16', '2025-08-05 17:41:39'),
(29, 'enable_mpesa', '1', '2025-08-05 15:37:04', '2025-08-05 15:37:04'),
(30, 'mpesa_business_number', '0743125249', '2025-08-05 15:37:04', '2025-08-05 15:37:04'),
(31, 'enable_card', '1', '2025-08-05 15:37:04', '2025-08-05 15:37:04'),
(32, 'enable_whatsapp', '1', '2025-08-05 15:37:04', '2025-08-05 15:37:04'),
(33, 'whatsapp_number', '0743125249', '2025-08-05 15:37:04', '2025-08-05 15:37:04'),
(34, 'enable_standard_delivery', '1', '2025-08-05 15:37:27', '2025-08-05 15:37:27'),
(35, 'standard_delivery_time', '3-5 business days', '2025-08-05 15:37:27', '2025-08-05 15:37:27'),
(36, 'enable_express_delivery', '1', '2025-08-05 15:37:27', '2025-08-05 15:37:27'),
(37, 'express_delivery_time', '1-2 business days', '2025-08-05 15:37:27', '2025-08-05 15:37:27'),
(38, 'enable_pickup', '1', '2025-08-05 15:37:27', '2025-08-05 15:37:27'),
(39, 'pickup_location', 'jowakielectricalsrvs@gmail.com\nGaborone Road,Nairobi\nOne stop shop for cctv,solar and more', '2025-08-05 15:37:27', '2025-08-05 15:37:27'),
(82, 'enable_2fa', '0', '2025-08-10 16:56:48', '2025-08-10 16:56:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `google_id`, `phone`, `password`, `created_at`, `address`, `city`, `postal_code`) VALUES
(1, 'Michael', 'Njuri', 'jowakielectrical@gmail.com', NULL, '0721442248', '$2y$10$wfS2W3TncGPDxz52Q2xtw.xNXWgNWx53TAYG1IjVjgE8y3cGSvppm', '2025-07-13 19:16:08', '742 kikuyu', 'central', NULL),
(2, 'John', 'Doe', 'michaelnjuri4@gmail.com', NULL, '0758744739', '$2y$10$taB9Q0GJfDSs7C.Vill1E.OcV1h1HvCRH7XMAoGhxY7aQpqAGENpm', '2025-07-14 17:47:43', 'good', 'here', '00902'),
(3, 'Michael', 'Kinuthia', 'michaelnjuri4@gmail.comTesting2', NULL, '0758744739', NULL, '2025-07-27 05:23:11', 'kenya', 'kikuyu', '00902'),
(4, 'John', 'Doe', 'kibukush@gmail.com', NULL, '0721442248', '$2y$10$dL79TMWQDho8F/46IqZ3S.AaLEWD4cKr7BrP.QHjWCWndZ8e252Bi', '2025-08-03 14:17:04', '742 kikuyu', 'central', '00902'),
(5, 'Michael', 'Njuri', 'gamexprience2@gmail.com', '114846474928603786034', NULL, NULL, '2025-08-10 14:30:12', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `admin_activity`
--
ALTER TABLE `admin_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_permission` (`admin_id`,`permission`),
  ADD KEY `granted_by` (`granted_by`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_permission` (`permission`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `carousel_banners`
--
ALTER TABLE `carousel_banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_status_logs`
--
ALTER TABLE `order_status_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store_categories`
--
ALTER TABLE `store_categories`
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
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_activity`
--
ALTER TABLE `admin_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `carousel_banners`
--
ALTER TABLE `carousel_banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_status_logs`
--
ALTER TABLE `order_status_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `store_categories`
--
ALTER TABLE `store_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_permissions_ibfk_2` FOREIGN KEY (`granted_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD CONSTRAINT `admin_sessions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD CONSTRAINT `admin_users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`),
  ADD CONSTRAINT `admin_users_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
