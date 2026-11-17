<?php
/**
 * AuthHelper
 * Helper manajemen autentikasi, sesi, dan proteksi role-based access control (RBAC)
 */

class AuthHelper {

    /**
     * Pastikan sesi PHP sudah dimulai jika belum aktif dan header belum terkirim
     * Menerapkan pengaturan keamanan cookie sesi (httponly, samesite=Lax)
     */
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            
            $currentParams = session_get_cookie_params();
            session_set_cookie_params([
                'lifetime' => $currentParams['lifetime'] ?? 0,
                'path' => $currentParams['path'] ?? '/',
                'domain' => $currentParams['domain'] ?? '',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /**
     * Menghasilkan atau mengambil token CSRF dari session
     */
    public static function csrfToken(): string {
        self::init();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Menghasilkan tag input hidden untuk CSRF token di form HTML
     */
    public static function csrfField(): string {
        $token = self::csrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verifikasi token CSRF pada request form POST
     * Memeriksa kesesuaian token dengan session menggunakan hash_equals
     */
    public static function verifyCsrf(): bool {
        self::init();
        $submittedToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if ($submittedToken !== null) {
            $sessionToken = $_SESSION['csrf_token'] ?? '';
            return !empty($sessionToken) && hash_equals($sessionToken, (string)$submittedToken);
        }
        return true;
    }

    /**
     * Menyimpan data login user ke dalam session
     */
    public static function login(array $user): void {
        self::init();
        
        // Regenerasi session ID untuk mencegah session fixation attacks
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
    }

    /**
     * Menghapus sesi user saat logout
     */
    public static function logout(): void {
        self::init();
        unset($_SESSION['user']);
        unset($_SESSION['role']);
        unset($_SESSION['logged_in']);
        
        // Hapus cookie session jika ada
        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Regenerasi ID sesi baru yang bersih
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    /**
     * Memeriksa apakah ada user yang sedang login
     */
    public static function check(): bool {
        self::init();
        return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Mendapatkan data user yang sedang login
     */
    public static function user(): ?array {
        self::init();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Mendapatkan ID user yang sedang aktif
     */
    public static function userId(): ?int {
        self::init();
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    /**
     * Mendapatkan role aktif ('admin', 'user', atau 'guest')
     */
    public static function role(): string {
        self::init();
        if (self::check()) {
            return $_SESSION['user']['role'] ?? 'guest';
        }
        return 'guest';
    }

    /**
     * Memeriksa apakah user saat ini adalah admin
     */
    public static function isAdmin(): bool {
        return self::check() && self::role() === 'admin';
    }

    /**
     * Memeriksa apakah user saat ini adalah user/penghuni
     */
    public static function isUser(): bool {
        return self::check() && self::role() === 'user';
    }

    /**
     * Menyimpan flash message ke session
     */
    public static function setFlash(string $type, string $message, string $icon = ''): void {
        self::init();
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
            'icon' => $icon
        ];
    }

    /**
     * Mengambil dan membersihkan flash message dari session
     */
    public static function getFlash(): ?array {
        self::init();
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Middleware/Guard: Wajib Login
     * Guest diarahkan ke login jika mencoba mengakses halaman yang membutuhkan authentication.
     */
    public static function requireLogin(): void {
        self::init();
        if (!self::check()) {
            self::setFlash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.', 'bi-exclamation-triangle-fill');
            header("Location: " . url('auth/login'));
            exit;
        }
    }

    /**
     * Middleware/Guard: Role-Based Access Control
     * - User hanya dapat mengakses halaman user.
     * - Admin hanya dapat mengakses halaman admin.
     * - Guest diarahkan ke login jika mencoba mengakses halaman yang membutuhkan authentication.
     */
    public static function requireRole(string $requiredRole): void {
        self::requireLogin();

        $currentRole = self::role();
        if ($currentRole !== $requiredRole) {
            if ($currentRole === 'user') {
                self::setFlash('danger', 'Akses ditolak! Halaman ini hanya dapat diakses oleh Administrator.', 'bi-shield-x');
                header("Location: " . url('dashboard/user'));
                exit;
            } elseif ($currentRole === 'admin') {
                self::setFlash('warning', 'Halaman ini diperuntukkan bagi Calon Penghuni (User).', 'bi-info-circle-fill');
                header("Location: " . url('dashboard/admin'));
                exit;
            } else {
                self::setFlash('danger', 'Anda tidak memiliki izin untuk mengakses halaman tersebut.', 'bi-shield-slash');
                header("Location: " . url('auth/login'));
                exit;
            }
        }
    }

    /**
     * Middleware/Guard: Hanya untuk Guest (yang belum login)
     * Jika sudah login, diarahkan langsung ke dashboard masing-masing.
     */
    public static function guestOnly(): void {
        self::init();
        if (self::check()) {
            if (self::isAdmin()) {
                header("Location: " . url('dashboard/admin'));
                exit;
            } else {
                header("Location: " . url('dashboard/user'));
                exit;
            }
        }
    }
}
