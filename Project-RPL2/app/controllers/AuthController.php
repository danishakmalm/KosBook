<?php
/**
 * AuthController
 * Mengelola proses autentikasi pengguna (Login, Register, Logout) dan proteksi sesi
 */

class AuthController extends Controller {

    /**
     * Default redirect ke login
     */
    public function index(): void {
        header("Location: " . url('auth/login'));
        exit;
    }

    /**
     * Menangani form dan proses autentikasi Login
     */
    public function login(): void {
        // Jika sudah login, langsung alihkan ke dashboard sesuai perannya
        AuthHelper::guestOnly();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validasi CSRF Token jika dikirimkan form
            if (isset($_POST['csrf_token']) && !AuthHelper::verifyCsrf()) {
                AuthHelper::setFlash('danger', 'Sesi form login telah kedaluwarsa. Silakan muat ulang halaman.', 'bi-shield-x');
                header("Location: " . url('auth/login'));
                exit;
            }

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validasi: Semua field wajib diisi
            if (empty($email) || empty($password)) {
                $_SESSION['old'] = ['email' => $email];
                AuthHelper::setFlash('danger', 'Semua field wajib diisi.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('auth/login'));
                exit;
            }

            // Validasi: Format email harus valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['old'] = ['email' => $email];
                AuthHelper::setFlash('danger', 'Format alamat email tidak valid.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('auth/login'));
                exit;
            }

            // Cek pengguna di database
            $userModel = $this->model('UserModel');
            $user = $userModel->findByEmail($email);

            // Verifikasi kecocokan password menggunakan password_verify()
            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['old'] = ['email' => $email];
                AuthHelper::setFlash('danger', 'Email atau kata sandi salah. Silakan periksa kembali.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('auth/login'));
                exit;
            }

            // Autentikasi berhasil: Buat sesi login
            AuthHelper::login($user);
            AuthHelper::setFlash('success', 'Selamat datang kembali, ' . htmlspecialchars($user['nama']) . '!', 'bi-check-circle-fill');

            // Role-based Access: Arahkan ke dashboard sesuai role
            if ($user['role'] === 'admin') {
                header("Location: " . url('dashboard/admin'));
            } else {
                header("Location: " . url('dashboard/user'));
            }
            exit;
        }

        // Tampilan Form Login (GET)
        $data = [
            'title' => 'Login - KosBook',
            'activePage' => 'login',
            'flash' => AuthHelper::getFlash(),
            'old' => $_SESSION['old'] ?? []
        ];
        unset($_SESSION['old']);

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('auth/login', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Menangani pendaftaran akun user baru
     */
    public function register(): void {
        // Jika sudah login, langsung alihkan ke dashboard sesuai perannya
        AuthHelper::guestOnly();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validasi CSRF Token jika dikirimkan form
            if (isset($_POST['csrf_token']) && !AuthHelper::verifyCsrf()) {
                AuthHelper::setFlash('danger', 'Sesi pendaftaran telah kedaluwarsa. Silakan muat ulang halaman.', 'bi-shield-x');
                header("Location: " . url('auth/register'));
                exit;
            }

            $nama = trim($_POST['nama'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            // Validasi 1: Semua field wajib diisi
            if (empty($nama) || empty($email) || empty($password) || empty($password_confirm)) {
                $_SESSION['old'] = ['nama' => $nama, 'email' => $email];
                AuthHelper::setFlash('danger', 'Semua field wajib diisi.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('auth/register'));
                exit;
            }

            // Validasi 2: Email harus valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['old'] = ['nama' => $nama, 'email' => $email];
                AuthHelper::setFlash('danger', 'Format alamat email tidak valid.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('auth/register'));
                exit;
            }

            // Validasi 3: Password minimal 6 karakter
            if (strlen($password) < 6) {
                $_SESSION['old'] = ['nama' => $nama, 'email' => $email];
                AuthHelper::setFlash('danger', 'Password minimal harus 6 karakter.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('auth/register'));
                exit;
            }

            // Validasi 4: Password dan konfirmasi harus sama
            if ($password !== $password_confirm) {
                $_SESSION['old'] = ['nama' => $nama, 'email' => $email];
                AuthHelper::setFlash('danger', 'Password dan konfirmasi password tidak cocok.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('auth/register'));
                exit;
            }

            // Validasi 5: Email tidak boleh duplikat
            $userModel = $this->model('UserModel');
            if ($userModel->emailExists($email)) {
                $_SESSION['old'] = ['nama' => $nama, 'email' => $email];
                AuthHelper::setFlash('danger', 'Alamat email sudah terdaftar. Silakan gunakan email lain atau masuk.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('auth/register'));
                exit;
            }

            // Simpan ke database dengan password_hash()
            $userId = $userModel->register([
                'nama' => $nama,
                'email' => $email,
                'password' => $password,
                'role' => 'user'
            ]);

            if ($userId) {
                AuthHelper::setFlash('success', 'Pendaftaran akun berhasil! Silakan masuk dengan email dan kata sandi Anda.', 'bi-check-circle-fill');
                header("Location: " . url('auth/login'));
                exit;
            } else {
                $_SESSION['old'] = ['nama' => $nama, 'email' => $email];
                AuthHelper::setFlash('danger', 'Terjadi kesalahan sistem saat menyimpan data akun. Coba beberapa saat lagi.', 'bi-exclamation-triangle-fill');
                header("Location: " . url('auth/register'));
                exit;
            }
        }

        // Tampilan Form Register (GET)
        $data = [
            'title' => 'Daftar Akun Baru - KosBook',
            'activePage' => 'register',
            'flash' => AuthHelper::getFlash(),
            'old' => $_SESSION['old'] ?? []
        ];
        unset($_SESSION['old']);

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('auth/register', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Menangani proses Logout pengguna
     */
    public function logout(): void {
        AuthHelper::logout();
        AuthHelper::setFlash('info', 'Anda telah berhasil logout dari sistem KosBook.', 'bi-box-arrow-right');
        header("Location: " . url('auth/login'));
        exit;
    }
}
