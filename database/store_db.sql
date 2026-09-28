-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 28 Sep 2026 pada 06.38
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `store_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Umum',
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `image`, `price`, `stock`, `created_at`) VALUES
(5, 'Kamera Canon M50', 'Kamera & Lensa', '1790371047_EOSM50SupportFirmwareSoftwareManuals.jpg', 7000000.00, 4, '2026-09-25 21:17:27'),
(8, 'Gimbal  Zhiyun Crane M2S', 'Aksesori & Stabilizer', '1790372934_WhatsAppImage20260926at16.47.43.jpeg', 4000000.00, 5, '2026-09-25 21:48:54'),
(9, 'Lensa Canon EF 50mm', 'Kamera & Lensa', '1790376897_WhatsAppImage20260926at17.54.50.jpeg', 1800000.00, 9, '2026-09-25 22:54:57'),
(10, 'DJI Osmo Pocket 3', 'Kamera & Lensa', '1790377100_WhatsAppImage20260926at17.58.21.jpeg', 8500000.00, 6, '2026-09-25 22:58:21'),
(11, 'SanDisk Extreme Pro SDXC 128GB', 'Aksesori & Stabilizer', '1790377327_WhatsAppImage20260926at18.02.07.jpeg', 400000.00, 32, '2026-09-25 23:02:07'),
(12, 'Lampu Studio Godox SL60W Video Light', 'Studio & Alat Desain', '1790377616_WhatsAppImage20260926at18.06.52.jpeg', 1500000.00, 10, '2026-09-25 23:06:56'),
(14, 'Pen Tablet Wacom Intuos Medium', 'Studio & Alat Desain', '1790378037_WhatsAppImage20260926at18.12.46.jpeg', 2800000.00, 15, '2026-09-25 23:13:57'),
(15, 'Boya BY-M1 Lavalier Microphone', 'Audio & Recording', '1790378323_WhatsAppImage20260926at18.18.45.jpeg', 150000.00, 50, '2026-09-25 23:18:43'),
(17, 'Tripod Takara Rover 66', 'Aksesori & Stabilizer', '1790569830_WhatsApp Image 2026-09-26 at 18.24.22.jpeg', 750000.00, 17, '2026-09-25 23:25:18');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
