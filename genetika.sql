-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 12:16 PM
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
-- Database: `genetika`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `activity`, `icon`, `timestamp`) VALUES
(1, 'Jadwal berhasil digenerate', 'calendar-check', '2025-05-03 19:03:48'),
(2, 'Mata kuliah baru ditambahkan', 'book', '2025-05-03 19:03:48'),
(3, 'Dosen baru ditambahkan', 'user-plus', '2025-05-03 19:03:48'),
(4, 'Ruangan baru ditambahkan', 'door-open', '2025-05-03 19:03:48'),
(5, 'Jurusan baru ditambahkan', 'graduation-cap', '2025-05-03 19:03:48');

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` int(11) NOT NULL,
  `kode_dosen` varchar(10) NOT NULL,
  `nama_dosen` varchar(100) NOT NULL,
  `kode_jurusan` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `kode_dosen`, `nama_dosen`, `kode_jurusan`) VALUES
(15, '1', 'Nurnaila, S.T., M.T', NULL),
(16, '2', 'Ryfial Azhar, S.Kom., M.Kom.', NULL),
(17, '3', 'Irfandi, S.Ag.,M.Ag', NULL),
(18, '4', 'Rinianty, S.Kom., M.TI.', NULL),
(19, '5', 'Anindya Febtiasari, S.Ip., M.AP', NULL),
(20, '6', 'Ayu Hernita, M.Kom.', NULL),
(21, '7', 'Syahrullah, M.Kom.', NULL),
(22, '8', 'Fizar Syafa\'at, S.Kom., M.Kom.', NULL),
(23, '9', 'Dr. Deny Wiria Nugraha, S.T., M.Eng.', NULL),
(24, '10', 'Ir. Sri Khaerawati Nur, S.Kom., M.Kom.', NULL),
(25, '11', 'Dwi Shinta Angreni, S.Si., M.Kom.', NULL),
(26, '12', 'Anisa Yulandari, M.Kom.', NULL),
(27, '13', 'Rahmah Laila, S.Si., M.Kom.', NULL),
(28, '14', 'Muhammad Akbar, S.Kom., M.Kom.', NULL),
(29, '15', 'Ir. Nouval Trezandy Lapatta, S.Kom., M.Kom.', NULL),
(30, '16', 'Dessy Santi, S.Kom., M.T.', NULL),
(31, '17', 'Septiano Anggun Pratama, M.Kom.', NULL),
(32, '18', 'Dr. Amriana, S.T., M.T.', NULL),
(33, '19', 'Wirdayanti, ST.,M.Eng', NULL),
(34, '20', 'Chairunnisa Ar. Lamasitudju, S.Kom., M.Pd.', NULL),
(35, '21', 'Adiguna Kharismawan, S.H., M.H', NULL),
(36, '22', 'Deni Luvi Jayanto, M.Kom.', NULL),
(37, '23', 'Dr. H. Mohammad Yazdi, S.Kom., M.Eng.', NULL),
(38, '24', 'Yusuf Anshori, S.T., M.T.', NULL),
(39, '25', 'Rizka Ardiansyah, S.Kom., M.Kom.', NULL),
(40, '26', 'Afiyah Rifkha Rahmika, M.Kom.', NULL),
(41, '27', 'Andi Hendra, S.Si., M.Kom., Ph.D.', NULL),
(42, '28', 'Sabarudin Saputra, M.Kom.', NULL),
(43, '29', 'Dr. Anita Ahmad Kasim, S.Kom., M.Cs.', NULL),
(44, '30', 'Ridwan Wanasi, S.Pd., M.Pd.', NULL),
(45, '31', 'Nirwana, S.Pd., M.Pd', NULL),
(46, '32', 'Yasir Arafat, S.PdI.,M.S.I', NULL),
(47, '33', 'Nurhani Amin, S.Pd., MT.', NULL),
(48, '34', 'Dr. Ir. Sri Chandrabakty, ST.,M.Eng', NULL),
(49, '35', 'Ir. Irwan Mahmudi, ST., MT', NULL),
(50, '36', 'Sulfitri Husain, S.IP.,M.A', NULL),
(51, '37', 'Agustina SH., MH.', NULL),
(52, '38', 'Dr. Iptdan, M.Pd', NULL),
(53, '39', 'Baso Mukhlis, S.T., M.T.', NULL),
(54, '40', 'Martdiansyah, S.T.,M.T', NULL),
(55, '41', 'Dr. Ir. Alamsyah, S.T.,M.T', NULL),
(56, '42', 'Yusnaini Arifin, ST., MT', NULL),
(57, '43', 'Drs. Agustinus Kali, M.Si.', NULL),
(58, '44', 'Dr.Ir.Yuli Asmi Rahman, S.T.,M.Eng', NULL),
(59, '45', 'Khairunnisa, S.T.,M.T', NULL),
(60, '46', 'Ir. Tan Suryani Sollu, MT.', NULL),
(61, '47', 'Ir. Mery Subito, S.T.,M.T', NULL),
(62, '48', 'Muh. Aristo Indrajaya, S.T., M.T', NULL),
(63, '49', 'Dr. Ahmad Antares Adam, S.T,M.EngSc', NULL),
(64, '50', 'Ratih Mar\'atus Solihah, ST., MT', NULL),
(65, '51', 'Ir. Maryantho Masarrang, S.T.,M.T', NULL),
(66, '52', 'Dr. Ir. Khairil Anwar, ST.,MT', NULL),
(67, '53', 'Ir. Rizana Fauzi, ST., MT', NULL),
(68, '54', 'Erwin Ardias Saputra, ST., MT', NULL),
(69, '55', 'Aidynal Mustari, S.T., M.T.', NULL),
(70, '56', 'Yuri Yudhaswana Joefrie, S.T., M.T., Ph.D.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `kode_mk` varchar(10) DEFAULT NULL,
  `kelas` char(1) DEFAULT 'A',
  `pola` varchar(20) DEFAULT NULL,
  `kode_jurusan` varchar(10) DEFAULT NULL,
  `kode_dosen` varchar(10) DEFAULT NULL,
  `kode_ruangan` varchar(100) DEFAULT NULL,
  `hari` varchar(10) DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id`, `kode_mk`, `kelas`, `pola`, `kode_jurusan`, `kode_dosen`, `kode_ruangan`, `hari`, `jam_mulai`, `jam_selesai`) VALUES
