<?php
/**
 * DashboardController
 * Mengelola antarmuka Dashboard User (Penghuni) dan Dashboard Admin (Pemilik Kos)
 * Dilengkapi dengan proteksi otorisasi berbasis peran (Role-Based Access Control)
 */

class DashboardController extends Controller {

    /**
     * Dashboard User (Penghuni)
     * Hanya dapat diakses oleh pengguna dengan role 'user'
     */
    public function user(): void {
        // Access Control: Hanya role 'user' yang diizinkan
        AuthHelper::requireRole('user');

        $sessionUser = AuthHelper::user();
        $userId = (int) $sessionUser['id'];

        // Ambil data profil user lengkap dari UserModel
        $userModel = $this->model('UserModel');
        $fullUser = $userModel->findById($userId);
        $user = $fullUser ?: $sessionUser;

        // Ambil data booking milik user dari database via BookingModel (Hanya milik user_id ini)
        $bookingModel = $this->model('BookingModel');
        $userBookings = $bookingModel->getByUserId($userId);

        // Hitung statistik sederhana
        $stats = [
            'total' => count($userBookings),
            'menunggu' => count(array_filter($userBookings, fn($b) => $b['status'] === 'menunggu')),
            'dikonfirmasi' => count(array_filter($userBookings, fn($b) => $b['status'] === 'dikonfirmasi')),
            'ditolak' => count(array_filter($userBookings, fn($b) => $b['status'] === 'ditolak')),
            'selesai' => count(array_filter($userBookings, fn($b) => $b['status'] === 'selesai')),
        ];

        // Pisahkan kategori booking
        $activeBookings = array_values(array_filter($userBookings, fn($b) => $b['status'] === 'dikonfirmasi'));
        $pendingBookings = array_values(array_filter($userBookings, fn($b) => $b['status'] === 'menunggu'));

        $data = [
            'title' => 'Dashboard Penghuni - KosBook',
            'activePage' => 'booking_saya',
            'user' => $user,
            'stats' => $stats,
            'bookings' => $userBookings,
            'activeBookings' => $activeBookings,
            'pendingBookings' => $pendingBookings,
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('dashboard/user', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Dashboard Admin (Pengelola Kos)
     * Hanya dapat diakses oleh pengguna dengan role 'admin'
     */
    public function admin(): void {
        // Access Control: Hanya role 'admin' yang diizinkan setelah login
        AuthHelper::requireRole('admin');

        $currentUser = AuthHelper::user();

        // Ambil data users langsung dari database via UserModel
        $userModel = $this->model('UserModel');
        $users = $userModel->getAllUsers();

        // Ambil data kamar langsung dari database via KamarModel
        $kamarModel = $this->model('KamarModel');
        $rooms = $kamarModel->getAll();

        // Ambil data bookings langsung dari database via BookingModel
        $bookingModel = $this->model('BookingModel');
        $bookings = $bookingModel->getAllBookings();

        // 7 Statistik lengkap sesuai spesifikasi:
        // Total Kamar, Kamar Tersedia, Kamar Booking, Kamar Terisi, Total User, Total Booking, Booking Menunggu
        $stats = [
            'total_kamar' => count($rooms),
            'kamar_tersedia' => count(array_filter($rooms, fn($r) => $r['status'] === 'tersedia')),
            'kamar_booking' => count(array_filter($rooms, fn($r) => $r['status'] === 'booking')),
            'kamar_terisi' => count(array_filter($rooms, fn($r) => $r['status'] === 'terisi')),
            'total_user' => count(array_filter($users, fn($u) => $u['role'] === 'user')),
            'total_booking' => count($bookings),
            'booking_menunggu' => count(array_filter($bookings, fn($b) => $b['status'] === 'menunggu')),
        ];

        // Ambil data terbaru (teratas)
        $recentBookings = array_slice($bookings, 0, 5);
        $recentRooms = array_slice($rooms, 0, 5);

        // Konfigurasi AI Chatbot (Gemini API)
        require_once __DIR__ . '/../services/AiConfigService.php';
        $aiConfigService = new AiConfigService();
        $aiConfig = $aiConfigService->getCurrentConfig();

        $data = [
            'title' => 'Dashboard Pengelola Kos - KosBook',
            'activePage' => 'admin_dashboard',
            'currentAdmin' => $currentUser,
            'stats' => $stats,
            'rooms' => $rooms,
            'recentRooms' => $recentRooms,
            'bookings' => $bookings,
            'recentBookings' => $recentBookings,
            'users' => $users,
            'aiConfig' => $aiConfig,
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('dashboard/admin', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Endpoint API untuk mengambil daftar model AI yang tersedia dari Google Gemini
     * URL: ?url=dashboard/get_ai_models
     */
    public function get_ai_models(): void {
        AuthHelper::requireRole('admin');
        header('Content-Type: application/json');

        require_once __DIR__ . '/../services/AiConfigService.php';
        $aiConfigService = new AiConfigService();

        $apiKey = trim($_POST['api_key'] ?? $_GET['api_key'] ?? '');
        $result = $aiConfigService->fetchAvailableModels($apiKey ?: null);

        echo json_encode($result);
        exit;
    }

    /**
     * Endpoint API untuk menguji koneksi API key dan model yang dipilih
     * URL: ?url=dashboard/test_ai_connection
     */
    public function test_ai_connection(): void {
        AuthHelper::requireRole('admin');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Metode request harus POST.']);
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Token CSRF tidak valid atau sesi berakhir.']);
            exit;
        }

        $apiKey = trim($_POST['api_key'] ?? '');
        $model = trim($_POST['model'] ?? '');

        require_once __DIR__ . '/../services/AiConfigService.php';
        $aiConfigService = new AiConfigService();

        $result = $aiConfigService->testConnection($apiKey, $model);
        echo json_encode($result);
        exit;
    }

    /**
     * Menyimpan pembaruan konfigurasi Gemini API Key dan pilihan Model
     * URL: ?url=dashboard/simpan_ai_config
     */
    public function simpan_ai_config(): void {
        AuthHelper::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('dashboard/admin'));
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($csrfToken)) {
            AuthHelper::setFlash('danger', 'Sesi Anda telah kedaluwarsa atau token tidak valid. Silakan coba lagi.');
            header('Location: ' . url('dashboard/admin#konfigurasi-ai'));
            exit;
        }

        $apiKey = trim($_POST['gemini_api_key'] ?? '');
        $primaryModel = trim($_POST['gemini_model'] ?? '');
        $fallbackModel = trim($_POST['gemini_fallback_model'] ?? '');

        if (empty($apiKey)) {
            AuthHelper::setFlash('danger', 'API Key Gemini tidak boleh kosong.');
            header('Location: ' . url('dashboard/admin#konfigurasi-ai'));
            exit;
        }

        if (empty($primaryModel)) {
            AuthHelper::setFlash('danger', 'Model utama AI harus dipilih.');
            header('Location: ' . url('dashboard/admin#konfigurasi-ai'));
            exit;
        }

        require_once __DIR__ . '/../services/AiConfigService.php';
        $aiConfigService = new AiConfigService();
        $result = $aiConfigService->saveConfig($apiKey, $primaryModel, $fallbackModel);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        if ($result['success']) {
            AuthHelper::setFlash('success', 'Konfigurasi Google Gemini API Key & Model berhasil disimpan!');
        } else {
            AuthHelper::setFlash('danger', $result['error'] ?? 'Gagal menyimpan konfigurasi.');
        }

        header('Location: ' . url('dashboard/admin#konfigurasi-ai'));
        exit;
    }
}
