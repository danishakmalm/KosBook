<?php
/**
 * Test Runner KosBook
 * Menjalankan seluruh skrip pengujian otomatis di folder tests/
 * Penggunaan: php tests/run_all.php
 */

echo "=================================================================\n";
echo "              KOSBOOK - MASTER TEST RUNNER                       \n";
echo "=================================================================\n\n";

$testFiles = [
    'test_auth.php'            => 'Sistem Autentikasi & RBAC',
    'test_crud_kamar.php'      => 'CRUD Data Kamar & Access Control',
    'test_booking.php'         => 'Fitur Booking & Database Transaction',
    'test_admin_booking.php'   => 'Admin Kelola Booking (Konfirmasi & Tolak)',
    'test_user_dashboard.php'  => 'Dashboard User & Isolasi Data',
    'test_admin_dashboard.php' => 'Dashboard Admin & 7 Metrik KPI',
    'test_kamar_pencarian.php' => 'Pencarian & Multi-Filter Kamar',
    'test_http_auth.php'       => 'HTTP Flow & Session Security',
    'test_security_audit.php'  => 'Audit Keamanan (SQLi, XSS, CSRF, URL Tampering)',
    'test_ai_config.php'       => 'Konfigurasi Gemini API & Fetch Models',
    'test_chatbot_ai.php'      => 'Chatbot AI (Fairy & Jarvis) Integration'
];

$passedCount = 0;
$totalCount = count($testFiles);

foreach ($testFiles as $file => $description) {
    echo "\n>>> Menjalankan: {$file} ({$description})...\n";
    $cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/' . $file);
    passthru($cmd, $exitCode);

    if ($exitCode === 0) {
        $passedCount++;
    } else {
        echo "[ERROR] Pengujian {$file} keluar dengan exit code: {$exitCode}\n";
    }
}

echo "\n=================================================================\n";
echo "HASIL AKHIR: {$passedCount} / {$totalCount} Suite Pengujian Berhasil!\n";
echo "=================================================================\n";

exit($passedCount === $totalCount ? 0 : 1);
