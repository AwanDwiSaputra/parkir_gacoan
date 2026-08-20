-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 10, 2026 at 07:40 AM
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
-- Database: `db_parkir_cloud`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_area_parkir`
--

CREATE TABLE `tb_area_parkir` (
  `id_area` int(11) NOT NULL,
  `nama_area` varchar(50) NOT NULL,
  `kapasitas` int(5) NOT NULL,
  `terisi` int(5) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_area_parkir`
--

INSERT INTO `tb_area_parkir` (`id_area`, `nama_area`, `kapasitas`, `terisi`) VALUES
(1, 'Area Depan (Motor)', 10, 1),
(2, 'Area Basement (Mobil)', 10, 0),
(3, 'Area Samping (Member VIP)', 8, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tb_booking`
--

CREATE TABLE `tb_booking` (
  `id_booking` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_kendaraan` int(11) NOT NULL,
  `id_area` int(11) DEFAULT NULL,
  `tanggal_booking` date NOT NULL,
  `jam_booking` time NOT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','dikonfirmasi','dibatalkan','selesai') NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_booking`
--

INSERT INTO `tb_booking` (`id_booking`, `id_user`, `id_kendaraan`, `id_area`, `tanggal_booking`, `jam_booking`, `catatan`, `status`, `created_at`) VALUES
(1, 4, 1, NULL, '2026-07-25', '10:00:00', 'butuh', 'selesai', '2026-07-24 05:56:25'),
(2, 4, 2, 2, '2026-07-26', '20:00:00', 'butuh slot', 'selesai', '2026-07-24 06:20:10'),
(3, 4, 3, 1, '2026-08-05', '20:00:00', 'butuh slot', 'selesai', '2026-08-05 01:11:49'),
(4, 4, 3, 1, '2026-08-05', '11:00:00', NULL, 'selesai', '2026-08-05 01:14:49'),
(5, 4, 2, 2, '2026-08-05', '11:00:00', NULL, 'dibatalkan', '2026-08-05 01:59:56'),
(6, 4, 3, 1, '2026-08-05', '10:00:00', NULL, 'selesai', '2026-08-05 02:23:57'),
(7, 4, 1, 1, '2026-08-05', '11:00:00', NULL, 'dikonfirmasi', '2026-08-05 02:24:18'),
(8, 4, 4, 1, '2026-08-05', '12:00:00', NULL, 'selesai', '2026-08-05 02:25:04'),
(9, 4, 4, 1, '2026-08-05', '12:00:00', NULL, 'selesai', '2026-08-05 02:25:04'),
(10, 4, 5, 1, '2026-08-05', '01:00:00', NULL, 'selesai', '2026-08-05 02:27:03'),
(11, 4, 6, 1, '2026-08-10', '10:00:00', NULL, 'selesai', '2026-08-10 01:22:25'),
(12, 4, 3, 1, '2026-08-10', '05:00:00', NULL, 'selesai', '2026-08-10 02:33:35');

-- --------------------------------------------------------

--
-- Table structure for table `tb_kendaraan`
--

CREATE TABLE `tb_kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `plat_nomor` varchar(15) NOT NULL,
  `jenis_kendaraan` varchar(20) NOT NULL,
  `warna` varchar(20) DEFAULT NULL,
  `pemilik` varchar(100) NOT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kendaraan`
--

INSERT INTO `tb_kendaraan` (`id_kendaraan`, `plat_nomor`, `jenis_kendaraan`, `warna`, `pemilik`, `id_user`) VALUES
(1, 'B 1234', 'Motor', 'merah', 'buby', 4),
(2, 'A 2345', 'Mobil', 'hitam', 'awan dwi', 4),
(3, 'AB 1234', 'Motor', NULL, 'awan dwi', 4),
(4, 'AD 1234', 'Motor', NULL, 'awan dwi', 4),
(5, 'BA 1234', 'Motor', NULL, 'awan dwi', 4),
(6, 'BC 231', 'Motor', NULL, 'awan dwi', 4);

-- --------------------------------------------------------

--
-- Table structure for table `tb_log_aktivitas`
--

CREATE TABLE `tb_log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `aktivitas` varchar(150) NOT NULL,
  `waktu_aktivitas` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_log_aktivitas`
--

INSERT INTO `tb_log_aktivitas` (`id_log`, `id_user`, `aktivitas`, `waktu_aktivitas`) VALUES
(1, 1, 'Login ke sistem', '2026-07-23 11:06:25'),
(2, 1, 'Logout dari sistem', '2026-07-23 11:09:11'),
(3, 3, 'Login ke sistem', '2026-07-23 11:10:24'),
(4, 3, 'Logout dari sistem', '2026-07-23 11:10:39'),
(5, 1, 'Login ke sistem', '2026-07-23 11:30:11'),
(6, 1, 'Menambahkan tarif jenis lainnya', '2026-07-23 11:30:24'),
(7, 1, 'Logout dari sistem', '2026-07-23 12:23:33'),
(8, 1, 'Login ke sistem', '2026-07-23 13:04:48'),
(9, 1, 'Login ke sistem', '2026-07-23 13:27:18'),
(10, 1, 'Logout dari sistem', '2026-07-23 13:31:53'),
(11, 1, 'Login ke sistem', '2026-07-23 13:34:59'),
(12, 1, 'Mengubah tarif ID 1', '2026-07-23 14:08:40'),
(13, 1, 'Mengubah area parkir ID 1', '2026-07-23 14:10:43'),
(14, 1, 'Logout dari sistem', '2026-07-23 14:10:48'),
(15, 1, 'Login ke sistem', '2026-07-24 09:20:54'),
(16, 1, 'Logout dari sistem', '2026-07-24 09:21:10'),
(17, 3, 'Login ke sistem', '2026-07-24 10:24:22'),
(18, 3, 'Logout dari sistem', '2026-07-24 10:24:39'),
(19, 1, 'Login ke sistem', '2026-07-24 10:48:32'),
(20, 1, 'Logout dari sistem', '2026-07-24 10:48:48'),
(21, 4, 'Login ke sistem', '2026-07-24 10:57:05'),
(22, 4, 'Logout dari sistem', '2026-07-24 11:00:49'),
(23, 1, 'Login ke sistem', '2026-07-24 11:01:07'),
(24, 1, 'Logout dari sistem', '2026-07-24 11:01:15'),
(25, 4, 'Login ke sistem', '2026-07-24 11:01:29'),
(26, 4, 'Logout dari sistem', '2026-07-24 11:08:49'),
(27, 4, 'Login ke sistem', '2026-07-24 11:12:07'),
(28, 4, 'Logout dari sistem', '2026-07-24 11:24:33'),
(29, 4, 'Login ke sistem', '2026-07-24 12:25:13'),
(30, 4, 'Logout dari sistem', '2026-07-24 12:38:17'),
(31, 2, 'Login ke sistem', '2026-07-24 12:43:33'),
(32, 2, 'Logout dari sistem', '2026-07-24 12:44:32'),
(33, 2, 'Login ke sistem', '2026-07-24 12:44:48'),
(34, 2, 'Logout dari sistem', '2026-07-24 12:44:52'),
(35, 2, 'Login ke sistem', '2026-07-24 12:45:12'),
(36, 2, 'Logout dari sistem', '2026-07-24 12:55:25'),
(37, 4, 'Login ke sistem', '2026-07-24 12:55:39'),
(38, 4, 'Logout dari sistem', '2026-07-24 12:56:30'),
(39, 2, 'Login ke sistem', '2026-07-24 12:56:44'),
(40, 2, 'Mencatat kendaraan masuk: B 1234', '2026-07-24 12:57:56'),
(41, 2, 'Logout dari sistem', '2026-07-24 12:58:35'),
(42, 4, 'Login ke sistem', '2026-07-24 12:58:45'),
(43, 4, 'Logout dari sistem', '2026-07-24 13:02:40'),
(44, 2, 'Login ke sistem', '2026-07-24 13:02:56'),
(45, 2, 'Memproses kendaraan keluar transaksi #1', '2026-07-24 13:03:06'),
(46, 2, 'Logout dari sistem', '2026-07-24 13:03:13'),
(47, 4, 'Login ke sistem', '2026-07-24 13:03:28'),
(48, 4, 'Logout dari sistem', '2026-07-24 13:03:34'),
(49, 1, 'Login ke sistem', '2026-07-24 13:03:55'),
(50, 1, 'Logout dari sistem', '2026-07-24 13:05:03'),
(51, 4, 'Login ke sistem', '2026-07-24 13:05:19'),
(52, 4, 'Logout dari sistem', '2026-07-24 13:20:13'),
(53, 2, 'Login ke sistem', '2026-07-24 13:20:27'),
(54, 2, 'Mencatat kendaraan masuk: A 2345 (dari booking #2)', '2026-07-24 13:25:00'),
(55, 2, 'Logout dari sistem', '2026-07-24 13:25:19'),
(56, 4, 'Login ke sistem', '2026-07-24 13:25:27'),
(57, 4, 'Logout dari sistem', '2026-07-24 13:25:57'),
(58, 4, 'Login ke sistem', '2026-07-24 13:26:20'),
(59, 4, 'Logout dari sistem', '2026-07-24 13:26:52'),
(60, 2, 'Login ke sistem', '2026-07-24 13:27:10'),
(61, 2, 'Memproses kendaraan keluar transaksi #2', '2026-07-24 13:27:49'),
(62, 2, 'Logout dari sistem', '2026-07-24 13:29:34'),
(63, 4, 'Login ke sistem', '2026-07-24 13:29:44'),
(64, 1, 'Login ke sistem', '2026-08-05 07:42:08'),
(65, 1, 'Logout dari sistem', '2026-08-05 07:42:58'),
(66, 1, 'Login ke sistem', '2026-08-05 07:50:51'),
(67, 1, 'Moderasi testimoni #1: approve', '2026-08-05 07:56:56'),
(68, 1, 'Moderasi testimoni #1: approve', '2026-08-05 07:57:03'),
(69, 1, 'Logout dari sistem', '2026-08-05 08:05:27'),
(70, 4, 'Login ke sistem', '2026-08-05 08:05:38'),
(71, 4, 'Logout dari sistem', '2026-08-05 08:06:41'),
(72, 1, 'Login ke sistem', '2026-08-05 08:09:27'),
(73, 1, 'Logout dari sistem', '2026-08-05 08:10:31'),
(74, 4, 'Login ke sistem', '2026-08-05 08:10:40'),
(75, 4, 'Logout dari sistem', '2026-08-05 08:11:53'),
(76, 2, 'Login ke sistem', '2026-08-05 08:12:11'),
(77, 2, 'Mencatat kendaraan masuk: AB 1234 (dari booking #3)', '2026-08-05 08:12:42'),
(78, 2, 'Memproses kendaraan keluar transaksi #3', '2026-08-05 08:13:21'),
(79, 2, 'Logout dari sistem', '2026-08-05 08:14:05'),
(80, 4, 'Login ke sistem', '2026-08-05 08:14:20'),
(81, 4, 'Logout dari sistem', '2026-08-05 08:14:52'),
(82, 2, 'Login ke sistem', '2026-08-05 08:15:17'),
(83, 2, 'Mencatat kendaraan masuk: AB 1234 (dari booking #4)', '2026-08-05 08:15:36'),
(84, 2, 'Memproses kendaraan keluar transaksi #4', '2026-08-05 08:15:52'),
(85, 2, 'Logout dari sistem', '2026-08-05 08:16:30'),
(86, 1, 'Login ke sistem', '2026-08-05 08:16:42'),
(87, 1, 'Logout dari sistem', '2026-08-05 08:23:57'),
(88, 2, 'Login ke sistem', '2026-08-05 08:24:35'),
(89, 2, 'Logout dari sistem', '2026-08-05 08:24:47'),
(90, 3, 'Login ke sistem', '2026-08-05 08:26:20'),
(91, 3, 'Logout dari sistem', '2026-08-05 08:26:32'),
(92, 1, 'Login ke sistem', '2026-08-05 08:56:39'),
(93, 1, 'Logout dari sistem', '2026-08-05 08:59:27'),
(94, 4, 'Login ke sistem', '2026-08-05 08:59:36'),
(95, 4, 'Logout dari sistem', '2026-08-05 09:11:53'),
(96, 1, 'Login ke sistem', '2026-08-05 09:16:25'),
(97, 1, 'Mengubah area parkir ID 1', '2026-08-05 09:22:59'),
(98, 1, 'Mengubah area parkir ID 3', '2026-08-05 09:23:04'),
(99, 1, 'Mengubah area parkir ID 1', '2026-08-05 09:23:13'),
(100, 1, 'Logout dari sistem', '2026-08-05 09:23:17'),
(101, 4, 'Login ke sistem', '2026-08-05 09:23:31'),
(102, 4, 'Logout dari sistem', '2026-08-05 09:25:10'),
(103, 1, 'Login ke sistem', '2026-08-05 09:25:20'),
(104, 1, 'Logout dari sistem', '2026-08-05 09:25:30'),
(105, 2, 'Login ke sistem', '2026-08-05 09:25:44'),
(106, 2, 'Logout dari sistem', '2026-08-05 09:26:09'),
(107, 4, 'Login ke sistem', '2026-08-05 09:26:17'),
(108, 4, 'Logout dari sistem', '2026-08-05 09:27:10'),
(109, 2, 'Login ke sistem', '2026-08-05 09:27:25'),
(110, 2, 'Logout dari sistem', '2026-08-05 10:02:54'),
(111, 4, 'Login ke sistem', '2026-08-05 10:03:04'),
(112, 4, 'Logout dari sistem', '2026-08-05 10:03:23'),
(113, 2, 'Login ke sistem', '2026-08-05 10:03:37'),
(114, 2, 'Mencatat kendaraan masuk: B 1234 (dari booking #1)', '2026-08-05 10:03:53'),
(115, 2, 'Mencatat kendaraan masuk: BA 1234 (dari booking #10)', '2026-08-05 10:04:06'),
(116, 2, 'Logout dari sistem', '2026-08-05 10:04:29'),
(117, 4, 'Login ke sistem', '2026-08-05 10:04:37'),
(118, 4, 'Logout dari sistem', '2026-08-05 10:05:24'),
(119, 1, 'Login ke sistem', '2026-08-05 10:06:09'),
(120, 1, 'Mengubah area parkir ID 1', '2026-08-05 10:06:44'),
(121, 1, 'Mengubah area parkir ID 2', '2026-08-05 10:06:55'),
(122, 1, 'Mengubah area parkir ID 3', '2026-08-05 10:07:02'),
(123, 1, 'Logout dari sistem', '2026-08-05 10:11:44'),
(124, 1, 'Login ke sistem', '2026-08-05 10:34:50'),
(125, 1, 'Logout dari sistem', '2026-08-05 10:39:33'),
(126, 4, 'Login ke sistem', '2026-08-05 10:39:49'),
(127, 4, 'Logout dari sistem', '2026-08-05 10:51:56'),
(128, 1, 'Login ke sistem', '2026-08-05 12:38:49'),
(129, 1, 'Logout dari sistem', '2026-08-05 12:58:13'),
(130, 4, 'Login ke sistem', '2026-08-05 12:58:26'),
(131, 4, 'Logout dari sistem', '2026-08-05 13:06:43'),
(132, 4, 'Login ke sistem', '2026-08-05 13:07:03'),
(133, 4, 'Logout dari sistem', '2026-08-05 13:07:08'),
(134, 2, 'Login ke sistem', '2026-08-05 13:07:27'),
(135, 2, 'Logout dari sistem', '2026-08-05 13:26:52'),
(136, 3, 'Login ke sistem', '2026-08-05 13:27:06'),
(137, 2, 'Login ke sistem', '2026-08-10 08:04:26'),
(138, 2, 'Logout dari sistem', '2026-08-10 08:04:50'),
(139, 2, 'Login ke sistem', '2026-08-10 08:05:18'),
(140, 2, 'Logout dari sistem', '2026-08-10 08:05:30'),
(141, 1, 'Login ke sistem', '2026-08-10 08:05:42'),
(142, 1, 'Logout dari sistem', '2026-08-10 08:05:46'),
(143, 4, 'Login ke sistem', '2026-08-10 08:06:23'),
(144, 4, 'Logout dari sistem', '2026-08-10 08:06:36'),
(145, 2, 'Login ke sistem', '2026-08-10 08:06:52'),
(146, 2, 'Logout dari sistem', '2026-08-10 08:15:23'),
(147, 2, 'Login ke sistem', '2026-08-10 08:15:40'),
(148, 2, 'Logout dari sistem', '2026-08-10 08:15:47'),
(149, 3, 'Login ke sistem', '2026-08-10 08:16:05'),
(150, 3, 'Logout dari sistem', '2026-08-10 08:20:39'),
(151, 4, 'Login ke sistem', '2026-08-10 08:21:15'),
(152, 4, 'Logout dari sistem', '2026-08-10 08:22:33'),
(153, 2, 'Login ke sistem', '2026-08-10 08:22:52'),
(154, 2, 'Mencatat kendaraan masuk: BC 231 (dari booking #11)', '2026-08-10 08:23:25'),
(155, 2, 'Memproses kendaraan keluar transaksi #7', '2026-08-10 08:24:13'),
(156, 2, 'Memproses kendaraan keluar transaksi #6', '2026-08-10 08:24:29'),
(157, 2, 'Memproses kendaraan keluar transaksi #5', '2026-08-10 08:24:39'),
(158, 2, 'Logout dari sistem', '2026-08-10 08:33:05'),
(159, 1, 'Login ke sistem', '2026-08-10 08:33:20'),
(160, 1, 'Mengubah tarif ID 3', '2026-08-10 08:33:43'),
(161, 1, 'Mengubah tarif ID 4', '2026-08-10 08:34:06'),
(162, 1, 'Menghapus tarif ID 3', '2026-08-10 08:34:09'),
(163, 1, 'Menghapus tarif ID 4', '2026-08-10 08:34:11'),
(164, 1, 'Logout dari sistem', '2026-08-10 08:44:40'),
(165, 1, 'Login ke sistem', '2026-08-10 08:45:26'),
(166, 1, 'Menambahkan tarif jenis truk/bus', '2026-08-10 08:57:06'),
(167, 1, 'Logout dari sistem', '2026-08-10 08:57:11'),
(168, 2, 'Login ke sistem', '2026-08-10 08:57:25'),
(169, 2, 'Logout dari sistem', '2026-08-10 09:03:25'),
(170, 1, 'Login ke sistem', '2026-08-10 09:03:37'),
(171, 1, 'Menambahkan tarif jenis sepeda', '2026-08-10 09:03:49'),
(172, 1, 'Logout dari sistem', '2026-08-10 09:03:56'),
(173, 4, 'Login ke sistem', '2026-08-10 09:04:19'),
(174, 4, 'Logout dari sistem', '2026-08-10 09:12:06'),
(175, 4, 'Login ke sistem', '2026-08-10 09:12:52'),
(176, 4, 'Logout dari sistem', '2026-08-10 09:14:31'),
(177, 2, 'Login ke sistem', '2026-08-10 09:14:47'),
(178, 2, 'Logout dari sistem', '2026-08-10 09:16:34'),
(179, 1, 'Login ke sistem', '2026-08-10 09:16:47'),
(180, 1, 'Menghapus tarif ID 6', '2026-08-10 09:16:54'),
(181, 1, 'Logout dari sistem', '2026-08-10 09:21:38'),
(182, 3, 'Login ke sistem', '2026-08-10 09:22:04'),
(183, 3, 'Logout dari sistem', '2026-08-10 09:33:03'),
(184, 4, 'Login ke sistem', '2026-08-10 09:33:16'),
(185, 4, 'Logout dari sistem', '2026-08-10 09:33:39'),
(186, 2, 'Login ke sistem', '2026-08-10 09:33:56'),
(187, 2, 'Mencatat kendaraan masuk: AB 1234 (dari booking #12)', '2026-08-10 09:34:49'),
(188, 2, 'Memproses kendaraan keluar transaksi #8', '2026-08-10 09:35:00'),
(189, 2, 'Logout dari sistem', '2026-08-10 10:39:05'),
(190, 2, 'Login ke sistem', '2026-08-10 11:10:36'),
(191, 2, 'Mencatat kendaraan masuk: AD 1234 (dari booking #9)', '2026-08-10 11:10:49'),
(192, 2, 'Memproses kendaraan keluar transaksi #9 (bayar: TUNAI)', '2026-08-10 11:11:19'),
(193, 2, 'Logout dari sistem', '2026-08-10 11:11:22'),
(194, 4, 'Login ke sistem', '2026-08-10 11:11:37'),
(195, 4, 'Logout dari sistem', '2026-08-10 11:30:08'),
(196, 2, 'Login ke sistem', '2026-08-10 11:30:25'),
(197, 2, 'Mencatat kendaraan masuk: AB 1234 (dari booking #6)', '2026-08-10 11:30:31'),
(198, 2, 'Memproses kendaraan keluar transaksi #10 (bayar: QRIS)', '2026-08-10 11:32:37'),
(199, 2, 'Mencatat kendaraan masuk: AD 1234 (dari booking #8)', '2026-08-10 11:33:41'),
(200, 2, 'Logout dari sistem', '2026-08-10 12:37:43'),
(201, 4, 'Login ke sistem', '2026-08-10 12:38:08'),
(202, 4, 'Logout dari sistem', '2026-08-10 12:38:51');

-- --------------------------------------------------------

--
-- Table structure for table `tb_tarif`
--

CREATE TABLE `tb_tarif` (
  `id_tarif` int(11) NOT NULL,
  `jenis_kendaraan` enum('motor','mobil','truk/bus') NOT NULL,
  `tarif_per_jam` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_tarif`
--

INSERT INTO `tb_tarif` (`id_tarif`, `jenis_kendaraan`, `tarif_per_jam`) VALUES
(1, 'motor', 2000),
(2, 'mobil', 5000),
(5, 'truk/bus', 8000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi`
--

CREATE TABLE `tb_transaksi` (
  `id_parkir` int(11) NOT NULL,
  `id_kendaraan` int(11) NOT NULL,
  `waktu_masuk` datetime NOT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `id_tarif` int(11) NOT NULL,
  `durasi_jam` int(5) DEFAULT NULL,
  `biaya_total` decimal(10,0) DEFAULT NULL,
  `metode_bayar` enum('tunai','qris') NOT NULL DEFAULT 'tunai',
  `status` enum('masuk','keluar') NOT NULL DEFAULT 'masuk',
  `id_user` int(11) NOT NULL,
  `id_area` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_transaksi`
--

INSERT INTO `tb_transaksi` (`id_parkir`, `id_kendaraan`, `waktu_masuk`, `waktu_keluar`, `id_tarif`, `durasi_jam`, `biaya_total`, `metode_bayar`, `status`, `id_user`, `id_area`) VALUES
(1, 1, '2026-07-24 12:57:56', '2026-07-24 13:03:06', 1, 5, 10000, 'tunai', 'keluar', 2, 3),
(2, 2, '2026-07-24 13:25:00', '2026-07-24 13:27:49', 2, 5, 25000, 'tunai', 'keluar', 2, 2),
(3, 3, '2026-08-05 08:12:42', '2026-08-05 08:13:21', 1, 5, 10000, 'tunai', 'keluar', 2, 1),
(4, 3, '2026-08-05 08:15:36', '2026-08-05 08:15:52', 1, 5, 10000, 'tunai', 'keluar', 2, 1),
(5, 1, '2026-08-05 10:03:53', '2026-08-10 08:24:39', 1, 114, 228000, 'tunai', 'keluar', 2, 1),
(6, 5, '2026-08-05 10:04:06', '2026-08-10 08:24:29', 1, 114, 228000, 'tunai', 'keluar', 2, 1),
(7, 6, '2026-08-10 08:23:25', '2026-08-10 08:24:13', 1, 5, 10000, 'tunai', 'keluar', 2, 1),
(8, 3, '2026-08-10 09:34:49', '2026-08-10 09:35:00', 1, 5, 10000, 'tunai', 'keluar', 2, 1),
(9, 4, '2026-08-10 11:10:49', '2026-08-10 11:11:19', 1, 5, 10000, 'tunai', 'keluar', 2, 1),
(10, 3, '2026-08-10 11:30:31', '2026-08-10 11:32:37', 1, 5, 10000, 'qris', 'keluar', 2, 1),
(11, 4, '2026-08-10 11:33:41', NULL, 1, NULL, NULL, 'tunai', 'masuk', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas','owner','member') NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `nama_lengkap`, `username`, `password`, `role`, `foto`, `status_aktif`, `created_at`) VALUES
(1, 'Administrator Gacoan', 'admin', '$2y$10$R.uq7kBi9mQYNBXw.Bita.RyG3Pt7hFh7xw0bztIGVAoDsDIY67ua', 'admin', 'user_1_1785908361.jpeg', 1, '2026-07-23 04:03:38'),
(2, 'Rian Petugas', 'petugas', '$2y$10$DeJ.k/RH8tnNgbSgbr90ZeeT/bs8r3nobrpMDTu3dONIUS1bV330K', 'petugas', 'user_2_1785911186.jpeg', 1, '2026-07-23 04:03:38'),
(3, 'Coach Dedi', 'owner', '$2y$10$2DhC/jwMEh3aTqOsVUyCGu7mTrR8/cIBMvbwiF7Oo/pOzdawoypX2', 'owner', 'user_3_1785911535.jpeg', 1, '2026-07-23 04:03:38'),
(4, 'awan dwi', 'awan', '$2y$10$or9vxOEJ/vwAr34IayeFOuFhdBGjxtdPT4FQX.NytUMoC0XgumwVe', 'member', 'user_4_1785909519.jpeg', 1, '2026-07-24 03:56:50');

-- --------------------------------------------------------

--
-- Table structure for table `testimoni`
--

CREATE TABLE `testimoni` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Pengguna',
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `komentar` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `testimoni`
--

INSERT INTO `testimoni` (`id`, `nama`, `role`, `rating`, `komentar`, `status`, `created_at`) VALUES
(1, 'awan dwi', 'member', 5, 'sangat bagus', 'approved', '2026-08-05 07:41:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  ADD PRIMARY KEY (`id_area`);

--
-- Indexes for table `tb_booking`
--
ALTER TABLE `tb_booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_kendaraan` (`id_kendaraan`),
  ADD KEY `fk_booking_area` (`id_area`);

--
-- Indexes for table `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD KEY `fk_kendaraan_user` (`id_user`);

--
-- Indexes for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_log_user` (`id_user`);

--
-- Indexes for table `tb_tarif`
--
ALTER TABLE `tb_tarif`
  ADD PRIMARY KEY (`id_tarif`);

--
-- Indexes for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id_parkir`),
  ADD KEY `fk_transaksi_kendaraan` (`id_kendaraan`),
  ADD KEY `fk_transaksi_tarif` (`id_tarif`),
  ADD KEY `fk_transaksi_user` (`id_user`),
  ADD KEY `fk_transaksi_area` (`id_area`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `testimoni`
--
ALTER TABLE `testimoni`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_booking`
--
ALTER TABLE `tb_booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=203;

--
-- AUTO_INCREMENT for table `tb_tarif`
--
ALTER TABLE `tb_tarif`
  MODIFY `id_tarif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  MODIFY `id_parkir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `testimoni`
--
ALTER TABLE `testimoni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_booking`
--
ALTER TABLE `tb_booking`
  ADD CONSTRAINT `fk_booking_area` FOREIGN KEY (`id_area`) REFERENCES `tb_area_parkir` (`id_area`),
  ADD CONSTRAINT `tb_booking_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`),
  ADD CONSTRAINT `tb_booking_ibfk_2` FOREIGN KEY (`id_kendaraan`) REFERENCES `tb_kendaraan` (`id_kendaraan`);

--
-- Constraints for table `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  ADD CONSTRAINT `fk_kendaraan_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD CONSTRAINT `fk_transaksi_area` FOREIGN KEY (`id_area`) REFERENCES `tb_area_parkir` (`id_area`),
  ADD CONSTRAINT `fk_transaksi_kendaraan` FOREIGN KEY (`id_kendaraan`) REFERENCES `tb_kendaraan` (`id_kendaraan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transaksi_tarif` FOREIGN KEY (`id_tarif`) REFERENCES `tb_tarif` (`id_tarif`),
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