(55271, 'SI1', 'A', '33-22', NULL, '1', 'FF 9', 'Jumat', '12:40:00', '14:20:00'),
(55272, 'SI1', 'B', '33-22', NULL, '1', 'FF 7', 'Rabu', '14:20:00', '16:00:00'),
(55273, 'SI1', 'C', '33-22', NULL, '21', 'FF 11', 'Senin', '12:40:00', '14:20:00'),
(55274, 'SI10', 'A', '33-22', NULL, '19', 'FF 3', 'Senin', '10:00:00', '12:00:00'),
(55275, 'SI10', 'B', '33-22', NULL, '4', 'FF 3', 'Jumat', '07:30:00', '10:00:00'),
(55276, 'SI10', 'C', '33-22', NULL, '19', 'FF 1', 'Senin', '10:00:00', '12:00:00'),
(55277, 'SI11', 'A', '33-22', NULL, '12', 'FF 3', 'Selasa', '07:30:00', '10:00:00'),
(55278, 'SI11', 'B', '33-22', NULL, '12', 'FF 9', 'Rabu', '07:30:00', '10:00:00'),
(55279, 'SI11', 'C', '33-22', NULL, '19', 'FF 11', 'Selasa', '07:30:00', '10:00:00'),
(55280, 'SI12', 'A', '33-22', NULL, '28', 'FF 4', 'Senin', '10:00:00', '12:00:00'),
(55281, 'SI12', 'B', '33-22', NULL, '19', 'FF 6', 'Rabu', '10:00:00', '12:00:00'),
(55282, 'SI12', 'C', '33-22', NULL, '19', 'FF 5', 'Rabu', '10:00:00', '12:00:00'),
(55283, 'SI13', 'A', '33-22', NULL, '7', 'FF 8', 'Selasa', '07:30:00', '10:00:00'),
(55284, 'SI13', 'B', '33-22', NULL, '7', 'FF 6', 'Selasa', '10:00:00', '12:00:00'),
(55285, 'SI13', 'C', '33-22', NULL, '7', 'FF 9', 'Senin', '07:30:00', '10:00:00'),
(55286, 'SI14', 'A', '33-22', NULL, '23', 'FF 11', 'Jumat', '10:00:00', '12:00:00'),
(55287, 'SI14', 'B', '33-22', NULL, '23', 'FF 11', 'Senin', '10:00:00', '12:00:00'),
(55288, 'SI14', 'C', '33-22', NULL, '11', 'FF 12', 'Kamis', '07:30:00', '10:00:00'),
(55289, 'SI15', 'A', '33-22', NULL, '11', 'FF 2', 'Jumat', '10:00:00', '12:00:00'),
(55290, 'SI15', 'B', '33-22', NULL, '11', 'FF 4', 'Selasa', '07:30:00', '10:00:00'),
(55291, 'SI15', 'C', '33-22', NULL, '13', 'FF 7', 'Selasa', '07:30:00', '10:00:00'),
(55292, 'SI16', 'A', '33-22', NULL, '14', 'FF 3', 'Jumat', '10:00:00', '12:00:00'),
(55293, 'SI16', 'B', '33-22', NULL, '2', 'FF 8', 'Jumat', '10:00:00', '12:00:00'),
(55294, 'SI16', 'C', '33-22', NULL, '13', 'FF 12', 'Senin', '07:30:00', '10:00:00'),
(55295, 'SI17', 'A', '33-22', NULL, '4', 'FF 3', 'Selasa', '14:20:00', '16:00:00'),
(55296, 'SI18', 'A', '33-22', NULL, '22', 'FF 7', 'Jumat', '10:00:00', '12:00:00'),
(55297, 'SI19', 'A', '33-22', NULL, '22', 'FF 9', 'Rabu', '07:30:00', '10:00:00'),
(55298, 'SI2', 'A', '33-22', NULL, '3', 'FF 7', 'Senin', '14:20:00', '16:00:00'),
(55299, 'SI2', 'B', '33-22', NULL, '2', 'FF 1', 'Selasa', '12:40:00', '14:20:00'),
(55300, 'SI2', 'C', '33-22', NULL, '2', 'FF 6', 'Kamis', '14:20:00', '16:00:00'),
(55301, 'SI20', 'A', '33-22', NULL, '15', 'FF 5', 'Rabu', '07:30:00', '10:00:00'),
(55302, 'SI21', 'A', '33-22', NULL, '14', 'FF 4', 'Rabu', '12:40:00', '14:20:00'),
(55303, 'SI22', 'A', '33-22', NULL, '19', 'FF 6', 'Kamis', '07:30:00', '10:00:00'),
(55304, 'SI23', 'A', '33-22', NULL, '24', 'FF 4', 'Kamis', '10:00:00', '12:00:00'),
(55305, 'SI24', 'A', '33-22', NULL, '56', 'FF 12', 'Kamis', '10:00:00', '12:00:00'),
(55306, 'SI25', 'A', '33-22', NULL, '8', 'FF 5', 'Jumat', '07:30:00', '10:00:00'),
(55307, 'SI26', 'A', '33-22', NULL, '4', 'FF 2', 'Senin', '07:30:00', '10:00:00'),
(55308, 'SI27', 'A', '33-22', NULL, '6', 'FF 4', 'Selasa', '10:00:00', '12:00:00'),
(55309, 'SI28', 'A', '33-22', NULL, '10', 'FF 12', 'Senin', '10:00:00', '12:00:00'),
(55310, 'SI29', 'A', '33-22', NULL, '10', 'FF 5', 'Senin', '07:30:00', '10:00:00'),
(55311, 'SI3', 'A', '33-22', NULL, '4', 'FF 8', 'Rabu', '12:40:00', '14:20:00'),
(55312, 'SI3', 'B', '33-22', NULL, '6', 'FF 2', 'Senin', '14:20:00', '16:00:00'),
(55313, 'SI3', 'C', '33-22', NULL, '6', 'FF 2', 'Kamis', '14:20:00', '16:00:00'),
(55314, 'SI4', 'A', '33-22', NULL, '5', 'FF 4', 'Kamis', '12:40:00', '14:20:00'),
(55315, 'SI4', 'B', '33-22', NULL, '21', 'FF 5', 'Rabu', '12:40:00', '14:20:00'),
(55316, 'SI4', 'C', '33-22', NULL, '21', 'FF 1', 'Senin', '14:20:00', '16:00:00'),
(55317, 'SI5', 'A', '33-22', NULL, '20', 'FF 8', 'Senin', '10:00:00', '12:00:00'),
(55318, 'SI5', 'B', '33-22', NULL, '20', 'FF 11', 'Senin', '07:30:00', '10:00:00'),
(55319, 'SI5', 'C', '33-22', NULL, '20', 'FF 6', 'Jumat', '10:00:00', '12:00:00'),
(55320, 'SI6', 'A', '33-22', NULL, '22', 'FF 7', 'Rabu', '10:00:00', '12:00:00'),
(55321, 'SI6', 'B', '33-22', NULL, '25', 'FF 10', 'Jumat', '10:00:00', '12:00:00'),
(55322, 'SI6', 'C', '33-22', NULL, '7', 'FF 5', 'Rabu', '10:00:00', '12:00:00'),
(55323, 'SI7', 'A', '33-22', NULL, '24', 'FF 12', 'Selasa', '07:30:00', '10:00:00'),
(55324, 'SI7', 'B', '33-22', NULL, '24', 'FF 5', 'Kamis', '07:30:00', '10:00:00'),
(55325, 'SI7', 'C', '33-22', NULL, '8', 'FF 8', 'Kamis', '10:00:00', '12:00:00'),
(55326, 'SI8', 'A', '33-22', NULL, '10', 'FF 7', 'Selasa', '07:30:00', '10:00:00'),
(55327, 'SI8', 'B', '33-22', NULL, '9', 'FF 1', 'Selasa', '07:30:00', '10:00:00'),
(55328, 'SI8', 'C', '33-22', NULL, '9', 'FF 12', 'Selasa', '10:00:00', '12:00:00'),
(55329, 'SI9', 'A', '33-22', NULL, '7', 'FF 8', 'Jumat', '07:30:00', '10:00:00'),
(55330, 'SI9', 'B', '33-22', NULL, '20', 'FF 3', 'Rabu', '10:00:00', '12:00:00'),
(55331, 'SI9', 'C', '33-22', NULL, '7', 'FF 9', 'Jumat', '07:30:00', '10:00:00'),
(55332, 'TE1', 'A', '33-22', NULL, '31', 'SG 7', 'Kamis', '12:40:00', '14:20:00'),
(55333, 'TE1', 'B', '33-22', NULL, '38', 'SG 10', 'Selasa', '14:20:00', '16:00:00'),
(55334, 'TE10', 'A', '33-22', NULL, '47', 'SG 11', 'Selasa', '14:20:00', '16:00:00'),
(55335, 'TE10', 'B', '33-22', NULL, '48', 'SG 8', 'Kamis', '12:40:00', '14:20:00'),
(55336, 'TE11', 'A', '33-22', NULL, '46', 'LAB TERPADU 2', 'Senin', '10:00:00', '12:00:00'),
(55337, 'TE11', 'B', '33-22', NULL, '53', 'SG 3', 'Kamis', '07:30:00', '10:00:00'),
(55338, 'TE12', 'A', '33-22', NULL, '45', 'SG 3', 'Selasa', '14:20:00', '16:00:00'),
(55339, 'TE12', 'B', '33-22', NULL, '46', 'SG 6', 'Selasa', '12:40:00', '14:20:00'),
(55340, 'TE13', 'A', '33-22', NULL, '49', 'SG 5', 'Jumat', '10:00:00', '12:00:00'),
(55341, 'TE13', 'B', '33-22', NULL, '49', 'SG 8', 'Kamis', '07:30:00', '10:00:00'),
(55342, 'TE14', 'A', '33-22', NULL, '48', 'RBT', 'Selasa', '14:20:00', '16:00:00'),
(55343, 'TE14', 'B', '33-22', NULL, '48', 'SG 8', 'Rabu', '12:40:00', '14:20:00'),
(55344, 'TE15', 'A', '33-22', NULL, '54', 'SG 4', 'Jumat', '10:00:00', '12:00:00'),
(55345, 'TE15', 'B', '33-22', NULL, '50', 'FTL 36', 'Kamis', '10:00:00', '12:00:00'),
(55346, 'TE16', 'A', '33-22', NULL, '33', 'SG 8', 'Jumat', '14:20:00', '16:00:00'),
(55347, 'TE16', 'B', '33-22', NULL, '33', 'LAB KOMPUTER', 'Selasa', '12:40:00', '14:20:00'),
(55348, 'TE17', 'A', '33-22', NULL, '46', 'LAB KOMPUTER', 'Senin', '12:40:00', '14:20:00'),
(55349, 'TE18', 'A', '33-22', NULL, '39', 'SG 5', 'Selasa', '12:40:00', '14:20:00'),
(55350, 'TE18', 'B', '33-22', NULL, '49', 'SG 11', 'Senin', '12:40:00', '14:20:00'),
(55351, 'TE19', 'A', '33-22', NULL, '35', 'SG 3', 'Selasa', '10:00:00', '12:00:00'),
(55352, 'TE19', 'B', '33-22', NULL, '43', 'SG 8', 'Jumat', '10:00:00', '12:00:00'),
(55353, 'TE2', 'A', '33-22', NULL, '32', 'LAB TERPADU 2', 'Selasa', '12:40:00', '14:20:00'),
(55354, 'TE2', 'B', '33-22', NULL, '55', 'SG 4', 'Kamis', '12:40:00', '14:20:00'),
(55355, 'TE20', 'A', '33-22', NULL, '40', 'SG 6', 'Kamis', '12:40:00', '14:20:00'),
(55356, 'TE20', 'B', '33-22', NULL, '40', 'SG 9', 'Senin', '12:40:00', '14:20:00'),
(55357, 'TE21', 'A', '33-22', NULL, '33', 'SG 1', 'Rabu', '14:20:00', '16:00:00'),
(55358, 'TE21', 'B', '33-22', NULL, '33', 'SG 2', 'Senin', '12:40:00', '14:20:00'),
(55359, 'TE22', 'A', '33-22', NULL, '35', 'LAB MESIN-MESIN', 'Jumat', '07:30:00', '10:00:00'),
(55360, 'TE22', 'B', '33-22', NULL, '35', 'SG 5', 'Jumat', '07:30:00', '10:00:00'),
(55361, 'TE23', 'A', '33-22', NULL, '42', 'LAB MESIN-MESIN', 'Senin', '12:40:00', '14:20:00'),
(55362, 'TE24', 'A', '33-22', NULL, '45', 'SG 4', 'Senin', '12:40:00', '14:20:00'),
(55363, 'TE25', 'A', '33-22', NULL, '53', 'SG 9', 'Kamis', '07:30:00', '10:00:00'),
(55364, 'TE26', 'A', '33-22', NULL, '48', 'LAB TERPADU 2', 'Kamis', '12:40:00', '14:20:00'),
(55365, 'TE27', 'A', '33-22', NULL, '47', 'RBT', 'Selasa', '10:00:00', '12:00:00'),
(55366, 'TE28', 'A', '33-22', NULL, '54', 'SG 11', 'Rabu', '10:00:00', '12:00:00'),
(55367, 'TE29', 'A', '33-22', NULL, '49', 'LAB MESIN-MESIN', 'Jumat', '14:20:00', '16:00:00'),
(55368, 'TE3', 'A', '33-22', NULL, '45', 'LAB LISTRIK DASAR / LAB ELEKTRONIKA', 'Jumat', '10:00:00', '12:00:00'),
(55369, 'TE3', 'B', '33-22', NULL, '45', 'LAB INSTALASI', 'Jumat', '10:00:00', '12:00:00'),
(55370, 'TE30', 'A', '33-22', NULL, '51', 'SG 5', 'Rabu', '12:40:00', '14:20:00'),
(55371, 'TE31', 'A', '33-22', NULL, '35', 'SG 9', 'Senin', '14:20:00', '16:00:00'),
(55372, 'TE32', 'A', '33-22', NULL, '44', 'FTL 36', 'Rabu', '12:40:00', '14:20:00'),
(55373, 'TE33', 'A', '33-22', NULL, '53', 'FTL 36', 'Selasa', '12:40:00', '14:20:00'),
(55374, 'TE34', 'A', '33-22', NULL, '51', 'SG 8', 'Jumat', '14:20:00', '16:00:00'),
(55375, 'TE35', 'A', '33-22', NULL, '55', 'SG 4', 'Rabu', '12:40:00', '14:20:00'),
(55376, 'TE36', 'A', '33-22', NULL, '56', 'LAB INSTALASI', 'Jumat', '14:20:00', '16:00:00'),
(55377, 'TE4', 'A', '33-22', NULL, '48', 'LAB LISTRIK DASAR / LAB ELEKTRONIKA', 'Kamis', '12:40:00', '14:20:00'),
(55378, 'TE4', 'B', '33-22', NULL, '35', 'SG 6', 'Selasa', '14:20:00', '16:00:00'),
(55379, 'TE5', 'A', '33-22', NULL, '45', 'LAB LISTRIK DASAR / LAB ELEKTRONIKA', 'Senin', '14:20:00', '16:00:00'),
(55380, 'TE5', 'B', '33-22', NULL, '45', 'SG 10', 'Rabu', '14:20:00', '16:00:00'),
(55381, 'TE6', 'A', '33-22', NULL, '45', 'SG 6', 'Rabu', '14:20:00', '16:00:00'),
(55382, 'TE6', 'B', '33-22', NULL, '47', 'SG 9', 'Selasa', '12:40:00', '14:20:00'),
(55383, 'TE7', 'A', '33-22', NULL, '36', 'SG 10', 'Senin', '14:20:00', '16:00:00'),
(55384, 'TE7', 'B', '33-22', NULL, '32', 'LAB LISTRIK DASAR / LAB ELEKTRONIKA', 'Rabu', '12:40:00', '14:20:00'),
(55385, 'TE8', 'A', '33-22', NULL, '47', 'LAB TERPADU 2', 'Kamis', '14:20:00', '16:00:00'),
(55386, 'TE8', 'B', '33-22', NULL, '47', 'LAB INSTALASI', 'Selasa', '12:40:00', '14:20:00'),
(55387, 'TE9', 'A', '33-22', NULL, '40', 'FTL 37', 'Selasa', '07:30:00', '10:00:00'),
(55388, 'TE9', 'B', '33-22', NULL, '40', 'LAB INSTALASI', 'Senin', '07:30:00', '10:00:00'),
(55389, 'TI1', 'A', '33-22', NULL, '13', 'F.F 12', 'Senin', '10:00:00', '12:00:00'),
(55390, 'TI1', 'B', '33-22', NULL, '26', 'TI 3', 'Rabu', '10:00:00', '12:00:00'),
(55391, 'TI1', 'C', '33-22', NULL, '26', 'TI 1', 'Selasa', '10:00:00', '12:00:00'),
(55392, 'TI10', 'A', '33-22', NULL, '26', 'F.F 9', 'Senin', '10:00:00', '12:00:00'),
(55393, 'TI10', 'B', '33-22', NULL, '26', 'TI 2', 'Selasa', '10:00:00', '12:00:00'),
(55394, 'TI10', 'C', '33-22', NULL, '25', 'F.F 4', 'Kamis', '07:30:00', '10:00:00'),
(55395, 'TI11', 'A', '33-22', NULL, '15', 'F.F 8', 'Selasa', '07:30:00', '10:00:00'),
(55396, 'TI11', 'B', '33-22', NULL, '16', 'F.F 4', 'Selasa', '07:30:00', '10:00:00'),
(55397, 'TI11', 'C', '33-22', NULL, '16', 'F.F 11', 'Rabu', '07:30:00', '10:00:00'),
(55398, 'TI12', 'A', '33-22', NULL, '18', 'F.F 8', 'Kamis', '10:00:00', '12:00:00'),
(55399, 'TI12', 'B', '33-22', NULL, '29', 'F.F 2', 'Rabu', '07:30:00', '10:00:00'),
(55400, 'TI12', 'C', '33-22', NULL, '29', 'F.F 10', 'Rabu', '10:00:00', '12:00:00'),
(55401, 'TI13', 'A', '33-22', NULL, '18', 'F.F 8', 'Senin', '10:00:00', '12:00:00'),
(55402, 'TI13', 'B', '33-22', NULL, '18', 'F.F 2', 'Senin', '07:30:00', '10:00:00'),
(55403, 'TI13', 'C', '33-22', NULL, '18', 'F.F 10', 'Rabu', '10:00:00', '12:00:00'),
(55404, 'TI14', 'A', '33-22', NULL, '16', 'F.F 11', 'Kamis', '10:00:00', '12:00:00'),
(55405, 'TI14', 'B', '33-22', NULL, '17', 'F.F 7', 'Selasa', '07:30:00', '10:00:00'),
(55406, 'TI14', 'C', '33-22', NULL, '17', 'F.F 10', 'Rabu', '10:00:00', '12:00:00'),
(55407, 'TI15', 'A', '33-22', NULL, '25', 'TI 3', 'Kamis', '07:30:00', '10:00:00'),
(55408, 'TI15', 'B', '33-22', NULL, '26', 'F.F 8', 'Kamis', '10:00:00', '12:00:00'),
(55409, 'TI15', 'C', '33-22', NULL, '26', 'F.F 12', 'Senin', '10:00:00', '12:00:00'),
(55410, 'TI16', 'A', '33-22', NULL, '8', 'TI 2', 'Kamis', '12:40:00', '14:20:00'),
(55411, 'TI17', 'A', '33-22', NULL, '24', 'F.F 6', 'Selasa', '12:40:00', '14:20:00'),
(55412, 'TI18', 'A', '33-22', NULL, '17', 'F.F 2', 'Senin', '10:00:00', '12:00:00'),
(55413, 'TI19', 'A', '33-22', NULL, '9', 'F.F 11', 'Jumat', '10:00:00', '12:00:00'),
(55414, 'TI2', 'A', '33-22', NULL, '56', 'TI 2', 'Jumat', '14:20:00', '16:00:00'),
(55415, 'TI2', 'B', '33-22', NULL, '7', 'F.F 5', 'Rabu', '12:40:00', '14:20:00'),
(55416, 'TI2', 'C', '33-22', NULL, '18', 'F.F 8', 'Jumat', '12:40:00', '14:20:00'),
(55417, 'TI20', 'A', '33-22', NULL, '56', 'F.F 11', 'Senin', '07:30:00', '10:00:00'),
(55418, 'TI21', 'A', '33-22', NULL, '15', 'F.F 6', 'Kamis', '07:30:00', '10:00:00'),
(55419, 'TI22', 'A', '33-22', NULL, '14', 'TI 1', 'Rabu', '10:00:00', '12:00:00'),
(55420, 'TI23', 'A', '33-22', NULL, '22', 'F.F 11', 'Kamis', '07:30:00', '10:00:00'),
(55421, 'TI24', 'A', '33-22', NULL, '20', 'TI 3', 'Jumat', '07:30:00', '10:00:00'),
(55422, 'TI25', 'A', '33-22', NULL, '14', 'F.F 8', 'Kamis', '10:00:00', '12:00:00'),
(55423, 'TI26', 'A', '33-22', NULL, '56', 'F.F 9', 'Senin', '07:30:00', '10:00:00'),
(55424, 'TI27', 'A', '33-22', NULL, '56', 'F.F 8', 'Senin', '07:30:00', '10:00:00'),
(55425, 'TI28', 'A', '33-22', NULL, '14', 'F.F 1', 'Rabu', '07:30:00', '10:00:00'),
(55426, 'TI3', 'A', '33-22', NULL, '19', 'F.F 5', 'Rabu', '12:40:00', '14:20:00'),
(55427, 'TI3', 'B', '33-22', NULL, '20', 'TI 1', 'Jumat', '12:40:00', '14:20:00'),
(55428, 'TI3', 'C', '33-22', NULL, '20', 'F.F 4', 'Kamis', '14:20:00', '16:00:00'),
(55429, 'TI4', 'A', '33-22', NULL, '9', 'F.F 2', 'Jumat', '14:20:00', '16:00:00'),
(55430, 'TI4', 'B', '33-22', NULL, '9', 'F.F 12', 'Senin', '12:40:00', '14:20:00'),
(55431, 'TI4', 'C', '33-22', NULL, '24', 'F.F 7', 'Selasa', '14:20:00', '16:00:00'),
(55432, 'TI5', 'A', '33-22', NULL, '4', 'F.F 1', 'Rabu', '14:20:00', '16:00:00'),
(55433, 'TI5', 'B', '33-22', NULL, '20', 'F.F 7', 'Rabu', '14:20:00', '16:00:00'),
(55434, 'TI5', 'C', '33-22', NULL, '20', 'F.F 5', 'Selasa', '14:20:00', '16:00:00'),
(55435, 'TI6', 'A', '33-22', NULL, '2', 'F.F 5', 'Jumat', '12:40:00', '14:20:00'),
(55436, 'TI6', 'B', '33-22', NULL, '21', 'F.F 6', 'Senin', '14:20:00', '16:00:00'),
(55437, 'TI6', 'C', '33-22', NULL, '21', 'TI 1', 'Kamis', '14:20:00', '16:00:00'),
(55438, 'TI7', 'A', '33-22', NULL, '10', 'F.F 5', 'Senin', '07:30:00', '10:00:00'),
(55439, 'TI7', 'B', '33-22', NULL, '10', 'F.F 1', 'Kamis', '07:30:00', '10:00:00'),
(55440, 'TI7', 'C', '33-22', NULL, '6', 'F.F 8', 'Jumat', '07:30:00', '10:00:00'),
(55441, 'TI8', 'A', '33-22', NULL, '12', 'F.F 11', 'Rabu', '10:00:00', '12:00:00'),
(55442, 'TI8', 'B', '33-22', NULL, '28', 'F.F 8', 'Selasa', '10:00:00', '12:00:00'),
(55443, 'TI8', 'C', '33-22', NULL, '28', 'TI 2', 'Rabu', '10:00:00', '12:00:00'),
(55444, 'TI9', 'A', '33-22', NULL, '23', 'F.F 4', 'Senin', '07:30:00', '10:00:00'),
(55445, 'TI9', 'B', '33-22', NULL, '12', 'F.F 1', 'Senin', '07:30:00', '10:00:00'),
(55446, 'TI9', 'C', '33-22', NULL, '28', 'TI 3', 'Selasa', '10:00:00', '12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `jurusan`
--

CREATE TABLE `jurusan` (
  `kode_jurusan` varchar(10) NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`kode_jurusan`, `nama_jurusan`) VALUES
('1', 'Sistem Informasi'),
('2', 'Teknik Informatika'),
('3', 'Teknik Elektro');

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id` int(11) NOT NULL,
  `kode_mk` varchar(10) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int(11) NOT NULL,
  `kode_jurusan` varchar(10) DEFAULT NULL,
  `kode_dosen` varchar(10) DEFAULT NULL,
  `kelas` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id`, `kode_mk`, `nama_mk`, `sks`, `kode_jurusan`, `kode_dosen`, `kelas`) VALUES
