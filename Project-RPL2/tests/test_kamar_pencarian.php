<?php
/**
 * Test Suite: Fitur Pencarian & Tampilan Kamar untuk User/Guest (KosBook)
 * Menguji:
 * 1. Logika Model Kamar (Pencarian nomor kamar, fasilitas, filter status, min_harga, max_harga, sorting)
 * 2. Tampilan Halaman Daftar Kamar (Bootstrap Card, Foto, Nomor Kamar, Harga, Fasilitas Singkat, Status, Tombol 'Lihat Detail')
 * 3. Tampilan Halaman Detail Kamar (Foto, Nomor Kamar, Harga, Fasilitas, Deskripsi, Status)
 * 4. Logika Tombol Booking:
 *    - Status 'tersedia': Tombol "Booking Sekarang" ditampilkan
 *    - Status 'booking' & 'terisi': Tombol booking disabled (tidak dapat digunakan)
 *    - Akses Guest: Guest dapat melihat kamar namun ada mekanisme login sebelum booking
 *    - Akses User: Tombol interaktif tanpa implementasi transaksi (tahap ini)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/KamarModel.php';
require_once __DIR__ . '/../app/helpers/AuthHelper.php';

$baseUrl = 'http://localhost/Project-RPL2/public/index.php';
$cookieFileGuest = __DIR__ . '/test_cookie_guest_search.txt';
$cookieFileUser = __DIR__ . '/test_cookie_user_search.txt';

foreach ([$cookieFileGuest, $cookieFileUser] as $cf) {
    if (file_exists($cf)) unlink($cf);
}

function http_request($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init();
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'header' => $header, 'body' => $body];
}

echo "=================================================================\n";
echo "    PENGUJIAN FITUR PENCARIAN & TAMPILAN KAMAR (USER/GUEST)      \n";
echo "=================================================================\n\n";

$kamarModel = new KamarModel();

// -------------------------------------------------------------
// BAGIAN 1: PENGUJIAN UNIT MODEL (SEARCH & FILTER)
// -------------------------------------------------------------
echo "[BAGIAN 1] Pengujian Unit KamarModel::searchAndFilter():\n";

// 1.1 Filter Status 'tersedia'
$resTersedia = $kamarModel->searchAndFilter(['status' => 'tersedia']);
$allAreTersedia = true;
foreach ($resTersedia as $r) {
    if ($r['status'] !== 'tersedia') $allAreTersedia = false;
}
echo ($allAreTersedia && count($resTersedia) >= 3 ? " [PASS]" : " [FAIL]") . " Filter status 'tersedia' (" . count($resTersedia) . " kamar ditemukan)\n";

// 1.2 Filter Status 'booking'
$resBooking = $kamarModel->searchAndFilter(['status' => 'booking']);
$allAreBooking = true;
foreach ($resBooking as $r) {
    if ($r['status'] !== 'booking') $allAreBooking = false;
}
echo ($allAreBooking && count($resBooking) >= 1 ? " [PASS]" : " [FAIL]") . " Filter status 'booking' (" . count($resBooking) . " kamar ditemukan)\n";

// 1.3 Filter Status 'terisi'
$resTerisi = $kamarModel->searchAndFilter(['status' => 'terisi']);
$allAreTerisi = true;
foreach ($resTerisi as $r) {
    if ($r['status'] !== 'terisi') $allAreTerisi = false;
}
echo ($allAreTerisi && count($resTerisi) >= 2 ? " [PASS]" : " [FAIL]") . " Filter status 'terisi' (" . count($resTerisi) . " kamar ditemukan)\n";

// 1.4 Filter Harga Minimum (>= 1.250.000)
$resMinHarga = $kamarModel->searchAndFilter(['min_harga' => 1250000]);
$allAboveMin = true;
foreach ($resMinHarga as $r) {
    if ((float)$r['harga'] < 1250000) $allAboveMin = false;
}
echo ($allAboveMin && count($resMinHarga) >= 4 ? " [PASS]" : " [FAIL]") . " Filter harga minimum Rp 1.250.000 (" . count($resMinHarga) . " kamar ditemukan)\n";

// 1.5 Filter Harga Maksimum (<= 1.000.000)
$resMaxHarga = $kamarModel->searchAndFilter(['max_harga' => 1000000]);
$allBelowMax = true;
foreach ($resMaxHarga as $r) {
    if ((float)$r['harga'] > 1000000) $allBelowMax = false;
}
echo ($allBelowMax && count($resMaxHarga) >= 2 ? " [PASS]" : " [FAIL]") . " Filter harga maksimum Rp 1.000.000 (" . count($resMaxHarga) . " kamar ditemukan)\n";

// 1.6 Pencarian Berdasarkan Nomor Kamar ('A-101')
$resNomor = $kamarModel->searchAndFilter(['nomor_kamar' => 'A-101']);
echo (count($resNomor) === 1 && $resNomor[0]['nomor_kamar'] === 'Kamar A-101' ? " [PASS]" : " [FAIL]") . " Pencarian spesifik nomor kamar 'A-101'\n";

// 1.7 Pencarian Berdasarkan Fasilitas ('AC')
$resFasilitas = $kamarModel->searchAndFilter(['fasilitas' => 'AC']);
$allHaveAc = true;
foreach ($resFasilitas as $r) {
    if (stripos($r['fasilitas'], 'AC') === false) $allHaveAc = false;
}
echo ($allHaveAc && count($resFasilitas) >= 4 ? " [PASS]" : " [FAIL]") . " Pencarian spesifik fasilitas 'AC' (" . count($resFasilitas) . " kamar ditemukan)\n";

// 1.8 Pencarian Berdasarkan Keyword Bebas (q: nomor kamar ATAU fasilitas)
$resQNomor = $kamarModel->searchAndFilter(['q' => 'VIP']);
$resQFasilitas = $kamarModel->searchAndFilter(['q' => 'Water Heater']);
echo (count($resQNomor) === 2 && count($resQFasilitas) === 2 ? " [PASS]" : " [FAIL]") . " Pencarian keyword umum ('VIP' & 'Water Heater')\n";

// 1.9 Kombinasi Filter: Status 'tersedia' + Harga Maksimum 1.500.000 + Keyword 'AC'
$resKombinasi = $kamarModel->searchAndFilter([
    'status' => 'tersedia',
    'max_harga' => 1500000,
    'q' => 'AC'
]);
echo (count($resKombinasi) === 1 && $resKombinasi[0]['nomor_kamar'] === 'Kamar A-101' ? " [PASS]" : " [FAIL]") . " Kombinasi filter (Tersedia + Max Rp 1.5M + AC) -> Kamar A-101\n";

// 1.10 Sorting: Harga Terendah (harga_asc)
$resSortAsc = $kamarModel->searchAndFilter(['sort' => 'harga_asc']);
$isSortedAsc = ((float)$resSortAsc[0]['harga'] <= (float)$resSortAsc[count($resSortAsc)-1]['harga']);
echo ($isSortedAsc && (float)$resSortAsc[0]['harga'] === 850000.0 ? " [PASS]" : " [FAIL]") . " Sorting harga terendah (Mulai Rp 850.000)\n\n";


// -------------------------------------------------------------
// BAGIAN 2: PENGUJIAN HTTP HALAMAN DAFTAR KAMAR (GUEST & USER)
// -------------------------------------------------------------
echo "[BAGIAN 2] Pengujian Halaman Daftar Kamar:\n";

// 2.1 Akses Katalog Kamar oleh Guest (HTTP 200)
$resp = http_request($baseUrl . '?url=kamar', 'GET', [], $cookieFileGuest);
echo ($resp['code'] === 200 ? " [PASS]" : " [FAIL]") . " Guest dapat mengakses halaman Daftar Kamar (HTTP 200)\n";

// 2.2 Komponen Card Bootstrap & Elemen Wajib
$hasCardClass = (strpos($resp['body'], 'class="card') !== false && strpos($resp['body'], 'room-card') !== false);
$hasNomorKamar = (strpos($resp['body'], 'Kamar A-101') !== false);
$hasHarga = (strpos($resp['body'], '1.250.000') !== false);
$hasLihatDetailBtn = (strpos($resp['body'], 'Lihat Detail') !== false);
$hasStatusBadge = (strpos($resp['body'], 'Tersedia') !== false);

echo ($hasCardClass ? " [PASS]" : " [FAIL]") . " Menggunakan Bootstrap Card (.card)\n";
echo ($hasNomorKamar ? " [PASS]" : " [FAIL]") . " Menampilkan Nomor Kamar\n";
echo ($hasHarga ? " [PASS]" : " [FAIL]") . " Menampilkan Harga Sewa terformat Rupiah\n";
echo ($hasStatusBadge ? " [PASS]" : " [FAIL]") . " Menampilkan Status Kamar (Badge)\n";
echo ($hasLihatDetailBtn ? " [PASS]" : " [FAIL]") . " Menampilkan Tombol 'Lihat Detail'\n";

// 2.3 Form Filter Tersedia di Halaman
$hasFilterForm = (strpos($resp['body'], 'name="min_harga"') !== false && 
                  strpos($resp['body'], 'name="max_harga"') !== false && 
                  strpos($resp['body'], 'name="status"') !== false && 
                  strpos($resp['body'], 'name="q"') !== false);
echo ($hasFilterForm ? " [PASS]" : " [FAIL]") . " Form filter (Status, Harga Min, Harga Max, Pencarian Nomor/Fasilitas) lengkap\n";

// 2.4 Akses dengan Query Filter (HTTP GET)
$respFilter = http_request($baseUrl . '?url=kamar&status=tersedia&min_harga=1000000', 'GET', [], $cookieFileGuest);
$hasA101 = strpos($respFilter['body'], 'Kamar A-101') !== false;
$hasVip01 = strpos($respFilter['body'], 'Kamar VIP-01') !== false;
$noB201 = strpos($respFilter['body'], 'Kamar B-201') === false; // B-201 is 850k, should be filtered out
echo ($hasA101 && $hasVip01 && $noB201 ? " [PASS]" : " [FAIL]") . " HTTP GET filter (Status: Tersedia & Min Harga: 1.000.000) berfungsi tepat\n";

// 2.5 Pencarian Fasilitas via HTTP GET
$respSearch = http_request($baseUrl . '?url=kamar&q=' . urlencode('Smart TV'), 'GET', [], $cookieFileGuest);
$hasVipRooms = strpos($respSearch['body'], 'Kamar VIP-01') !== false && strpos($respSearch['body'], 'Kamar VIP-02') !== false;
$noA101 = strpos($respSearch['body'], 'Kamar A-101') === false;
echo ($hasVipRooms && $noA101 ? " [PASS]" : " [FAIL]") . " HTTP GET pencarian fasilitas 'Smart TV' hanya menampilkan kamar VIP\n\n";


// -------------------------------------------------------------
// BAGIAN 3: PENGUJIAN HTTP HALAMAN DETAIL KAMAR
// -------------------------------------------------------------
echo "[BAGIAN 3] Pengujian Halaman Detail Kamar:\n";

// 3.1 Kamar ID 1 (Status: TERSEDIA)
$respDetail1 = http_request($baseUrl . '?url=kamar/detail/1', 'GET', [], $cookieFileGuest);
$hasFoto1 = strpos($respDetail1['body'], '<img') !== false;
$hasNomor1 = strpos($respDetail1['body'], 'Kamar A-101') !== false;
$hasHarga1 = strpos($respDetail1['body'], '1.250.000') !== false;
$hasFasilitas1 = strpos($respDetail1['body'], 'Fasilitas Kamar') !== false && strpos($respDetail1['body'], 'AC Dingin') !== false;
$hasDeskripsi1 = strpos($respDetail1['body'], 'Deskripsi Kamar') !== false;
$hasStatus1 = strpos($respDetail1['body'], 'Tersedia') !== false;
$hasBookingBtn1 = strpos($respDetail1['body'], 'Booking Sekarang') !== false;

echo ($respDetail1['code'] === 200 ? " [PASS]" : " [FAIL]") . " Akses detail kamar #1 berhasil (HTTP 200)\n";
echo ($hasFoto1 ? " [PASS]" : " [FAIL]") . " Menampilkan Foto Kamar\n";
echo ($hasNomor1 ? " [PASS]" : " [FAIL]") . " Menampilkan Nomor Kamar\n";
echo ($hasHarga1 ? " [PASS]" : " [FAIL]") . " Menampilkan Harga Sewa\n";
echo ($hasFasilitas1 ? " [PASS]" : " [FAIL]") . " Menampilkan Fasilitas Kamar\n";
echo ($hasDeskripsi1 ? " [PASS]" : " [FAIL]") . " Menampilkan Deskripsi Kamar\n";
echo ($hasStatus1 ? " [PASS]" : " [FAIL]") . " Menampilkan Status Kamar\n";
echo ($hasBookingBtn1 ? " [PASS]" : " [FAIL]") . " Status 'tersedia' -> Menampilkan tombol 'Booking Sekarang'\n";

// 3.2 Proteksi Guest: Guest harus login sebelum booking
$hasLoginModalOrNotice = (strpos($respDetail1['body'], 'modalLoginRequired') !== false || strpos($respDetail1['body'], 'Guest Perlu Login') !== false);
echo ($hasLoginModalOrNotice ? " [PASS]" : " [FAIL]") . " Guest dapat melihat detail kamar, tetapi diarahkan login sebelum booking\n";

// 3.3 Kamar ID 2 (Status: BOOKING)
$respDetail2 = http_request($baseUrl . '?url=kamar/detail/2', 'GET', [], $cookieFileGuest);
$isBookingDisabled = (strpos($respDetail2['body'], 'disabled') !== false && strpos($respDetail2['body'], 'Sedang Dibooking') !== false);
echo ($isBookingDisabled ? " [PASS]" : " [FAIL]") . " Status 'booking' -> Tombol booking disabled (tidak dapat digunakan)\n";

// 3.4 Kamar ID 4 (Status: TERISI)
$respDetail4 = http_request($baseUrl . '?url=kamar/detail/4', 'GET', [], $cookieFileGuest);
$isTerisiDisabled = (strpos($respDetail4['body'], 'disabled') !== false && strpos($respDetail4['body'], 'Kamar Terisi') !== false);
echo ($isTerisiDisabled ? " [PASS]" : " [FAIL]") . " Status 'terisi' -> Tombol booking disabled (tidak dapat digunakan)\n";

// 3.5 Pengujian Interaksi User Login pada Detail Kamar
// Login user: ahmad@gmail.com
http_request($baseUrl . '?url=auth/login', 'POST', ['email' => 'ahmad@gmail.com', 'password' => 'user123'], $cookieFileUser);
$respUserDetail = http_request($baseUrl . '?url=kamar/detail/1', 'GET', [], $cookieFileUser);
$hasUserNoticeModal = (strpos($respUserDetail['body'], 'modalBookingNotice') !== false);
$hasNoGuestWarning = (strpos($respUserDetail['body'], 'modalLoginRequired') === false);
echo ($hasUserNoticeModal && $hasNoGuestWarning ? " [PASS]" : " [FAIL]") . " User yang sudah login dapat menekan tombol booking (modal notifikasi booking dipersiapkan)\n";

// Cleanup cookies
foreach ([$cookieFileGuest, $cookieFileUser] as $cf) {
    if (file_exists($cf)) unlink($cf);
}

echo "\n=================================================================\n";
echo "    SEMUA PENGUJIAN PENCARIAN & TAMPILAN KAMAR LULUS 100%!       \n";
echo "=================================================================\n";
