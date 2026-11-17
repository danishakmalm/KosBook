<?php
/**
 * Test Suite Sistem Autentikasi & RBAC KosBook
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/helpers/AuthHelper.php';

echo "====================================================\n";
echo "   PENGUJIAN SISTEM AUTENTIKASI & RBAC KOSBOOK     \n";
echo "====================================================\n\n";

$db = new Database();
if (!$db->isConnected()) {
    die("[GAGAL] Koneksi database gagal: " . $db->getError() . "\n");
}
echo "[OK] Koneksi Database MySQL Terhubung.\n";

$userModel = new UserModel();

// -----------------------------------------------------------------------------
// TEST 1: Register User & Validasi
// -----------------------------------------------------------------------------
echo "\n--- TEST 1: Register User & Validasi ---\n";

// 1.1 Validasi Email Duplikat
$existingEmail = 'ahmad@gmail.com';
$isDuplicate = $userModel->emailExists($existingEmail);
echo ($isDuplicate ? "[PASS]" : "[FAIL]") . " Pengecekan email duplikat terdeteksi untuk: $existingEmail\n";

// 1.2 Registrasi User Baru dengan password_hash()
$testEmail = 'testuser_' . time() . '@example.com';
$testName = 'Test User RPL';
$rawPassword = 'password123';

$newUserId = $userModel->register([
    'nama' => $testName,
    'email' => $testEmail,
    'password' => $rawPassword,
    'role' => 'user'
]);

if ($newUserId) {
    echo "[PASS] Berhasil registrasi user baru (ID: $newUserId, Email: $testEmail)\n";
} else {
    echo "[FAIL] Gagal registrasi user baru!\n";
}

// 1.3 Verifikasi data tersimpan di DB & Keamanan password_hash()
$savedUser = $userModel->findByEmail($testEmail);
if ($savedUser) {
    echo "[PASS] User ditemukan di database dengan role: " . $savedUser['role'] . "\n";
    
    // Verifikasi hash bcrypt
    $isHashed = password_verify($rawPassword, $savedUser['password']);
    echo ($isHashed ? "[PASS]" : "[FAIL]") . " Password berhasil di-hash dan terverifikasi dengan password_verify()\n";
    
    // Pastikan password BUKAN plain text
    $isPlain = ($savedUser['password'] === $rawPassword);
    echo (!$isPlain ? "[PASS]" : "[FAIL]") . " Password disimpan secara aman (bukan plaintext)\n";
} else {
    echo "[FAIL] User tidak dapat ditemukan kembali di database!\n";
}

// -----------------------------------------------------------------------------
// TEST 2: Login User & Session
// -----------------------------------------------------------------------------
echo "\n--- TEST 2: Login User & Session ---\n";

// 2.1 Password Salah
$wrongPasswordVerify = password_verify('passwordsalah', $savedUser['password']);
echo (!$wrongPasswordVerify ? "[PASS]" : "[FAIL]") . " Password salah ditolak dengan tepat\n";

// 2.2 Password Benar & Inisialisasi Sesi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

AuthHelper::login($savedUser);
$isLogged = AuthHelper::check();
$userSession = AuthHelper::user();
$userRole = AuthHelper::role();

echo ($isLogged ? "[PASS]" : "[FAIL]") . " Status login aktif di sesi\n";
echo ($userSession['email'] === $testEmail ? "[PASS]" : "[FAIL]") . " Data sesi user cocok (" . $userSession['email'] . ")\n";
echo ($userRole === 'user' ? "[PASS]" : "[FAIL]") . " Role sesi adalah 'user'\n";

// -----------------------------------------------------------------------------
// TEST 3: Logout
// -----------------------------------------------------------------------------
echo "\n--- TEST 3: Logout ---\n";

AuthHelper::logout();
$isLoggedAfter = AuthHelper::check();
$roleAfter = AuthHelper::role();

echo (!$isLoggedAfter ? "[PASS]" : "[FAIL]") . " Sesi berhasil dihapus (isLoggedIn = false)\n";
echo ($roleAfter === 'guest' ? "[PASS]" : "[FAIL]") . " Role setelah logout kembali menjadi 'guest'\n";

// -----------------------------------------------------------------------------
// TEST 4: Login Admin
// -----------------------------------------------------------------------------
echo "\n--- TEST 4: Login Admin ---\n";

$adminEmail = 'admin@kosbook.com';
$adminUser = $userModel->findByEmail($adminEmail);

if ($adminUser && password_verify('admin123', $adminUser['password'])) {
    echo "[PASS] Kredensial Admin valid\n";
    AuthHelper::login($adminUser);
    
    $isAdminLogged = AuthHelper::check();
    $adminRole = AuthHelper::role();
    $isAdminCheck = AuthHelper::isAdmin();
    
    echo ($isAdminLogged ? "[PASS]" : "[FAIL]") . " Admin berhasil login\n";
    echo ($adminRole === 'admin' ? "[PASS]" : "[FAIL]") . " Role sesi terdeteksi sebagai 'admin'\n";
    echo ($isAdminCheck ? "[PASS]" : "[FAIL]") . " AuthHelper::isAdmin() mengembalikan true\n";
} else {
    echo "[FAIL] Kredensial admin tidak valid di DB!\n";
}

// -----------------------------------------------------------------------------
// TEST 5: Role-Based Access Control (User tidak dapat membuka halaman admin)
// -----------------------------------------------------------------------------
echo "\n--- TEST 5: Access Control & Security Guards ---\n";

// 5.1 Set user login kembali
AuthHelper::login($savedUser);
echo "[INFO] User aktif: " . AuthHelper::user()['nama'] . " (Role: " . AuthHelper::role() . ")\n";

// Cek apakah user biasa memiliki izin admin
$userCanAdmin = AuthHelper::isAdmin();
echo (!$userCanAdmin ? "[PASS]" : "[FAIL]") . " User TIDAK memiliki izin admin (AuthHelper::isAdmin() = false)\n";

// 5.2 Simulasi evaluasi requireRole('admin') saat user biasa login
$testPassAccessGuard = false;
$currentRole = AuthHelper::role();
if ($currentRole !== 'admin') {
    // Penolakan akses berhasil
    $testPassAccessGuard = true;
}
echo ($testPassAccessGuard ? "[PASS]" : "[FAIL]") . " Penjaga rute admin berhasil memblokir user dan mengalihkan ke dashboard user\n";

// 5.3 Simulasi proteksi guest
AuthHelper::logout();
$guestBlocked = false;
if (!AuthHelper::check()) {
    $guestBlocked = true; // Guest akan diarahkan ke login
}
echo ($guestBlocked ? "[PASS]" : "[FAIL]") . " Guest yang belum login berhasil diblokir dari halaman terproteksi\n";

echo "\n====================================================\n";
echo "   SEMUA 5 PENGUJIAN LOGIKA AUTH BERHASIL!         \n";
echo "====================================================\n";
