-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 07:55 PM
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
-- Database: `capstone`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$Cv54UCOtycJI6dQ4JESiL.V6OWY0p.hCObZZIcUUX51nKZWGtqWai'),
(4, 'admin2', '$2y$10$pq/7AgwJcQbCT189vKn3dO.UCE5g8dEiPjhHg0Hz5.8NfADedARSS'),
(5, 'admin1', '$2y$10$in1bVFr.lUrOwEycVSp6eelo9ArgmV2TyD2G8WM7lwr9bcVz3P2c2');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_customized` tinyint(1) DEFAULT 0,
  `customized` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `category_id`, `is_customized`, `customized`) VALUES
(1, 'Wedding', 'Floral arrangements for weddings', NULL, 0, NULL),
(2, 'Birthday', 'Special birthday bouquet orders', NULL, 0, NULL),
(5, 'Sympathy Flowers', NULL, NULL, 0, NULL),
(7, 'Money bouquets', NULL, NULL, 0, NULL),
(8, 'Custom bundle', NULL, NULL, 0, NULL),
(9, 'Funeral', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `customer_address` text NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `order_message` text DEFAULT NULL,
  `proof_of_payment` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `delivery_service` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_name`, `customer_address`, `customer_email`, `customer_phone`, `order_message`, `proof_of_payment`, `payment_method`, `order_status`, `order_date`, `product_id`, `quantity`, `total_amount`, `product_name`, `price`, `expected_delivery_date`, `delivery_service`) VALUES
