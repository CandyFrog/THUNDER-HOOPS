-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 01:22 AM
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
-- Database: `basketball_arcade`
--

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `player1_score` int(11) NOT NULL,
  `player2_score` int(11) NOT NULL,
  `winner` varchar(20) NOT NULL,
  `game_duration` int(11) NOT NULL,
  `played_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `player1_score`, `player2_score`, `winner`, `game_duration`, `played_at`, `notes`) VALUES
(1, 15, 12, 'Player 1', 120, '2026-01-25 05:40:14', 'Game pertama - Player 1 menang'),
(2, 10, 10, 'Draw', 120, '2026-01-25 05:40:14', 'Pertandingan seru - berakhir seri'),
(3, 8, 14, 'Player 2', 120, '2026-01-25 05:40:14', 'Player 2 dominan'),
(4, 20, 15, 'Player 1', 120, '2026-01-25 05:40:14', 'Skor tinggi!'),
(5, 12, 18, 'Player 2', 120, '2026-01-25 05:40:14', 'Player 2 comeback'),
(6, 16, 9, 'Player 1', 120, '2026-01-25 05:40:14', 'Player 1 unggul'),
(7, 11, 11, 'Draw', 120, '2026-01-25 05:40:14', 'Seri lagi'),
(8, 13, 17, 'Player 2', 120, '2026-01-25 05:40:14', 'Player 2 konsisten'),
(9, 19, 14, 'Player 1', 120, '2026-01-25 05:40:14', 'Player 1 agresif'),
(10, 7, 12, 'Player 2', 120, '2026-01-25 05:40:14', 'Player 2 efektif'),
(11, 20, 12, 'Player 1', 120, '2026-01-25 06:11:00', 'Test from web');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$V9yBgoNv8yRAaKXpOz9apOhRfdA98rWIeWZU5/5boR8IV33fKc0ge', 'Administrator', 'admin', '2026-01-25 05:40:14'),
(2, 'johndoe', '$2y$10$KjWR0.KQMACAv/SH8T52rO7nnhbQvOSbUkQOzzJ7E34FLeYOiWqgK', 'John Doe', 'user', '2026-01-25 05:45:13'),
(3, 'gemintangshyam', '$2y$10$JE.pps22BLldVEAgMnd74usGp5zjHQBkY1AKR7cAEg.RleCM8TEye', 'gemintang', 'user', '2026-01-28 06:53:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
