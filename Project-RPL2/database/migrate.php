<?php
/**
 * CLI Migration & Database Seeder Helper
 * Jalankan file ini via terminal: php database/migrate.php
 */

require_once __DIR__ . '/../config/config.php';

echo "=== KOSBOOK DATABASE MIGRATION ===\n";
echo "Host: " . DB_HOST . ":" . DB_PORT . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n\n";

try {
    // 1. Hubungkan ke server MySQL tanpa memilih DB terlebih dahulu
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // 2. Baca file SQL
    $sqlFile = __DIR__ . '/kosbook.sql';
    if (!file_exists($sqlFile)) {
        die("Error: File kosbook.sql tidak ditemukan!\n");
    }

    $sql = file_get_contents($sqlFile);

    // 3. Eksekusi skrip SQL
    echo "Sedang mengeksekusi skema dan seeding data...\n";
    $pdo->exec($sql);

    echo "\n[BERHASIL] Database 'kosbook_db' dan tabel (users, kamar, booking) telah siap!\n";
    echo "Akun Default:\n";
    echo "  - Admin: admin@kosbook.com (Password: admin123)\n";
    echo "  - Admin: pemilik@kosbook.com (Password: admin123)\n";
    echo "  - User : ahmad@gmail.com (Password: user123)\n";
    echo "  - User : siti@gmail.com (Password: user123)\n";
    echo "  - User : budi@gmail.com (Password: user123)\n";

} catch (PDOException $e) {
    echo "\n[CATATAN]: Tidak dapat terhubung ke MySQL secara otomatis: " . $e->getMessage() . "\n";
    echo "Pastikan modul MySQL di XAMPP Control Panel sudah aktif (Running).\n";
    echo "Anda juga dapat mengimpor file 'database/kosbook.sql' langsung via phpMyAdmin (http://localhost/phpmyadmin).\n";
}
