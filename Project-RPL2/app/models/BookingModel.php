<?php
/**
 * BookingModel
 * Model untuk mengelola data transaksi pemesanan (booking) kamar kos
 * Menggunakan Database Transaction untuk menjamin konsistensi data booking dan status kamar
 */

class BookingModel {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Memeriksa apakah kamar ada dan statusnya 'tersedia'
     */
    public function isKamarAvailable(int $kamarId): bool {
        $this->db->query("SELECT id, status FROM kamar WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $kamarId);
        $kamar = $this->db->single();
        return ($kamar && $kamar['status'] === 'tersedia');
    }

    /**
     * Membuat data booking baru menggunakan Database Transaction
     * Langkah:
     * 1. Mulai transaksi
     * 2. Kunci dan cek ketersediaan kamar (status 'tersedia')
     * 3. Hitung total_harga = harga_kamar * lama_sewa
     * 4. Simpan ke tabel booking (status = 'menunggu')
     * 5. Ubah status kamar menjadi 'booking'
     * 6. Commit transaksi
     *
     * @param array $data ['user_id', 'kamar_id', 'tanggal_mulai', 'lama_sewa']
     * @return int|false ID booking baru jika berhasil, false jika gagal
     */
    public function createBooking(array $data): int|false {
        $userId = (int) ($data['user_id'] ?? 0);
        $kamarId = (int) ($data['kamar_id'] ?? 0);
        $tanggalMulai = trim($data['tanggal_mulai'] ?? '');
        $lamaSewa = (int) ($data['lama_sewa'] ?? 1);

        if ($userId <= 0 || $kamarId <= 0 || empty($tanggalMulai) || $lamaSewa < 1) {
            return false;
        }

        try {
            // 1. Mulai Database Transaction
            $this->db->beginTransaction();

            // 2. Cek apakah kamar ada dan statusnya 'tersedia' dengan locking
            $this->db->query("SELECT id, nomor_kamar, harga, status FROM kamar WHERE id = :id FOR UPDATE");
            $this->db->bind(':id', $kamarId);
            $kamar = $this->db->single();

            if (!$kamar || $kamar['status'] !== 'tersedia') {
                // Kamar tidak ada atau sudah tidak berstatus tersedia
                $this->db->rollBack();
                return false;
            }

            // 3. Sistem menghitung: total_harga = harga_kamar * lama_sewa
            $hargaKamar = (float) $kamar['harga'];
            $totalHarga = $hargaKamar * $lamaSewa;

            // 4. Masukkan data ke tabel booking dengan status 'menunggu'
            $this->db->query("INSERT INTO booking 
                              (user_id, kamar_id, tanggal_booking, tanggal_mulai, lama_sewa, total_harga, status, created_at) 
                              VALUES 
                              (:user_id, :kamar_id, NOW(), :tanggal_mulai, :lama_sewa, :total_harga, 'menunggu', NOW())");
            
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':kamar_id', $kamarId);
            $this->db->bind(':tanggal_mulai', $tanggalMulai);
            $this->db->bind(':lama_sewa', $lamaSewa);
            $this->db->bind(':total_harga', $totalHarga);

            if (!$this->db->execute()) {
                $this->db->rollBack();
                return false;
            }

            $bookingId = (int) $this->db->lastInsertId();

            // 5. Ubah status kamar di tabel kamar menjadi 'booking'
            $this->db->query("UPDATE kamar SET status = 'booking', updated_at = NOW() WHERE id = :kamar_id");
            $this->db->bind(':kamar_id', $kamarId);

            if (!$this->db->execute()) {
                $this->db->rollBack();
                return false;
            }

            // 6. Commit seluruh perubahan transaksi
            $this->db->commit();
            return $bookingId;

        } catch (Throwable $e) {
            // Batalkan transaksi jika terjadi error tak terduga
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in BookingModel::createBooking: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil data detail booking berdasarkan ID (dengan JOIN tabel kamar dan users)
     */
    public function getById(int $id): ?array {
        $this->db->query("SELECT b.*, 
                                 k.nomor_kamar, 
                                 k.harga AS harga_kamar, 
                                 k.fasilitas AS kamar_fasilitas, 
                                 k.deskripsi AS kamar_deskripsi, 
                                 k.foto AS kamar_foto, 
                                 k.status AS kamar_status,
                                 u.nama AS user_nama, 
                                 u.email AS user_email 
                          FROM booking b
                          JOIN kamar k ON b.kamar_id = k.id
                          JOIN users u ON b.user_id = u.id
                          WHERE b.id = :id 
                          LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Mengambil seluruh data booking milik user tertentu
     */
    public function getByUserId(int $userId): array {
        $this->db->query("SELECT b.*, 
                                 k.nomor_kamar, 
                                 k.harga AS harga_kamar, 
                                 k.fasilitas AS kamar_fasilitas, 
                                 k.deskripsi AS kamar_deskripsi, 
                                 k.foto AS kamar_foto, 
                                 k.status AS kamar_status 
                          FROM booking b
                          JOIN kamar k ON b.kamar_id = k.id
                          WHERE b.user_id = :user_id
                          ORDER BY b.id DESC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Mengambil semua data booking di sistem (untuk admin/dashboard)
     */
    public function getAllBookings(): array {
        $this->db->query("SELECT b.*, 
                                 k.nomor_kamar, 
                                 k.harga AS harga_kamar, 
                                 k.status AS kamar_status, 
                                 u.nama AS user_nama, 
                                 u.email AS user_email
                          FROM booking b
                          JOIN kamar k ON b.kamar_id = k.id
                          JOIN users u ON b.user_id = u.id
                          ORDER BY b.id DESC");
        return $this->db->resultSet();
    }

    /**
     * Konfirmasi Pesanan Booking oleh Admin menggunakan Database Transaction
     * Aturan:
     * - Hanya booking dengan status 'menunggu' yang dapat dikonfirmasi.
     * - Tidak dapat mengkonfirmasi booking yang sudah 'dikonfirmasi' atau 'ditolak'.
     * - Perubahan status:
     *     status booking: menunggu -> dikonfirmasi
     *     status kamar  : booking  -> terisi
     */
    public function confirmBooking(int $bookingId): array {
        try {
            $this->db->beginTransaction();

            // Kunci baris booking untuk menghindari race condition
            $this->db->query("SELECT b.*, k.nomor_kamar, k.status as kamar_status 
                              FROM booking b 
                              JOIN kamar k ON b.kamar_id = k.id 
                              WHERE b.id = :id 
                              FOR UPDATE");
            $this->db->bind(':id', $bookingId);
            $booking = $this->db->single();

            if (!$booking) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Data pesanan booking tidak ditemukan.'
                ];
            }

            // Validasi: Pastikan admin tidak dapat mengkonfirmasi booking yang sudah ditolak atau dikonfirmasi
            if ($booking['status'] !== 'menunggu') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Booking #BK-00' . $bookingId . ' tidak dapat dikonfirmasi karena status saat ini adalah "' . ucfirst($booking['status']) . '". Hanya booking berstatus "Menunggu" yang dapat dikonfirmasi.'
                ];
            }

            // 1. Ubah status booking: menunggu -> dikonfirmasi
            $this->db->query("UPDATE booking SET status = 'dikonfirmasi' WHERE id = :id");
            $this->db->bind(':id', $bookingId);
            if (!$this->db->execute()) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Gagal memperbarui status booking.'
                ];
            }

            // 2. Ubah status kamar: booking -> terisi
            $this->db->query("UPDATE kamar SET status = 'terisi', updated_at = NOW() WHERE id = :kamar_id");
            $this->db->bind(':kamar_id', (int)$booking['kamar_id']);
            if (!$this->db->execute()) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Gagal memperbarui status kamar menjadi terisi.'
                ];
            }

            // 3. Commit seluruh perubahan
            $this->db->commit();
            return [
                'success' => true,
                'booking' => $booking,
                'error' => null
            ];

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in BookingModel::confirmBooking: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Terjadi kesalahan sistem saat memproses konfirmasi booking. Silakan coba beberapa saat lagi.'
            ];
        }
    }