(217, 'SI1', 'Pendidikan Kewarganegaraan (SI)', 2, '1', NULL, 'A,B,C'),
(218, 'SI2', 'Pendidikan Karakter dan Anti Korupsi (SI)', 2, '1', NULL, 'A,B,C'),
(219, 'SI3', 'Kewirausahaan (SI)', 2, '1', NULL, 'A,B,C'),
(220, 'SI4', 'Ilmu Sosial Budaya Dasar (SI)', 2, '1', NULL, 'A,B,C'),
(221, 'SI5', 'Pengantar Bisnis dan Manajemen', 3, '1', NULL, 'A,B,C'),
(222, 'TI1', 'Kalkulus', 3, '2', NULL, 'A,B,C'),
(223, 'TI2', 'Struktur Data (TI)', 4, '2', NULL, 'A,B,C'),
(224, 'TI3', 'Basis Data', 4, '2', NULL, 'A,B,C'),
(225, 'TE1', 'Bahasa Indonesia', 2, '3', NULL, 'A,B'),
(226, 'TE2', 'Pendidikan Kewarganegaraan (Elektro)', 2, '3', NULL, 'A,B'),
(227, 'TE3', 'Matematika Dasar II', 3, '3', NULL, 'A,B'),
(228, 'SI6', 'Struktur Data (SI)', 3, '1', NULL, 'A,B,C'),
(229, 'SI7', 'Arsitektur SI/TI Perusahaan', 3, '1', NULL, 'A,B,C'),
(230, 'SI8', 'Dasar-Dasar Pengembangan Perangkat Lunak', 3, '1', NULL, 'A,B,C'),
(231, 'SI9', 'Desain & Manajemen Proses Bisnis', 3, '1', NULL, 'A,B,C'),
(232, 'SI10', 'Sistem Informasi Manajemen', 3, '1', NULL, 'A,B,C'),
(233, 'TI4', 'Kajian Lingkungan Hidup', 2, '2', NULL, 'A,B,C'),
(234, 'TI5', 'Kewirausahaan (TI)', 2, '2', NULL, 'A,B,C'),
(235, 'TI6', 'Pendidikan Karakter dan Anti Korupsi (TI)', 2, '2', NULL, 'A,B,C'),
(236, 'TI7', 'Matematika Dikrit', 3, '2', NULL, 'A,B,C'),
(237, 'TI8', 'Pemrograman Web 2', 3, '2', NULL, 'A,B,C'),
(238, 'TI9', 'Perancangan dan Analisis Algoritma 2', 3, '2', NULL, 'A,B,C'),
(239, 'TI10', 'Cloud Computing', 3, '2', NULL, 'A,B,C'),
(240, 'TE4', 'Gambar Teknik', 2, '3', NULL, 'A,B'),
(241, 'TE5', 'Dasar Telekomunikasi', 2, '3', NULL, 'A,B'),
(242, 'TE6', 'Dasar Elektronika', 2, '3', NULL, 'A,B'),
(243, 'TE7', 'Ilmu Sosial Budaya Dasar (Elektro)', 2, '3', NULL, 'A,B'),
(244, 'TE8', 'Karakter dan anti Korupsi', 2, '3', NULL, 'A,B'),
(245, 'TE9', 'Rangkaian Elektrik I', 3, '3', NULL, 'A,B'),
(246, 'TE10', 'Sinyal dan Sistem Linear', 2, '3', NULL, 'A,B'),
(247, 'SI11', 'Sistem Pendukung Keputusan', 3, '1', NULL, 'A,B,C'),
(248, 'SI12', 'Data Warehouse (SI)', 3, '1', NULL, 'A,B,C'),
(249, 'SI13', 'Pemrograman Web', 3, '1', NULL, 'A,B,C'),
(250, 'SI14', 'Pemrograman Berorientasi Objek', 3, '1', NULL, 'A,B,C'),
(251, 'SI15', 'Statistika dan Probabilitas (SI)', 3, '1', NULL, 'A,B,C'),
(252, 'SI16', 'Riset Operasi', 3, '1', NULL, 'A,B,C'),
(253, 'SI17', 'Tata Kelola Teknologi Informasi', 2, '1', NULL, 'A'),
(254, 'SI18', 'E-Government/SPBE', 3, '1', NULL, 'A'),
(255, 'SI19', 'Etika Profesi', 3, '1', NULL, 'A'),
(256, 'SI20', 'Kuliah Kerja Lapangan Profesi', 3, '1', NULL, 'A'),
(257, 'SI21', 'Proyek Pengembangan Sistem Informasi', 4, '1', NULL, 'A'),
(258, 'SI22', 'Pemodelan dan Simulasi Bencana', 3, '1', NULL, 'A'),
(259, 'SI23', 'Internet of Things Kebencanaan', 3, '1', NULL, 'A'),
(260, 'SI24', 'Data Science Kebencanaan', 3, '1', NULL, 'A'),
(261, 'SI25', 'Tata Kelola Kebencanaan', 3, '1', NULL, 'A'),
(262, 'SI26', 'Manajemen Layanan Teknologi Informasi', 3, '1', NULL, 'A'),
(263, 'SI27', 'Business Intelligence', 3, '1', NULL, 'A'),
(264, 'SI28', 'Digital Marketing', 3, '1', NULL, 'A'),
(265, 'SI29', 'E-Business', 3, '1', NULL, 'A'),
(266, 'TI11', 'Sistem Informasi Geografis 1', 3, '2', NULL, 'A,B,C'),
(267, 'TI12', 'Pengolahan Citra Digital', 3, '2', NULL, 'A,B,C'),
(268, 'TI13', 'Machine Learning', 3, '2', NULL, 'A,B,C'),
(269, 'TI14', 'Data Warehouse (TI)', 3, '2', NULL, 'A,B,C'),
(270, 'TI15', 'Aplication Programming Interface (API)', 3, '2', NULL, 'A,B,C'),
(271, 'TI16', 'Statistika dan Probabilitas (TI)', 2, '2', NULL, 'A'),
(272, 'TI17', 'Kriptografi', 2, '2', NULL, 'A'),
(273, 'TI18', 'Pemrograman Paralel', 3, '2', NULL, 'A'),
(274, 'TI19', 'Metode Penelitian', 3, '2', NULL, 'A'),
(275, 'TI20', 'Topik Khusus Riset Teknologi Informasi', 3, '2', NULL, 'A'),
(276, 'TI21', 'Augmented Reality', 3, '2', NULL, 'A'),
(277, 'TI22', 'Pengembangan Game', 3, '2', NULL, 'A'),
(278, 'TI23', 'Audit Sistem', 3, '2', NULL, 'A'),
(279, 'TI24', 'Digital Forensic', 3, '2', NULL, 'A'),
(280, 'TI25', 'Robotika', 3, '2', NULL, 'A'),
(281, 'TI26', 'Data Mining', 3, '2', NULL, 'A'),
(282, 'TI27', 'Deep Learning', 3, '2', NULL, 'A'),
(283, 'TI28', 'Embedded System', 3, '2', NULL, 'A'),
(284, 'TE11', 'Mikroprosesor dan Mikrokontroller', 3, '3', NULL, 'A,B'),
(285, 'TE12', 'Praktikum TE 2', 2, '3', NULL, 'A,B'),
(286, 'TE13', 'Pemodelan dan Simulasi Bencana', 3, '3', NULL, 'A,B'),
(287, 'TE14', 'Komunikasi Data', 2, '3', NULL, 'A,B'),
(288, 'TE15', 'Kecerdasan Buatan Dan JST', 3, '3', NULL, 'A,B'),
(289, 'TE16', 'Metode Numerik', 2, '3', NULL, 'A,B'),
(290, 'TE17', 'Aplikasi Op-Amp', 2, '3', NULL, 'A'),
(291, 'TE18', 'Mesin-mesin Elektrik 1', 2, '3', NULL, 'A,B'),
(292, 'TE19', 'Instalasi Sistem Kelistrikan', 3, '3', NULL, 'A,B'),
(293, 'TE20', 'Pembangkit Teknik Tenaga Elektrik', 2, '3', NULL, 'A,B'),
(294, 'TE21', 'Sistem Transmisi Tenaga Elektrik', 2, '3', NULL, 'A,B'),
(295, 'TE22', 'Sistem Proteksi', 3, '3', NULL, 'A,B'),
(296, 'TE23', 'Praktikum TEE 2', 2, '3', NULL, 'A'),
(297, 'TE24', 'Sistem Komunikasi Bergerak', 2, '3', NULL, 'A'),
(298, 'TE25', 'Rangkaian Elektronika Lanjut', 3, '3', NULL, 'A'),
(299, 'TE26', 'Praktikum TEN 2', 2, '3', NULL, 'A'),
(300, 'TE27', 'Sistem Pemrosesan Sinyal', 3, '3', NULL, 'A'),
(301, 'TE28', 'Perancangan Sistem Elektronika', 3, '3', NULL, 'A'),
(302, 'TE29', 'Kontrol Power Elektronik', 2, '3', NULL, 'A'),
(303, 'TE30', 'Manajemen Proyek dan K3', 2, '3', NULL, 'A'),
(304, 'TE31', 'Smart Grid', 2, '3', NULL, 'A'),
(305, 'TE32', 'Manajemen Sistem energi', 2, '3', NULL, 'A'),
(306, 'TE33', 'Embeded Sistem', 2, '3', NULL, 'A'),
(307, 'TE34', 'Manajemen Industri', 2, '3', NULL, 'A'),
(308, 'TE35', 'Public Speaking', 2, '3', NULL, 'A'),
(309, 'TE36', 'Bahasa Asing di Industri', 2, '3', NULL, 'A');

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah_dosen`
--

CREATE TABLE `mata_kuliah_dosen` (
  `id` int(11) NOT NULL,
  `kode_mk` varchar(10) DEFAULT NULL,
  `kode_dosen` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mata_kuliah_dosen`
