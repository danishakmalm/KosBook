<?php
/**
 * Test Fitur Konfigurasi AI Key, Model Picker, Penghapusan Demo Shortcut, & Respon AI Ringkas
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/services/AiConfigService.php';
require_once __DIR__ . '/../app/services/ChatbotService.php';
require_once __DIR__ . '/../app/services/GeminiService.php';
require_once __DIR__ . '/../app/helpers/AuthHelper.php';

function assertTest(string $desc, bool $condition): void {
    if ($condition) {
        echo " [PASS] " . $desc . PHP_EOL;
    } else {
        echo " [FAIL] " . $desc . PHP_EOL;
        exit(1);
    }
}

echo "=================================================================" . PHP_EOL;
echo "  PENGUJIAN FITUR KONFIGURASI AI, SHORTCUT DEMO, & RESPON RINGKAS" . PHP_EOL;
echo "=================================================================" . PHP_EOL . PHP_EOL;

// -----------------------------------------------------------------------------
// TAHAP 1: Pengujian Penghapusan Shortcut Demo di Halaman Login
// -----------------------------------------------------------------------------
echo "--- TAHAP 1: PENGUJIAN HALAMAN LOGIN TANPA SHORTCUT DEMO ---" . PHP_EOL;

$loginViewFile = __DIR__ . '/../app/views/auth/login.php';
$loginContent = file_get_contents($loginViewFile);

assertTest("Shortcut 'fillDemo' telah dihapus dari script login", strpos($loginContent, 'fillDemo') === false);
assertTest("Box 'Akun Demo Tersedia' telah dihapus dari tampilan login", strpos($loginContent, 'Akun Demo Tersedia') === false);
assertTest("Tombol 'Isi Demo User' telah dihapus", strpos($loginContent, 'Isi Demo User') === false);
assertTest("Tombol 'Isi Demo Admin' telah dihapus", strpos($loginContent, 'Isi Demo Admin') === false);
assertTest("Form login (email, password, remember) tetap utuh", 
    strpos($loginContent, 'id="email"') !== false && 
    strpos($loginContent, 'id="password"') !== false && 
    strpos($loginContent, 'name="remember"') !== false);

// Verifikasi akun di database tetap ada
$db = new Database();
$db->query("SELECT id, email, role FROM users WHERE email = 'ahmad@gmail.com' OR email = 'admin@kosbook.com'");
$usersInDb = $db->resultSet();
assertTest("Akun user & admin demo tetap tersimpan aman di database (tidak terhapus)", count($usersInDb) >= 2);

echo PHP_EOL;

// -----------------------------------------------------------------------------
// TAHAP 2: Pengujian AiConfigService & Pengambilan Model Gemini
// -----------------------------------------------------------------------------
echo "--- TAHAP 2: PENGUJIAN AI CONFIG SERVICE & FETCH AVAILABLE MODELS ---" . PHP_EOL;

$aiConfigService = new AiConfigService();
$currentCfg = $aiConfigService->getCurrentConfig();

assertTest("AiConfigService membaca API Key saat ini", !empty($currentCfg['api_key']));
assertTest("AiConfigService menghasilkan masked API Key aman", !empty($currentCfg['masked_api_key']) && strpos($currentCfg['masked_api_key'], '•') !== false);
assertTest("AiConfigService membaca Primary Model saat ini", !empty($currentCfg['model']));
assertTest("Status is_configured bernilai true", $currentCfg['is_configured'] === true);

// Fetch Model langsung dari Google Gemini API
echo "  Mengambil model aktif dari Google Gemini API endpoint..." . PHP_EOL;
$modelsResult = $aiConfigService->fetchAvailableModels();
assertTest("Fetch model berhasil (HTTP 200 dari Google API)", $modelsResult['success'] === true);
assertTest("Ditemukan model yang mendukung generateContent (> 0 model)", $modelsResult['count'] > 0);

$modelIds = array_column($modelsResult['models'], 'id');
assertTest("Daftar model memuat gemini-2.5-flash", in_array('gemini-2.5-flash', $modelIds));
echo "  Total {$modelsResult['count']} model aktif berhasil dimuat." . PHP_EOL;

// Validasi jika API key kosong
$emptyResult = $aiConfigService->fetchAvailableModels('');
assertTest("Pengambilan model dengan API Key kosong ditolak dengan pesan jelas", $emptyResult['success'] === false);

echo PHP_EOL;

// -----------------------------------------------------------------------------
// TAHAP 3: Pengujian UI Rendering Konfigurasi AI di Dashboard Admin
// -----------------------------------------------------------------------------
echo "--- TAHAP 3: PENGUJIAN UI DASHBOARD ADMIN KONFIGURASI AI ---" . PHP_EOL;

ob_start();
$currentAdmin = ['id' => 1, 'nama' => 'Admin KosBook', 'email' => 'admin@kosbook.com', 'role' => 'admin'];
$flash = null;
$stats = [
    'total_kamar' => 6,
    'kamar_tersedia' => 3,
    'kamar_booking' => 1,
    'kamar_terisi' => 2,
    'total_user' => 3,
    'total_booking' => 3,
    'booking_menunggu' => 1
];
$recentBookings = [];
$recentRooms = [];
$users = [];
require __DIR__ . '/../app/views/dashboard/admin.php';
$adminHtml = ob_get_clean();

assertTest("Dashboard Admin memuat tombol shortcut 'Konfigurasi AI' di header", strpos($adminHtml, 'Konfigurasi AI') !== false);
assertTest("Dashboard Admin memuat section #konfigurasi-ai", strpos($adminHtml, 'id="konfigurasi-ai"') !== false);
assertTest("Memuat input field gemini_api_key", strpos($adminHtml, 'name="gemini_api_key"') !== false);
assertTest("Memuat tombol 'Cek & Muat Model' (btnFetchModels)", strpos($adminHtml, 'id="btnFetchModels"') !== false);
assertTest("Memuat dropdown pilihan Model Utama (gemini_model)", strpos($adminHtml, 'id="gemini_model"') !== false);
assertTest("Memuat dropdown pilihan Model Cadangan (gemini_fallback_model)", strpos($adminHtml, 'id="gemini_fallback_model"') !== false);
assertTest("Memuat tombol 'Uji Koneksi AI' (btnTestAi)", strpos($adminHtml, 'id="btnTestAi"') !== false);
assertTest("Memuat tombol 'Simpan Konfigurasi' (btnSaveAi)", strpos($adminHtml, 'id="btnSaveAi"') !== false);
assertTest("Memuat toggle show/hide API key (btnToggleApiKey)", strpos($adminHtml, 'id="btnToggleApiKey"') !== false);

echo PHP_EOL;

// -----------------------------------------------------------------------------
// TAHAP 4: Pengujian Prompt AI Ringkas & To-the-Point
// -----------------------------------------------------------------------------
echo "--- TAHAP 4: PENGUJIAN RESPON AI RINGKAS TANPA BASA-BASI ---" . PHP_EOL;

$chatbotService = new ChatbotService();

// Ambil respon Fairy untuk pertanyaan sederhana harga kamar
AuthHelper::init();
AuthHelper::logout();

$responseFairy = $chatbotService->handleMessage("Berapa harga kamar A-101?");
assertTest("Fairy merespons pertanyaan harga kamar", $responseFairy['success'] === true);
assertTest("Respon Fairy memuat harga '1.250.000'", strpos($responseFairy['reply'], '1.250.000') !== false);
assertTest("Respon Fairy tidak bertele-tele (kurang dari 250 karakter)", strlen($responseFairy['reply']) < 250);
assertTest("Respon Fairy TIDAK memuat penutup klise 'Ada yang bisa Fairy bantu'", stripos($responseFairy['reply'], 'Ada yang bisa Fairy bantu') === false);
assertTest("Respon Fairy TIDAK memuat pembuka klise 'Selamat datang di KosBook'", stripos($responseFairy['reply'], 'Selamat datang di KosBook') === false);

echo "  Contoh respon Fairy terkini:" . PHP_EOL;
echo "  \"" . trim($responseFairy['reply']) . "\"" . PHP_EOL . PHP_EOL;

// -----------------------------------------------------------------------------
// TAHAP 5: Pengujian Simpan Konfigurasi ke .env
// -----------------------------------------------------------------------------
echo "--- TAHAP 5: PENGUJIAN PERSISTENSI KONFIGURASI KE FILE .ENV ---" . PHP_EOL;

$originalKey = $currentCfg['api_key'];
$originalModel = $currentCfg['model'];
$originalFallback = $currentCfg['fallback_model'];

// Simpan konfigurasi
$saveRes = $aiConfigService->saveConfig($originalKey, 'gemini-2.5-flash', 'gemini-3.5-flash-lite');
assertTest("Menyimpan konfigurasi baru ke .env berhasil", $saveRes['success'] === true);
assertTest("Runtime environment GEMINI_MODEL langsung diperbarui", getenv('GEMINI_MODEL') === 'gemini-2.5-flash');

// Kembalikan ke model semula agar stabil
$aiConfigService->saveConfig($originalKey, $originalModel, $originalFallback);
assertTest("Restorasi konfigurasi semula berhasil", getenv('GEMINI_MODEL') === $originalModel);

echo PHP_EOL;

// -----------------------------------------------------------------------------
// TAHAP 6: PENGUJIAN LARANGAN CODING PADA FAIRY & JARVIS
// -----------------------------------------------------------------------------
echo "--- TAHAP 6: PENGUJIAN LARANGAN CODING PADA FAIRY & JARVIS ---" . PHP_EOL;

AuthHelper::init();
AuthHelper::logout();

// 1. Fairy menolak coding python
$resFairyPy = $chatbotService->handleMessage("Buatkan saya kode python kalkulator sederhana");
assertTest("Fairy menolak perintah coding python", 
    $resFairyPy['success'] === true && 
    stripos($resFairyPy['reply'], 'tidak melayani permintaan coding') !== false);

// 2. Fairy menolak mengoding javascript
$resFairyJs = $chatbotService->handleMessage("Tolong koding javascript untuk form");
assertTest("Fairy menolak perintah koding javascript", 
    $resFairyJs['success'] === true && 
    stripos($resFairyJs['reply'], 'tidak melayani permintaan coding') !== false);

// 3. Jarvis menolak perintah coding untuk admin
AuthHelper::login([
    'id' => 1,
    'nama' => 'Administrator KosBook',
    'email' => 'admin@kosbook.com',
    'role' => 'admin'
]);
$resJarvisCode = $chatbotService->handleMessage("di suruh mengoding dengan python scraper");
assertTest("Jarvis menolak perintah mengoding python dari admin", 
    $resJarvisCode['success'] === true && 
    stripos($resJarvisCode['reply'], 'tidak melayani permintaan coding') !== false);

// 4. Pertanyaan valid KosBook (misal kamar atau kode booking) tidak terblokir
assertTest("Pertanyaan normal kamar tidak dianggap perintah coding", 
    $chatbotService->isCodingRequest("Berapa nomor kamar yang tersedia?") === false);
assertTest("Pertanyaan 'apa kode booking saya' tidak dianggap perintah coding", 
    $chatbotService->isCodingRequest("Apa kode booking saya?") === false);
assertTest("Pertanyaan 'Kamar apa yang masih tersedia dan berapa harganya?' tidak terblokir", 
    $chatbotService->isCodingRequest("Kamar apa yang masih tersedia dan berapa harganya?") === false);

echo PHP_EOL;
echo "=================================================================" . PHP_EOL;
echo "  SELURUH PENGUJIAN SELESAI & LULUS 100% (PASS)!                 " . PHP_EOL;
echo "=================================================================" . PHP_EOL;