    /**
     * Tolak Pesanan Booking oleh Admin menggunakan Database Transaction
     * Aturan:
     * - Hanya booking dengan status 'menunggu' yang dapat ditolak.
     * - Tidak dapat menolak booking yang sudah 'dikonfirmasi' atau 'ditolak'.
     * - Perubahan status:
     *     status booking: menunggu -> ditolak
     *     status kamar  : booking  -> tersedia
     */
    public function rejectBooking(int $bookingId): array {
        try {
            $this->db->beginTransaction();

            // Kunci baris booking
            $this->db->query("SELECT b.*, k.nomor_kamar, k.status as kamar_status 
                              FROM booking b 
                              JOIN kamar k ON b.kamar_id = k.id 
                              WHERE b.id = :id 
                              FOR UPDATE");
            $this->db->bind(':id', $bookingId);
            $booking = $this->db->single();

            if (!$booking) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Data pesanan booking tidak ditemukan.'
                ];
            }

            // Validasi: Pastikan admin tidak dapat menolak booking yang sudah ditolak atau dikonfirmasi
            if ($booking['status'] !== 'menunggu') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Booking #BK-00' . $bookingId . ' tidak dapat ditolak karena status saat ini adalah "' . ucfirst($booking['status']) . '". Hanya booking berstatus "Menunggu" yang dapat ditolak.'
                ];
            }

            // 1. Ubah status booking: menunggu -> ditolak
            $this->db->query("UPDATE booking SET status = 'ditolak' WHERE id = :id");
            $this->db->bind(':id', $bookingId);
            if (!$this->db->execute()) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Gagal memperbarui status booking menjadi ditolak.'
                ];
            }

            // 2. Ubah status kamar: booking -> tersedia (agar dapat dipesan oleh pengguna lain)
            $this->db->query("UPDATE kamar SET status = 'tersedia', updated_at = NOW() WHERE id = :kamar_id");
            $this->db->bind(':kamar_id', (int)$booking['kamar_id']);
            if (!$this->db->execute()) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Gagal mengembalikan status kamar menjadi tersedia.'
                ];
            }

            // 3. Commit seluruh perubahan
            $this->db->commit();
            return [
                'success' => true,
                'booking' => $booking,
                'error' => null
            ];

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in BookingModel::rejectBooking: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Terjadi kesalahan sistem saat memproses penolakan booking. Silakan coba beberapa saat lagi.'
            ];
        }
    }

    /**
     * Statistik ringkasan booking untuk panel admin
     */
    public function getStats(): array {
        $all = $this->getAllBookings();
        return [
            'total' => count($all),
            'menunggu' => count(array_filter($all, fn($b) => $b['status'] === 'menunggu')),
            'dikonfirmasi' => count(array_filter($all, fn($b) => $b['status'] === 'dikonfirmasi')),
            'ditolak' => count(array_filter($all, fn($b) => $b['status'] === 'ditolak')),
            'selesai' => count(array_filter($all, fn($b) => $b['status'] === 'selesai')),
        ];
    }
}
