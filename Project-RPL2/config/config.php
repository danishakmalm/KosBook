<?php
/**
 * Konfigurasi Dasar Aplikasi KosBook
 */

// Konstanta Aplikasi
define('APP_NAME', 'KosBook');
define('APP_VERSION', '1.0.0');

// Deteksi BASE_URL secara dinamis agar portabel (XAMPP / PHP Built-in Server / CLI)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Ambil direktori tempat script dijalankan
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dirName = trim(dirname($scriptName), '/.');
    
    if (!empty($dirName)) {
        define('BASE_URL', $protocol . $host . '/' . $dirName);
    } else {
        define('BASE_URL', $protocol . $host);
    }
}

// Muat variabel lingkungan dari file .env jika ada
if (file_exists(__DIR__ . '/../.env')) {
    $envLines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Konfigurasi Database MySQL
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'kosbook_db');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
define('DB_CHARSET', 'utf8mb4');

// Konfigurasi Google Gemini API
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: 'gemini-3.5-flash');
define('GEMINI_FALLBACK_MODEL', getenv('GEMINI_FALLBACK_MODEL') ?: 'gemini-3.5-flash-lite');


/**
 * Helper URL generator internal
 * Mendukung path route maupun query parameter (misal: 'kamar/admin?status=tersedia')
 */
if (!function_exists('url')) {
    function url(string $path = ''): string {
        $path = ltrim($path, '/');
        if (empty($path)) {
            return BASE_URL;
        }
        if (str_contains($path, '?')) {
            [$route, $query] = explode('?', $path, 2);
            return BASE_URL . '/index.php?url=' . $route . '&' . $query;
        }
        return BASE_URL . '/index.php?url=' . $path;
    }
}

/**
 * Helper format angka mata uang Rupiah
 */
if (!function_exists('formatRupiah')) {
    function formatRupiah($number): string {
        return 'Rp ' . number_format((float)$number, 0, ',', '.');
    }
}

// Muat Helper Autentikasi jika belum dimuat
if (file_exists(__DIR__ . '/../app/helpers/AuthHelper.php')) {
    require_once __DIR__ . '/../app/helpers/AuthHelper.php';
}

