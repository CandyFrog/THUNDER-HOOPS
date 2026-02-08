-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 08, 2026 at 03:13 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `basketball_arcade`
--

-- --------------------------------------------------------

--
-- Table structure for table `match_data`
--

CREATE TABLE `match_data` (
  `id` int NOT NULL,
  `skor_kiri` int DEFAULT '0',
  `skor_kanan` int DEFAULT '0',
  `durasi` int DEFAULT '0',
  `pemenang` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `match_data`
--

INSERT INTO `match_data` (`id`, `skor_kiri`, `skor_kanan`, `durasi`, `pemenang`, `created_at`) VALUES
(1, 1, 2, 10, 'KANAN', '2026-02-04 16:27:55'),
(2, 0, 2, 5, 'KANAN', '2026-02-04 16:29:45'),
(3, 0, 0, 10, 'SERI', '2026-02-04 16:30:18'),
(4, 2, 3, 5, 'KANAN', '2026-02-07 15:08:50'),
(5, 2, 2, 5, 'SERI', '2026-02-07 15:10:37'),
(6, 5, 4, 10, 'KIRI', '2026-02-07 15:12:47'),
(7, 0, 1, 10, 'KANAN', '2026-02-07 15:27:11'),
(8, 6, 1, 11, 'KIRI', '2026-02-07 15:29:47'),
(9, 0, 9, 11, 'KANAN', '2026-02-08 06:34:01'),
(10, 0, 0, 10, 'SERI', '2026-02-08 06:37:55'),
(11, 11, 11, 10, 'SERI', '2026-02-08 06:43:38'),
(12, 5, 5, 10, 'SERI', '2026-02-08 07:12:14'),
(13, 10, 11, 10, 'KANAN', '2026-02-08 07:58:33'),
(14, 13, 13, 10, 'SERI', '2026-02-08 07:59:36');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1, 'match_duration', '10'),
(2, 'game_command', 'idle');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`, `foto_profil`) VALUES
(1, 'admin', '$2y$10$V9yBgoNv8yRAaKXpOz9apOhRfdA98rWIeWZU5/5boR8IV33fKc0ge', 'Administrator', 'admin', '2026-01-25 05:40:14', NULL),
(2, 'johndoe', '$2y$10$KjWR0.KQMACAv/SH8T52rO7nnhbQvOSbUkQOzzJ7E34FLeYOiWqgK', 'John Doe', 'user', '2026-01-25 05:45:13', NULL),
(3, 'gemintangshyam', '$2y$10$JE.pps22BLldVEAgMnd74usGp5zjHQBkY1AKR7cAEg.RleCM8TEye', 'gemintang', 'user', '2026-01-28 06:53:58', NULL),
(4, 'Rei_Ayanami', '$2y$10$cXjW/il6Nf.YZefaL4bNdOlEZfQEycRvBHccUx5lodykYk1kLWWT.', 'Rei Ayanami', 'admin', '2026-02-04 06:56:59', 'profile_4_1770191298.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `match_data`
--
ALTER TABLE `match_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `match_data`
--
ALTER TABLE `match_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
