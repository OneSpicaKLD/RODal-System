-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 11, 2026 at 07:34 AM
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
-- Database: `rodal`
--
CREATE DATABASE IF NOT EXISTS `rodal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `rodal`;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1000, 'FOOD_RICE'),
(1001, 'FOOD_INGREDIENT'),
(1002, 'FOOD_SNACK'),
(1003, 'FOOD_INSTANT'),
(1004, 'FOOD_CANNED'),
(1005, 'DRINK_POWDERED'),
(1006, 'BEVERAGE'),
(1007, 'TOILETRIES'),
(1008, 'CLEANING AGENTS');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `product_id`, `transaction_id`, `title`, `message`, `status`, `created_at`) VALUES
(1, 1001, 52, 'Low Stock', 'HANSEL 12PCS is running low (3 left).', 'read', '2026-05-08 09:50:01'),
(2, 1005, 40, 'Low Stock', 'REBISCO CRACKERS 4PCS is running low (3 left).', 'read', '2026-05-08 09:50:01'),
(3, 1006, 37, 'Low Stock', 'FUDGEE BAR 42GRMS is running low (9 left).', 'read', '2026-05-08 09:50:01'),
(4, 1007, 48, 'Low Stock', 'SKYFLAKES CRACKERS 3PCS is running low (4 left).', 'read', '2026-05-08 09:50:01'),
(5, 1009, NULL, 'Low Stock', 'MARTYS 24GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(6, 1010, NULL, 'Low Stock', 'LUCKY ME 55GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(7, 1011, NULL, 'Low Stock', 'PANCIT CANTON 80GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(8, 1012, NULL, 'Low Stock', 'NISSIN RAMEN 55GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(9, 1013, 4, 'Low Stock', 'CUP NOODLES 45GRMS is running low (9 left).', 'read', '2026-05-08 09:50:01'),
(10, 1014, NULL, 'Low Stock', 'LIGO SARDINES 155GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(11, 1015, NULL, 'Low Stock', 'MEGA SARDINES 155 GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(12, 1016, NULL, 'Low Stock', 'YOUNG\'S TOWN 155GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(13, 1018, NULL, 'Low Stock', 'CENTURY TUNA 155GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(14, 1019, NULL, 'Low Stock', 'SAN MARINO 150GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(15, 1020, NULL, 'Low Stock', 'WOW ULAM 155GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(16, 1021, NULL, 'Low Stock', 'CDO CORNED BEEF 150GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(17, 1022, NULL, 'Low Stock', 'ARGENTINA CORNED BEEF 150GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(18, 1023, NULL, 'Low Stock', 'ARGENTINA MEAT LOAF 150GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(19, 1024, NULL, 'Low Stock', 'BEAR BRAND SWAK 33GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(20, 1025, NULL, 'Low Stock', 'BIRCH TREE SWAK 33GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(21, 1026, NULL, 'Low Stock', 'MILO 24GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(22, 1027, NULL, 'Low Stock', 'TANG ORANGE 19GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(23, 1028, NULL, 'Low Stock', 'TANG PINEAPPLE 19GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(24, 1029, NULL, 'Low Stock', 'TANG DALANDAN 19GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(25, 1030, NULL, 'Low Stock', 'NESTEA APPLE 19GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(26, 1031, NULL, 'Low Stock', 'NESTEA LEMON 19GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(27, 1032, NULL, 'Low Stock', 'KOPIKO TWIN 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(28, 1033, NULL, 'Low Stock', 'NESCAFE TWIN 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(29, 1034, NULL, 'Low Stock', 'COKE MISMO 290ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(30, 1035, NULL, 'Low Stock', 'SPRITE MISMO 290ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(31, 1036, NULL, 'Low Stock', 'ROYAL MISMO 250ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(32, 1037, NULL, 'Low Stock', 'RC BIG  is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(33, 1038, NULL, 'Low Stock', 'EMPERADOR LIGHTS 1L is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(34, 1039, NULL, 'Low Stock', 'RED HORSE BIG 1L is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(35, 1040, NULL, 'Low Stock', 'RED HORSE SMALL 500ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(36, 1041, NULL, 'Low Stock', 'GIN BILOG 350ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(37, 1042, NULL, 'Low Stock', 'PLUS APPLE 250ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(38, 1043, NULL, 'Low Stock', 'PLUS ORANGE 250ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(39, 1044, NULL, 'Low Stock', 'SAFEGUARD SACHET 55G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(40, 1045, NULL, 'Low Stock', 'BIODERM SOAP 60G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(41, 1046, NULL, 'Low Stock', 'DOVE SHAMPOO 13.5ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(42, 1047, NULL, 'Low Stock', 'SUNSILK SHAMPOO 15ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(43, 1048, NULL, 'Low Stock', 'PALMOLIVE SHAMPOO 15ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(44, 1049, NULL, 'Low Stock', 'CREAM SILK 13ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(45, 1050, NULL, 'Low Stock', 'COLGATE TOOTHPASTE 20G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(46, 1051, NULL, 'Low Stock', 'CLOSEUP TOOTHPASTE 20G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(47, 1052, NULL, 'Low Stock', 'THOSE DAYS 8PADS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(48, 1053, NULL, 'Low Stock', 'WHISPER WINGS 48PADS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(49, 1054, NULL, 'Low Stock', 'ZONROX WHITE 250ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(50, 1055, NULL, 'Low Stock', 'ZONROX VIOLET 250ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(51, 1056, NULL, 'Low Stock', 'CALLA POWDER 100G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(52, 1057, NULL, 'Low Stock', 'SURF POWDER 65G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(53, 1058, NULL, 'Low Stock', 'ARIEL POWDER 132G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(54, 1059, NULL, 'Low Stock', 'TIDE POWDER 130G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(55, 1060, NULL, 'Low Stock', 'CHAMPION POWDER 105G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(56, 1061, NULL, 'Low Stock', 'SURF BAR 120G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(57, 1062, NULL, 'Low Stock', 'CHAMPION BAR 120G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(58, 1063, NULL, 'Low Stock', 'DOWNY SMALL 26ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(59, 1064, NULL, 'Low Stock', 'MAGIC SARAP 8G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(60, 1065, NULL, 'Low Stock', 'VETSIN 11G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(61, 1066, NULL, 'Low Stock', 'GINISA MIX 8G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(62, 1067, NULL, 'Low Stock', 'DATU PUTI PATIS 150ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(63, 1068, NULL, 'Low Stock', 'OYSTER SAUCE 30G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(64, 1069, NULL, 'Low Stock', 'DATU PUTI TOYO 200ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(65, 1070, NULL, 'Low Stock', 'DATU PUTI SUKA 200ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(66, 1071, NULL, 'Low Stock', 'KNOR SINIGANG MIX 22G is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(67, 1072, NULL, 'Low Stock', 'PANDA RICE 25KGS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(68, 1073, NULL, 'Low Stock', ' SINANDOMENG RICE 25KGS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(69, 1074, NULL, 'Low Stock', 'CHEESE CAKE 30GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(70, 1075, NULL, 'Low Stock', 'COMBI 30GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(71, 1076, NULL, 'Low Stock', 'LAVA CAKE 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(72, 1077, NULL, 'Low Stock', 'MAMON MONDE 43GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(73, 1078, NULL, 'Low Stock', 'WAFRETS CHEESE 24GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(74, 1079, NULL, 'Low Stock', 'BEAR BRAND CHOCO 33GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(75, 1080, NULL, 'Low Stock', 'NESCAFE CREAMY LATTE TWIN 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(76, 1081, NULL, 'Low Stock', 'NESCAFE ORINAL TWIN 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(77, 1082, NULL, 'Low Stock', 'KOPIKO BROWN TWIN 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(78, 1083, NULL, 'Low Stock', 'KOPIKO BLACK TWIN 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(79, 1084, NULL, 'Low Stock', 'GREAT TASTE WHITE TWIN 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(80, 1085, NULL, 'Low Stock', 'GREAT TASTE CHOCOLATE TWIN 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(81, 1086, NULL, 'Low Stock', 'ENERGEN CHOCOLATE 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(82, 1087, NULL, 'Low Stock', 'ENERGEN VANILLA 40GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(83, 1088, NULL, 'Low Stock', 'CHAMPION CHOCOLATE TWIN 64GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(84, 1089, NULL, 'Low Stock', 'C2 SOLO 230ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(85, 1090, NULL, 'Low Stock', 'MINERAL SMALL 350ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(86, 1091, NULL, 'Low Stock', 'SPRITE KASALO 720ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(87, 1092, NULL, 'Low Stock', 'COKE KASALO 720ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(88, 1093, NULL, 'Low Stock', 'ROYAL KASALO 720ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(89, 1094, NULL, 'Low Stock', 'KERATIN GOLD 24GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(90, 1095, NULL, 'Low Stock', 'KERATIN PULA 20GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(91, 1096, NULL, 'Low Stock', 'GIVE SOAP 76GRMS is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(92, 1097, NULL, 'Low Stock', 'JOY BIG GREEN 40ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(93, 1098, NULL, 'Low Stock', 'JOY BIG BLUE 40ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(94, 1099, NULL, 'Low Stock', 'JOY BIG YELLOW 40ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(95, 1100, NULL, 'Low Stock', 'JOY SMALL GREEN 19ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(96, 1101, NULL, 'Low Stock', 'JOY SMALL BLUE 19ML is running low (0 left).', 'read', '2026-05-08 09:50:01'),
(97, 1017, 5, 'Expiry Warning', 'Expiring soon: 555 TUNA 155GRMS [Batch #5] (13 units) on 2026-06-01', 'read', '2026-05-08 09:50:01'),
(98, 1001, 10, 'Expiry Warning', 'Expiring soon: HANSEL 12PCS [Batch #10] (5 units) on 2026-06-01', 'read', '2026-05-08 09:50:01'),
(99, 1002, 13, 'Expiry Warning', 'Expiring soon: FITA 9PCS [Batch #13] (15 units) on 2026-06-03', 'read', '2026-05-08 09:50:01'),
(100, 1006, 18, 'Expiry Warning', 'Expiring soon: FUDGEE BAR 42GRMS [Batch #18] (11 units) on 2026-06-01', 'read', '2026-05-08 09:50:01'),
(101, 1000, 21, 'Expiry Warning', 'Expiring soon: CREAM-O 3PCS [Batch #21] (1 units) on 2026-05-31', 'read', '2026-05-08 09:50:01'),
(102, 1005, 39, 'Expiry Warning', 'Expiring soon: REBISCO CRACKERS 4PCS [Batch #39] (1 units) on 2026-06-01', 'read', '2026-05-08 09:50:01'),
(103, 1000, 45, 'Expiry Warning', 'Expiring soon: CREAM-O 3PCS [Batch #45] (1 units) on 2026-05-09', 'read', '2026-05-08 09:50:01'),
(104, 1002, 53, 'Expiry Warning', 'Expiring soon: FITA 9PCS [Batch #53] (1 units) on 2026-05-09', 'read', '2026-05-08 09:50:01'),
(105, 1000, 54, 'Expiry Warning', 'Expiring soon: CREAM-O 3PCS [Batch #54] (1 units) on 2026-06-01', 'read', '2026-05-08 09:50:01'),
(106, 1000, 56, 'Expiry Warning', 'Expiring soon: CREAM-O 3PCS [Batch #56] (1 units) on 2026-05-29', 'read', '2026-05-08 09:50:29'),
(107, 1000, 42, 'Expiry Warning', 'Expiring soon: CREAM-O 3PCS [Batch #42] (1 units) on 2026-06-08', 'read', '2026-05-09 05:55:04'),
(108, 1007, 58, 'Low Stock', 'SKYFLAKES CRACKERS 3PCS is running low (3 left).', 'unread', '2026-05-10 16:38:44'),
(109, 1007, 59, 'Low Stock', 'SKYFLAKES CRACKERS 3PCS is running low (2 left).', 'unread', '2026-05-10 16:39:12');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `product_sku` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `category_id`, `cost`, `price`, `product_sku`, `is_active`) VALUES
(1000, 'CREAM-O 3PCS', 1002, 8.00, 10.00, 'FS-CRMO-3PCS', 1),
(1001, 'HANSEL 12PCS', 1002, 6.00, 8.00, 'FS-HANSEL-12PCS', 1),
(1002, 'FITA 9PCS', 1002, 6.20, 8.00, 'FS-FITA-9PCS', 1),
(1005, 'REBISCO CRACKERS 4PCS', 1002, 0.00, 8.00, 'FS-REBCRACK-4PCS', 1),
(1006, 'FUDGEE BAR 42GRMS', 1002, 8.50, 10.00, 'FS-FUDBAR-42GRMS', 1),
(1007, 'SKYFLAKES CRACKERS 3PCS', 1002, 0.00, 9.00, 'FS-SKYFLCRACK-3PCS', 1),
(1008, 'OISHI 24GRMS', 1002, 6.50, 8.00, 'FS-OISHI-24GRMS', 1),
(1009, 'MARTYS 24GRMS', 1002, 6.50, 8.00, 'FS-MARTY\'S-24GRMS', 1),
(1010, 'LUCKY ME 55GRMS', 1003, 8.50, 11.00, 'FI-LUCKYME-55GRMS', 1),
(1011, 'PANCIT CANTON 80GRMS', 1003, 14.25, 17.00, 'FI-PANCAN-80GRMS', 1),
(1012, 'NISSIN RAMEN 55GRMS', 1003, 15.00, 17.00, 'FI-NISSRAM-55GRMS', 1),
(1013, 'CUP NOODLES 45GRMS', 1003, 20.00, 26.00, 'FI-CN-45GRMS', 1),
(1014, 'LIGO SARDINES 155GRMS', 1004, 23.00, 28.00, 'FC-LIGO-155GRMS', 1),
(1015, 'MEGA SARDINES 155 GRMS', 1004, 23.00, 28.00, 'FC-MEGA-155GRMS', 1),
(1016, 'YOUNG\'S TOWN 155GRMS', 1004, 22.00, 27.00, 'FC-YOUNG\'S-155GRMS', 1),
(1017, '555 TUNA 155GRMS', 1004, 26.00, 33.00, 'FC-555-155GRMS', 1),
(1018, 'CENTURY TUNA 155GRMS', 1004, 33.00, 38.00, 'FC-CENTURY-155GRMS', 1),
(1019, 'SAN MARINO 150GRMS', 1004, 38.00, 45.00, 'FC-SANMARINO-150GRMS', 1),
(1020, 'WOW ULAM 155GRMS', 1004, 24.00, 28.00, 'FC-WOW-155GRMS', 1),
(1021, 'CDO CORNED BEEF 150GRMS', 1004, 28.00, 33.00, 'FC-CDO-150GRMS', 1),
(1022, 'ARGENTINA CORNED BEEF 150GRMS', 1004, 33.00, 38.00, 'FC-ARGENCB-15OGRMS', 1),
(1023, 'ARGENTINA MEAT LOAF 150GRMS', 1004, 26.00, 32.00, 'FC-ARGENML-15OGRMS', 1),
(1024, 'BEAR BRAND SWAK 33GRMS', 1005, 9.75, 13.00, 'DP-BBS-33GRMS', 1),
(1025, 'BIRCH TREE SWAK 33GRMS', 1005, 9.00, 12.00, 'DP-BTS-33GRMS', 1),
(1026, 'MILO 24GRMS', 1005, 9.25, 12.00, 'DP-MILO-24GRMS', 1),
(1027, 'TANG ORANGE 19GRMS', 1005, 18.00, 22.00, 'DP-TANGO-19GRMS', 1),
(1028, 'TANG PINEAPPLE 19GRMS', 1005, 18.00, 22.00, 'DP-TANGA-19GRMS', 1),
(1029, 'TANG DALANDAN 19GRMS', 1005, 18.00, 22.00, 'DP-TANGD-19GRMS', 1),
(1030, 'NESTEA APPLE 19GRMS', 1005, 19.00, 23.00, 'DP-NESAPPLE-19GRMS', 1),
(1031, 'NESTEA LEMON 19GRMS', 1005, 19.00, 23.00, 'DP-NESLEMON-19GRMS', 1),
(1032, 'KOPIKO TWIN 40GRMS', 1005, 11.00, 15.00, 'DP-KOPTWIN-40GRMS', 1),
(1033, 'NESCAFE TWIN 40GRMS', 1005, 11.25, 15.00, 'DP-NESTWIN-40GRMS', 1),
(1034, 'COKE MISMO 290ML', 1006, 17.00, 22.00, 'BEV-COKM-290ML', 1),
(1035, 'SPRITE MISMO 290ML', 1006, 17.00, 22.00, 'BEV-SPRM-290ML', 1),
(1036, 'ROYAL MISMO 250ML', 1006, 17.00, 22.00, 'BEV-ROYM-250ML', 1),
(1037, 'RC BIG ', 1006, 24.00, 30.00, 'BEV-RCBIG-', 1),
(1038, 'EMPERADOR LIGHTS 1L', 1006, 210.00, 230.00, 'BEV-EMP-1L', 1),
(1039, 'RED HORSE BIG 1L', 1006, 130.00, 140.00, 'BEV-RHBG-1L', 1),
(1040, 'RED HORSE SMALL 500ML', 1006, 66.00, 73.00, 'BEV-RHSM-500ML', 1),
(1041, 'GIN BILOG 350ML', 1006, 71.00, 75.00, 'BEV-GINB-350ML', 1),
(1042, 'PLUS APPLE 250ML', 1006, 9.00, 12.00, 'BEV-PLUSA-250ML', 1),
(1043, 'PLUS ORANGE 250ML', 1006, 9.00, 12.00, 'BEV-PLUSO-250ML', 1),
(1044, 'SAFEGUARD SACHET 55G', 1007, 22.00, 25.00, 'TL-SFG-55G', 1),
(1045, 'BIODERM SOAP 60G', 1007, 16.00, 20.00, 'TL-BIO-6OG', 1),
(1046, 'DOVE SHAMPOO 13.5ML', 1007, 5.50, 8.00, 'TL-DOVE-13.5ML', 1),
(1047, 'SUNSILK SHAMPOO 15ML', 1007, 5.75, 8.00, 'TL-SNSLK-15ML', 1),
(1048, 'PALMOLIVE SHAMPOO 15ML', 1007, 5.50, 8.00, 'TL-PLMLIVE-15ML', 1),
(1049, 'CREAM SILK 13ML', 1007, 6.00, 8.00, 'TL-CRMSLK-13ML', 1),
(1050, 'COLGATE TOOTHPASTE 20G', 1007, 7.00, 11.00, 'TL-CLGT-20G', 1),
(1051, 'CLOSEUP TOOTHPASTE 20G', 1007, 7.25, 11.00, 'TL-CLSEP-20G', 1),
(1052, 'THOSE DAYS 8PADS', 1007, 15.00, 19.00, 'TL-THSEDYS-8PADS', 1),
(1053, 'WHISPER WINGS 48PADS', 1007, 6.00, 8.00, 'TL-WHSPR-48PADS', 1),
(1054, 'ZONROX WHITE 250ML', 1008, 13.75, 17.00, 'CA-ZNRXW-25OML', 1),
(1055, 'ZONROX VIOLET 250ML', 1008, 21.50, 26.00, 'CA-ZNRXV-250ML', 1),
(1056, 'CALLA POWDER 100G', 1008, 14.00, 17.00, 'CA-CLLA-100G', 1),
(1057, 'SURF POWDER 65G', 1008, 6.50, 9.00, 'CA-SRF-65G', 1),
(1058, 'ARIEL POWDER 132G', 1008, 13.75, 17.00, 'CA-ARL-132G', 1),
(1059, 'TIDE POWDER 130G', 1008, 14.00, 17.00, 'CA-TIDE-130', 1),
(1060, 'CHAMPION POWDER 105G', 1008, 13.75, 17.00, 'CA-CHMPN-105G', 1),
(1061, 'SURF BAR 120G', 1008, 11.00, 0.00, 'CA-SRFBR-120G', 1),
(1062, 'CHAMPION BAR 120G', 1008, 12.00, 15.00, 'CA-CHMPNBR-120G', 1),
(1063, 'DOWNY SMALL 26ML', 1008, 6.00, 8.00, 'CA-DWNYS-26ML', 1),
(1064, 'MAGIC SARAP 8G', 1001, 4.00, 6.00, 'FI-MGCSRP-8G', 1),
(1065, 'VETSIN 11G', 1001, 3.25, 5.00, 'FI-VTSN-11G', 1),
(1066, 'GINISA MIX 8G', 1001, 4.00, 6.00, 'FI-GNSMX-8G', 1),
(1067, 'DATU PUTI PATIS 150ML', 1001, 16.50, 20.00, 'FI-PATIS-150ML', 1),
(1068, 'OYSTER SAUCE 30G', 1001, 6.00, 8.00, 'FI-OYSTSCS-30G', 1),
(1069, 'DATU PUTI TOYO 200ML', 1001, 8.25, 12.00, 'FI-TOYO-200ML', 1),
(1070, 'DATU PUTI SUKA 200ML', 1001, 7.50, 10.00, 'FI-SUKA-200ML', 1),
(1071, 'KNOR SINIGANG MIX 22G', 1001, 16.50, 19.00, 'FI-SNGNG-22G', 1),
(1072, 'PANDA RICE 25KGS', 1000, 58.50, 62.00, 'FR-PNDRICE-25KGS', 1),
(1073, 'SINANDOMENG RICE 25KGS', 1000, 59.00, 64.00, 'FR-SNDMGRICE-25KGS', 1),
(1074, 'CHEESE CAKE 30GRMS', 1002, 8.25, 10.00, 'FS-CHSCAKE-3OGRMS', 1),
(1075, 'COMBI 30GRMS', 1002, 6.00, 8.00, 'FS-C0MBI-30GRMS', 1),
(1076, 'LAVA CAKE 40GRMS', 1002, 8.25, 10.00, 'FS-LVACAKE-40GRMS', 1),
(1077, 'MAMON MONDE 43GRMS', 1002, 17.00, 20.00, 'FS-MMNMNDE-43GRMS', 1),
(1078, 'WAFRETS CHEESE 24GRMS', 1002, 6.00, 8.00, 'FS-WFRTS-24GRMS', 1),
(1079, 'BEAR BRAND CHOCO 33GRMS', 1005, 9.75, 13.00, 'DP-BBC-33GRMS', 1),
(1080, 'NESCAFE CREAMY LATTE TWIN 40GRMS', 1005, 11.25, 15.00, 'DP-NCLT-40GRMS', 1),
(1081, 'NESCAFE ORINAL TWIN 40GRMS', 1005, 11.25, 15.00, 'DP-NOT-40GRMS', 1),
(1082, 'KOPIKO BROWN TWIN 40GRMS', 1005, 11.00, 15.00, 'DP-KBT-40GRMS', 1),
(1083, 'KOPIKO BLACK TWIN 40GRMS', 1005, 11.00, 15.00, 'DP-KBLCKT-40GRMS', 1),
(1084, 'GREAT TASTE WHITE TWIN 40GRMS', 1005, 11.00, 15.00, 'DP-GTWT-40GRMS', 1),
(1085, 'GREAT TASTE CHOCOLATE TWIN 40GRMS', 1005, 11.00, 15.00, 'DP-GTCT-40GRMS', 1),
(1086, 'ENERGEN CHOCOLATE 40GRMS', 1005, 9.75, 12.00, 'DP-EC-40GRMS', 1),
(1087, 'ENERGEN VANILLA 40GRMS', 1005, 9.75, 12.00, 'DP-EV-40GRMS', 1),
(1088, 'CHAMPION CHOCOLATE TWIN 64GRMS', 1005, 16.00, 20.00, 'DP-CCT-64GRMS', 1),
(1089, 'C2 SOLO 230ML', 1006, 16.00, 18.00, 'BEV-C2S-230ML', 1),
(1090, 'MINERAL SMALL 350ML', 1006, 8.00, 10.00, 'BEV-MS-350ML', 1),
(1091, 'SPRITE KASALO 720ML', 1006, 28.00, 33.00, 'BEV-SK-720ML', 1),
(1092, 'COKE KASALO 720ML', 1006, 28.00, 33.00, 'BEV-CK-720ML', 1),
(1093, 'ROYAL KASALO 720ML', 1006, 28.00, 33.00, 'BEV-RK-720ML', 1),
(1094, 'KERATIN GOLD 24GRMS', 1007, 8.00, 10.00, 'TL-KG-24GRMS', 1),
(1095, 'KERATIN PULA 20GRMS', 1007, 8.00, 10.00, 'TL-KP-20GRMS', 1),
(1096, 'GIVE SOAP 76GRMS', 1007, 22.00, 24.00, 'TL-GS-76GRMS', 1),
(1097, 'JOY BIG GREEN 40ML', 1008, 12.00, 14.00, 'CA-JBG-40ML', 1),
(1098, 'JOY BIG BLUE 40ML', 1008, 12.00, 14.00, 'CA-JBB-40ML', 1),
(1099, 'JOY BIG YELLOW 40ML', 1008, 12.00, 14.00, 'CA-JBY-40ML', 1),
(1100, 'JOY SMALL GREEN 19ML', 1008, 6.25, 8.00, 'CA-JSG-19ML', 1),
(1101, 'JOY SMALL BLUE 19ML', 1008, 6.25, 8.00, 'CA-JSB-19ML', 1);

-- --------------------------------------------------------

--
-- Table structure for table `stock_transaction`
--

CREATE TABLE `stock_transaction` (
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `transaction_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL,
  `buy_amount` decimal(10,2) DEFAULT NULL,
  `sell_amount` decimal(11,2) DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `related_tid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_transaction`
