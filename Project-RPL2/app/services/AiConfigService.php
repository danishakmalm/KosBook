<?php
/**
 * AiConfigService
 * Layanan manajemen konfigurasi Google Gemini API:
 * - Membaca dan memperbarui variabel environment (.env)
 * - Mengambil daftar model yang aktif & didukung oleh API Key dari Google Gemini API
 * - Menguji konektivitas model dengan prompt verifikasi
 */

class AiConfigService {
    private string $envPath;

    public function __construct(?string $envPath = null) {
        $this->envPath = $envPath ?? dirname(__DIR__, 2) . '/.env';
    }

    /**
     * Mengambil konfigurasi AI saat ini
     */
    public function getCurrentConfig(): array {
        $apiKey = getenv('GEMINI_API_KEY') ?: (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '');
        $model = getenv('GEMINI_MODEL') ?: (defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-3.5-flash');
        $fallbackModel = getenv('GEMINI_FALLBACK_MODEL') ?: (defined('GEMINI_FALLBACK_MODEL') ? GEMINI_FALLBACK_MODEL : 'gemini-3.5-flash-lite');

        $maskedKey = '';
        if (!empty($apiKey)) {
            $len = strlen($apiKey);
            if ($len > 12) {
                $maskedKey = substr($apiKey, 0, 8) . str_repeat('•', max(4, $len - 14)) . substr($apiKey, -6);
            } else {
                $maskedKey = str_repeat('•', $len);
            }
        }

        return [
            'api_key' => $apiKey,
            'masked_api_key' => $maskedKey,
            'model' => $model,
            'fallback_model' => $fallbackModel,
            'is_configured' => !empty(trim($apiKey))
        ];
    }

    /**
     * Mengambil daftar model yang didukung dari Google Gemini API endpoint:
     * GET https://generativelanguage.googleapis.com/v1beta/models
     * 
     * @param string|null $apiKey Jika null, gunakan API key yang tersimpan di sistem
     * @return array [ 'success' => bool, 'models' => array, 'count' => int, 'error' => ?string ]
     */
    public function fetchAvailableModels(?string $apiKey = null): array {
        if ($apiKey === null) {
            $keyToUse = trim($this->getCurrentConfig()['api_key']);
        } else {
            $keyToUse = trim($apiKey);
        }

        if (empty($keyToUse)) {
            return [
                'success' => false,
                'models' => [],
                'count' => 0,
                'error' => 'API Key belum diisi. Masukkan API Key terlebih dahulu.'
            ];
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $keyToUse
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);

        $rawResponse = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false || !empty($curlError)) {
            return [
                'success' => false,
                'models' => [],
                'count' => 0,
                'error' => 'Koneksi ke Google AI gagal: ' . ($curlError ?: 'Network error')
            ];
        }

        $data = json_decode($rawResponse, true);

        if ($httpCode !== 200 || !isset($data['models'])) {
            $errorMsg = $data['error']['message'] ?? 'Gagal mengambil daftar model dari Google API (HTTP ' . $httpCode . ').';
            if ($httpCode === 400 || $httpCode === 403) {
                $errorMsg = 'API Key Gemini tidak valid atau tidak memiliki akses (HTTP ' . $httpCode . ').';
            }
            return [
                'success' => false,
                'models' => [],
                'count' => 0,
                'error' => $errorMsg
            ];
        }

        $availableModels = [];
        foreach ($data['models'] as $m) {
            $methods = $m['supportedGenerationMethods'] ?? [];
            // Hanya ambil model yang mendukung pembuatan konten teks (generateContent)
            if (in_array('generateContent', $methods)) {
                $rawName = $m['name'] ?? '';
                $cleanId = str_replace('models/', '', $rawName);
                
                $availableModels[] = [
                    'id' => $cleanId,
                    'displayName' => $m['displayName'] ?? $cleanId,
                    'description' => $m['description'] ?? '',
                    'inputTokenLimit' => $m['inputTokenLimit'] ?? null,
                    'outputTokenLimit' => $m['outputTokenLimit'] ?? null,
                    'is_recommended' => in_array($cleanId, ['gemini-2.5-flash', 'gemini-3.5-flash', 'gemini-3.5-flash-lite', 'gemini-2.5-pro', 'gemini-flash-latest'])
                ];
            }
        }

        // Urutkan model rekomendasi di urutan teratas
        usort($availableModels, function ($a, $b) {
            if ($a['is_recommended'] === $b['is_recommended']) {
                return strcmp($a['id'], $b['id']);
            }
            return $a['is_recommended'] ? -1 : 1;
        });

        return [
            'success' => true,
            'models' => $availableModels,
            'count' => count($availableModels),
            'error' => null
        ];
    }