--

INSERT INTO `mata_kuliah_dosen` (`id`, `kode_mk`, `kode_dosen`) VALUES
(551, 'SI3', '4'),
(552, 'SI3', '6'),
(553, 'SI1', '1'),
(554, 'SI1', '21'),
(555, 'SI2', '2'),
(556, 'SI2', '3'),
(557, 'SI4', '21'),
(558, 'SI4', '5'),
(559, 'SI5', '20'),
(560, 'SI5', '4'),
(561, 'SI5', '6'),
(562, 'TI1', '10'),
(563, 'TI1', '13'),
(564, 'TI1', '26'),
(565, 'TI2', '18'),
(566, 'TI2', '28'),
(567, 'TI2', '56'),
(568, 'TI2', '7'),
(569, 'TI3', '19'),
(570, 'TI3', '20'),
(571, 'TI3', '28'),
(572, 'TI3', '29'),
(573, 'TE1', '30'),
(574, 'TE1', '31'),
(575, 'TE1', '38'),
(576, 'TE2', '32'),
(577, 'TE2', '55'),
(578, 'TE3', '33'),
(579, 'TE3', '45'),
(580, 'SI6', '22'),
(581, 'SI6', '25'),
(582, 'SI6', '7'),
(583, 'SI7', '24'),
(584, 'SI7', '25'),
(585, 'SI7', '8'),
(586, 'SI8', '10'),
(587, 'SI8', '4'),
(588, 'SI8', '9'),
(589, 'SI9', '20'),
(590, 'SI9', '4'),
(591, 'SI9', '7'),
(592, 'SI10', '19'),
(593, 'SI10', '4'),
(594, 'SI10', '8'),
(595, 'TI4', '24'),
(596, 'TI4', '9'),
(597, 'TI5', '20'),
(598, 'TI5', '4'),
(599, 'TI6', '2'),
(600, 'TI6', '21'),
(601, 'TI7', '10'),
(602, 'TI7', '13'),
(603, 'TI7', '6'),
(604, 'TI8', '12'),
(605, 'TI8', '23'),
(606, 'TI8', '28'),
(607, 'TI9', '12'),
(608, 'TI9', '23'),
(609, 'TI9', '28'),
(610, 'TI10', '14'),
(611, 'TI10', '25'),
(612, 'TI10', '26'),
(613, 'TE4', '34'),
(614, 'TE4', '35'),
(615, 'TE4', '48'),
(616, 'TE5', '45'),
(617, 'TE5', '55'),
(618, 'TE6', '45'),
(619, 'TE6', '47'),
(620, 'TE7', '32'),
(621, 'TE7', '36'),
(622, 'TE8', '37'),
(623, 'TE8', '47'),
(624, 'TE9', '39'),
(625, 'TE9', '40'),
(626, 'TE10', '47'),
(627, 'TE10', '48'),
(628, 'SI11', '12'),
(629, 'SI11', '19'),
(630, 'SI11', '8'),
(631, 'SI12', '11'),
(632, 'SI12', '19'),
(633, 'SI12', '28'),
(634, 'SI13', '12'),
(635, 'SI13', '23'),
(636, 'SI13', '7'),
(637, 'SI14', '11'),
(638, 'SI14', '12'),
(639, 'SI14', '23'),
(640, 'SI15', '11'),
(641, 'SI15', '13'),
(642, 'SI15', '22'),
(643, 'SI16', '13'),
(644, 'SI16', '14'),
(645, 'SI16', '2'),
(646, 'SI17', '25'),
(647, 'SI17', '26'),
(648, 'SI17', '4'),
(649, 'SI18', '22'),
(650, 'SI18', '6'),
(651, 'SI18', '7'),
(652, 'SI19', '22'),
(653, 'SI19', '6'),
(654, 'SI19', '7'),
(655, 'SI20', '15'),
(656, 'SI21', '14'),
(657, 'SI21', '16'),
(658, 'SI22', '11'),
(659, 'SI22', '19'),
(660, 'SI22', '8'),
(661, 'SI23', '14'),
(662, 'SI23', '2'),
(663, 'SI23', '24'),
(664, 'SI24', '11'),
(665, 'SI24', '56'),
(666, 'SI24', '9'),
(667, 'SI25', '11'),
(668, 'SI25', '12'),
(669, 'SI25', '8'),
(670, 'SI26', '22'),
(671, 'SI26', '4'),
(672, 'SI26', '7'),
(673, 'SI27', '17'),
(674, 'SI27', '23'),
(675, 'SI27', '6'),
(676, 'SI28', '10'),
(677, 'SI28', '17'),
(678, 'SI28', '23'),
(679, 'SI29', '10'),
(680, 'SI29', '17'),
(681, 'SI29', '6'),
(682, 'TI11', '15'),
(683, 'TI11', '16'),
(684, 'TI11', '9'),
(685, 'TI12', '18'),
(686, 'TI12', '27'),
(687, 'TI12', '29'),
(688, 'TI13', '18'),
(689, 'TI13', '23'),
(690, 'TI13', '27'),
(691, 'TI14', '15'),
(692, 'TI14', '16'),
(693, 'TI14', '17'),
(694, 'TI15', '17'),
(695, 'TI15', '25'),
(696, 'TI15', '26'),
(697, 'TI16', '13'),
(698, 'TI16', '8'),
(699, 'TI17', '11'),
(700, 'TI17', '24'),
(701, 'TI18', '17'),
(702, 'TI18', '25'),
(703, 'TI18', '26'),
(704, 'TI19', '18'),
(705, 'TI19', '27'),
(706, 'TI19', '29'),
(707, 'TI19', '9'),
(708, 'TI20', '56'),
(709, 'TI20', '9'),
(710, 'TI21', '15'),
(711, 'TI21', '17'),
(712, 'TI22', '14'),
(713, 'TI22', '15'),
(714, 'TI22', '17'),
(715, 'TI23', '10'),
(716, 'TI23', '22'),
(717, 'TI23', '28'),
(718, 'TI24', '10'),
(719, 'TI24', '2'),
(720, 'TI24', '20'),
(721, 'TI25', '14'),
(722, 'TI25', '2'),
(723, 'TI25', '26'),
(724, 'TI26', '16'),
(725, 'TI26', '56'),
(726, 'TI26', '9'),
(727, 'TI27', '29'),
(728, 'TI27', '56'),
(729, 'TI27', '9'),
(730, 'TI28', '14'),
(731, 'TI28', '2'),
(732, 'TI28', '26'),
(733, 'TE11', '46'),
(734, 'TE11', '53'),
(735, 'TE12', '45'),
(736, 'TE12', '46'),
(737, 'TE12', '50'),
(738, 'TE13', '49'),
(739, 'TE13', '53'),
(740, 'TE14', '41'),
(741, 'TE14', '48'),
(742, 'TE15', '50'),
(743, 'TE15', '54'),
(744, 'TE16', '33'),
(745, 'TE16', '47'),
(746, 'TE17', '46'),
(747, 'TE17', '55'),
(748, 'TE18', '39'),
(749, 'TE18', '42'),
(750, 'TE18', '49'),
(751, 'TE19', '35'),
(752, 'TE19', '43'),
(753, 'TE20', '40'),
(754, 'TE20', '42'),
(755, 'TE21', '33'),
(756, 'TE21', '42'),
(757, 'TE22', '35'),
(758, 'TE22', '44'),
(759, 'TE23', '42'),
(760, 'TE23', '49'),
(761, 'TE24', '45'),
(762, 'TE24', '55'),
(763, 'TE25', '46'),
(764, 'TE25', '53'),
(765, 'TE26', '48'),
(766, 'TE26', '53'),
(767, 'TE27', '47'),
(768, 'TE27', '48'),
(769, 'TE28', '53'),
(770, 'TE28', '54'),
(771, 'TE29', '49'),
(772, 'TE29', '50'),
(773, 'TE30', '40'),
(774, 'TE30', '51'),
(775, 'TE31', '35'),
(776, 'TE31', '54'),
(777, 'TE32', '44'),
(778, 'TE32', '52'),
(779, 'TE33', '53'),
(780, 'TE33', '54'),
(781, 'TE34', '51'),
(782, 'TE34', '55'),
(783, 'TE35', '47'),
(784, 'TE35', '55'),
(785, 'TE36', '42'),
(786, 'TE36', '56');