--

INSERT INTO `stock_transaction` (`transaction_id`, `product_id`, `transaction_type`, `quantity`, `transaction_date`, `buy_amount`, `sell_amount`, `expiry_date`, `related_tid`) VALUES
(1, 1000, 'IN', 2, '2026-04-23 17:24:28', 16.00, 0.00, NULL, NULL),
(2, 1000, 'IN', 5, '2026-04-23 17:25:01', 40.00, 0.00, NULL, NULL),
(3, 1000, 'OUT', 3, '2026-04-23 17:25:20', NULL, 30.00, NULL, NULL),
(4, 1013, 'IN', 9, '2026-05-05 18:44:18', 180.00, 0.00, NULL, NULL),
(5, 1017, 'IN', 13, '2026-05-05 18:47:02', 338.00, 0.00, '2026-06-01', NULL),
(6, 1000, 'IN', 3, '2026-05-06 07:03:17', 24.00, NULL, NULL, NULL),
(7, 1000, 'OUT', 2, '2026-05-06 07:03:27', NULL, 20.00, NULL, NULL),
(8, 1000, 'IN', 4, '2026-05-06 07:09:59', 32.00, NULL, '2029-06-05', NULL),
(9, 1000, 'OUT', 3, '2026-05-06 08:32:59', NULL, 30.00, NULL, NULL),
(10, 1001, 'IN', 5, '2026-05-06 09:25:04', 30.00, NULL, '2026-06-01', NULL),
(11, 1000, 'OUT', 4, '2026-05-06 09:30:50', NULL, 40.00, NULL, NULL),
(12, 1000, 'IN', 3, '2026-05-06 09:55:39', 24.00, NULL, '2026-07-01', NULL),
(13, 1002, 'IN', 15, '2026-05-06 10:03:10', 93.00, NULL, '2026-06-03', NULL),
(14, 1000, 'IN', 1000, '2026-05-06 10:06:15', 8000.00, NULL, NULL, NULL),
(15, 1000, 'OUT', 900, '2026-05-06 10:06:26', NULL, 9000.00, NULL, NULL),
(16, 1000, 'OUT', 100, '2026-05-06 10:09:02', NULL, 1000.00, NULL, NULL),
(17, 1005, 'IN', 2, '2026-05-06 14:05:34', 0.00, NULL, '2026-06-11', NULL),
(18, 1006, 'IN', 11, '2026-05-06 14:13:09', 94.00, NULL, '2026-06-01', NULL),
(19, 1005, 'OUT', 1, '2026-05-06 14:15:35', NULL, 8.00, NULL, NULL),
(20, 1001, 'OUT', 2, '2026-05-06 14:32:46', NULL, 16.00, NULL, NULL),
(21, 1000, 'IN', 1, '2026-05-06 14:33:18', 8.00, NULL, '2026-05-31', NULL),
(22, 1000, 'IN', 1, '2026-05-06 16:56:52', 8.00, NULL, NULL, NULL),
(23, 1000, 'OUT', 1, '2026-05-06 16:56:56', NULL, 10.00, NULL, NULL),
(24, 1000, 'IN', 1, '2026-05-06 16:57:13', 8.00, NULL, NULL, NULL),
(25, 1000, 'IN', 1, '2026-05-07 21:27:57', 8.00, NULL, NULL, NULL),
(26, 1000, 'IN', 1, '2026-05-08 09:34:42', 8.00, NULL, NULL, NULL),
(27, 1000, 'OUT', 1, '2026-05-08 09:58:36', NULL, 10.00, NULL, NULL),
(28, 1000, 'IN', 2, '2026-05-08 10:31:40', 16.00, NULL, NULL, NULL),
(29, 1000, 'OUT', 2, '2026-05-08 10:32:08', NULL, 20.00, NULL, NULL),
(30, 1000, 'OUT', 2, '2026-05-08 10:59:57', NULL, 20.00, NULL, NULL),
(31, 1000, 'OUT', 1, '2026-05-08 11:02:33', NULL, 10.00, NULL, NULL),
(32, 1000, 'IN', 3, '2026-05-08 11:05:04', 24.00, NULL, NULL, NULL),
(33, 1000, 'OUT', 1, '2026-05-08 11:06:15', NULL, 10.00, NULL, NULL),
(34, 1000, 'OUT', 1, '2026-05-08 11:16:46', NULL, 10.00, NULL, NULL),
(35, 1000, 'OUT', 1, '2026-05-08 11:22:53', NULL, 10.00, NULL, NULL),
(36, 1000, 'IN', 3, '2026-05-08 11:26:52', 24.00, NULL, NULL, NULL),
(37, 1006, 'OUT', 2, '2026-05-08 11:27:15', NULL, 20.00, NULL, NULL),
(38, 1002, 'OUT', 2, '2026-05-08 11:28:54', NULL, 16.00, NULL, NULL),
(39, 1005, 'IN', 1, '2026-05-08 15:32:40', 0.00, NULL, '2026-06-01', NULL),
(40, 1005, 'IN', 1, '2026-05-08 15:33:09', 0.00, NULL, '2028-01-01', NULL),
(41, 1008, 'IN', 21, '2026-05-08 15:33:39', 137.00, NULL, '2030-01-01', NULL),
(42, 1000, 'IN', 1, '2026-05-08 15:39:07', 8.00, NULL, '2026-06-08', NULL),
(43, 1000, 'IN', 1, '2026-05-08 15:42:58', 8.00, NULL, '2026-09-01', NULL),
(44, 1000, 'OUT', 1, '2026-05-08 15:43:44', NULL, 10.00, NULL, NULL),
(45, 1000, 'IN', 1, '2026-05-08 15:47:59', 8.00, NULL, '2026-05-09', NULL),
(46, 1000, 'OUT', 1, '2026-05-08 17:09:54', NULL, 10.00, NULL, NULL),
(47, 1007, 'IN', 5, '2026-05-08 17:12:50', 0.00, NULL, '2030-01-08', NULL),
(48, 1007, 'OUT', 1, '2026-05-08 17:13:27', NULL, 8.00, NULL, NULL),
(49, 1000, 'OUT', 10, '2026-05-08 17:17:21', NULL, 100.00, NULL, NULL),
(50, 1001, 'OUT', 5, '2026-05-08 17:22:52', NULL, 40.00, NULL, NULL),
(51, 1000, 'IN', 3, '2026-05-08 17:26:24', 24.00, NULL, '2027-08-19', NULL),
(52, 1001, 'IN', 5, '2026-05-08 17:26:39', 30.00, NULL, '2032-01-01', NULL),
(53, 1002, 'IN', 1, '2026-05-08 17:27:46', 6.00, NULL, '2026-05-09', NULL),
(54, 1000, 'IN', 1, '2026-05-08 17:33:37', 8.00, NULL, '2026-06-01', NULL),
(55, 1000, 'IN', 31, '2026-05-08 17:48:16', 248.00, NULL, NULL, NULL),
(56, 1000, 'IN', 1, '2026-05-08 17:50:24', 8.00, NULL, '2026-05-29', 57),
(57, 1000, 'ADJUSTMENT', -1, '2026-05-09 15:18:36', -8.00, -10.00, NULL, 56),
(58, 1007, 'OUT', 1, '2026-05-11 00:38:39', NULL, 8.00, NULL, NULL),
(59, 1007, 'OUT', 1, '2026-05-11 00:39:07', NULL, 9.00, NULL, NULL);