    /**
     * Menguji koneksi dengan prompt singkat ke model yang dipilih
     */
    public function testConnection(string $apiKey, string $model): array {
        $apiKey = trim($apiKey);
        $model = trim($model);

        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'API Key wajib diisi untuk melakukan pengujian.'];
        }
        if (empty($model)) {
            return ['success' => false, 'error' => 'Nama model belum dipilih.'];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => 'Jawab dengan 1 kata saja: Siap']
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 30,
                'temperature' => 0.2
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);

        $rawResponse = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false || !empty($curlError)) {
            return [
                'success' => false,
                'error' => 'Koneksi timeout/gagal: ' . ($curlError ?: 'Network error')
            ];
        }

        $data = json_decode($rawResponse, true);

        if ($httpCode === 200 && !empty($data['candidates'][0]['content']['parts'][0]['text'])) {
            $reply = trim($data['candidates'][0]['content']['parts'][0]['text']);
            return [
                'success' => true,
                'reply' => $reply,
                'model' => $model,
                'message' => "Koneksi berhasil! Model {$model} merespons normal: \"{$reply}\""
            ];
        }

        $errorMsg = $data['error']['message'] ?? "Pengujian gagal dengan kode HTTP {$httpCode}.";
        return [
            'success' => false,
            'error' => $errorMsg
        ];
    }

    /**
     * Menyimpan konfigurasi baru ke file .env dan memperbarui environment runtime
     */
    public function saveConfig(string $apiKey, string $model, ?string $fallbackModel = null): array {
        $apiKey = trim($apiKey);
        $model = trim($model);
        $fallbackModel = trim($fallbackModel ?? '');

        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'API Key tidak boleh kosong.'];
        }
        if (empty($model)) {
            return ['success' => false, 'error' => 'Model utama tidak boleh kosong.'];
        }
        if (empty($fallbackModel)) {
            $fallbackModel = $model;
        }

        $envFile = $this->envPath;
        $content = '';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES);
            $foundApiKey = false;
            $foundModel = false;
            $foundFallback = false;

            $newLines = [];
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (str_starts_with($trimmed, 'GEMINI_API_KEY=')) {
                    $newLines[] = "GEMINI_API_KEY={$apiKey}";
                    $foundApiKey = true;
                } elseif (str_starts_with($trimmed, 'GEMINI_MODEL=')) {
                    $newLines[] = "GEMINI_MODEL={$model}";
                    $foundModel = true;
                } elseif (str_starts_with($trimmed, 'GEMINI_FALLBACK_MODEL=')) {
                    $newLines[] = "GEMINI_FALLBACK_MODEL={$fallbackModel}";
                    $foundFallback = true;
                } else {
                    $newLines[] = $line;
                }
            }

            if (!$foundApiKey) {
                $newLines[] = "GEMINI_API_KEY={$apiKey}";
            }
            if (!$foundModel) {
                $newLines[] = "GEMINI_MODEL={$model}";
            }
            if (!$foundFallback) {
                $newLines[] = "GEMINI_FALLBACK_MODEL={$fallbackModel}";
            }

            $content = implode("\n", $newLines) . "\n";
        } else {
            // Jika file .env belum ada, buat baru
            $content = "# ==============================================================================\n"
                     . "# KOSBOOK - ENVIRONMENT SECRETS\n"
                     . "# ==============================================================================\n\n"
                     . "GEMINI_API_KEY={$apiKey}\n"
                     . "GEMINI_MODEL={$model}\n"
                     . "GEMINI_FALLBACK_MODEL={$fallbackModel}\n\n"
                     . "DB_HOST=" . (getenv('DB_HOST') ?: 'localhost') . "\n"
                     . "DB_USER=" . (getenv('DB_USER') ?: 'root') . "\n"
                     . "DB_PASS=" . (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '') . "\n"
                     . "DB_NAME=" . (getenv('DB_NAME') ?: 'kosbook_db') . "\n"
                     . "DB_PORT=" . (getenv('DB_PORT') ?: 3306) . "\n";
        }

        $saved = @file_put_contents($envFile, $content);
        if ($saved === false) {
            return [
                'success' => false,
                'error' => "Gagal menulis file konfigurasi .env. Pastikan server memiliki izin tulis ke direktori proyek."
            ];
        }

        // Perbarui environment runtime saat ini
        putenv("GEMINI_API_KEY={$apiKey}");
        $_ENV['GEMINI_API_KEY'] = $apiKey;
        $_SERVER['GEMINI_API_KEY'] = $apiKey;

        putenv("GEMINI_MODEL={$model}");
        $_ENV['GEMINI_MODEL'] = $model;
        $_SERVER['GEMINI_MODEL'] = $model;

        putenv("GEMINI_FALLBACK_MODEL={$fallbackModel}");
        $_ENV['GEMINI_FALLBACK_MODEL'] = $fallbackModel;
        $_SERVER['GEMINI_FALLBACK_MODEL'] = $fallbackModel;

        return [
            'success' => true,
            'message' => 'Konfigurasi Google Gemini API Key & Model berhasil diperbarui dan disimpan!'
        ];
    }
}
