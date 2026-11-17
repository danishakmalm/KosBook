<?php
/**
 * ChatbotService
 * Mengorkestrasi logika percakapan AI KosBook:
 * - Menentukan identitas: Fairy 🧚 untuk Calon Penghuni / User / Guest, Jarvis 🤖 untuk Admin
 * - Verifikasi server-side role & session secara ketat
 * - Menghubungkan ChatbotDataService (akses data aman) dan GeminiService
 * - Menegakkan aturan keamanan dan pembatasan data
 */

require_once __DIR__ . '/GeminiService.php';
require_once __DIR__ . '/ChatbotDataService.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';

class ChatbotService {
    private GeminiService $gemini;
    private ChatbotDataService $dataService;

    public function __construct(?GeminiService $gemini = null, ?ChatbotDataService $dataService = null) {
        $this->gemini = $gemini ?? new GeminiService();
        $this->dataService = $dataService ?? new ChatbotDataService();
    }

    /**
     * Memproses pesan masuk dari pengguna dan mengembalikan respons yang aman
     * 
     * @param string $message Pesan pengguna
     * @param array $chatHistory Riwayat percakapan sesi pengguna
     * @return array [ 'success' => bool, 'bot' => string, 'role' => string, 'reply' => string, 'error' => ?string ]
     */
    public function handleMessage(string $message, array $chatHistory = []): array {
        $message = trim($message);
        if ($message === '') {
            return [
                'success' => false,
                'bot' => 'Chatbot',
                'role' => 'unknown',
                'reply' => '',
                'error' => 'Pesan tidak boleh kosong.'
            ];
        }

        // 1. Verifikasi role berbasis sesi server (JANGAN PERNAH percaya client)
        $role = AuthHelper::role();
        $isLoggedIn = AuthHelper::check();
        $userId = AuthHelper::userId();
        $userName = $isLoggedIn ? (AuthHelper::user()['nama'] ?? 'Pengguna') : 'Tamu (Guest)';

        // 2. Tentukan Persona & Konteks Data
        if ($role === 'admin') {
            $botName = 'Jarvis';
            $systemInstruction = $this->buildJarvisSystemInstruction($userName);
        } else {
            $botName = 'Fairy';
            $systemInstruction = $this->buildFairySystemInstruction($isLoggedIn, $userId, $userName);
        }

        // 3. Batasan Khusus: Tolak Perintah Coding / Pembuatan Program
        if ($this->isCodingRequest($message)) {
            $refusal = ($botName === 'Jarvis')
                ? "Maaf, saya adalah asisten khusus operasional KosBook dan tidak melayani permintaan coding atau pembuatan program."
                : "Maaf, saya adalah asisten khusus KosBook untuk reservasi kamar kos dan tidak melayani permintaan coding atau pembuatan program.";

            return [
                'success' => true,
                'bot' => $botName,
                'role' => $role,
                'reply' => $refusal,
                'error' => null
            ];
        }

        // 4. Panggil Gemini API
        $response = $this->gemini->generate($message, $systemInstruction, $chatHistory);

        if (!$response['success']) {
            return [
                'success' => false,
                'bot' => $botName,
                'role' => $role,
                'reply' => '',
                'error' => $response['error'] ?? 'Gagal memproses pesan dengan AI.'
            ];
        }

        return [
            'success' => true,
            'bot' => $botName,
            'role' => $role,
            'reply' => $response['reply'],
            'error' => null
        ];
    }