(1, 'diza toralde', 'bago city', 'dza@gmail.com', '93455555', 'rest in peace', 'uploads/fb.jpg', NULL, 'completed', '2025-02-04 21:34:02', 4, 1, NULL, 'funeral bouquet 1', 500.00, NULL, NULL),
(2, 'diza toralde', 'bago city', 'dza@gmail.com', '93455555', '', NULL, NULL, 'completed', '2025-04-20 14:23:12', 101, 14, 13986.00, 'Pink Moment', 999.00, '2025-04-23', 'j and t'),
(3, 'diza toralde', 'bago city', 'dza@gmail.com', '93455555', 'wala', 'uploads/1745162095_6804d5c0055b1_sun.jpg', 'gcash', 'pending', '2025-04-20 15:14:55', 0, 1, 5057.00, '', NULL, '2025-04-22', 'j and t'),
(4, 'delia reyes', 'bago city', 'dza@gmail.com', '93455555', 'aaaaaaaaaaaa', NULL, 'cod', 'cancelled', '2025-02-20 15:24:59', 0, 1, 1235.00, '', NULL, '2025-04-25', 'j and t'),
(5, 'delia reyes', 'bago city', 'dza@gmail.com', '93455555', '', 'uploads/1745163425_6804cc893d66b_67f7fc47a69a1_scik.jpg', 'gcash', 'approved', '2025-04-20 15:37:05', 0, 1, 1234.00, '', NULL, '0000-00-00', ''),
(6, 'delia reyes', 'bago city', 'dza@gmail.com', '93455555', '', NULL, 'cod', 'approved', '2025-04-20 15:44:42', 0, 1, 1234.00, '', NULL, '0000-00-00', ''),
(7, 'diza toralde', 'bago city', 'dza@gmail.com', '93455555', '', 'uploads/1745164366_6804cc893d66b_67f7fc47a69a1_scik.jpg', 'gcash', 'completed', '2025-04-20 15:52:46', 0, 1, 1234.00, '', NULL, '0000-00-00', ''),
(8, 'diza', 'brgy. 1 bago city', 'diza@gmail.com', '09123456782', 'namia gid', 'uploads/1745307604_joker.jpg', 'gcash', 'completed', '2025-04-22 07:40:04', 0, 1, 1234.00, '', NULL, '2025-04-22', 'lbc'),
(9, 'diza', 'bago city', 'diza@gmail.com', '09123456782', 'okay', NULL, 'cod', 'completed', '2025-04-21 08:36:25', 0, 1, 1234.00, '', NULL, '0000-00-00', ''),
(10, 'diza', 'bago', 'diza@gmail.com', '09123456782', '', NULL, 'cod', 'completed', '2025-03-22 08:39:07', 0, 1, 2468.00, '', NULL, '0000-00-00', ''),
(11, 'user1', 'la carlota city', 'user1@gmail.com', '091234567111', 'okay', NULL, 'gcash', 'pending', '2025-04-22 13:35:50', 0, 1, NULL, '', NULL, NULL, NULL),
(12, 'user1', 'la carlota city', 'user1@gmail.com', '091234567111', 'hahahha', 'uploads/1745329257_productChart.png', 'gcash', 'pending', '2025-04-22 13:40:57', 0, 1, NULL, '', NULL, NULL, NULL),
(13, 'user1', 'la carlota city', 'user1@gmail.com', '091234567111', 'hahahha', 'uploads/1745329600_productChart.png', 'gcash', 'pending', '2025-04-22 13:46:40', 0, 1, NULL, '', NULL, NULL, NULL),
(14, 'user1', 'la carlota city', 'user1@gmail.com', '091234567111', '', 'uploads/1745329979_productChart.png', 'gcash', 'completed', '2025-04-22 13:52:59', 0, 1, 120.00, '', NULL, '2025-04-23', 'lbc'),
(15, 'user1', 'lcc', 'user1@gmail.com', '09124537386', 'huo', 'uploads/1745389127_productChart.png', 'gcash', 'pending', '2025-04-23 06:18:47', 0, 1, 500.00, '', NULL, NULL, NULL),
(16, 'user1', 'lcc', 'user1@gmail.com', '09124537386', 'hahahahha', NULL, 'cod', 'approved', '2025-04-24 17:41:18', 0, 1, 2468.00, '', NULL, '2025-04-25', 'palawan'),
(17, 'user1', 'huhuhu', 'user1@gmail.com', '09124537386', 'tani okay na', NULL, 'cod', 'approved', '2025-04-25 01:16:31', 0, 1, 1024.00, '', NULL, '2025-04-25', 'palawan'),
(18, 'user1', 'lcc', 'user1@gmail.com', '09124537386', 'okay nagid ni', 'uploads/1745545322_Screenshot (45).png', 'gcash', 'completed', '2025-04-25 01:42:02', 0, 1, 5000.00, '', NULL, '2025-04-25', '4-25-2025 final test'),
(19, 'user1', 'lcc', 'user1@gmail.com', '09124537386', 'hahahha', NULL, 'cod', 'pending', '2025-04-25 10:56:59', 0, 1, 1234.00, '', NULL, NULL, NULL),
(20, 'user1', 'lcc', 'user1@gmail.com', '09124537386', 'okiiiiiiiiiiiiiiiiiiiiiii', NULL, 'cod', 'pending', '2025-04-25 11:01:28', 0, 1, 23567.50, '', NULL, NULL, NULL),
(21, 'user1', 'lcc', 'user1@gmail.com', '09124537386', 'okiiiiiiiiiiiiiiiiiiiiiii', NULL, 'cod', 'pending', '2025-04-25 11:03:34', 0, 1, 23567.50, '', NULL, NULL, NULL),
(22, 'user1', 'lcc', 'user1@gmail.com', '09124537386', 'hatdogieeeeeeeeeeeeeee', 'uploads/1745579141_Screenshot (11).png', 'gcash', 'pending', '2025-04-25 11:05:41', 0, 1, 23553.00, '', NULL, NULL, NULL),
(23, 'user1', 'lcccccccccccccccccccccccccccccccccccc', 'user1@gmail.com', '09124537386', 'checkout naaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', NULL, 'cod', 'pending', '2025-04-25 13:01:02', 0, 1, 248.00, '', NULL, NULL, NULL),
(24, 'user3', 'sumag', 'user3@gmail.com', '09615438465', 'checkbox test 2', 'uploads/1745586167_Screenshot 2025-03-05 184803.png', 'gcash', 'delivered', '2025-04-25 13:02:47', 0, 1, 23928.00, '', NULL, '2025-04-26', 'ninjavan'),
(25, 'grover boy', 'Hacienda Remedios Barangay Balabag La Carlota City Negros Occidental Philippines', 'grover@gmail.com', '09123456782', 'tani ma send ka update', NULL, 'cod', 'delivered', '2025-04-25 17:20:23', 0, 1, 1999998.00, '', NULL, '2025-05-01', 'sa may 1 lang noy');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `created_at`, `expires_at`, `used`) VALUES
(1, 'user1@gmail.com', '28e33849ba6258d3b5cdd8fa7e848074cfad49bf7829a1962ea7cafac7f1f1a5', '2025-04-25 17:46:06', '2025-04-25 12:46:06', 0),
(2, 'user1@gmail.com', 'fa77fedda0df187bb02fbd17bca663d23bd39e477af4a197d6b051c90d918c0e', '2025-04-25 17:55:17', '2025-04-25 12:55:17', 0),
(3, 'user1@gmail.com', '871043', '2025-04-25 18:00:24', '2025-04-25 13:00:24', 0),
(4, 'user1@gmail.com', '692171', '2025-04-25 18:03:21', '2025-04-25 13:03:21', 0),
(5, 'user1@gmail.com', '938736', '2025-04-25 18:03:36', '2025-04-25 13:03:36', 0),
(6, 'user1@gmail.com', '865402', '2025-04-25 18:05:55', '2025-04-25 13:05:55', 0),
(7, 'user1@gmail.com', '826636', '2025-04-25 18:33:05', '2025-04-25 13:33:05', 0),
(8, 'user1@gmail.com', '953694', '2025-04-25 18:40:11', '2025-04-25 13:40:11', 0),
(9, 'user1@gmail.com', '731076', '2025-04-25 18:42:48', '2025-04-25 13:42:48', 0),
(10, 'user1@gmail.com', '987259', '2025-04-25 18:44:13', '2025-04-25 13:44:13', 0),
(11, 'user1@gmail.com', '880970', '2025-04-25 18:47:05', '2025-04-25 13:47:05', 0),
(12, 'user1@gmail.com', '410885', '2025-04-25 18:50:35', '2025-04-25 13:50:35', 0),
(13, 'annanicoleermeo22@gmail.com', '554382', '2025-04-26 00:48:21', '2025-04-25 19:48:21', 0),
(14, 'user1@gmail.com', '720310', '2025-04-26 01:00:14', '2025-04-25 20:00:14', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) NOT NULL DEFAULT 'uploads/default_profile.png',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `address`, `phone`, `email`, `profile_image`) VALUES
(4, 'admin123', '$2y$10$Cv54UCOtycJI6dQ4JESiL.V6OWY0p.hCObZZIcUUX51nKZWGtqWai', '', '', '', NULL, 'uploads/default_profile.png'),
(5, 'diza', '$2y$10$I9KYYiNzymlGiOoJ598zvugT/DMTW5/Ki8m.LgbNvg6MgO6hTUoxK', 'diza toralde', 'bago city', '+63902938594', 'diza@gmail.com', 'uploads/profile_images/fff.jpg'),
(6, 'users', '$2y$10$yN/is73t0W.4qm7t4Hq.cOub4UQERuNEofoZHfT7BliZYZ.cC1l8.', '', '', '', NULL, 'uploads/default_profile.png'),
(7, 'diza11111111', '$2y$10$Wa1y0FFWG8JBmYR1rA6Miuiia3MR2j73cXr9JqmSGFNdvh2YnSqWy', 'diza toralde', 'bago city', '+63678905545', NULL, 'uploads/profile_images/sun.jpg'),
(9, 'user1', '$2y$10$T/r3nO31D84TPaQJf4WCGu0IVvHuKE15TpOOYilVGe5Snni6cSIYu', '', '', '', 'user1@gmail.com', 'uploads/default_profile.png'),
(11, '', '$2y$10$1piQkbvDwhmXskyDNyntgeytumILZuv69FaL8bn2nlBFiaYXwE.rC', '', '', '', NULL, 'uploads/default_profile.png'),
(12, 'user3', '$2y$10$QABsIWmG.nkhL54BSGYy/eJ8j4pv0OmrBeZEwyli/cK86bfxSzrj2', '', '', '', 'user3@gmail.com', 'uploads/default_profile.png'),
(13, 'Grover', '$2y$10$kre6ThcRYS7mYdOeaaOGUO6FtQdqM71Rp.zfoQ5Qdv9jPNk7QOAni', 'Grover Boy', 'Hacienda Remedios Barangay Balabag La Carlota City Negros Occidental Philippines', '09876543451', 'grover@gmail.com', 'uploads/profile_images/Screenshot (7).png');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `product_description` text,
  `product_image` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_customized` tinyint(1) DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `original_stock` int(11) DEFAULT 0,
  `stock_count` int(11) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `custom_options` json DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO products (
  product_id,
  product_name,
  product_description,
  product_image,
  category_id,
  is_customized,
  price,
  original_stock,
  stock_count,
  is_deleted,
  created_at,
  custom_options
) VALUES
(9, 'SUNFLOWER', 'Sunflowers (genus Helianthus) are tall, annual plants known for their large, daisy-like flower heads that often turn to face the sun, a phenomenon called heliotropism in young plants. Native to North America, they are widely cultivated for their edible seeds and the oil extracted from them. These cheerful flowers typically have bright yellow petals (ray florets) surrounding a central disk of florets that mature into seeds. Sunflowers can grow to impressive heights, sometimes reaching over 3 meters, and their seeds are a nutritious snack and a source of cooking oil. Beyond their practical uses, sunflowers are symbols of happiness, warmth, and longevity.', 'sun.jpg', 1, 0, 499.00, 0, 0, 0, '2025-04-17 06:11:00', NULL),
(10, 'SUNFLOWER', 'aaaaaaaaaaaaaaaa', 'roses.jpg', NULL, 0, 1234.00, 0, 0, 0, '2025-04-17 06:14:55', NULL),
(11, 'Funeral flowers', 'For funeral flower messages, consider simple yet meaningful phrases like "In loving memory," "Rest in peace," or "Gone but never forgotten." These phrases convey sympathy and respect, offering comfort to the grieving family. You can also personalize your message with a specific memory or sentiment related to the deceased. \r\nHere are some more ideas:\r\nSimple & Traditional:\r\nIn loving memory.\r\nForever in our thoughts.\r\nMay you rest in peace.\r\nLoved and remembered.\r\nGone but never forgotten.\r\nWith love and fond memories.\r\nAlways in my/our heart(s).\r\nSleep peacefully.\r\nRest in peace.\r\nAlways in our hearts.\r\nForever in our hearts.\r\nWith deepest sympathy.\r\nWith heartfelt condolences. \r\nMore Personal:\r\n"[Deceased''s Name] will truly be missed".\r\n"Remembering [Deceased''s Name]''s life with love".\r\n"[Deceased''s Name] will live on in our hearts".\r\n"May you rest peacefully, [Deceased''s Name]".\r\n"Thinking of you always, [Deceased''s Name]".\r\n"You were the most [positive adjective] person I/we knew".\r\n"You brought so much joy into our lives".\r\n"Thank you for everything, [Deceased''s Name]". \r\nAdditional Tips:\r\nConsider the relationship to the deceased: If you were a close friend or family member, you might include a more personal anecdote or memory. \r\nKeep it concise: Funeral flowers often have limited space for messages, so it''s best to choose a few impactful words. \r\nWrite from the heart: The most important thing is to express your sympathy and love.', 'fff.jpg', 0, 0, 0.00, 0, 0, 0, '2025-04-20 15:11:39', NULL),
(16, 'SUNFLOWER', '', 'fff.jpg', NULL, 0, 1234.00, 0, 0, 0, '2025-04-20 15:12:00', NULL),
(17, 'wwwww', 'Sunflowers (genus Helianthus) are tall, annual plants known for their large, daisy-like flower heads that often turn to face the sun, a phenomenon called heliotropism in young plants. Native to North America, they are widely cultivated for their edible seeds and the oil extracted from them. These cheerful flowers typically have bright yellow petals (ray florets) surrounding a central disk of florets that mature into seeds. Sunflowers can grow to impressive heights, sometimes reaching over 3 meters, and their seeds are a nutritious snack and a source of cooking oil. Beyond their practical uses, sunflowers are symbols of happiness, warmth, and longevity.', 'fb.jpg', 10, 0, 124.00, 0, 0, 0, '2025-04-19 06:18:21', NULL),
(18, 'tuilps', '', 'sun.jpg', NULL, 0, 60.00, 0, 0, 0, '2025-04-20 15:11:50', NULL),
(19, 'tuilps', 'a nice wow', 'sun.jpg', 0, 0, 60.00, 50, 9, 0, '2025-04-20 09:52:33', NULL),
(24, 'Funeral flowers', 'wwwwwwwwwww', '6804d5c0055b1_sun.jpg', 10, 0, 1234.00, 15, 5, 0, '2025-04-20 11:08:48', NULL),
(28, 'Roses', 'pula2 gid nisa', 'productChart.png', NULL, 0, 500.00, 20, 10, 0, '2025-04-23 06:10:13', NULL),
(29, 'hatdog', 'Customized product', 'default.jpg', NULL, 0, 23567.50, 0, 0, 0, '2025-04-25 11:03:34', NULL),
(30, 'hatdog', 'Customized product', 'default.jpg', NULL, 0, 23553.00, 0, 0, 0, '2025-04-25 11:05:41', NULL),
(31, 'hatdog', 'Customized product', 'default.jpg', NULL, 0, 23928.00, 0, 0, 0, '2025-04-25 13:02:47', NULL),
(32, 'rosepharmachyyyyyyyyy', 'De dónde viene?\r\nAl contrario del pensamiento popular, el texto de Lorem Ipsum no es simplemente texto aleatorio. Tiene sus raices en una pieza clásica de la literatura del Latin, que data del año 45 antes de Cristo, haciendo que este adquiera mas de 2000 años de antigüedad. Richard McClintock, un profesor de Latin de la Universidad de Hampden-Sydney en Virginia, encontró una de las palabras más oscuras de la lengua del latín, "consecteur", en un pasaje de Lorem Ipsum, y al seguir leyendo distintos textos del latín, descubrió la fuente indudable. Lorem Ipsum viene de las secciones 1.10.32 y 1.10.33 de "de Finnibus Bonorum et Malorum" (Los Extremos del Bien y El Mal) por Cicero, escrito en el año 45 antes de Cristo. Este libro es un tratado de teoría de éticas, muy popular durante el Renacimiento. La primera linea del Lorem Ipsum, "Lorem ipsum dolor sit amet..", viene de una linea en la sección 1.10.32\r\n\r\nEl trozo de texto estándar de Lorem Ipsum usado desde el año 1500 es reproducido debajo para aquellos interesados. Las secciones 1.10.32 y 1.10.33 de "de Finibus Bonorum et Malorum" por Cicero son también reproducidas en su forma original exacta, acompañadas por versiones en Inglés de la traducción realizada en 1914 por H. Rackham.', 'Screenshot (2).png', NULL, 0, 7.00, 0, 0, 0, '2025-04-25 14:00:00', NULL);
-- --------------------------------------------------------

--
-- Table structure for table `product_option_types`
--

CREATE TABLE `product_option_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_options`
--

CREATE TABLE `product_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_type_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `option_type_id` (`option_type_id`),
  CONSTRAINT `product_options_ibfk_1` FOREIGN KEY (`option_type_id`) REFERENCES `product_option_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_option_map`
