-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 26, 2026 at 04:22 AM
-- Server version: 8.0.40
-- PHP Version: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_inventori`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `foto` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `alamat` text NOT NULL,
  `metode` enum('selfie','rfid','auto') NOT NULL DEFAULT 'selfie',
  `keterangan` varchar(255) DEFAULT NULL,
  `is_auto_out` tinyint(1) NOT NULL DEFAULT '0',
  `tipe` enum('in','out') NOT NULL DEFAULT 'in',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `absensi`
--


-- --------------------------------------------------------

--
-- Table structure for table `broadcasts`
--

CREATE TABLE `broadcasts` (
  `id` int UNSIGNED NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` enum('info','warning','success','danger') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `target_type` enum('all','group','level') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `target_value` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'comma-separated group names or levels',
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'filename di uploads/broadcast/',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `dibuat_oleh` int UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `broadcast_dismisses`
--

CREATE TABLE `broadcast_dismisses` (
  `id` int UNSIGNED NOT NULL,
  `broadcast_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `dismissed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` int UNSIGNED NOT NULL DEFAULT '0',
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ci_sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` int NOT NULL,
  `kode` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `npwp` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_npwp` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telepon` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pic` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ppn` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nilai: 1.1%, 11%',
  `pph` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nilai: 0.5%, 2%, 2.5%',
  `alamat` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--


-- --------------------------------------------------------

--
-- Table structure for table `daily_rent`
--

CREATE TABLE `daily_rent` (
  `id` int NOT NULL,
  `no_rent` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Auto-generate: DR001, DR002, ...',
  `customer_id` int DEFAULT NULL,
  `vendor_id` int DEFAULT NULL COMMENT 'Vendor utama order, bisa dioverride per unit',
  `pic_customer` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'PIC di sisi customer',
  `pic_customer_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rent_start_date` date DEFAULT NULL,
  `rent_start_time` time DEFAULT NULL,
  `rent_end_date` date DEFAULT NULL,
  `rent_end_time` time DEFAULT NULL,
  `actual_start_date` date DEFAULT NULL,
  `actual_start_time` time DEFAULT NULL,
  `location` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Lokasi operasional awal/default',
  `status_rent` enum('Sourcing Vendor','Scheduled','Active','Partially Returned','Completed','Cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Sourcing Vendor',
  `notes` text COLLATE utf8mb4_general_ci,
  `cancel_reason` text COLLATE utf8mb4_general_ci,
  `cancelled_at` datetime DEFAULT NULL,
  `cancelled_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL COMMENT 'Soft delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Header order daily rent';

-- --------------------------------------------------------

--
-- Table structure for table `daily_rent_driver_logs`
--

CREATE TABLE `daily_rent_driver_logs` (
  `id` int NOT NULL,
  `unit_id` int NOT NULL,
  `rent_id` int NOT NULL COMMENT 'Denormalized',
  `old_driver` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `old_no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `new_driver` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `new_no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `changed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `changed_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reason` text COLLATE utf8mb4_general_ci COMMENT 'Alasan ganti driver (sakit, cuti, dll)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log pergantian driver per unit mid-period';

-- --------------------------------------------------------

--
-- Table structure for table `daily_rent_extensions`
--

CREATE TABLE `daily_rent_extensions` (
  `id` int NOT NULL,
  `rent_id` int NOT NULL,
  `unit_id` int DEFAULT NULL COMMENT 'NULL = perpanjangan level order (semua unit)',
  `old_end_date` date NOT NULL,
  `old_end_time` time DEFAULT NULL,
  `new_end_date` date NOT NULL,
  `new_end_time` time DEFAULT NULL,
  `extension_days` decimal(5,1) NOT NULL DEFAULT '0.0' COMMENT 'Dihitung otomatis: new_end - old_end',
  `reason` text COLLATE utf8mb4_general_ci,
  `extended_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `extended_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='History perpanjangan sewa per order atau per unit';

-- --------------------------------------------------------

--
-- Table structure for table `daily_rent_units`
--

CREATE TABLE `daily_rent_units` (
  `id` int NOT NULL,
  `rent_id` int NOT NULL COMMENT 'FK ke daily_rent.id',
  `vendor_id` int DEFAULT NULL COMMENT 'Vendor per unit (boleh beda sama header)',
  `truck_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nopol` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `driver` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rent_start_date` date DEFAULT NULL,
  `rent_start_time` time DEFAULT NULL,
  `rent_end_date` date DEFAULT NULL,
  `rent_end_time` time DEFAULT NULL,
  `actual_start_date` date DEFAULT NULL,
  `actual_start_time` time DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `actual_return_time` time DEFAULT NULL,
  `current_location` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_unit` enum('Pending Assign','Assigned','Active','Extended','Returned','Cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending Assign',
  `overrun_days` decimal(5,1) DEFAULT '0.0' COMMENT 'Hari lebih dari target end, dihitung saat return',
  `overrun_notes` text COLLATE utf8mb4_general_ci,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detail unit/kendaraan per order daily rent';

-- --------------------------------------------------------

--
-- Table structure for table `daily_rent_unit_locations`
--

CREATE TABLE `daily_rent_unit_locations` (
  `id` int NOT NULL,
  `unit_id` int NOT NULL COMMENT 'FK ke daily_rent_units.id',
  `rent_id` int NOT NULL COMMENT 'Denormalized untuk query mudah',
  `location` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `moved_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moved_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Username yang input',
  `notes` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log perpindahan lokasi per unit';

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int NOT NULL,
  `nama_driver` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nik` varchar(16) COLLATE utf8mb4_general_ci NOT NULL,
  `sim` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `masa_berlaku_sim` date NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nomor HP driver',
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Email driver',
  `alamat` text COLLATE utf8mb4_general_ci COMMENT 'Alamat lengkap',
  `tipe_sim` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tipe SIM (A/B1/B2/C)',
  `status_driver` enum('aktif','cuti','resign','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif' COMMENT 'Status driver',
  `tanggal_bergabung` date DEFAULT NULL COMMENT 'Tanggal mulai kerja',
  `rating` decimal(3,2) DEFAULT '0.00' COMMENT 'Rating performance (0-5)',
  `total_trip` int DEFAULT '0' COMMENT 'Total perjalanan',
  `last_trip_date` date DEFAULT NULL COMMENT 'Last trip date',
  `keterangan` text COLLATE utf8mb4_general_ci COMMENT 'Catatan tambahan',
  `foto_sim` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_driver` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_performance`
--

CREATE TABLE `driver_performance` (
  `id` int NOT NULL,
  `driver_id` int NOT NULL,
  `surat_jalan_id` int DEFAULT NULL,
  `tanggal` date NOT NULL,
  `rating` int DEFAULT '5',
  `on_time` enum('yes','no') COLLATE utf8mb4_general_ci DEFAULT 'yes',
  `kondisi_barang` enum('baik','rusak','hilang') COLLATE utf8mb4_general_ci DEFAULT 'baik',
  `attitude` enum('baik','cukup','kurang') COLLATE utf8mb4_general_ci DEFAULT 'baik',
  `kendaraan_bersih` enum('yes','no') COLLATE utf8mb4_general_ci DEFAULT 'yes',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `reviewed_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `driver_violations`
--

CREATE TABLE `driver_violations` (
  `id` int NOT NULL,
  `driver_id` int NOT NULL,
  `violation_type` enum('speeding','accident','complaint','damage','late_delivery','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `violation_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `penalty_amount` decimal(15,2) DEFAULT '0.00',
  `status` enum('pending','paid','waived') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_date` date DEFAULT NULL,
  `resolved_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ftl_non_spx`
--

CREATE TABLE `ftl_non_spx` (
  `id` int NOT NULL,
  `no_shipment` varchar(20) NOT NULL,
  `customer_id` int DEFAULT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `origin2` varchar(100) DEFAULT NULL,
  `dest1` varchar(100) DEFAULT NULL,
  `dest2` varchar(100) DEFAULT NULL,
  `truck_type` varchar(50) DEFAULT NULL,
  `vendor_id` int DEFAULT NULL,
  `nopol` varchar(20) DEFAULT NULL,
  `driver` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `target_standby_date` date DEFAULT NULL,
  `target_standby_time` time DEFAULT NULL,
  `target_arrival_date` date DEFAULT NULL,
  `target_arrival_time` time DEFAULT NULL,
  `actual_tiba_muat_date` date DEFAULT NULL,
  `actual_tiba_muat_time` time DEFAULT NULL,
  `actual_loading_date` date DEFAULT NULL,
  `actual_loading_time` time DEFAULT NULL,
  `actual_depart_date` date DEFAULT NULL,
  `actual_depart_time` time DEFAULT NULL,
  `actual_tiba_bongkar_date` date DEFAULT NULL,
  `actual_tiba_bongkar_time` time DEFAULT NULL,
  `actual_done_at` datetime DEFAULT NULL,
  `done_notes` varchar(500) DEFAULT NULL COMMENT 'Catatan saat shipment selesai',
  `status_shipment` enum('Scheduled','Sourcing Vendor','Loading','On Trip','Tiba di Lokasi Muat','Tiba di Lokasi Bongkar','Completed','Cancelled') DEFAULT 'Scheduled',
  `cancel_reason` varchar(500) DEFAULT NULL COMMENT 'Alasan pembatalan shipment',
  `cancelled_at` datetime DEFAULT NULL COMMENT 'Waktu pembatalan',
  `cancelled_by` varchar(100) DEFAULT NULL COMMENT 'User yang membatalkan',
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ftl_non_spx`
--


-- --------------------------------------------------------

--
-- Table structure for table `golongan_jadwal`
--

CREATE TABLE `golongan_jadwal` (
  `id` int NOT NULL,
  `golongan` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `jadwal_kerja_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hari_off`
--

CREATE TABLE `hari_off` (
  `id` int NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `berlaku_untuk` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'operational_staff',
  `user_id` int UNSIGNED DEFAULT NULL,
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_kerja`
--

CREATE TABLE `jadwal_kerja` (
  `id` int NOT NULL,
  `nama_jadwal` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `hari_kerja` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_kerja`
--


-- --------------------------------------------------------

--
-- Table structure for table `karyawan_cuti`
--

CREATE TABLE `karyawan_cuti` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `tanggal_mulai` date NOT NULL COMMENT 'Tanggal mulai cuti',
  `tanggal_selesai` date NOT NULL COMMENT 'Tanggal selesai cuti',
  `jumlah_hari` int NOT NULL DEFAULT '1' COMMENT 'Total hari cuti (auto hitung)',
  `alasan` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Alasan pengajuan cuti',
  `status` enum('Pending','Disetujui','Ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `approved_by` int UNSIGNED DEFAULT NULL,
  `catatan_admin` text COLLATE utf8mb4_unicode_ci COMMENT 'Catatan dari admin saat approve/tolak',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='History pengajuan cuti karyawan';

--
-- Dumping data for table `karyawan_cuti`
--


-- --------------------------------------------------------

--
-- Table structure for table `karyawan_dokumen`
--

CREATE TABLE `karyawan_dokumen` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `jenis_dokumen` enum('Kontrak Kerja','SK Pengangkatan','SP1','SP2','SP3','Surat Peringatan Lainnya','Sertifikat','Ijazah','Lainnya') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Jenis dokumen',
  `nomor_dokumen` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor/kode dokumen',
  `tanggal_dokumen` date NOT NULL COMMENT 'Tanggal terbit dokumen',
  `tanggal_berlaku` date DEFAULT NULL COMMENT 'Tanggal berlaku (untuk kontrak)',
  `tanggal_expired` date DEFAULT NULL COMMENT 'Tanggal kadaluarsa (untuk kontrak)',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Path file upload',
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'MIME type file',
  `keterangan` text COLLATE utf8mb4_unicode_ci COMMENT 'Keterangan tambahan',
  `uploaded_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dokumen karyawan (kontrak, SK, SP, sertifikat, dll)';

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int UNSIGNED NOT NULL,
  `kode` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL COMMENT 'Tanggal lahir pengguna',
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nik` varchar(16) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_level` enum('superadmin','admin_operational','operational_staff','finance_staff','fleet_staff','viewer','admin_document','yamazaki','tsf','sinar_boga','rorotan','head_of_departemen','operational_lead','administration_lead','hr_staff') COLLATE utf8mb4_general_ci NOT NULL,
  `status_akun` enum('aktif','pending','ditolak','nonaktif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  `golongan` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Golongan kepegawaian (1A, 2B, 3C, dst)',
  `status_kepegawaian` enum('Tetap','Kontrak','Magang') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Status kepegawaian karyawan',
  `group_karyawan` enum('Yamazaki Staff','Admin TSC','Operasional TSC','TSF Staff','Sinar Boga Staff','Rorotan Staff') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Group/divisi karyawan',
  `can_view_laporan` tinyint(1) DEFAULT '0',
  `tanggal_join` date DEFAULT NULL COMMENT 'Tanggal mulai bergabung',
  `jatah_cuti` int NOT NULL DEFAULT '12' COMMENT 'Total jatah cuti per tahun (hari)',
  `sisa_cuti` int NOT NULL DEFAULT '12' COMMENT 'Sisa cuti yang belum dipakai',
  `foto_ktp` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default-1.png' COMMENT 'Foto profil user (default avatar)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `kode`, `nama`, `tanggal_lahir`, `username`, `password`, `nik`, `user_level`, `status_akun`, `golongan`, `status_kepegawaian`, `group_karyawan`, `can_view_laporan`, `tanggal_join`, `jatah_cuti`, `sisa_cuti`, `foto_ktp`, `foto_profil`) VALUES
(1, 'PGN17', 'RAYNOR HAYAT', '2003-11-08', 'raynorhayat', '$2y$10$uDNoJrJSy56edjfuQPyR8.ETTsopbeA3py7jQSESlLpJNL7FFtf8i', '3216090811030003', 'superadmin', 'aktif', NULL, NULL, 'Admin TSC', 0, NULL, 12, 12, 'ktp_1763349761_395.png', 'default-2.png');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna_group_akses`
--

CREATE TABLE `pengguna_group_akses` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `group_karyawan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengguna_group_akses`
--


-- --------------------------------------------------------

--
-- Table structure for table `register_requests`
--

CREATE TABLE `register_requests` (
  `id` int UNSIGNED NOT NULL,
  `nik` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `user_level` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_karyawan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_kepegawaian` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `golongan` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_join` date DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_profil` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default-1.png',
  `foto_ktp` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `register_requests`
--


-- --------------------------------------------------------

--
-- Table structure for table `rfid_cards`
--

CREATE TABLE `rfid_cards` (
  `id` int NOT NULL,
  `uid` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfid_cards`
--


-- --------------------------------------------------------

--
-- Table structure for table `rfid_pending`
--

CREATE TABLE `rfid_pending` (
  `id` int NOT NULL,
  `uid` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `scanned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_assigned` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_access_log`
--

CREATE TABLE `tb_access_log` (
  `id` int NOT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `page_url` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `method` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'GET',
  `user_agent` text COLLATE utf8mb4_general_ci,
  `country` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `isp` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'success',
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log all web access';

-- --------------------------------------------------------

--
-- Table structure for table `tb_active_sessions`
--

CREATE TABLE `tb_active_sessions` (
  `id` int UNSIGNED NOT NULL,
  `session_id` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `user_level` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `device_type` enum('desktop','mobile','tablet','unknown') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'unknown',
  `os` varchar(80) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Unknown',
  `browser` varchar(80) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Unknown',
  `user_agent` text COLLATE utf8mb4_general_ci,
  `country` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_code` varchar(4) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `isp` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_current` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_active_sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_akunbiaya`
--

CREATE TABLE `tb_akunbiaya` (
  `id` int NOT NULL,
  `tipe_akun` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kode_perkiraan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `akun_induk` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `saldo_awal` decimal(15,2) DEFAULT '0.00' COMMENT 'Saldo awal periode',
  `is_kas_bank` tinyint(1) DEFAULT '0' COMMENT '1=Kas/Bank, 0=Bukan',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_akunbiaya`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_akunbiaya_backup`
--

CREATE TABLE `tb_akunbiaya_backup` (
  `id` int NOT NULL DEFAULT '0',
  `tipe_akun` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kode_perkiraan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `akun_induk` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `saldo_awal` decimal(15,2) DEFAULT '0.00' COMMENT 'Saldo awal periode',
  `is_kas_bank` tinyint(1) DEFAULT '0' COMMENT '1=Kas/Bank, 0=Bukan',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_akunbiaya_backup`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_driver_keluhan`
--

CREATE TABLE `tb_driver_keluhan` (
  `id` int UNSIGNED NOT NULL,
  `nama_driver` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `no_polisi` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `no_lt` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nomor LT / surat jalan',
  `vendor` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `origin` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `destinasi` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keluhan` text COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Path file foto bukti',
  `status` enum('baru','diproses','selesai') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'baru',
  `catatan_admin` text COLLATE utf8mb4_general_ci COMMENT 'Catatan dari operational/superadmin',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_driver_keluhan`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_driver_performance`
--

CREATE TABLE `tb_driver_performance` (
  `id` int NOT NULL,
  `driver_id` int NOT NULL,
  `total_trips` int DEFAULT '0',
  `completed_trips` int DEFAULT '0',
  `cancelled_trips` int DEFAULT '0',
  `on_time_deliveries` int DEFAULT '0',
  `late_deliveries` int DEFAULT '0',
  `damaged_deliveries` int DEFAULT '0',
  `shortage_deliveries` int DEFAULT '0',
  `violations_count` int DEFAULT '0',
  `rating_average` decimal(3,2) DEFAULT '0.00',
  `last_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Driver performance metrics';

-- --------------------------------------------------------

--
-- Table structure for table `tb_email_log`
--

CREATE TABLE `tb_email_log` (
  `id` int NOT NULL,
  `to_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'pod_completion, invoice, payment_reminder, etc',
  `ref_id` int DEFAULT NULL COMMENT 'Reference ID (SJ ID, Invoice ID, etc)',
  `subject` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('sent','failed','pending') COLLATE utf8mb4_general_ci DEFAULT 'sent',
  `error_message` text COLLATE utf8mb4_general_ci,
  `retry_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Email sending log';

-- --------------------------------------------------------

--
-- Table structure for table `tb_fuel_consumption`
--

CREATE TABLE `tb_fuel_consumption` (
  `id` int NOT NULL,
  `unit_id` int NOT NULL,
  `sj_id` int DEFAULT NULL,
  `date` date NOT NULL,
  `fuel_amount` decimal(10,2) NOT NULL COMMENT 'Liters',
  `distance` decimal(10,2) DEFAULT '0.00' COMMENT 'Kilometers',
  `efficiency` decimal(10,2) DEFAULT '0.00' COMMENT 'km/liter',
  `cost` decimal(15,2) DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Vehicle fuel consumption tracking';

-- --------------------------------------------------------

--
-- Table structure for table `tb_import_log`
--

CREATE TABLE `tb_import_log` (
  `id` int NOT NULL,
  `batch_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `sheet_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_rows` int DEFAULT '0',
  `success_rows` int DEFAULT '0',
  `failed_rows` int DEFAULT '0',
  `imported_by` int DEFAULT NULL,
  `imported_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_import_log`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_invoice_tsc`
--

CREATE TABLE `tb_invoice_tsc` (
  `id` int NOT NULL,
  `no_invoice` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Manual input: TSC-SPV/00022/XI/2025',
  `customer_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Reference to customer.kode',
  `sj_id` int DEFAULT NULL COMMENT 'Reference to tb_surat_jalan',
  `no_surat_jalan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Surat Jalan number',
  `customer_kode` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_nama` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_alamat` text COLLATE utf8mb4_general_ci,
  `customer_pic` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_npwp` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_nama_npwp` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL COMMENT 'invoice_date + 14 days',
  `delivery_date` datetime DEFAULT NULL COMMENT 'POD delivery date',
  `driver_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Driver name',
  `vehicle_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Vehicle plate number',
  `route` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Origin → Destination',
  `qty_delivered` int DEFAULT '0' COMMENT 'Quantity delivered from POD',
  `delivery_condition` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'baik, rusak, rusak_sebagian, kurang',
  `is_auto_generated` tinyint(1) DEFAULT '0' COMMENT '1=Auto from POD, 0=Manual',
  `no_faktur` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_po` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nomor Purchase Order',
  `periode_shipment` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `ppn_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `ppn_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pph_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pph_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `terbilang` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `revenue_account_id` int DEFAULT NULL,
  `status` enum('draft','sent','unsent','paid','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `paid_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_invoice_tsc`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_invoice_tsc_items`
--

CREATE TABLE `tb_invoice_tsc_items` (
  `id` int NOT NULL,
  `invoice_id` int NOT NULL,
  `item_type` enum('item','deduction') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'item',
  `deskripsi` text COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_invoice_tsc_items`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_ip_blacklist`
--

CREATE TABLE `tb_ip_blacklist` (
  `id` int NOT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `blocked_at` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Blocked IP addresses';

-- --------------------------------------------------------

--
-- Table structure for table `tb_login_attempts`
--

CREATE TABLE `tb_login_attempts` (
  `id` int NOT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('success','failed') COLLATE utf8mb4_general_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Track all login attempts';

-- --------------------------------------------------------

--
-- Table structure for table `tb_login_history`
--

CREATE TABLE `tb_login_history` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `user_level` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `device_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'unknown',
  `os` varchar(80) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Unknown',
  `browser` varchar(80) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Unknown',
  `country` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `action` enum('login','logout','force_logout','auto_login') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'login',
  `status` enum('success','failed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'success',
  `fail_reason` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_login_history`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_monitoring_shipment`
--

CREATE TABLE `tb_monitoring_shipment` (
  `id` int NOT NULL,
  `sheet_type` enum('FTL_Non_SPX','Dailyrent','FTL_A1_SPX','FTL_Dedicated','FTL_COC_SPX','FTL_Reguler_SPX') COLLATE utf8mb4_general_ci NOT NULL,
  `periode` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Januari, Februari, dst',
  `customer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `division` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `origin` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest_1` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest_2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `truck_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `vendor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `driver` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'DONE, CANCEL, UNFULFILL, dll',
  `trip_cost_from_user` decimal(15,2) DEFAULT '0.00',
  `biaya_lain_user` decimal(15,2) DEFAULT '0.00',
  `rate_user_tsc` decimal(15,2) DEFAULT '0.00',
  `trip_cost_to_vendor` decimal(15,2) DEFAULT '0.00',
  `pph` decimal(15,2) DEFAULT '0.00',
  `biaya_lain_vendor` decimal(15,2) DEFAULT '0.00',
  `rate_tsc_vendor` decimal(15,2) DEFAULT '0.00',
  `margin` decimal(15,2) DEFAULT '0.00',
  `status_payment_vendor` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_payment_user` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_invoice_user` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `project` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rent_hours` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `imported_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `import_batch` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Batch ID saat import'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Data monitoring shipment TSC 2026';

--
-- Dumping data for table `tb_monitoring_shipment`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_notifications`
--

CREATE TABLE `tb_notifications` (
  `id` int NOT NULL,
  `user_target` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_level_target` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` enum('info','success','warning','danger','primary') COLLATE utf8mb4_general_ci DEFAULT 'info',
  `category` enum('purchase_order','surat_jalan','payment','approval','system','other') COLLATE utf8mb4_general_ci DEFAULT 'other',
  `icon` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'fa-bell',
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `ref_module` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ref_id` int DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_org_node`
--

CREATE TABLE `tb_org_node` (
  `id` int UNSIGNED NOT NULL,
  `parent_id` int UNSIGNED DEFAULT NULL,
  `departemen` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jabatan` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `pengguna_id` int UNSIGNED DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_org_node`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_org_visibility`
--

CREATE TABLE `tb_org_visibility` (
  `id` int UNSIGNED NOT NULL,
  `user_level` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_pemasukan`
--

CREATE TABLE `tb_pemasukan` (
  `id` int NOT NULL,
  `reff_no` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Format: I-xxxxx (Income)',
  `tanggal` date NOT NULL,
  `jenis_penerimaan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '201 atau 202',
  `no_invoice_cust` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi_rincian` text COLLATE utf8mb4_general_ci,
  `customer_id` int DEFAULT NULL,
  `nama_customer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tagihan_id` int DEFAULT NULL COMMENT 'Relasi ke tb_tagihan_customer',
  `nominal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `ppn` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pph` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_diterima` decimal(15,2) NOT NULL DEFAULT '0.00',
  `akun_bank_id` int NOT NULL COMMENT 'Bank/Kas tujuan terima uang',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_pembayaran_pajak`
--

CREATE TABLE `tb_pembayaran_pajak` (
  `id` int NOT NULL,
  `reff_no` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'PPH-00001, PPH-00002, ...',
  `jenis_pajak` varchar(10) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'PPH23, PPH42',
  `tanggal_bayar` date NOT NULL COMMENT 'Tanggal pembayaran ke negara',
  `masa_pajak` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Desember 2025, Januari 2026, ...',
  `no_bukti_potong` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nomor bukti potong',
  `nominal` decimal(15,2) NOT NULL COMMENT 'Nominal yang dibayar',
  `akun_ocas_id` int NOT NULL COMMENT 'ID akun OCAS (51 atau 52)',
  `akun_bank_id` int NOT NULL COMMENT 'ID akun bank yang digunakan',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Pembayaran PPH ke Negara';

-- --------------------------------------------------------

--
-- Table structure for table `tb_pengeluaran`
--

CREATE TABLE `tb_pengeluaran` (
  `id` int NOT NULL,
  `postingan_biaya` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `bulan_shipment` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_invoice_vendor` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi_rincian` text COLLATE utf8mb4_general_ci,
  `vendor_id` int DEFAULT NULL,
  `tagihan_id` int DEFAULT NULL,
  `akun_bank_id` int DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Approved' COMMENT 'Status: Approved, Paid, Pending, Rejected, Draft',
  `order_id` int DEFAULT NULL,
  `nama_vendor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `ppn` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pph` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_bayar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `reff_no` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pengeluaran`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_piutang_usaha`
--

CREATE TABLE `tb_piutang_usaha` (
  `id` int NOT NULL,
  `invoice_id` int NOT NULL,
  `customer_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `no_invoice` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `outstanding` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('outstanding','partial','paid','overdue') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'outstanding',
  `aging_days` int DEFAULT '0',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_piutang_usaha`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_pod_photos`
--

CREATE TABLE `tb_pod_photos` (
  `id` int NOT NULL,
  `sj_id` int NOT NULL,
  `photo_type` enum('barang','surat_jalan','tanda_tangan','lainnya') COLLATE utf8mb4_unicode_ci DEFAULT 'barang',
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uploaded_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_po_payment`
--

CREATE TABLE `tb_po_payment` (
  `id` int NOT NULL,
  `po_id` int NOT NULL,
  `no_payment` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `metode_bayar` enum('cash','transfer','giro','cek') COLLATE utf8mb4_general_ci DEFAULT 'transfer',
  `bank_nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_rekening` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_referensi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bukti_transfer` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_po_receiving`
--

CREATE TABLE `tb_po_receiving` (
  `id` int NOT NULL,
  `po_id` int NOT NULL,
  `po_detail_id` int NOT NULL,
  `no_receiving` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_terima` date NOT NULL,
  `qty_received` decimal(10,2) NOT NULL,
  `qty_rejected` decimal(10,2) DEFAULT '0.00',
  `kondisi` enum('baik','rusak','kurang') COLLATE utf8mb4_general_ci DEFAULT 'baik',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `received_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_bukti` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_purchase_order`
--

CREATE TABLE `tb_purchase_order` (
  `id` int NOT NULL,
  `no_po` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_po` date NOT NULL,
  `vendor_kode` int NOT NULL,
  `vendor_nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vendor_alamat` text COLLATE utf8mb4_general_ci,
  `vendor_npwp` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vendor_pic` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vendor_telp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kategori` enum('barang','jasa','aset') COLLATE utf8mb4_general_ci DEFAULT 'barang',
  `jenis_pembelian` enum('stock','project','operational') COLLATE utf8mb4_general_ci DEFAULT 'stock',
  `subtotal` decimal(15,2) DEFAULT '0.00',
  `diskon_persen` decimal(5,2) DEFAULT '0.00',
  `diskon_nominal` decimal(15,2) DEFAULT '0.00',
  `ppn_persen` decimal(5,2) DEFAULT '0.00',
  `ppn_nominal` decimal(15,2) DEFAULT '0.00',
  `pph_persen` decimal(5,2) DEFAULT '0.00',
  `pph_nominal` decimal(15,2) DEFAULT '0.00',
  `ongkir` decimal(15,2) DEFAULT '0.00',
  `biaya_lain` decimal(15,2) DEFAULT '0.00',
  `total_po` decimal(15,2) DEFAULT '0.00',
  `status` enum('draft','pending','approved','rejected','partial_received','received','completed','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `request_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approved_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_reason` text COLLATE utf8mb4_general_ci,
  `expected_delivery` date DEFAULT NULL,
  `delivery_address` text COLLATE utf8mb4_general_ci,
  `payment_terms` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_purchase_order_detail`
--

CREATE TABLE `tb_purchase_order_detail` (
  `id` int NOT NULL,
  `po_id` int NOT NULL,
  `item_nama` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `item_kode` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_spesifikasi` text COLLATE utf8mb4_general_ci,
  `item_satuan` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `qty_order` decimal(10,2) NOT NULL,
  `qty_received` decimal(10,2) DEFAULT '0.00',
  `harga_satuan` decimal(15,2) NOT NULL,
  `diskon_persen` decimal(5,2) DEFAULT '0.00',
  `diskon_nominal` decimal(15,2) DEFAULT '0.00',
  `subtotal` decimal(15,2) DEFAULT '0.00',
  `keterangan` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_rute`
--

CREATE TABLE `tb_rute` (
  `id` int NOT NULL,
  `kode_rute` varchar(200) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Auto generate dari gabungan field',
  `customer` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `service` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_unit` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `sla` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `origin` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nama DC Origin (manual)',
  `dest1` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Destination 1 (wajib)',
  `dest2` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Destination 2 (opsional)',
  `dest3` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Destination 3 (opsional)',
  `dest4` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Destination 4 (opsional)',
  `harga` decimal(15,0) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Master Rute Pengiriman';

--
-- Dumping data for table `tb_rute`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_security_threats`
--

CREATE TABLE `tb_security_threats` (
  `id` int NOT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `threat_type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `severity` enum('low','medium','high','critical') COLLATE utf8mb4_general_ci DEFAULT 'medium',
  `request_uri` text COLLATE utf8mb4_general_ci,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `detected_at` datetime NOT NULL,
  `blocked` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log detected security threats';

-- --------------------------------------------------------

--
-- Table structure for table `tb_surat_jalan`
--

CREATE TABLE `tb_surat_jalan` (
  `id` int NOT NULL,
  `no_surat_jalan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Format: SJ/YYYYMM/0001',
  `tanggal` date NOT NULL,
  `kode_rute` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Reference ke tb_rute.kode_rute',
  `driver_id` int DEFAULT NULL COMMENT 'FK ke drivers.id',
  `unit_id` int DEFAULT NULL COMMENT 'FK ke units.id',
  `customer` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'FTL, LTL, etc',
  `sla` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Express, Non Express',
  `tipe_unit` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'WB, FUSO, TRONTON',
  `origin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest1` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest2` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest3` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest4` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `muatan` text COLLATE utf8mb4_general_ci COMMENT 'Deskripsi barang',
  `tonase_aktual` decimal(10,2) DEFAULT '0.00' COMMENT 'Berat aktual (ton)',
  `kubikasi_aktual` decimal(10,2) DEFAULT '0.00' COMMENT 'Volume aktual (m3)',
  `biaya_sewa` decimal(15,0) DEFAULT '0' COMMENT 'Dari tb_rute.harga',
  `biaya_solar` decimal(15,0) DEFAULT '0',
  `biaya_tol` decimal(15,0) DEFAULT '0',
  `biaya_parkir` decimal(15,0) DEFAULT '0',
  `biaya_makan` decimal(15,0) DEFAULT '0',
  `biaya_lainnya` decimal(15,0) DEFAULT '0',
  `total_biaya` decimal(15,0) DEFAULT '0' COMMENT 'Total semua biaya',
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `jam_berangkat` datetime DEFAULT NULL,
  `jam_tiba` datetime DEFAULT NULL,
  `target_tiba` datetime DEFAULT NULL COMMENT 'Auto calculate dari SLA',
  `keterlambatan` int DEFAULT '0' COMMENT 'Delay dalam menit',
  `sla_status` enum('on_time','late','very_late') COLLATE utf8mb4_general_ci DEFAULT 'on_time',
  `dest1_status` enum('pending','delivered','failed','skip') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `dest1_time` datetime DEFAULT NULL,
  `dest1_catatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest2_status` enum('pending','delivered','failed','skip') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `dest2_time` datetime DEFAULT NULL,
  `dest2_catatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest3_status` enum('pending','delivered','failed','skip') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `dest3_time` datetime DEFAULT NULL,
  `dest3_catatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest4_status` enum('pending','delivered','failed','skip') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `dest4_time` datetime DEFAULT NULL,
  `dest4_catatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_general_ci,
  `foto_surat_jalan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_bukti_kirim` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `arrival_time` datetime DEFAULT NULL COMMENT 'Waktu tiba di lokasi',
  `unloading_start` datetime DEFAULT NULL COMMENT 'Mulai bongkar',
  `unloading_finish` datetime DEFAULT NULL COMMENT 'Selesai bongkar',
  `qty_delivered` int DEFAULT NULL COMMENT 'Jumlah barang diterima',
  `qty_rejected` int DEFAULT '0' COMMENT 'Jumlah barang ditolak',
  `receiver_name` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nama penerima',
  `receiver_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'No HP penerima',
  `receiver_signature` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Path foto tanda tangan',
  `photo_proof` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Path foto bukti terima',
  `delivery_condition` enum('baik','rusak_sebagian','rusak','kurang') COLLATE utf8mb4_general_ci DEFAULT 'baik' COMMENT 'Kondisi barang',
  `delivery_notes` text COLLATE utf8mb4_general_ci COMMENT 'Catatan pengiriman',
  `pod_status` enum('pending','completed','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending' COMMENT 'Status POD',
  `pod_submitted_at` datetime DEFAULT NULL COMMENT 'Waktu submit POD',
  `pod_submitted_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Yang submit POD',
  `invoice_tsc_id` int DEFAULT NULL COMMENT 'Reference to tb_invoice_tsc',
  `invoice_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Invoice number from tb_invoice_tsc',
  `return_time` datetime DEFAULT NULL COMMENT 'Waktu mulai kembali',
  `return_arrival` datetime DEFAULT NULL COMMENT 'Waktu tiba kembali di gudang',
  `actual_distance_km` decimal(10,2) DEFAULT NULL COMMENT 'Jarak tempuh actual (km)',
  `fuel_consumed_liters` decimal(10,2) DEFAULT NULL COMMENT 'Konsumsi BBM (liter)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Surat Jalan / Delivery Order';

--
-- Dumping data for table `tb_surat_jalan`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_surat_jalan_backup`
--

CREATE TABLE `tb_surat_jalan_backup` (
  `id` int NOT NULL DEFAULT '0',
  `no_surat_jalan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Format: SJ/YYYYMM/0001',
  `tanggal` date NOT NULL,
  `kode_rute` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Reference ke tb_rute.kode_rute',
  `driver_id` int DEFAULT NULL COMMENT 'FK ke drivers.id',
  `unit_id` int DEFAULT NULL COMMENT 'FK ke units.id',
  `customer` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'FTL, LTL, etc',
  `sla` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Express, Non Express',
  `tipe_unit` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'WB, FUSO, TRONTON',
  `origin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest1` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest2` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest3` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest4` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `muatan` text COLLATE utf8mb4_general_ci COMMENT 'Deskripsi barang',
  `tonase_aktual` decimal(10,2) DEFAULT '0.00' COMMENT 'Berat aktual (ton)',
  `kubikasi_aktual` decimal(10,2) DEFAULT '0.00' COMMENT 'Volume aktual (m3)',
  `biaya_sewa` decimal(15,0) DEFAULT '0' COMMENT 'Dari tb_rute.harga',
  `biaya_solar` decimal(15,0) DEFAULT '0',
  `biaya_tol` decimal(15,0) DEFAULT '0',
  `biaya_parkir` decimal(15,0) DEFAULT '0',
  `biaya_makan` decimal(15,0) DEFAULT '0',
  `biaya_lainnya` decimal(15,0) DEFAULT '0',
  `total_biaya` decimal(15,0) DEFAULT '0' COMMENT 'Total semua biaya',
  `status` enum('draft','scheduled','approved','loading','departed','in_transit','arrived','unloading','completed','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `jam_berangkat` datetime DEFAULT NULL,
  `jam_tiba` datetime DEFAULT NULL,
  `target_tiba` datetime DEFAULT NULL COMMENT 'Auto calculate dari SLA',
  `keterlambatan` int DEFAULT '0' COMMENT 'Delay dalam menit',
  `sla_status` enum('on_time','late','very_late') COLLATE utf8mb4_general_ci DEFAULT 'on_time',
  `dest1_status` enum('pending','delivered','failed','skip') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `dest1_time` datetime DEFAULT NULL,
  `dest1_catatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest2_status` enum('pending','delivered','failed','skip') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `dest2_time` datetime DEFAULT NULL,
  `dest2_catatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest3_status` enum('pending','delivered','failed','skip') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `dest3_time` datetime DEFAULT NULL,
  `dest3_catatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dest4_status` enum('pending','delivered','failed','skip') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `dest4_time` datetime DEFAULT NULL,
  `dest4_catatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_general_ci,
  `foto_surat_jalan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_bukti_kirim` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `arrival_time` datetime DEFAULT NULL COMMENT 'Waktu tiba di lokasi',
  `unloading_start` datetime DEFAULT NULL COMMENT 'Mulai bongkar',
  `unloading_finish` datetime DEFAULT NULL COMMENT 'Selesai bongkar',
  `qty_delivered` int DEFAULT NULL COMMENT 'Jumlah barang diterima',
  `qty_rejected` int DEFAULT '0' COMMENT 'Jumlah barang ditolak',
  `receiver_name` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nama penerima',
  `receiver_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'No HP penerima',
  `receiver_signature` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Path foto tanda tangan',
  `photo_proof` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Path foto bukti terima',
  `delivery_condition` enum('baik','rusak_sebagian','rusak','kurang') COLLATE utf8mb4_general_ci DEFAULT 'baik' COMMENT 'Kondisi barang',
  `delivery_notes` text COLLATE utf8mb4_general_ci COMMENT 'Catatan pengiriman',
  `pod_status` enum('pending','completed','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending' COMMENT 'Status POD',
  `pod_submitted_at` datetime DEFAULT NULL COMMENT 'Waktu submit POD',
  `pod_submitted_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Yang submit POD',
  `invoice_tsc_id` int DEFAULT NULL COMMENT 'Reference to tb_invoice_tsc',
  `invoice_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Invoice number from tb_invoice_tsc',
  `return_time` datetime DEFAULT NULL COMMENT 'Waktu mulai kembali',
  `return_arrival` datetime DEFAULT NULL COMMENT 'Waktu tiba kembali di gudang',
  `actual_distance_km` decimal(10,2) DEFAULT NULL COMMENT 'Jarak tempuh actual (km)',
  `fuel_consumed_liters` decimal(10,2) DEFAULT NULL COMMENT 'Konsumsi BBM (liter)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_surat_jalan_backup`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_surat_jalan_biaya`
--

CREATE TABLE `tb_surat_jalan_biaya` (
  `id` int NOT NULL,
  `surat_jalan_id` int NOT NULL,
  `jenis_biaya` enum('solar','tol','parkir','makan','service','lainnya') COLLATE utf8mb4_general_ci NOT NULL,
  `nominal` decimal(15,0) NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_bukti` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detail biaya operasional per trip';

-- --------------------------------------------------------

--
-- Table structure for table `tb_surat_jalan_tracking`
--

CREATE TABLE `tb_surat_jalan_tracking` (
  `id` int NOT NULL,
  `surat_jalan_id` int NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `lokasi` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `lat` decimal(10,8) DEFAULT NULL COMMENT 'GPS Latitude (optional)',
  `lng` decimal(11,8) DEFAULT NULL COMMENT 'GPS Longitude (optional)',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracking history & GPS log';

--
-- Dumping data for table `tb_surat_jalan_tracking`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_tagihan_customer`
--

CREATE TABLE `tb_tagihan_customer` (
  `id` int NOT NULL,
  `customer_id` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_customer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_invoice` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `bulan_shipment` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `ppn` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pph` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_tagihan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status_payment` enum('Waiting Payment','Paid') COLLATE utf8mb4_general_ci DEFAULT 'Waiting Payment',
  `kode_payment` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Reff dari pemasukan',
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_tagihan_vendor`
--

CREATE TABLE `tb_tagihan_vendor` (
  `id` int NOT NULL,
  `vendor_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_vendor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_invoice` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `invoice_recieve_date` date DEFAULT NULL,
  `bulan_shipment` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nominal` decimal(15,2) DEFAULT '0.00',
  `status_payment` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Waiting Payment',
  `kode_payment` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Relasi ke Reff No Pengeluaran',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi_keuangan`
--

CREATE TABLE `tb_transaksi_keuangan` (
  `id` int NOT NULL,
  `tanggal` date NOT NULL,
  `no_transaksi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `akun_id` int NOT NULL COMMENT 'FK ke tb_akunbiaya',
  `nominal` decimal(15,2) NOT NULL,
  `debit` decimal(15,2) DEFAULT '0.00',
  `kredit` decimal(15,2) DEFAULT '0.00',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `referensi_tipe` enum('Manual','Order','Pengeluaran','TagihanVendor','Pemasukan','Penerimaan_Pembayaran','Pembayaran_Tagihan') COLLATE utf8mb4_general_ci DEFAULT 'Manual',
  `referensi_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabel transaksi keuangan untuk laporan';

--
-- Dumping data for table `tb_transaksi_keuangan`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi_order`
--

CREATE TABLE `tb_transaksi_order` (
  `id` int NOT NULL,
  `kode_order` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` int DEFAULT NULL,
  `nama_customer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_order` date NOT NULL,
  `bulan_shipment` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_invoice_customer` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nominal_payment` decimal(15,2) DEFAULT '0.00',
  `status_payment_customer` enum('Waiting Payment','Paid') COLLATE utf8mb4_general_ci DEFAULT 'Waiting Payment',
  `status_payment_vendor` enum('Waiting Payment','Paid') COLLATE utf8mb4_general_ci DEFAULT 'Waiting Payment',
  `reff_payment_vendor` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_trip_events`
--

CREATE TABLE `tb_trip_events` (
  `id` int NOT NULL,
  `sj_id` int NOT NULL,
  `event_type` enum('created','approved','loading_start','departure','in_transit','arrival','unloading_start','unloading_finish','pod_submitted','return_start','return_arrival','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_time` datetime NOT NULL,
  `location_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_vendor`
--

CREATE TABLE `tb_vendor` (
  `kode` int NOT NULL,
  `nama_vendor` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat_vendor` text COLLATE utf8mb4_general_ci NOT NULL,
  `npwp_vendor` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `pic_vendor` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `no_telp_vendor` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `ppn_vendor` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `pph_vendor` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ppn` decimal(5,2) DEFAULT '0.00',
  `pph` decimal(5,2) DEFAULT '0.00',
  `file_npwp` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_skb` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_sppkp` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_vendor`
--


-- --------------------------------------------------------

--
-- Table structure for table `tb_whatsapp_log`
--

CREATE TABLE `tb_whatsapp_log` (
  `id` int NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `message_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'pod_notification, invoice, payment_reminder, etc',
  `ref_id` int DEFAULT NULL,
  `message_content` text COLLATE utf8mb4_general_ci,
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('sent','failed','pending') COLLATE utf8mb4_general_ci DEFAULT 'sent',
  `provider` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'fonnte' COMMENT 'fonnte, wablas, twilio, etc',
  `response` text COLLATE utf8mb4_general_ci,
  `error_message` text COLLATE utf8mb4_general_ci,
  `retry_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='WhatsApp message log';

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int UNSIGNED NOT NULL,
  `kode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'TSC-TICKET-YYYYMMDD-XXXX',
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('bug','akses','hardware','software','jaringan','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lainnya',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `submitted_by` int UNSIGNED NOT NULL COMMENT 'FK ke tabel users/pengguna',
  `handled_by` int UNSIGNED DEFAULT NULL COMMENT 'Admin/IT yang handle',
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--


-- --------------------------------------------------------

--
-- Table structure for table `ticket_logs`
--

CREATE TABLE `ticket_logs` (
  `id` int UNSIGNED NOT NULL,
  `ticket_id` int UNSIGNED NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `by_user_id` int UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `tms_alerts`
--

CREATE TABLE `tms_alerts` (
  `id` int NOT NULL,
  `alert_type` enum('stnk_expired','kir_expired','sim_expired','service_due','document_expired') COLLATE utf8mb4_general_ci NOT NULL,
  `reference_type` enum('unit','driver','document') COLLATE utf8mb4_general_ci NOT NULL,
  `reference_id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `alert_date` date NOT NULL,
  `expired_date` date DEFAULT NULL,
  `status` enum('pending','acknowledged','resolved') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_general_ci DEFAULT 'medium',
  `acknowledged_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='TMS Alert & Reminder System';

--
-- Dumping data for table `tms_alerts`
--


-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int NOT NULL,
  `no_polisi` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_unit` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_box` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tahun_unit` year NOT NULL,
  `panjang` decimal(5,2) NOT NULL,
  `lebar` decimal(5,2) NOT NULL,
  `tinggi` decimal(5,2) NOT NULL,
  `tonase` decimal(5,2) NOT NULL,
  `foto_stnk` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_kir` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_barcode_solar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stnk_expired` date DEFAULT NULL COMMENT 'Tanggal expired STNK',
  `kir_expired` date DEFAULT NULL COMMENT 'Tanggal expired KIR',
  `status_unit` enum('aktif','maintenance','rusak','dijual','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif' COMMENT 'Status operasional unit',
  `kapasitas_kg` int DEFAULT NULL COMMENT 'Kapasitas maksimal (kg)',
  `bahan_bakar` enum('bensin','solar','pertamax','pertalite') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Jenis bahan bakar',
  `konsumsi_bbm` decimal(5,2) DEFAULT NULL COMMENT 'Rata-rata km/liter',
  `last_service_date` date DEFAULT NULL COMMENT 'Tanggal service terakhir',
  `last_service_km` int DEFAULT NULL COMMENT 'KM saat service terakhir',
  `next_service_km` int DEFAULT NULL COMMENT 'KM untuk service berikutnya',
  `current_km` int DEFAULT '0' COMMENT 'KM saat ini',
  `last_used_date` date DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci COMMENT 'Catatan tambahan',
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--


-- --------------------------------------------------------

--
-- Table structure for table `unit_documents`
--

CREATE TABLE `unit_documents` (
  `id` int NOT NULL,
  `unit_id` int NOT NULL,
  `jenis_dokumen` enum('stnk','kir','asuransi','pajak','keur','lainnya') COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_dokumen` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_terbit` date DEFAULT NULL,
  `tanggal_expired` date NOT NULL,
  `biaya` decimal(15,2) DEFAULT '0.00',
  `file_dokumen` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('aktif','expired','diproses') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `reminder_days` int DEFAULT '30' COMMENT 'Reminder berapa hari sebelum expired',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Dokumen-dokumen unit (STNK, KIR, dll)';

-- --------------------------------------------------------

--
-- Table structure for table `unit_fuel`
--

CREATE TABLE `unit_fuel` (
  `id` int NOT NULL,
  `unit_id` int NOT NULL,
  `driver_id` int DEFAULT NULL,
  `tanggal_isi` date NOT NULL,
  `waktu_isi` time DEFAULT NULL,
  `liter` decimal(10,2) NOT NULL,
  `harga_per_liter` decimal(10,2) NOT NULL,
  `total_biaya` decimal(15,2) NOT NULL,
  `km_saat_isi` int DEFAULT NULL,
  `km_terakhir` int DEFAULT NULL COMMENT 'KM saat isi sebelumnya',
  `jarak_tempuh` int DEFAULT NULL COMMENT 'KM sejak isi terakhir',
  `konsumsi` decimal(5,2) DEFAULT NULL COMMENT 'KM per liter',
  `jenis_bbm` enum('bensin','solar','pertamax','pertalite') COLLATE utf8mb4_general_ci NOT NULL,
  `spbu` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lokasi` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bukti_struk` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Upload foto struk',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `driver_nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='History pengisian BBM unit';

-- --------------------------------------------------------

--
-- Table structure for table `unit_maintenance`
--

CREATE TABLE `unit_maintenance` (
  `id` int NOT NULL,
  `unit_id` int NOT NULL,
  `tanggal_service` date NOT NULL,
  `km_saat_service` int DEFAULT NULL,
  `jenis_service` enum('service_rutin','perbaikan','ganti_oli','ganti_ban','ganti_aki','tune_up','lainnya') COLLATE utf8mb4_general_ci NOT NULL,
  `bengkel` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `teknisi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `biaya` decimal(15,2) DEFAULT '0.00',
  `parts_diganti` text COLLATE utf8mb4_general_ci COMMENT 'List spare parts yang diganti',
  `next_service_km` int DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `bukti_nota` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'File upload nota/invoice',
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='History maintenance unit';

-- --------------------------------------------------------

--
-- Table structure for table `vendor_operasional`
--

CREATE TABLE `vendor_operasional` (
  `id` int NOT NULL,
  `nama_vendor` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor_operasional`
--


-- --------------------------------------------------------

--
-- Stand-in structure for view `v_daily_rent_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_daily_rent_summary` (
`id` int
,`no_rent` varchar(20)
,`customer_id` int
,`vendor_id` int
,`pic_customer` varchar(100)
,`rent_start_date` date
,`rent_start_time` time
,`rent_end_date` date
,`rent_end_time` time
,`actual_start_date` date
,`location` varchar(200)
,`status_rent` enum('Sourcing Vendor','Scheduled','Active','Partially Returned','Completed','Cancelled')
,`notes` text
,`cancel_reason` text
,`created_at` datetime
,`deleted_at` datetime
,`nama_customer` varchar(80)
,`nama_vendor` varchar(150)
,`total_units` bigint
,`units_pending` decimal(23,0)
,`units_assigned` decimal(23,0)
,`units_active` decimal(23,0)
,`units_extended` decimal(23,0)
,`units_returned` decimal(23,0)
,`units_cancelled` decimal(23,0)
,`target_duration_days` int
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_daily_rent_units_detail`
-- (See below for the actual view)
--
CREATE TABLE `v_daily_rent_units_detail` (
`id` int
,`rent_id` int
,`vendor_id` int
,`truck_type` varchar(50)
,`nopol` varchar(20)
,`driver` varchar(100)
,`no_hp` varchar(20)
,`rent_start_date` date
,`rent_start_time` time
,`rent_end_date` date
,`rent_end_time` time
,`actual_start_date` date
,`actual_start_time` time
,`actual_return_date` date
,`actual_return_time` time
,`current_location` varchar(200)
,`status_unit` enum('Pending Assign','Assigned','Active','Extended','Returned','Cancelled')
,`overrun_days` decimal(5,1)
,`overrun_notes` text
,`notes` text
,`created_at` datetime
,`updated_at` datetime
,`deleted_at` datetime
,`no_rent` varchar(20)
,`order_start_date` date
,`order_end_date` date
,`status_rent` enum('Sourcing Vendor','Scheduled','Active','Partially Returned','Completed','Cancelled')
,`customer_id` int
,`nama_customer` varchar(80)
,`nama_vendor` varchar(150)
,`actual_duration_days` int
,`remaining_days` int
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_performa_karyawan`
-- (See below for the actual view)
--
CREATE TABLE `v_performa_karyawan` (
`user_id` int unsigned
,`nama` varchar(100)
,`nik` varchar(16)
,`user_level` enum('superadmin','admin_operational','operational_staff','finance_staff','fleet_staff','viewer','admin_document','yamazaki','tsf','sinar_boga','rorotan','head_of_departemen','operational_lead','administration_lead','hr_staff')
,`foto_profil` varchar(255)
,`golongan` varchar(10)
,`status_kepegawaian` enum('Tetap','Kontrak','Magang')
,`group_karyawan` enum('Yamazaki Staff','Admin TSC','Operasional TSC','TSF Staff','Sinar Boga Staff','Rorotan Staff')
,`tanggal_join` date
,`jatah_cuti` bigint
,`nama_jadwal` varchar(50)
,`hari_kerja_efektif` varchar(20)
,`sisa_cuti` decimal(33,0)
,`hadir_bulan_ini` bigint
,`total_hari_bulan_ini` bigint
,`persen_kehadiran` decimal(25,1)
,`jumlah_sp` bigint
,`hadir_tahun_ini` bigint
,`cuti_disetujui_tahun_ini` bigint
,`cuti_pending` bigint
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tanggal_tipe` (`tanggal`,`tipe`),
  ADD KEY `idx_user_tanggal` (`user_id`,`tanggal`);

--
-- Indexes for table `broadcasts`
--
ALTER TABLE `broadcasts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_pinned` (`is_pinned`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indexes for table `broadcast_dismisses`
--
ALTER TABLE `broadcast_dismisses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_dismiss` (`broadcast_id`,`user_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_rent`
--
ALTER TABLE `daily_rent`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_no_rent` (`no_rent`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_vendor_id` (`vendor_id`),
  ADD KEY `idx_status_rent` (`status_rent`),
  ADD KEY `idx_rent_start_date` (`rent_start_date`),
  ADD KEY `idx_rent_end_date` (`rent_end_date`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `daily_rent_driver_logs`
--
ALTER TABLE `daily_rent_driver_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_unit_id` (`unit_id`),
  ADD KEY `idx_rent_id` (`rent_id`);

--
-- Indexes for table `daily_rent_extensions`
--
ALTER TABLE `daily_rent_extensions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rent_id` (`rent_id`),
  ADD KEY `idx_unit_id` (`unit_id`);

--
-- Indexes for table `daily_rent_units`
--
ALTER TABLE `daily_rent_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rent_id` (`rent_id`),
  ADD KEY `idx_nopol` (`nopol`),
  ADD KEY `idx_driver` (`driver`),
  ADD KEY `idx_status_unit` (`status_unit`);

--
-- Indexes for table `daily_rent_unit_locations`
--
ALTER TABLE `daily_rent_unit_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_unit_id` (`unit_id`),
  ADD KEY `idx_rent_id` (`rent_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `idx_status_driver` (`status_driver`),
  ADD KEY `idx_masa_berlaku_sim` (`masa_berlaku_sim`);

--
-- Indexes for table `driver_performance`
--
ALTER TABLE `driver_performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_driver_tanggal` (`driver_id`,`tanggal`);

--
-- Indexes for table `driver_violations`
--
ALTER TABLE `driver_violations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_driver_id` (`driver_id`),
  ADD KEY `idx_violation_date` (`violation_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_driver_status` (`driver_id`,`status`),
  ADD KEY `idx_date_range` (`violation_date`);

--
-- Indexes for table `ftl_non_spx`
--
ALTER TABLE `ftl_non_spx`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_shipment` (`no_shipment`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `idx_status_shipment` (`status_shipment`),
  ADD KEY `idx_standby_sort` (`target_standby_date`,`target_standby_time`,`created_at`),
  ADD KEY `idx_status_deleted` (`status_shipment`,`deleted_at`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_vendor_id` (`vendor_id`),
  ADD KEY `idx_nopol` (`nopol`),
  ADD KEY `idx_driver` (`driver`),
  ADD KEY `idx_target_arrival` (`target_arrival_date`);
ALTER TABLE `ftl_non_spx` ADD FULLTEXT KEY `ft_search` (`no_shipment`,`origin`,`origin2`,`dest1`,`dest2`,`truck_type`,`nopol`,`driver`,`no_hp`);

--
-- Indexes for table `golongan_jadwal`
--
ALTER TABLE `golongan_jadwal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `golongan` (`golongan`),
  ADD KEY `jadwal_kerja_id` (`jadwal_kerja_id`);

--
-- Indexes for table `hari_off`
--
ALTER TABLE `hari_off`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hari_off_user` (`user_id`),
  ADD KEY `hari_off_ibfk_1` (`created_by`);

--
-- Indexes for table `jadwal_kerja`
--
ALTER TABLE `jadwal_kerja`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `karyawan_cuti`
--
ALTER TABLE `karyawan_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tanggal_mulai` (`tanggal_mulai`),
  ADD KEY `fk_cuti_approver` (`approved_by`);

--
-- Indexes for table `karyawan_dokumen`
--
ALTER TABLE `karyawan_dokumen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_jenis_dokumen` (`jenis_dokumen`),
  ADD KEY `idx_tanggal_expired` (`tanggal_expired`),
  ADD KEY `fk_dokumen_uploader` (`uploaded_by`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indexes for table `pengguna_group_akses`
--
ALTER TABLE `pengguna_group_akses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_group` (`user_id`,`group_karyawan`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_group` (`group_karyawan`);

--
-- Indexes for table `register_requests`
--
ALTER TABLE `register_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_nik` (`nik`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rfid_pending`
--
ALTER TABLE `rfid_pending`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`);

--
-- Indexes for table `tb_access_log`
--
ALTER TABLE `tb_access_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_ip_timestamp` (`ip_address`,`timestamp`);

--
-- Indexes for table `tb_active_sessions`
--
ALTER TABLE `tb_active_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_session` (`session_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_active` (`last_activity`);

--
-- Indexes for table `tb_akunbiaya`
--
ALTER TABLE `tb_akunbiaya`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik_kode` (`kode_perkiraan`),
  ADD KEY `idx_kode` (`kode_perkiraan`);

--
-- Indexes for table `tb_driver_keluhan`
--
ALTER TABLE `tb_driver_keluhan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_driver_performance`
--
ALTER TABLE `tb_driver_performance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_driver` (`driver_id`);

--
-- Indexes for table `tb_email_log`
--
ALTER TABLE `tb_email_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`to_email`),
  ADD KEY `idx_type` (`email_type`),
  ADD KEY `idx_ref` (`ref_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `tb_fuel_consumption`
--
ALTER TABLE `tb_fuel_consumption`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_unit` (`unit_id`),
  ADD KEY `idx_sj` (`sj_id`),
  ADD KEY `idx_date` (`date`);

--
-- Indexes for table `tb_import_log`
--
ALTER TABLE `tb_import_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_invoice_tsc`
--
ALTER TABLE `tb_invoice_tsc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_no_invoice` (`no_invoice`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_invoice_date` (`invoice_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sj_id` (`sj_id`),
  ADD KEY `idx_no_surat_jalan` (`no_surat_jalan`),
  ADD KEY `idx_auto_generated` (`is_auto_generated`),
  ADD KEY `idx_revenue_account` (`revenue_account_id`),
  ADD KEY `idx_no_po` (`no_po`);

--
-- Indexes for table `tb_invoice_tsc_items`
--
ALTER TABLE `tb_invoice_tsc_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_id`);

--
-- Indexes for table `tb_ip_blacklist`
--
ALTER TABLE `tb_ip_blacklist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip_address` (`ip_address`),
  ADD KEY `idx_ip_active` (`ip_address`,`is_active`);

--
-- Indexes for table `tb_login_attempts`
--
ALTER TABLE `tb_login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `tb_login_history`
--
ALTER TABLE `tb_login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`,`created_at`);

--
-- Indexes for table `tb_monitoring_shipment`
--
ALTER TABLE `tb_monitoring_shipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer` (`customer`),
  ADD KEY `idx_periode` (`periode`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_sheet_type` (`sheet_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_vendor` (`vendor`);

--
-- Indexes for table `tb_notifications`
--
ALTER TABLE `tb_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_target` (`user_target`),
  ADD KEY `idx_user_level` (`user_level_target`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `tb_org_node`
--
ALTER TABLE `tb_org_node`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_pengguna` (`pengguna_id`),
  ADD KEY `fk_org_created` (`created_by`),
  ADD KEY `fk_org_updated` (`updated_by`);

--
-- Indexes for table `tb_org_visibility`
--
ALTER TABLE `tb_org_visibility`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_org_level` (`user_level`);

--
-- Indexes for table `tb_pemasukan`
--
ALTER TABLE `tb_pemasukan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reff_no` (`reff_no`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `tagihan_id` (`tagihan_id`);

--
-- Indexes for table `tb_pembayaran_pajak`
--
ALTER TABLE `tb_pembayaran_pajak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reff_no` (`reff_no`),
  ADD KEY `idx_tanggal` (`tanggal_bayar`),
  ADD KEY `idx_jenis` (`jenis_pajak`);

--
-- Indexes for table `tb_pengeluaran`
--
ALTER TABLE `tb_pengeluaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reff_no` (`reff_no`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_vendor_id` (`vendor_id`),
  ADD KEY `tagihan_id` (`tagihan_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indexes for table `tb_piutang_usaha`
--
ALTER TABLE `tb_piutang_usaha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `tb_pod_photos`
--
ALTER TABLE `tb_pod_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sj_id` (`sj_id`);

--
-- Indexes for table `tb_po_payment`
--
ALTER TABLE `tb_po_payment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_payment` (`no_payment`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `tb_po_receiving`
--
ALTER TABLE `tb_po_receiving`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_receiving` (`no_receiving`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `po_detail_id` (`po_detail_id`);

--
-- Indexes for table `tb_purchase_order`
--
ALTER TABLE `tb_purchase_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_po` (`no_po`),
  ADD KEY `vendor_kode` (`vendor_kode`);

--
-- Indexes for table `tb_purchase_order_detail`
--
ALTER TABLE `tb_purchase_order_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `tb_rute`
--
ALTER TABLE `tb_rute`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_rute` (`kode_rute`),
  ADD KEY `idx_customer` (`customer`),
  ADD KEY `idx_origin` (`origin`),
  ADD KEY `idx_dest1` (`dest1`);

--
-- Indexes for table `tb_security_threats`
--
ALTER TABLE `tb_security_threats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_detected_at` (`detected_at`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_ip_detected` (`ip_address`,`detected_at`);

--
-- Indexes for table `tb_surat_jalan`
--
ALTER TABLE `tb_surat_jalan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_surat_jalan` (`no_surat_jalan`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_kode_rute` (`kode_rute`),
  ADD KEY `idx_customer` (`customer`),
  ADD KEY `idx_sj_pod_status` (`pod_status`),
  ADD KEY `idx_sj_arrival_time` (`arrival_time`),
  ADD KEY `idx_sj_status_date` (`status`,`tanggal`),
  ADD KEY `idx_invoice_tsc` (`invoice_tsc_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `tb_surat_jalan_biaya`
--
ALTER TABLE `tb_surat_jalan_biaya`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surat_jalan_id` (`surat_jalan_id`);

--
-- Indexes for table `tb_surat_jalan_tracking`
--
ALTER TABLE `tb_surat_jalan_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_surat_jalan` (`surat_jalan_id`);

--
-- Indexes for table `tb_tagihan_customer`
--
ALTER TABLE `tb_tagihan_customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `status_payment` (`status_payment`);

--
-- Indexes for table `tb_tagihan_vendor`
--
ALTER TABLE `tb_tagihan_vendor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `status_payment` (`status_payment`);

--
-- Indexes for table `tb_transaksi_keuangan`
--
ALTER TABLE `tb_transaksi_keuangan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_akun` (`akun_id`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_referensi` (`referensi_tipe`,`referensi_id`);

--
-- Indexes for table `tb_transaksi_order`
--
ALTER TABLE `tb_transaksi_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_order` (`kode_order`);

--
-- Indexes for table `tb_trip_events`
--
ALTER TABLE `tb_trip_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sj_event` (`sj_id`,`event_type`),
  ADD KEY `idx_event_time` (`event_time`);

--
-- Indexes for table `tb_vendor`
--
ALTER TABLE `tb_vendor`
  ADD PRIMARY KEY (`kode`);

--
-- Indexes for table `tb_whatsapp_log`
--
ALTER TABLE `tb_whatsapp_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone_number`),
  ADD KEY `idx_type` (`message_type`),
  ADD KEY `idx_ref` (`ref_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_kode` (`kode`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_submitted_by` (`submitted_by`);

--
-- Indexes for table `ticket_logs`
--
ALTER TABLE `ticket_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ticket_id` (`ticket_id`);

--
-- Indexes for table `tms_alerts`
--
ALTER TABLE `tms_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_priority` (`status`,`priority`),
  ADD KEY `idx_alert_date` (`alert_date`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_polisi` (`no_polisi`),
  ADD KEY `idx_status_unit` (`status_unit`),
  ADD KEY `idx_stnk_expired` (`stnk_expired`),
  ADD KEY `idx_kir_expired` (`kir_expired`);

--
-- Indexes for table `unit_documents`
--
ALTER TABLE `unit_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_unit_expired` (`unit_id`,`tanggal_expired`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `unit_fuel`
--
ALTER TABLE `unit_fuel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `idx_unit_tanggal` (`unit_id`,`tanggal_isi`);

--
-- Indexes for table `unit_maintenance`
--
ALTER TABLE `unit_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_unit_tanggal` (`unit_id`,`tanggal_service`);

--
-- Indexes for table `vendor_operasional`
--
ALTER TABLE `vendor_operasional`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1806;

--
-- AUTO_INCREMENT for table `broadcasts`
--
ALTER TABLE `broadcasts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `broadcast_dismisses`
--
ALTER TABLE `broadcast_dismisses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `daily_rent`
--
ALTER TABLE `daily_rent`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_rent_driver_logs`
--
ALTER TABLE `daily_rent_driver_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_rent_extensions`
--
ALTER TABLE `daily_rent_extensions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_rent_units`
--
ALTER TABLE `daily_rent_units`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_rent_unit_locations`
--
ALTER TABLE `daily_rent_unit_locations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `driver_performance`
--
ALTER TABLE `driver_performance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_violations`
--
ALTER TABLE `driver_violations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ftl_non_spx`
--
ALTER TABLE `ftl_non_spx`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `golongan_jadwal`
--
ALTER TABLE `golongan_jadwal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hari_off`
--
ALTER TABLE `hari_off`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jadwal_kerja`
--
ALTER TABLE `jadwal_kerja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `karyawan_cuti`
--
ALTER TABLE `karyawan_cuti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `karyawan_dokumen`
--
ALTER TABLE `karyawan_dokumen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `pengguna_group_akses`
--
ALTER TABLE `pengguna_group_akses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `register_requests`
--
ALTER TABLE `register_requests`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `rfid_pending`
--
ALTER TABLE `rfid_pending`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `tb_access_log`
--
ALTER TABLE `tb_access_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_active_sessions`
--
ALTER TABLE `tb_active_sessions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1293;

--
-- AUTO_INCREMENT for table `tb_akunbiaya`
--
ALTER TABLE `tb_akunbiaya`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `tb_driver_keluhan`
--
ALTER TABLE `tb_driver_keluhan`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `tb_driver_performance`
--
ALTER TABLE `tb_driver_performance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_email_log`
--
ALTER TABLE `tb_email_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_fuel_consumption`
--
ALTER TABLE `tb_fuel_consumption`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_import_log`
--
ALTER TABLE `tb_import_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_invoice_tsc`
--
ALTER TABLE `tb_invoice_tsc`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT for table `tb_invoice_tsc_items`
--
ALTER TABLE `tb_invoice_tsc_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1013;

--
-- AUTO_INCREMENT for table `tb_ip_blacklist`
--
ALTER TABLE `tb_ip_blacklist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_login_attempts`
--
ALTER TABLE `tb_login_attempts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_login_history`
--
ALTER TABLE `tb_login_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1453;

--
-- AUTO_INCREMENT for table `tb_monitoring_shipment`
--
ALTER TABLE `tb_monitoring_shipment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31021;

--
-- AUTO_INCREMENT for table `tb_notifications`
--
ALTER TABLE `tb_notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_org_node`
--
ALTER TABLE `tb_org_node`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tb_org_visibility`
--
ALTER TABLE `tb_org_visibility`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_pemasukan`
--
ALTER TABLE `tb_pemasukan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_pembayaran_pajak`
--
ALTER TABLE `tb_pembayaran_pajak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_pengeluaran`
--
ALTER TABLE `tb_pengeluaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=481;

--
-- AUTO_INCREMENT for table `tb_piutang_usaha`
--
ALTER TABLE `tb_piutang_usaha`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT for table `tb_pod_photos`
--
ALTER TABLE `tb_pod_photos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_po_payment`
--
ALTER TABLE `tb_po_payment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_po_receiving`
--
ALTER TABLE `tb_po_receiving`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_purchase_order`
--
ALTER TABLE `tb_purchase_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_purchase_order_detail`
--
ALTER TABLE `tb_purchase_order_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_rute`
--
ALTER TABLE `tb_rute`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109621;

--
-- AUTO_INCREMENT for table `tb_security_threats`
--
ALTER TABLE `tb_security_threats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_surat_jalan`
--
ALTER TABLE `tb_surat_jalan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_surat_jalan_biaya`
--
ALTER TABLE `tb_surat_jalan_biaya`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_surat_jalan_tracking`
--
ALTER TABLE `tb_surat_jalan_tracking`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_tagihan_customer`
--
ALTER TABLE `tb_tagihan_customer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_tagihan_vendor`
--
ALTER TABLE `tb_tagihan_vendor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_transaksi_keuangan`
--
ALTER TABLE `tb_transaksi_keuangan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4184;

--
-- AUTO_INCREMENT for table `tb_transaksi_order`
--
ALTER TABLE `tb_transaksi_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_trip_events`
--
ALTER TABLE `tb_trip_events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_vendor`
--
ALTER TABLE `tb_vendor`
  MODIFY `kode` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `tb_whatsapp_log`
--
ALTER TABLE `tb_whatsapp_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ticket_logs`
--
ALTER TABLE `ticket_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tms_alerts`
--
ALTER TABLE `tms_alerts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `unit_documents`
--
ALTER TABLE `unit_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unit_fuel`
--
ALTER TABLE `unit_fuel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unit_maintenance`
--
ALTER TABLE `unit_maintenance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_operasional`
--
ALTER TABLE `vendor_operasional`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

-- --------------------------------------------------------

--
-- Structure for view `v_daily_rent_summary`
--
DROP TABLE IF EXISTS `v_daily_rent_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`tsct1296`@`localhost` SQL SECURITY DEFINER VIEW `v_daily_rent_summary`  AS SELECT `dr`.`id` AS `id`, `dr`.`no_rent` AS `no_rent`, `dr`.`customer_id` AS `customer_id`, `dr`.`vendor_id` AS `vendor_id`, `dr`.`pic_customer` AS `pic_customer`, `dr`.`rent_start_date` AS `rent_start_date`, `dr`.`rent_start_time` AS `rent_start_time`, `dr`.`rent_end_date` AS `rent_end_date`, `dr`.`rent_end_time` AS `rent_end_time`, `dr`.`actual_start_date` AS `actual_start_date`, `dr`.`location` AS `location`, `dr`.`status_rent` AS `status_rent`, `dr`.`notes` AS `notes`, `dr`.`cancel_reason` AS `cancel_reason`, `dr`.`created_at` AS `created_at`, `dr`.`deleted_at` AS `deleted_at`, `c`.`nama` AS `nama_customer`, `v`.`nama_vendor` AS `nama_vendor`, count(`dru`.`id`) AS `total_units`, sum((`dru`.`status_unit` = 'Pending Assign')) AS `units_pending`, sum((`dru`.`status_unit` = 'Assigned')) AS `units_assigned`, sum((`dru`.`status_unit` = 'Active')) AS `units_active`, sum((`dru`.`status_unit` = 'Extended')) AS `units_extended`, sum((`dru`.`status_unit` = 'Returned')) AS `units_returned`, sum((`dru`.`status_unit` = 'Cancelled')) AS `units_cancelled`, (to_days(`dr`.`rent_end_date`) - to_days(`dr`.`rent_start_date`)) AS `target_duration_days` FROM (((`daily_rent` `dr` left join `customer` `c` on((`c`.`id` = `dr`.`customer_id`))) left join `vendor_operasional` `v` on((`v`.`id` = `dr`.`vendor_id`))) left join `daily_rent_units` `dru` on(((`dru`.`rent_id` = `dr`.`id`) and (`dru`.`deleted_at` is null)))) WHERE (`dr`.`deleted_at` is null) GROUP BY `dr`.`id` ;

-- --------------------------------------------------------

--
-- Structure for view `v_daily_rent_units_detail`
--
DROP TABLE IF EXISTS `v_daily_rent_units_detail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`tsct1296`@`localhost` SQL SECURITY DEFINER VIEW `v_daily_rent_units_detail`  AS SELECT `dru`.`id` AS `id`, `dru`.`rent_id` AS `rent_id`, `dru`.`vendor_id` AS `vendor_id`, `dru`.`truck_type` AS `truck_type`, `dru`.`nopol` AS `nopol`, `dru`.`driver` AS `driver`, `dru`.`no_hp` AS `no_hp`, `dru`.`rent_start_date` AS `rent_start_date`, `dru`.`rent_start_time` AS `rent_start_time`, `dru`.`rent_end_date` AS `rent_end_date`, `dru`.`rent_end_time` AS `rent_end_time`, `dru`.`actual_start_date` AS `actual_start_date`, `dru`.`actual_start_time` AS `actual_start_time`, `dru`.`actual_return_date` AS `actual_return_date`, `dru`.`actual_return_time` AS `actual_return_time`, `dru`.`current_location` AS `current_location`, `dru`.`status_unit` AS `status_unit`, `dru`.`overrun_days` AS `overrun_days`, `dru`.`overrun_notes` AS `overrun_notes`, `dru`.`notes` AS `notes`, `dru`.`created_at` AS `created_at`, `dru`.`updated_at` AS `updated_at`, `dru`.`deleted_at` AS `deleted_at`, `dr`.`no_rent` AS `no_rent`, `dr`.`rent_start_date` AS `order_start_date`, `dr`.`rent_end_date` AS `order_end_date`, `dr`.`status_rent` AS `status_rent`, `dr`.`customer_id` AS `customer_id`, `c`.`nama` AS `nama_customer`, `v`.`nama_vendor` AS `nama_vendor`, (case when ((`dru`.`actual_return_date` is not null) and (`dru`.`actual_start_date` is not null)) then (to_days(`dru`.`actual_return_date`) - to_days(`dru`.`actual_start_date`)) when (`dru`.`actual_start_date` is not null) then (to_days(curdate()) - to_days(`dru`.`actual_start_date`)) else NULL end) AS `actual_duration_days`, (case when (`dru`.`status_unit` in ('Returned','Cancelled')) then NULL when (`dru`.`rent_end_date` is not null) then (to_days(`dru`.`rent_end_date`) - to_days(curdate())) else NULL end) AS `remaining_days` FROM (((`daily_rent_units` `dru` left join `daily_rent` `dr` on((`dr`.`id` = `dru`.`rent_id`))) left join `customer` `c` on((`c`.`id` = `dr`.`customer_id`))) left join `vendor_operasional` `v` on((`v`.`id` = `dru`.`vendor_id`))) WHERE (`dru`.`deleted_at` is null) ;

-- --------------------------------------------------------

--
-- Structure for view `v_performa_karyawan`
--
DROP TABLE IF EXISTS `v_performa_karyawan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`tsct1296`@`localhost` SQL SECURITY DEFINER VIEW `v_performa_karyawan`  AS SELECT `p`.`id` AS `user_id`, `p`.`nama` AS `nama`, `p`.`nik` AS `nik`, `p`.`user_level` AS `user_level`, `p`.`foto_profil` AS `foto_profil`, `p`.`golongan` AS `golongan`, `p`.`status_kepegawaian` AS `status_kepegawaian`, `p`.`group_karyawan` AS `group_karyawan`, `p`.`tanggal_join` AS `tanggal_join`, coalesce(`p`.`jatah_cuti`,12) AS `jatah_cuti`, coalesce(`j`.`nama_jadwal`,'Senin - Sabtu') AS `nama_jadwal`, coalesce(`j`.`hari_kerja`,'1,2,3,4,5,6') AS `hari_kerja_efektif`, (coalesce(`p`.`jatah_cuti`,12) - coalesce((select sum(`kc`.`jumlah_hari`) from `karyawan_cuti` `kc` where ((`kc`.`user_id` = `p`.`id`) and (`kc`.`status` = 'Disetujui') and (year(`kc`.`tanggal_mulai`) = year(curdate())))),0)) AS `sisa_cuti`, coalesce((select count(0) from `absensi` `a` where ((`a`.`user_id` = `p`.`id`) and (month(`a`.`tanggal`) = month(curdate())) and (year(`a`.`tanggal`) = year(curdate())))),0) AS `hadir_bulan_ini`, (select count(0) from (select (date_format(curdate(),'%Y-%m-01') + interval ((`t4`.`i` * 10) + `t3`.`i`) day) AS `tgl` from ((select 0 AS `i` union select 1 AS `1` union select 2 AS `2` union select 3 AS `3` union select 4 AS `4` union select 5 AS `5` union select 6 AS `6` union select 7 AS `7` union select 8 AS `8` union select 9 AS `9`) `t3` join (select 0 AS `i` union select 1 AS `1` union select 2 AS `2` union select 3 AS `3`) `t4`)) `cal` where ((month(`cal`.`tgl`) = month(curdate())) and (year(`cal`.`tgl`) = year(curdate())) and (find_in_set((case dayofweek(`cal`.`tgl`) when 2 then 1 when 3 then 2 when 4 then 3 when 5 then 4 when 6 then 5 when 7 then 6 when 1 then 7 end),coalesce((`j`.`hari_kerja` collate utf8mb4_general_ci),'1,2,3,4,5,6')) > 0) and (((`p`.`user_level` collate utf8mb4_general_ci) <> 'operational_staff') or `cal`.`tgl` in (select `hari_off`.`tanggal` from `hari_off` where ((`hari_off`.`berlaku_untuk` = 'operational_staff') and ((`hari_off`.`user_id` is null) or (`hari_off`.`user_id` = `p`.`id`)))) is false))) AS `total_hari_bulan_ini`, round(((coalesce((select count(0) from `absensi` `a` where ((`a`.`user_id` = `p`.`id`) and (month(`a`.`tanggal`) = month(curdate())) and (year(`a`.`tanggal`) = year(curdate())))),0) * 100.0) / nullif((select count(0) from (select (date_format(curdate(),'%Y-%m-01') + interval ((`t4`.`i` * 10) + `t3`.`i`) day) AS `tgl` from ((select 0 AS `i` union select 1 AS `1` union select 2 AS `2` union select 3 AS `3` union select 4 AS `4` union select 5 AS `5` union select 6 AS `6` union select 7 AS `7` union select 8 AS `8` union select 9 AS `9`) `t3` join (select 0 AS `i` union select 1 AS `1` union select 2 AS `2` union select 3 AS `3`) `t4`)) `cal` where ((month(`cal`.`tgl`) = month(curdate())) and (year(`cal`.`tgl`) = year(curdate())) and (find_in_set((case dayofweek(`cal`.`tgl`) when 2 then 1 when 3 then 2 when 4 then 3 when 5 then 4 when 6 then 5 when 7 then 6 when 1 then 7 end),coalesce((`j`.`hari_kerja` collate utf8mb4_general_ci),'1,2,3,4,5,6')) > 0) and (((`p`.`user_level` collate utf8mb4_general_ci) <> 'operational_staff') or `cal`.`tgl` in (select `hari_off`.`tanggal` from `hari_off` where ((`hari_off`.`berlaku_untuk` = 'operational_staff') and ((`hari_off`.`user_id` is null) or (`hari_off`.`user_id` = `p`.`id`)))) is false))),0)),1) AS `persen_kehadiran`, coalesce((select count(0) from `karyawan_dokumen` `kd` where ((`kd`.`user_id` = `p`.`id`) and (`kd`.`jenis_dokumen` in ('SP1','SP2','SP3')))),0) AS `jumlah_sp`, coalesce((select count(0) from `absensi` `a` where ((`a`.`user_id` = `p`.`id`) and (year(`a`.`tanggal`) = year(curdate())))),0) AS `hadir_tahun_ini`, coalesce((select count(0) from `karyawan_cuti` `kc` where ((`kc`.`user_id` = `p`.`id`) and (`kc`.`status` = 'Disetujui') and (year(`kc`.`tanggal_mulai`) = year(curdate())))),0) AS `cuti_disetujui_tahun_ini`, coalesce((select count(0) from `karyawan_cuti` `kc` where ((`kc`.`user_id` = `p`.`id`) and (`kc`.`status` = 'Pending'))),0) AS `cuti_pending` FROM ((`pengguna` `p` left join `golongan_jadwal` `gj` on(((`gj`.`golongan` collate utf8mb4_general_ci) = (`p`.`golongan` collate utf8mb4_general_ci)))) left join `jadwal_kerja` `j` on((`j`.`id` = `gj`.`jadwal_kerja_id`))) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_rent_driver_logs`
--
ALTER TABLE `daily_rent_driver_logs`
  ADD CONSTRAINT `fk_drdl_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `daily_rent_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_rent_extensions`
--
ALTER TABLE `daily_rent_extensions`
  ADD CONSTRAINT `fk_drex_rent_id` FOREIGN KEY (`rent_id`) REFERENCES `daily_rent` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_drex_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `daily_rent_units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `daily_rent_units`
--
ALTER TABLE `daily_rent_units`
  ADD CONSTRAINT `fk_dru_rent_id` FOREIGN KEY (`rent_id`) REFERENCES `daily_rent` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_rent_unit_locations`
--
ALTER TABLE `daily_rent_unit_locations`
  ADD CONSTRAINT `fk_drul_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `daily_rent_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_performance`
--
ALTER TABLE `driver_performance`
  ADD CONSTRAINT `driver_performance_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_violations`
--
ALTER TABLE `driver_violations`
  ADD CONSTRAINT `fk_driver_violations_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `golongan_jadwal`
--
ALTER TABLE `golongan_jadwal`
  ADD CONSTRAINT `golongan_jadwal_ibfk_1` FOREIGN KEY (`jadwal_kerja_id`) REFERENCES `jadwal_kerja` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hari_off`
--
ALTER TABLE `hari_off`
  ADD CONSTRAINT `fk_hari_off_user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `hari_off_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `karyawan_cuti`
--
ALTER TABLE `karyawan_cuti`
  ADD CONSTRAINT `fk_cuti_approver` FOREIGN KEY (`approved_by`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `fk_cuti_user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `karyawan_dokumen`
--
ALTER TABLE `karyawan_dokumen`
  ADD CONSTRAINT `fk_dokumen_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `fk_dokumen_user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD CONSTRAINT `fk_rfid_user` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `tb_invoice_tsc_items`
--
ALTER TABLE `tb_invoice_tsc_items`
  ADD CONSTRAINT `tb_invoice_tsc_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tb_invoice_tsc` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_org_node`
--
ALTER TABLE `tb_org_node`
  ADD CONSTRAINT `fk_org_created` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_org_parent` FOREIGN KEY (`parent_id`) REFERENCES `tb_org_node` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_org_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_org_updated` FOREIGN KEY (`updated_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tb_piutang_usaha`
--
ALTER TABLE `tb_piutang_usaha`
  ADD CONSTRAINT `tb_piutang_usaha_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tb_invoice_tsc` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_pod_photos`
--
ALTER TABLE `tb_pod_photos`
  ADD CONSTRAINT `tb_pod_photos_ibfk_1` FOREIGN KEY (`sj_id`) REFERENCES `tb_surat_jalan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_po_payment`
--
ALTER TABLE `tb_po_payment`
  ADD CONSTRAINT `tb_po_payment_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `tb_purchase_order` (`id`);

--
-- Constraints for table `tb_po_receiving`
--
ALTER TABLE `tb_po_receiving`
  ADD CONSTRAINT `tb_po_receiving_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `tb_purchase_order` (`id`),
  ADD CONSTRAINT `tb_po_receiving_ibfk_2` FOREIGN KEY (`po_detail_id`) REFERENCES `tb_purchase_order_detail` (`id`);

--
-- Constraints for table `tb_purchase_order`
--
ALTER TABLE `tb_purchase_order`
  ADD CONSTRAINT `tb_purchase_order_ibfk_1` FOREIGN KEY (`vendor_kode`) REFERENCES `tb_vendor` (`kode`);

--
-- Constraints for table `tb_purchase_order_detail`
--
ALTER TABLE `tb_purchase_order_detail`
  ADD CONSTRAINT `tb_purchase_order_detail_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `tb_purchase_order` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_surat_jalan`
--
ALTER TABLE `tb_surat_jalan`
  ADD CONSTRAINT `tb_surat_jalan_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tb_surat_jalan_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tb_surat_jalan_biaya`
--
ALTER TABLE `tb_surat_jalan_biaya`
  ADD CONSTRAINT `tb_surat_jalan_biaya_ibfk_1` FOREIGN KEY (`surat_jalan_id`) REFERENCES `tb_surat_jalan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_surat_jalan_tracking`
--
ALTER TABLE `tb_surat_jalan_tracking`
  ADD CONSTRAINT `tb_surat_jalan_tracking_ibfk_1` FOREIGN KEY (`surat_jalan_id`) REFERENCES `tb_surat_jalan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_trip_events`
--
ALTER TABLE `tb_trip_events`
  ADD CONSTRAINT `tb_trip_events_ibfk_1` FOREIGN KEY (`sj_id`) REFERENCES `tb_surat_jalan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `unit_documents`
--
ALTER TABLE `unit_documents`
  ADD CONSTRAINT `unit_documents_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `unit_fuel`
--
ALTER TABLE `unit_fuel`
  ADD CONSTRAINT `unit_fuel_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `unit_fuel_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `unit_maintenance`
--
ALTER TABLE `unit_maintenance`
  ADD CONSTRAINT `unit_maintenance_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;