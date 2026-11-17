<?php
/**
 * UserModel
 * Model untuk mengelola data akun pengguna pada tabel 'users'
 */

class UserModel {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Mencari pengguna berdasarkan alamat email
     */
    public function findByEmail(string $email): ?array {
        $this->db->query("SELECT * FROM users WHERE email = :email LIMIT 1");
        $this->db->bind(':email', trim($email));
        return $this->db->single();
    }

    /**
     * Mencari pengguna berdasarkan ID
     */
    public function findById(int $id): ?array {
        $this->db->query("SELECT id, nama, email, role, created_at FROM users WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Memeriksa apakah email sudah terdaftar di sistem
     */
    public function emailExists(string $email, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $this->db->query("SELECT id FROM users WHERE email = :email AND id != :excludeId LIMIT 1");
            $this->db->bind(':excludeId', $excludeId);
        } else {
            $this->db->query("SELECT id FROM users WHERE email = :email LIMIT 1");
        }
        $this->db->bind(':email', trim($email));
        $this->db->single();
        return $this->db->rowCount() > 0;
    }

    /**
     * Mendaftarkan pengguna baru ke tabel users
     * Menggunakan password_hash() dengan algoritma default (Bcrypt)
     */
    public function register(array $data): bool|string {
        $this->db->query("INSERT INTO users (nama, email, password, role, created_at) 
                          VALUES (:nama, :email, :password, :role, NOW())");
        
        $this->db->bind(':nama', trim($data['nama']));
        $this->db->bind(':email', trim($data['email']));
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':role', $data['role'] ?? 'user');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Mengambil semua daftar user (untuk panel admin)
     */
    public function getAllUsers(): array {
        $this->db->query("SELECT id, nama, email, role, created_at FROM users ORDER BY id DESC");
        return $this->db->resultSet();
    }

    /**
     * Menghitung jumlah user berdasarkan role
     */
    public function countUsersByRole(string $role = 'user'): int {
        $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = :role");
        $this->db->bind(':role', $role);
        $res = $this->db->single();
        return (int)($res['total'] ?? 0);
    }
}
