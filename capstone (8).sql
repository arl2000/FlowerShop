-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 24, 2025 at 06:09 PM
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
-- Table structure for table `add_ons`
--

CREATE TABLE `add_ons` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `add_ons`
--

INSERT INTO `add_ons` (`id`, `product_id`, `name`, `image_path`, `price`) VALUES
(3, NULL, 'Chocolate Bar', 'uploads/Screenshot (2).png', 75.00),
(4, NULL, 'teddy bear', 'uploads/Screenshot (8).png', 450.00),
(5, NULL, 'hatdog', NULL, 30.00),
(6, NULL, 'mani', NULL, 5.00),
(7, NULL, 'choco mani', 'uploads/6871118e293f9_Ford Mustang Neon Glowing Dream.jpg', 15.00);

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
-- Table structure for table `bouquet_sizes`
--

CREATE TABLE `bouquet_sizes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bouquet_sizes`
--

INSERT INTO `bouquet_sizes` (`id`, `name`, `price`) VALUES
(1, 'Small', 5.00),
(2, 'Medium', 10.00),
(3, 'Large', 15.00);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_customized` tinyint(1) DEFAULT 0,
  `ribbon_color_id` int(11) DEFAULT NULL,
  `ribbon_color_name` varchar(255) DEFAULT NULL,
  `ribbon_color_price` decimal(10,2) DEFAULT NULL,
  `wrapper_color_id` int(11) DEFAULT NULL,
  `wrapper_color_name` varchar(255) DEFAULT NULL,
  `wrapper_color_price` decimal(10,2) DEFAULT NULL,
  `leaves` varchar(255) DEFAULT NULL,
  `flowers` varchar(255) DEFAULT NULL,
  `customer_message` text DEFAULT NULL,
  `addons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`addons`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `product_name`, `product_price`, `product_image`, `quantity`, `added_at`, `is_customized`, `ribbon_color_id`, `ribbon_color_name`, `ribbon_color_price`, `wrapper_color_id`, `wrapper_color_name`, `wrapper_color_price`, `leaves`, `flowers`, `customer_message`, `addons`) VALUES
(24, 12, 10, 'SUNFLOWER', 1234.00, 'roses.jpg', 1, '2025-04-25 03:31:32', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 13, 28, 'Roses', 500.00, 'productChart.png', 1, '2025-04-25 17:19:23', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 9, 33, 'Custom Bouquet', 132.00, NULL, 1, '2025-07-10 08:00:36', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'test 1 july 10, 2025 4:00pm', '{\"addons\":[{\"id\":\"3\",\"name\":\"Chocolate Bar\",\"price\":\"75.00\",\"qty\":1}],\"ribbons\":[{\"id\":\"1\",\"name\":\"Red\",\"price\":\"2.00\",\"qty\":1}],\"wrappers\":[{\"id\":\"1\",\"name\":\"pink\",\"price\":\"10.00\",\"qty\":1}],\"leaves\":[{\"id\":\"1\",\"name\":\"Banana Leaf\",\"price\":\"25.00\",\"qty\":1}],\"flowers\":[{\"id\":\"1\",\"name\":\"Red Rose\",\"price\":\"20.00\",\"qty\":1}],\"positions\":[]}'),
(36, 12, 34, 'Custom Bouquet', 135.50, NULL, 1, '2025-07-10 08:04:11', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'test 2 july 10, 2025 4:04pm\r\n', '{\"addons\":[],\"ribbons\":[{\"id\":\"2\",\"name\":\"White\",\"price\":\"1.50\",\"qty\":1}],\"wrappers\":[{\"id\":\"2\",\"name\":\"black\",\"price\":\"100.00\",\"qty\":1}],\"leaves\":[{\"id\":\"2\",\"name\":\"Taro Leaf\",\"price\":\"15.50\",\"qty\":1}],\"flowers\":[{\"id\":\"2\",\"name\":\"White Tulip\",\"price\":\"18.50\",\"qty\":1}],\"positions\":[]}'),
(38, 9, 37, 'Custom Bouquet', 507.00, NULL, 1, '2025-07-11 12:59:15', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'test 2', '{\"flowers\":[{\"id\":\"1\",\"qty\":1,\"name\":null,\"price\":null}],\"ribbons\":[{\"id\":\"1\",\"qty\":1,\"name\":null,\"price\":null}],\"wrappers\":[{\"id\":\"1\",\"qty\":1,\"name\":null,\"price\":null}],\"addons\":[{\"id\":\"4\",\"qty\":1,\"name\":null,\"price\":null}],\"leaves\":[{\"id\":\"1\",\"qty\":1,\"name\":null,\"price\":null}]}'),
(39, 9, 38, 'Custom Bouquet', 507.00, NULL, 1, '2025-07-11 13:04:55', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'test 2', '{\"flowers\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Red Rose\",\"price\":\"20.00\"}],\"ribbons\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Red\",\"price\":\"2.00\"}],\"wrappers\":[{\"id\":\"1\",\"qty\":1,\"name\":\"pink\",\"price\":\"10.00\"}],\"addons\":[{\"id\":\"4\",\"qty\":1,\"name\":\"teddy bear\",\"price\":\"450.00\"}],\"leaves\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Banana Leaf\",\"price\":\"25.00\"}]}'),
(41, 9, 41, 'Custom Bouquet', 142.00, NULL, 1, '2025-07-24 16:03:44', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '{\"flowers\":[{\"id\":\"0\",\"qty\":1,\"name\":\"santan\",\"price\":\"30.00\"}],\"ribbons\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Red\",\"price\":\"2.00\"}],\"wrappers\":[{\"id\":\"1\",\"qty\":1,\"name\":\"pink\",\"price\":\"10.00\"}],\"addons\":[{\"id\":\"3\",\"qty\":1,\"name\":\"Chocolate Bar\",\"price\":\"75.00\"}],\"leaves\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Banana Leaf\",\"price\":\"25.00\"}]}');

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
-- Table structure for table `customized_products`
--

