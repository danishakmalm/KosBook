<?php
/**
 * ChatbotDataService
 * Layanan pengambilan data database yang aman dan terkontrol untuk Chatbot KosBook.
 * Menggunakan PDO prepared statements melalui Database wrapper.
 * Tidak ada query SQL mentah yang dapat dieksekusi dari AI.
 */

require_once __DIR__ . '/../core/Database.php';

class ChatbotDataService {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    // =========================================================================
    // FUNGSI UNTUK FAIRY (DATA PUBLIK & DATA MILIK USER YANG SEDANG LOGIN)
    // =========================================================================

    /**
     * Mendapatkan daftar kamar yang berstatus 'tersedia' untuk publik/calon penghuni
     * Hanya field publik yang disertakan (tidak ada data internal atau kredensial)
     */
    public function getAvailableRooms(): array {
        $this->db->query("SELECT id, nomor_kamar, harga, fasilitas, deskripsi, status 
                          FROM kamar 
                          WHERE status = 'tersedia' 
                          ORDER BY harga ASC");
        return $this->db->resultSet();
    }

    /**
     * Mendapatkan semua kamar dengan statusnya untuk konteks informasi ketersediaan
     */
    public function getAllPublicRooms(): array {
        $this->db->query("SELECT id, nomor_kamar, harga, fasilitas, deskripsi, status 
                          FROM kamar 
                          ORDER BY nomor_kamar ASC");
        return $this->db->resultSet();
    }

    /**
     * Mendapatkan ringkasan statistik kamar publik
     */
    public function getPublicRoomSummary(): array {
        $this->db->query("SELECT 
                            COUNT(*) as total_kamar,
                            SUM(CASE WHEN status = 'tersedia' THEN 1 ELSE 0 END) as kamar_tersedia,
                            MIN(CASE WHEN status = 'tersedia' THEN harga ELSE NULL END) as harga_termurah,
                            MAX(CASE WHEN status = 'tersedia' THEN harga ELSE NULL END) as harga_termahal
                          FROM kamar");
        $res = $this->db->single();
        return [
            'total_kamar' => (int)($res['total_kamar'] ?? 0),
            'kamar_tersedia' => (int)($res['kamar_tersedia'] ?? 0),
            'harga_termurah' => (float)($res['harga_termurah'] ?? 0),
            'harga_termahal' => (float)($res['harga_termahal'] ?? 0),
        ];
    }

    /**
     * Mendapatkan daftar booking KHUSUS milik user yang sedang login
     * Filter ketat berdasarkan user_id dari sesi server
     */
    public function getUserBookings(int $userId): array {
        if ($userId <= 0) {
            return [];
        }

        $this->db->query("SELECT 
                            b.id as booking_id,
                            b.tanggal_booking,
                            b.tanggal_mulai,
                            b.lama_sewa,
                            b.total_harga,
                            b.status as status_booking,
                            k.nomor_kamar,
                            k.harga as harga_per_bulan,
                            k.fasilitas
                          FROM booking b
                          JOIN kamar k ON b.kamar_id = k.id
                          WHERE b.user_id = :user_id
                          ORDER BY b.id DESC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Mendapatkan ringkasan status booking milik user
     */
    public function getUserBookingSummary(int $userId): array {
        if ($userId <= 0) {
            return ['total' => 0, 'menunggu' => 0, 'dikonfirmasi' => 0, 'ditolak' => 0, 'selesai' => 0];
        }

        $bookings = $this->getUserBookings($userId);
        return [
            'total' => count($bookings),
            'menunggu' => count(array_filter($bookings, fn($b) => $b['status_booking'] === 'menunggu')),
            'dikonfirmasi' => count(array_filter($bookings, fn($b) => $b['status_booking'] === 'dikonfirmasi')),
            'ditolak' => count(array_filter($bookings, fn($b) => $b['status_booking'] === 'ditolak')),
            'selesai' => count(array_filter($bookings, fn($b) => $b['status_booking'] === 'selesai')),
        ];
    }

    // =========================================================================
    // FUNGSI UNTUK JARVIS (DATA OPERASIONAL ADMIN - HANYA SETELAH VERIFIKASI ADMIN)
    // =========================================================================

    /**
     * Statistik Kamar Lengkap & Tingkat Keterisian untuk Admin
     */
    public function getAdminRoomStats(): array {
        $this->db->query("SELECT 
                            COUNT(*) as total_kamar,
                            SUM(CASE WHEN status = 'tersedia' THEN 1 ELSE 0 END) as kamar_tersedia,
                            SUM(CASE WHEN status = 'booking' THEN 1 ELSE 0 END) as kamar_booking,
                            SUM(CASE WHEN status = 'terisi' THEN 1 ELSE 0 END) as kamar_terisi
                          FROM kamar");
        $res = $this->db->single();
        $total = (int)($res['total_kamar'] ?? 0);
        $terisi = (int)($res['kamar_terisi'] ?? 0);
        $occupancyRate = ($total > 0) ? round(($terisi / $total) * 100, 1) : 0;

        return [
            'total_kamar' => $total,
            'kamar_tersedia' => (int)($res['kamar_tersedia'] ?? 0),
            'kamar_booking' => (int)($res['kamar_booking'] ?? 0),
            'kamar_terisi' => $terisi,
            'tingkat_keterisian_persen' => $occupancyRate
        ];
    }

    /**
     * Statistik Booking Lengkap untuk Admin
     */
    public function getAdminBookingStats(): array {
        $this->db->query("SELECT 
                            COUNT(*) as total_booking,
                            SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as booking_menunggu,
                            SUM(CASE WHEN status = 'dikonfirmasi' THEN 1 ELSE 0 END) as booking_dikonfirmasi,
                            SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as booking_ditolak,
                            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as booking_selesai,
                            COALESCE(SUM(total_harga), 0) as total_nilai_semua_booking,
                            COALESCE(SUM(CASE WHEN status = 'dikonfirmasi' THEN total_harga ELSE 0 END), 0) as total_nilai_dikonfirmasi
                          FROM booking");
        $res = $this->db->single();

        return [
            'total_booking' => (int)($res['total_booking'] ?? 0),
            'booking_menunggu' => (int)($res['booking_menunggu'] ?? 0),
            'booking_dikonfirmasi' => (int)($res['booking_dikonfirmasi'] ?? 0),
            'booking_ditolak' => (int)($res['booking_ditolak'] ?? 0),
            'booking_selesai' => (int)($res['booking_selesai'] ?? 0),
            'total_nilai_tercatat' => (float)($res['total_nilai_semua_booking'] ?? 0),
            'total_nilai_dikonfirmasi' => (float)($res['total_nilai_dikonfirmasi'] ?? 0)
        ];
    }

    /**
     * Booking yang Menunggu Konfirmasi Admin
     */
    public function getPendingBookings(int $limit = 10): array {
        $limit = max(1, min(50, $limit));
        $this->db->query("SELECT 
                            b.id as booking_id,
                            b.tanggal_booking,
                            b.tanggal_mulai,
                            b.lama_sewa,
                            b.total_harga,
                            b.status,
                            u.nama as nama_user,
                            u.email as email_user,
                            k.nomor_kamar,
                            k.harga as harga_kamar
                          FROM booking b
                          JOIN users u ON b.user_id = u.id
                          JOIN kamar k ON b.kamar_id = k.id
                          WHERE b.status = 'menunggu'
                          ORDER BY b.tanggal_booking ASC
                          LIMIT " . (int)$limit);
        return $this->db->resultSet();
    }

    /**
     * Booking Terbaru di Sistem
     */
    public function getRecentBookings(int $limit = 5): array {
        $limit = max(1, min(50, $limit));
        $this->db->query("SELECT 
                            b.id as booking_id,
                            b.tanggal_booking,
                            b.tanggal_mulai,
                            b.lama_sewa,
                            b.total_harga,
                            b.status,
                            u.nama as nama_user,
                            k.nomor_kamar
                          FROM booking b
                          JOIN users u ON b.user_id = u.id
                          JOIN kamar k ON b.kamar_id = k.id
                          ORDER BY b.id DESC
                          LIMIT " . (int)$limit);
        return $this->db->resultSet();
    }

    /**
     * Analisis Booking Bulan Ini vs Bulan Lalu
     */
    public function getMonthlyBookingAnalysis(): array {
        // Bulan ini
        $this->db->query("SELECT 
                            COUNT(*) as count,
                            COALESCE(SUM(total_harga), 0) as total_nilai,
                            COALESCE(SUM(CASE WHEN status = 'dikonfirmasi' THEN total_harga ELSE 0 END), 0) as nilai_dikonfirmasi
                          FROM booking
                          WHERE YEAR(tanggal_booking) = YEAR(CURRENT_DATE()) 
                            AND MONTH(tanggal_booking) = MONTH(CURRENT_DATE())");
        $thisMonth = $this->db->single();

        // Bulan lalu
        $this->db->query("SELECT 
                            COUNT(*) as count,
                            COALESCE(SUM(total_harga), 0) as total_nilai,
                            COALESCE(SUM(CASE WHEN status = 'dikonfirmasi' THEN total_harga ELSE 0 END), 0) as nilai_dikonfirmasi
                          FROM booking
                          WHERE tanggal_booking >= DATE_SUB(DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01'), INTERVAL 1 MONTH)
                            AND tanggal_booking < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')");
        $lastMonth = $this->db->single();

        return [
            'bulan_ini' => [
                'jumlah_booking' => (int)($thisMonth['count'] ?? 0),
                'total_nilai_tercatat' => (float)($thisMonth['total_nilai'] ?? 0),
                'nilai_dikonfirmasi' => (float)($thisMonth['nilai_dikonfirmasi'] ?? 0)
            ],
            'bulan_lalu' => [
                'jumlah_booking' => (int)($lastMonth['count'] ?? 0),
                'total_nilai_tercatat' => (float)($lastMonth['total_nilai'] ?? 0),
                'nilai_dikonfirmasi' => (float)($lastMonth['nilai_dikonfirmasi'] ?? 0)
            ]
        ];
    }

    /**
     * Ringkasan Aktivitas Booking Minggu Ini (7 hari terakhir)
     */
    public function getWeeklyActivity(): array {
        $this->db->query("SELECT 
                            COUNT(*) as booking_minggu_ini,
                            COALESCE(SUM(total_harga), 0) as total_nilai_minggu_ini,
                            SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
                            SUM(CASE WHEN status = 'dikonfirmasi' THEN 1 ELSE 0 END) as dikonfirmasi
                          FROM booking
                          WHERE tanggal_booking >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $res = $this->db->single();
        return [
            'booking_7_hari_terakhir' => (int)($res['booking_minggu_ini'] ?? 0),
            'total_nilai_tercatat' => (float)($res['total_nilai_minggu_ini'] ?? 0),
            'menunggu' => (int)($res['menunggu'] ?? 0),
            'dikonfirmasi' => (int)($res['dikonfirmasi'] ?? 0),
        ];
    }

    /**
     * Daftar Semua Kamar beserta Statusnya untuk Admin
     */
    public function getAdminRooms(): array {
        $this->db->query("SELECT id, nomor_kamar, harga, status, fasilitas, updated_at 
                          FROM kamar 
                          ORDER BY id ASC");
        return $this->db->resultSet();
    }
}
