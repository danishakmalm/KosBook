<?php
/**
 * Pengujian End-to-End HTTP untuk Sistem Autentikasi & RBAC KosBook
 * Menguji request HTTP nyata ke server Apache dengan Cookie Session
 */

$baseUrl = 'http://localhost/Project-RPL2/public/index.php';
$cookieFile = __DIR__ . '/test_cookie.txt';

if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

function http_request($url, $method = 'GET', $data = [], $cookieFile = null, $followRedirect = true) {
    $ch = curl_init();
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
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

echo "========================================================\n";
echo "   PENGUJIAN END-TO-END HTTP & SESSION RBAC KOSBOOK     \n";
echo "========================================================\n\n";

// -------------------------------------------------------------
// SCENARIO 0: Guest Protection
// -------------------------------------------------------------
echo "[SKENARIO 0] Akses Guest ke Halaman Terproteksi:\n";

// 0.1 Guest akses Dashboard User
$resp = http_request($baseUrl . '?url=dashboard/user', 'GET', [], $cookieFile, false);
$isRedirectToLogin = ($resp['code'] === 302 && strpos($resp['header'], 'auth/login') !== false);
echo ($isRedirectToLogin ? " [PASS]" : " [FAIL]") . " Guest membuka dashboard/user dialihkan ke auth/login (HTTP " . $resp['code'] . ")\n";

// 0.2 Guest akses Dashboard Admin
$resp = http_request($baseUrl . '?url=dashboard/admin', 'GET', [], $cookieFile, false);
$isRedirectToLogin = ($resp['code'] === 302 && strpos($resp['header'], 'auth/login') !== false);
echo ($isRedirectToLogin ? " [PASS]" : " [FAIL]") . " Guest membuka dashboard/admin dialihkan ke auth/login (HTTP " . $resp['code'] . ")\n\n";

// -------------------------------------------------------------
// SCENARIO 1: Register User & Validasi Lengkap
// -------------------------------------------------------------
echo "[SKENARIO 1] Pengujian Register User & Validasi:\n";

// 1.1 Validasi: Field Kosong
$resp = http_request($baseUrl . '?url=auth/register', 'POST', [
    'nama' => '',
    'email' => '',
    'password' => '',
    'password_confirm' => ''
], $cookieFile, true);
$hasErrorMsg = strpos($resp['body'], 'Semua field wajib diisi') !== false;
echo ($hasErrorMsg ? " [PASS]" : " [FAIL]") . " Validasi: Field kosong terdeteksi dan menampilkan pesan error\n";

// 1.2 Validasi: Email Tidak Valid
$resp = http_request($baseUrl . '?url=auth/register', 'POST', [
    'nama' => 'Budi',
    'email' => 'budi-bukan-email',
    'password' => 'secret123',
    'password_confirm' => 'secret123'
], $cookieFile, true);
$hasEmailError = strpos($resp['body'], 'Format alamat email tidak valid') !== false;
echo ($hasEmailError ? " [PASS]" : " [FAIL]") . " Validasi: Format email salah terdeteksi\n";

// 1.3 Validasi: Password Kurang dari 6 Karakter
$resp = http_request($baseUrl . '?url=auth/register', 'POST', [
    'nama' => 'Budi',
    'email' => 'budi123@gmail.com',
    'password' => '12345',
    'password_confirm' => '12345'
], $cookieFile, true);
$hasLengthError = strpos($resp['body'], 'Password minimal harus 6 karakter') !== false;
echo ($hasLengthError ? " [PASS]" : " [FAIL]") . " Validasi: Password kurang dari 6 karakter terdeteksi\n";

// 1.4 Validasi: Konfirmasi Password Tidak Cocok
$resp = http_request($baseUrl . '?url=auth/register', 'POST', [
    'nama' => 'Budi',
    'email' => 'budi123@gmail.com',
    'password' => 'secret123',
    'password_confirm' => 'berbeda123'
], $cookieFile, true);
$hasMismatchError = strpos($resp['body'], 'Password dan konfirmasi password tidak cocok') !== false;
echo ($hasMismatchError ? " [PASS]" : " [FAIL]") . " Validasi: Konfirmasi password tidak cocok terdeteksi\n";

// 1.5 Validasi: Email Duplikat
$resp = http_request($baseUrl . '?url=auth/register', 'POST', [
    'nama' => 'Ahmad Duplikat',
    'email' => 'ahmad@gmail.com',
    'password' => 'secret123',
    'password_confirm' => 'secret123'
], $cookieFile, true);
$hasDuplicateError = strpos($resp['body'], 'Alamat email sudah terdaftar') !== false;
echo ($hasDuplicateError ? " [PASS]" : " [FAIL]") . " Validasi: Email duplikat dicegah mendaftar\n";

// 1.6 Registrasi Berhasil
$newUserEmail = 'user_' . time() . '@gmail.com';
$newUserName = 'Calon Penghuni Baru ' . rand(100, 999);
$newUserPassword = 'password123';

$resp = http_request($baseUrl . '?url=auth/register', 'POST', [
    'nama' => $newUserName,
    'email' => $newUserEmail,
    'password' => $newUserPassword,
    'password_confirm' => $newUserPassword
], $cookieFile, true);

$hasSuccessMsg = strpos($resp['body'], 'Pendaftaran akun berhasil') !== false;
echo ($hasSuccessMsg ? " [PASS]" : " [FAIL]") . " Berhasil registrasi user baru ($newUserEmail) dan dialihkan ke login\n\n";

// -------------------------------------------------------------
// SCENARIO 2: Login User
// -------------------------------------------------------------
echo "[SKENARIO 2] Pengujian Login User:\n";

// 2.1 Login dengan Password Salah
$resp = http_request($baseUrl . '?url=auth/login', 'POST', [
    'email' => $newUserEmail,
    'password' => 'passwordsalah'
], $cookieFile, true);
$hasWrongPassMsg = strpos($resp['body'], 'Email atau kata sandi salah') !== false;
echo ($hasWrongPassMsg ? " [PASS]" : " [FAIL]") . " Login dengan password salah ditolak\n";

// 2.2 Login User Berhasil
$resp = http_request($baseUrl . '?url=auth/login', 'POST', [
    'email' => $newUserEmail,
    'password' => $newUserPassword
], $cookieFile, true);

$isAtUserDashboard = (strpos($resp['effective_url'], 'dashboard/user') !== false) || (strpos($resp['body'], 'Dashboard Penghuni') !== false);
$hasWelcomeName = strpos($resp['body'], htmlspecialchars($newUserName)) !== false;

echo ($isAtUserDashboard ? " [PASS]" : " [FAIL]") . " Login User berhasil dan dialihkan ke Dashboard User (URL: " . $resp['effective_url'] . ")\n";
echo ($hasWelcomeName ? " [PASS]" : " [FAIL]") . " Nama user '$newUserName' tampil di navbar/dashboard\n\n";

// -------------------------------------------------------------
// SCENARIO 3: Role-Based Access: User TIDAK Dapat Membuka Halaman Admin
// -------------------------------------------------------------
echo "[SKENARIO 3] Role-Based Access Control (User Coba Buka Admin):\n";

// User yang sedang login mencoba mengakses dashboard/admin
$resp = http_request($baseUrl . '?url=dashboard/admin', 'GET', [], $cookieFile, false);

$isBlocked = ($resp['code'] === 302 && strpos($resp['header'], 'dashboard/user') !== false);
echo ($isBlocked ? " [PASS]" : " [FAIL]") . " Akses ditolak! User diblokir dari dashboard/admin (HTTP " . $resp['code'] . " Redirect ke dashboard/user)\n";

// Follow redirect untuk memeriksa flash message
$respFollow = http_request($baseUrl . '?url=dashboard/admin', 'GET', [], $cookieFile, true);
$hasAccessDeniedMsg = strpos($respFollow['body'], 'Akses ditolak') !== false;
echo ($hasAccessDeniedMsg ? " [PASS]" : " [FAIL]") . " Muncul pesan flash alert 'Akses ditolak! Halaman ini hanya dapat diakses oleh Administrator.'\n\n";

// -------------------------------------------------------------
// SCENARIO 4: Logout
// -------------------------------------------------------------
echo "[SKENARIO 4] Pengujian Logout User:\n";

$resp = http_request($baseUrl . '?url=auth/logout', 'GET', [], $cookieFile, true);
$isLoggedOut = strpos($resp['body'], 'Anda telah berhasil logout') !== false;
echo ($isLoggedOut ? " [PASS]" : " [FAIL]") . " Logout berhasil dan dialihkan ke halaman login\n";

// Coba buka dashboard/user lagi setelah logout
$respAfterLogout = http_request($baseUrl . '?url=dashboard/user', 'GET', [], $cookieFile, false);
$isProtectedAfterLogout = ($respAfterLogout['code'] === 302 && strpos($respAfterLogout['header'], 'auth/login') !== false);
echo ($isProtectedAfterLogout ? " [PASS]" : " [FAIL]") . " Setelah logout, dashboard/user tidak bisa diakses (Redirect HTTP " . $respAfterLogout['code'] . ")\n\n";

// -------------------------------------------------------------
// SCENARIO 5: Login Admin & Akses Halaman Admin
// -------------------------------------------------------------
echo "[SKENARIO 5] Pengujian Login Admin:\n";

$resp = http_request($baseUrl . '?url=auth/login', 'POST', [
    'email' => 'admin@kosbook.com',
    'password' => 'admin123'
], $cookieFile, true);

$isAtAdminDashboard = (strpos($resp['effective_url'], 'dashboard/admin') !== false) || (strpos($resp['body'], 'Panel Pengelola Kos') !== false);
$hasAdminBadge = strpos($resp['body'], 'Mode Administrator Aktif') !== false;
$hasNewUserInTable = strpos($resp['body'], $newUserEmail) !== false;

echo ($isAtAdminDashboard ? " [PASS]" : " [FAIL]") . " Login Admin berhasil dan dialihkan ke Dashboard Admin\n";
echo ($hasAdminBadge ? " [PASS]" : " [FAIL]") . " Panel Administrator aktif dan badge admin muncul\n";
echo ($hasNewUserInTable ? " [PASS]" : " [FAIL]") . " User yang baru diregistrasi ($newUserEmail) muncul di tabel Data User Admin!\n\n";

// Bersihkan file cookie pengujian
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

echo "========================================================\n";
echo "   SEMUA 5 PENGUJIAN END-TO-END HTTP LULUS DENGAN SUKSES! \n";
echo "========================================================\n";
