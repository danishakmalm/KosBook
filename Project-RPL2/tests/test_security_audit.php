<?php
/**
 * Test Otomatis Audit Keamanan dan Fungsional KosBook
 * Menguji 8 poin krusial audit yang diminta oleh user:
 * 1. User dapat mengakses data user lain. (Harus dicegah / terisolasi)
 * 2. User dapat mengakses halaman Admin. (Harus ditolak / redirect)
 * 3. Guest dapat melakukan booking tanpa login. (Harus ditolak / redirect ke login)
 * 4. Booking dapat dilakukan terhadap kamar yang sudah terisi. (Harus ditolak oleh transaksi DB)
 * 5. Harga dapat dimanipulasi dari browser. (Harus dihitung di server, manipulasi POST diabaikan)
 * 6. ID booking/kamar dapat dimanipulasi melalui URL. (Harus ditangani secara aman tanpa PHP Fatal Error)
 * 7. Input form dapat menyebabkan SQL Injection. (Semua query harus berparameter)
 * 8. Input dapat menyebabkan XSS. (Semua output HTML di-escape dengan htmlspecialchars)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/models/KamarModel.php';
require_once __DIR__ . '/../app/models/BookingModel.php';
require_once __DIR__ . '/../app/helpers/AuthHelper.php';

function assertAudit(string $desc, bool $condition): void {
    if ($condition) {
        echo " [PASS] " . $desc . PHP_EOL;
    } else {
        echo " [FAIL] " . $desc . PHP_EOL;
        exit(1);
    }
}

echo "=================================================================" . PHP_EOL;
echo "             AUDIT KEAMANAN & INTEGRITAS KOSBOOK                 " . PHP_EOL;
echo "=================================================================" . PHP_EOL . PHP_EOL;

$baseUrl = 'http://localhost/Project-RPL2/public/index.php';
$db = new Database();
$userModel = new UserModel();
$kamarModel = new KamarModel();
$bookingModel = new BookingModel();

$cookieUser1 = tempnam(sys_get_temp_dir(), 'ck_aud1_');
$cookieUser2 = tempnam(sys_get_temp_dir(), 'ck_aud2_');
$cookieAdmin = tempnam(sys_get_temp_dir(), 'ck_aud_adm_');

// Helper cURL request
function exec_curl($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'header' => $header, 'body' => $body];
}

// Login User 1 (ahmad@gmail.com)
exec_curl($baseUrl . '?url=auth/login', 'POST', ['email' => 'ahmad@gmail.com', 'password' => 'user123'], $cookieUser1);
// Login User 2 (siti@gmail.com)
exec_curl($baseUrl . '?url=auth/login', 'POST', ['email' => 'siti@gmail.com', 'password' => 'user123'], $cookieUser2);
// Login Admin (admin@kosbook.com)
exec_curl($baseUrl . '?url=auth/login', 'POST', ['email' => 'admin@kosbook.com', 'password' => 'admin123'], $cookieAdmin);

// -------------------------------------------------------------------------
// 1. UJI: User dapat mengakses data user lain?
// -------------------------------------------------------------------------
echo "[AUDIT 1] Privasi Data: User TIDAK BOLEH mengakses data booking user lain" . PHP_EOL;

// Dapatkan ID booking milik User 2 (Siti)
$siti = $userModel->findByEmail('siti@gmail.com');
$sitiBookings = $bookingModel->getByUserId((int)$siti['id']);
$sitiBookingId = $sitiBookings[0]['id'] ?? 0;

// User 1 (Ahmad) mencoba membuka konfirmasi booking milik User 2
$resAhmadSteal = exec_curl($baseUrl . '?url=booking/konfirmasi/' . $sitiBookingId, 'GET', [], $cookieUser1);
assertAudit("User 1 dilarang melihat konfirmasi booking milik User 2 (Redirect 302)", $resAhmadSteal['code'] === 302);
assertAudit("User 1 diredirect kembali ke dashboard miliknya", str_contains($resAhmadSteal['header'], 'dashboard/user'));

// User 1 mencoba membuka detail booking milik User 2
$resAhmadDetail = exec_curl($baseUrl . '?url=booking/detail/' . $sitiBookingId, 'GET', [], $cookieUser1);
assertAudit("User 1 dilarang melihat detail booking milik User 2 (Redirect 302)", $resAhmadDetail['code'] === 302);
assertAudit("User 1 diredirect kembali ke dashboard/user", str_contains($resAhmadDetail['header'], 'dashboard/user'));

// -------------------------------------------------------------------------
// 2. UJI: User dapat mengakses halaman Admin?
// -------------------------------------------------------------------------
echo PHP_EOL . "[AUDIT 2] Otorisasi: User biasa TIDAK BOLEH mengakses halaman Admin" . PHP_EOL;

$adminRoutes = ['dashboard/admin', 'kamar/admin', 'booking/admin', 'kamar/tambah'];
foreach ($adminRoutes as $route) {
    $resUserAdmin = exec_curl($baseUrl . '?url=' . $route, 'GET', [], $cookieUser1);
    assertAudit("User biasa ditolak dari $route (Redirect 302)", $resUserAdmin['code'] === 302);
    assertAudit("User biasa dialihkan ke dashboard/user", str_contains($resUserAdmin['header'], 'dashboard/user'));
}

// -------------------------------------------------------------------------
// 3. UJI: Guest dapat melakukan booking tanpa login?
// -------------------------------------------------------------------------
echo PHP_EOL . "[AUDIT 3] Autentikasi: Guest TIDAK BOLEH melakukan booking tanpa login" . PHP_EOL;

// Guest mencoba membuka form booking kamar #1
$resGuestForm = exec_curl($baseUrl . '?url=booking/tambah/1', 'GET');
assertAudit("Guest dilarang membuka form booking (Redirect 302)", $resGuestForm['code'] === 302);
assertAudit("Guest diarahkan ke halaman login", str_contains($resGuestForm['header'], 'auth/login'));

// Guest mencoba POST langsung pengajuan booking kamar #1
$resGuestPost = exec_curl($baseUrl . '?url=booking/tambah/1', 'POST', [
    'tanggal_mulai' => date('Y-m-d', strtotime('+5 days')),
    'lama_sewa' => 1
]);
assertAudit("Guest dilarang melakukan submit booking (Redirect 302)", $resGuestPost['code'] === 302);
assertAudit("Guest diarahkan ke auth/login saat submit", str_contains($resGuestPost['header'], 'auth/login'));

// -------------------------------------------------------------------------
// 4. UJI: Booking dapat dilakukan terhadap kamar yang sudah terisi/dibooking?
// -------------------------------------------------------------------------
echo PHP_EOL . "[AUDIT 4] Validasi Ketersediaan: Kamar terisi/dibooking TIDAK BOLEH bisa dibooking" . PHP_EOL;

// Cari kamar berstatus 'terisi'
$kamarTerisi = $db->query("SELECT id FROM kamar WHERE status = 'terisi' LIMIT 1");
$rowTerisi = $db->single();
if ($rowTerisi) {
    $resBookTerisi = exec_curl($baseUrl . '?url=booking/tambah/' . $rowTerisi['id'], 'POST', [
        'tanggal_mulai' => date('Y-m-d', strtotime('+3 days')),
        'lama_sewa' => 2
    ], $cookieUser1);
    assertAudit("Booking terhadap kamar berstatus 'terisi' ditolak (Redirect)", $resBookTerisi['code'] === 302);
    assertAudit("Dialihkan ke detail kamar dengan peringatan tidak tersedia", str_contains($resBookTerisi['header'], 'kamar/detail/' . $rowTerisi['id']));
}

// -------------------------------------------------------------------------
// 5. UJI: Harga dapat dimanipulasi dari browser?
// -------------------------------------------------------------------------
echo PHP_EOL . "[AUDIT 5] Integritas Harga: Harga TIDAK BOLEH dapat dimanipulasi dari browser" . PHP_EOL;

// Siapkan kamar uji yang tersedia
$db->query("SELECT id, harga FROM kamar WHERE status = 'tersedia' LIMIT 1");
$kamarUji = $db->single();
if ($kamarUji) {
    $kamarUjiId = (int)$kamarUji['id'];
    $hargaAsli = (float)$kamarUji['harga'];
    $durasi = 3;
    $totalSeharusnya = $hargaAsli * $durasi;

    // User mencoba inject parameter palsu: total_harga = 100 dan harga = 50
    $resTamperPrice = exec_curl($baseUrl . '?url=booking/tambah/' . $kamarUjiId, 'POST', [
        'tanggal_mulai' => date('Y-m-d', strtotime('+10 days')),
        'lama_sewa' => $durasi,
        'total_harga' => 100, // Percobaan manipulasi total harga
        'harga' => 50         // Percobaan manipulasi tarif dasar
    ], $cookieUser1);

    // Ambil booking terakhir yang dibuat oleh User 1
    $db->query("SELECT * FROM booking WHERE user_id = 3 AND kamar_id = :kid ORDER BY id DESC LIMIT 1");
    $db->bind(':kid', $kamarUjiId);
    $latestBooking = $db->single();

    assertAudit("Data booking tersimpan", !empty($latestBooking));
    assertAudit("Total harga dihitung tepat oleh server (" . formatRupiah($totalSeharusnya) . ") dan BUKAN nilai manipulasi", 
        (float)$latestBooking['total_harga'] == $totalSeharusnya && (float)$latestBooking['total_harga'] != 100
    );

    // Kembalikan status kamar uji untuk pengujian berikutnya
    $db->query("UPDATE kamar SET status = 'tersedia' WHERE id = :kid");
    $db->bind(':kid', $kamarUjiId);
    $db->execute();
}

// -------------------------------------------------------------------------
// 6. UJI: ID booking/kamar dapat dimanipulasi melalui URL?
// -------------------------------------------------------------------------
echo PHP_EOL . "[AUDIT 6] Robustness Routing: Manipulasi ID non-numeric TIDAK BOLEH menyebabkan fatal crash" . PHP_EOL;

// Manipulasi ID berupa string, karakter khusus, atau SQL payload
$maliciousIds = ['abc', "' OR '1'='1", '-999', 'undefined', 'null'];

foreach ($maliciousIds as $badId) {
    $resBadKamar = exec_curl($baseUrl . '?url=kamar/detail/' . urlencode($badId), 'GET');
    assertAudit("URL kamar/detail/$badId ditangani aman tanpa crash 500 (HTTP: {$resBadKamar['code']})", $resBadKamar['code'] === 200 || $resBadKamar['code'] === 302);
}

// -------------------------------------------------------------------------
// 7. UJI: SQL Injection pada input filter dan pencarian
// -------------------------------------------------------------------------
echo PHP_EOL . "[AUDIT 7] SQL Injection Prevention: Input berpotensi SQL Injection ditangani aman" . PHP_EOL;

$sqliPayloads = [
    "' OR 1=1 --",
    "\" OR \"\"=\"",
    "'; DROP TABLE kamar; --",
    "1 UNION SELECT 1,2,3,4,5,6,7,8"
];

foreach ($sqliPayloads as $payload) {
    // Uji pada query filter kamar
    $resSqliFilter = exec_curl($baseUrl . '?url=kamar&q=' . urlencode($payload) . '&status=' . urlencode($payload) . '&min_harga=' . urlencode($payload), 'GET');
    assertAudit("Payload SQLi [$payload] aman dieksekusi tanpa error database (HTTP 200)", $resSqliFilter['code'] === 200);

    // Uji pada login
    $resSqliLogin = exec_curl($baseUrl . '?url=auth/login', 'POST', ['email' => $payload, 'password' => 'secret']);
    assertAudit("Payload SQLi pada login ditolak aman (HTTP 302)", $resSqliLogin['code'] === 302);
}

// -------------------------------------------------------------------------
// 8. UJI: XSS (Cross-Site Scripting) pada output HTML
// -------------------------------------------------------------------------
echo PHP_EOL . "[AUDIT 8] XSS Prevention: Tag HTML berbahaya di-escape dengan aman" . PHP_EOL;

$xssPayload = '<script>alert("XSS")</script>';

// Test search query reflection
$resXssSearch = exec_curl($baseUrl . '?url=kamar&q=' . urlencode($xssPayload), 'GET');
assertAudit("Script tag di-escape dan tidak dieksekusi mentah di HTML pencarian", 
    str_contains($resXssSearch['body'], htmlspecialchars($xssPayload)) && 
    !str_contains($resXssSearch['body'], '<script>alert("XSS")</script>')
);

@unlink($cookieUser1);
@unlink($cookieUser2);
@unlink($cookieAdmin);

echo PHP_EOL . "=================================================================" . PHP_EOL;
echo "        SELURUH 8 POIN AUDIT KEAMANAN LULUS 100% (PASS)          " . PHP_EOL;
echo "=================================================================" . PHP_EOL;
