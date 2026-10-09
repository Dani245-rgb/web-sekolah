
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agenda` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lokasi` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Published','Draft') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Published',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `anggota_organisasi` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_organisasi` int unsigned NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `anggota_organisasi_id_organisasi_foreign` (`id_organisasi`),
  CONSTRAINT `anggota_organisasi_id_organisasi_foreign` FOREIGN KEY (`id_organisasi`) REFERENCES `organisasi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id_log` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aksi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tabel_target` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_record_target` int unsigned DEFAULT NULL,
  `nilai_lama` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nilai_baru` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_import_log` int unsigned DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_log`),
  KEY `user_id` (`user_id`),
  KEY `idx_audit_created_at` (`created_at`),
  KEY `idx_record_target` (`tabel_target`,`id_record_target`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `berita` (
  `id_berita` int unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` enum('Akademik','Prestasi','Kegiatan','Umum') COLLATE utf8mb4_general_ci NOT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ringkasan` varchar(300) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `konten` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Draft','Published') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Draft',
  `posisi` enum('hero','utama','biasa','populer','hits') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'biasa',
  `tanggal_publish` date DEFAULT NULL,
  `id_user` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_berita`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bk_artikel` (
  `id_artikel` int unsigned NOT NULL AUTO_INCREMENT,
  `kategori` enum('kesehatan_mental','karier','tes_minat') COLLATE utf8mb4_general_ci NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `konten` text COLLATE utf8mb4_general_ci,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `link_eksternal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('Draft','Published') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Draft',
  `id_guru_penulis` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_artikel`),
  UNIQUE KEY `slug` (`slug`),
  KEY `bk_artikel_id_guru_penulis_foreign` (`id_guru_penulis`),
  CONSTRAINT `bk_artikel_id_guru_penulis_foreign` FOREIGN KEY (`id_guru_penulis`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekstrakurikuler` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `status` enum('Published','Draft') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Published',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `galeri` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` enum('Kegiatan','Fasilitas','Prestasi','Lainnya') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Kegiatan',
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `status` enum('Published','Draft') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Published',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guru` (
  `id_guru` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `nip` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `nuptk` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tempat_lahir` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Aktif',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_guru`),
  UNIQUE KEY `nip` (`nip`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `guru_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_log` (
  `id_import_log` int unsigned NOT NULL AUTO_INCREMENT,
  `jenis_import` enum('siswa','guru','jadwal','nilai') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_referensi` int unsigned DEFAULT NULL,
  `id_user` int unsigned DEFAULT NULL,
  `nama_file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `path_file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `total_baris` int NOT NULL DEFAULT '0',
  `baris_selesai` int NOT NULL DEFAULT '0',
  `total_sukses` int NOT NULL DEFAULT '0',
  `total_ditimpa` int DEFAULT '0',
  `total_gagal` int NOT NULL DEFAULT '0',
  `status` enum('Proses','Selesai','Gagal','di-rollback') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Proses',
  `detail_gagal` longtext COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_import_log`),
  KEY `idx_jenis_import` (`jenis_import`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurusan` (
  `id_jurusan` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_jurusan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_jurusan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `singkatan` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kompetensi` text COLLATE utf8mb4_general_ci,
  `prospek_kerja` text COLLATE utf8mb4_general_ci,
  `status` enum('Aktif','Nonaktif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Aktif',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_jurusan`),
  UNIQUE KEY `kode_jurusan` (`kode_jurusan`),
  UNIQUE KEY `unique_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kalender_akademik` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kegiatan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `semester` enum('Ganjil','Genap') COLLATE utf8mb4_general_ci NOT NULL,
  `tahun_ajaran` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Published','Draft') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Published',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifikasi` (
  `id_notif` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `judul` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `pesan` text COLLATE utf8mb4_general_ci,
  `jenis` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_notif`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifikasi_log` (
  `id_notifikasi` int unsigned NOT NULL AUTO_INCREMENT,
  `id_siswa` int unsigned NOT NULL,
  `tipe` enum('absensi','nilai') COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_tujuan` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pesan` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('terkirim','gagal_kirim','gagal_no_hp') COLLATE utf8mb4_general_ci NOT NULL,
  `response` text COLLATE utf8mb4_general_ci,
  `ref_key` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_notifikasi`),
  KEY `id_siswa` (`id_siswa`),
  KEY `ref_key` (`ref_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisasi` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `status` enum('Published','Draft') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Published',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `partner` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Published','Draft') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Published',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pendaftar_ppdb` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_general_ci NOT NULL,
  `asal_sekolah` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jurusan_pilihan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tahun_ajaran` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `status` enum('Menunggu','Diterima','Ditolak') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Menunggu',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan_ppdb` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('buka','tutup') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'tutup',
  `pesan_tutup` text COLLATE utf8mb4_general_ci,
  `tahun_ajaran` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan_sekolah` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_sekolah` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `favicon` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tahun_ajaran_aktif_id` int unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengumuman` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `isi` text COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_publish` date NOT NULL,
  `status` enum('Published','Draft') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Draft',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesan_kontak` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `subjek` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pesan` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Belum Dibaca','Sudah Dibaca') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Belum Dibaca',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pkl_jurnal` (
  `id_jurnal` int unsigned NOT NULL AUTO_INCREMENT,
  `id_penempatan` int unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `kegiatan` text COLLATE utf8mb4_general_ci NOT NULL,
  `kendala` text COLLATE utf8mb4_general_ci,
  `status_validasi` enum('Menunggu','Disetujui','Ditolak') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Menunggu',
  `catatan_pembimbing` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_jurnal`),
  UNIQUE KEY `id_penempatan_tanggal` (`id_penempatan`,`tanggal`),
  KEY `id_penempatan` (`id_penempatan`),
  CONSTRAINT `pkl_jurnal_id_penempatan_foreign` FOREIGN KEY (`id_penempatan`) REFERENCES `pkl_penempatan` (`id_penempatan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pkl_penempatan` (
  `id_penempatan` int unsigned NOT NULL AUTO_INCREMENT,
  `id_siswa` int unsigned NOT NULL,
  `id_guru_pembimbing` int unsigned DEFAULT NULL,
  `nama_perusahaan` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat_perusahaan` text COLLATE utf8mb4_general_ci,
  `nama_pembimbing_industri` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp_pembimbing_industri` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('Aktif','Selesai','Dibatalkan') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Aktif',
  `catatan` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_penempatan`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_guru_pembimbing` (`id_guru_pembimbing`),
  CONSTRAINT `pkl_penempatan_id_guru_pembimbing_foreign` FOREIGN KEY (`id_guru_pembimbing`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `pkl_penempatan_id_siswa_foreign` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prestasi` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tim` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tingkat` enum('Kabupaten','Provinsi','Nasional','Internasional') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Kabupaten',
  `foto` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Published','Draft') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Published',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profil_konten` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `jenis` enum('sejarah','visi_misi','kepala_sekolah') COLLATE utf8mb4_general_ci NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `konten` text COLLATE utf8mb4_general_ci,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis` (`jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_role` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_role`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siswa` (
  `id_siswa` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `nis` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nisn` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `jurusan_id` int unsigned DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tempat_lahir` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci NOT NULL,
  `agama` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `nama_ayah` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_ibu` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan_ortu` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp_ortu` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Aktif','Lulus','Pindah','Keluar') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Aktif',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `nis` (`nis`),
  UNIQUE KEY `nisn` (`nisn`),
  KEY `siswa_jurusan_id_foreign` (`jurusan_id`),
  KEY `siswa_user_id_foreign` (`user_id`),
  CONSTRAINT `siswa_jurusan_id_foreign` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `siswa_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unduhan` (
  `id_unduhan` int unsigned NOT NULL AUTO_INCREMENT,
  `kategori` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_file_asli` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nama file asli sebelum di-random, dipakai saat download',
  `ukuran_file` int unsigned DEFAULT NULL COMMENT 'Ukuran file dalam byte',
  `ekstensi` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jumlah_unduh` int unsigned NOT NULL DEFAULT '0',
  `status` enum('Draft','Published') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Draft',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_unduhan`),
  KEY `kategori` (`kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int unsigned NOT NULL,
  `is_superadmin` tinyint(1) NOT NULL DEFAULT '0',
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `must_change_password` tinyint(1) DEFAULT '1',
  `login_attempts` int DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Aktif',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id_role`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4471 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

