-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Sep 2026 pada 07.23
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_basarnas_bogor`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `draft_laporan`
--

CREATE TABLE `draft_laporan` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` varchar(50) DEFAULT NULL,
  `nama_kegiatan` varchar(150) DEFAULT NULL,
  `jenis_kegiatan` varchar(100) DEFAULT NULL,
  `opsi` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('Draft','Selesai') DEFAULT 'Draft',
  `dokumentasi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `draft_laporan`
--

INSERT INTO `draft_laporan` (`id`, `tanggal`, `waktu`, `nama_kegiatan`, `jenis_kegiatan`, `opsi`, `deskripsi`, `status`, `dokumentasi`) VALUES
(14, '2026-09-16', '09:23:32', 'Briefing Siaga', 'Siaga SAR Rutin', 'Briefing Siaga', 'Melaksanakan briefing serah terima siaga', 'Draft', '1789525412_0_1001579649.jpg'),
(15, '2026-09-16', '09:54:41', 'Water Rescue', 'Keterampilan Teknis', 'Water Rescue', 'Mengikuti webinar Pemeliharaan Motor Tempel Puslat Basarnas', 'Draft', '1789527281_0_1001579689.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `jenis_kegiatan` varchar(100) NOT NULL,
  `press_release` text NOT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_final`
--

CREATE TABLE `laporan_final` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `nama_kegiatan` varchar(255) NOT NULL,
  `jenis_kegiatan` varchar(255) NOT NULL,
  `opsi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `laporan_final`
--

INSERT INTO `laporan_final` (`id`, `tanggal`, `waktu`, `nama_kegiatan`, `jenis_kegiatan`, `opsi`) VALUES
(1, '2026-09-03', '08:00:00', 'Briefing Siaga', 'Siaga SAR Rutin', 'Briefing Siaga'),
(2, '2026-09-03', '09:00:20', 'Samapta A & B', 'Pembinaan Fisik', 'Kegiatan Lainnya'),
(3, '2026-09-03', '15:51:30', 'Pengecekan Palsar (Foto Tabel)', 'Siaga SAR Rutin', 'Kegiatan Lainnya'),
(4, '2026-09-03', '15:58:17', 'Pemeliharaan Palsar', 'Alat Air', 'Pemeliharaan Palsar'),
(5, '2026-09-03', '15:59:27', 'Pengecekan Palsar (Eviden Lapangan)', 'Siaga SAR Rutin', 'Kegiatan Lainnya'),
(6, '2026-09-03', '16:07:51', 'Briefing Siaga', 'Siaga SAR Rutin', 'Briefing Siaga'),
(7, '2026-09-03', '16:09:01', 'Pengecekan Kendaraan Operasional', 'Siaga SAR Rutin', 'Kegiatan Lainnya'),
(10, '2026-09-09', '12:53:56', 'Operasi SAR', 'Kondisi Membahayakan Manusia', 'Operasi SAR'),
(11, '2026-09-09', '12:54:23', 'Narasumber', 'Kegiatan Lain', 'Narasumber'),
(12, '2026-09-09', '13:01:57', 'Operasi SAR', 'Kecelakaan Darat', 'Operasi SAR'),
(13, '2026-09-09', '13:02:29', 'Pemeliharaan Palsar', 'Alat Jungle', 'Pemeliharaan Palsar'),
(14, '2026-09-09', '13:11:35', 'MFR', 'Keterampilan Teknis', 'MFR'),
(15, '2026-09-09', '13:11:59', 'Koordinasi', 'Kegiatan Lain', 'Koordinasi'),
(16, '2026-09-09', '13:13:08', 'MFR', 'Keterampilan Teknis', 'MFR');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rekap_laporan`
--

CREATE TABLE `rekap_laporan` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `total_kegiatan` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `file_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`) VALUES
(1, 'unitsiagasarbogor', '12345');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `draft_laporan`
--
ALTER TABLE `draft_laporan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `laporan_final`
--
ALTER TABLE `laporan_final`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `rekap_laporan`
--
ALTER TABLE `rekap_laporan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `draft_laporan`
--
ALTER TABLE `draft_laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `laporan_final`
--
ALTER TABLE `laporan_final`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `rekap_laporan`
--
ALTER TABLE `rekap_laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