CREATE TABLE `customized_products` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `occasion_type` varchar(255) NOT NULL,
  `color` varchar(50) NOT NULL,
  `size` varchar(50) NOT NULL,
  `add_ons` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `bouquet_sizes` varchar(255) DEFAULT NULL,
  `ribbon_colors` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_image` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `category_id` int(11) DEFAULT NULL,
  `message_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_count` int(11) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customized_products`
--

INSERT INTO `customized_products` (`id`, `product_id`, `occasion_type`, `color`, `size`, `add_ons`, `message`, `bouquet_sizes`, `ribbon_colors`, `created_at`, `updated_at`, `product_name`, `product_price`, `product_description`, `product_image`, `category_id`, `message_price`, `stock_count`, `is_deleted`) VALUES
(7, 0, 'any', 'red,blue,yellow', 'small, mini', '[{\"name\":\"snickers\",\"quantity\":\"4\",\"image\":\"\"}]', '', 'xl, mini', 'lavender,red,blue,pink', '2025-04-20 11:35:17', '2025-04-25 11:13:01', 'hatdog', 23466.00, 'aaaaaaaaaaa', '6804dbf5b160d_6804c38e81713_flores.gif', NULL, 0.00, 16, 0),
(8, 0, 'any', 'red,blue,yellow', 'small, mini', '[{\"name\":\"snickers\",\"quantity\":\"4\",\"image\":\"\"}]', '', 'xl, mini', 'lavender,red,blue,pink', '2025-04-20 11:35:50', '2025-04-25 11:13:04', 'hatdog', 23466.00, 'wsdjhdufyeu', '6804dc16eb142_6804c38e80e42_67f7fc47a69a1_scik.jpg', NULL, 0.00, 0, 0),
(9, 26, '', 'red,blue,yellow', 'small, mini', 'SNICKERS 3X', '', 'small,medium', 'lavender,red,blue,pink', '2025-04-21 03:04:06', '2025-04-25 11:13:08', 'SAMPAGUITA', 50.00, 'NICE', '6805b5a69009b.jpg', 1, 12.00, 13, 0),
(10, 27, '', 'red,blue,yellow', 'small, mini', 'SNICKERS 3X', '', 'small,medium', 'lavender,red,blue,pink', '2025-04-21 03:06:19', '2025-04-25 11:13:12', 'hatdog', 12.00, 'DDDDXA', '6805b62b1c736.jpg', 0, 0.00, 20, 0),
(11, 33, '', '', '', '{\"addons\":[{\"id\":\"3\",\"name\":\"Chocolate Bar\",\"price\":\"75.00\",\"qty\":1}],\"ribbons\":[{\"id\":\"1\",\"name\":\"Red\",\"price\":\"2.00\",\"qty\":1}],\"wrappers\":[{\"id\":\"1\",\"name\":\"pink\",\"price\":\"10.00\",\"qty\":1}],\"leaves\":[{\"id\":\"1\",\"name\":\"Banana Leaf\",\"price\":\"25.00\",\"qty\":1}],\"flowers\":[{\"id\":\"1\",\"name\":\"Red Rose\",\"price\":\"20.00\",\"qty\":1}],\"positions\":[]}', 'test 1 july 10, 2025 4:00pm', NULL, NULL, '2025-07-10 08:00:36', '2025-07-10 08:00:36', 'Custom Bouquet', 132.00, 'Custom Bouquet', 'default.jpg', 8, 0.00, 0, 0),
(12, 34, '', '', '', '{\"addons\":[],\"ribbons\":[{\"id\":\"2\",\"name\":\"White\",\"price\":\"1.50\",\"qty\":1}],\"wrappers\":[{\"id\":\"2\",\"name\":\"black\",\"price\":\"100.00\",\"qty\":1}],\"leaves\":[{\"id\":\"2\",\"name\":\"Taro Leaf\",\"price\":\"15.50\",\"qty\":1}],\"flowers\":[{\"id\":\"2\",\"name\":\"White Tulip\",\"price\":\"18.50\",\"qty\":1}],\"positions\":[]}', 'test 2 july 10, 2025 4:04pm\r\n', NULL, NULL, '2025-07-10 08:04:11', '2025-07-10 08:04:11', 'Custom Bouquet', 135.50, 'Custom Bouquet', 'default.jpg', 8, 0.00, 0, 0),
(13, 35, '', '', '', '{\"flowers\":[],\"ribbons\":[],\"wrappers\":[],\"addons\":[{\"id\":\"3\",\"qty\":1,\"name\":null,\"price\":null}]}', 'test 1 july 11, 2025 3:56pm', NULL, NULL, '2025-07-11 07:56:56', '2025-07-11 07:56:56', 'Custom Bouquet', 75.00, 'Custom Bouquet', 'default.jpg', 8, 0.00, 0, 0),
(14, 37, '', '', '', '{\"flowers\":[{\"id\":\"1\",\"qty\":1,\"name\":null,\"price\":null}],\"ribbons\":[{\"id\":\"1\",\"qty\":1,\"name\":null,\"price\":null}],\"wrappers\":[{\"id\":\"1\",\"qty\":1,\"name\":null,\"price\":null}],\"addons\":[{\"id\":\"4\",\"qty\":1,\"name\":null,\"price\":null}],\"leaves\":[{\"id\":\"1\",\"qty\":1,\"name\":null,\"price\":null}]}', 'test 2', NULL, NULL, '2025-07-11 12:59:15', '2025-07-11 12:59:15', 'Custom Bouquet', 507.00, 'Custom Bouquet', 'default.jpg', 8, 0.00, 0, 0),
(15, 38, '', '', '', '{\"flowers\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Red Rose\",\"price\":\"20.00\"}],\"ribbons\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Red\",\"price\":\"2.00\"}],\"wrappers\":[{\"id\":\"1\",\"qty\":1,\"name\":\"pink\",\"price\":\"10.00\"}],\"addons\":[{\"id\":\"4\",\"qty\":1,\"name\":\"teddy bear\",\"price\":\"450.00\"}],\"leaves\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Banana Leaf\",\"price\":\"25.00\"}]}', 'test 2', NULL, NULL, '2025-07-11 13:04:55', '2025-07-11 13:04:55', 'Custom Bouquet', 507.00, 'Custom Bouquet', 'default.jpg', 8, 0.00, 0, 0),
(16, 39, '', '', '', '{\"flowers\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Red Rose\",\"price\":\"20.00\"}],\"ribbons\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Red\",\"price\":\"2.00\"}],\"wrappers\":[{\"id\":\"1\",\"qty\":1,\"name\":\"pink\",\"price\":\"10.00\"}],\"addons\":[{\"id\":\"4\",\"qty\":1,\"name\":\"teddy bear\",\"price\":\"450.00\"}],\"leaves\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Banana Leaf\",\"price\":\"25.00\"}]}', 'test 2', NULL, NULL, '2025-07-11 13:08:44', '2025-07-11 13:08:44', 'Custom Bouquet', 507.00, 'Custom Bouquet', 'default.jpg', 8, 0.00, 0, 0),
(17, 41, '', '', '', '{\"flowers\":[{\"id\":\"0\",\"qty\":1,\"name\":\"santan\",\"price\":\"30.00\"}],\"ribbons\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Red\",\"price\":\"2.00\"}],\"wrappers\":[{\"id\":\"1\",\"qty\":1,\"name\":\"pink\",\"price\":\"10.00\"}],\"addons\":[{\"id\":\"3\",\"qty\":1,\"name\":\"Chocolate Bar\",\"price\":\"75.00\"}],\"leaves\":[{\"id\":\"1\",\"qty\":1,\"name\":\"Banana Leaf\",\"price\":\"25.00\"}]}', '', NULL, NULL, '2025-07-24 16:03:44', '2025-07-24 16:03:44', 'Custom Bouquet', 142.00, 'Custom Bouquet', 'default.jpg', 8, 0.00, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `flowers`
--

CREATE TABLE `flowers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flowers`
--

INSERT INTO `flowers` (`id`, `name`, `price`, `image_path`) VALUES
(0, 'santan', 30.00, 'uploads/6871136fc7578_Screenshot (2).png'),
(1, 'Red Rose', 20.00, 'images/red_rose.jpg'),
(2, 'White Tulip', 18.50, 'images/white_tulip.jpg'),
(3, 'Sunflower', 22.75, 'images/sunflower.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `name`, `price`, `image_path`) VALUES
(1, 'Banana Leaf', 25.00, 'images/banana_leaf.jpg'),
(2, 'Taro Leaf', 15.50, 'images/taro_leaf.jpg'),
(3, 'Pandan Leaf', 10.00, 'images/pandan_leaf.jpg'),
(4, 'Lotus Leaf', 30.00, 'images/lotus_leaf.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
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
  `delivery_service` varchar(100) DEFAULT NULL
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
(25, 'grover boy', 'Hacienda Remedios Barangay Balabag La Carlota City Negros Occidental Philippines', 'grover@gmail.com', '09123456782', 'tani ma send ka update', NULL, 'cod', 'delivered', '2025-04-25 17:20:23', 0, 1, 1999998.00, '', NULL, '2025-05-01', 'sa may 1 lang noy'),
(26, 'user1', 'lcc', 'user1@gmail.com', '09123456782', 'checkout test 1 july 11, 2025 5:31pm', 'uploads/1752226343_Ford Mustang Neon Glowing Dream.jpg', 'gcash', 'shipped', '2025-07-11 09:32:23', 0, 1, 75.00, '', NULL, '2025-07-12', 'july 11 test 1'),
(27, 'ariel gabiandan', 'Hacienda Remedios Barangay Balabag La Carlota City Negros Occidental Philippines', 'user1@gmail.com', '09123456782', 'test 2', 'uploads/1752239380_joker.jpg', 'gcash', 'pending', '2025-07-11 13:09:40', 0, 1, 507.00, '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price_per_item` decimal(10,2) NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `product_name`, `price_per_item`, `quantity`) VALUES
(1, 3, 10, 'SUNFLOWER', 1234.00, 1),
(2, 3, 11, 'Funeral flowers', 1235.00, 1),
(3, 3, 18, 'tuilps', 60.00, 1),
(4, 3, 16, 'SUNFLOWER', 1234.00, 1),
(5, 3, 24, 'Funeral flowers', 1234.00, 1),
(6, 3, 19, 'tuilps', 60.00, 1),
(7, 4, 11, 'Funeral flowers', 1235.00, 1),
(8, 5, 16, 'SUNFLOWER', 1234.00, 1),
(9, 6, 10, 'SUNFLOWER', 1234.00, 1),
(10, 7, 10, 'SUNFLOWER', 1234.00, 1),
(11, 8, 24, 'Funeral flowers', 1234.00, 1),
(12, 9, 24, 'Funeral flowers', 1234.00, 1),
(13, 10, 10, 'SUNFLOWER', 1234.00, 2),
(14, 11, 17, 'wwwww', 124.00, 2),
(15, 14, 18, 'tuilps', 60.00, 2),
(16, 15, 28, 'Roses', 500.00, 1),
(17, 16, 10, 'SUNFLOWER', 1234.00, 2),
(18, 17, 9, 'SAMPAGUITA', 512.00, 2),
(19, 18, 28, 'Roses', 500.00, 10),
(20, 19, 10, 'SUNFLOWER', 1234.00, 1),
(22, 21, 29, 'hatdog', 23567.50, 1),
(23, 22, 30, 'hatdog', 23553.00, 1),
(24, 23, 17, 'wwwww', 124.00, 2),
(25, 24, 31, 'hatdog', 23928.00, 1),
(26, 25, 32, 'rosepharmachyyyyyyyyy', 1999998.00, 1),
(27, 26, 36, 'Custom Bouquet', 75.00, 1),
(28, 27, 40, 'Custom Bouquet', 507.00, 1);

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
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_description` text NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bouquet_sizes` varchar(255) DEFAULT NULL,
  `ribbon_colors` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_type` varchar(50) DEFAULT NULL,
  `original_stock` int(11) DEFAULT 0,
  `stock_count` int(11) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `price`, `product_price`, `product_description`, `product_image`, `created_at`, `bouquet_sizes`, `ribbon_colors`, `category_id`, `product_type`, `original_stock`, `stock_count`, `is_deleted`) VALUES
(9, 'SUNFLOWER', 0.00, 499.00, 'Sunflowers (genus Helianthus) are tall, annual plants known for their large, daisy-like flower heads that often turn to face the sun, a phenomenon called heliotropism in young plants. Native to North America, they are widely cultivated for their edible seeds and the oil extracted from them. These cheerful flowers typically have bright yellow petals (ray florets) surrounding a central disk of florets that mature into seeds. Sunflowers can grow to impressive heights, sometimes reaching over 3 meters, and their seeds are a nutritious snack and a source of cooking oil. Beyond their practical uses, sunflowers are symbols of happiness, warmth, and longevity.', 'sun.jpg', '2025-04-17 06:11:00', 'small,medium', 'red', 1, NULL, 0, 0, 0),
(10, 'SUNFLOWER', 0.00, 1234.00, 'aaaaaaaaaaaaaaaa', 'roses.jpg', '2025-04-17 06:14:55', NULL, NULL, NULL, NULL, 0, 0, 0),
(11, 'Funeral flowers', 1235.00, 1235.00, 'For funeral flower messages, consider simple yet meaningful phrases like \"In loving memory,\" \"Rest in peace,\" or \"Gone but never forgotten.\" These phrases convey sympathy and respect, offering comfort to the grieving family. You can also personalize your message with a specific memory or sentiment related to the deceased. \r\nHere are some more ideas:\r\nSimple & Traditional:\r\nIn loving memory.\r\nForever in our thoughts.\r\nMay you rest in peace.\r\nLoved and remembered.\r\nGone but never forgotten.\r\nWith love and fond memories.\r\nAlways in my/our heart(s).\r\nSleep peacefully.\r\nRest in peace.\r\nAlways in our hearts.\r\nForever in our hearts.\r\nWith deepest sympathy.\r\nWith heartfelt condolences. \r\nMore Personal:\r\n\"[Deceased\'s Name] will truly be missed\".\r\n\"Remembering [Deceased\'s Name]\'s life with love\".\r\n\"[Deceased\'s Name] will live on in our hearts\".\r\n\"May you rest peacefully, [Deceased\'s Name]\".\r\n\"Thinking of you always, [Deceased\'s Name]\".\r\n\"You were the most [positive adjective] person I/we knew\".\r\n\"You brought so much joy into our lives\".\r\n\"Thank you for everything, [Deceased\'s Name]\". \r\nAdditional Tips:\r\nConsider the relationship to the deceased: If you were a close friend or family member, you might include a more personal anecdote or memory. \r\nKeep it concise: Funeral flowers often have limited space for messages, so it\'s best to choose a few impactful words. \r\nWrite from the heart: The most important thing is to express your sympathy and love. ', 'fff.jpg', '2025-04-20 15:11:39', '', '', 0, NULL, 0, 0, 0),
(16, 'SUNFLOWER', 1234.00, 1234.00, '', 'fff.jpg', '2025-04-20 15:12:00', NULL, NULL, NULL, NULL, 0, 0, 0),
(17, 'wwwww', 0.00, 124.00, 'Sunflowers (genus Helianthus) are tall, annual plants known for their large, daisy-like flower heads that often turn to face the sun, a phenomenon called heliotropism in young plants. Native to North America, they are widely cultivated for their edible seeds and the oil extracted from them. These cheerful flowers typically have bright yellow petals (ray florets) surrounding a central disk of florets that mature into seeds. Sunflowers can grow to impressive heights, sometimes reaching over 3 meters, and their seeds are a nutritious snack and a source of cooking oil. Beyond their practical uses, sunflowers are symbols of happiness, warmth, and longevity.', 'fb.jpg', '2025-04-19 06:18:21', 'small, medium', 'lavender', 10, NULL, 0, 0, 0),
(18, 'tuilps', 60.00, 60.00, '', 'sun.jpg', '2025-04-20 15:11:50', NULL, NULL, NULL, NULL, 0, 0, 0),
(19, 'tuilps', 0.00, 60.00, 'a nice wow', 'sun.jpg', '2025-04-20 09:52:33', 'small, medium', 'lavender,red,blue', 0, NULL, 50, 9, 0),
(24, 'Funeral flowers', 0.00, 1234.00, 'wwwwwwwwwww', '6804d5c0055b1_sun.jpg', '2025-04-20 11:08:48', 'small, medium', 'lavender,red,blue', 10, NULL, 15, 5, 0),
(28, 'Roses', 0.00, 500.00, 'pula2 gid nisa', 'productChart.png', '2025-04-23 06:10:13', NULL, NULL, NULL, NULL, 20, 10, 0),
(29, 'hatdog', 23567.50, 23567.50, 'Customized product', 'default.jpg', '2025-04-25 11:03:34', NULL, NULL, NULL, NULL, 0, 0, 0),
(30, 'hatdog', 23553.00, 23553.00, 'Customized product', 'default.jpg', '2025-04-25 11:05:41', NULL, NULL, NULL, NULL, 0, 0, 0),
(31, 'hatdog', 23928.00, 23928.00, 'Customized product', 'default.jpg', '2025-04-25 13:02:47', NULL, NULL, NULL, NULL, 0, 0, 0),
(32, 'rosepharmachyyyyyyyyy', 0.00, 1999998.00, 'De dónde viene?\r\nAl contrario del pensamiento popular, el texto de Lorem Ipsum no es simplemente texto aleatorio. Tiene sus raices en una pieza cl´sica de la literatura del Latin, que data del año 45 antes de Cristo, haciendo que este adquiera mas de 2000 años de antiguedad. Richard McClintock, un profesor de Latin de la Universidad de Hampden-Sydney en Virginia, encontró una de las palabras más oscuras de la lengua del latín, \"consecteur\", en un pasaje de Lorem Ipsum, y al seguir leyendo distintos textos del latín, descubrió la fuente indudable. Lorem Ipsum viene de las secciones 1.10.32 y 1.10.33 de \"de Finnibus Bonorum et Malorum\" (Los Extremos del Bien y El Mal) por Cicero, escrito en el año 45 antes de Cristo. Este libro es un tratado de teoría de éticas, muy popular durante el Renacimiento. La primera linea del Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", viene de una linea en la sección 1.10.32\r\n\r\nEl trozo de texto estándar de Lorem Ipsum usado desde el año 1500 es reproducido debajo para aquellos interesados. Las secciones 1.10.32 y 1.10.33 de \"de Finibus Bonorum et Malorum\" por Cicero son también reproducidas en su forma original exacta, acompañadas por versiones en Inglés de la traducción realizada en 1914 por H. Rackham.', 'Screenshot (2).png', '2025-04-25 16:18:22', NULL, NULL, 7, NULL, 65, 65, 0),
(33, 'Custom Bouquet', 132.00, 132.00, 'Customized product', 'default.jpg', '2025-07-10 08:00:36', NULL, NULL, 8, NULL, 0, 0, 0),
(34, 'Custom Bouquet', 135.50, 135.50, 'Customized product', 'default.jpg', '2025-07-10 08:04:11', NULL, NULL, 8, NULL, 0, 0, 0),
(35, 'Custom Bouquet', 75.00, 75.00, 'Customized product', 'default.jpg', '2025-07-11 07:56:56', NULL, NULL, 8, NULL, 0, 0, 0),
(36, 'Custom Bouquet', 75.00, 75.00, 'Customized product', 'default.jpg', '2025-07-11 09:32:23', NULL, NULL, NULL, NULL, 0, 0, 0),
(37, 'Custom Bouquet', 507.00, 507.00, 'Customized product', 'default.jpg', '2025-07-11 12:59:15', NULL, NULL, 8, NULL, 0, 0, 0),
(38, 'Custom Bouquet', 507.00, 507.00, 'Customized product', 'default.jpg', '2025-07-11 13:04:55', NULL, NULL, 8, NULL, 0, 0, 0),
(39, 'Custom Bouquet', 507.00, 507.00, 'Customized product', 'default.jpg', '2025-07-11 13:08:44', NULL, NULL, 8, NULL, 0, 0, 0),
(40, 'Custom Bouquet', 507.00, 507.00, 'Customized product', 'default.jpg', '2025-07-11 13:09:40', NULL, NULL, NULL, NULL, 0, 0, 0),
(41, 'Custom Bouquet', 142.00, 142.00, 'Customized product', 'default.jpg', '2025-07-24 16:03:44', NULL, NULL, 8, NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ribbon_colors`
--

CREATE TABLE `ribbon_colors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ribbon_colors`
--

INSERT INTO `ribbon_colors` (`id`, `name`, `price`) VALUES
(1, 'Red', 2.00),
(2, 'White', 1.50),
(3, 'Lavender', 2.50),
(4, 'black', 20.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) NOT NULL DEFAULT 'uploads/default_profile.png'
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
-- Table structure for table `wrappers`
--

CREATE TABLE `wrappers` (
  `id` int(11) NOT NULL,
  `color` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wrappers`
--

INSERT INTO `wrappers` (`id`, `color`, `price`, `image_path`) VALUES
(1, 'pink', 10.00, NULL),
(2, 'black', 100.00, NULL),
(3, 'red', 10.00, NULL),
(4, 'white', 10.00, 'uploads/6871168cb29d4_Screenshot (8).png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `add_ons`
--
ALTER TABLE `add_ons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bouquet_sizes`
--
ALTER TABLE `bouquet_sizes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `ribbon_color_id` (`ribbon_color_id`),
  ADD KEY `wrapper_color_id` (`wrapper_color_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customized_products`
--
ALTER TABLE `customized_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flowers`
--
ALTER TABLE `flowers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customized_products`
--
ALTER TABLE `customized_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`ribbon_color_id`) REFERENCES `ribbon_colors` (`id`),
  ADD CONSTRAINT `cart_ibfk_4` FOREIGN KEY (`wrapper_color_id`) REFERENCES `wrappers` (`id`);

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
