<?php
/**
 * KamarModel
 * Model untuk mengelola tabel 'kamar' (CRUD Data Kamar Kos)
 */

class KamarModel {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Mengambil semua daftar kamar, dengan opsi filter status
     */
    public function getAll(?string $status = null): array {
        if (!empty($status) && in_array($status, ['tersedia', 'booking', 'terisi'])) {
            $this->db->query("SELECT * FROM kamar WHERE status = :status ORDER BY id DESC");
            $this->db->bind(':status', $status);
        } else {
            $this->db->query("SELECT * FROM kamar ORDER BY id DESC");
        }
        return $this->db->resultSet();
    }

    /**
     * Pencarian dan Filter Fleksibel Kamar Kos
     * Mendukung: status, min_harga, max_harga, nomor_kamar, fasilitas, keyword umum (q), sorting
     */
    public function searchAndFilter(array $filters = []): array {
        $sql = "SELECT * FROM kamar WHERE 1=1";
        $binds = [];

        // 1. Filter Status Kamar
        if (!empty($filters['status']) && in_array($filters['status'], ['tersedia', 'booking', 'terisi'])) {
            $sql .= " AND status = :status";
            $binds[':status'] = $filters['status'];
        }

        // 2. Filter Harga Minimum
        if (isset($filters['min_harga']) && is_numeric($filters['min_harga']) && (float)$filters['min_harga'] > 0) {
            $sql .= " AND harga >= :min_harga";
            $binds[':min_harga'] = (float)$filters['min_harga'];
        }

        // 3. Filter Harga Maksimum
        if (isset($filters['max_harga']) && is_numeric($filters['max_harga']) && (float)$filters['max_harga'] > 0) {
            $sql .= " AND harga <= :max_harga";
            $binds[':max_harga'] = (float)$filters['max_harga'];
        }

        // 4. Pencarian Spesifik: Nomor Kamar
        if (!empty($filters['nomor_kamar'])) {
            $sql .= " AND nomor_kamar LIKE :nomor_kamar";
            $binds[':nomor_kamar'] = '%' . trim($filters['nomor_kamar']) . '%';
        }

        // 5. Pencarian Spesifik: Fasilitas
        if (!empty($filters['fasilitas'])) {
            $sql .= " AND fasilitas LIKE :fasilitas";
            $binds[':fasilitas'] = '%' . trim($filters['fasilitas']) . '%';
        }

        // 6. Pencarian Bebas (Keyword gabungan: Nomor Kamar ATAU Fasilitas)
        if (!empty($filters['q'])) {
            $sql .= " AND (nomor_kamar LIKE :q_nomor OR fasilitas LIKE :q_fasilitas)";
            $binds[':q_nomor'] = '%' . trim($filters['q']) . '%';
            $binds[':q_fasilitas'] = '%' . trim($filters['q']) . '%';
        }

        // 7. Pengurutan Data (Sorting)
        $sort = $filters['sort'] ?? 'terbaru';
        switch ($sort) {
            case 'harga_asc':
            case 'termurah':
                $sql .= " ORDER BY harga ASC, id DESC";
                break;
            case 'harga_desc':
            case 'termahal':
                $sql .= " ORDER BY harga DESC, id DESC";
                break;
            case 'nomor_asc':
                $sql .= " ORDER BY nomor_kamar ASC";
                break;
            case 'nomor_desc':
                $sql .= " ORDER BY nomor_kamar DESC";
                break;
            case 'terlama':
                $sql .= " ORDER BY id ASC";
                break;
            case 'terbaru':
            default:
                $sql .= " ORDER BY id DESC";
                break;
        }

        $this->db->query($sql);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet();
    }

    /**
     * Mendapatkan batas harga terendah dan tertinggi dari semua kamar di database
     */
    public function getPriceBounds(): array {
        $this->db->query("SELECT MIN(harga) as min_harga, MAX(harga) as max_harga FROM kamar");
        $res = $this->db->single();
        return [
            'min' => (float)($res['min_harga'] ?? 0),
            'max' => (float)($res['max_harga'] ?? 0),
        ];
    }

