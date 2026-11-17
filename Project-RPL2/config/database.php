<?php
/**
 * Koneksi Database menggunakan PDO
 */

require_once __DIR__ . '/config.php';

class DatabaseConnection {
    private static ?PDO $instance = null;

    public static function getConnection(): ?PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Jangan hentikan seluruh web jika DB belum dibuat saat inisialisasi tahap awal
                error_log("Database Connection Error: " . $e->getMessage());
                return null;
            }
        }

        return self::$instance;
    }
}
