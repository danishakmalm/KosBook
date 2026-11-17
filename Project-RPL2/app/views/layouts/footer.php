<!-- Komponen Reusable: Footer KosBook -->
<footer class="bg-dark text-white pt-5 pb-4 mt-auto border-top border-secondary border-opacity-25" id="kontak">
    <div class="container">
        <div class="row g-4 justify-content-between">
            <!-- Kolom Brand & Deskripsi -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="d-inline-flex justify-content-center align-items-center rounded-3 bg-white bg-opacity-10 text-white" style="width: 38px; height: 38px;">
                        <i class="bi bi-houses-fill fs-5"></i>
                    </span>
                    <span class="fs-4 fw-bold text-white">Kos<span class="text-warning">Book</span></span>
                </div>
                <p class="text-secondary small leading-relaxed mb-3">
                    Sistem Booking Kamar Kos Berbasis Web modern yang dirancang untuk memudahkan mahasiswa dan calon penghuni menemukan kamar idaman dengan proses reservasi yang praktis dan transparan.
                </p>
                <div class="d-flex gap-2">
                    <span class="badge bg-secondary-subtle text-dark border px-3 py-2 rounded-pill small">Bootstrap 5</span>
                    <span class="badge bg-secondary-subtle text-dark border px-3 py-2 rounded-pill small">PHP Native MVC</span>
                    <span class="badge bg-secondary-subtle text-dark border px-3 py-2 rounded-pill small">MySQL</span>
                </div>
            </div>

            <!-- Tautan Halaman (Guest & User) -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="fw-bold text-white mb-3 text-uppercase small">Navigasi</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <li><a href="<?= url(''); ?>" class="text-secondary text-decoration-none hover-white">Home</a></li>
                    <li><a href="<?= url('kamar'); ?>" class="text-secondary text-decoration-none hover-white">Daftar Kamar</a></li>
                    <li><a href="<?= url('auth/login'); ?>" class="text-secondary text-decoration-none hover-white">Login</a></li>
                    <li><a href="<?= url('auth/register'); ?>" class="text-secondary text-decoration-none hover-white">Register</a></li>
                </ul>
            </div>

            <!-- Panel Pengguna & Admin -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="fw-bold text-white mb-3 text-uppercase small">Akses Panel</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <li><a href="<?= url('dashboard/user'); ?>" class="text-secondary text-decoration-none hover-white"><i class="bi bi-person me-1"></i> Dashboard User</a></li>
                    <li><a href="<?= url('dashboard/admin'); ?>" class="text-secondary text-decoration-none hover-white"><i class="bi bi-shield-lock me-1"></i> Dashboard Admin</a></li>
                    <li><a href="<?= url('kamar'); ?>" class="text-secondary text-decoration-none hover-white"><i class="bi bi-grid me-1"></i> Katalog Kamar</a></li>
                </ul>
            </div>

            <!-- Kontak Pengelola -->
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold text-white mb-3 text-uppercase small">Kontak Pengelola</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary">
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt text-warning"></i>
                        <span>Area Kampus Utama, Indonesia</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-telephone text-warning"></i>
                        <span>+62 812-3456-7890</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope text-warning"></i>
                        <span>pengelola@kosbook.local</span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="row align-items-center justify-content-between small text-secondary">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                &copy; <?= date('Y'); ?> <strong>KosBook</strong>. Sistem Booking Kamar Kos Berbasis Web.
            </div>
            <div class="col-md-6 text-center text-md-end">
                <span>UI Bersih, Modern, dan Responsif dengan Bootstrap 5</span>
            </div>
        </div>
    </div>
</footer>

<?php
// Komponen AI Chatbot KosBook (Fairy untuk User/Guest, Jarvis untuk Admin)
// Ditampilkan di semua halaman publik dan dashboard, dikecualikan di halaman login/register
if (!in_array($activePage ?? '', ['login', 'register'])):
    require __DIR__ . '/../components/chatbot.php';
?>
<!-- Script Chatbot AI -->
<script src="<?= BASE_URL; ?>/js/chatbot.js"></script>
<?php endif; ?>

<!-- Bootstrap 5.3.3 JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Vanilla JS Kustom -->
<script src="<?= BASE_URL; ?>/js/script.js"></script>
</body>
</html>