    /**
     * Membangun System Instruction dan Ground Truth Data untuk FAIRY 🧚
     */
    private function buildFairySystemInstruction(bool $isLoggedIn, ?int $userId, string $userName): string {
        // Ambil data kamar publik
        $availableRooms = $this->dataService->getAvailableRooms();
        $roomSummary = $this->dataService->getPublicRoomSummary();

        $roomsText = "DAFTAR KAMAR TERSEDIA SAAT INI DI KOSBOOK:\n";
        if (empty($availableRooms)) {
            $roomsText .= "- Saat ini semua kamar sedang penuh/dalam booking.\n";
        } else {
            foreach ($availableRooms as $r) {
                $roomsText .= sprintf(
                    "- %s | Tarif: %s/bulan | Fasilitas: %s | Deskripsi: %s\n",
                    $r['nomor_kamar'],
                    formatRupiah($r['harga']),
                    $r['fasilitas'] ?? '-',
                    $r['deskripsi'] ?? '-'
                );
            }
        }

        // Ambil data booking user yang sedang login (HANYA MILIK USER INI)
        $userBookingText = "";
        if ($isLoggedIn && $userId > 0) {
            $userBookings = $this->dataService->getUserBookings($userId);
            $userBookingText = "DATA BOOKING MILIK USER '{$userName}' (User ID: {$userId}):\n";
            if (empty($userBookings)) {
                $userBookingText .= "- Pengguna ini belum memiliki riwayat booking kamar.\n";
            } else {
                foreach ($userBookings as $b) {
                    $userBookingText .= sprintf(
                        "- Kode Booking #BK-00%d | Kamar: %s | Tarif: %s/bln | Masuk: %s | Sewa: %d bulan | Total: %s | Status: %s | Dibuat: %s\n",
                        $b['booking_id'],
                        $b['nomor_kamar'],
                        formatRupiah($b['harga_per_bulan']),
                        $b['tanggal_mulai'],
                        $b['lama_sewa'],
                        formatRupiah($b['total_harga']),
                        strtoupper($b['status_booking']),
                        $b['tanggal_booking']
                    );
                }
            }
        } else {
            $userBookingText = "STATUS LOGIN PENGGUNA: Belum login (Guest / Pengunjung).\n- Jika pengguna menanyakan booking pribadi atau status bookingnya, jelaskan dengan ramah bahwa ia harus login terlebih dahulu di menu Login (url: auth/login).\n";
        }

        return <<<INSTRUCTION
Kamu adalah Fairy 🧚, asisten virtual resmi untuk sistem booking kamar kos KosBook.
Karaktermu: Sopan, ringkas, akurat, to-the-point, dan langsung pada inti pertanyaan.
Gunakan Bahasa Indonesia yang baik dan lugas.

ATURAN FORMAT JAWABAN (WAJIB DIPATUHI - SANGAT RINGKAS & TO-THE-POINT):
1. JAWAB LANGSUNG pada inti pertanyaan pengguna tanpa tambahan-tambahan kata yang tidak perlu.
2. DILARANG menggunakan salam pembuka klise (misal: "Halo! Selamat datang di KosBook...", "Senang sekali kamu...", "Salam kenal...").
3. DILARANG menggunakan kalimat penutup basa-basi (misal: "Apakah ada lagi yang bisa saya bantu?", "Semoga harimu menyenangkan!", "Jika butuh info lain...", dll).
4. Berikan jawaban yang padat dan singkat. Jika ditanya harga, langsung sebutkan kamar dan harganya. Jika ditanya kamar tersedia, langsung sebutkan daftarnya secara ringkas. Jika tamu belum login dan bertanya status booking, cukup sampaikan secara singkat bahwa perlu login di menu Login (auth/login).

TUGAS DAN TANGGUNG JAWAB FAIRY:
1. Menjelaskan cara menggunakan KosBook dan alur reservasi kamar kos:
   - Pilih kamar idaman di halaman Kamar.
   - Login atau buat akun baru di KosBook.
   - Masukkan tanggal mulai sewa dan durasi sewa (dalam bulan), lalu sistem menghitung total biaya.
   - Pemilik kos (Admin) akan memverifikasi dan menyetujui pesanan.
2. Memberikan informasi tentang kamar kos yang masih TERSEDIA secara akurat berdasarkan data aktual yang disediakan di bawah.
3. Menjelaskan harga sewa, fasilitas kamar, dan deskripsi kamar secara transparan.
4. Menjelaskan status booking kamar milik pengguna yang sedang login saat ini.
5. Memberikan arahan link atau petunjuk navigasi menu di KosBook (misal: "halaman Daftar Kamar", "menu Booking Saya", atau "halaman Login").

BATASAN KETAT FAIRY (WAJIB DIPATUHI!):
1. HANYA membaca data kamar publik dan data booking milik user saat ini yang tertera di bawah. DILARANG mengarang data kamar atau status booking yang tidak ada di daftar.
2. DILARANG KERAS mengakses atau menyebutkan data booking milik orang lain, daftar seluruh pengguna, data keuangan admin, kredensial, password, token, atau API key.
3. DILARANG mengubah status kamar, harga, atau menyetujui/menolak booking. Fairy adalah asisten informasi, bukan pengubah data.
4. Jika user belum login dan bertanya tentang status booking pribadinya, beri tahu dengan sopan bahwa ia perlu login terlebih dahulu.
5. DILARANG KERAS menulis kode program, membuat script, atau mengoding dalam bahasa pemrograman apapun (Python, PHP, JavaScript, C++, dll). Kamu adalah asisten khusus KosBook, bukan AI coding. Jika ditanya atau disuruh coding, tolak dengan singkat dan tegas.

DATA AKTUAL TERKINI KOSBOOK (SUMBER KEBENARAN UTAMA):
{$roomsText}
Ringkasan: Total {$roomSummary['total_kamar']} unit kamar, {$roomSummary['kamar_tersedia']} unit tersedia. Tarif termurah: Rp 850.000, tertinggi: Rp 1.650.000.

{$userBookingText}
INSTRUCTION;
    }

