<?php
/**
 * KamarController
 * Mengelola katalog kamar untuk User/Guest dan operasi CRUD Kamar untuk Admin
 */

class KamarController extends Controller {

    /**
     * Helper upload file foto kamar
     * Validasi ekstensi (jpg, jpeg, png, webp) dan ukuran file (maksimal 2MB)
     */
    private function handleUploadPhoto(array $file, ?string $currentPhoto = null): array {
        // Jika tidak ada file baru yang diunggah
        if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return [
                'success' => true,
                'filename' => $currentPhoto ?: 'default_kamar.jpg',
                'error' => null
            ];
        }

        // Cek error upload dasar
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'filename' => '',
                'error' => 'Terjadi kesalahan saat mengunggah foto (Kode error: ' . $file['error'] . ').'
            ];
        }

        // 1. Validasi Ukuran File (Maksimal 2 MB)
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'filename' => '',
                'error' => 'Ukuran file foto terlalu besar! Maksimal ukuran adalah 2 MB.'
            ];
        }

        // 2. Validasi Ekstensi File
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');

        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'filename' => '',
                'error' => 'Format file tidak diizinkan! Gunakan format JPG, JPEG, PNG, atau WEBP.'
            ];
        }

        // 3. Validasi MIME Type jika fileinfo tersedia
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mimeType, $allowedMimes)) {
                return [
                    'success' => false,
                    'filename' => '',
                    'error' => 'File yang diunggah bukan gambar yang valid (MIME type tidak sesuai).'
                ];
            }
        }

        // 4. Validasi binary image header dengan getimagesize
        if (function_exists('getimagesize')) {
            $imageSize = @getimagesize($file['tmp_name']);
            if ($imageSize === false) {
                return [
                    'success' => false,
                    'filename' => '',
                    'error' => 'File yang diunggah bukan file gambar yang valid atau file rusak.'
                ];
            }
        }

        // 5. Simpan File ke direktori public/images/kamar/
        $targetDir = __DIR__ . '/../../public/images/kamar/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $newFilename = 'kamar_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetPath = $targetDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Hapus file foto lama jika bukan default (menggunakan basename untuk proteksi path traversal)
            if ($currentPhoto && $currentPhoto !== 'default_kamar.jpg') {
                $safeOldPhoto = basename($currentPhoto);
                $oldPath = $targetDir . $safeOldPhoto;
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            return [
                'success' => true,
                'filename' => $newFilename,
                'error' => null
            ];
        }

        return [
            'success' => false,
            'filename' => '',
            'error' => 'Gagal menyimpan file foto ke server.'
        ];
    }

    /**
     * 1. Halaman Publik: Daftar Kamar Kos (User & Guest)
     * Dilengkapi fitur pencarian (nomor kamar & fasilitas) dan filter (status, harga min, harga max, sorting)
     */
    public function index(): void {
        $kamarModel = $this->model('KamarModel');
        
        // Ambil query parameter filter & pencarian
        $q = trim($_GET['q'] ?? $_GET['keyword'] ?? '');
        $filterStatus = trim($_GET['status'] ?? 'semua');
        $minHarga = (isset($_GET['min_harga']) && $_GET['min_harga'] !== '') ? (float)$_GET['min_harga'] : null;
        $maxHarga = (isset($_GET['max_harga']) && $_GET['max_harga'] !== '') ? (float)$_GET['max_harga'] : null;
        $budget = trim($_GET['budget'] ?? '');
        if ($budget === '1') {
            $maxHarga = 1000000.0;
        } elseif ($budget === '2') {
            $minHarga = 1000000.0;
            $maxHarga = 1500000.0;
        } elseif ($budget === '3') {
            $minHarga = 1500000.0;
        }
        $nomorKamar = trim($_GET['nomor_kamar'] ?? '');
        $fasilitas = trim($_GET['fasilitas'] ?? '');
        $sort = trim($_GET['sort'] ?? 'terbaru');

        // Susun parameter filter
        $filters = [
            'q' => $q,
            'status' => $filterStatus,
            'min_harga' => $minHarga,
            'max_harga' => $maxHarga,
            'nomor_kamar' => $nomorKamar,
            'fasilitas' => $fasilitas,
            'sort' => $sort
        ];

        // Jalankan pencarian & filter
        $rooms = $kamarModel->searchAndFilter($filters);
        $stats = $kamarModel->getStats();
        $priceBounds = $kamarModel->getPriceBounds();

        // Cek apakah ada filter yang sedang aktif
        $isFiltered = (!empty($q) || 
                       ($filterStatus !== 'semua' && !empty($filterStatus)) || 
                       $minHarga !== null || 
                       $maxHarga !== null || 
                       !empty($nomorKamar) || 
                       !empty($fasilitas));

        $data = [
            'title' => 'Daftar Kamar Kos - KosBook',
            'activePage' => 'kamar',
            'rooms' => $rooms,
            'stats' => $stats,
            'totalRooms' => $stats['total_kamar'],
            'filteredCount' => count($rooms),
            'filterStatus' => $filterStatus,
            'filters' => $filters,
            'isFiltered' => $isFiltered,
            'priceBounds' => $priceBounds,
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('kamar/index', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * 2. Halaman Publik: Detail Kamar Kos (User, Guest, Admin)
     */
    public function detail(int $id = 0): void {
        $kamarModel = $this->model('KamarModel');
        $room = $kamarModel->getById($id);

        if (!$room) {
            AuthHelper::setFlash('warning', 'Data kamar tidak ditemukan.', 'bi-exclamation-circle-fill');
            header("Location: " . url('kamar'));
            exit;
        }

        // Ambil 3 kamar rekomendasi lainnya
        $allRooms = $kamarModel->getAll();
        $relatedRooms = array_slice(array_filter($allRooms, fn($r) => (int)$r['id'] !== (int)$room['id']), 0, 3);

        $data = [
            'title' => 'Detail ' . $room['nomor_kamar'] . ' - KosBook',
            'activePage' => 'kamar',
            'kamar' => $room,
            'relatedRooms' => $relatedRooms,
            'isLoggedIn' => AuthHelper::check(),
            'isGuest' => !AuthHelper::check(),
            'currentUser' => AuthHelper::user(),
            'currentRole' => AuthHelper::role(),
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('kamar/detail', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * 3. Halaman Admin: Manajemen Daftar Kamar (Admin Only)
     */
    public function admin(): void {
        AuthHelper::requireRole('admin');

        $kamarModel = $this->model('KamarModel');
        $filterStatus = $_GET['status'] ?? 'semua';

        if ($filterStatus !== 'semua' && in_array($filterStatus, ['tersedia', 'booking', 'terisi'])) {
            $rooms = $kamarModel->getAll($filterStatus);
        } else {
            $rooms = $kamarModel->getAll();
        }

        $data = [
            'title' => 'Kelola Data Kamar - KosBook Admin',
            'activePage' => 'admin_kamar',
            'rooms' => $rooms,
            'filterStatus' => $filterStatus,
            'stats' => $kamarModel->getStats(),
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('kamar/admin', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * 4. Halaman Admin: Tambah Kamar Baru (Admin Only)
     */
    public function tambah(): void {
        AuthHelper::requireRole('admin');
        $kamarModel = $this->model('KamarModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validasi Token CSRF jika form mengirimkan token
            if (isset($_POST['csrf_token']) && !AuthHelper::verifyCsrf()) {
                AuthHelper::setFlash('danger', 'Sesi pengiriman formulir tidak valid atau telah kedaluwarsa. Silakan coba lagi.', 'bi-shield-x');
                header("Location: " . url('kamar/tambah'));
                exit;
            }

            $nomor_kamar = trim($_POST['nomor_kamar'] ?? '');
            $harga = trim($_POST['harga'] ?? '');
            $fasilitas = trim($_POST['fasilitas'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $status = trim($_POST['status'] ?? 'tersedia');

            $_SESSION['old_kamar'] = [
                'nomor_kamar' => $nomor_kamar,
                'harga' => $harga,
                'fasilitas' => $fasilitas,
                'deskripsi' => $deskripsi,
                'status' => $status
            ];

            // Validasi 1: Nomor kamar wajib diisi
            if (empty($nomor_kamar)) {
                AuthHelper::setFlash('danger', 'Nomor kamar wajib diisi.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/tambah'));
                exit;
            }

            // Validasi 2: Harga harus berupa angka
            if (!is_numeric($harga)) {
                AuthHelper::setFlash('danger', 'Harga kamar harus berupa angka yang valid.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/tambah'));
                exit;
            }

            // Validasi 3: Harga tidak boleh negatif
            if ((float)$harga < 0) {
                AuthHelper::setFlash('danger', 'Harga sewa kamar tidak boleh bernilai negatif.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/tambah'));
                exit;
            }

            // Validasi 4: Status harus menggunakan pilihan yang valid
            if (!in_array($status, ['tersedia', 'booking', 'terisi'])) {
                AuthHelper::setFlash('danger', 'Status kamar harus berupa: tersedia, booking, atau terisi.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/tambah'));
                exit;
            }

            // Validasi 5: Nomor kamar tidak boleh duplikat
            if ($kamarModel->isNomorKamarExists($nomor_kamar)) {
                AuthHelper::setFlash('danger', 'Nomor kamar "' . htmlspecialchars($nomor_kamar) . '" sudah digunakan. Silakan gunakan nomor lain.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/tambah'));
                exit;
            }

            // Upload Foto dengan validasi tipe dan ukuran file
            $uploadResult = $this->handleUploadPhoto($_FILES['foto'] ?? []);
            if (!$uploadResult['success']) {
                AuthHelper::setFlash('danger', $uploadResult['error'], 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/tambah'));
                exit;
            }

            // Simpan data kamar ke Database
            $insertId = $kamarModel->insert([
                'nomor_kamar' => $nomor_kamar,
                'harga' => (float) $harga,
                'fasilitas' => $fasilitas,
                'deskripsi' => $deskripsi,
                'foto' => $uploadResult['filename'],
                'status' => $status
            ]);

            if ($insertId) {
                unset($_SESSION['old_kamar']);
                AuthHelper::setFlash('success', 'Kamar ' . htmlspecialchars($nomor_kamar) . ' berhasil ditambahkan ke sistem!', 'bi-check-circle-fill');
                header("Location: " . url('kamar/admin'));
                exit;
            } else {
                AuthHelper::setFlash('danger', 'Terjadi kesalahan sistem saat menyimpan data kamar.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/tambah'));
                exit;
            }
        }

        // Tampilan Form Tambah (GET)
        $data = [
            'title' => 'Tambah Kamar Baru - KosBook Admin',
            'activePage' => 'admin_kamar',
            'old' => $_SESSION['old_kamar'] ?? [],
            'flash' => AuthHelper::getFlash()
        ];
        unset($_SESSION['old_kamar']);

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('kamar/tambah', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * 5. Halaman Admin: Ubah Kamar (Admin Only)
     */
    public function edit(int $id = 0): void {
        AuthHelper::requireRole('admin');
        $kamarModel = $this->model('KamarModel');
        $kamar = $kamarModel->getById($id);

        if (!$kamar) {
            AuthHelper::setFlash('warning', 'Data kamar tidak ditemukan.', 'bi-exclamation-circle-fill');
            header("Location: " . url('kamar/admin'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validasi Token CSRF jika form mengirimkan token
            if (isset($_POST['csrf_token']) && !AuthHelper::verifyCsrf()) {
                AuthHelper::setFlash('danger', 'Sesi pengiriman formulir tidak valid atau telah kedaluwarsa. Silakan coba lagi.', 'bi-shield-x');
                header("Location: " . url('kamar/edit/' . $id));
                exit;
            }

            $nomor_kamar = trim($_POST['nomor_kamar'] ?? '');
            $harga = trim($_POST['harga'] ?? '');
            $fasilitas = trim($_POST['fasilitas'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $status = trim($_POST['status'] ?? 'tersedia');

            // Validasi 1: Nomor kamar wajib diisi
            if (empty($nomor_kamar)) {
                AuthHelper::setFlash('danger', 'Nomor kamar wajib diisi.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/edit/' . $id));
                exit;
            }

            // Validasi 2: Harga harus berupa angka
            if (!is_numeric($harga)) {
                AuthHelper::setFlash('danger', 'Harga kamar harus berupa angka yang valid.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/edit/' . $id));
                exit;
            }

            // Validasi 3: Harga tidak boleh negatif
            if ((float)$harga < 0) {
                AuthHelper::setFlash('danger', 'Harga sewa kamar tidak boleh bernilai negatif.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/edit/' . $id));
                exit;
            }

            // Validasi 4: Status harus menggunakan pilihan yang valid
            if (!in_array($status, ['tersedia', 'booking', 'terisi'])) {
                AuthHelper::setFlash('danger', 'Status kamar harus berupa: tersedia, booking, atau terisi.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/edit/' . $id));
                exit;
            }

            // Validasi 5: Nomor kamar tidak boleh duplikat (kecuali dirinya sendiri)
            if ($kamarModel->isNomorKamarExists($nomor_kamar, $id)) {
                AuthHelper::setFlash('danger', 'Nomor kamar "' . htmlspecialchars($nomor_kamar) . '" sudah digunakan kamar lain.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/edit/' . $id));
                exit;
            }

            // Upload Foto baru jika disediakan
            $uploadResult = $this->handleUploadPhoto($_FILES['foto'] ?? [], $kamar['foto']);
            if (!$uploadResult['success']) {
                AuthHelper::setFlash('danger', $uploadResult['error'], 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/edit/' . $id));
                exit;
            }

            // Update Database
            $updated = $kamarModel->update($id, [
                'nomor_kamar' => $nomor_kamar,
                'harga' => (float) $harga,
                'fasilitas' => $fasilitas,
                'deskripsi' => $deskripsi,
                'foto' => $uploadResult['filename'],
                'status' => $status
            ]);

            if ($updated) {
                AuthHelper::setFlash('success', 'Perubahan data kamar ' . htmlspecialchars($nomor_kamar) . ' berhasil disimpan!', 'bi-check-circle-fill');
                header("Location: " . url('kamar/admin'));
                exit;
            } else {
                AuthHelper::setFlash('danger', 'Gagal memperbarui data kamar.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('kamar/edit/' . $id));
                exit;
            }
        }

        // Tampilan Form Edit (GET)
        $data = [
            'title' => 'Edit ' . $kamar['nomor_kamar'] . ' - KosBook Admin',
            'activePage' => 'admin_kamar',
            'kamar' => $kamar,
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('kamar/edit', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * 6. Halaman Admin: Ubah Status Kamar (Admin Only)
     */
    public function ubah_status(int $id = 0): void {
        AuthHelper::requireRole('admin');
        $kamarModel = $this->model('KamarModel');
        $kamar = $kamarModel->getById($id);

        if (!$kamar) {
            AuthHelper::setFlash('warning', 'Data kamar tidak ditemukan.', 'bi-exclamation-circle-fill');
            header("Location: " . url('kamar/admin'));
            exit;
        }

        // Ambil status dari POST atau GET
        $newStatus = $_POST['status'] ?? ($_GET['status'] ?? '');

        if (!in_array($newStatus, ['tersedia', 'booking', 'terisi'])) {
            AuthHelper::setFlash('danger', 'Pilihan status tidak valid.', 'bi-exclamation-triangle-fill');
            header("Location: " . url('kamar/admin'));
            exit;
        }

        if ($kamarModel->updateStatus($id, $newStatus)) {
            AuthHelper::setFlash('success', 'Status ' . htmlspecialchars($kamar['nomor_kamar']) . ' berhasil diubah menjadi "' . ucfirst($newStatus) . '"!', 'bi-check-circle-fill');
        } else {
            AuthHelper::setFlash('danger', 'Gagal memperbarui status kamar.', 'bi-exclamation-triangle-fill');
        }

        header("Location: " . url('kamar/admin'));
        exit;
    }

    /**
     * 7. Halaman Admin: Hapus Kamar (Admin Only)
     */
    public function hapus(int $id = 0): void {
        AuthHelper::requireRole('admin');
        $kamarModel = $this->model('KamarModel');
        $kamar = $kamarModel->getById($id);

        if (!$kamar) {
            AuthHelper::setFlash('warning', 'Data kamar tidak ditemukan.', 'bi-exclamation-circle-fill');
            header("Location: " . url('kamar/admin'));
            exit;
        }

        // Periksa apakah kamar sedang digunakan di transaksi booking
        if ($kamarModel->hasBookings($id)) {
            AuthHelper::setFlash('danger', 'Kamar ' . htmlspecialchars($kamar['nomor_kamar']) . ' tidak dapat dihapus karena memiliki riwayat pemesanan/booking terkait.', 'bi-shield-x');
            header("Location: " . url('kamar/admin'));
            exit;
        }

        // Hapus kamar
        if ($kamarModel->delete($id)) {
            // Hapus file foto jika bukan default (menggunakan basename untuk keamanan)
            if (!empty($kamar['foto']) && $kamar['foto'] !== 'default_kamar.jpg') {
                $safePhoto = basename($kamar['foto']);
                $photoPath = __DIR__ . '/../../public/images/kamar/' . $safePhoto;
                if (file_exists($photoPath)) {
                    @unlink($photoPath);
                }
            }

            AuthHelper::setFlash('success', 'Kamar ' . htmlspecialchars($kamar['nomor_kamar']) . ' berhasil dihapus dari sistem.', 'bi-trash-fill');
        } else {
            AuthHelper::setFlash('danger', 'Gagal menghapus data kamar dari database.', 'bi-exclamation-triangle-fill');
        }

        header("Location: " . url('kamar/admin'));
        exit;
    }
}
