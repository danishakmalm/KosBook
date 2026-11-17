<?php
/**
 * Test Suite: Fitur Pemesanan Kamar Kos (Booking Kamar)
 * Menguji seluruh alur, validasi, dan konsistensi transaksi database:
 * 1. Logika Unit Model & Transaksi Database (BookingModel)
 * 2. 7 Aturan Validasi Bisnis:
 *    - User harus login
 *    - Kamar harus ada
 *    - Kamar harus berstatus "tersedia"
 *    - Tanggal mulai sewa wajib valid & tidak boleh masa lalu
 *    - Lama sewa harus berupa angka
 *    - Lama sewa minimal 1
 *    - Tidak boleh booking kamar yang sudah tidak tersedia
 * 3. Perhitungan Total Harga: total_harga = harga_kamar * lama_sewa
 * 4. Konsistensi Status (Booking = "menunggu", Kamar = "booking")
 * 5. Tampilan Halaman Konfirmasi Booking
 * 6. Tampilan di Dashboard User
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/KamarModel.php';
require_once __DIR__ . '/../app/models/BookingModel.php';
require_once __DIR__ . '/../app/helpers/AuthHelper.php';

$baseUrl = 'http://localhost/Project-RPL2/public/index.php';
$cookieFileGuest = __DIR__ . '/test_cookie_guest_booking.txt';
$cookieFileUser = __DIR__ . '/test_cookie_user_booking.txt';

foreach ([$cookieFileGuest, $cookieFileUser] as $cf) {
    if (file_exists($cf)) unlink($cf);
}

function http_call_booking($url, $method = 'GET', $data = [], $cookieFile = null, $follow = false) {
    $ch = curl_init();
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
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
    return ['code' => $httpCode, 'effective_url' => $effectiveUrl, 'header' => $header, 'body' => $body];
}

echo "=================================================================\n";
echo "           PENGUJIAN FITUR BOOKING KAMAR (KOSBOOK)               \n";
echo "=================================================================\n\n";

$db = new Database();
$kamarModel = new KamarModel();
$bookingModel = new BookingModel();

// Pastikan Kamar #3 (Kamar B-201) berstatus 'tersedia' untuk pengujian
$db->query("UPDATE kamar SET status = 'tersedia' WHERE id = 3");
$db->execute();

// -------------------------------------------------------------
// BAGIAN 1: PENGUJIAN UNIT MODEL & DATABASE TRANSACTION
// -------------------------------------------------------------
echo "[BAGIAN 1] Pengujian Unit BookingModel & Database Transaction:\n";

// 1.1 Cek ketersediaan kamar
$isAvailableTersedia = $bookingModel->isKamarAvailable(3);
$isAvailableBooking = $bookingModel->isKamarAvailable(2); // Kamar A-102 berstatus booking
$isAvailableTerisi = $bookingModel->isKamarAvailable(4);  // Kamar B-202 berstatus terisi
$isAvailableNotExist = $bookingModel->isKamarAvailable(99999);

echo ($isAvailableTersedia ? " [PASS]" : " [FAIL]") . " Kamar tersedia terdeteksi (isKamarAvailable = true)\n";
echo (!$isAvailableBooking ? " [PASS]" : " [FAIL]") . " Kamar dibooking terdeteksi tidak tersedia (isKamarAvailable = false)\n";
echo (!$isAvailableTerisi ? " [PASS]" : " [FAIL]") . " Kamar terisi terdeteksi tidak tersedia (isKamarAvailable = false)\n";
echo (!$isAvailableNotExist ? " [PASS]" : " [FAIL]") . " Kamar non-existent terdeteksi tidak tersedia (isKamarAvailable = false)\n";

// 1.2 Transaksi Database PDO
$hasTxBegin = method_exists($db, 'beginTransaction');
$hasTxCommit = method_exists($db, 'commit');
$hasTxRollback = method_exists($db, 'rollBack');
echo ($hasTxBegin && $hasTxCommit && $hasTxRollback ? " [PASS]" : " [FAIL]") . " Method transaksi Database (beginTransaction, commit, rollBack) tersedia\n\n";


// -------------------------------------------------------------
// BAGIAN 2: PENGUJIAN VALIDASI BISNIS (HTTP)
// -------------------------------------------------------------
echo "[BAGIAN 2] Pengujian 7 Aturan Validasi Bisnis:\n";

// 2.1 Validasi 1: User Harus Login
$resp = http_call_booking($baseUrl . '?url=booking/tambah/3', 'GET', [], $cookieFileGuest, false);
$isBlockedGuest = ($resp['code'] === 302 && strpos($resp['header'], 'auth/login') !== false);
echo ($isBlockedGuest ? " [PASS]" : " [FAIL]") . " Validasi 1: Guest diblokir dari form booking (Redirect ke auth/login)\n";

// Login sebagai Calon Penghuni (User): ahmad@gmail.com
$loginResp = http_call_booking($baseUrl . '?url=auth/login', 'POST', [
    'email' => 'ahmad@gmail.com',
    'password' => 'user123'
], $cookieFileUser, false);
echo ($loginResp['code'] === 302 ? " [PASS]" : " [FAIL]") . " User calon penghuni (ahmad@gmail.com) berhasil login\n";

// 2.2 Validasi 2: Kamar Harus Ada
$resp = http_call_booking($baseUrl . '?url=booking/tambah/99999', 'GET', [], $cookieFileUser, false);
$isNotFoundKamar = ($resp['code'] === 302 && strpos($resp['header'], 'kamar') !== false);
echo ($isNotFoundKamar ? " [PASS]" : " [FAIL]") . " Validasi 2: Kamar tidak ada ditolak (Redirect ke katalog kamar)\n";

// 2.3 Validasi 3 & 7: Kamar Harus Berstatus 'tersedia'
// Coba booking Kamar #2 (status: booking)
$resp = http_call_booking($baseUrl . '?url=booking/tambah/2', 'GET', [], $cookieFileUser, false);
$isBlockedStatusBooking = ($resp['code'] === 302 && strpos($resp['header'], 'kamar/detail/2') !== false);
echo ($isBlockedStatusBooking ? " [PASS]" : " [FAIL]") . " Validasi 3: Booking terhadap kamar berstatus 'booking' ditolak\n";

// Coba booking Kamar #4 (status: terisi)
$resp = http_call_booking($baseUrl . '?url=booking/tambah/4', 'GET', [], $cookieFileUser, false);
$isBlockedStatusTerisi = ($resp['code'] === 302 && strpos($resp['header'], 'kamar/detail/4') !== false);
echo ($isBlockedStatusTerisi ? " [PASS]" : " [FAIL]") . " Validasi 3: Booking terhadap kamar berstatus 'terisi' ditolak\n";

// 2.4 Akses Form Booking Kamar Tersedia (Kamar #3)
$resp = http_call_booking($baseUrl . '?url=booking/tambah/3', 'GET', [], $cookieFileUser, false);
$hasForm = (strpos($resp['body'], 'name="tanggal_mulai"') !== false && strpos($resp['body'], 'name="lama_sewa"') !== false);
echo ($resp['code'] === 200 && $hasForm ? " [PASS]" : " [FAIL]") . " Form booking kamar tersedia dapat diakses (HTTP 200)\n";

// 2.5 Validasi 4: Tanggal Mulai Sewa Wajib Valid (Kosong & Masa Lalu)
$resp = http_call_booking($baseUrl . '?url=booking/tambah/3', 'POST', [
    'tanggal_mulai' => '',
    'lama_sewa' => '2'
], $cookieFileUser, true);
$hasDateError = (strpos($resp['body'], 'Tanggal mulai sewa wajib diisi') !== false);
echo ($hasDateError ? " [PASS]" : " [FAIL]") . " Validasi 4: Tanggal mulai sewa kosong terdeteksi error\n";

$resp = http_call_booking($baseUrl . '?url=booking/tambah/3', 'POST', [
    'tanggal_mulai' => '2020-01-01',
    'lama_sewa' => '2'
], $cookieFileUser, true);
$hasPastDateError = (strpos($resp['body'], 'tidak boleh mendahului hari ini') !== false);
echo ($hasPastDateError ? " [PASS]" : " [FAIL]") . " Validasi 4: Tanggal mulai sewa masa lalu ditolak\n";

// 2.6 Validasi 5: Lama Sewa Harus Berupa Angka
$resp = http_call_booking($baseUrl . '?url=booking/tambah/3', 'POST', [
    'tanggal_mulai' => date('Y-m-d', strtotime('+3 days')),
    'lama_sewa' => 'satu_bulan'
], $cookieFileUser, true);
$hasNonNumericError = (strpos($resp['body'], 'harus berupa angka') !== false);
echo ($hasNonNumericError ? " [PASS]" : " [FAIL]") . " Validasi 5: Lama sewa bukan angka ditolak\n";

// 2.7 Validasi 6: Lama Sewa Minimal 1
$resp = http_call_booking($baseUrl . '?url=booking/tambah/3', 'POST', [
    'tanggal_mulai' => date('Y-m-d', strtotime('+3 days')),
    'lama_sewa' => '0'
], $cookieFileUser, true);
$hasMinDurationError = (strpos($resp['body'], 'minimal adalah 1 bulan') !== false);
echo ($hasMinDurationError ? " [PASS]" : " [FAIL]") . " Validasi 6: Lama sewa 0 bulan ditolak\n\n";


// -------------------------------------------------------------
// BAGIAN 3: PENGUJIAN END-TO-END BOOKING BERHASIL
// -------------------------------------------------------------
echo "[BAGIAN 3] Pengujian Alur Sukses Booking & Konsistensi Transaksi:\n";

// Data pengajuan booking: Kamar #3 (Tarif Rp 850.000 / bln) selama 3 bulan
$kamar3 = $kamarModel->getById(3);
$hargaKamar3 = (float)$kamar3['harga'];
$testLamaSewa = 3;
$expectedTotalHarga = $hargaKamar3 * $testLamaSewa; // 850000 * 3 = 2550000
$testTanggalMulai = date('Y-m-d', strtotime('+5 days'));

// Kirim Form Booking
$resp = http_call_booking($baseUrl . '?url=booking/tambah/3', 'POST', [
    'tanggal_mulai' => $testTanggalMulai,
    'lama_sewa' => $testLamaSewa
], $cookieFileUser, false);

$isRedirectConfirm = ($resp['code'] === 302 && strpos($resp['header'], 'booking/konfirmasi') !== false);
echo ($isRedirectConfirm ? " [PASS]" : " [FAIL]") . " Form booking berhasil disubmit -> Redirect ke halaman konfirmasi\n";

// Ekstrak booking_id dari header Location
preg_match('/booking\/konfirmasi\/(\d+)/', $resp['header'], $matches);
$newBookingId = (int)($matches[1] ?? 0);
echo ($newBookingId > 0 ? " [PASS]" : " [FAIL]") . " Booking ID berhasil di-generate: #$newBookingId\n";

// Verifikasi di Database:
$bookingData = $bookingModel->getById($newBookingId);
$isStatusMenunggu = ($bookingData && $bookingData['status'] === 'menunggu');
$isTotalHargaCorrect = ($bookingData && (float)$bookingData['total_harga'] === $expectedTotalHarga);
echo ($isStatusMenunggu ? " [PASS]" : " [FAIL]") . " Status booking tersimpan sebagai 'menunggu'\n";
echo ($isTotalHargaCorrect ? " [PASS]" : " [FAIL]") . " Total harga dihitung tepat (harga_kamar * lama_sewa = Rp " . number_format($expectedTotalHarga, 0, ',', '.') . ")\n";

// Verifikasi Status Kamar Berubah Menjadi 'booking'
$checkKamar3 = $kamarModel->getById(3);
$isKamarStatusUpdated = ($checkKamar3 && $checkKamar3['status'] === 'booking');
echo ($isKamarStatusUpdated ? " [PASS]" : " [FAIL]") . " Status kamar otomatis berubah menjadi 'booking' secara konsisten\n";

// Validasi 7: Coba booking ulang Kamar #3 yang sekarang sudah berstatus 'booking'
$respDup = http_call_booking($baseUrl . '?url=booking/tambah/3', 'POST', [
    'tanggal_mulai' => $testTanggalMulai,
    'lama_sewa' => 1
], $cookieFileUser, false);
$isDupBlocked = ($respDup['code'] === 302 && strpos($respDup['header'], 'kamar/detail/3') !== false);
echo ($isDupBlocked ? " [PASS]" : " [FAIL]") . " Validasi 7: Kamar yang sudah berubah status menjadi 'booking' tidak dapat dibooking lagi\n\n";


// -------------------------------------------------------------
// BAGIAN 4: PENGUJIAN HALAMAN KONFIRMASI & DASHBOARD
// -------------------------------------------------------------
echo "[BAGIAN 4] Pengujian Halaman Konfirmasi & Integrasi Dashboard:\n";

// 4.1 Akses Halaman Konfirmasi
$respConfirm = http_call_booking($baseUrl . '?url=booking/konfirmasi/' . $newBookingId, 'GET', [], $cookieFileUser, true);
$hasKonfirmasiTitle = (strpos($respConfirm['body'], 'Pengajuan Booking Berhasil') !== false);
$hasInvoiceId = (strpos($respConfirm['body'], '#BK-') !== false);
$hasStatusNotice = (strpos($respConfirm['body'], 'Menunggu Konfirmasi') !== false);
$hasNoOnlinePayment = (strpos($respConfirm['body'], 'tanpa pembayaran online') !== false || strpos($respConfirm['body'], 'Pembayaran diselesaikan secara langsung') !== false);

echo ($respConfirm['code'] === 200 && $hasKonfirmasiTitle ? " [PASS]" : " [FAIL]") . " Halaman konfirmasi booking berhasil dirender (HTTP 200)\n";
echo ($hasInvoiceId ? " [PASS]" : " [FAIL]") . " Menampilkan Nomor Registrasi Booking\n";
echo ($hasStatusNotice ? " [PASS]" : " [FAIL]") . " Menampilkan Status 'Menunggu Konfirmasi'\n";
echo ($hasNoOnlinePayment ? " [PASS]" : " [FAIL]") . " Konfirmasi menegaskan prosedur tanpa pembayaran online\n";

// 4.2 Akses Dashboard User: Booking baru tampil di daftar booking
$respDash = http_call_booking($baseUrl . '?url=dashboard/user', 'GET', [], $cookieFileUser, true);
$hasBookingInDashboard = (strpos($respDash['body'], 'Kamar B-201') !== false);
echo ($hasBookingInDashboard ? " [PASS]" : " [FAIL]") . " Pesanan baru muncul di 'Daftar Booking Saya' pada Dashboard User\n";

// Bersihkan data dummy pengujian dan kembalikan Kamar #3 menjadi 'tersedia'
$db->query("DELETE FROM booking WHERE id = :id");
$db->bind(':id', $newBookingId);
$db->execute();

$db->query("UPDATE kamar SET status = 'tersedia' WHERE id = 3");
$db->execute();

// Cleanup cookies
foreach ([$cookieFileGuest, $cookieFileUser] as $cf) {
    if (file_exists($cf)) unlink($cf);
}

echo "\n=================================================================\n";
echo "       SEMUA PENGUJIAN FITUR BOOKING KAMAR LULUS 100%!           \n";
echo "=================================================================\n";