    /**
     * Membangun System Instruction dan Ground Truth Data untuk JARVIS 🤖
     */
    private function buildJarvisSystemInstruction(string $adminName): string {
        // Ambil data operasional admin
        $roomStats = $this->dataService->getAdminRoomStats();
        $bookingStats = $this->dataService->getAdminBookingStats();
        $pendingBookings = $this->dataService->getPendingBookings(10);
        $recentBookings = $this->dataService->getRecentBookings(5);
        $monthlyAnalysis = $this->dataService->getMonthlyBookingAnalysis();
        $weeklyActivity = $this->dataService->getWeeklyActivity();
        $allRooms = $this->dataService->getAdminRooms();

        // Format data pending booking
        $pendingText = "BOOKING MENUNGGU KONFIRMASI (PENDING):\n";
        if (empty($pendingBookings)) {
            $pendingText .= "- Saat ini tidak ada booking yang menunggu konfirmasi (semua sudah diproses).\n";
        } else {
            foreach ($pendingBookings as $pb) {
                $pendingText .= sprintf(
                    "- ID: #BK-00%d | User: %s (%s) | Kamar: %s | Mulai: %s | Durasi: %d bln | Total: %s | Tgl: %s\n",
                    $pb['booking_id'],
                    $pb['nama_user'],
                    $pb['email_user'],
                    $pb['nomor_kamar'],
                    $pb['tanggal_mulai'],
                    $pb['lama_sewa'],
                    formatRupiah($pb['total_harga']),
                    $pb['tanggal_booking']
                );
            }
        }

        // Format data booking terbaru
        $recentText = "BOOKING TERBARU DI SISTEM:\n";
        foreach ($recentBookings as $rb) {
            $recentText .= sprintf(
                "- ID #BK-00%d | User: %s | Kamar: %s | Total: %s | Status: %s | Tgl: %s\n",
                $rb['booking_id'],
                $rb['nama_user'],
                $rb['nomor_kamar'],
                formatRupiah($rb['total_harga']),
                strtoupper($rb['status']),
                $rb['tanggal_booking']
            );
        }

        // Format kamar
        $roomsSummaryText = "STATUS SELURUH UNIT KAMAR:\n";
        foreach ($allRooms as $rm) {
            $roomsSummaryText .= sprintf(
                "- %s: Status %s, Tarif %s/bln\n",
                $rm['nomor_kamar'],
                strtoupper($rm['status']),
                formatRupiah($rm['harga'])
            );
        }

        return <<<INSTRUCTION
Kamu adalah Jarvis 🤖, asisten kecerdasan buatan operasional dan analitik khusus untuk Administrator/Pemilik KosBook.
Karaktermu: Profesional, efisien, sangat ringkas, berorientasi data, tajam, dan analitis.
Gunakan Bahasa Indonesia yang lugas dan to-the-point. Kamu berbicara kepada {$adminName} (Admin KosBook).

ATURAN FORMAT JAWABAN (WAJIB DIPATUHI - SANGAT RINGKAS & TO-THE-POINT):
1. JAWAB LANGSUNG pada poin analitik/pertanyaan admin tanpa kata-kata pengantar atau salam panjang.
2. DILARANG menggunakan kalimat penutup basa-basi (misal: "Apakah ada data lain yang ingin Anda periksa?", "Silakan beri tahu saya...").
3. Sajikan data/angka dalam format ringkas (poin-poin pendek) tanpa narasi bertele-tele.
4. Jawab hanya apa yang ditanyakan secara akurat dan efisien.

KEMAMPUAN JARVIS:
1. Menyajikan ringkasan statistik kamar: jumlah total unit, kamar tersedia, kamar booking, kamar terisi, dan persentase tingkat keterisian (occupancy rate).
2. Menyajikan data booking yang menunggu konfirmasi (pending) agar admin dapat segera mengambil tindakan di menu 'Data Booking'.
3. Menganalisis tren pemesanan: perbandingan booking bulan ini vs bulan lalu, aktivitas 7 hari terakhir.
4. Menganalisis nilai booking tercatat: berikan rincian nilai booking yang tercatat di sistem. 
   PENTING: Jelaskan secara tegas kepada admin bahwa nilai total booking adalah nilai komitmen transaksi yang tercatat di sistem KosBook, dan BUKAN otomatis pendapatan tunai yang sudah masuk rekening sebelum admin memverifikasi pembayaran fisik/transfer.
5. Memberikan rekomendasi operasional berbasis data nyata (misal: jika okupansi tinggi, rekomendasi penetapan harga; jika ada booking menunggu, segera arahkan admin untuk meninjau).
6. Mengarahkan admin ke menu navigasi resmi (Dashboard Admin, Data Kamar di 'kamar/admin', Data Booking di 'booking/admin').

BATASAN KETAT JARVIS (WAJIB DIPATUHI!):
1. Jarvis TIDAK MEMILIKI akses untuk mengeksekusi SQL langsung atau memodifikasi data database.
2. DILARANG KERAS menyetujui, menolak, atau menghapus booking/kamar secara langsung melalui chat AI. Jika admin meminta "tolak booking X" atau "setujui booking Y", beri tahu dengan sopan dan arahkan admin untuk menekan tombol aksi konfirmasi/tolak di halaman resmi 'Data Booking' (url: booking/admin).
3. DILARANG mengungkapkan password, password hash, API key, token rahasia, kredensial database, atau session ID.
4. Hanya gunakan angka aktual dari DATA OPERASIONAL RESMI di bawah ini. Jangan mengarang angka atau statistik.
5. DILARANG KERAS menulis kode program, membuat script, atau mengoding dalam bahasa pemrograman apapun (Python, PHP, JavaScript, SQL eksternal, dll). Jarvis adalah asisten operasional KosBook, bukan AI coding. Jika diminta coding/script, tolak dengan singkat dan tegas.

DATA OPERASIONAL RESMI KOSBOOK (REAL-TIME DARI DATABASE):
A. STATISTIK KAMAR:
- Total Kamar: {$roomStats['total_kamar']} unit
- Kamar Tersedia: {$roomStats['kamar_tersedia']} unit
- Kamar Booking: {$roomStats['kamar_booking']} unit
- Kamar Terisi: {$roomStats['kamar_terisi']} unit
- Tingkat Keterisian (Occupancy Rate): {$roomStats['tingkat_keterisian_persen']}%

B. STATISTIK BOOKING:
- Total Booking Seluruh Riwayat: {$bookingStats['total_booking']} transaksi
- Booking Menunggu Konfirmasi: {$bookingStats['booking_menunggu']} transaksi
- Booking Dikonfirmasi: {$bookingStats['booking_dikonfirmasi']} transaksi
- Booking Ditolak: {$bookingStats['booking_ditolak']} transaksi
- Booking Selesai: {$bookingStats['booking_selesai']} transaksi
- Total Nilai Komitmen Booking Tercatat: Rp {$bookingStats['total_nilai_tercatat']}
- Total Nilai Booking Berstatus Dikonfirmasi: Rp {$bookingStats['total_nilai_dikonfirmasi']}

C. {$pendingText}

D. {$recentText}

E. PERBANDINGAN BULAN INI VS BULAN LALU:
- Bulan Ini: {$monthlyAnalysis['bulan_ini']['jumlah_booking']} booking (Total tercatat: Rp {$monthlyAnalysis['bulan_ini']['total_nilai_tercatat']})
- Bulan Lalu: {$monthlyAnalysis['bulan_lalu']['jumlah_booking']} booking (Total tercatat: Rp {$monthlyAnalysis['bulan_lalu']['total_nilai_tercatat']})

F. AKTIVITAS 7 HARI TERAKHIR:
- Booking Baru: {$weeklyActivity['booking_7_hari_terakhir']} transaksi
- Menunggu Konfirmasi: {$weeklyActivity['menunggu']}
- Dikonfirmasi: {$weeklyActivity['dikonfirmasi']}

G. {$roomsSummaryText}
INSTRUCTION;
    }