--

CREATE TABLE `product_option_map` (
  `product_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`, `option_id`),
  CONSTRAINT `product_option_map_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `product_option_map_ibfk_2` FOREIGN KEY (`option_id`) REFERENCES `product_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customization` json DEFAULT NULL,
  PRIMARY KEY (`cart_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `added_at`, `customization`) VALUES
(24, 12, 10, 1, '2025-04-25 03:31:32', NULL),
(30, 9, 24, 1, '2025-04-25 11:35:02', NULL),
(32, 9, 10, 2, '2025-04-25 13:38:39', '[{\"id\":\"5\",\"name\":\"hatdog\",\"price\":\"30.00\"}]'),
(34, 13, 28, 1, '2025-04-25 17:19:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `price_per_item` decimal(10,2) NOT NULL,
  `customization` json DEFAULT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price_per_item`) VALUES
(1, 3, 10, 1, 1234.00),
(2, 3, 11, 1, 1235.00),
(3, 3, 18, 1, 60.00),
(4, 3, 16, 1, 1234.00),
(5, 3, 24, 1, 1234.00),
(6, 3, 19, 1, 60.00),
(7, 4, 11, 1, 1235.00),
(8, 5, 16, 1, 1234.00),
(9, 6, 10, 1, 1234.00),
(10, 7, 10, 1, 1234.00),
(11, 8, 24, 1, 1234.00),
(12, 9, 24, 1, 1234.00),
(13, 10, 10, 2, 1234.00),
(14, 11, 17, 2, 124.00),
(15, 14, 18, 2, 60.00),
(16, 15, 28, 1, 500.00),
(17, 16, 10, 2, 1234.00),
(18, 17, 9, 2, 512.00),
(19, 18, 28, 10, 500.00),
(20, 19, 10, 1, 1234.00),
(22, 21, 29, 1, 23567.50),
(23, 22, 30, 1, 23553.00),
(24, 23, 17, 2, 124.00),
(25, 24, 31, 1, 23928.00),
(26, 25, 32, 1, 1999998.00);

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
--
-- Indexes for table `customized_products`
--
ALTER TABLE `customized_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `ribbon_colors`
--
ALTER TABLE `ribbon_colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wrappers`
--
ALTER TABLE `wrappers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `add_ons`
--
ALTER TABLE `add_ons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bouquet_sizes`
--
ALTER TABLE `bouquet_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customized_products`
--
ALTER TABLE `customized_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `ribbon_colors`
--
ALTER TABLE `ribbon_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `wrappers`
--
ALTER TABLE `wrappers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `add_ons`
--
ALTER TABLE `add_ons`
  ADD CONSTRAINT `add_ons_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
