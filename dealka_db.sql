-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 08, 2026 at 04:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dealka_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `central_account`
--

CREATE TABLE `central_account` (
  `id` int(11) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `qr_image_path` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `central_account`
--

INSERT INTO `central_account` (`id`, `account_name`, `account_number`, `bank_name`, `qr_image_path`, `status`, `created_at`) VALUES
(1, 'Dealka Official', '0123456789', 'BCEL', 'assets/images/my_qr.png', 'active', '2026-02-04 16:02:18');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `description`, `table_name`, `record_id`, `old_value`, `new_value`, `ip_address`, `created_at`) VALUES
(1, 1, 'register', 'User registered: ter', NULL, NULL, NULL, NULL, '::1', '2026-02-01 15:14:23'),
(2, 1, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-01 15:14:55'),
(3, 1, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-02 13:24:01'),
(4, 1, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-02 13:24:11'),
(5, 1, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-02 13:29:39'),
(6, 2, 'register', 'User registered: admin', NULL, NULL, NULL, NULL, '::1', '2026-02-02 13:30:47'),
(7, 2, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-02 13:31:05'),
(8, 2, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-02 13:39:16'),
(9, NULL, 'login_failed', 'Failed login attempt: ter', NULL, NULL, NULL, NULL, '::1', '2026-02-02 14:38:10'),
(10, 1, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-02 14:38:25'),
(11, 1, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-04 16:28:14'),
(12, 1, 'add_product', 'Product added: ff', 'products', 1, NULL, NULL, '::1', '2026-02-04 16:55:32'),
(13, 1, 'approve_product', 'Product approved: 1', 'products', 1, NULL, NULL, '::1', '2026-02-04 16:56:54'),
(14, 1, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-04 16:58:08'),
(15, 2, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-04 16:58:51'),
(16, 2, 'create_order', 'Order created: ORD20260204175933900D76', 'orders', 1, NULL, NULL, '::1', '2026-02-04 16:59:33'),
(17, 2, 'upload_slip', 'Slip uploaded for order: ORD20260204175933900D76', 'payments', 1, NULL, NULL, '::1', '2026-02-04 17:02:15'),
(18, 2, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-04 17:03:04'),
(19, NULL, 'login_failed', 'Failed login attempt: ter', NULL, NULL, NULL, NULL, '::1', '2026-02-04 17:03:13'),
(20, 1, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-04 17:03:21'),
(21, 1, 'approve_payment', 'Payment approved: 1', 'payments', 1, NULL, NULL, '::1', '2026-02-04 17:04:13'),
(22, 1, 'mark_shipped', 'Order marked as shipped: ORD20260204175933900D76', 'orders', 1, NULL, NULL, '::1', '2026-02-04 17:04:58'),
(23, 1, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-04 17:05:57'),
(24, 2, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-04 17:06:10'),
(25, 2, 'mark_received', 'Order marked as received: ORD20260204175933900D76', 'orders', 1, NULL, NULL, '::1', '2026-02-04 17:06:33'),
(26, 2, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-04 17:07:01'),
(27, 1, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-04 17:07:11'),
(28, 1, 'edit_product', 'Product updated: gg', 'products', 1, NULL, NULL, '::1', '2026-02-05 14:43:12'),
(29, 1, 'edit_product', 'Product updated: gg', 'products', 1, NULL, NULL, '::1', '2026-02-05 14:43:16'),
(30, 1, 'add_product', 'Product added: dee', 'products', 2, NULL, NULL, '::1', '2026-02-05 15:23:08'),
(31, 1, 'add_product', 'Product added: dee', 'products', 3, NULL, NULL, '::1', '2026-02-05 15:23:58'),
(32, 1, 'request_withdrawal', 'Withdrawal requested: 40,000.00 LAK', 'withdrawals', 0, NULL, NULL, '::1', '2026-02-05 15:25:32'),
(33, 1, 'approve_withdrawal', 'Withdrawal approved: 1 - Amount: 39000.00', 'withdrawals', 1, NULL, NULL, '::1', '2026-02-05 15:26:28'),
(34, 1, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-05 15:27:51'),
(35, 1, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-05 15:28:08'),
(36, 1, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-05 15:30:29'),
(37, 2, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-05 15:30:43'),
(38, 2, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-05 16:28:08'),
(39, 2, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-05 16:28:23'),
(40, 2, 'add_product', 'Product added: admin', 'products', 4, NULL, NULL, '::1', '2026-02-05 16:30:08'),
(41, 2, 'approve_product', 'Product approved: 2', 'products', 2, NULL, NULL, '::1', '2026-02-05 16:30:47'),
(42, 2, 'reject_product', 'Product rejected: 3 - Reason: no', 'products', 3, NULL, NULL, '::1', '2026-02-05 16:31:03'),
(43, 2, 'approve_product', 'Product approved: 4', 'products', 4, NULL, NULL, '::1', '2026-02-05 16:31:05'),
(44, 2, 'reject_product', 'Product rejected: 3 - Reason: no', 'products', 3, NULL, NULL, '::1', '2026-02-05 16:31:18'),
(45, 2, 'create_order', 'Order created: ORD20260205173221398990', 'orders', 2, NULL, NULL, '::1', '2026-02-05 16:32:21'),
(46, 2, 'upload_slip', 'Slip uploaded for order: ORD20260205173221398990', 'payments', 2, NULL, NULL, '::1', '2026-02-05 16:33:19'),
(47, 2, 'approve_payment', 'Payment approved: 2', 'payments', 2, NULL, NULL, '::1', '2026-02-05 16:33:48'),
(48, 2, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-05 16:33:53'),
(49, 1, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-05 16:34:02'),
(50, 1, 'mark_shipped', 'Order marked as shipped: ORD20260205173221398990', 'orders', 2, NULL, NULL, '::1', '2026-02-05 16:34:33'),
(51, 1, 'delete_product', 'Product deleted: dee', 'products', 3, NULL, NULL, '::1', '2026-02-06 16:21:32'),
(52, 1, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-06 16:58:29'),
(53, NULL, 'login_failed', 'Failed login attempt: ter', NULL, NULL, NULL, NULL, '::1', '2026-02-06 17:25:01'),
(54, 1, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-06 17:25:12'),
(55, 1, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-07 13:42:27'),
(56, 2, 'login', 'User logged in', NULL, NULL, NULL, NULL, '::1', '2026-02-07 14:50:34'),
(57, 3, 'logout', 'User logged out', NULL, NULL, NULL, NULL, '::1', '2026-02-08 15:26:49');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(50) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `fee` decimal(12,2) NOT NULL,
  `net_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_address_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `buyer_id`, `seller_id`, `product_id`, `price`, `fee`, `net_amount`, `status`, `created_at`, `shipping_address_id`) VALUES
(1, 'ORD20260204175933900D76', 2, 1, 1, 100000.00, 3000.00, 97000.00, 'completed', '2026-02-04 16:59:33', 1),
(2, 'ORD20260205173221398990', 2, 1, 2, 1000.00, 30.00, 970.00, 'shipped', '2026-02-05 16:32:21', 2);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `slip_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejected_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `user_id`, `amount`, `slip_path`, `status`, `created_at`, `rejected_reason`) VALUES
(1, 1, 2, 100000.00, 'file_1770224535_8d8b819ac53ac5cc.jpg', 'approved', '2026-02-04 17:02:15', NULL),
(2, 2, 2, 1000.00, 'file_1770309199_c9a0e9d9e22523db.jpg', 'approved', '2026-02-05 16:33:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','sold','hidden') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `user_id`, `title`, `description`, `price`, `category`, `image_path`, `status`, `created_at`) VALUES
(1, 1, 'gg', 'firefree', 100000.00, 'electronics', 'file_1770224132_b5aceb5d4897efe5.jpg', 'approved', '2026-02-04 16:55:32'),
(2, 1, 'dee', 'ดด', 1000.00, 'electronics', 'file_1770304988_6aa12c3904a4505c.jpg', 'approved', '2026-02-05 15:23:08'),
(4, 2, 'admin', 'deemak', 100000.00, 'electronics', 'file_1770309008_9a4fedbe94682bba.jpg', 'approved', '2026-02-05 16:30:08');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_text` text NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_addresses`
--

INSERT INTO `shipping_addresses` (`id`, `order_id`, `full_name`, `phone`, `address_text`, `note`, `created_at`) VALUES
(1, 1, 'PeeTer', '02057860411', 'Nhommalath', '', '2026-02-04 16:59:33'),
(2, 2, 'PeeTer', '02057860411', 'Nhommalath', '', '2026-02-05 16:32:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `balance` decimal(12,2) DEFAULT 0.00,
  `status` enum('active','banned') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pending_withdrawal` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `fee` decimal(12,2) NOT NULL,
  `net_amount` decimal(12,2) NOT NULL,
  `method` varchar(50) DEFAULT 'BCEL ONE',
  `account_info` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejected_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `withdrawals`
--

INSERT INTO `withdrawals` (`id`, `user_id`, `amount`, `fee`, `net_amount`, `method`, `account_info`, `status`, `created_at`, `rejected_reason`) VALUES
(1, 1, 40000.00, 1000.00, 39000.00, 'BCEL ONE', '123456789', 'completed', '2026-02-05 15:25:32', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `central_account`
--
ALTER TABLE `central_account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `central_account`
--
ALTER TABLE `central_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
