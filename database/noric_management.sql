-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 29 Jan 2026 pada 02.21
-- Versi server: 10.6.24-MariaDB-cll-lve
-- Versi PHP: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uwjqdfka_noric_management`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `pin` varchar(20) NOT NULL,
  `scan_date` datetime NOT NULL,
  `status_scan` int(11) NOT NULL COMMENT '0=Masuk, 1=Pulang, dst (sesuai setting mesin)',
  `verify_mode` int(11) NOT NULL COMMENT '1=Finger, 4=Face, dst',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `absensi`
--

INSERT INTO `absensi` (`id`, `pin`, `scan_date`, `status_scan`, `verify_mode`, `created_at`) VALUES
(50, '1014', '2026-01-26 08:29:00', 0, 1, '2026-01-26 07:25:18'),
(51, '1014', '2026-01-26 08:32:00', 1, 1, '2026-01-26 07:28:55'),
(54, '1002', '2026-01-26 08:10:00', 0, 1, '2026-01-26 11:22:55'),
(55, '1013', '2026-01-26 08:10:00', 0, 1, '2026-01-26 11:23:19'),
(56, '1004', '2026-01-26 08:00:00', 0, 1, '2026-01-26 11:23:32'),
(57, '1010', '2026-01-26 08:00:00', 0, 1, '2026-01-26 11:24:29'),
(58, '1007', '2026-01-26 08:30:00', 0, 1, '2026-01-26 11:24:49'),
(59, '1011', '2026-01-26 08:00:00', 0, 1, '2026-01-26 11:25:30'),
(60, '1012', '2026-01-26 08:00:00', 0, 1, '2026-01-26 11:25:43'),
(61, '1008', '2026-01-26 12:31:00', 0, 1, '2026-01-26 11:26:36'),
(62, '1009', '2026-01-26 08:00:00', 0, 1, '2026-01-26 11:27:06'),
(63, '1003', '2026-01-26 12:30:00', 0, 1, '2026-01-26 11:42:13'),
(65, '1002', '2026-01-26 16:30:00', 1, 1, '2026-01-26 11:44:30'),
(66, '1013', '2026-01-26 16:10:00', 1, 1, '2026-01-26 11:45:07'),
(67, '1004', '2026-01-26 16:05:00', 1, 1, '2026-01-26 11:45:27'),
(68, '1006', '2026-01-26 16:00:00', 1, 1, '2026-01-26 11:45:33'),
(69, '1010', '2026-01-26 16:00:00', 1, 1, '2026-01-26 11:45:54'),
(70, '1007', '2026-01-26 16:30:00', 1, 1, '2026-01-26 11:46:14'),
(71, '1011', '2026-01-26 16:00:00', 1, 1, '2026-01-26 11:46:40'),
(72, '1012', '2026-01-26 16:00:00', 1, 1, '2026-01-26 11:46:53'),
(73, '1003', '2026-01-26 16:00:00', 1, 1, '2026-01-26 11:47:08'),
(74, '1008', '2026-01-26 16:00:00', 1, 1, '2026-01-26 11:47:20'),
(75, '1009', '2026-01-26 16:00:00', 1, 1, '2026-01-26 11:47:33'),
(76, '1001', '2026-01-26 17:10:00', 1, 1, '2026-01-26 11:51:18'),
(77, '1001', '2026-01-26 08:10:00', 0, 1, '2026-01-26 11:51:30'),
(89, '1001', '2026-01-26 11:33:00', 3, 1, '2026-01-26 12:04:14'),
(93, '1001', '2026-01-26 12:33:00', 2, 1, '2026-01-26 12:04:14'),
(100, '1006', '2026-01-26 08:06:00', 0, 1, '2026-01-26 13:05:26'),
(102, '1001', '2026-01-27 08:00:00', 0, 1, '2026-01-27 01:31:02'),
(103, '1002', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:34:42'),
(104, '1004', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:37:27'),
(106, '1010', '2026-01-27 12:31:00', 3, 4, '2026-01-27 05:31:00'),
(107, '1013', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:41:05'),
(108, '1011', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:42:11'),
(109, '1012', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:43:33'),
(110, '1006', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:44:29'),
(111, '1009', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:46:12'),
(112, '1022', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:47:54'),
(113, '1008', '2026-01-27 08:20:00', 0, 4, '2026-01-27 01:20:00'),
(114, '1008', '2026-01-27 11:30:00', 3, 4, '2026-01-27 04:30:00'),
(115, '1025', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:53:08'),
(117, '1018', '2026-01-27 08:00:00', 0, 4, '2026-01-27 01:56:10'),
(126, '1012', '2026-01-27 11:28:00', 2, 1, '2026-01-27 04:28:24'),
(127, '1004', '2026-01-27 11:28:00', 2, 4, '2026-01-27 04:28:40'),
(128, '1010', '2026-01-27 11:28:00', 2, 4, '2026-01-27 04:28:43'),
(131, '1012', '2026-01-27 12:29:00', 3, 4, '2026-01-27 05:29:00'),
(135, '1022', '2026-01-27 11:30:00', 0, 4, '2026-01-27 04:30:40'),
(136, '1013', '2026-01-27 11:30:00', 2, 1, '2026-01-27 04:31:07'),
(137, '1011', '2026-01-27 11:30:00', 2, 4, '2026-01-27 04:31:10'),
(138, '1018', '2026-01-27 11:31:00', 2, 4, '2026-01-27 04:31:23'),
(140, '1006', '2026-01-27 11:31:00', 2, 4, '2026-01-27 04:31:51'),
(145, '1022', '2026-01-27 11:33:00', 0, 4, '2026-01-27 04:33:27'),
(149, '1022', '2026-01-27 11:34:00', 0, 1, '2026-01-27 04:35:06'),
(153, '1006', '2026-01-27 11:43:00', 3, 4, '2026-01-27 04:43:33'),
(154, '1002', '2026-01-27 11:48:00', 3, 4, '2026-01-27 04:48:48'),
(156, '1013', '2026-01-27 12:30:00', 3, 4, '2026-01-27 05:30:42'),
(157, '1018', '2026-01-27 12:30:00', 3, 4, '2026-01-27 05:30:57'),
(159, '1004', '2026-01-27 12:30:00', 3, 4, '2026-01-27 05:31:11'),
(160, '1022', '2026-01-27 12:32:00', 0, 4, '2026-01-27 05:32:47'),
(162, '1010', '2026-01-27 08:00:00', 0, 1, '2026-01-27 07:00:19'),
(166, '1027', '2026-01-26 08:30:00', 0, 1, '2026-01-27 07:13:02'),
(167, '1027', '2026-01-26 16:10:00', 0, 1, '2026-01-27 07:13:26'),
(168, '1027', '2026-01-27 11:32:00', 2, 1, '2026-01-27 07:14:29'),
(169, '1027', '2026-01-27 12:31:00', 3, 1, '2026-01-27 07:14:42'),
(170, '1027', '2026-01-27 08:10:00', 0, 1, '2026-01-27 07:16:17'),
(173, '1010', '2026-01-27 16:03:00', 1, 4, '2026-01-27 09:03:35'),
(174, '1009', '2026-01-27 16:03:00', 1, 4, '2026-01-27 09:03:47'),
(175, '1011', '2026-01-27 16:06:00', 1, 4, '2026-01-27 09:06:50'),
(176, '1013', '2026-01-27 16:06:00', 1, 4, '2026-01-27 09:07:09'),
(177, '1006', '2026-01-27 16:07:00', 1, 4, '2026-01-27 09:07:28'),
(178, '1006', '2026-01-27 16:08:00', 1, 4, '2026-01-27 09:08:33'),
(179, '1004', '2026-01-27 16:09:00', 1, 4, '2026-01-27 09:09:59'),
(180, '1004', '2026-01-27 16:10:00', 1, 4, '2026-01-27 09:10:22'),
(181, '1001', '2026-01-27 16:55:00', 1, 1, '2026-01-27 09:55:52'),
(182, '1012', '2026-01-27 17:13:00', 1, 1, '2026-01-27 10:14:15'),
(183, '1022', '2026-01-27 17:36:00', 1, 4, '2026-01-27 10:37:01'),
(184, '1022', '2026-01-27 18:31:00', 1, 4, '2026-01-27 11:32:06'),
(185, '1008', '2026-01-27 18:38:00', 1, 1, '2026-01-27 11:38:42'),
(186, '1002', '2026-01-27 19:46:00', 1, 4, '2026-01-27 12:47:03'),
(187, '1002', '2026-01-27 19:48:00', 1, 4, '2026-01-27 12:48:51'),
(188, '1002', '2026-01-27 19:49:00', 1, 4, '2026-01-27 12:50:07'),
(189, '1022', '2026-01-27 19:50:00', 1, 4, '2026-01-27 12:50:34'),
(190, '1018', '2026-01-27 19:56:00', 1, 4, '2026-01-27 12:56:52'),
(191, '1018', '2026-01-27 19:57:00', 1, 4, '2026-01-27 12:57:27'),
(193, '1027', '2026-01-27 16:00:00', 1, 1, '2026-01-27 19:59:41'),
(198, '1010', '2026-01-28 07:43:00', 0, 4, '2026-01-28 00:43:40'),
(199, '1009', '2026-01-28 07:44:00', 0, 4, '2026-01-28 00:44:44'),
(200, '1001', '2026-01-28 07:44:00', 0, 1, '2026-01-28 00:44:57'),
(201, '1006', '2026-01-28 07:53:00', 0, 4, '2026-01-28 00:53:55'),
(202, '1006', '2026-01-28 07:54:00', 0, 4, '2026-01-28 00:54:26'),
(203, '1006', '2026-01-28 07:55:00', 0, 4, '2026-01-28 00:55:27'),
(204, '1011', '2026-01-28 08:00:00', 0, 1, '2026-01-28 01:00:51'),
(205, '1004', '2026-01-28 08:01:00', 0, 4, '2026-01-28 01:01:48'),
(206, '1006', '2026-01-28 08:01:00', 0, 4, '2026-01-28 01:02:13'),
(207, '1022', '2026-01-28 08:02:00', 0, 4, '2026-01-28 01:02:34'),
(208, '1013', '2026-01-28 08:02:00', 0, 4, '2026-01-28 01:03:10'),
(209, '1002', '2026-01-28 08:04:00', 0, 4, '2026-01-28 01:04:55'),
(210, '1018', '2026-01-28 08:16:00', 0, 4, '2026-01-28 01:16:35'),
(211, '1012', '2026-01-28 08:20:00', 0, 1, '2026-01-28 01:20:31'),
(212, '1023', '2026-01-28 08:30:00', 0, 4, '2026-01-28 01:31:11'),
(213, '1024', '2026-01-28 08:31:00', 0, 4, '2026-01-28 01:32:17'),
(214, '1007', '2026-01-28 08:34:00', 0, 4, '2026-01-28 01:34:24'),
(215, '1025', '2026-01-28 08:36:00', 0, 4, '2026-01-28 01:36:54'),
(216, '1008', '2026-01-28 08:59:00', 0, 1, '2026-01-28 01:59:46'),
(217, '1010', '2026-01-28 11:29:00', 0, 4, '2026-01-28 04:30:04'),
(218, '1009', '2026-01-28 11:30:00', 0, 4, '2026-01-28 04:30:44'),
(219, '1004', '2026-01-28 11:30:00', 0, 4, '2026-01-28 04:31:04'),
(220, '1011', '2026-01-28 11:31:00', 0, 4, '2026-01-28 04:31:37'),
(221, '1007', '2026-01-28 11:31:00', 0, 4, '2026-01-28 04:31:57'),
(222, '1013', '2026-01-28 11:31:00', 0, 4, '2026-01-28 04:32:06'),
(223, '1023', '2026-01-28 11:31:00', 0, 4, '2026-01-28 04:32:12'),
(224, '1024', '2026-01-28 11:32:00', 0, 4, '2026-01-28 04:32:26'),
(225, '1025', '2026-01-28 11:32:00', 0, 4, '2026-01-28 04:32:43'),
(226, '1006', '2026-01-28 11:32:00', 0, 4, '2026-01-28 04:32:53'),
(227, '1022', '2026-01-28 11:32:00', 0, 4, '2026-01-28 04:33:20'),
(228, '1004', '2026-01-28 11:34:00', 0, 4, '2026-01-28 04:34:32'),
(229, '1008', '2026-01-28 11:37:00', 0, 4, '2026-01-28 04:38:09'),
(230, '1002', '2026-01-28 11:37:00', 0, 4, '2026-01-28 04:38:18'),
(231, '1018', '2026-01-28 11:38:00', 0, 4, '2026-01-28 04:38:27'),
(232, '1001', '2026-01-28 11:40:00', 0, 1, '2026-01-28 04:40:29'),
(233, '1021', '2026-01-28 11:40:00', 0, 4, '2026-01-28 04:40:45'),
(234, '1018', '2026-01-28 12:09:00', 0, 4, '2026-01-28 05:10:00'),
(235, '1008', '2026-01-28 12:23:00', 0, 1, '2026-01-28 05:24:12'),
(236, '1009', '2026-01-28 12:23:00', 0, 4, '2026-01-28 05:24:19'),
(237, '1002', '2026-01-28 12:24:00', 0, 4, '2026-01-28 05:25:08'),
(238, '1004', '2026-01-28 12:25:00', 0, 4, '2026-01-28 05:25:23'),
(239, '1011', '2026-01-28 12:25:00', 0, 1, '2026-01-28 05:25:29'),
(240, '1010', '2026-01-28 12:25:00', 0, 4, '2026-01-28 05:25:39'),
(241, '1006', '2026-01-28 12:25:00', 0, 4, '2026-01-28 05:25:50'),
(242, '1013', '2026-01-28 12:26:00', 0, 4, '2026-01-28 05:26:46'),
(243, '1019', '2026-01-28 12:26:00', 0, 4, '2026-01-28 05:27:01'),
(244, '1019', '2026-01-28 12:27:00', 0, 4, '2026-01-28 05:27:30'),
(245, '1021', '2026-01-28 12:27:00', 0, 4, '2026-01-28 05:28:10'),
(246, '1022', '2026-01-28 12:28:00', 0, 4, '2026-01-28 05:28:30'),
(247, '1007', '2026-01-28 12:28:00', 0, 4, '2026-01-28 05:28:44'),
(248, '1012', '2026-01-28 12:29:00', 0, 1, '2026-01-28 05:29:27'),
(249, '1005', '2026-01-28 12:35:00', 0, 4, '2026-01-28 05:35:24'),
(250, '1024', '2026-01-28 12:35:00', 0, 4, '2026-01-28 05:35:45'),
(251, '1025', '2026-01-28 12:35:00', 0, 4, '2026-01-28 05:36:22'),
(252, '1009', '2026-01-28 16:00:00', 0, 4, '2026-01-28 09:01:00'),
(253, '1010', '2026-01-28 16:01:00', 0, 4, '2026-01-28 09:02:12'),
(254, '1006', '2026-01-28 16:03:00', 0, 4, '2026-01-28 09:03:30'),
(255, '1011', '2026-01-28 16:04:00', 0, 4, '2026-01-28 09:04:52'),
(256, '1024', '2026-01-28 16:05:00', 0, 4, '2026-01-28 09:05:56'),
(257, '1001', '2026-01-28 12:30:00', 3, 1, '2026-01-28 09:10:43'),
(258, '1012', '2026-01-28 16:26:00', 0, 1, '2026-01-28 09:26:27'),
(259, '1025', '2026-01-28 16:26:00', 0, 4, '2026-01-28 09:26:55'),
(260, '1007', '2026-01-28 16:32:00', 0, 4, '2026-01-28 09:32:40'),
(261, '1013', '2026-01-26 08:05:00', 0, 1, '2026-01-28 09:34:18'),
(262, '1007', '2026-01-28 08:20:00', 0, 1, '2026-01-28 09:35:36'),
(263, '1005', '2026-01-28 16:56:00', 0, 4, '2026-01-28 09:57:07'),
(264, '1001', '2026-01-28 17:06:00', 0, 1, '2026-01-28 10:06:57'),
(265, '1013', '2026-01-28 17:37:00', 0, 1, '2026-01-28 10:37:38'),
(266, '1002', '2026-01-28 20:00:00', 0, 4, '2026-01-28 13:01:14'),
(267, '1002', '2026-01-28 20:01:00', 0, 4, '2026-01-28 13:01:27'),
(268, '1022', '2026-01-28 20:39:00', 0, 4, '2026-01-28 13:39:55'),
(269, '1021', '2026-01-28 20:45:00', 0, 4, '2026-01-28 13:45:54'),
(270, '1021', '2026-01-28 20:46:00', 0, 4, '2026-01-28 13:46:24'),
(271, '1004', '2026-01-28 21:01:00', 0, 4, '2026-01-28 14:02:06'),
(272, '1018', '2026-01-28 21:02:00', 0, 4, '2026-01-28 14:02:29'),
(273, '1008', '2026-01-28 21:23:00', 0, 4, '2026-01-28 14:24:04'),
(274, '1019', '2026-01-28 21:25:00', 0, 4, '2026-01-28 14:25:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `gaji_karyawan`
--

CREATE TABLE `gaji_karyawan` (
  `user_id` int(11) NOT NULL,
  `gaji_pokok` decimal(15,0) NOT NULL DEFAULT 0,
  `uang_makan` decimal(15,0) NOT NULL DEFAULT 0,
  `gaji_lembur` decimal(15,0) NOT NULL DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `gaji_karyawan`
--

INSERT INTO `gaji_karyawan` (`user_id`, `gaji_pokok`, `uang_makan`, `gaji_lembur`, `updated_at`) VALUES
(1, 0, 0, 0, NULL),
(18, 85000, 10000, 10000, NULL),
(19, 105000, 10000, 15000, NULL),
(20, 100000, 10000, 15000, NULL),
(21, 115000, 10000, 15000, NULL),
(22, 50000, 10000, 10000, NULL),
(23, 90000, 10000, 15000, NULL),
(24, 100000, 10000, 15000, NULL),
(25, 100000, 10000, 15000, NULL),
(26, 95000, 10000, 15000, NULL),
(27, 90000, 10000, 15000, NULL),
(28, 75000, 10000, 10000, NULL),
(29, 70000, 10000, 10000, NULL),
(30, 60000, 10000, 10000, NULL),
(48, 100000, 10000, 15000, NULL),
(50, 100000, 10000, 12000, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kasbon`
--