--
-- Triggers `stock_transaction`
--
DELIMITER $$
CREATE TRIGGER `trg_calculate_buy_amount` BEFORE INSERT ON `stock_transaction` FOR EACH ROW BEGIN
    -- Only calculate if transaction_type is NOT "IN"
    IF NEW.transaction_type != 'OUT' THEN
        SET NEW.buy_amount = NEW.quantity * (
            SELECT cost 
            FROM product
            WHERE product_id = NEW.product_id
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_calculate_sell_amount` BEFORE INSERT ON `stock_transaction` FOR EACH ROW BEGIN
    -- Only calculate if transaction_type is NOT "IN"
    IF NEW.transaction_type != 'IN' THEN
        SET NEW.sell_amount = NEW.quantity * (
            SELECT price 
            FROM product
            WHERE product_id = NEW.product_id
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(250) NOT NULL,
  `role` varchar(20) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'RODal', '$2y$10$lYPHm8i61lVs6iaVLbVZ..XDqlK8Xd4SRkpLcjtHiEDk0ZULQgmDS', 'owner'),
(2, 'SysAdmin', '$2y$10$CL.I.q1DBpoxgvzjkEgLpu4l67KodIizGjgDtvJ1JQOaNSZb.eGZq', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notify_product` (`product_id`),
  ADD KEY `fk_notify_transaction` (`transaction_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `pd_cat` (`category_id`);

--
-- Indexes for table `stock_transaction`
--
ALTER TABLE `stock_transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `tr_pd` (`product_id`),
  ADD KEY `fk_related_transaction` (`related_tid`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1011;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1102;

--
-- AUTO_INCREMENT for table `stock_transaction`
--
ALTER TABLE `stock_transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notify_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notify_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `stock_transaction` (`transaction_id`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `pd_cat` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `stock_transaction`
--
ALTER TABLE `stock_transaction`
  ADD CONSTRAINT `fk_related_transaction` FOREIGN KEY (`related_tid`) REFERENCES `stock_transaction` (`transaction_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tr_pd` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
