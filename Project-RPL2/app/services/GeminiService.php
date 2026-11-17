<?php
/**
 * GeminiService
 * Layanan komunikasi dengan Google Gemini API (v1beta REST API).
 * Menangani request generation, timeouts, error status, dan failover model secara transparan.
 */

class GeminiService {
    private string $apiKey;
    private string $primaryModel;
    private string $fallbackModel;
    private float|int $timeoutSeconds;

    public function __construct(
        ?string $apiKey = null, 
        ?string $primaryModel = null, 
        ?string $fallbackModel = null, 
        float|int $timeoutSeconds = 20
    ) {
        $this->apiKey = $apiKey ?? (getenv('GEMINI_API_KEY') ?: (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : ''));
        $this->primaryModel = $primaryModel ?? (getenv('GEMINI_MODEL') ?: (defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-3.5-flash'));
        $this->fallbackModel = $fallbackModel ?? (getenv('GEMINI_FALLBACK_MODEL') ?: (defined('GEMINI_FALLBACK_MODEL') ? GEMINI_FALLBACK_MODEL : 'gemini-3.5-flash-lite'));
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * Memeriksa ketersediaan API key
     */
    public function isConfigured(): bool {
        return !empty(trim($this->apiKey));
    }

    /**
     * Mengirim pesan percakapan ke Gemini API dan mengembalikan balasan teks
     * 
     * @param string $userMessage Pesan dari pengguna
     * @param string $systemInstruction Instruksi sistem/karakter bot
     * @param array $chatHistory Riwayat chat sebelumnya [ ['role' => 'user'|'model', 'text' => '...'], ... ]
     * @return array [ 'success' => bool, 'reply' => string, 'error' => ?string, 'model' => string ]
     */
    public function generate(string $userMessage, string $systemInstruction = '', array $chatHistory = []): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'reply' => '',
                'error' => 'API Key Gemini belum dikonfigurasi di server. Silakan hubungi administrator.',
                'model' => $this->primaryModel
            ];
        }

        // Siapkan struktur payload Gemini
        $contents = $this->buildContents($userMessage, $chatHistory);
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.35,
                'topP' => 0.9,
                'maxOutputTokens' => 500
            ]
        ];

        if (!empty(trim($systemInstruction))) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => trim($systemInstruction)]
                ]
            ];
        }

        // 1. Coba request menggunakan Primary Model
        $result = $this->executeCurl($this->primaryModel, $payload);

        // 2. Jika primary model gagal karena 429 (Rate Limit), 503 (Overloaded), 404 (Not Found), atau 0 (Network Timeout)
        if (!$result['success'] && in_array($result['http_code'], [429, 503, 404, 0]) && $this->fallbackModel !== $this->primaryModel) {
            error_log("GeminiService: Primary model {$this->primaryModel} gagal (HTTP {$result['http_code']}). Mencoba fallback ke {$this->fallbackModel}...");
            $fallbackResult = $this->executeCurl($this->fallbackModel, $payload);
            if ($fallbackResult['success']) {
                return [
                    'success' => true,
                    'reply' => $fallbackResult['text'],
                    'error' => null,
                    'model' => $this->fallbackModel
                ];
            }
        }

        if ($result['success']) {
            return [
                'success' => true,
                'reply' => $result['text'],
                'error' => null,
                'model' => $this->primaryModel
            ];
        }

        return [
            'success' => false,
            'reply' => '',
            'error' => $result['error'],
            'model' => $this->primaryModel
        ];
    }

    /**
     * Membentuk urutan contents yang valid untuk Gemini API:
     * - Role harus bergantian antara 'user' dan 'model'
     * - Elemen terakhir harus role 'user'
     */
    private function buildContents(string $userMessage, array $chatHistory): array {
        $contents = [];
        $lastRole = null;

        // Masukkan riwayat percakapan (maksimal 6 percakapan terakhir)
        $trimmedHistory = array_slice($chatHistory, -6);
        foreach ($trimmedHistory as $item) {
            $role = ($item['role'] === 'model' || $item['role'] === 'assistant') ? 'model' : 'user';
            $text = trim($item['text'] ?? '');
            if ($text === '') {
                continue;
            }

            // Hindari duplikasi role berurutan
            if ($role === $lastRole) {
                // Tambahkan teks ke part terakhir jika role sama
                $lastIdx = count($contents) - 1;
                $contents[$lastIdx]['parts'][0]['text'] .= "\n" . $text;
            } else {
                $contents[] = [
                    'role' => $role,
                    'parts' => [
                        ['text' => $text]
                    ]
                ];
                $lastRole = $role;
            }
        }

        // Tambahkan user message saat ini
        if ($lastRole === 'user') {
            $lastIdx = count($contents) - 1;
            $contents[$lastIdx]['parts'][0]['text'] .= "\n" . trim($userMessage);
        } else {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => trim($userMessage)]
                ]
            ];
        }

        return $contents;
    }

    /**
     * Eksekusi cURL HTTP POST ke endpoint Gemini
     */
    private function executeCurl(string $model, array $payload): array {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
        $jsonData = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $this->apiKey
        ]);
        if ($this->timeoutSeconds < 1) {
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, max(1, (int)($this->timeoutSeconds * 1000)));
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        } else {
            curl_setopt($ch, CURLOPT_TIMEOUT, (int)$this->timeoutSeconds);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(10, (int)$this->timeoutSeconds));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Portabilitas lokal XAMPP

        $rawResponse = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // 1. Error Jaringan / cURL Timeout
        if ($rawResponse === false || !empty($curlError)) {
            error_log("GeminiService cURL Error: " . $curlError);
            $isTimeout = (stripos($curlError, 'timed out') !== false || stripos($curlError, 'timeout') !== false);
            $userMsg = $isTimeout 
                ? 'Koneksi ke server AI melebihi batas waktu (timeout). Silakan coba ajukan pertanyaan Anda kembali.' 
                : 'Tidak dapat terhubung ke server AI. Periksa koneksi internet server Anda (' . htmlspecialchars($curlError) . ').';
            return [
                'success' => false,
                'http_code' => 0,
                'error' => $userMsg
            ];
        }

        // 2. Parse response JSON
        $responseData = json_decode($rawResponse, true);

        // 3. Tangani HTTP Status Code
        if ($httpCode === 200 && is_array($responseData)) {
            $replyText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($replyText !== null && trim($replyText) !== '') {
                return [
                    'success' => true,
                    'http_code' => 200,
                    'text' => trim($replyText)
                ];
            } else {
                // Bisa terjadi jika terkena filter safety
                $finishReason = $responseData['candidates'][0]['finishReason'] ?? 'UNKNOWN';
                return [
                    'success' => false,
                    'http_code' => 200,
                    'error' => "Jawaban AI tidak dapat ditampilkan (Reason: {$finishReason}). Silakan gunakan kalimat lain."
                ];
            }
        }

        // 4. Tangani error spesifik dari Google API
        $apiErrorMsg = $responseData['error']['message'] ?? 'Terjadi kesalahan pada layanan AI.';
        error_log("Gemini API Error (HTTP {$httpCode}): " . $apiErrorMsg);

        switch ($httpCode) {
            case 400:
                $friendly = 'Format permintaan tidak dikenali oleh AI. Silakan coba kembali.';
                break;
            case 403:
                $friendly = 'Autentikasi API Key Gemini tidak valid atau tidak memiliki akses.';
                break;
            case 404:
                $friendly = "Model AI '{$model}' tidak ditemukan atau sedang dinonaktifkan.";
                break;
            case 429:
                $friendly = 'Batas kuota request AI terlampaui (Rate Limit). Mohon tunggu beberapa saat sebelum mencoba kembali.';
                break;
            case 500:
            case 503:
                $friendly = 'Layanan AI Google saat ini sedang mengalami lonjakan lalu lintas. Silakan coba sesaat lagi.';
                break;
            default:
                $friendly = 'Terjadi kendala saat menghubungkan ke asisten AI (' . htmlspecialchars($apiErrorMsg) . ').';
                break;
        }

        return [
            'success' => false,
            'http_code' => $httpCode,
            'error' => $friendly
        ];
    }
}
