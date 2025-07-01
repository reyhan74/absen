-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 11 Jun 2025 pada 14.34
-- Versi server: 10.11.10-MariaDB-log
-- Versi PHP: 8.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absen`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `username` varchar(225) NOT NULL,
  `password` varchar(225) NOT NULL,
  `status` varchar(20) NOT NULL,
  `role` varchar(20) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `jenis_kelamin` varchar(50) NOT NULL,
  `alamat` varchar(67) NOT NULL,
  `no_handphone` varchar(34) NOT NULL,
  `lokasi_presensi` varchar(56) NOT NULL,
  `foto` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru`
--

INSERT INTO `guru` (`id`, `username`, `password`, `status`, `role`, `nama`, `jenis_kelamin`, `alamat`, `no_handphone`, `lokasi_presensi`, `foto`) VALUES
(8, 'admin', '$2y$10$LAIeCIMholKGi4YF6OI.4OI4/2muNk3L51EYlF9Xu5OvMS1HA2Nr2', 'aktif', 'admin', 'guru', 'laki-laki', 'njfd', '5tr', 'xdjt', 'foto/hero-bg.jpg'),
(12, 'guru', '$2y$10$Cs67De1w9ayscfueULVovud8U25/db7.Aev/R4sRNs8SJKDfEoMUy', 'aktif', 'guru', 'guru', 'laki-laki', 'puncu', '087957', '0poqje', '../../siswa/home/profilemasuk_2025-05-20_05-07-36.png'),
(13, 'reyhan', '$2y$10$S69o4iKyNdmNTFGEe3j9Buz6UMbaJebDncyB5ENcYrpa5b7Ml4K2O', 'aktif', 'guru', 'reyhan', 'laki-laki', 'pare', '082334158533', '0poqje', '../../siswa/home/profilelogo_cb.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatan`
--

CREATE TABLE `jabatan` (
  `id` int(11) NOT NULL,
  `jabatan` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `jabatan`
--

INSERT INTO `jabatan` (`id`, `jabatan`) VALUES
(2, 'admin'),
(3, 'guru'),
(4, 'siswa');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lokasi_presensi`
--

CREATE TABLE `lokasi_presensi` (
  `id` int(11) NOT NULL,
  `nama_lokasi` varchar(255) NOT NULL,
  `alamat_lokasi` varchar(255) NOT NULL,
  `tipe_lokasi` varchar(255) NOT NULL,
  `latitut` varchar(50) NOT NULL,
  `longitude` varchar(50) NOT NULL,
  `radius` int(11) NOT NULL,
  `zona_waktu` varchar(4) NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `lokasi_presensi`
--

INSERT INTO `lokasi_presensi` (`id`, `nama_lokasi`, `alamat_lokasi`, `tipe_lokasi`, `latitut`, `longitude`, `radius`, `zona_waktu`, `jam_masuk`, `jam_pulang`) VALUES
(1, 'Kampus 1', 'adfadfafda', 'Pusat', '-7.76636150824456', '112.19107524207931', 100, 'WIB', '13:48:00', '13:49:00'),
(11, 'Kampus 2', '3io', 'Cabang', '-7,8582990', '112,2648048', 20, 'WIB', '16:10:00', '20:10:00'),
(13, 'Kampus 3', 'adfadfafda', 'Kampus 4', '-7.766353', '112.1818823', 100, 'WIB', '22:36:00', '22:33:00'),
(14, 'cb', 'pare', 'pusat', '-7.766243713589902', '112.19100419629886', 200000, 'WIB', '17:30:11', '22:33:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pegawai`
--

CREATE TABLE `pegawai` (
  `id` int(11) NOT NULL,
  `nis` varchar(50) NOT NULL,
  `nama` varchar(225) NOT NULL,
  `jenis_kelamin` varchar(10) NOT NULL,
  `alamat` varchar(225) NOT NULL,
  `no_handphone` varchar(20) NOT NULL,
  `level` varchar(20) NOT NULL,
  `lokasi_Presensi` varchar(50) NOT NULL,
  `foto` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `pegawai`
--

INSERT INTO `pegawai` (`id`, `nis`, `nama`, `jenis_kelamin`, `alamat`, `no_handphone`, `level`, `lokasi_Presensi`, `foto`) VALUES
(1, '000001', 'Reyhan Dwiandika', 'laki laki', 'jl siaga 2', '081805256116', 'admin', 'Kampus 1', 'rhn.jpg'),
(4, '000002', 'Rayu Fajaruni', 'Perempuan', 'resa', '0816667890', 'pegawai', '009575/69860', 'foto/Screenshot 2024-07-18 185554.png'),
(5, '000004', 'Rayu Fajaruni', 'Perempuan', 'resa', '0816667890', 'guru', '009575/69860', 'foto/Screenshot 2024-07-18 185554.png'),
(6, '000003', 'sadang', 'Laki-laki', 'pare', '087957', 'siswa', '2987098/87-98790', 'foto/Capture.PNG'),
(7, '000006', 'sadang', 'Laki-laki', 'pare', '087957', 'siswa', '2987098/87-98790', 'foto/Capture.PNG');

-- --------------------------------------------------------

--
-- Struktur dari tabel `presensi`
--

CREATE TABLE `presensi` (
  `id` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `jam_masuk` time NOT NULL,
  `foto_masuk` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data untuk tabel `presensi`
--

INSERT INTO `presensi` (`id`, `id_siswa`, `tanggal_masuk`, `jam_masuk`, `foto_masuk`) VALUES
(106, 33, '2025-06-10', '10:01:40', 'masuk_2025-06-10_10-01-40.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `presensi_out`
--

CREATE TABLE `presensi_out` (
  `id` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `tanggal_keluar` date NOT NULL,
  `jam_keluar` time NOT NULL,
  `foto_keluar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(225) NOT NULL,
  `no_absen` varchar(2) NOT NULL,
  `nama` varchar(225) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `jenis_kelamin` varchar(20) NOT NULL,
  `alamat` varchar(20) NOT NULL,
  `no_handphone` varchar(50) NOT NULL,
  `lokasi_presensi` varchar(50) NOT NULL,
  `foto` varchar(67) NOT NULL,
  `status` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `no_absen`, `nama`, `kelas`, `jenis_kelamin`, `alamat`, `no_handphone`, `lokasi_presensi`, `foto`, `status`) VALUES
(15, '000001', '1', 'NUR WAFIROTUL MAGFIROH', 'XI TKJ 3', 'Perempuan', 'pare', '085707149416', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  NUR WAFIROTU  7146.JPG', 'aktif'),
(16, '000002', '2', 'NURMA FEBRIYANTI', 'XI TKJ 3', 'Perempuan', 'Ds.kepung barat,Kec.', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  NURMA FEBRIY  7103.JPG', 'aktif'),
(17, '000003', '3', 'NURVITA DYAH PUSPITONINGRUM', 'XI TKJ 3', 'Perempuan', 'JL. SLAMET NO.19 RT.', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  NURVITA DYAH  7107.JPG', 'aktif'),
(18, '000004', '4', 'OLIVIA TRISNA WULANDARI', 'XI TKJ 3', 'Perempuan', 'pare', '085755254982', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  OLIVIA TRISN  7092.JPG', 'aktif'),
(19, '000005', '5', 'PANJI ANGGER RAMADANI', 'XI TKJ 3', 'Laki-Laki', 'pare', '088991877383', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  PANJI ANGGER  7083.JPG', 'aktif'),
(20, '000006', '6', 'PINGKAN WULANDARI DZA', 'XI TKJ 3', 'Laki-Laki', 'pare', '085784567026', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  PINGKAN WULA  7106.JPG', 'aktif'),
(21, '000007', '7', 'PYCO MAHARANI', 'X TKJ 3', 'Laki-Laki', 'pare', '089625490030', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  PYCO MAHARAN  7090.JPG', 'aktif'),
(23, '000008', '8', 'QONI&#039;ATUZ ZAHRA VELIRA ADISTIA', 'XI TKJ 3', 'Laki-Laki', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  QONI\'ATUZ ZA  7098.JPG', 'aktif'),
(24, '000009', '9', 'RADITYA REZA EKA PRATAMA', 'XI TKJ 3', 'Laki-Laki', 'Kec. Badas Kab. Kedi', '085713117031', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  RADITYA REZA  7078.JPG', 'aktif'),
(25, '000010', '10', 'RAFIASKA DETALENTA PUTRA', 'XI TKJ 3', 'Laki-Laki', 'pare', '082337829623', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  RAFIASKA DET  7084.JPG', 'aktif'),
(26, '000011', '11', 'RAHMA KURNIA DEWI', 'XI TKJ 3', 'Perempuan', 'pare', '087758034839', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  RAHMA KURNIA  7110.JPG', 'aktif'),
(27, '000012', '12', 'RAKHA AZZAHRO ALVASTO', 'XI TKJ 3', 'Laki-Laki', 'pare', '085707291352', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  RAKHA AZZARO  7147.JPG', 'aktif'),
(28, '000013', '13', 'RARATRISMA HANIKMATUL RIZKI', 'XI TKJ 3', 'Perempuan', 'pare', '082123146657', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  RARA TRISMA   7102.JPG', 'aktif'),
(29, '000014', '14', 'RAYA AISYA PUTRI', 'XI TKJ 3', 'Perempuan', 'pare', '089509947864', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  RAYA AISYA P  7100.JPG', 'aktif'),
(30, '000015', '15', 'RAYU FAJARUNI', 'XI TKJ 3', 'Perempuan', 'pare', '082228289216', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  RAYU FAJARUN  7086.JPG', 'aktif'),
(31, '000016', '16', 'REVA AZZAHRA', 'XI TKJ 3', 'Perempuan', 'pare', '08888', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  REVA AZZAHRA  7091.JPG', 'aktif'),
(32, '000017', '17', 'REVI MARSKA', 'XI TKJ 3', 'Laki-Laki', 'pare', '083112289114', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  REVI MARISKA  7089.JPG', 'aktif'),
(33, '000018', '18', 'REYHAN DWIANDIKA ', 'XI TKJ 3', 'Laki-Laki', 'pare', '081815256116', 'Kampus 1', '../../assets/img/profile_siswa/TKJ3  REYHAN DWIAN  7085.JPG', 'aktif'),
(34, '000019', '19', 'ROBI&#039;AH AL ADAWIYAH ', 'XI TKJ 3', 'Perempuan', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  ROBI\'AH AL A  7096.JPG', 'aktif'),
(35, '000020', '20', 'ROFIATUL SAFIRA', 'XI TKJ 3', 'Perempuan', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  ROFIATUL SAF  7097.JPG', 'aktif'),
(36, '000021', '21', 'SATRIA MAJID', 'XI TKJ 3', 'Laki-Laki', 'Badas', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  SATRIA MAJID  7076.JPG', 'aktif'),
(37, '000022', '22', 'SEFINA RAMADHANI', 'XI TKJ 3', 'Perempuan', 'jombangan', '085852701829', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  SEFINA RAMAD  7099.JPG', 'aktif'),
(38, '000023', '23', 'SELA DWI KIRANA ', 'XI TKJ 3', 'Laki-Laki', 'pare', '081553546241', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  SELA DWI SUR  7079.JPG', 'aktif'),
(39, '000024', '24', 'SELLVIA MAJID SANTOSO', 'XI TKJ 3', 'Perempuan', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  SELLVIA MAJI  7105.JPG', 'aktif'),
(40, '000025', '25', 'SHADANG ADRIANSYAH MAULANA R.', 'XI TKJ 3', 'Laki-Laki', 'pare', '085232186580', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  SHADANG ANDR  0021.jpg', 'aktif'),
(41, '000026', '26', 'SHAFIRA NURUL AFIFFAH', 'XI TKJ 3', 'Perempuan', 'pare', '085784575908', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  SHAFIRA NURU  7104.JPG', 'aktif'),
(42, '000027', '27', 'SYLVIA YULVIANA', 'XI TKJ 3', 'Perempuan', 'pare', '087851658334', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  SYLVIA YULVI  7109.JPG', 'aktif'),
(43, '000028', '28', 'TAUFIQ AKBAR WAKHID MAULANA', 'XI TKJ 3', 'Laki-Laki', 'puncu', '082338005558', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  TAUFIQ AKBAR  7075.JPG', 'aktif'),
(44, '000029', '29', 'TIARA SILVI RAHAYU', 'XI TKJ 3', 'Perempuan', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  TIARA SILVI   7095.JPG', 'aktif'),
(45, '000030', '30', 'TONI LUCKMAN HADY LULUT WIJAYA', 'XI TKJ 3', 'Perempuan', 'puncu', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  TONI LUCKMAN  7077.JPG', 'aktif'),
(46, '000031', '31', 'VALENTINO FAHRESI', 'XI TKJ 3', 'Laki-Laki', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  VALENTINO FA  7081.JPG', 'aktif'),
(47, '000032', '32', 'VICO ARI BRILIANSYAH ', 'XI TKJ 3', 'Laki-Laki', 'pare', '081334774943', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  VICO ARI BRI  7082.JPG', 'aktif'),
(48, '000033', '33', 'VIKA ANASTASYA PUTRI', 'XI TKJ 3', 'Perempuan', 'puncu', '088214926545', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  VIKA ANASTAS  7101.JPG', 'aktif'),
(49, '000034', '34', 'WIDHESKA BIAS BUMIARSA', 'XI TKJ 3', 'Laki-Laki', 'pare', '081215099104', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  WIDHESKA BIA  7073.JPG', 'aktif'),
(50, '000035', '35', 'YULI ARUM SARI ', 'XI TKJ 3', 'Perempuan', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  YULI ARUM SA  7088.JPG', 'aktif'),
(51, '000036', '36', 'YUNINDA VERGANATA ', 'XI TKJ 3', 'Perempuan', 'puncu', '085745876114', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  YUNINDA VERG  7087.JPG', 'aktif'),
(52, '000037', '37', 'YURINDA DEVANKA NINGTYAS', 'XI TKJ 3', 'Perempuan', 'pare', '082334824245', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  YURINDA DEVA  7093.JPG', 'aktif'),
(53, '000038', '38', 'YUSUF MAULANA ', 'XI TKJ 3', 'Laki-Laki', 'pare', '085894658780', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  YUSUF MAULAN  7080.JPG', 'aktif'),
(54, '000039', '39', 'ZAHRA NURAINI', 'XI TKJ 3', 'Perempuan', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ3  ZAHRA NURAIN  7108.JPG', 'aktif'),
(56, '000040', '1', 'FLORA AINA RHAFIKA', 'XI TKJ 2', 'Perempuan', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  FLORA AINA R  7038.JPG', 'aktif'),
(57, '000041', '2', 'GALANG ERDIANSYAH', 'XI TKJ 2', 'Laki-Laki', 'iewo', '0867', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  GALANG ERDIA  7044.JPG', 'aktif'),
(58, '000042', '3', 'GALANG SURYA PRAMUDYA', 'XI TKJ 2', 'Laki-Laki', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  GALANG SURYA  7047.JPG', 'aktif'),
(59, '000043', '4', 'HADING EKA RAMDANI', 'XI TKJ 2', 'Laki-Laki', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  HADING EKA R  7065.JPG', 'aktif'),
(60, '000044', '5', 'HAFIYYAN YUSUF', 'XI TKJ 3', 'Laki-Laki', 'pare', '0867', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  HAFIYYAN YUS  7068.JPG', 'aktif'),
(61, '000045', '6', 'ILMA RAHMA AMELIA', 'XI TKJ 2', 'Laki-Laki', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  ILMA RAHMA A  7035.JPG', 'aktif'),
(62, '000046', '7', 'IRFAN MAULANA', 'XI TKJ 2', 'Laki-Laki', 'pare', '0867', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  IRFAN MAULAN  7055.JPG', 'aktif'),
(63, '000047', '8', 'IRFAN WIDIANSHAH', 'XI TKJ 3', 'Laki-Laki', 'puncu', 'pjtwio', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  IRFAN WIDIAN  7072.JPG', 'aktif'),
(64, '000048', '9', 'JOHANNA ZAHRA ELYSIA', 'XI TKJ 2', 'Perempuan', 'pare', '081334774943', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  JOHANNA ZAHR  7033.JPG', 'aktif'),
(65, '000049', '10', 'KAYLA ZUYYINA PUTRI ROZY', 'XI TKJ 2', 'Perempuan', 'iewo', '081334774943', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  KAYLA ZUYYIN  7034.JPG', 'aktif'),
(66, '000050', '11', 'KEVIN ADI PUTRA RAMADAN', 'XI TKJ 2', 'Laki-Laki', 'pare', 'pjtwio', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  KEVIN ADI PU  7045.JPG', 'aktif'),
(67, '000051', '12', 'LAILATUL MIFTAKHUN NI&#039;MAH', 'XI TKJ 3', 'Laki-Laki', 'pare', '081334774943', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  LAILATUL MIF  7031.JPG', 'aktif'),
(68, '000052', '13', 'LAURA ARUM PRAMESWARI', 'XI TKJ 2', 'Perempuan', 'pare', 'pjtwio', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  LAURA ARUM P  7040.JPG', 'aktif'),
(69, '000053', '14', 'LEO ALFIAN ADE PERMANA', 'XI TKJ 2', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  LEO ALFIAN A  7069.JPG', 'aktif'),
(70, '000054', '15', 'M. RIZQI SIFA&#039;UL AZIZ', 'XI TKJ 2', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  M. RIZQI SIF  7051.JPG', 'aktif'),
(71, '000055', '16', 'M. WILDAN AL AHWAN', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  M. WILDAN AL  7057.JPG', 'aktif'),
(72, '000056', '17', 'MAHARDIKA HAFIZ', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MAHARDIKA HA  7053.JPG', 'aktif'),
(73, '000057', '18', 'MARSHA AULIA PUTRI', 'XI TKJ 2', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MARSHA AULIA  7039.JPG', 'aktif'),
(74, '000058', '19', 'MAS RUHANIYAH NUR LATIFAH', 'XI TKJ 2', 'Perempuan', 'iewo', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MAS RUHANIYA  7041.JPG', 'aktif'),
(75, '000059', '20', 'MOCHAMMAD AKBAR PURNOMO', 'XI TKJ 2', 'Laki-Laki', 'pare', 'wy7qiu', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MOCHAMMAD AK  7048.JPG', 'aktif'),
(76, '000060', '21', 'MOCHAMMAD ALDI HERDIANSA', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MOCHAMMAD AL  7046.JPG', 'aktif'),
(77, '000061', '22', 'MOHAMAD KHARIS MAULANA', 'XI TKJ 2', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', 'TKJ2  MOHAMAD KHAR  7064.JPG', 'aktif'),
(78, '000062', '23', 'MOHAMAD TABUTI ISIKOZAKY', 'XI TKJ 2', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MOHAMAD TABU  7070.JPG', 'aktif'),
(79, '000063', '24', 'MOHAMMAD FAJAR FAHRELLA', 'XI TKJ 2', 'Laki-Laki', 'pare', '082334158533', 'Kampus 1', '../../assets/img/profile_siswa/TKJ2  MOHAMMAD FAJ  7067.JPG', 'aktif'),
(80, '000064', '25', 'MUHAMMAD BALAYA BIN SHOFA', 'XI TKJ 2', 'Laki-Laki', 'pare', 'pjtwio', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MUHAMMAD BAL  7050.JPG', 'aktif'),
(81, '000065', '26', 'MUHAMMAD FAREL RADITYA', 'XI TKJ 2', 'Laki-Laki', 'pare', 'pjtwio', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MUHAMMAD FAR  7056.JPG', 'aktif'),
(82, '000066', '27', 'MUHAMMAD FARKHAN AKBAR', 'XI TKJ 2', 'Laki-Laki', 'pare', '5tr', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MUHAMMAD FAR  7062.JPG', 'aktif'),
(83, '000067', '28', 'MUHAMMAD HAFID FAIZ YULIANTO', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MUHAMMAD HAF  7043.JPG', 'aktif'),
(84, '000068', '29', 'MUHAMMAD HAYDAR AR ROSYADI', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/DPIB1  ALBERTS ARDI  7139.JPG', 'aktif'),
(85, '000069', '30', 'MUHAMMAD NUR FAIZIN', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MUHAMMAD NUR  7063.JPG', 'aktif'),
(86, '000070', '31', 'MUHAMMAD SEPTIAN RAMA WIJAYA', 'XII TKJ 2', 'Laki-Laki', 'pare', 'wy7qiu', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MUHAMMAD SEP  7066.JPG', 'aktif'),
(87, '000071', '32', 'MUHAMMAD WILDAN RIZKY', 'XI TKJ 2', 'Laki-Laki', 'pare', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MUHAMMAD WIL  7071.JPG', 'aktif'),
(88, '000072', '33', 'MUHAMMAD WISNU RADITA', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  MUHAMMAD WIS  7049.JPG', 'aktif'),
(89, '000073', '34', 'NABILA SESILIA NOVITA SARI', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  NABILA SESIL  7036.JPG', 'aktif'),
(90, '000074', '35', 'NABILLA NURUL ANJANI', 'XI TKJ 3', 'Perempuan', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  NABILLA NURU  7042.JPG', 'aktif'),
(91, '000075', '36', 'NADHIROH SALMA', 'XI TKJ 2', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  NADHIROH SAL  7037.JPG', 'aktif'),
(92, '000076', '37', 'NAKULA BAYU PUTRA PRATAMA', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  NAKULA BAYU   7061.JPG', 'aktif'),
(93, '000077', '38', 'NICHOLAS DWIYAN', 'XI TKJ 2', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  NICHOLAS DWI  7059.JPG', 'aktif'),
(94, '000078', '39', 'NUR ARISA DEVILAKSANA PUTRI', 'XI TKJ 2', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ2  NUR ARISA DE  7032.JPG', 'aktif'),
(95, '000079', '1', 'ABDEE FITRAH RAMADHAN', 'XI TKJ 1', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ABDEE FITRAH  0009.jpg', 'aktif'),
(96, '000080', '2', 'ABDUL AZIZ ZAKKI PRABAYU', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ABDUL AZIZ Z  6992.JPG', 'aktif'),
(97, '000081', '3', 'ACHMAD RIZKI FEBRIANTO', 'XI TKJ 1', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ACHMAD RIZKI  7014.JPG', 'aktif'),
(98, '000082', '4', 'ADAM FAHRUDIN PUTRA', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ADAM FAHRUDI  7008.JPG', 'aktif'),
(99, '000083', '5', 'ADI TASA', 'XI TKJ 1', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ADI TASA  7010.JPG', 'aktif'),
(100, '000084', '6', 'ADIN BERLIAN DEFURA', 'XI TKJ 1', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ADIN BERLIAN  7027.JPG', 'aktif'),
(101, '000085', '7', 'ADRIAN ARYA RABANI', 'XI TKJ 1', 'Perempuan', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ADRIAN ARYA   7017.JPG', 'aktif'),
(102, '000086', '8', 'ADTIYA RAMA PRASTYA', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ADTIYA RAMA   6994.JPG', 'aktif'),
(103, '000087', '9', 'AHMADDIRA WINARTA', 'XI TKJ 1', 'Laki-Laki', 'AHMADDIRA WINARTA', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  AHMADDIRA WI  6993.JPG', 'aktif'),
(104, '000088', '10', 'ALFIBIAN YUAFI NIA MAHU KAMAL', 'XI TKJ 1', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ALFIBIAN YUA  7030.JPG', 'aktif'),
(105, '000089', '11', 'ALIF KHABIBUR ROHMAN', 'XI TKJ 1', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ALIF KHABIBU  6995.JPG', 'aktif'),
(106, '000090', '12', 'ALIFIAN', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ALIFIAN  7009.JPG', 'aktif'),
(107, '000091', '13', 'ALSA ARDITA RASTY', 'XI TKJ 1', 'Perempuan', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ALSA ARDITA   7020.JPG', 'aktif'),
(108, '000092', '14', 'ANDIKA YESA CHRISTIAN', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ANDIKA YESA   7006.JPG', 'aktif'),
(109, '000093', '15', 'ANDRE DWI WAHYUDI', 'XI TKJ 1', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ANDRE DWI WA  7012.JPG', 'aktif'),
(110, '000094', '16', 'ANNAFI IRFAN ALAUDIN', 'XI TKJ 1', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ANNAFI IRFAN  7001.JPG', 'aktif'),
(111, '000095', '17', 'ARDI PUTRA ADITYA', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ARDI PUTRA A  7007.JPG', 'aktif'),
(112, '000096', '18', 'ARDINA REGITA CAHYANI', 'XI TKJ 1', 'Perempuan', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ARDINA REGIT  7029.JPG', 'aktif'),
(113, '000097', '19', 'ARDINO PRAHASTANTO', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ARDINO PRAHA  7002.JPG', 'aktif'),
(114, '000098', '20', 'ARUM CINTA YULIANA PUTRI', 'XI TKJ 3', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ARUM CINTA Y  7025.JPG', 'aktif'),
(115, '000099', '21', 'ARVEL FIRDAUS SAFRIANO PUTRA', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ARVEL FIRDAU  6996.JPG', 'aktif'),
(116, '000100', '22', 'AULIA GHANI RAHMA', 'XI TKJ 1', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  AULIA GHANI   7026.JPG', 'aktif'),
(117, '000101', '24', 'BETA DWI MEI SAPUTRI', 'XI TKJ 1', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  BETA DWI MEI  7023.JPG', 'aktif'),
(118, '000102', '24', 'CELLO DAVINIARIVI PUTRA', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  CELLO DAVINI  7016.JPG', 'aktif'),
(119, '000103', '25', 'CINTHYA WULANDARI', 'XI TKJ 1', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  CINTHYA WULA  7021.JPG', 'aktif'),
(120, '000104', '26', 'DEDEK HARNEST SURYANA', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  DEDEK HARNES  7011.JPG', 'aktif'),
(121, '000105', '27', 'DOVIZIO ADITYA PUTRA', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  DOVIZIO ADIT  7005.JPG', 'aktif'),
(122, '000106', '28', 'ECHA MARISCA PUTRI', 'XI TKJ 1', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ECHA MARISCA  7028.JPG', 'aktif'),
(123, '000107', '29', 'EKA PERMADANI', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  EKA PERMADAN  7015.JPG', 'aktif'),
(124, '000108', '30', 'ENGGAR SYAH PUTRA', 'XI TKJ 1', 'Laki-Laki', 'puncu', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  ENGGAR SYAH   6998.JPG', 'aktif'),
(125, '000109', '31', 'ESYA AULIA AYU PRATIWI', 'XI TKJ 1', 'Perempuan', 'pare', '082334158533', 'Kampus 1', '../../assets/img/profile_siswa/TKJ1  ESYA AULIA A  7019.JPG', 'aktif'),
(126, '000110', '32', 'FABIAN ABDILA AKBAR', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  FABIAN ABDIL  6999.JPG', 'aktif'),
(127, '000111', '33', 'FARDAN GUNTUR AJI SUSENO', 'XI TKJ 1', 'Laki-Laki', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  FARDAN GUNTU  6997.JPG', 'aktif'),
(128, '000112', '34', 'FARISKA KINAR RAMADHANI', 'XI TKJ 1', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  FARISKA KINA  7022.JPG', 'aktif'),
(129, '000113', '35', 'FARIZ MOCHAMAD RAHMAN', 'XI TKJ 1', 'Laki-Laki', 'JL. SLAMET NO.19 RT.', '087957', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  FARIZ MOCHAM  7018.JPG', 'aktif'),
(130, '000114', '36', 'FARREL CHIEVO ARDIAN', 'XI TKJ 1', 'Laki-Laki', 'Ds.kepung barat,Kec.', 'pjtwio', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  FARREL CHIEV  7013.JPG', 'aktif'),
(131, '000115', '37', 'FAUHAN IRFAN FAHRUDIN', 'XI TKJ 1', 'Laki-Laki', 'laharpang', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  FAUHAN IRFAN  7004.JPG', 'aktif'),
(132, '000116', '38', 'FERDIANSYAH BAGUS PRATAMA', 'XI TKJ 1', 'Laki-Laki', 'puncu', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  FERDIANSYAH   7003.JPG', 'aktif'),
(133, '000117', '39', 'FERDINAN MUHAMMAD RAMADHAN', 'XI TKJ 1', 'Laki-Laki', 'pare', '081217673678', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  FERDINAN MUH  7000.JPG', 'aktif'),
(134, '000118', '40', 'FIKA DWI OKTAFIA', 'XI TKJ 1', 'Perempuan', 'pare', '082334158533', 'Kampus 3', '../../assets/img/profile_siswa/TKJ1  FIKA DWI OKT  7024.JPG', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `username` varchar(225) NOT NULL,
  `password` varchar(225) NOT NULL,
  `status` varchar(20) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `id_pegawai`, `username`, `password`, `status`, `role`) VALUES
(2, 1, 'reyhan', '$2y$10$0sEmBXz9UPGsg3y2I7ZDx.gOeZZB2vXRwSZ6FkYRhFTUgCzOwTOfm', 'aktif', 'admin'),
(3, 4, 'rayu', '$2y$10$IMVzrDfgolps47dNuWAh5uN2r0piOyn/igtxU7RW53o56jK44Rfk2', 'aktif', 'pegawai');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `lokasi_presensi`
--
ALTER TABLE `lokasi_presensi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `presensi`
--
ALTER TABLE `presensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pegawai` (`id_siswa`);

--
-- Indeks untuk tabel `presensi_out`
--
ALTER TABLE `presensi_out`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`,`nis`),
  ADD KEY `nis` (`nis`),
  ADD KEY `nis_2` (`nis`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id pegawai` (`id_pegawai`),
  ADD KEY `id_pegawai` (`id_pegawai`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `lokasi_presensi`
--
ALTER TABLE `lokasi_presensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `presensi`
--
ALTER TABLE `presensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT untuk tabel `presensi_out`
--
ALTER TABLE `presensi_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `presensi`
--
ALTER TABLE `presensi`
  ADD CONSTRAINT `presensi_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id`);

--
-- Ketidakleluasaan untuk tabel `presensi_out`
--
ALTER TABLE `presensi_out`
  ADD CONSTRAINT `presensi_out_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
