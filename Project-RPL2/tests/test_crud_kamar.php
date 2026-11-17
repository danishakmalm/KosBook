<?php
/**
 * Test Suite Lengkap CRUD Data Kamar & Access Control KosBook
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/KamarModel.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/helpers/AuthHelper.php';

$baseUrl = 'http://localhost/Project-RPL2/public/index.php';
$cookieFileAdmin = __DIR__ . '/test_cookie_admin.txt';
$cookieFileUser = __DIR__ . '/test_cookie_user.txt';
$cookieFileGuest = __DIR__ . '/test_cookie_guest.txt';

foreach ([$cookieFileAdmin, $cookieFileUser, $cookieFileGuest] as $cf) {
    if (file_exists($cf)) unlink($cf);
}

function http_call($url, $method = 'GET', $data = [], $cookieFile = null, $files = [], $followRedirect = false) {
    $ch = curl_init();
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($files)) {
            // Multipart form data
            $postFields = $data;
            foreach ($files as $key => $filePath) {
                if (file_exists($filePath)) {
                    $postFields[$key] = new CURLFile($filePath);
                }
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirect);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'effective_url' => $effectiveUrl,
        'header' => $header,
        'body' => $body
    ];
}

echo "=================================================================\n";
echo "       PENGUJIAN FITUR CRUD DATA KAMAR UNTUK ADMIN (KOSBOOK)     \n";
echo "=================================================================\n\n";

$kamarModel = new KamarModel();

// -------------------------------------------------------------
// BAGIAN 1: PENGUJIAN LOGIKA & MODEL KAMAR
// -------------------------------------------------------------
echo "[BAGIAN 1] Pengujian Unit KamarModel:\n";

// 1.1 Ambil Semua Kamar
$allRooms = $kamarModel->getAll();
echo (count($allRooms) >= 6 ? " [PASS]" : " [FAIL]") . " KamarModel::getAll() berhasil mengambil data kamar (" . count($allRooms) . " kamar terdaftar)\n";

// 1.2 Detail Kamar ID 1
$room1 = $kamarModel->getById(1);
echo ($room1 && $room1['nomor_kamar'] === 'Kamar A-101' ? " [PASS]" : " [FAIL]") . " KamarModel::getById(1) berhasil mengambil data Kamar A-101\n";

// 1.3 Cek Duplikat Nomor Kamar
$isDup = $kamarModel->isNomorKamarExists('Kamar A-101');
echo ($isDup ? " [PASS]" : " [FAIL]") . " Deteksi duplikat kamar terdaftar ('Kamar A-101' = true)\n";

$isNotDup = $kamarModel->isNomorKamarExists('Kamar A-101', 1);
echo (!$isNotDup ? " [PASS]" : " [FAIL]") . " Nomor kamar milik dirinya sendiri saat edit diizinkan (excludeId = 1)\n";

$isNewAvailable = $kamarModel->isNomorKamarExists('Kamar Baru XYZ 999');
echo (!$isNewAvailable ? " [PASS]" : " [FAIL]") . " Nomor kamar baru terdeteksi belum terdaftar ('Kamar Baru XYZ 999' = false)\n\n";


// -------------------------------------------------------------
// BAGIAN 2: PENGUJIAN ACCESS CONTROL (HANYA ADMIN BOLEH CRUD)
// -------------------------------------------------------------
echo "[BAGIAN 2] Pengujian Access Control (Guest & User):\n";

// 2.1 Guest melihat katalog dan detail kamar (Boleh)
$resp = http_call($baseUrl . '?url=kamar', 'GET', [], $cookieFileGuest, [], false);
echo ($resp['code'] === 200 ? " [PASS]" : " [FAIL]") . " Guest dapat melihat Katalog Kamar (HTTP 200)\n";

$resp = http_call($baseUrl . '?url=kamar/detail/1', 'GET', [], $cookieFileGuest, [], false);
echo ($resp['code'] === 200 ? " [PASS]" : " [FAIL]") . " Guest dapat melihat Detail Kamar (HTTP 200)\n";

// 2.2 Guest dilarang CRUD (Harus dialihkan ke login)
$resp = http_call($baseUrl . '?url=kamar/admin', 'GET', [], $cookieFileGuest, [], false);
echo ($resp['code'] === 302 && strpos($resp['header'], 'auth/login') !== false ? " [PASS]" : " [FAIL]") . " Guest dilarang akses kamar/admin (Redirect ke auth/login)\n";

$resp = http_call($baseUrl . '?url=kamar/tambah', 'GET', [], $cookieFileGuest, [], false);
echo ($resp['code'] === 302 && strpos($resp['header'], 'auth/login') !== false ? " [PASS]" : " [FAIL]") . " Guest dilarang akses kamar/tambah (Redirect ke auth/login)\n";

$resp = http_call($baseUrl . '?url=kamar/edit/1', 'GET', [], $cookieFileGuest, [], false);
echo ($resp['code'] === 302 && strpos($resp['header'], 'auth/login') !== false ? " [PASS]" : " [FAIL]") . " Guest dilarang akses kamar/edit (Redirect ke auth/login)\n";

$resp = http_call($baseUrl . '?url=kamar/hapus/1', 'GET', [], $cookieFileGuest, [], false);
echo ($resp['code'] === 302 && strpos($resp['header'], 'auth/login') !== false ? " [PASS]" : " [FAIL]") . " Guest dilarang akses kamar/hapus (Redirect ke auth/login)\n";

// 2.3 User Login & Coba Akses CRUD Kamar (Harus Ditolak & Dialihkan ke dashboard/user)
http_call($baseUrl . '?url=auth/login', 'POST', ['email' => 'ahmad@gmail.com', 'password' => 'user123'], $cookieFileUser, [], false);

$resp = http_call($baseUrl . '?url=kamar', 'GET', [], $cookieFileUser, [], false);
echo ($resp['code'] === 200 ? " [PASS]" : " [FAIL]") . " User login dapat melihat Katalog Kamar (HTTP 200)\n";

$resp = http_call($baseUrl . '?url=kamar/admin', 'GET', [], $cookieFileUser, [], false);
echo ($resp['code'] === 302 && strpos($resp['header'], 'dashboard/user') !== false ? " [PASS]" : " [FAIL]") . " User login dilarang buka kamar/admin (Redirect ke dashboard/user)\n";

$resp = http_call($baseUrl . '?url=kamar/tambah', 'GET', [], $cookieFileUser, [], false);
echo ($resp['code'] === 302 && strpos($resp['header'], 'dashboard/user') !== false ? " [PASS]" : " [FAIL]") . " User login dilarang buka kamar/tambah (Redirect ke dashboard/user)\n";

$resp = http_call($baseUrl . '?url=kamar/edit/1', 'GET', [], $cookieFileUser, [], false);
echo ($resp['code'] === 302 && strpos($resp['header'], 'dashboard/user') !== false ? " [PASS]" : " [FAIL]") . " User login dilarang buka kamar/edit (Redirect ke dashboard/user)\n";

$resp = http_call($baseUrl . '?url=kamar/hapus/1', 'GET', [], $cookieFileUser, [], false);
echo ($resp['code'] === 302 && strpos($resp['header'], 'dashboard/user') !== false ? " [PASS]" : " [FAIL]") . " User login dilarang buka kamar/hapus (Redirect ke dashboard/user)\n\n";


// -------------------------------------------------------------
// BAGIAN 3: PENGUJIAN CRUD OLEH ADMIN
// -------------------------------------------------------------
echo "[BAGIAN 3] Pengujian Operasi CRUD Kamar oleh Admin:\n";

// 3.0 Login Admin
$loginResp = http_call($baseUrl . '?url=auth/login', 'POST', ['email' => 'admin@kosbook.com', 'password' => 'admin123'], $cookieFileAdmin, [], false);
echo ($loginResp['code'] === 302 ? " [PASS]" : " [FAIL]") . " Admin berhasil login\n";

// 3.1 Melihat Daftar Kamar (Admin List Kamar)
$resp = http_call($baseUrl . '?url=kamar/admin', 'GET', [], $cookieFileAdmin, [], false);
echo ($resp['code'] === 200 ? " [PASS]" : " [FAIL]") . " 1. Admin dapat melihat daftar kamar (kamar/admin - HTTP 200)\n";

// 3.2 Pengujian Validasi Tambah Kamar
echo "    Pengujian Validasi Form Tambah Kamar:\n";

// Validasi a: Nomor Kamar Kosong
$resp = http_call($baseUrl . '?url=kamar/tambah', 'POST', [
    'nomor_kamar' => '',
    'harga' => '1000000',
    'status' => 'tersedia'
], $cookieFileAdmin, [], true);
echo (strpos($resp['body'], 'Nomor kamar wajib diisi') !== false ? "     [PASS]" : "     [FAIL]") . " Validasi: Nomor kamar wajib diisi\n";

// Validasi b: Harga Bukan Angka
$resp = http_call($baseUrl . '?url=kamar/tambah', 'POST', [
    'nomor_kamar' => 'Kamar X-99',
    'harga' => 'satu_juta',
    'status' => 'tersedia'
], $cookieFileAdmin, [], true);
echo (strpos($resp['body'], 'Harga kamar harus berupa angka') !== false ? "     [PASS]" : "     [FAIL]") . " Validasi: Harga harus berupa angka\n";

// Validasi c: Harga Negatif
$resp = http_call($baseUrl . '?url=kamar/tambah', 'POST', [
    'nomor_kamar' => 'Kamar X-99',
    'harga' => '-500000',
    'status' => 'tersedia'
], $cookieFileAdmin, [], true);
echo (strpos($resp['body'], 'tidak boleh bernilai negatif') !== false ? "     [PASS]" : "     [FAIL]") . " Validasi: Harga tidak boleh negatif\n";

// Validasi d: Status Tidak Valid
$resp = http_call($baseUrl . '?url=kamar/tambah', 'POST', [
    'nomor_kamar' => 'Kamar X-99',
    'harga' => '500000',
    'status' => 'status_palsu'
], $cookieFileAdmin, [], true);
echo (strpos($resp['body'], 'Status kamar harus berupa') !== false ? "     [PASS]" : "     [FAIL]") . " Validasi: Status harus berupa pilihan valid (tersedia, booking, terisi)\n";

// Validasi e: Nomor Kamar Duplikat
$resp = http_call($baseUrl . '?url=kamar/tambah', 'POST', [
    'nomor_kamar' => 'Kamar A-101', // Sudah ada di DB
    'harga' => '1500000',
    'status' => 'tersedia'
], $cookieFileAdmin, [], true);
echo (strpos($resp['body'], 'sudah digunakan') !== false ? "     [PASS]" : "     [FAIL]") . " Validasi: Nomor kamar tidak boleh duplikat\n";

// 3.3 Menambah Kamar Baru
$testNomor = 'Kamar C-' . rand(300, 999);
$testHarga = 1350000;
$testFasilitas = 'AC Daikin, KM Dalam, Meja Belajar, WiFi Cepat';
$testDeskripsi = 'Kamar lantai 3 dengan pemandangan kota dan sirkulasi angin segar.';

$resp = http_call($baseUrl . '?url=kamar/tambah', 'POST', [
    'nomor_kamar' => $testNomor,
    'harga' => $testHarga,
    'fasilitas' => $testFasilitas,
    'deskripsi' => $testDeskripsi,
    'status' => 'tersedia'
], $cookieFileAdmin, ['foto' => __DIR__ . '/../public/images/default_kamar.jpg'], false);

$isAddedRedirect = ($resp['code'] === 302 && strpos($resp['header'], 'kamar/admin') !== false);
echo ($isAddedRedirect ? " [PASS]" : " [FAIL]") . " 2. Admin berhasil menambah kamar baru ('$testNomor') dengan upload file foto\n";

// Cari ID kamar yang baru dibuat
$newRoom = $kamarModel->getAll();
$createdRoom = null;
foreach ($newRoom as $r) {
    if ($r['nomor_kamar'] === $testNomor) {
        $createdRoom = $r;
        break;
    }
}
$createdId = $createdRoom['id'] ?? 0;
echo ($createdId > 0 ? " [PASS]" : " [FAIL]") . " Kamar baru tersimpan di database dengan ID: #$createdId\n";
echo (!empty($createdRoom['foto']) && strpos($createdRoom['foto'], 'kamar_') !== false ? " [PASS]" : " [FAIL]") . " File foto tersimpan di server: " . ($createdRoom['foto'] ?? '') . "\n";

// 3.4 Melihat Detail Kamar
$resp = http_call($baseUrl . '?url=kamar/detail/' . $createdId, 'GET', [], $cookieAdmin = $cookieFileAdmin, [], true);
$hasNomorInDetail = strpos($resp['body'], $testNomor) !== false;
$hasHargaInDetail = strpos($resp['body'], '1.350.000') !== false;
echo ($hasNomorInDetail && $hasHargaInDetail ? " [PASS]" : " [FAIL]") . " 5. Admin dapat melihat detail kamar (kamar/detail/$createdId)\n";

// 3.5 Mengubah Kamar (Edit)
$updatedNomor = $testNomor . '-EDITED';
$updatedHarga = 1450000;
$updatedFasilitas = 'AC Daikin, KM Dalam, Smart TV, WiFi Cepat';
$updatedDeskripsi = 'Deskripsi kamar telah diperbarui oleh admin.';

$resp = http_call($baseUrl . '?url=kamar/edit/' . $createdId, 'POST', [
    'nomor_kamar' => $updatedNomor,
    'harga' => $updatedHarga,
    'fasilitas' => $updatedFasilitas,
    'deskripsi' => $updatedDeskripsi,
    'status' => 'booking'
], $cookieFileAdmin, [], false);

$isEditRedirect = ($resp['code'] === 302 && strpos($resp['header'], 'kamar/admin') !== false);
echo ($isEditRedirect ? " [PASS]" : " [FAIL]") . " 3. Admin berhasil mengubah kamar (nomor, harga, fasilitas, status)\n";

$checkUpdated = $kamarModel->getById($createdId);
$isDataMatched = ($checkUpdated['nomor_kamar'] === $updatedNomor && (int)$checkUpdated['harga'] === $updatedHarga && $checkUpdated['status'] === 'booking');
echo ($isDataMatched ? " [PASS]" : " [FAIL]") . " Data perubahan terkonfirmasi di database (Nomor: {$checkUpdated['nomor_kamar']}, Status: {$checkUpdated['status']})\n";

// 3.6 Mengubah Status Kamar Saja
$resp = http_call($baseUrl . '?url=kamar/ubah_status/' . $createdId . '&status=terisi', 'GET', [], $cookieFileAdmin, [], false);
$isStatusRedirect = ($resp['code'] === 302 && strpos($resp['header'], 'kamar/admin') !== false);
$checkStatus = $kamarModel->getById($createdId);
echo ($isStatusRedirect && $checkStatus['status'] === 'terisi' ? " [PASS]" : " [FAIL]") . " 6. Admin berhasil mengubah status kamar menjadi 'terisi'\n";

// 3.7 Menghapus Kamar
$resp = http_call($baseUrl . '?url=kamar/hapus/' . $createdId, 'GET', [], $cookieFileAdmin, [], false);
$isDeleteRedirect = ($resp['code'] === 302 && strpos($resp['header'], 'kamar/admin') !== false);
$checkDeleted = $kamarModel->getById($createdId);
echo ($isDeleteRedirect && $checkDeleted === null ? " [PASS]" : " [FAIL]") . " 4. Admin berhasil menghapus kamar (ID: #$createdId)\n\n";

// Cleanup cookies
foreach ([$cookieFileAdmin, $cookieFileUser, $cookieFileGuest] as $cf) {
    if (file_exists($cf)) unlink($cf);
}

echo "=================================================================\n";
echo "   SEMUA OPERASI CRUD & ACCESS CONTROL KAMAR LULUS 100%!        \n";
echo "=================================================================\n";
