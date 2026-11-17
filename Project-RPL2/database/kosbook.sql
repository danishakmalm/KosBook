-- ==============================================================================
-- SISTEM BOOKING KAMAR KOS (KOSBOOK)
-- File: database/kosbook.sql
-- Deskripsi: Skema Database Relasional, Index, Foreign Keys, & Dummy Data
-- ==============================================================================

-- 1. CREATE DATABASE
CREATE DATABASE IF NOT EXISTS `kosbook_db`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `kosbook_db`;

-- Nonaktifkan pengecekan foreign key sementara agar DROP TABLE berjalan bersih
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `booking`;
DROP TABLE IF EXISTS `kamar`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- 2. CREATE TABLE: users
-- Menyimpan data akun pengguna dengan peran 'admin' dan 'user' (calon penghuni)
-- ==============================================================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Primary Key
    CONSTRAINT `pk_users` PRIMARY KEY (`id`),

    -- Index & Unique Constraints
    CONSTRAINT `uq_users_email` UNIQUE (`email`),
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- 3. CREATE TABLE: kamar
-- Menyimpan data kamar kos, nomor kamar, harga, fasilitas, foto, dan status
-- ==============================================================================
CREATE TABLE `kamar` (
    `id` INT AUTO_INCREMENT,
    `nomor_kamar` VARCHAR(50) NOT NULL,
    `harga` DECIMAL(12, 2) NOT NULL,
    `fasilitas` TEXT NULL,
    `deskripsi` TEXT NULL,
    `foto` VARCHAR(255) DEFAULT 'default_kamar.jpg',
    `status` ENUM('tersedia', 'booking', 'terisi') NOT NULL DEFAULT 'tersedia',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Primary Key
    CONSTRAINT `pk_kamar` PRIMARY KEY (`id`),

    -- Index & Unique Constraints
    CONSTRAINT `uq_kamar_nomor` UNIQUE (`nomor_kamar`),
    INDEX `idx_kamar_status` (`status`),
    INDEX `idx_kamar_harga` (`harga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- 4. CREATE TABLE: booking
-- Menyimpan data transaksi reservasi kamar oleh user kepada pihak kos
-- ==============================================================================
CREATE TABLE `booking` (
    `id` INT AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `kamar_id` INT NOT NULL,
    `tanggal_booking` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `tanggal_mulai` DATE NOT NULL,
    `lama_sewa` INT NOT NULL DEFAULT 1, -- durasi sewa dalam hitungan bulan
    `total_harga` DECIMAL(12, 2) NOT NULL,
    `status` ENUM('menunggu', 'dikonfirmasi', 'ditolak', 'selesai') NOT NULL DEFAULT 'menunggu',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Primary Key
    CONSTRAINT `pk_booking` PRIMARY KEY (`id`),

    -- Foreign Keys
    CONSTRAINT `fk_booking_user` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_booking_kamar` 
        FOREIGN KEY (`kamar_id`) REFERENCES `kamar` (`id`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,

    -- Indexes untuk optimasi query pencarian dan pelaporan
    INDEX `idx_booking_user_id` (`user_id`),
    INDEX `idx_booking_kamar_id` (`kamar_id`),
    INDEX `idx_booking_status` (`status`),
    INDEX `idx_booking_tanggal` (`tanggal_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- 5. DUMMY DATA SEEDING
-- Catatan Keamanan: Semua password di-hash menggunakan password_hash() PHP (Bcrypt).
-- Password asli:
--   - Semua akun admin: 'admin123'
--   - Semua akun user : 'user123'
-- ==============================================================================

-- A. Data Users (2 Admin, 3 Calon Penghuni / User)
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
-- Admin
(1, 'Administrator KosBook', 'admin@kosbook.com', '$2y$10$KBdrVDhsFOvXj65v1G9bx./HJah2LhVsdCK.bYmaXOPds2lSOe8LK', 'admin', NOW()),
(2, 'Ibu Hj. Maryam (Pemilik Kos)', 'pemilik@kosbook.com', '$2y$10$KBdrVDhsFOvXj65v1G9bx./HJah2LhVsdCK.bYmaXOPds2lSOe8LK', 'admin', NOW()),

-- User / Calon Penghuni
(3, 'Ahmad Pratama', 'ahmad@gmail.com', '$2y$10$QywtsQZrwbcb.gc1CztmnOhG2axURVUaQRePp2b8ErVf06u4te5Cy', 'user', NOW()),
(4, 'Siti Nurhaliza', 'siti@gmail.com', '$2y$10$QywtsQZrwbcb.gc1CztmnOhG2axURVUaQRePp2b8ErVf06u4te5Cy', 'user', NOW()),
(5, 'Budi Santoso', 'budi@gmail.com', '$2y$10$QywtsQZrwbcb.gc1CztmnOhG2axURVUaQRePp2b8ErVf06u4te5Cy', 'user', NOW());


-- B. Data Kamar Kos (6 Kamar dengan variasi status & tipe)
INSERT INTO `kamar` (`id`, `nomor_kamar`, `harga`, `fasilitas`, `deskripsi`, `foto`, `status`, `created_at`) VALUES
(1, 'Kamar A-101', 1250000.00, 'AC Dingin, Kamar Mandi Dalam, Kasur Springbed, Meja Belajar, Lemari Pakaian, WiFi 50 Mbps', 'Kamar lantai 1 dengan ventilasi udara segar dan pencahayaan alami.', 'kamar_a101.jpg', 'tersedia', NOW()),
(2, 'Kamar A-102', 1250000.00, 'AC Dingin, Kamar Mandi Dalam, Kasur Springbed, Meja Belajar, Lemari Pakaian, WiFi 50 Mbps', 'Kamar lantai 1 berdampingan dengan taman kos yang asri.', 'kamar_a102.jpg', 'booking', NOW()),
(3, 'Kamar B-201', 850000.00, 'Kipas Angin, Kamar Mandi Luar Bersih, Kasur Busa Super, Meja Belajar, WiFi Gratis', 'Kamar lantai 2 hemat dan tenang, cocok untuk mahasiswa yang butuh suasana hening.', 'kamar_b201.jpg', 'tersedia', NOW()),
(4, 'Kamar B-202', 850000.00, 'Kipas Angin, Kamar Mandi Luar Bersih, Kasur Busa Super, Meja Belajar, WiFi Gratis', 'Kamar lantai 2 menghadap pemandangan kota, dekat dengan akses jemuran.', 'kamar_b202.jpg', 'terisi', NOW()),
(5, 'Kamar VIP-01', 1650000.00, 'AC Daikin, Water Heater, Kamar Mandi Dalam, Smart TV 32 Inch, Kulkas Mini, Kasur Queen, Akses 24 Jam', 'Kamar VIP mewah standar hotel dengan fasilitas super lengkap dan parkir motor terlindung.', 'kamar_vip01.jpg', 'tersedia', NOW()),
(6, 'Kamar VIP-02', 1650000.00, 'AC Daikin, Water Heater, Kamar Mandi Dalam, Smart TV 32 Inch, Kulkas Mini, Kasur Queen, Akses 24 Jam', 'Kamar VIP lantai atas dengan balkon pribadi untuk bersantai.', 'kamar_vip02.jpg', 'terisi', NOW());


-- C. Data Dummy Booking (3 Transaksi mewakili relasi users dan kamar)
INSERT INTO `booking` (`id`, `user_id`, `kamar_id`, `tanggal_booking`, `tanggal_mulai`, `lama_sewa`, `total_harga`, `status`, `created_at`) VALUES
-- Ahmad Pratama memesan Kamar A-102 (status kamar saat ini 'booking', status booking 'menunggu')
(1, 3, 2, '2026-10-21 09:15:00', '2026-11-01', 3, 3750000.00, 'menunggu', NOW()),

-- Siti Nurhaliza memesan Kamar B-202 (status booking 'dikonfirmasi', status kamar 'terisi')
(2, 4, 4, '2026-10-15 14:30:00', '2026-10-18', 6, 5100000.00, 'dikonfirmasi', NOW()),

-- Budi Santoso pernah memesan Kamar VIP-02 (status booking 'selesai')
(3, 5, 6, '2026-08-01 10:00:00', '2026-08-05', 1, 1650000.00, 'selesai', NOW());
