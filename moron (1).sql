-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2025 at 02:47 PM
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
-- Database: `moron`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Aneri', 'aneripatel2502@gmail.com', '2025-08-14 08:20:39', '2025-08-14 08:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `image` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_by` varchar(191) NOT NULL,
  `updated_by` varchar(191) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(2, 'Test1', 'uploads/img_68dd03191fa0f.jpeg', 1, 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', '2025-09-30 18:51:20', '2025-10-01 16:01:53'),
(3, 'Test', 'uploads/img_68dd02fca21fa.jpeg', 1, 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', '2025-09-30 19:00:51', '2025-10-01 16:01:24'),
(5, 'Test2', 'uploads/img_68dd02d8027a3.jpeg', 1, 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', '2025-10-01 16:00:03', '2025-10-01 16:00:48');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `company` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `product_type` varchar(100) NOT NULL,
  `role` varchar(191) NOT NULL,
  `file` varchar(191) NOT NULL,
  `is_read` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `phone`, `email`, `type`, `message`, `company`, `location`, `product_type`, `role`, `file`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 'Test', '8956231245', 'test@gmail.com', 'test', 'This is test message from test.', '', '', '', '', '', 1, '2025-09-18 16:28:38', '2025-09-22 12:42:15'),
(2, 'Test', '8956231245', 'test@gmail.com', 'test', 'This is test message from test.', '', '', '', '', '', 0, '2025-09-18 16:33:27', '2025-09-22 12:40:44'),
(3, 'Roary Dyer', '8523012453', 'buzynolim@mailinator.com', 'Sales', 'Fugiat dolore volupt', '', '', '', '', '', 0, '2025-09-18 18:45:55', '0000-00-00 00:00:00'),
(4, 'Natalie Albert', '8569320124', 'qunuwojog@mailinator.com', 'General', 'Sed non at aliqua O', '', '', '', '', '', 0, '2025-09-19 12:07:50', '0000-00-00 00:00:00'),
(5, 'Kylynn Snider', '7854120210', 'lecipyloja@mailinator.com', 'Sales', 'Dolores iste aliquip', '', '', '', '', '', 0, '2025-09-19 12:38:54', '2025-09-22 12:42:30'),
(6, 'Gareth Gardner', '8541203025', 'wocy@mailinator.com', 'Feedback', 'Et sint in reprehend', '', '', '', '', '', 0, '2025-09-19 12:42:31', '0000-00-00 00:00:00'),
(9, 'Test', '8956231245', 'test@gmail.com', 'test', 'This is test message from test.', '', '', '', '', '', 1, '2025-09-22 12:26:26', '2025-09-22 12:28:19'),
(10, 'Test2', '9856321478', 'test@test.com', '', 'This is test message.', '', '', '', '', '', 0, '2025-09-30 15:01:56', '0000-00-00 00:00:00'),
(11, 'Test Dealer', '8523698526', 'testdealer@test.com', 'Dealer', 'This is  test dealer message.', 'Test Company', 'Test Location', '', '', '', 0, '2025-09-30 15:19:09', '2025-09-30 15:57:29'),
(15, 'Test Vendor', '8569856985', 'testvendor@test.com', 'Vendor', 'This is test vendor message.', 'Test Company Vender', '', 'Test Type', '', '', 0, '2025-09-30 15:31:20', '2025-09-30 16:01:45'),
(16, 'Ralph Ellis', '8523698569', 'kufedo@mailinator.com', 'Vendor', 'Fugiat voluptatem e', 'Daniels Francis Inc', '', 'Illum dolor molesti', '', '', 1, '2025-09-30 15:31:34', '2025-09-30 16:01:49'),
(17, 'Jelani Hood', '8754215263', 'vygedijyd@mailinator.com', '0', 'Amet neque culpa o', '', '', '', 'Lorem quia placeat ', 'uploads/file_68e4bd0004d1c.pdf', 0, '2025-10-07 12:40:56', '0000-00-00 00:00:00'),
(18, 'Jelani Hood', '8754215263', 'vygedijyd@mailinator.com', '0', 'Amet neque culpa o', '', '', '', 'Lorem quia placeat ', 'uploads/file_68e4bd0758781.pdf', 0, '2025-10-07 12:41:03', '0000-00-00 00:00:00'),
(19, 'Hope Oneal', '8523698523', 'nimobu@mailinator.com', 'Career', 'Nostrud voluptates v', '', '', '', 'Recusandae Reiciend', 'uploads/file_68e4bfbac4ba0.pdf', 0, '2025-10-07 12:52:34', '2025-10-07 13:45:41'),
(20, 'Jelani Hood', '8754215263', 'vygedijyd@mailinator.com', 'Career', 'Amet neque culpa o', '', '', '', 'Lorem quia placeat ', 'uploads/file_68e4dbb1f3430.pdf', 0, '2025-10-07 14:51:54', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `advantages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`advantages`)),
  `technical_specifications` longtext DEFAULT NULL,
  `special_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`special_features`)),
  `created_by` varchar(255) NOT NULL,
  `updated_by` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `subcategory_id`, `name`, `images`, `advantages`, `technical_specifications`, `special_features`, `created_by`, `updated_by`, `status`, `created_at`, `updated_at`) VALUES
(10, 2, 4, 'Captain', '[\"uploads\\/img_68da5a4d8b338.jpeg\",\"uploads\\/img_68da5a4d8b63c.jpeg\"]', '[\"High Efficiency\",\"Low Maintenance\"]', '{\"model\":\"Captain\",\"variant\":[\"5 feet\",\"5.5 feet\",\"6 feet\",\"6 feet\"],\"tractor_power\":[\"35-40\",\"40-45\",\"45-50\",\"45-50\"],\"overall_width\":[\"1766 / 5.80\",\"1923 / 6.30\",\"2018 / 6.62\",\"2018 / 6.62\"],\"working_width\":[\"1516 / 5.00\",\"1673 / 5.50\",\"1828 / 6.00\",\"1828 / 6.00\"],\"max_working_depth\":\"8 inch\",\"pto_speed\":\"540/1000\",\"no_of_flanges\":[6,7,7,7],\"no_of_blade\":[36,42,42,42],\"transmission_type\":\"GEAR DRIVE\",\"standard_gear_pair\":\"16-19@540 & 17-18@540\"}', '[\"Durable Build\"]', 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', 1, '2025-09-24 11:47:32', '2025-10-03 18:20:01'),
(20, 2, 6, 'Necessitatibus illum', '[\"uploads\\/img_68dd18c842bcc.jpeg\"]', '[\"Neque et et ipsum c\"]', '{\"model\":\"Laborum Et cupidita\",\"variant\":[\"Voluptatem qui alias\",\"Irure magnam volupta\"],\"tractor_power\":[\"Minima voluptate vol\",\"Tempor aliqua Quis \"],\"overall_width\":[\"Nulla amet placeat\",\"Et est aperiam quod \"],\"working_width\":[\"Ipsa consectetur do\",\"Id voluptatum lorem\"],\"max_working_depth\":\"Suscipit iure conseq\",\"pto_speed\":\"Quae et qui doloribu\",\"no_of_flanges\":[\"Dolores est reprehen\",\"Vel dolore dolorum p\"],\"no_of_blade\":[\"Exercitationem non s\",\"Ipsam aspernatur nos\"],\"transmission_type\":\"Temporibus et error \",\"standard_gear_pair\":\"Aliquip explicabo N\"}', '[\"Consequatur Labore \"]', '0', 'aneripatel2502@gmail.com', 1, '2025-10-01 17:34:24', '2025-10-03 18:20:59'),
(21, 5, 0, 'Error dolores dicta ', '[\"uploads\\/img_68dd2478a3dcc.jpeg\"]', '[\"Accusantium architec\"]', '{\"model\":\"Qui sequi hic neque \",\"variant\":[\"Exercitationem simil\"],\"tractor_power\":[\"Aut do et qui enim p\"],\"overall_width\":[\"Elit tempore imped\"],\"working_width\":[\"Et illum atque sunt\"],\"max_working_depth\":\"Eaque similique proi\",\"pto_speed\":\"Ad minus labore itaq\",\"no_of_flanges\":[\"Dolorum sunt aliqua\"],\"no_of_blade\":[\"Asperiores soluta pr\"],\"transmission_type\":\"Dolor excepteur at o\",\"standard_gear_pair\":\"Tempore quaerat dol\"}', '[\"Et voluptatem Alias\"]', 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', 1, '2025-10-01 17:39:55', '2025-10-03 18:21:23'),
(22, 3, 8, 'Est laborum Dolor ', '[\"uploads\\/img_68df9246ed5e5.png\"]', '[\"Illum quo quos aut \"]', '{\"model\":\"Iure dignissimos rer\",\"variant\":[\"Doloribus qui laudan\",\"Harum tenetur fuga \"],\"tractor_power\":[\"Id quis consequatur\",\"Dicta tempora nisi a\"],\"overall_width\":[\"Accusantium sint ips\",\"Molestiae vitae ut e\"],\"working_width\":[\"Nihil eu dolor enim \",\"Accusamus ullam veli\"],\"max_working_depth\":\"Dolor incididunt vol\",\"pto_speed\":\"Odio non qui ea aute\",\"no_of_flanges\":[\"Et non labore quis p\",\"Et hic amet qui odi\"],\"no_of_blade\":[\"Nesciunt distinctio\",\"Cum et cumque consec\"],\"transmission_type\":\"Aut laboris quidem u\",\"standard_gear_pair\":\"Repudiandae fugiat \"}', '[\"Cillum laborum solut\"]', 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', 1, '2025-10-03 14:37:18', '2025-10-03 14:37:18'),
(23, 3, 8, 'Necessitatibus illum', '[\"uploads\\/img_68dfc01eb23b7.jpeg\",\"uploads\\/img_68dfc01eb28f4.jpeg\",\"uploads\\/img_68dfc01eb29f0.jpeg\"]', '[\"Neque et et ipsum c\"]', '{\"model\":\"Laborum Et cupidita\",\"variant\":[\"Voluptatem qui alias\",\"Irure magnam volupta\"],\"tractor_power\":[\"Minima voluptate vol\",\"Tempor aliqua Quis \"],\"overall_width\":[\"Nulla amet placeat\",\"Et est aperiam quod \"],\"working_width\":[\"Ipsa consectetur do\",\"Id voluptatum lorem\"],\"max_working_depth\":\"Suscipit iure conseq\",\"pto_speed\":\"Quae et qui doloribu\",\"no_of_flanges\":[\"Dolores est reprehen\",\"Vel dolore dolorum p\"],\"no_of_blade\":[\"Exercitationem non s\",\"Ipsam aspernatur nos\"],\"transmission_type\":\"Temporibus et error \",\"standard_gear_pair\":\"Aliquip explicabo N\"}', '[\"Consequatur Labore \"]', 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', 1, '2025-10-03 17:52:54', '2025-10-03 17:52:54');

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

CREATE TABLE `sub_categories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `image` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_by` varchar(191) NOT NULL,
  `updated_by` varchar(191) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `image`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(4, 2, 'test', 'uploads/img_68dcfe1e0e056.jpeg', 1, 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', '2025-09-30 20:48:03', '2025-10-01 15:40:38'),
(6, 2, 'Test Sub', 'uploads/img_68dcfc5803bb8.jpeg', 1, 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', '2025-10-01 15:00:04', '2025-10-01 15:33:04'),
(7, 2, 'Sub Test ', 'uploads/img_68dcfd6bdcea4.jpg', 1, 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', '2025-10-01 15:37:39', '0000-00-00 00:00:00'),
(8, 3, 'SubCategory Test', 'uploads/img_68dcfe0d7053d.jpeg', 1, 'aneripatel2502@gmail.com', 'aneripatel2502@gmail.com', '2025-10-01 15:40:21', '0000-00-00 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `otp`
--
ALTER TABLE `otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
