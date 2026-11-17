<?php
/**
 * ChatbotController
 * Menangani endpoint API Chatbot KosBook:
 * - POST /chatbot/message : Mengirim pesan ke AI (Fairy / Jarvis)
 * - GET  /chatbot/history : Mengambil riwayat percakapan sesi
 * - POST /chatbot/clear   : Menghapus riwayat percakapan sesi
 * 
 * Keamanan:
 * - CSRF verification
 * - Rate limiting (maks 20 request/menit)
 * - Validasi input (maks 1000 karakter, non-empty)
 * - Otorisasi role berbasis sesi server (client tidak bisa memalsukan role)
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/ChatbotService.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';

class ChatbotController extends Controller {
    private ChatbotService $chatbotService;

    public function __construct() {
        AuthHelper::init();
        $this->chatbotService = new ChatbotService();
    }

    /**
     * Endpoint utama pengiriman pesan chatbot
     * Method: POST
     */
    public function message(): void {
        header('Content-Type: application/json; charset=utf-8');

        // 1. Validasi HTTP Method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Metode HTTP tidak diizinkan. Gunakan POST.'
            ]);
            exit;
        }

        // 2. Baca payload JSON atau form-urlencoded
        $input = [];
        $rawBody = file_get_contents('php://input');
        if (!empty($rawBody)) {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }
        if (empty($input)) {
            $input = $_POST;
        }

        // 3. Validasi CSRF Token
        $csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if ($csrfToken !== null || isset($_SESSION['csrf_token'])) {
            $sessionToken = $_SESSION['csrf_token'] ?? '';
            if (empty($sessionToken) || !hash_equals($sessionToken, (string)$csrfToken)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Token CSRF tidak valid atau sesi telah kedaluwarsa. Silakan muat ulang halaman.'
                ]);
                exit;
            }
        }

        // 4. Rate Limiting (Maks 20 pesan per menit per sesi)
        if (!$this->checkRateLimit()) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => 'Anda mengirim pesan terlalu cepat. Harap tunggu beberapa detik sebelum mengirim pesan berikutnya.'
            ]);
            exit;
        }

        // 5. Validasi Pesan
        $userMessage = trim($input['message'] ?? '');
        if ($userMessage === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Pesan tidak boleh kosong.'
            ]);
            exit;
        }

        if (mb_strlen($userMessage) > 1000) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Pesan terlalu panjang (maksimal 1000 karakter).'
            ]);
            exit;
        }

        // 6. Tentukan bot berdasarkan role server yang valid
        $role = AuthHelper::role();
        $botName = ($role === 'admin') ? 'Jarvis' : 'Fairy';

        // 7. Ambil riwayat percakapan dari sesi
        $history = ChatbotService::getSessionHistory($botName);

        // 8. Proses pesan melalui ChatbotService
        $result = $this->chatbotService->handleMessage($userMessage, $history);

        if (!$result['success']) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'bot' => $result['bot'],
                'role' => $result['role'],
                'error' => $result['error']
            ]);
            exit;
        }

        // 9. Simpan percakapan ke riwayat sesi
        ChatbotService::appendSessionHistory($botName, $userMessage, $result['reply']);

        echo json_encode([
            'success' => true,
            'bot' => $result['bot'],
            'role' => $result['role'],
            'reply' => $result['reply'],
            'timestamp' => date('H:i')
        ]);
        exit;
    }

    /**
     * Endpoint mengambil riwayat pesan sesi aktif
     * Method: GET
     */
    public function history(): void {
        header('Content-Type: application/json; charset=utf-8');

        $role = AuthHelper::role();
        $botName = ($role === 'admin') ? 'Jarvis' : 'Fairy';
        $history = ChatbotService::getSessionHistory($botName);

        echo json_encode([
            'success' => true,
            'bot' => $botName,
            'role' => $role,
            'history' => $history
        ]);
        exit;
    }

    /**
     * Endpoint menghapus riwayat pesan sesi
     * Method: POST
     */
    public function clear(): void {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if ($csrfToken !== null && !empty($_SESSION['csrf_token'])) {
            if (!hash_equals($_SESSION['csrf_token'], (string)$csrfToken)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'CSRF Token Invalid']);
                exit;
            }
        }

        $role = AuthHelper::role();
        $botName = ($role === 'admin') ? 'Jarvis' : 'Fairy';
        ChatbotService::clearSessionHistory($botName);

        echo json_encode([
            'success' => true,
            'message' => "Riwayat percakapan {$botName} berhasil dibersihkan.",
            'bot' => $botName
        ]);
        exit;
    }

    /**
     * Proteksi Rate Limiting Sederhana (20 request / 60 detik)
     */
    private function checkRateLimit(): bool {
        $now = time();
        $window = 60;
        $maxRequests = 20;

        if (!isset($_SESSION['chatbot_rate_limit']) || !is_array($_SESSION['chatbot_rate_limit'])) {
            $_SESSION['chatbot_rate_limit'] = [];
        }

        // Bersihkan timestamp yang lewat dari window
        $_SESSION['chatbot_rate_limit'] = array_values(array_filter(
            $_SESSION['chatbot_rate_limit'],
            fn($timestamp) => ($now - $timestamp) < $window
        ));

        if (count($_SESSION['chatbot_rate_limit']) >= $maxRequests) {
            return false;
        }

        $_SESSION['chatbot_rate_limit'][] = $now;
        return true;
    }
}