    /**
     * Mengambil data kamar berdasarkan ID
     */
    public function getById(int $id): ?array {
        $this->db->query("SELECT * FROM kamar WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Memeriksa apakah nomor kamar sudah terdaftar
     * $excludeId digunakan saat update agar tidak menolak nomor kamar milik dirinya sendiri
     */
    public function isNomorKamarExists(string $nomorKamar, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $this->db->query("SELECT id FROM kamar WHERE LOWER(TRIM(nomor_kamar)) = LOWER(TRIM(:nomor)) AND id != :excludeId LIMIT 1");
            $this->db->bind(':excludeId', $excludeId);
        } else {
            $this->db->query("SELECT id FROM kamar WHERE LOWER(TRIM(nomor_kamar)) = LOWER(TRIM(:nomor)) LIMIT 1");
        }
        $this->db->bind(':nomor', $nomorKamar);
        $this->db->single();
        return $this->db->rowCount() > 0;
    }

    /**
     * Menambah kamar baru
     */
    public function insert(array $data): int|bool {
        $this->db->query("INSERT INTO kamar (nomor_kamar, harga, fasilitas, deskripsi, foto, status, created_at, updated_at) 
                          VALUES (:nomor_kamar, :harga, :fasilitas, :deskripsi, :foto, :status, NOW(), NOW())");
        
        $this->db->bind(':nomor_kamar', trim($data['nomor_kamar']));
        $this->db->bind(':harga', (float) $data['harga']);
        $this->db->bind(':fasilitas', trim($data['fasilitas'] ?? ''));
        $this->db->bind(':deskripsi', trim($data['deskripsi'] ?? ''));
        $this->db->bind(':foto', $data['foto'] ?? 'default_kamar.jpg');
        $this->db->bind(':status', $data['status'] ?? 'tersedia');

        if ($this->db->execute()) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Mengubah data kamar yang sudah ada
     */
    public function update(int $id, array $data): bool {
        $this->db->query("UPDATE kamar 
                          SET nomor_kamar = :nomor_kamar, 
                              harga = :harga, 
                              fasilitas = :fasilitas, 
                              deskripsi = :deskripsi, 
                              foto = :foto, 
                              status = :status, 
                              updated_at = NOW() 
                          WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':nomor_kamar', trim($data['nomor_kamar']));
        $this->db->bind(':harga', (float) $data['harga']);
        $this->db->bind(':fasilitas', trim($data['fasilitas'] ?? ''));
        $this->db->bind(':deskripsi', trim($data['deskripsi'] ?? ''));
        $this->db->bind(':foto', $data['foto'] ?? 'default_kamar.jpg');
        $this->db->bind(':status', $data['status'] ?? 'tersedia');

        return $this->db->execute();
    }

    /**
     * Mengubah hanya status kamar
     */
    public function updateStatus(int $id, string $status): bool {
        if (!in_array($status, ['tersedia', 'booking', 'terisi'])) {
            return false;
        }

        $this->db->query("UPDATE kamar SET status = :status, updated_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }

    /**
     * Menghapus data kamar
     */
    public function delete(int $id): bool {
        $this->db->query("DELETE FROM kamar WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Cek apakah kamar memiliki relasi ke tabel booking
     */
    public function hasBookings(int $id): bool {
        $this->db->query("SELECT COUNT(*) as total FROM booking WHERE kamar_id = :id");
        $this->db->bind(':id', $id);
        $res = $this->db->single();
        return (int) ($res['total'] ?? 0) > 0;
    }

    /**
     * Statistik ringkasan kamar
     */
    public function getStats(): array {
        $all = $this->getAll();
        return [
            'total_kamar' => count($all),
            'kamar_tersedia' => count(array_filter($all, fn($r) => $r['status'] === 'tersedia')),
            'kamar_booking' => count(array_filter($all, fn($r) => $r['status'] === 'booking')),
            'kamar_terisi' => count(array_filter($all, fn($r) => $r['status'] === 'terisi')),
        ];
    }
}
