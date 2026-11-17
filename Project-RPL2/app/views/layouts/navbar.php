<?php
/**
 * Komponen Reusable: Navbar Responsif
 * Menyesuaikan tampilan navigasi berdasarkan status autentikasi dan peran (Guest, User, Admin)
 */

$isLoggedIn = AuthHelper::check();
$currentRole = AuthHelper::role();
$currentUser = AuthHelper::user();
$activePage = $activePage ?? '';
$userName = $currentUser['nama'] ?? ($currentRole === 'admin' ? 'Admin Kos' : 'Pengguna');
?>

<!-- Navbar Utama KosBook -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom sticky-top shadow-sm py-2">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-4 text-white" href="<?= url(''); ?>">
            <span class="brand-icon d-inline-flex justify-content-center align-items-center rounded-3 bg-white bg-opacity-25 text-white" style="width: 36px; height: 36px;">
                <i class="bi bi-houses-fill"></i>
            </span>
            <span>Kos<span class="text-accent text-warning">Book</span></span>
            <?php if ($currentRole === 'admin'): ?>
                <span class="badge bg-danger rounded-pill fs-7 ms-1 px-2 py-1 small">Admin Panel</span>
            <?php elseif ($currentRole === 'user'): ?>
                <span class="badge bg-success rounded-pill fs-7 ms-1 px-2 py-1 small">Penghuni</span>
            <?php endif; ?>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#kosbookNav" aria-controls="kosbookNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="kosbookNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                <?php if ($currentRole === 'admin'): ?>
                    <!-- ================= MENU ADMIN ================= -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'admin_dashboard') ? 'active fw-semibold text-white' : 'text-white-50'; ?> px-3" href="<?= url('dashboard/admin'); ?>">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'admin_kamar') ? 'active fw-semibold text-white' : 'text-white-50'; ?> px-3" href="<?= url('kamar/admin'); ?>">
                            <i class="bi bi-door-closed me-1"></i> Data Kamar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'admin_user') ? 'active fw-semibold text-white' : 'text-white-50'; ?> px-3" href="<?= url('dashboard/admin#data-user'); ?>">
                            <i class="bi bi-people me-1"></i> Data User
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'admin_booking') ? 'active fw-semibold text-white' : 'text-white-50'; ?> px-3" href="<?= url('booking/admin'); ?>">
                            <i class="bi bi-journal-bookmark me-1"></i> Data Booking
                        </a>
                    </li>

                <?php elseif ($currentRole === 'user'): ?>
                    <!-- ================= MENU USER ================= -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'home') ? 'active fw-semibold text-white' : 'text-white-50'; ?> px-3" href="<?= url(''); ?>">
                            <i class="bi bi-house-door me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'kamar') ? 'active fw-semibold text-white' : 'text-white-50'; ?> px-3" href="<?= url('kamar'); ?>">
                            <i class="bi bi-grid me-1"></i> Kamar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'booking_saya') ? 'active fw-semibold text-white' : 'text-white-50'; ?> px-3" href="<?= url('dashboard/user'); ?>">
                            <i class="bi bi-bookmark-check me-1"></i> Booking Saya
                        </a>
                    </li>

                <?php else: ?>
                    <!-- ================= MENU GUEST ================= -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'home') ? 'active fw-semibold text-white' : 'text-white-50'; ?> px-3" href="<?= url(''); ?>">
                            <i class="bi bi-house-door me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'kamar') ? 'active fw-semibold text-white' : 'text-white-50'; ?> px-3" href="<?= url('kamar'); ?>">
                            <i class="bi bi-grid me-1"></i> Kamar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Sisi Kanan: Auth Actions / Profile -->
            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <?php if ($isLoggedIn): ?>
                    <!-- Profil Pengguna & Logout -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi <?= $currentRole === 'admin' ? 'bi-shield-lock-fill' : 'bi-person-circle'; ?>"></i>
                            <span class="small fw-semibold"><?= htmlspecialchars($userName); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                            <li class="px-3 py-1 small text-muted border-bottom">
                                <div>Signed in as:</div>
                                <strong class="text-dark"><?= htmlspecialchars($currentUser['email'] ?? ''); ?></strong>
                            </li>
                            <?php if ($currentRole === 'admin'): ?>
                                <li><a class="dropdown-item small mt-1" href="<?= url('dashboard/admin'); ?>"><i class="bi bi-speedometer2 me-2 text-primary"></i>Panel Admin</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item small mt-1" href="<?= url('dashboard/user'); ?>"><i class="bi bi-bookmark-check me-2 text-primary"></i>Booking Saya</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item small text-danger" href="<?= url('auth/logout'); ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- Guest: Tombol Login & Register -->
                    <a href="<?= url('auth/login'); ?>" class="btn btn-outline-light btn-sm px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                    <a href="<?= url('auth/register'); ?>" class="btn btn-warning btn-sm px-3 py-2 rounded-pill fw-semibold text-dark shadow-sm">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
