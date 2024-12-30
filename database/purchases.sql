-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2024 at 09:16 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `amimpharmacy`
--

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product` varchar(255) NOT NULL,
  `batch_number` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cost_price` decimal(8,2) DEFAULT NULL,
  `quantity` varchar(255) NOT NULL,
  `expiry_date` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `product`, `batch_number`, `category_id`, `supplier_id`, `cost_price`, `quantity`, `expiry_date`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol', 'DE-4567', 1, 1, '5000.00', '0', '2026-01-17', '1734423640.jpg', '2024-12-17 05:20:40', '2024-12-18 19:05:01'),
(2, 'amoxicillin', 'AM561', 8, 1, '6700.00', '1', '2024-12-02', '1734639134.jpg', '2024-12-17 09:20:35', '2024-12-20 14:00:37'),
(3, 'Azithromycin', '096889', 2, 1, '70000.00', '0', '2024-12-26', NULL, '2024-12-18 07:57:52', '2024-12-20 13:40:23'),
(4, 'Mseto', '87643', 5, 1, '134000.00', '234', '2024-12-01', NULL, '2024-12-18 10:46:08', '2024-12-18 10:46:08'),
(5, 'Clonazepam', '4CD45', 2, 1, '5000.00', '40', '2024-12-19', '1734639054.jpg', '2024-12-19 17:10:54', '2024-12-19 17:10:54'),
(6, 'Chloquine', 'CT234', 9, 1, '70000.00', '281', '2025-01-10', '1734690881.png', '2024-12-20 07:34:23', '2024-12-22 09:56:22'),
(7, 'babaydffyd', '2345', 3, 1, '2000.00', '56', '2024-12-21', NULL, '2024-12-21 02:16:15', '2024-12-21 02:16:15'),
(8, 'sfdfedve', '2355', 1, 1, '345.00', '22', '2024-12-21', NULL, '2024-12-21 02:17:38', '2024-12-21 02:17:38'),
(9, 'PARACETAMOL', '1234', 6, 1, '200.00', '1000', '2027-09-19', NULL, '2024-12-21 10:16:44', '2024-12-21 10:16:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchases_category_id_foreign` (`category_id`),
  ADD KEY `purchases_supplier_id_foreign` (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