-- --------------------------------------------------------

--
-- Table structure for table `ruangan`
--

CREATE TABLE `ruangan` (
  `id` int(11) NOT NULL,
  `kode_ruangan` varchar(100) DEFAULT NULL,
  `kode_jurusan` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ruangan`
--

INSERT INTO `ruangan` (`id`, `kode_ruangan`, `kode_jurusan`) VALUES
(38, 'FF 2', '1'),
(39, 'FF 3', '1'),
(40, 'FF 4', '1'),
(41, 'FF 5', '1'),
(42, 'FF 6', '1'),
(43, 'FF 7', '1'),
(44, 'FF 8', '1'),
(45, 'FF 9', '1'),
(47, 'FF 11', '1'),
(48, 'FF 12', '1'),
(50, 'F.F 2', '2'),
(51, 'F.F 3', '2'),
(52, 'F.F 4', '2'),
(53, 'F.F 5', '2'),
(54, 'F.F 6', '2'),
(55, 'F.F 7', '2'),
(56, 'F.F 8', '2'),
(57, 'F.F 9', '2'),
(58, 'F.F 10', '2'),
(59, 'F.F 11', '2'),
(60, 'F.F 12', '2'),
(62, 'TI 2', '2'),
(63, 'TI 3', '2'),
(65, 'SG 2', '3'),
(66, 'SG 3', '3'),
(67, 'SG 4', '3'),
(68, 'SG 5', '3'),
(70, 'SG 7', '3'),
(71, 'SG 8', '3'),
(72, 'SG 9', '3'),
(73, 'SG 10', '3'),
(74, 'SG 11', '3'),
(75, 'SG 12', '3'),
(76, 'FTL 36', '3'),
(77, 'FTL 37', '3'),
(78, 'LAB KOMPUTER', '3'),
(79, 'LAB TERPADU 2', '3'),
(80, 'LAB INSTALASI', '3'),
(81, 'RBT', '3'),
(82, 'LAB MESIN-MESIN', '3'),
(83, 'LAB LISTRIK DASAR / LAB ELEKTRONIKA', '3'),
(95, 'FF 1', '1'),
(96, 'F.F 1', '2'),
(97, 'SG 1', '3'),
(103, 'FF 10', '1'),
(104, 'TI 1', '2'),
(105, 'SG 6', '3');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_dosen` (`kode_dosen`),
  ADD KEY `dosen_ibfk_1` (`kode_jurusan`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_mk` (`kode_mk`),
  ADD KEY `kode_dosen` (`kode_dosen`),
  ADD KEY `kode_ruangan` (`kode_ruangan`),
  ADD KEY `idx_pola` (`pola`),
  ADD KEY `idx_kode_mk` (`kode_mk`),
  ADD KEY `idx_kode_dosen` (`kode_dosen`),
  ADD KEY `idx_kode_ruangan` (`kode_ruangan`),
  ADD KEY `idx_hari` (`hari`);

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`kode_jurusan`);

--
-- Indexes for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_mk` (`kode_mk`),
  ADD KEY `kode_jurusan` (`kode_jurusan`),
  ADD KEY `kode_dosen` (`kode_dosen`),
  ADD KEY `idx_kode_jurusan` (`kode_jurusan`);

--
-- Indexes for table `mata_kuliah_dosen`
--
ALTER TABLE `mata_kuliah_dosen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_mk` (`kode_mk`),
  ADD KEY `kode_dosen` (`kode_dosen`);

--
-- Indexes for table `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_ruangan` (`kode_ruangan`),
  ADD KEY `fk_ruangan_jurusan` (`kode_jurusan`),
  ADD KEY `idx_kode_jurusan` (`kode_jurusan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55447;

--
-- AUTO_INCREMENT for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=310;

--
-- AUTO_INCREMENT for table `mata_kuliah_dosen`
--
ALTER TABLE `mata_kuliah_dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=787;

--
-- AUTO_INCREMENT for table `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dosen`
--
ALTER TABLE `dosen`
  ADD CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`kode_jurusan`) REFERENCES `jurusan` (`kode_jurusan`) ON DELETE SET NULL;

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`kode_mk`) REFERENCES `mata_kuliah` (`kode_mk`),
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`kode_dosen`) REFERENCES `dosen` (`kode_dosen`),
  ADD CONSTRAINT `jadwal_ibfk_3` FOREIGN KEY (`kode_ruangan`) REFERENCES `ruangan` (`kode_ruangan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD CONSTRAINT `mata_kuliah_ibfk_1` FOREIGN KEY (`kode_jurusan`) REFERENCES `jurusan` (`kode_jurusan`),
  ADD CONSTRAINT `mata_kuliah_ibfk_2` FOREIGN KEY (`kode_dosen`) REFERENCES `dosen` (`kode_dosen`);

--
-- Constraints for table `mata_kuliah_dosen`
--
ALTER TABLE `mata_kuliah_dosen`
  ADD CONSTRAINT `mata_kuliah_dosen_ibfk_1` FOREIGN KEY (`kode_mk`) REFERENCES `mata_kuliah` (`kode_mk`),
  ADD CONSTRAINT `mata_kuliah_dosen_ibfk_2` FOREIGN KEY (`kode_dosen`) REFERENCES `dosen` (`kode_dosen`);

--
-- Constraints for table `ruangan`
--
ALTER TABLE `ruangan`
  ADD CONSTRAINT `fk_ruangan_jurusan` FOREIGN KEY (`kode_jurusan`) REFERENCES `jurusan` (`kode_jurusan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
