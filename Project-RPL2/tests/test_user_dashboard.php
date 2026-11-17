<?php
/**
 * Test Otomatis Dashboard User KosBook
 * Menguji:
 * 1. Proteksi akses: Guest harus diredirect ke login (302).
 * 2. Isolasi data: User hanya bisa melihat booking miliknya sendiri.
 * 3. User TIDAK BOLEH melihat booking milik user lain.
 * 4. Tampilan 4 Statistik: Total, Menunggu, Dikonfirmasi, Ditolak.
 * 5. Tampilan Informasi User.
 * 6. Tampilan Booking Aktif.
 * 7. Tampilan Booking yang Sedang Menunggu.
 * 8. Tampilan Tabel Riwayat Booking dengan kolom:
 *    ID | Kamar | Tanggal Booking | Mulai Sewa | Lama Sewa | Total | Status
 * 9. Badge status Bootstrap:
 *    Menunggu = warning, Dikonfirmasi = success, Ditolak = danger, Selesai = secondary.
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

echo "=== MEMULAI PENGUJIAN OTOMATIS DASHBOARD USER KOSBOOK ===" . PHP_EOL . PHP_EOL;

$db = new Database();
$userModel = new UserModel();
$kamarModel = new KamarModel();
$bookingModel = new BookingModel();

// 1. Dapatkan dua user biasa (misal Ahmad dan Siti)
$user1 = $userModel->findByEmail('ahmad@gmail.com');
$user2 = $userModel->findByEmail('siti@gmail.com');

if (!$user1 || !$user2) {
    echo "Menyiapkan test user jika belum ada..." . PHP_EOL;
    if (!$user1) {
        $userModel->register([
            'nama' => 'Ahmad Calon Penghuni',
            'email' => 'ahmad@gmail.com',
            'password' => 'user123',
            'role' => 'user'
        ]);
        $user1 = $userModel->findByEmail('ahmad@gmail.com');
    }
    if (!$user2) {
        $userModel->register([
            'nama' => 'Siti Calon Penghuni',
            'email' => 'siti@gmail.com',
            'password' => 'user123',
            'role' => 'user'
        ]);
        $user2 = $userModel->findByEmail('siti@gmail.com');
    }
}

echo "User 1: " . $user1['nama'] . " (ID: " . $user1['id'] . ")" . PHP_EOL;
echo "User 2: " . $user2['nama'] . " (ID: " . $user2['id'] . ")" . PHP_EOL . PHP_EOL;

// Pastikan masing-masing user punya set data booking yang bervariasi untuk pengujian
// Cek booking milik user 1 dan user 2
$bookingsUser1 = $bookingModel->getByUserId((int)$user1['id']);
$bookingsUser2 = $bookingModel->getByUserId((int)$user2['id']);

// Jika user 2 belum punya booking, buat 1 booking untuk user 2 agar kita bisa uji isolasi
if (empty($bookingsUser2)) {
    // Cari kamar yang tersedia atau buatkan
    $kamarTersedia = $db->query("SELECT id FROM kamar WHERE status = 'tersedia' LIMIT 1");
    $kamar = $db->single();
    if (!$kamar) {
        $kamarId = $kamarModel->create([
            'nomor_kamar' => 'TEST-ISO-' . time(),
            'harga' => 850000,
            'fasilitas' => 'AC, WiFi',
            'deskripsi' => 'Kamar uji isolasi',
            'status' => 'tersedia'
        ]);
    } else {
        $kamarId = (int)$kamar['id'];
    }

    $b2Id = $bookingModel->createBooking([
        'user_id' => (int)$user2['id'],
        'kamar_id' => $kamarId,
        'tanggal_mulai' => date('Y-m-d', strtotime('+3 days')),
        'lama_sewa' => 2
    ]);
    echo "Dibuat booking uji untuk User 2 (ID Booking: $b2Id)" . PHP_EOL;
    $bookingsUser2 = $bookingModel->getByUserId((int)$user2['id']);
}

// =========================================================================
// TEST 1: Verifikasi Model Query Isolation
// =========================================================================
echo "--- TEST 1: Isolasi Data Booking pada Model ---" . PHP_EOL;
$b1List = $bookingModel->getByUserId((int)$user1['id']);
$b2List = $bookingModel->getByUserId((int)$user2['id']);

$b1Ids = array_column($b1List, 'id');
$b2Ids = array_column($b2List, 'id');

// Tidak boleh ada irisan booking antara User 1 dan User 2
$intersection = array_intersect($b1Ids, $b2Ids);
assertTest("Booking milik User 1 dan User 2 saling terisolasi (tidak ada irisan ID)", empty($intersection));

foreach ($b1List as $b) {
    assertTest("Semua data booking User 1 memiliki user_id = {$user1['id']}", (int)$b['user_id'] === (int)$user1['id']);
}
foreach ($b2List as $b) {
    assertTest("Semua data booking User 2 memiliki user_id = {$user2['id']}", (int)$b['user_id'] === (int)$user2['id']);
}

// =========================================================================
// TEST 2: Simulasi View Rendering untuk Dashboard User 1
// =========================================================================
echo PHP_EOL . "--- TEST 2: Validasi Rendering Dashboard User 1 ---" . PHP_EOL;

// Hitung statistik untuk User 1
$stats1 = [
    'total' => count($b1List),
    'menunggu' => count(array_filter($b1List, fn($b) => $b['status'] === 'menunggu')),
    'dikonfirmasi' => count(array_filter($b1List, fn($b) => $b['status'] === 'dikonfirmasi')),
    'ditolak' => count(array_filter($b1List, fn($b) => $b['status'] === 'ditolak')),
    'selesai' => count(array_filter($b1List, fn($b) => $b['status'] === 'selesai')),
];

$activeBookings1 = array_values(array_filter($b1List, fn($b) => $b['status'] === 'dikonfirmasi'));
$pendingBookings1 = array_values(array_filter($b1List, fn($b) => $b['status'] === 'menunggu'));

// Render view ke output buffer
ob_start();
$user = $user1;
$stats = $stats1;
$bookings = $b1List;
$activeBookings = $activeBookings1;
$pendingBookings = $pendingBookings1;
$flash = null;

require __DIR__ . '/../app/views/dashboard/user.php';
$htmlUser1 = ob_get_clean();

// 2a. Cek Informasi User
assertTest("Tampilan memuat nama user: " . $user1['nama'], str_contains($htmlUser1, htmlspecialchars($user1['nama'])));
assertTest("Tampilan memuat email user: " . $user1['email'], str_contains($htmlUser1, htmlspecialchars($user1['email'])));
assertTest("Tampilan memuat ID User", str_contains($htmlUser1, "ID User: #" . $user1['id']));

// 2b. Cek 4 Metrik Statistik Sederhana
assertTest("Tampilan memuat judul metrik 'Total Booking'", str_contains($htmlUser1, "Total Booking"));
assertTest("Tampilan memuat angka Total Booking ({$stats1['total']})", str_contains($htmlUser1, (string)$stats1['total']));
assertTest("Tampilan memuat judul metrik 'Booking Menunggu'", str_contains($htmlUser1, "Booking Menunggu"));
assertTest("Tampilan memuat angka Booking Menunggu ({$stats1['menunggu']})", str_contains($htmlUser1, (string)$stats1['menunggu']));
assertTest("Tampilan memuat judul metrik 'Booking Dikonfirmasi'", str_contains($htmlUser1, "Booking Dikonfirmasi"));
assertTest("Tampilan memuat angka Booking Dikonfirmasi ({$stats1['dikonfirmasi']})", str_contains($htmlUser1, (string)$stats1['dikonfirmasi']));
assertTest("Tampilan memuat judul metrik 'Booking Ditolak'", str_contains($htmlUser1, "Booking Ditolak"));
assertTest("Tampilan memuat angka Booking Ditolak ({$stats1['ditolak']})", str_contains($htmlUser1, (string)$stats1['ditolak']));

// 2c. Cek Bagian Booking Aktif
assertTest("Tampilan memuat bagian 'Booking Aktif'", str_contains($htmlUser1, "Booking Aktif"));

// 2d. Cek Bagian Booking yang Sedang Menunggu
assertTest("Tampilan memuat bagian 'Booking yang Sedang Menunggu'", str_contains($htmlUser1, "Booking yang Sedang Menunggu"));

// 2e. Cek Bagian Riwayat Booking & Kolom Tabel
assertTest("Tampilan memuat bagian 'Riwayat Booking'", str_contains($htmlUser1, "Riwayat Booking"));
assertTest("Tabel memiliki kolom ID", str_contains($htmlUser1, ">ID</th>"));
assertTest("Tabel memiliki kolom Kamar", str_contains($htmlUser1, ">Kamar</th>"));
assertTest("Tabel memiliki kolom Tanggal Booking", str_contains($htmlUser1, ">Tanggal Booking</th>"));
assertTest("Tabel memiliki kolom Mulai Sewa", str_contains($htmlUser1, ">Mulai Sewa</th>"));
assertTest("Tabel memiliki kolom Lama Sewa", str_contains($htmlUser1, ">Lama Sewa</th>"));
assertTest("Tabel memiliki kolom Total", str_contains($htmlUser1, ">Total</th>"));
assertTest("Tabel memiliki kolom Status", str_contains($htmlUser1, ">Status</th>"));

// 2f. Cek Badge Bootstrap untuk Status (Menunggu, Dikonfirmasi, Ditolak, Selesai)
ob_start();
$sampleBookings = [
    ['id' => 991, 'user_id' => $user1['id'], 'kamar_id' => 101, 'nomor_kamar' => 'A-01', 'tanggal_booking' => '2026-10-24 10:00:00', 'tanggal_mulai' => '2026-11-01', 'lama_sewa' => 1, 'total_harga' => 1000000, 'status' => 'menunggu', 'kamar_foto' => 'default_kamar.jpg', 'kamar_fasilitas' => 'AC'],
    ['id' => 992, 'user_id' => $user1['id'], 'kamar_id' => 102, 'nomor_kamar' => 'A-02', 'tanggal_booking' => '2026-10-24 10:00:00', 'tanggal_mulai' => '2026-11-01', 'lama_sewa' => 3, 'total_harga' => 3000000, 'status' => 'dikonfirmasi', 'kamar_foto' => 'default_kamar.jpg', 'kamar_fasilitas' => 'AC'],
    ['id' => 993, 'user_id' => $user1['id'], 'kamar_id' => 103, 'nomor_kamar' => 'A-03', 'tanggal_booking' => '2026-10-24 10:00:00', 'tanggal_mulai' => '2026-11-01', 'lama_sewa' => 6, 'total_harga' => 6000000, 'status' => 'ditolak', 'kamar_foto' => 'default_kamar.jpg', 'kamar_fasilitas' => 'AC'],
    ['id' => 994, 'user_id' => $user1['id'], 'kamar_id' => 104, 'nomor_kamar' => 'A-04', 'tanggal_booking' => '2026-10-24 10:00:00', 'tanggal_mulai' => '2026-11-01', 'lama_sewa' => 12, 'total_harga' => 12000000, 'status' => 'selesai', 'kamar_foto' => 'default_kamar.jpg', 'kamar_fasilitas' => 'AC']
];
$user = $user1;
$stats = ['total' => 4, 'menunggu' => 1, 'dikonfirmasi' => 1, 'ditolak' => 1, 'selesai' => 1];
$bookings = $sampleBookings;
$activeBookings = [$sampleBookings[1]];
$pendingBookings = [$sampleBookings[0]];
$flash = null;
require __DIR__ . '/../app/views/dashboard/user.php';
$htmlBadgeTest = ob_get_clean();

assertTest("Badge status 'Menunggu' menggunakan badge warning (bg-warning text-dark)", str_contains($htmlBadgeTest, 'bg-warning text-dark') && str_contains($htmlBadgeTest, 'Menunggu'));
assertTest("Badge status 'Dikonfirmasi' menggunakan badge success (bg-success text-white)", str_contains($htmlBadgeTest, 'bg-success text-white') && str_contains($htmlBadgeTest, 'Dikonfirmasi'));
assertTest("Badge status 'Ditolak' menggunakan badge danger (bg-danger text-white)", str_contains($htmlBadgeTest, 'bg-danger text-white') && str_contains($htmlBadgeTest, 'Ditolak'));
assertTest("Badge status 'Selesai' menggunakan badge secondary (bg-secondary text-white)", str_contains($htmlBadgeTest, 'bg-secondary text-white') && str_contains($htmlBadgeTest, 'Selesai'));

// =========================================================================
// TEST 3: Verifikasi Isolasi HTML antar User
// =========================================================================
echo PHP_EOL . "--- TEST 3: Verifikasi Isolasi Data di HTML Dashboard ---" . PHP_EOL;

// Pastikan User 1 TIDAK melihat ID Booking milik User 2
foreach ($b2Ids as $b2Id) {
    assertTest("Dashboard User 1 TIDAK mengandung booking #BK-00$b2Id milik User 2", !str_contains($htmlUser1, '#BK-00' . $b2Id));
    assertTest("Dashboard User 1 TIDAK mengandung link konfirmasi booking #$b2Id milik User 2", !str_contains($htmlUser1, 'booking/konfirmasi/' . $b2Id));
}

// Render HTML untuk User 2
ob_start();
$user = $user2;
$stats = [
    'total' => count($b2List),
    'menunggu' => count(array_filter($b2List, fn($b) => $b['status'] === 'menunggu')),
    'dikonfirmasi' => count(array_filter($b2List, fn($b) => $b['status'] === 'dikonfirmasi')),
    'ditolak' => count(array_filter($b2List, fn($b) => $b['status'] === 'ditolak')),
    'selesai' => count(array_filter($b2List, fn($b) => $b['status'] === 'selesai')),
];
$bookings = $b2List;
$activeBookings = array_values(array_filter($b2List, fn($b) => $b['status'] === 'dikonfirmasi'));
$pendingBookings = array_values(array_filter($b2List, fn($b) => $b['status'] === 'menunggu'));
$flash = null;

require __DIR__ . '/../app/views/dashboard/user.php';
$htmlUser2 = ob_get_clean();

// Pastikan User 2 TIDAK melihat ID Booking milik User 1
foreach ($b1Ids as $b1Id) {
    assertTest("Dashboard User 2 TIDAK mengandung booking #BK-00$b1Id milik User 1", !str_contains($htmlUser2, '#BK-00' . $b1Id));
    assertTest("Dashboard User 2 TIDAK mengandung link konfirmasi booking #$b1Id milik User 1", !str_contains($htmlUser2, 'booking/konfirmasi/' . $b1Id));
}

// =========================================================================
// TEST 4: Verifikasi HTTP Flow / Access Control
// =========================================================================
echo PHP_EOL . "--- TEST 4: Verifikasi HTTP Flow Melalui cURL ---" . PHP_EOL;

$baseUrl = 'http://localhost/Project-RPL2/public/index.php';

// 4a. Guest mengakses dashboard/user -> harus diarahkan ke auth/login
$ch = curl_init($baseUrl . '?url=dashboard/user');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertTest("Guest mengakses dashboard/user diredirect (HTTP 302)", $httpCode === 302);
assertTest("Redirect mengarah ke halaman login", str_contains($response, 'auth/login'));

// 4b. Login sebagai User 1 lalu akses dashboard/user
$cookieJar1 = tempnam(sys_get_temp_dir(), 'ck1_');

$ch = curl_init($baseUrl . '?url=auth/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'email' => 'ahmad@gmail.com',
        'password' => 'user123'
    ]),
    CURLOPT_COOKIEJAR => $cookieJar1,
    CURLOPT_COOKIEFILE => $cookieJar1,
    CURLOPT_FOLLOWLOCATION => true
]);
$loginResp = curl_exec($ch);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

// Akses dashboard/user dengan cookie User 1
$ch = curl_init($baseUrl . '?url=dashboard/user');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE => $cookieJar1,
    CURLOPT_FOLLOWLOCATION => true
]);
$userDashboardHttp = curl_exec($ch);
$httpCodeUser = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertTest("User yang sudah login berhasil mengakses dashboard/user (HTTP 200)", $httpCodeUser === 200);
assertTest("Dashboard via HTTP menampilkan nama user Ahmad", str_contains($userDashboardHttp, 'Ahmad'));
assertTest("Dashboard via HTTP memuat tabel dengan kolom ID, Kamar, Tanggal Booking, Mulai Sewa, Lama Sewa, Total, Status", 
    str_contains($userDashboardHttp, '>ID</th>') &&
    str_contains($userDashboardHttp, '>Kamar</th>') &&
    str_contains($userDashboardHttp, '>Tanggal Booking</th>') &&
    str_contains($userDashboardHttp, '>Mulai Sewa</th>') &&
    str_contains($userDashboardHttp, '>Lama Sewa</th>') &&
    str_contains($userDashboardHttp, '>Total</th>') &&
    str_contains($userDashboardHttp, '>Status</th>')
);

// Pastikan via HTTP pun, booking milik User 2 tidak muncul di dashboard User 1
foreach ($b2Ids as $b2Id) {
    assertTest("HTTP Dashboard User 1 TIDAK mengandung booking #BK-00$b2Id milik User 2", !str_contains($userDashboardHttp, '#BK-00' . $b2Id));
    assertTest("HTTP Dashboard User 1 TIDAK mengandung link konfirmasi booking #$b2Id milik User 2", !str_contains($userDashboardHttp, 'booking/konfirmasi/' . $b2Id));
}

@unlink($cookieJar1);

echo PHP_EOL . "=== SEMUA PENGUJIAN DASHBOARD USER BERHASIL (100% PASS) ===" . PHP_EOL;