    /**
     * Mengambil riwayat percakapan sesi pengguna
     */
    public static function getSessionHistory(string $bot): array {
        AuthHelper::init();
        $botKey = strtolower($bot) === 'jarvis' ? 'jarvis' : 'fairy';
        return $_SESSION['chatbot_history'][$botKey] ?? [];
    }

    /**
     * Menyimpan riwayat percakapan ke sesi pengguna (dibatasi 10 interaksi terakhir)
     */
    public static function appendSessionHistory(string $bot, string $userText, string $botText): void {
        AuthHelper::init();
        $botKey = strtolower($bot) === 'jarvis' ? 'jarvis' : 'fairy';
        if (!isset($_SESSION['chatbot_history'][$botKey])) {
            $_SESSION['chatbot_history'][$botKey] = [];
        }

        $_SESSION['chatbot_history'][$botKey][] = ['role' => 'user', 'text' => $userText];
        $_SESSION['chatbot_history'][$botKey][] = ['role' => 'model', 'text' => $botText];

        // Batasi maksimal 10 pasang (20 elemen)
        if (count($_SESSION['chatbot_history'][$botKey]) > 20) {
            $_SESSION['chatbot_history'][$botKey] = array_slice($_SESSION['chatbot_history'][$botKey], -20);
        }
    }

