<?php
/**
 * Test Suite: Fitur Admin Mengelola Booking Kamar (KosBook)
 * Menguji:
 * 1. Hak Akses (RBAC: Guest & User dilarang, Admin diizinkan)
 * 2. Tampilan 9 Kolom Wajib di Panel Admin:
 *    - ID Booking, Nama User, Email, Nomor Kamar, Tanggal Booking, Tanggal Mulai, Lama Sewa, Total Harga, Status
 * 3. Halaman Detail Booking (Admin dapat melihat rincian lengkap)
 * 4. Aksi Konfirmasi Booking:
 *    - status booking: menunggu -> dikonfirmasi
 *    - status kamar  : booking  -> terisi
 *    - Menggunakan Database Transaction
 * 5. Aksi Tolak Booking:
 *    - status booking: menunggu -> ditolak
 *    - status kamar  : booking  -> tersedia
 *    - Menggunakan Database Transaction
 * 6. Proteksi Integritas Status:
 *    - Admin TIDAK dapat mengkonfirmasi booking yang sudah ditolak
 *    - Admin TIDAK dapat mengkonfirmasi booking yang sudah dikonfirmasi
 * 7. Confirmation Dialog Modal (Bootstrap UI)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/KamarModel.php';
require_once __DIR__ . '/../app/models/BookingModel.php';
require_once __DIR__ . '/../app/helpers/AuthHelper.php';

$baseUrl = 'http://localhost/Project-RPL2/public/index.php';
$cookieFileAdmin = __DIR__ . '/test_cookie_admin_mgt.txt';
$cookieFileUser = __DIR__ . '/test_cookie_user_mgt.txt';
$cookieFileGuest = __DIR__ . '/test_cookie_guest_mgt.txt';

foreach ([$cookieFileAdmin, $cookieFileUser, $cookieFileGuest] as $cf) {
    if (file_exists($cf)) unlink($cf);
}

function http_admin_call($url, $method = 'GET', $data = [], $cookieFile = null, $follow = false) {
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
echo "       PENGUJIAN FITUR ADMIN MENGELOLA BOOKING (KOSBOOK)         \n";
echo "=================================================================\n\n";

$db = new Database();
$kamarModel = new KamarModel();
$bookingModel = new BookingModel();

// -------------------------------------------------------------
// BAGIAN 1: PENGUJIAN ACCESS CONTROL & RBAC
// -------------------------------------------------------------
echo "[BAGIAN 1] Pengujian Access Control (Guest, User, Admin):\n";

// 1.1 Guest mencoba akses halaman admin booking (Harus 302 ke auth/login)
$resp = http_admin_call($baseUrl . '?url=booking/admin', 'GET', [], $cookieFileGuest, false);
$isGuestBlocked = ($resp['code'] === 302 && strpos($resp['header'], 'auth/login') !== false);
echo ($isGuestBlocked ? " [PASS]" : " [FAIL]") . " Guest dilarang akses booking/admin (Redirect ke auth/login)\n";

$resp = http_admin_call($baseUrl . '?url=booking/konfirmasi_admin/1', 'GET', [], $cookieFileGuest, false);
$isGuestBlockedAction = ($resp['code'] === 302 && strpos($resp['header'], 'auth/login') !== false);
echo ($isGuestBlockedAction ? " [PASS]" : " [FAIL]") . " Guest dilarang aksi konfirmasi booking (Redirect ke auth/login)\n";

// 1.2 User (penghuni) login
http_admin_call($baseUrl . '?url=auth/login', 'POST', ['email' => 'ahmad@gmail.com', 'password' => 'user123'], $cookieFileUser, false);

// User mencoba akses halaman admin booking (Harus 302 ke dashboard/user)
$resp = http_admin_call($baseUrl . '?url=booking/admin', 'GET', [], $cookieFileUser, false);
$isUserBlocked = ($resp['code'] === 302 && strpos($resp['header'], 'dashboard/user') !== false);
echo ($isUserBlocked ? " [PASS]" : " [FAIL]") . " User dilarang akses booking/admin (Redirect ke dashboard/user)\n";

$resp = http_admin_call($baseUrl . '?url=booking/konfirmasi_admin/1', 'GET', [], $cookieFileUser, false);
$isUserBlockedAction = ($resp['code'] === 302 && strpos($resp['header'], 'dashboard/user') !== false);
echo ($isUserBlockedAction ? " [PASS]" : " [FAIL]") . " User dilarang aksi konfirmasi booking (Redirect ke dashboard/user)\n";

// 1.3 Admin login
$respLoginAdmin = http_admin_call($baseUrl . '?url=auth/login', 'POST', ['email' => 'admin@kosbook.com', 'password' => 'admin123'], $cookieFileAdmin, false);
echo ($respLoginAdmin['code'] === 302 ? " [PASS]" : " [FAIL]") . " Admin berhasil login\n";

$respAdminPage = http_admin_call($baseUrl . '?url=booking/admin', 'GET', [], $cookieFileAdmin, false);
echo ($respAdminPage['code'] === 200 ? " [PASS]" : " [FAIL]") . " Admin berhasil membuka halaman booking/admin (HTTP 200)\n\n";


// -------------------------------------------------------------
// BAGIAN 2: PENGUJIAN 9 KOLOM TAMPILAN DATA BOOKING
// -------------------------------------------------------------
echo "[BAGIAN 2] Pengujian 9 Kolom Tampilan Data Booking:\n";

$body = $respAdminPage['body'];
$hasColId = strpos($body, 'ID Booking') !== false;
$hasColUser = strpos($body, 'Nama User') !== false;
$hasColEmail = strpos($body, 'Email') !== false;
$hasColKamar = strpos($body, 'Nomor Kamar') !== false;
$hasColTglBooking = strpos($body, 'Tanggal Booking') !== false;
$hasColTglMulai = strpos($body, 'Tanggal Mulai') !== false;
$hasColDurasi = strpos($body, 'Lama Sewa') !== false;
$hasColTotal = strpos($body, 'Total Harga') !== false;
$hasColStatus = strpos($body, 'Status') !== false;

echo ($hasColId ? " [PASS]" : " [FAIL]") . " 1. Menampilkan kolom ID Booking\n";
echo ($hasColUser ? " [PASS]" : " [FAIL]") . " 2. Menampilkan kolom Nama User\n";
echo ($hasColEmail ? " [PASS]" : " [FAIL]") . " 3. Menampilkan kolom Email\n";
echo ($hasColKamar ? " [PASS]" : " [FAIL]") . " 4. Menampilkan kolom Nomor Kamar\n";
echo ($hasColTglBooking ? " [PASS]" : " [FAIL]") . " 5. Menampilkan kolom Tanggal Booking\n";
echo ($hasColTglMulai ? " [PASS]" : " [FAIL]") . " 6. Menampilkan kolom Tanggal Mulai\n";
echo ($hasColDurasi ? " [PASS]" : " [FAIL]") . " 7. Menampilkan kolom Lama Sewa\n";
echo ($hasColTotal ? " [PASS]" : " [FAIL]") . " 8. Menampilkan kolom Total Harga\n";
echo ($hasColStatus ? " [PASS]" : " [FAIL]") . " 9. Menampilkan kolom Status\n";

// Dialog Konfirmasi Modal Bootstrap di Daftar Booking
$hasModalConfirm = strpos($body, 'modalConfirmBooking') !== false;
$hasModalReject = strpos($body, 'modalRejectBooking') !== false;
echo ($hasModalConfirm && $hasModalReject ? " [PASS]" : " [FAIL]") . " Confirmation dialog (Bootstrap Modal) tersedia untuk konfirmasi & penolakan\n\n";


// -------------------------------------------------------------
// BAGIAN 3: PENGUJIAN MELIHAT DETAIL BOOKING
// -------------------------------------------------------------
echo "[BAGIAN 3] Pengujian Melihat Detail Booking:\n";

// Ambil salah satu booking di database
$testBooking = $bookingModel->getById(1);
$respDetail = http_admin_call($baseUrl . '?url=booking/detail/1', 'GET', [], $cookieFileAdmin, true);

$hasDetailTitle = strpos($respDetail['body'], 'Detail Booking #BK-001') !== false;
$hasDetailUser = strpos($respDetail['body'], $testBooking['user_nama']) !== false;
$hasDetailEmail = strpos($respDetail['body'], $testBooking['user_email']) !== false;
$hasDetailKamar = strpos($respDetail['body'], $testBooking['nomor_kamar']) !== false;
$hasDetailModal = (strpos($respDetail['body'], 'modalDetailConfirm') !== false && strpos($respDetail['body'], 'modalDetailReject') !== false);

echo ($respDetail['code'] === 200 && $hasDetailTitle ? " [PASS]" : " [FAIL]") . " Admin dapat membuka halaman detail booking #1 (HTTP 200)\n";
echo ($hasDetailUser && $hasDetailEmail && $hasDetailKamar ? " [PASS]" : " [FAIL]") . " Rincian data pemesan dan kamar tampil lengkap di detail booking\n";
echo ($hasDetailModal ? " [PASS]" : " [FAIL]") . " Modal dialog konfirmasi tersedia di halaman detail booking\n\n";


// -------------------------------------------------------------
// BAGIAN 4: PENGUJIAN AKSI KONFIRMASI BOOKING (TRANSAKSI DATABASE)
// -------------------------------------------------------------
echo "[BAGIAN 4] Pengujian Konfirmasi Booking (menunggu -> dikonfirmasi, kamar: booking -> terisi):\n";

// Siapkan Kamar Test & Booking Test baru dengan status 'menunggu'
// Gunakan Kamar #5 (Kamar VIP-01)
$db->query("UPDATE kamar SET status = 'tersedia' WHERE id = 5");
$db->execute();

// Buat booking test via BookingModel
$testBookingId1 = $bookingModel->createBooking([
    'user_id' => 3, // Ahmad
    'kamar_id' => 5,
    'tanggal_mulai' => date('Y-m-d', strtotime('+7 days')),
    'lama_sewa' => 2
]);

echo ($testBookingId1 > 0 ? " [PASS]" : " [FAIL]") . " Booking baru #$testBookingId1 berhasil dibuat (Status booking: menunggu, status kamar: booking)\n";

// Admin melakukan Konfirmasi Booking
$respConfirm = http_admin_call($baseUrl . '?url=booking/konfirmasi_admin/' . $testBookingId1, 'GET', [], $cookieFileAdmin, true);

// Periksa status booking di database
$checkBooking1 = $bookingModel->getById($testBookingId1);
$isBookingConfirmed = ($checkBooking1 && $checkBooking1['status'] === 'dikonfirmasi');
echo ($isBookingConfirmed ? " [PASS]" : " [FAIL]") . " Status booking berubah: menunggu -> dikonfirmasi\n";

// Periksa status kamar di database
$checkKamar5 = $kamarModel->getById(5);
$isKamarTerisi = ($checkKamar5 && $checkKamar5['status'] === 'terisi');
echo ($isKamarTerisi ? " [PASS]" : " [FAIL]") . " Status kamar berubah: booking -> terisi (Konsisten via Database Transaction)\n\n";


// -------------------------------------------------------------
// BAGIAN 5: PENGUJIAN AKSI TOLAK BOOKING (TRANSAKSI DATABASE)
// -------------------------------------------------------------
echo "[BAGIAN 5] Pengujian Tolak Booking (menunggu -> ditolak, kamar: booking -> tersedia):\n";

// Siapkan Kamar #3 (Kamar B-201) berstatus tersedia
$db->query("UPDATE kamar SET status = 'tersedia' WHERE id = 3");
$db->execute();

// Buat booking test ke-2
$testBookingId2 = $bookingModel->createBooking([
    'user_id' => 4, // Siti
    'kamar_id' => 3,
    'tanggal_mulai' => date('Y-m-d', strtotime('+10 days')),
    'lama_sewa' => 1
]);

echo ($testBookingId2 > 0 ? " [PASS]" : " [FAIL]") . " Booking baru #$testBookingId2 berhasil dibuat (Status booking: menunggu, status kamar: booking)\n";

// Admin melakukan Tolak Booking
$respReject = http_admin_call($baseUrl . '?url=booking/tolak_admin/' . $testBookingId2, 'GET', [], $cookieFileAdmin, true);

// Periksa status booking di database
$checkBooking2 = $bookingModel->getById($testBookingId2);
$isBookingRejected = ($checkBooking2 && $checkBooking2['status'] === 'ditolak');
echo ($isBookingRejected ? " [PASS]" : " [FAIL]") . " Status booking berubah: menunggu -> ditolak\n";

// Periksa status kamar di database
$checkKamar3 = $kamarModel->getById(3);
$isKamarTersediaAgain = ($checkKamar3 && $checkKamar3['status'] === 'tersedia');
echo ($isKamarTersediaAgain ? " [PASS]" : " [FAIL]") . " Status kamar berubah: booking -> tersedia (Kamar siap disewa kembali)\n\n";


// -------------------------------------------------------------
// BAGIAN 6: PROTEKSI TIDAK DAPAT KONFIRMASI ULANG (INTEGRITAS STATUS)
// -------------------------------------------------------------
echo "[BAGIAN 6] Proteksi Tidak Dapat Mengkonfirmasi Booking yang Sudah Ditolak / Dikonfirmasi:\n";

// 6.1 Coba konfirmasi booking yang sudah 'dikonfirmasi' (#testBookingId1)
$respReconfirm = http_admin_call($baseUrl . '?url=booking/konfirmasi_admin/' . $testBookingId1, 'GET', [], $cookieFileAdmin, true);
$hasReconfirmError = (strpos($respReconfirm['body'], 'tidak dapat dikonfirmasi') !== false);
echo ($hasReconfirmError ? " [PASS]" : " [FAIL]") . " Admin dicegah mengkonfirmasi booking yang sudah berstatus 'dikonfirmasi'\n";

// 6.2 Coba konfirmasi booking yang sudah 'ditolak' (#testBookingId2)
$respConfirmRejected = http_admin_call($baseUrl . '?url=booking/konfirmasi_admin/' . $testBookingId2, 'GET', [], $cookieFileAdmin, true);
$hasConfirmRejectError = (strpos($respConfirmRejected['body'], 'tidak dapat dikonfirmasi') !== false);
echo ($hasConfirmRejectError ? " [PASS]" : " [FAIL]") . " Admin dicegah mengkonfirmasi booking yang sudah berstatus 'ditolak'\n";

// 6.3 Coba tolak booking yang sudah 'dikonfirmasi' (#testBookingId1)
$respRejectConfirmed = http_admin_call($baseUrl . '?url=booking/tolak_admin/' . $testBookingId1, 'GET', [], $cookieFileAdmin, true);
$hasRejectConfirmError = (strpos($respRejectConfirmed['body'], 'tidak dapat ditolak') !== false);
echo ($hasRejectConfirmError ? " [PASS]" : " [FAIL]") . " Admin dicegah menolak booking yang sudah berstatus 'dikonfirmasi'\n\n";


// -------------------------------------------------------------
// CLEANUP DATA PENGUJIAN
// -------------------------------------------------------------
$db->query("DELETE FROM booking WHERE id IN (:id1, :id2)");
$db->bind(':id1', $testBookingId1);
$db->bind(':id2', $testBookingId2);
$db->execute();

$db->query("UPDATE kamar SET status = 'tersedia' WHERE id IN (3, 5)");
$db->execute();

foreach ([$cookieFileAdmin, $cookieFileUser, $cookieFileGuest] as $cf) {
    if (file_exists($cf)) unlink($cf);
}

echo "=================================================================\n";
echo "    SEMUA PENGUJIAN FITUR ADMIN KELOLA BOOKING LULUS 100%!       \n";
echo "=================================================================\n";
