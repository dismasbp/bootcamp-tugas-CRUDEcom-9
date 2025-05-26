-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 05:36 PM
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
-- Database: `bootcamp`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `catid` int(11),
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `stock` int(11) NOT NULL,
  FOREIGN KEY (`catid`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `userid` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `orderid` int(11) NOT NULL,
  `productid` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  FOREIGN KEY (`orderid`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`productid`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Add Users
INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(1, 'John Doe', 'john.doe@example.com', 'password123'),
(2, 'Jane Doe', 'jane.doe@example.com', 'pass456'),
(3, 'Jude Doe', 'jude.doe@example.com', 'secure789');

-- Add categories
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Dell'),
(2, 'Apple'),
(3, 'Asus'),
(4, 'Lenovo'),
(5, 'HP'),
(6, 'Microsoft'),
(7, 'Acer'),
(8, 'Razer'),
(9, 'LG'),
(10, 'Samsung'),
(11, 'MSI');

-- Add Products
INSERT INTO `products` (`id`, `catid`, `name`, `price`, `description`, `photo`, `stock`) VALUES
(1, 1, 'Dell XPS 13', 14490000, 'Sleek design with powerful performance, perfect for professionals on the go.', 'img/product/photo-1517336714731-489689fd1ca8.avif', 15),
(2, 2, 'Apple MacBook Air M1', 15500000, 'High-end laptop with latest processors, ideal for creative professionals.', 'img/product/15-macbook-air-vs-13-air-weight-size.webp', 20),
(3, 3, 'Asus ROG Zephyrus G14', 23500000, 'Gaming laptop with fast CPU and dedicated GPU.', 'img/product/Intro-ASUS-ROG-Zephyrus-G14.webp', 10),
(4, 4, 'Lenovo ThinkPad X1 Carbon', 27000000, 'Durable laptop with great keyboard and excellent battery life.', 'img/product/vmmifhcodk8bu3j59juxcadhuac6rp932193.avif', 25),
(5, 5, 'HP Spectre x360', 19000000, 'Convertible laptop with stunning touchscreen display.', 'img/product/spectre-hp-x360.png', 12),
(6, 6, 'Microsoft Surface Laptop 4', 21000000, 'Thin and light laptop with great display and Windows integration.', 'img/product/Gear-Surface-Book-3-4-ways-SOURCE-Microsoft.webp', 12),
(7, 7, 'Acer Swift 3', 9500000, 'Affordable ultrabook with solid performance and battery life.', 'img/product/acer-laptop-swift-3-kick-start-your-productivity-l-2.png', 12),
(8, 8, 'Razer Blade 15', 28000000, 'Premium gaming laptop with sleek design and powerful hardware.', 'img/product/730x480-img-82047-laptop-razer-blade-15-2023.jpg', 12),
(9, 9, 'LG Gram 17', 18500000, 'Extremely lightweight laptop with a large screen and good battery life.', 'img/product/D-02.jpg', 25),
(10, 10, 'Samsung Galaxy Book Pro', 16500000, 'Lightweight laptop with AMOLED display and long battery life.', 'img/product/hk_en-feature--nbsp-374907448.jpeg', 15),
(11, 11, 'MSI Prestige 14', 17500000, 'High-performance laptop with advanced cooling system.', 'img/product/fa64aa70-7d30-4e15-bba2-9e548409edb1.jpg', 20),
(12, 2, 'Apple MacBook Pro 14" M1 Pro', 32000000, 'High-end laptop with latest processors, ideal for creative professionals.', 'img/product/macbook-pro-2021-cnet-review-12.webp', 10),
(13, 1, 'Dell Inspiron 15 7000', 14000000, 'Versatile laptop with a large screen and powerful performance.', 'img/product/dellinspironblack1-100812154-orig.webp', 10),
(14, 3, 'Asus VivoBook S14', 11000000, 'Stylish laptop with good performance for everyday tasks.', 'img/product/download.png', 25),
(15, 4, 'Lenovo Legion 5', 20500000, 'Gaming laptop with advanced cooling system and powerful graphics.', 'img/product/3400450d-1dcc-4eb7-832b-c80fef70b373.jpg', 12),
(16, 5, 'HP Envy 13', 12500000, 'Ultraportable laptop with premium build quality and solid performance.', 'img/product/GopCyNjhvkFXGvg5yDDgBE.jpg', 12),
(17, 6, 'Microsoft Surface Pro 8', 18500000, '2-in-1 tablet with detachable keyboard and reliable performance.', 'img/product/Microsoft-Surface-Pro-8-001.jpg', 12),
(18, 7, 'Acer Predator Helios 300', 22000000, 'Gaming laptop with high refresh rate and powerful hardware.', 'img/product/3ff81d4b-0da6-4c5f-b56e-48dec492a154.jpg', 12),
(19, 8, 'Razer Blade Stealth 13', 21000000, 'Ultraportable gaming laptop with a thin design and maximum performance.', 'img/product/razer-blade-stealth-2020-usp-gaming-performance-mobile.jpg', 25);

-- Add Orders
INSERT INTO `orders` (`id`, `userid`, `total`) VALUES
(1, 1, 14490000),
(2, 2, 93500000),
(3, 3, 126500000),
(4, 1, 40000000);

-- Add Order Details
INSERT INTO `order_details` (`id`, `orderid`, `productid`, `qty`) VALUES
(1, 1, 1, 1),
(2, 2, 3, 2),
(3, 2, 2, 3),
(4, 3, 2, 1),
(5, 3, 4, 2),
(6, 3, 5, 3),
(7, 4, 6, 1),
(8, 4, 7, 2);