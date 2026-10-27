-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2026 at 09:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_street_ni`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(1, 'Minna', 'Minna1213'),
(2, 'streetni_admin', 'Minna1213'),
(4, 'admin', '$2y$10$2PyJ2b8JNF46HzJlhw0BUeCt5HXjZWA.fYiuLO/zOxttl1sgHK.pq');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(14, 14, 3, 1, 10000.00, 10000.00),
(15, 15, 22, 1, 15000.00, 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Coffee'),
(2, 'Ice Coffee'),
(3, 'Non Coffee/Tea'),
(4, 'Mocktail');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `google_sub` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `metode_login` enum('hp','google') NOT NULL DEFAULT 'hp',
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `no_hp`, `email`, `google_sub`, `password_hash`, `metode_login`, `tanggal_daftar`) VALUES
(1, 'Mirnawati', '081342703957', NULL, NULL, '$2y$10$jtVe8lXsCQS9nHssAUyJzuVgylE5uFfQZpca5evBGo7OqH3GPXlfm', 'hp', '2026-10-24 08:22:34');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `nama_pemesan` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal_pesanan` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) NOT NULL,
  `status_pesanan` varchar(30) DEFAULT 'Menunggu',
  `id_pelanggan` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `nama_pemesan`, `no_hp`, `catatan`, `tanggal_pesanan`, `total_harga`, `status_pesanan`, `id_pelanggan`) VALUES
(14, 'ichal', '081244541335', 'lAST SUGAR', '2026-10-19 08:39:28', 10000.00, 'Selesai', NULL),
(15, 'Mulkia', '081342703957', 'sedikit gula', '2026-10-24 05:56:26', 15000.00, 'Selesai', 1);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_kategori`, `nama_produk`, `deskripsi`, `harga`, `foto`) VALUES
(1, 1, 'Kopi Susu', NULL, 10000.00, 'kopi_susu.jpeg'),
(2, 1, 'Espresso', NULL, 10000.00, 'espresso.jpeg'),
(3, 1, 'Americano', NULL, 10000.00, 'americano.jpeg'),
(4, 2, 'Americano Full Arabika', NULL, 18000.00, 'americano_full_arabica.jpeg'),
(5, 2, 'Americano Blend', NULL, 15000.00, 'americano_blend.jpeg'),
(6, 2, 'Americano Gembira', NULL, 15000.00, 'americano_gembira.jpeg'),
(7, 2, 'Americano Cranberry', NULL, 15000.00, 'americano_cranberry.jpeg'),
(8, 2, 'Kopi Susu', NULL, 15000.00, 'kopi_susu_1.jpeg'),
(9, 2, 'Aren Street', NULL, 15000.00, 'aren_street.jpeg'),
(10, 2, 'Aren Extra N.I', NULL, 15000.00, 'aren_extra_n.i.jpeg'),
(11, 2, 'Hazelnut', NULL, 15000.00, 'hazelnut.jpeg'),
(12, 2, 'Butterscotch', NULL, 15000.00, 'butterscotch.jpeg'),
(13, 2, 'Pisachio N.I', NULL, 15000.00, NULL),
(14, 2, 'Caramel Macchiato', NULL, 15000.00, 'caramel.jpeg'),
(15, 3, 'Matcha Milk', NULL, 15000.00, 'matcha milk.jpeg'),
(16, 3, 'Lemon Tea', NULL, 15000.00, 'lemon tea.jpeg'),
(17, 3, 'Lemon Madu', NULL, 15000.00, 'lemon_madu.jpeg'),
(18, 3, 'Thai Tea', NULL, 15000.00, 'thai_tea.jpeg'),
(19, 3, 'Chocolate Milk', NULL, 15000.00, 'chocolate_milk.jpeg'),
(20, 4, 'Mangga Milk', NULL, 15000.00, 'mangga_milk.jpeg'),
(21, 4, 'Blue Stroberry', NULL, 15000.00, 'blue_stroberry.jpeg'),
(22, 4, 'Stroberry Mangga', NULL, 15000.00, NULL),
(23, 4, 'Wilberry Lemon', NULL, 15000.00, NULL),
(24, 4, 'Mangga Wilberry', NULL, 15000.00, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `no_hp` (`no_hp`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_sub`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `idx_pesanan_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`),
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