CREATE TABLE `kasbon` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `nominal` decimal(15,0) NOT NULL,
  `keterangan` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `tenor` int(11) NOT NULL DEFAULT 1 COMMENT 'Lama cicilan (minggu)',
  `terbayar` decimal(15,0) NOT NULL DEFAULT 0,
  `status_lunas` enum('Lunas','Belum') NOT NULL DEFAULT 'Belum',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kasbon`
--

INSERT INTO `kasbon` (`id`, `user_id`, `tanggal`, `nominal`, `keterangan`, `status`, `tenor`, `terbayar`, `status_lunas`, `created_at`) VALUES
(13, 27, '2026-01-27', 300000, 'Nicil bank tlg d acc boss', 'Pending', 3, 0, 'Belum', '2026-01-27 10:39:08'),
(14, 24, '2026-01-28', 10000, 'Ambil Uang Makan Harian', 'Approved', 1, 0, 'Belum', '2026-01-28 04:42:41'),
(15, 41, '2026-01-28', 10000, 'Uang makan', 'Approved', 1, 0, 'Belum', '2026-01-28 04:43:22'),
(16, 43, '2026-01-28', 10000, 'Uang makan', 'Approved', 1, 0, 'Belum', '2026-01-28 04:43:56'),
(17, 35, '2026-01-28', 20000, 'uang makan', 'Approved', 1, 0, 'Belum', '2026-01-28 04:45:40'),
(18, 38, '2026-01-28', 20000, 'Uang makan', 'Approved', 1, 0, 'Belum', '2026-01-28 04:46:52'),
(19, 28, '2026-01-28', 10000, 'Ambil Uang Makan Harian', 'Approved', 1, 0, 'Belum', '2026-01-28 04:47:22'),
(20, 26, '2026-01-28', 10000, 'Ambil Uang Makan Harian', 'Approved', 1, 0, 'Belum', '2026-01-28 05:13:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_pekerjaan`
--

