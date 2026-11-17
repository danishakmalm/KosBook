<?php
/**
 * Entry Point Utama (Front Controller) - KosBook
 * Semua request masuk dan dialihkan melalui file ini.
 */

// 1. Muat Konfigurasi Dasar & Helper
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/AuthHelper.php';

// Inisialisasi sesi aplikasi secara aman
AuthHelper::init();

// 2. Muat Core Engine
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/App.php';

// 3. Jalankan Aplikasi
$app = new App();
