<?php
/**
 * Test Otomatis Dashboard Admin KosBook
 * Menguji:
 * 1. Access Control:
 *    - Guest mengakses dashboard/admin dilarang (Redirect 302 ke auth/login)
 *    - Regular user mengakses dashboard/admin dilarang (Redirect 302 ke dashboard/user)
 *    - Admin dapat mengakses dashboard/admin setelah login (HTTP 200)
 * 2. 7 Statistik Lengkap:
 *    - Total Kamar
 *    - Kamar Tersedia
 *    - Kamar Booking
 *    - Kamar Terisi
 *    - Total User
 *    - Total Booking
 *    - Booking Menunggu
 * 3. Tabel "Booking Terbaru" dengan kolom:
 *    - User
 *    - Kamar
 *    - Tanggal Booking
 *    - Total
 *    - Status
 *    - Action
 * 4. Tabel "Kamar Terbaru":
 *    - Judul "Kamar Terbaru"
 *    - Kolom Kamar, Harga, Fasilitas, Status, Action
 * 5. Bootstrap 5 Cards dan Responsivitas (.table-responsive, grid responsif)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/models/KamarModel.php';
require_once __DIR__ . '/../app/models/BookingModel.php';

function assertTest(string $desc, bool $condition): void {
    if ($condition) {
        echo " [PASS] " . $desc . PHP_EOL;
    } else {
        echo " [FAIL] " . $desc . PHP_EOL;
        exit(1);
    }
}

echo "=== MEMULAI PENGUJIAN OTOMATIS DASHBOARD ADMIN KOSBOOK ===" . PHP_EOL . PHP_EOL;

$db = new Database();
$userModel = new UserModel();
$kamarModel = new KamarModel();
$bookingModel = new BookingModel();

// Ambil data untuk validasi
$rooms = $kamarModel->getAll();
$users = $userModel->getAllUsers();
$bookings = $bookingModel->getAllBookings();

$stats = [
    'total_kamar' => count($rooms),
    'kamar_tersedia' => count(array_filter($rooms, fn($r) => $r['status'] === 'tersedia')),
    'kamar_booking' => count(array_filter($rooms, fn($r) => $r['status'] === 'booking')),
    'kamar_terisi' => count(array_filter($rooms, fn($r) => $r['status'] === 'terisi')),
    'total_user' => count(array_filter($users, fn($u) => $u['role'] === 'user')),
    'total_booking' => count($bookings),
    'booking_menunggu' => count(array_filter($bookings, fn($b) => $b['status'] === 'menunggu')),
];

$recentRooms = array_slice($rooms, 0, 5);
$recentBookings = array_slice($bookings, 0, 5);

// =========================================================================
// TEST 1: Rendering View Dashboard Admin
// =========================================================================
echo "--- TEST 1: Pengujian Rendering View Dashboard Admin ---" . PHP_EOL;

ob_start();
$currentAdmin = ['id' => 1, 'nama' => 'Pengelola Kos Admin', 'email' => 'admin@kosbook.com', 'role' => 'admin'];
$flash = null;

require __DIR__ . '/../app/views/dashboard/admin.php';
$htmlAdmin = ob_get_clean();

// 1a. Cek 7 Statistik
assertTest("Menampilkan statistik 'Total Kamar'", str_contains($htmlAdmin, 'Total Kamar'));
assertTest("Menampilkan nilai Total Kamar ({$stats['total_kamar']})", str_contains($htmlAdmin, (string)$stats['total_kamar']));

assertTest("Menampilkan statistik 'Kamar Tersedia'", str_contains($htmlAdmin, 'Kamar Tersedia'));
assertTest("Menampilkan nilai Kamar Tersedia ({$stats['kamar_tersedia']})", str_contains($htmlAdmin, (string)$stats['kamar_tersedia']));

assertTest("Menampilkan statistik 'Kamar Booking'", str_contains($htmlAdmin, 'Kamar Booking'));
assertTest("Menampilkan nilai Kamar Booking ({$stats['kamar_booking']})", str_contains($htmlAdmin, (string)$stats['kamar_booking']));

assertTest("Menampilkan statistik 'Kamar Terisi'", str_contains($htmlAdmin, 'Kamar Terisi'));
assertTest("Menampilkan nilai Kamar Terisi ({$stats['kamar_terisi']})", str_contains($htmlAdmin, (string)$stats['kamar_terisi']));

assertTest("Menampilkan statistik 'Total User'", str_contains($htmlAdmin, 'Total User'));
assertTest("Menampilkan nilai Total User ({$stats['total_user']})", str_contains($htmlAdmin, (string)$stats['total_user']));

assertTest("Menampilkan statistik 'Total Booking'", str_contains($htmlAdmin, 'Total Booking'));
assertTest("Menampilkan nilai Total Booking ({$stats['total_booking']})", str_contains($htmlAdmin, (string)$stats['total_booking']));

assertTest("Menampilkan statistik 'Booking Menunggu'", str_contains($htmlAdmin, 'Booking Menunggu'));
assertTest("Menampilkan nilai Booking Menunggu ({$stats['booking_menunggu']})", str_contains($htmlAdmin, (string)$stats['booking_menunggu']));

// 1b. Cek Tabel "Booking Terbaru" dan Kolom-kolomnya
assertTest("Terdapat card tabel 'Booking Terbaru'", str_contains($htmlAdmin, 'Booking Terbaru'));
assertTest("Tabel Booking Terbaru memiliki kolom 'User'", str_contains($htmlAdmin, '>User</th>'));
assertTest("Tabel Booking Terbaru memiliki kolom 'Kamar'", str_contains($htmlAdmin, '>Kamar</th>'));
assertTest("Tabel Booking Terbaru memiliki kolom 'Tanggal Booking'", str_contains($htmlAdmin, '>Tanggal Booking</th>'));
assertTest("Tabel Booking Terbaru memiliki kolom 'Total'", str_contains($htmlAdmin, '>Total</th>'));
assertTest("Tabel Booking Terbaru memiliki kolom 'Status'", str_contains($htmlAdmin, '>Status</th>'));
assertTest("Tabel Booking Terbaru memiliki kolom 'Action'", str_contains($htmlAdmin, '>Action</th>'));

// 1c. Cek Tabel "Kamar Terbaru"
assertTest("Terdapat card tabel 'Kamar Terbaru'", str_contains($htmlAdmin, 'Kamar Terbaru'));
assertTest("Tabel Kamar Terbaru memiliki kolom 'Kamar'", str_contains($htmlAdmin, '>Kamar</th>'));
assertTest("Tabel Kamar Terbaru memiliki kolom 'Harga / Bulan'", str_contains($htmlAdmin, '>Harga / Bulan</th>'));
assertTest("Tabel Kamar Terbaru memiliki kolom 'Fasilitas'", str_contains($htmlAdmin, '>Fasilitas</th>'));
assertTest("Tabel Kamar Terbaru memiliki kolom 'Status'", str_contains($htmlAdmin, '>Status</th>'));

// 1d. Cek Bootstrap 5 Elements & Responsivitas
assertTest("Menggunakan Bootstrap 5 Card (.card)", str_contains($htmlAdmin, 'class="card'));
assertTest("Menggunakan Bootstrap 5 Table (.table)", str_contains($htmlAdmin, 'class="table'));
assertTest("Menggunakan Bootstrap responsive table wrapper (.table-responsive)", str_contains($htmlAdmin, 'table-responsive'));
assertTest("Menggunakan modal konfirmasi admin", str_contains($htmlAdmin, 'modal fade'));

// =========================================================================
// TEST 2: Pengujian Access Control Melalui HTTP cURL
// =========================================================================
echo PHP_EOL . "--- TEST 2: Pengujian Access Control Melalui HTTP cURL ---" . PHP_EOL;

$baseUrl = 'http://localhost/Project-RPL2/public/index.php';

// 2a. Guest mengakses dashboard/admin -> Harus diredirect (HTTP 302) ke auth/login
$ch = curl_init($baseUrl . '?url=dashboard/admin');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true
]);
$respGuest = curl_exec($ch);
$httpCodeGuest = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertTest("Guest mengakses dashboard/admin dilarang (HTTP 302)", $httpCodeGuest === 302);
assertTest("Guest diredirect ke auth/login", str_contains($respGuest, 'auth/login'));

// 2b. User biasa login, lalu mengakses dashboard/admin -> Ditolak (Redirect ke dashboard/user)
$cookieUser = tempnam(sys_get_temp_dir(), 'ck_u_');
$ch = curl_init($baseUrl . '?url=auth/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'email' => 'ahmad@gmail.com',
        'password' => 'user123'
    ]),
    CURLOPT_COOKIEJAR => $cookieUser,
    CURLOPT_COOKIEFILE => $cookieUser,
    CURLOPT_FOLLOWLOCATION => false
]);
curl_exec($ch);
curl_close($ch);

$ch = curl_init($baseUrl . '?url=dashboard/admin');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE => $cookieUser,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true
]);
$respUserAccess = curl_exec($ch);
$httpCodeUserAccess = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertTest("User biasa mengakses dashboard/admin dilarang (HTTP 302)", $httpCodeUserAccess === 302);
assertTest("User biasa diredirect ke dashboard/user", str_contains($respUserAccess, 'dashboard/user'));
@unlink($cookieUser);

// 2c. Admin login, lalu mengakses dashboard/admin -> Berhasil (HTTP 200)
$cookieAdmin = tempnam(sys_get_temp_dir(), 'ck_adm_');
$ch = curl_init($baseUrl . '?url=auth/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'email' => 'admin@kosbook.com',
        'password' => 'admin123'
    ]),
    CURLOPT_COOKIEJAR => $cookieAdmin,
    CURLOPT_COOKIEFILE => $cookieAdmin,
    CURLOPT_FOLLOWLOCATION => false
]);
curl_exec($ch);
curl_close($ch);

$ch = curl_init($baseUrl . '?url=dashboard/admin');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE => $cookieAdmin,
    CURLOPT_FOLLOWLOCATION => true
]);
$htmlAdminHttp = curl_exec($ch);
$httpCodeAdmin = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertTest("Admin berhasil mengakses dashboard/admin setelah login (HTTP 200)", $httpCodeAdmin === 200);
assertTest("Dashboard Admin memuat 'Total Kamar'", str_contains($htmlAdminHttp, 'Total Kamar'));
assertTest("Dashboard Admin memuat 'Kamar Tersedia'", str_contains($htmlAdminHttp, 'Kamar Tersedia'));
assertTest("Dashboard Admin memuat 'Kamar Booking'", str_contains($htmlAdminHttp, 'Kamar Booking'));
assertTest("Dashboard Admin memuat 'Kamar Terisi'", str_contains($htmlAdminHttp, 'Kamar Terisi'));
assertTest("Dashboard Admin memuat 'Total User'", str_contains($htmlAdminHttp, 'Total User'));
assertTest("Dashboard Admin memuat 'Total Booking'", str_contains($htmlAdminHttp, 'Total Booking'));
assertTest("Dashboard Admin memuat 'Booking Menunggu'", str_contains($htmlAdminHttp, 'Booking Menunggu'));
assertTest("Dashboard Admin memuat tabel 'Booking Terbaru'", str_contains($htmlAdminHttp, 'Booking Terbaru'));
assertTest("Dashboard Admin memuat tabel 'Kamar Terbaru'", str_contains($htmlAdminHttp, 'Kamar Terbaru'));
@unlink($cookieAdmin);

echo PHP_EOL . "=== SEMUA PENGUJIAN DASHBOARD ADMIN BERHASIL (100% PASS) ===" . PHP_EOL;
