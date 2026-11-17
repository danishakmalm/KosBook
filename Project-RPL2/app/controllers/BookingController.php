<?php
/**
 * BookingController
 * Mengelola alur pemesanan (booking) kamar kos oleh User
 * Mengimplementasikan validasi bisnis, perhitungan total harga, dan database transaction
 */

class BookingController extends Controller {

    /**
     * Halaman index booking: alihkan ke dashboard user jika login, atau katalog kamar jika guest
     */
    public function index(): void {
        if (AuthHelper::check()) {
            header("Location: " . url('dashboard/user'));
        } else {
            header("Location: " . url('kamar'));
        }
        exit;
    }

    /**
     * Form Pengajuan Booking Kamar Baru
     * Mendukung GET (menampilkan form) dan POST (memproses simpan booking)
     *
     * Validasi:
     * 1. User harus login.
     * 2. Kamar harus ada.
     * 3. Kamar harus berstatus "tersedia".
     * 4. Tanggal mulai sewa wajib valid.
     * 5. Lama sewa harus berupa angka.
     * 6. Lama sewa minimal 1.
     * 7. User tidak boleh melakukan booking terhadap kamar yang sudah tidak tersedia.
     */
    public function tambah(int $kamarId = 0): void {
        // Validasi 1: User harus login terlebih dahulu
        if (!AuthHelper::check()) {
            AuthHelper::setFlash('warning', 'Silakan login terlebih dahulu untuk melakukan booking kamar kos.', 'bi-box-arrow-in-right');
            header("Location: " . url('auth/login'));
            exit;
        }

        $kamarModel = $this->model('KamarModel');
        $bookingModel = $this->model('BookingModel');

        // Validasi 2: Kamar harus ada di database
        $kamar = $kamarModel->getById($kamarId);
        if (!$kamar) {
            AuthHelper::setFlash('danger', 'Data kamar yang ingin Anda pesan tidak ditemukan.', 'bi-exclamation-triangle-fill');
            header("Location: " . url('kamar'));
            exit;
        }

        // Validasi 3 & 7: Kamar harus berstatus 'tersedia'
        if ($kamar['status'] !== 'tersedia') {
            AuthHelper::setFlash('danger', 'Maaf, ' . htmlspecialchars($kamar['nomor_kamar']) . ' saat ini sudah tidak tersedia untuk dibooking (Status: ' . ucfirst($kamar['status']) . ').', 'bi-slash-circle-fill');
            header("Location: " . url('kamar/detail/' . $kamarId));
            exit;
        }

        // ================= JIKA METHOD POST (SUBMIT FORM BOOKING) =================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validasi Token CSRF jika form mengirimkan token
            if (isset($_POST['csrf_token']) && !AuthHelper::verifyCsrf()) {
                AuthHelper::setFlash('danger', 'Sesi pengajuan booking telah kedaluwarsa. Silakan coba lagi.', 'bi-shield-x');
                header("Location: " . url('booking/tambah/' . $kamarId));
                exit;
            }

            $tanggalMulai = trim($_POST['tanggal_mulai'] ?? '');
            $lamaSewa = trim($_POST['lama_sewa'] ?? '');

            // Simpan input sementara untuk pengalaman pengguna saat error
            $_SESSION['old_booking'] = [
                'tanggal_mulai' => $tanggalMulai,
                'lama_sewa' => $lamaSewa
            ];

            // Validasi 4: Tanggal mulai sewa wajib valid dan tidak boleh masa lalu
            $today = date('Y-m-d');
            $parsedDate = strtotime($tanggalMulai);
            if (empty($tanggalMulai) || !$parsedDate || $tanggalMulai < $today) {
                AuthHelper::setFlash('danger', 'Tanggal mulai sewa wajib diisi dengan tanggal yang valid dan tidak boleh mendahului hari ini.', 'bi-calendar-x');
                header("Location: " . url('booking/tambah/' . $kamarId));
                exit;
            }

            // Validasi 5: Lama sewa harus berupa angka
            if (!is_numeric($lamaSewa)) {
                AuthHelper::setFlash('danger', 'Lama sewa harus berupa angka yang valid.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('booking/tambah/' . $kamarId));
                exit;
            }

            // Validasi 6: Lama sewa minimal 1 bulan
            $lamaSewaInt = (int) $lamaSewa;
            if ($lamaSewaInt < 1) {
                AuthHelper::setFlash('danger', 'Lama sewa minimal adalah 1 bulan.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('booking/tambah/' . $kamarId));
                exit;
            }

            // Validasi 7 & Eksekusi Simpan dengan Database Transaction:
            // - total_harga dihitung: harga_kamar * lama_sewa
            // - data masuk ke tabel booking (status = 'menunggu')
            // - status kamar berubah menjadi 'booking'
            $bookingId = $bookingModel->createBooking([
                'user_id' => AuthHelper::userId(),
                'kamar_id' => $kamarId,
                'tanggal_mulai' => $tanggalMulai,
                'lama_sewa' => $lamaSewaInt
            ]);

            if ($bookingId) {
                // Bersihkan old input session
                unset($_SESSION['old_booking']);

                // Set flash message sukses
                AuthHelper::setFlash('success', 'Booking kamar berhasil diajukan! Status pesanan Anda saat ini adalah Menunggu Konfirmasi pengelola kos.', 'bi-check-circle-fill');

                // Tampilkan halaman konfirmasi booking
                header("Location: " . url('booking/konfirmasi/' . $bookingId));
                exit;
            } else {
                AuthHelper::setFlash('danger', 'Gagal memproses booking. Kamar mungkin baru saja dipesan oleh calon penghuni lain.', 'bi-x-circle-fill');
                header("Location: " . url('kamar/detail/' . $kamarId));
                exit;
            }
        }

        // ================= JIKA METHOD GET (TAMPILKAN FORM BOOKING) =================
        $oldBooking = $_SESSION['old_booking'] ?? [
            'tanggal_mulai' => date('Y-m-d', strtotime('+1 day')),
            'lama_sewa' => 1
        ];
        unset($_SESSION['old_booking']);

        $data = [
            'title' => 'Formulir Booking ' . $kamar['nomor_kamar'] . ' - KosBook',
            'activePage' => 'kamar',
            'kamar' => $kamar,
            'currentUser' => AuthHelper::user(),
            'old' => $oldBooking,
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('booking/tambah', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Alias method create() mengarah ke tambah()
     */
    public function create(int $kamarId = 0): void {
        $this->tambah($kamarId);
    }

    /**
     * Halaman Konfirmasi Booking Kamar
     * Menampilkan rincian pesanan yang baru berhasil dibuat (Status: Menunggu Konfirmasi)
     */
    public function konfirmasi(int $bookingId = 0): void {
        // Validasi: Pengguna harus login
        AuthHelper::requireLogin();

        $bookingModel = $this->model('BookingModel');
        $booking = $bookingModel->getById($bookingId);

        if (!$booking) {
            AuthHelper::setFlash('danger', 'Data pesanan booking tidak ditemukan.', 'bi-exclamation-triangle-fill');
            header("Location: " . url('kamar'));
            exit;
        }

        // Otorisasi: Hanya pemilik booking atau Admin yang berhak melihat konfirmasi ini
        $currentUserId = AuthHelper::userId();
        if ($booking['user_id'] != $currentUserId && !AuthHelper::isAdmin()) {
            AuthHelper::setFlash('danger', 'Anda tidak memiliki hak untuk melihat data booking pengguna lain.', 'bi-shield-x');
            header("Location: " . url('dashboard/user'));
            exit;
        }

        $data = [
            'title' => 'Konfirmasi Pemesanan #' . $booking['id'] . ' - KosBook',
            'activePage' => 'booking_saya',
            'booking' => $booking,
            'currentUser' => AuthHelper::user(),
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('booking/konfirmasi', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Halaman Admin: Kelola Seluruh Data Booking (Admin Only)
     * Menampilkan: ID Booking, Nama User, Email, Nomor Kamar, Tanggal Booking, Tanggal Mulai, Lama Sewa, Total Harga, Status
     */
    public function admin(): void {
        AuthHelper::requireRole('admin');

        $bookingModel = $this->model('BookingModel');
        $allBookings = $bookingModel->getAllBookings();
        $stats = $bookingModel->getStats();

        $filterStatus = trim($_GET['status'] ?? 'semua');
        if ($filterStatus !== 'semua' && in_array($filterStatus, ['menunggu', 'dikonfirmasi', 'ditolak', 'selesai'])) {
            $filteredBookings = array_values(array_filter($allBookings, fn($b) => $b['status'] === $filterStatus));
        } else {
            $filteredBookings = $allBookings;
        }

        $data = [
            'title' => 'Kelola Data Booking - KosBook Admin',
            'activePage' => 'admin_booking',
            'bookings' => $filteredBookings,
            'allBookings' => $allBookings,
            'filterStatus' => $filterStatus,
            'stats' => $stats,
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('booking/admin', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Halaman Detail Booking (Bisa diakses oleh Admin atau Pemilik Booking)
     * Menampilkan informasi lengkap booking:
     * - ID Booking, Nama User, Email, Nomor Kamar, Tanggal Booking, Tanggal Mulai, Lama Sewa, Total Harga, Status
     */
    public function detail(int $id = 0): void {
        AuthHelper::requireLogin();

        $bookingModel = $this->model('BookingModel');
        $booking = $bookingModel->getById($id);

        if (!$booking) {
            AuthHelper::setFlash('danger', 'Data detail booking tidak ditemukan.', 'bi-exclamation-triangle-fill');
            if (AuthHelper::isAdmin()) {
                header("Location: " . url('booking/admin'));
            } else {
                header("Location: " . url('dashboard/user'));
            }
            exit;
        }

        // Access Control: Pengguna biasa hanya boleh melihat booking miliknya sendiri
        $currentUserId = AuthHelper::userId();
        if ($booking['user_id'] != $currentUserId && !AuthHelper::isAdmin()) {
            AuthHelper::setFlash('danger', 'Akses ditolak! Anda tidak memiliki izin untuk melihat booking pengguna lain.', 'bi-shield-x');
            header("Location: " . url('dashboard/user'));
            exit;
        }

        $data = [
            'title' => 'Detail Booking #BK-00' . $booking['id'] . ' - KosBook',
            'activePage' => AuthHelper::isAdmin() ? 'admin_booking' : 'booking_saya',
            'booking' => $booking,
            'isAdmin' => AuthHelper::isAdmin(),
            'currentUser' => AuthHelper::user(),
            'flash' => AuthHelper::getFlash()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('booking/detail', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Aksi Admin: Konfirmasi Booking (Admin Only)
     * Menggunakan Database Transaction:
     *   status booking: menunggu -> dikonfirmasi
     *   status kamar  : booking  -> terisi
     * Pastikan admin tidak dapat mengkonfirmasi booking yang sudah ditolak atau sudah dikonfirmasi.
     */
    public function konfirmasi_admin(int $id = 0): void {
        AuthHelper::requireRole('admin');

        $bookingModel = $this->model('BookingModel');
        $result = $bookingModel->confirmBooking($id);

        if ($result['success']) {
            AuthHelper::setFlash('success', 'Booking #BK-00' . $id . ' (' . htmlspecialchars($result['booking']['nomor_kamar'] ?? 'Kamar') . ') berhasil dikonfirmasi! Status kamar telah diperbarui menjadi "Terisi".', 'bi-check-circle-fill');
        } else {
            AuthHelper::setFlash('danger', $result['error'] ?? 'Gagal mengkonfirmasi booking.', 'bi-exclamation-triangle-fill');
        }

        // Arahkan kembali ke halaman asal (detail atau daftar booking)
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'booking/detail') !== false) {
            header("Location: " . url('booking/detail/' . $id));
        } elseif (strpos($referer, 'dashboard/admin') !== false) {
            header("Location: " . url('dashboard/admin#data-booking'));
        } else {
            header("Location: " . url('booking/admin'));
        }
        exit;
    }

    /**
     * Aksi Admin: Tolak Booking (Admin Only)
     * Menggunakan Database Transaction:
     *   status booking: menunggu -> ditolak
     *   status kamar  : booking  -> tersedia
     * Pastikan admin tidak dapat menolak booking yang sudah ditolak atau sudah dikonfirmasi.
     */
    public function tolak_admin(int $id = 0): void {
        AuthHelper::requireRole('admin');

        $bookingModel = $this->model('BookingModel');
        $result = $bookingModel->rejectBooking($id);

        if ($result['success']) {
            AuthHelper::setFlash('success', 'Booking #BK-00' . $id . ' (' . htmlspecialchars($result['booking']['nomor_kamar'] ?? 'Kamar') . ') telah ditolak! Status kamar telah dikembalikan menjadi "Tersedia".', 'bi-info-circle-fill');
        } else {
            AuthHelper::setFlash('danger', $result['error'] ?? 'Gagal menolak booking.', 'bi-exclamation-triangle-fill');
        }

        // Arahkan kembali ke halaman asal
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'booking/detail') !== false) {
            header("Location: " . url('booking/detail/' . $id));
        } elseif (strpos($referer, 'dashboard/admin') !== false) {
            header("Location: " . url('dashboard/admin#data-booking'));
        } else {
            header("Location: " . url('booking/admin'));
        }
        exit;
    }
}