CREATE TABLE `master_pekerjaan` (
  `id` int(11) NOT NULL,
  `jenis_pekerjaan` varchar(100) NOT NULL,
  `nama_motor` varchar(100) NOT NULL,
  `kategori` enum('Perorangan','Team') DEFAULT 'Perorangan',
  `harga` decimal(15,0) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `orderan`
--

CREATE TABLE `orderan` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `keterangan` text NOT NULL,
  `total_qty` int(11) DEFAULT 0,
  `status` enum('Pending','Proses','Selesai','Batal') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `qty_sent` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengiriman_items`
--

CREATE TABLE `pengiriman_items` (
  `id` int(11) NOT NULL,
  `pengiriman_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `qty_kirim` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produksi_borongan`
--

CREATE TABLE `produksi_borongan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_pekerjaan` varchar(100) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `total_upah` decimal(15,0) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings_jam_kerja`
--

CREATE TABLE `settings_jam_kerja` (
  `id` int(11) NOT NULL,
  `jam_masuk` time DEFAULT '08:00:00',
  `jam_pulang` time DEFAULT '17:00:00',
  `jam_istirahat_keluar` time DEFAULT '12:00:00',
  `jam_istirahat_masuk` time DEFAULT '13:00:00',
  `toleransi_telat` int(11) DEFAULT 15,
  `toleransi_pulang_awal` int(11) DEFAULT 0,
  `denda_per_menit` decimal(10,0) DEFAULT 5000,
  `lembur_min` int(11) DEFAULT 30,
  `lembur_max` int(11) DEFAULT 120,
  `lembur_pengurang` int(11) DEFAULT 0,
  `target_menit_full` int(11) DEFAULT 480,
  `target_menit_half` int(11) DEFAULT 240,
  `toleransi_full_day` int(11) NOT NULL DEFAULT 10 COMMENT 'Toleransi menit agar tetap dianggap Full Day',
  `min_menit_makan` int(11) NOT NULL DEFAULT 360 COMMENT 'Minimal menit kerja untuk dapat uang makan',
  `range_masuk_start` time DEFAULT '04:00:00',
  `range_masuk_end` time DEFAULT '10:30:00',
  `range_ist_out_start` time DEFAULT '10:30:01',
  `range_ist_out_end` time DEFAULT '12:29:59',
  `range_ist_in_start` time DEFAULT '12:30:00',
  `range_ist_in_end` time DEFAULT '14:00:00',
  `range_pulang_start` time DEFAULT '14:00:01',
  `range_pulang_end` time DEFAULT '23:59:59'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `settings_jam_kerja`
--

INSERT INTO `settings_jam_kerja` (`id`, `jam_masuk`, `jam_pulang`, `jam_istirahat_keluar`, `jam_istirahat_masuk`, `toleransi_telat`, `toleransi_pulang_awal`, `denda_per_menit`, `lembur_min`, `lembur_max`, `lembur_pengurang`, `target_menit_full`, `target_menit_half`, `toleransi_full_day`, `min_menit_makan`, `range_masuk_start`, `range_masuk_end`, `range_ist_out_start`, `range_ist_out_end`, `range_ist_in_start`, `range_ist_in_end`, `range_pulang_start`, `range_pulang_end`) VALUES
(1, '08:00:00', '16:00:00', '11:30:00', '12:30:00', 5, 0, 1000, 27, 500, 60, 420, 210, 10, 360, '04:00:00', '11:01:00', '11:00:00', '12:01:00', '12:00:00', '13:01:00', '13:00:00', '23:59:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_kas`
--

CREATE TABLE `transaksi_kas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jenis` enum('Masuk','Keluar') NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nominal` decimal(15,0) NOT NULL DEFAULT 0,
  `metode` enum('Cash','ATM') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_log`
--

CREATE TABLE `t_log` (
  `id` int(11) NOT NULL,
  `cloud_id` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `original_data` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `t_log`
--

INSERT INTO `t_log` (`id`, `cloud_id`, `type`, `created_at`, `original_data`) VALUES
(367, 'FZ1096818', 'attlog', '2026-01-28 00:43:40', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1010\",\"scan\":\"2026-01-28 07:43\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561020288.jpeg\",\"work_code\":null}}'),
(368, 'FZ1096818', 'attlog', '2026-01-28 00:44:44', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1009\",\"scan\":\"2026-01-28 07:44\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561084707.jpeg\",\"work_code\":null}}'),
(369, 'FZ1096818', 'attlog', '2026-01-28 00:44:57', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1001\",\"scan\":\"2026-01-28 07:44\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(370, 'FZ1096818', 'attlog', '2026-01-28 00:53:55', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 07:53\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561635275.jpeg\",\"work_code\":null}}'),
(371, 'FZ1096818', 'attlog', '2026-01-28 00:54:09', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 07:53\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561648932.jpeg\",\"work_code\":null}}'),
(372, 'FZ1096818', 'attlog', '2026-01-28 00:54:26', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 07:54\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561666534.jpeg\",\"work_code\":null}}'),
(373, 'FZ1096818', 'attlog', '2026-01-28 00:54:38', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 07:54\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561678582.jpeg\",\"work_code\":null}}'),
(374, 'FZ1096818', 'attlog', '2026-01-28 00:55:03', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 07:54\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561703690.jpeg\",\"work_code\":null}}'),
(375, 'FZ1096818', 'attlog', '2026-01-28 00:55:27', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 07:55\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561727168.jpeg\",\"work_code\":null}}'),
(376, 'FZ1096818', 'attlog', '2026-01-28 00:55:42', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 07:55\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561742847.jpeg\",\"work_code\":null}}'),
(377, 'FZ1096818', 'attlog', '2026-01-28 00:55:50', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 07:55\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561750342.jpeg\",\"work_code\":null}}'),
(378, 'FZ1096818', 'attlog', '2026-01-28 00:56:10', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 07:55\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769561770235.jpeg\",\"work_code\":null}}'),
(379, 'FZ1096818', 'attlog', '2026-01-28 01:00:51', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1011\",\"scan\":\"2026-01-28 08:00\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(380, 'FZ1096818', 'attlog', '2026-01-28 01:01:48', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1004\",\"scan\":\"2026-01-28 08:01\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562108040.jpeg\",\"work_code\":null}}'),
(381, 'FZ1096818', 'attlog', '2026-01-28 01:01:58', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1004\",\"scan\":\"2026-01-28 08:01\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562118377.jpeg\",\"work_code\":null}}'),
(382, 'FZ1096818', 'attlog', '2026-01-28 01:02:13', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 08:01\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562133466.jpeg\",\"work_code\":null}}'),
(383, 'FZ1096818', 'attlog', '2026-01-28 01:02:34', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1022\",\"scan\":\"2026-01-28 08:02\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562153935.jpeg\",\"work_code\":null}}'),
(384, 'FZ1096818', 'attlog', '2026-01-28 01:02:36', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1022\",\"scan\":\"2026-01-28 08:02\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562156499.jpeg\",\"work_code\":null}}'),
(385, 'FZ1096818', 'attlog', '2026-01-28 01:03:05', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1022\",\"scan\":\"2026-01-28 08:02\",\"verify\":99,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562184926.jpeg\",\"work_code\":null}}'),
(386, 'FZ1096818', 'attlog', '2026-01-28 01:03:10', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1013\",\"scan\":\"2026-01-28 08:02\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562190454.jpeg\",\"work_code\":null}}'),
(387, 'FZ1096818', 'attlog', '2026-01-28 01:03:16', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1013\",\"scan\":\"2026-01-28 08:02\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562196905.jpeg\",\"work_code\":null}}'),
(388, 'FZ1096818', 'attlog', '2026-01-28 01:03:20', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1013\",\"scan\":\"2026-01-28 08:02\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562200509.jpeg\",\"work_code\":null}}'),
(389, 'FZ1096818', 'attlog', '2026-01-28 01:04:55', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1002\",\"scan\":\"2026-01-28 08:04\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562294904.jpeg\",\"work_code\":null}}'),
(390, 'FZ1096818', 'attlog', '2026-01-28 01:04:58', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1002\",\"scan\":\"2026-01-28 08:04\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562298850.jpeg\",\"work_code\":null}}'),
(391, 'FZ1096818', 'attlog', '2026-01-28 01:05:02', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1002\",\"scan\":\"2026-01-28 08:04\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562302452.jpeg\",\"work_code\":null}}'),
(392, 'FZ1096818', 'attlog', '2026-01-28 01:16:35', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1018\",\"scan\":\"2026-01-28 08:16\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562994979.jpeg\",\"work_code\":null}}'),
(393, 'FZ1096818', 'attlog', '2026-01-28 01:16:36', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1018\",\"scan\":\"2026-01-28 08:16\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769562996870.jpeg\",\"work_code\":null}}'),
(394, 'FZ1096818', 'attlog', '2026-01-28 01:20:31', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1012\",\"scan\":\"2026-01-28 08:20\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(395, 'FZ1096818', 'attlog', '2026-01-28 01:31:11', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1023\",\"scan\":\"2026-01-28 08:30\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769563871250.jpeg\",\"work_code\":null}}'),
(396, 'FZ1096818', 'attlog', '2026-01-28 01:31:16', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1023\",\"scan\":\"2026-01-28 08:30\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769563876720.jpeg\",\"work_code\":null}}'),
(397, 'FZ1096818', 'attlog', '2026-01-28 01:32:17', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1024\",\"scan\":\"2026-01-28 08:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769563936991.jpeg\",\"work_code\":null}}'),
(398, 'FZ1096818', 'attlog', '2026-01-28 01:34:24', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1007\",\"scan\":\"2026-01-28 08:34\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769564064523.jpeg\",\"work_code\":null}}'),
(399, 'FZ1096818', 'attlog', '2026-01-28 01:34:26', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1007\",\"scan\":\"2026-01-28 08:34\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769564066438.jpeg\",\"work_code\":null}}'),
(400, 'FZ1096818', 'attlog', '2026-01-28 01:34:39', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1007\",\"scan\":\"2026-01-28 08:34\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769564079171.jpeg\",\"work_code\":null}}'),
(401, 'FZ1096818', 'attlog', '2026-01-28 01:36:54', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1025\",\"scan\":\"2026-01-28 08:36\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769564213989.jpeg\",\"work_code\":null}}'),
(402, 'FZ1096818', 'attlog', '2026-01-28 01:59:46', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1008\",\"scan\":\"2026-01-28 08:59\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(403, 'FZ1096818', 'attlog', '2026-01-28 04:30:04', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1010\",\"scan\":\"2026-01-28 11:29\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574603914.jpeg\",\"work_code\":null}}'),
(404, 'FZ1096818', 'attlog', '2026-01-28 04:30:14', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1010\",\"scan\":\"2026-01-28 11:29\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574614152.jpeg\",\"work_code\":null}}'),
(405, 'FZ1096818', 'attlog', '2026-01-28 04:30:44', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1009\",\"scan\":\"2026-01-28 11:30\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574644786.jpeg\",\"work_code\":null}}'),
(406, 'FZ1096818', 'attlog', '2026-01-28 04:31:04', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1004\",\"scan\":\"2026-01-28 11:30\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574664586.jpeg\",\"work_code\":null}}'),
(407, 'FZ1096818', 'attlog', '2026-01-28 04:31:37', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1011\",\"scan\":\"2026-01-28 11:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574697370.jpeg\",\"work_code\":null}}'),
(408, 'FZ1096818', 'attlog', '2026-01-28 04:31:44', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1011\",\"scan\":\"2026-01-28 11:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574704007.jpeg\",\"work_code\":null}}'),
(409, 'FZ1096818', 'attlog', '2026-01-28 04:31:50', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1011\",\"scan\":\"2026-01-28 11:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574710115.jpeg\",\"work_code\":null}}'),
(410, 'FZ1096818', 'attlog', '2026-01-28 04:31:57', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1007\",\"scan\":\"2026-01-28 11:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574717423.jpeg\",\"work_code\":null}}'),
(411, 'FZ1096818', 'attlog', '2026-01-28 04:32:06', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1013\",\"scan\":\"2026-01-28 11:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574725908.jpeg\",\"work_code\":null}}'),
(412, 'FZ1096818', 'attlog', '2026-01-28 04:32:09', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1013\",\"scan\":\"2026-01-28 11:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574729129.jpeg\",\"work_code\":null}}'),
(413, 'FZ1096818', 'attlog', '2026-01-28 04:32:12', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1023\",\"scan\":\"2026-01-28 11:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574732190.jpeg\",\"work_code\":null}}'),
(414, 'FZ1096818', 'attlog', '2026-01-28 04:32:14', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1023\",\"scan\":\"2026-01-28 11:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574734882.jpeg\",\"work_code\":null}}'),
(415, 'FZ1096818', 'attlog', '2026-01-28 04:32:21', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1023\",\"scan\":\"2026-01-28 11:31\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574740946.jpeg\",\"work_code\":null}}'),
(416, 'FZ1096818', 'attlog', '2026-01-28 04:32:26', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1024\",\"scan\":\"2026-01-28 11:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574746154.jpeg\",\"work_code\":null}}'),
(417, 'FZ1096818', 'attlog', '2026-01-28 04:32:31', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1024\",\"scan\":\"2026-01-28 11:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574751669.jpeg\",\"work_code\":null}}'),
(418, 'FZ1096818', 'attlog', '2026-01-28 04:32:34', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1024\",\"scan\":\"2026-01-28 11:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574754401.jpeg\",\"work_code\":null}}'),
(419, 'FZ1096818', 'attlog', '2026-01-28 04:32:43', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1025\",\"scan\":\"2026-01-28 11:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574763650.jpeg\",\"work_code\":null}}'),
(420, 'FZ1096818', 'attlog', '2026-01-28 04:32:53', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 11:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574772934.jpeg\",\"work_code\":null}}'),
(421, 'FZ1096818', 'attlog', '2026-01-28 04:33:20', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1022\",\"scan\":\"2026-01-28 11:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574800416.jpeg\",\"work_code\":null}}'),
(422, 'FZ1096818', 'attlog', '2026-01-28 04:34:32', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1004\",\"scan\":\"2026-01-28 11:34\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769574872660.jpeg\",\"work_code\":null}}'),
(423, 'FZ1096818', 'attlog', '2026-01-28 04:38:09', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1008\",\"scan\":\"2026-01-28 11:37\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769575089691.jpeg\",\"work_code\":null}}'),
(424, 'FZ1096818', 'attlog', '2026-01-28 04:38:18', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1002\",\"scan\":\"2026-01-28 11:37\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769575098642.jpeg\",\"work_code\":null}}'),
(425, 'FZ1096818', 'attlog', '2026-01-28 04:38:27', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1018\",\"scan\":\"2026-01-28 11:38\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769575106999.jpeg\",\"work_code\":null}}'),
(426, 'FZ1096818', 'attlog', '2026-01-28 04:38:37', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1018\",\"scan\":\"2026-01-28 11:38\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769575117612.jpeg\",\"work_code\":null}}'),
(427, 'FZ1096818', 'attlog', '2026-01-28 04:40:29', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1001\",\"scan\":\"2026-01-28 11:40\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(428, 'FZ1096818', 'attlog', '2026-01-28 04:40:45', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 11:40\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769575245194.jpeg\",\"work_code\":null}}'),
(429, 'FZ1096818', 'attlog', '2026-01-28 04:40:49', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 11:40\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769575249183.jpeg\",\"work_code\":null}}'),
(430, 'FZ1096818', 'attlog', '2026-01-28 05:10:00', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1018\",\"scan\":\"2026-01-28 12:09\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769576999000.jpeg\",\"work_code\":null}}'),
(431, 'FZ1096818', 'attlog', '2026-01-28 05:24:12', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1008\",\"scan\":\"2026-01-28 12:23\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(432, 'FZ1096818', 'attlog', '2026-01-28 05:24:19', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1009\",\"scan\":\"2026-01-28 12:23\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769577859785.jpeg\",\"work_code\":null}}'),
(433, 'FZ1096818', 'attlog', '2026-01-28 05:25:08', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1002\",\"scan\":\"2026-01-28 12:24\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769577908096.jpeg\",\"work_code\":null}}'),
(434, 'FZ1096818', 'attlog', '2026-01-28 05:25:23', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1004\",\"scan\":\"2026-01-28 12:25\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769577923401.jpeg\",\"work_code\":null}}'),
(435, 'FZ1096818', 'attlog', '2026-01-28 05:25:29', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1011\",\"scan\":\"2026-01-28 12:25\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(436, 'FZ1096818', 'attlog', '2026-01-28 05:25:39', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1010\",\"scan\":\"2026-01-28 12:25\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769577939819.jpeg\",\"work_code\":null}}'),
(437, 'FZ1096818', 'attlog', '2026-01-28 05:25:50', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 12:25\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769577950426.jpeg\",\"work_code\":null}}'),
(438, 'FZ1096818', 'attlog', '2026-01-28 05:26:46', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1013\",\"scan\":\"2026-01-28 12:26\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578006459.jpeg\",\"work_code\":null}}'),
(439, 'FZ1096818', 'attlog', '2026-01-28 05:26:48', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1013\",\"scan\":\"2026-01-28 12:26\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578008396.jpeg\",\"work_code\":null}}'),
(440, 'FZ1096818', 'attlog', '2026-01-28 05:27:01', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 12:26\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578021271.jpeg\",\"work_code\":null}}'),
(441, 'FZ1096818', 'attlog', '2026-01-28 05:27:04', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 12:26\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578024184.jpeg\",\"work_code\":null}}'),
(442, 'FZ1096818', 'attlog', '2026-01-28 05:27:09', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 12:26\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578029056.jpeg\",\"work_code\":null}}'),
(443, 'FZ1096818', 'attlog', '2026-01-28 05:27:14', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 12:26\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578034756.jpeg\",\"work_code\":null}}'),
(444, 'FZ1096818', 'attlog', '2026-01-28 05:27:30', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 12:27\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578050494.jpeg\",\"work_code\":null}}'),
(445, 'FZ1096818', 'attlog', '2026-01-28 05:27:33', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 12:27\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578053428.jpeg\",\"work_code\":null}}'),
(446, 'FZ1096818', 'attlog', '2026-01-28 05:27:45', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 12:27\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578065853.jpeg\",\"work_code\":null}}'),
(447, 'FZ1096818', 'attlog', '2026-01-28 05:27:51', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 12:27\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578071805.jpeg\",\"work_code\":null}}'),
(448, 'FZ1096818', 'attlog', '2026-01-28 05:27:56', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 12:27\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578076338.jpeg\",\"work_code\":null}}'),
(449, 'FZ1096818', 'attlog', '2026-01-28 05:28:10', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 12:27\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578089974.jpeg\",\"work_code\":null}}'),
(450, 'FZ1096818', 'attlog', '2026-01-28 05:28:30', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1022\",\"scan\":\"2026-01-28 12:28\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578110774.jpeg\",\"work_code\":null}}'),
(451, 'FZ1096818', 'attlog', '2026-01-28 05:28:44', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1007\",\"scan\":\"2026-01-28 12:28\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578123868.jpeg\",\"work_code\":null}}'),
(452, 'FZ1096818', 'attlog', '2026-01-28 05:29:27', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1012\",\"scan\":\"2026-01-28 12:29\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(453, 'FZ1096818', 'attlog', '2026-01-28 05:35:24', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1005\",\"scan\":\"2026-01-28 12:35\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578523922.jpeg\",\"work_code\":null}}'),
(454, 'FZ1096818', 'attlog', '2026-01-28 05:35:35', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1005\",\"scan\":\"2026-01-28 12:35\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578535584.jpeg\",\"work_code\":null}}'),
(455, 'FZ1096818', 'attlog', '2026-01-28 05:35:45', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1024\",\"scan\":\"2026-01-28 12:35\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578544953.jpeg\",\"work_code\":null}}'),
(456, 'FZ1096818', 'attlog', '2026-01-28 05:36:22', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1025\",\"scan\":\"2026-01-28 12:35\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769578582358.jpeg\",\"work_code\":null}}'),
(457, 'FZ1096818', 'attlog', '2026-01-28 09:01:00', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1009\",\"scan\":\"2026-01-28 16:00\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769590859963.jpeg\",\"work_code\":null}}'),
(458, 'FZ1096818', 'attlog', '2026-01-28 09:02:12', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1010\",\"scan\":\"2026-01-28 16:01\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769590932536.jpeg\",\"work_code\":null}}'),
(459, 'FZ1096818', 'attlog', '2026-01-28 09:03:30', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1006\",\"scan\":\"2026-01-28 16:03\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769591010841.jpeg\",\"work_code\":null}}'),
(460, 'FZ1096818', 'attlog', '2026-01-28 09:04:52', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1011\",\"scan\":\"2026-01-28 16:04\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769591092515.jpeg\",\"work_code\":null}}'),
(461, 'FZ1096818', 'attlog', '2026-01-28 09:05:56', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1024\",\"scan\":\"2026-01-28 16:05\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769591156695.jpeg\",\"work_code\":null}}'),
(462, 'FZ1096818', 'attlog', '2026-01-28 09:26:27', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1012\",\"scan\":\"2026-01-28 16:26\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(463, 'FZ1096818', 'attlog', '2026-01-28 09:26:55', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1025\",\"scan\":\"2026-01-28 16:26\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769592415725.jpeg\",\"work_code\":null}}'),
(464, 'FZ1096818', 'attlog', '2026-01-28 09:27:03', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1025\",\"scan\":\"2026-01-28 16:26\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769592423039.jpeg\",\"work_code\":null}}'),
(465, 'FZ1096818', 'attlog', '2026-01-28 09:32:40', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1007\",\"scan\":\"2026-01-28 16:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769592760319.jpeg\",\"work_code\":null}}'),
(466, 'FZ1096818', 'attlog', '2026-01-28 09:32:44', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1007\",\"scan\":\"2026-01-28 16:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769592764098.jpeg\",\"work_code\":null}}'),
(467, 'FZ1096818', 'attlog', '2026-01-28 09:32:48', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1007\",\"scan\":\"2026-01-28 16:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769592768048.jpeg\",\"work_code\":null}}'),
(468, 'FZ1096818', 'attlog', '2026-01-28 09:32:52', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1007\",\"scan\":\"2026-01-28 16:32\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769592772574.jpeg\",\"work_code\":null}}'),
(469, 'FZ1096818', 'attlog', '2026-01-28 09:57:07', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1005\",\"scan\":\"2026-01-28 16:56\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769594227075.jpeg\",\"work_code\":null}}'),
(470, 'FZ1096818', 'attlog', '2026-01-28 10:06:57', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1001\",\"scan\":\"2026-01-28 17:06\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(471, 'FZ1096818', 'attlog', '2026-01-28 10:37:38', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1013\",\"scan\":\"2026-01-28 17:37\",\"verify\":1,\"status_scan\":0,\"photo_url\":\"-\",\"work_code\":null}}'),
(472, 'FZ1096818', 'attlog', '2026-01-28 13:01:14', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1002\",\"scan\":\"2026-01-28 20:00\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769605274332.jpeg\",\"work_code\":null}}'),
(473, 'FZ1096818', 'attlog', '2026-01-28 13:01:27', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1002\",\"scan\":\"2026-01-28 20:01\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769605287397.jpeg\",\"work_code\":null}}'),
(474, 'FZ1096818', 'attlog', '2026-01-28 13:01:51', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1002\",\"scan\":\"2026-01-28 20:01\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769605311825.jpeg\",\"work_code\":null}}'),
(475, 'FZ1096818', 'attlog', '2026-01-28 13:02:19', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1002\",\"scan\":\"2026-01-28 20:01\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769605339894.jpeg\",\"work_code\":null}}'),
(476, 'FZ1096818', 'attlog', '2026-01-28 13:39:55', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1022\",\"scan\":\"2026-01-28 20:39\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769607595266.jpeg\",\"work_code\":null}}'),
(477, 'FZ1096818', 'attlog', '2026-01-28 13:39:59', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1022\",\"scan\":\"2026-01-28 20:39\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769607599735.jpeg\",\"work_code\":null}}'),
(478, 'FZ1096818', 'attlog', '2026-01-28 13:45:54', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 20:45\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769607954443.jpeg\",\"work_code\":null}}'),
(479, 'FZ1096818', 'attlog', '2026-01-28 13:46:15', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 20:45\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769607974916.jpeg\",\"work_code\":null}}'),
(480, 'FZ1096818', 'attlog', '2026-01-28 13:46:17', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 20:45\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769607977459.jpeg\",\"work_code\":null}}'),
(481, 'FZ1096818', 'attlog', '2026-01-28 13:46:20', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 20:45\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769607979798.jpeg\",\"work_code\":null}}'),
(482, 'FZ1096818', 'attlog', '2026-01-28 13:46:24', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 20:46\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769607984076.jpeg\",\"work_code\":null}}'),
(483, 'FZ1096818', 'attlog', '2026-01-28 13:46:30', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 20:46\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769607990616.jpeg\",\"work_code\":null}}'),
(484, 'FZ1096818', 'attlog', '2026-01-28 13:46:37', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 20:46\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769607997745.jpeg\",\"work_code\":null}}'),
(485, 'FZ1096818', 'attlog', '2026-01-28 13:46:42', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 20:46\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769608002248.jpeg\",\"work_code\":null}}'),
(486, 'FZ1096818', 'attlog', '2026-01-28 13:46:45', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1021\",\"scan\":\"2026-01-28 20:46\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769608004905.jpeg\",\"work_code\":null}}'),
(487, 'FZ1096818', 'attlog', '2026-01-28 14:02:06', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1004\",\"scan\":\"2026-01-28 21:01\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769608926650.jpeg\",\"work_code\":null}}'),
(488, 'FZ1096818', 'attlog', '2026-01-28 14:02:18', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1004\",\"scan\":\"2026-01-28 21:01\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769608938064.jpeg\",\"work_code\":null}}'),
(489, 'FZ1096818', 'attlog', '2026-01-28 14:02:29', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1018\",\"scan\":\"2026-01-28 21:02\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769608949517.jpeg\",\"work_code\":null}}'),
(490, 'FZ1096818', 'attlog', '2026-01-28 14:24:04', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1008\",\"scan\":\"2026-01-28 21:23\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769610244655.jpeg\",\"work_code\":null}}'),
(491, 'FZ1096818', 'attlog', '2026-01-28 14:24:15', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1008\",\"scan\":\"2026-01-28 21:23\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769610255675.jpeg\",\"work_code\":null}}'),
(492, 'FZ1096818', 'attlog', '2026-01-28 14:25:23', '{\"type\":\"attlog\",\"cloud_id\":\"FZ1096818\",\"data\":{\"pin\":\"1019\",\"scan\":\"2026-01-28 21:25\",\"verify\":4,\"status_scan\":0,\"photo_url\":\"https://fioapp.s3.ap-southeast-1.amazonaws.com/attendance/front-photo/developer-21763/FZ1096818_attendance_21763_1769610323368.jpeg\",\"work_code\":null}}');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `pin` varchar(20) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `privilege` enum('1','2','3') NOT NULL DEFAULT '1',
  `finger` tinyint(1) NOT NULL DEFAULT 0,
  `face` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `rfid` varchar(50) DEFAULT NULL,
  `vein` tinyint(1) NOT NULL DEFAULT 0,
  `template` text DEFAULT NULL,
  `role` enum('admin','user','kepala_bengkel') NOT NULL DEFAULT 'user',
  `status_karyawan` enum('Tetap','Borongan') NOT NULL DEFAULT 'Tetap',
  `no_hp` varchar(20) DEFAULT NULL,
  `tgl_masuk` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `sync_status` enum('synced','failed','pending') DEFAULT 'pending',
  `group_id` int(11) DEFAULT NULL COMMENT 'ID Kelompok/Tim',
  `is_mandor` tinyint(1) DEFAULT 0 COMMENT '1 = Mandor, 0 = Anggota'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `pin`, `fullname`, `username`, `privilege`, `finger`, `face`, `password`, `rfid`, `vein`, `template`, `role`, `status_karyawan`, `no_hp`, `tgl_masuk`, `created_at`, `updated_at`, `sync_status`, `group_id`, `is_mandor`) VALUES
(1, '1', 'Administrator', 'admin', '2', 0, 0, '$2y$10$fBljvKSwTAL4fKpnJx/h9u.9rX3OpNAtG6IbHS3BhspjtwIZ.jtPS', NULL, 0, NULL, 'admin', 'Tetap', NULL, NULL, '2026-01-22 22:54:12', NULL, 'pending', NULL, 0),
(18, '1001', 'DINDA', 'dinda', '1', 0, 0, '$2y$10$W0UvZ7CX34hZ0t..lxwiy.bIJrbIXiGi6UFOCIh3kmsT8T9nY.kXu', NULL, 0, NULL, 'user', 'Tetap', '085804692746', '2026-01-26', '2026-01-26 04:24:13', '2026-01-26 04:24:13', 'synced', NULL, 0),
(19, '1002', 'K BIRIN', 'birin', '1', 0, 0, '$2y$10$AqRG.fezxXpmvoiRXWq3Pu1JubBMQCZVZtBpInCETyUrPrv8GWpcW', NULL, 0, NULL, 'kepala_bengkel', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:25:53', '2026-01-28 18:45:00', 'synced', NULL, 0),
(20, '1003', 'DADAN', 'dadan', '1', 0, 0, '$2y$10$ks9sdRUW7FfHNY5SLu0sN.ykqvBFzEB2Fsd5K2OYTlrK7Wy0h5T/.', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:27:01', '2026-01-26 04:27:01', 'synced', NULL, 0),
(21, '1004', 'FAHMI', 'fahmi', '1', 0, 0, '$2y$10$RCXJwlSE1YcxF7eyP/sfmOXA6LNQI5vVdsKo0GeN3SXP7KIT5Lzku', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:28:09', '2026-01-26 04:28:09', 'synced', NULL, 0),
(22, '1005', 'FEBRI', 'febri', '1', 0, 0, '$2y$10$eHmF.Gv.qhSjUNB87CUSZexzmwG2XmCWYI/PdAx7KqGAQjB6sOhnS', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:29:19', '2026-01-26 04:29:20', 'synced', NULL, 0),
(23, '1006', 'FIRMAN', 'firman', '1', 0, 0, '$2y$10$PoVbkBvB2QSopxzKtm4UmOsZY0OElIFZUc8gteUsE5WRBaQK0FIqq', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:38:40', '2026-01-26 04:38:40', 'synced', NULL, 0),
(24, '1007', 'K HEMIN', 'hemin', '1', 0, 0, '$2y$10$ChPoqMaTMYwO0Fgu7U.B7u7eIJPF9FMQfYeMdt5B.zFXg9ZDy5UUG', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:39:40', '2026-01-26 04:39:40', 'synced', NULL, 0),
(25, '1008', 'NAUFAL', 'naufal', '1', 0, 0, '$2y$10$dbYJUGUzV5ev0IcIExjn.eqm/siVY.R7uznIgts5RDMqoyxcWxD.K', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:40:44', '2026-01-26 04:40:44', 'synced', NULL, 0),
(26, '1009', 'TEGAR', 'tegar', '1', 0, 0, '$2y$10$gNTzXBuXQUkCGw/iehBsSezjU3Dluo4I2LIWCX5yGIWQMSL3OPWsa', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:42:13', '2026-01-26 04:42:13', 'synced', NULL, 0),
(27, '1010', 'YONO', 'yono', '1', 0, 0, '$2y$10$BnDDc7h37eLQDp2Q7GtAgON7ww7DRJDxtlLXnc21v1eVLvp/M/keO', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:43:20', '2026-01-26 04:43:21', 'synced', NULL, 0),
(28, '1011', 'RIO', 'rio', '1', 0, 0, '$2y$10$2tONvUlgrSM7KGOwD5WnIuKbBArGJDaTwF.J0a9tghi7yfCioqX/6', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:44:09', '2026-01-26 04:44:09', 'synced', NULL, 0),
(29, '1012', 'VENAS', 'venas', '1', 0, 0, '$2y$10$eCfkXXHj7.PRTV9iUgj9QuvvIR5XAP3t7hJVCcWVo0SVKhcvBdkIm', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:45:03', '2026-01-26 04:45:03', 'synced', NULL, 0),
(30, '1013', 'WAHYU', 'wahyu', '1', 0, 0, '$2y$10$ScCPjOQkO5cQqVnqFKTrBu35DN/bu4E9GvFbAebFPld/zIYpIuAhG', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-26 04:46:13', '2026-01-26 04:46:13', 'synced', NULL, 0),
(31, '1014', 'TIWUL', 'tiwul', '1', 0, 0, '$2y$10$XHkpz100A9I02jxxuz9rLuN4mhbFmafGbVq8SiQ.eLEVUr1IWqJoC', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 04:48:32', '2026-01-26 04:48:32', 'synced', 31, 1),
(32, '1015', 'IFAN', 'ifan', '1', 0, 0, '$2y$10$QkUaD8ue5q.hRi7dxmwc7Ofm9EpR/oYdIK4dcDGxKy810dk/mVbiy', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 04:49:49', '2026-01-26 04:49:49', 'synced', 31, 0),
(34, '1017', 'RAFI', 'rafi', '1', 0, 0, '$2y$10$9cboO7QVtafTOWDbzZa28OnB1NN54p4uC/u4fCsOfCZBIHZyDpUH.', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 04:50:54', '2026-01-26 04:50:54', 'synced', 31, 0),
(35, '1018', 'HERI', 'heri', '1', 0, 0, '$2y$10$P5nlruSLuZFOyLm21bnxwuc5sYFK2NQ6EL39mm/YC1GHHAKDGH5aK', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 04:52:01', '2026-01-26 04:52:01', 'synced', NULL, 0),
(36, '1019', 'ROFI', 'rofi', '1', 0, 0, '$2y$10$zdsxudvwU6HlAcgortglIulxA/QET9/QtJkjILQ6efcLtRheluy/e', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 04:52:28', '2026-01-26 04:52:29', 'synced', NULL, 0),
(37, '1020', 'HALIM', 'halim', '1', 0, 0, '$2y$10$HmDin/bvhp1s3ejn7Q/3uOW8NgNw1B1D5BhGnkFwKjke1ASdcFAmm', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 04:52:50', '2026-01-26 04:52:51', 'synced', NULL, 0),
(38, '1021', 'VIKAR', 'vikar', '1', 0, 0, '$2y$10$VEToFVEaALo/0g7z2TOseOwuV8S0zynmKW8s0/Ko.KL3tq06OB0Ay', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 04:56:49', '2026-01-26 04:56:49', 'synced', 38, 1),
(40, '1022', 'BAO', 'bao', '1', 0, 0, '$2y$10$eSIT5GXEF0sZAj392bF.uubt3C51lUKIco4vMSoY8zTuWZ1EUQs0y', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 04:57:31', '2026-01-26 04:57:31', 'synced', 38, 0),
(41, '1023', 'WIWI', 'wiwi', '1', 0, 0, '$2y$10$B53RnqLVbXYz2YY4PcR0tOXM4SqjuuF/cg6KSTQ8i8HhU7Gpgitnm', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 05:05:24', '2026-01-26 05:05:24', 'synced', 41, 1),
(42, '1024', 'IUS', 'ius', '1', 0, 0, '$2y$10$EmC5CYVAQb5fe2dOxqBbSeO1.b2yKuzycuI2dqBUnp15JH/baWViG', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 05:05:55', '2026-01-26 05:05:56', 'synced', 41, 0),
(43, '1025', 'INDRA', 'indra', '1', 0, 0, '$2y$10$PwtSK3pKL/IWWL//iLTj1uP..FOFbOtmQlipFg9Xaf7oyJ2mZRjSO', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 05:06:23', '2026-01-26 05:06:24', 'synced', 41, 0),
(44, '1026', 'RIAN', 'rian', '1', 0, 0, '$2y$10$r3omWT8nL8xZHX1ymlHwnOk6ZeAyvmPwL7KFGeZwr/yT/ynUmRK6e', NULL, 0, NULL, 'user', 'Borongan', NULL, '2026-01-26', '2026-01-26 05:06:51', '2026-01-26 05:06:51', 'synced', 41, 0),
(48, '1027', 'DINO', 'dino', '1', 0, 0, '$2y$10$B5KxeMgQooKWol/Z54zUDuRzhp3RWAW6Wct6bDGuLSuj1dfRjRfPG', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-26', '2026-01-27 05:49:13', '2026-01-27 05:49:14', 'synced', NULL, 0),
(50, '1028', 'Test', 'test', '1', 0, 0, '$2y$10$sPpzv6O6/qpD2mLC.T9HYePYPqTQG.4zD0QSro2BBVCj4lcTe7qZK', NULL, 0, NULL, 'user', 'Tetap', NULL, '2026-01-27', '2026-01-27 07:25:28', '2026-01-27 07:25:28', 'synced', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pin_date` (`pin`,`scan_date`);

--
-- Indeks untuk tabel `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  ADD PRIMARY KEY (`user_id`);

--
-- Indeks untuk tabel `kasbon`
--
ALTER TABLE `kasbon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kasbon_user` (`user_id`);

--
-- Indeks untuk tabel `master_pekerjaan`
--
ALTER TABLE `master_pekerjaan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orderan`
--
ALTER TABLE `orderan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengiriman_items`
--
ALTER TABLE `pengiriman_items`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `produksi_borongan`
--
ALTER TABLE `produksi_borongan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tanggal` (`tanggal`);

--
-- Indeks untuk tabel `settings_jam_kerja`
--
ALTER TABLE `settings_jam_kerja`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi_kas`
--
ALTER TABLE `transaksi_kas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `t_log`
--
ALTER TABLE `t_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cloud_created` (`cloud_id`,`created_at`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pin` (`pin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_privilege` (`privilege`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=275;

--
-- AUTO_INCREMENT untuk tabel `kasbon`
--
ALTER TABLE `kasbon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `master_pekerjaan`
--
ALTER TABLE `master_pekerjaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `orderan`
--
ALTER TABLE `orderan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengiriman_items`
--
ALTER TABLE `pengiriman_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `produksi_borongan`
--
ALTER TABLE `produksi_borongan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `transaksi_kas`
--
ALTER TABLE `transaksi_kas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `t_log`
--
ALTER TABLE `t_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=493;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_absen_user` FOREIGN KEY (`pin`) REFERENCES `users` (`pin`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  ADD CONSTRAINT `fk_gaji_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kasbon`
--
ALTER TABLE `kasbon`
  ADD CONSTRAINT `fk_kasbon_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produksi_borongan`
--
ALTER TABLE `produksi_borongan`
  ADD CONSTRAINT `fk_produksi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