    /**
     * Menghapus riwayat percakapan sesi
     */
    public static function clearSessionHistory(string $bot): void {
        AuthHelper::init();
        $botKey = strtolower($bot) === 'jarvis' ? 'jarvis' : 'fairy';
        unset($_SESSION['chatbot_history'][$botKey]);
    }

    /**
     * Memeriksa apakah pesan pengguna merupakan perintah untuk coding / pemrograman
     */
    public function isCodingRequest(string $message): bool {
        $msg = strtolower(trim($message));
        
        // Kecualikan konteks internal sistem kos seperti "kode booking"
        $cleanMsg = preg_replace('/\bkode\s+booking\b/i', '', $msg);
        
        // 1. Kata kunci eksplisit seputar aktivitas coding (mengoding, ngoding, koding, kodingkan, coding)
        if (preg_match('/(coding|ngoding|mengoding|koding)/i', $cleanMsg)) {
            return true;
        }
        
        // 2. Permintaan pembuatan script / kode / fungsi
        if (preg_match('/(buatkan|bikin|tulis|tuliskan|berikan|generate|create|write|minta|tolong)\s+.*?(kode|code|script|skrip|program|fungsi|function|algoritma)/i', $cleanMsg)) {
            return true;
        }
        
        // 3. Permintaan bahasa pemrograman spesifik
        if (preg_match('/(python|javascript|typescript|c\+\+|golang|java|php|rust|html|css)/i', $cleanMsg)) {
            if (preg_match('/(kode|code|script|skrip|program|buat|bikin|tulis|fungsi|function|contoh|kalkulator|loop|array|def |print\(|class |scraper)/i', $cleanMsg)) {
                return true;
            }
        }
        
        return false;
    }
}
