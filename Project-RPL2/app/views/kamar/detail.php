<?php
/**
 * Halaman Detail Kamar Kos (User, Guest, Admin)
 * Menampilkan: Foto, Nomor Kamar, Harga, Fasilitas, Deskripsi, Status
 * Serta interaksi tombol 'Booking Sekarang' dengan proteksi Guest harus Login
 */

$isAvailable = ($kamar['status'] === 'tersedia');
$isBooking = ($kamar['status'] === 'booking');
$isTerisi = ($kamar['status'] === 'terisi');

// Styling badge dan status
$statusClass = [
    'tersedia' => 'bg-success text-white',
    'booking'  => 'bg-warning text-dark',
    'terisi'   => 'bg-secondary text-white'
][$kamar['status']] ?? 'bg-primary text-white';

$statusIcon = [
    'tersedia' => 'bi-check-circle-fill',
    'booking'  => 'bi-hourglass-split',
    'terisi'   => 'bi-slash-circle-fill'
][$kamar['status']] ?? 'bi-circle-fill';

$statusLabel = [
    'tersedia' => 'Tersedia untuk Dipesan',
    'booking'  => 'Sedang Dalam Proses Booking',
    'terisi'   => 'Kamar Sudah Terisi (Penuh)'
][$kamar['status']] ?? ucfirst($kamar['status']);

// Ekstrak fasilitas menjadi array
$fasilitasList = is_array($kamar['fasilitas']) 
    ? $kamar['fasilitas'] 
    : array_map('trim', explode(',', $kamar['fasilitas'] ?? ''));

// URL Foto Kamar
$photoUrl = !empty($kamar['foto']) && file_exists(__DIR__ . '/../../public/images/kamar/' . $kamar['foto'])
    ? BASE_URL . '/images/kamar/' . htmlspecialchars($kamar['foto'])
    : (file_exists(__DIR__ . '/../../public/images/' . $kamar['foto'])
        ? BASE_URL . '/images/' . htmlspecialchars($kamar['foto'])
        : BASE_URL . '/images/default_kamar.jpg');

$isLoggedIn = AuthHelper::check();
$currentUser = AuthHelper::user();
?>

<div class="container py-4">
    <!-- Breadcrumb Navigasi -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
            <?php if (AuthHelper::isAdmin()): ?>
                <li class="breadcrumb-item"><a href="<?= url('kamar/admin'); ?>" class="text-decoration-none">Kelola Kamar</a></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?= url('kamar'); ?>" class="text-decoration-none">Daftar Kamar</a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($kamar['nomor_kamar']); ?></li>
        </ol>
    </nav>

    <!-- Reusable Alert Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <?php if (AuthHelper::isAdmin()): ?>
        <!-- Panel Kontrol Cepat untuk Admin -->
        <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-3 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger rounded-pill px-3 py-2">
                        <i class="bi bi-shield-lock-fill me-1"></i> Mode Admin
                    </span>
                    <span class="small text-white-50">Anda login sebagai Administrator dan dapat mengelola langsung kamar ini.</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('kamar/edit/' . $kamar['id']); ?>" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i> Edit Kamar
                    </a>
                    <a href="<?= url('kamar/admin'); ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-list-ul me-1"></i> Data Kamar Admin
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- ================= KOLOM KIRI: FOTO, INFO, FASILITAS, DESKRIPSI ================= -->
        <div class="col-lg-8">
            <!-- 1. Foto Kamar -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 position-relative">
                <img src="<?= $photoUrl; ?>" 
                     alt="<?= htmlspecialchars($kamar['nomor_kamar']); ?>" 
                     class="w-100 object-fit-cover shadow-sm" 
                     style="height: 390px;"
                     onerror="this.src='<?= BASE_URL; ?>/images/default_kamar.jpg'">
                
                <!-- Status Badge Overlay -->
                <span class="badge <?= $statusClass; ?> position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill fw-semibold shadow d-inline-flex align-items-center gap-1">
                    <i class="bi <?= $statusIcon; ?> small"></i> <?= $statusLabel; ?>
                </span>
            </div>

            <!-- 2. Informasi Utama Kamar: Nomor Kamar, Harga, Status -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-light text-secondary border">ID Kamar #00<?= htmlspecialchars($kamar['id']); ?></span>
                            <span class="badge <?= $statusClass; ?> px-2 py-1 small rounded-pill">
                                <?= ucfirst($kamar['status']); ?>
                            </span>
                        </div>
                        <h2 class="fw-bold text-dark mb-1"><?= htmlspecialchars($kamar['nomor_kamar']); ?></h2>
                        <p class="text-secondary small mb-0">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> Gedung Kos KosBook &bull; Lantai <?= substr($kamar['nomor_kamar'], -3, 1) === '2' ? '2' : '1'; ?>
                        </p>
                    </div>
                    <div class="text-lg-end">
                        <span class="text-secondary small d-block">Harga Sewa Kamar</span>
                        <h3 class="fw-bold text-primary mb-0">
                            <?= formatRupiah($kamar['harga']); ?> 
                            <span class="fs-6 fw-normal text-secondary">/ bulan</span>
                        </h3>
                    </div>
                </div>

                <hr class="my-4">

                <!-- 3. Fasilitas Kamar -->
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-stars text-primary fs-5"></i>
                        <h5 class="fw-bold text-dark mb-0">Fasilitas Kamar</h5>
                    </div>
                    <div class="row g-3">
                        <?php if (!empty($fasilitasList)): ?>
                            <?php foreach ($fasilitasList as $fasilitas): if (!empty($fasilitas)): ?>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center gap-2 p-3 rounded-3 bg-light border-0">
                                        <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                                        <span class="small fw-semibold text-dark"><?= htmlspecialchars($fasilitas); ?></span>
                                    </div>
                                </div>
                            <?php endif; endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p class="text-secondary small fst-italic">Informasi fasilitas belum dicantumkan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-4">

                <!-- 4. Deskripsi Lengkap Kamar -->
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-file-text text-primary fs-5"></i>
                        <h5 class="fw-bold text-dark mb-0">Deskripsi Kamar</h5>
                    </div>
                    <div class="text-secondary leading-relaxed small p-3 bg-light bg-opacity-50 rounded-3">
                        <?= nl2br(htmlspecialchars($kamar['deskripsi'] ?? 'Kamar kos bersih, nyaman, dan strategis siap huni.')); ?>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Tata Tertib & Peraturan Kos -->
                <div>
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-shield-exclamation text-warning me-1"></i> Ketentuan &amp; Tata Tertib Kos
                    </h6>
                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-primary mt-1"></i>
                            <span>Batas jam bertamu maksimal pukul 22.00 WIB demi keamanan dan kenyamanan bersama.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-primary mt-1"></i>
                            <span>Dilarang membawa hewan peliharaan tanpa konfirmasi tertulis ke pengelola.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-primary mt-1"></i>
                            <span>Wajib menjaga kebersihan fasilitas bersama (dapur umum, koridor, dan tempat sampah).</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ================= KOLOM KANAN: CARD BOOKING & STATUS ================= -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 85px;">
                <h5 class="fw-bold text-dark mb-3 d-flex align-items-center justify-content-between">
                    <span>Status &amp; Pemesanan</span>
                    <span class="badge <?= $statusClass; ?> px-2 py-1 small rounded-pill fw-semibold">
                        <?= ucfirst($kamar['status']); ?>
                    </span>
                </h5>

                <!-- Ringkasan Tarif -->
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small">Tarif Sewa:</span>
                        <strong class="text-primary fs-5"><?= formatRupiah($kamar['harga']); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small text-muted">
                        <span>Periode Pembayaran:</span>
                        <span>Per Bulan</span>
                    </div>
                </div>

                <!-- Bagian Tombol Booking & Logika Status -->
                <div class="booking-action-box mb-3">
                    <?php if ($isAvailable): ?>
                        <!-- Status: TERSEDIA -->
                        <div class="mb-2">
                            <div class="d-flex align-items-center gap-2 text-success small fw-semibold mb-2">
                                <i class="bi bi-check-circle-fill"></i> Kamar siap untuk dipesan sekarang
                            </div>

                            <?php if ($isLoggedIn): ?>
                                <!-- User Sudah Login: Arahkan ke Form Booking Kamar -->
                                <a href="<?= url('booking/tambah/' . $kamar['id']); ?>" 
                                   class="btn btn-primary w-100 rounded-pill py-2 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-bookmark-plus me-1"></i> Booking Sekarang
                                </a>
                            <?php else: ?>
                                <!-- Guest (Belum Login): Guest harus login sebelum melakukan booking -->
                                <button type="button" 
                                        class="btn btn-primary w-100 rounded-pill py-2 fw-semibold shadow-sm"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalLoginRequired">
                                    <i class="bi bi-bookmark-plus me-1"></i> Booking Sekarang
                                </button>

                                <!-- Catatan Khusus Guest -->
                                <div class="alert alert-warning border-0 rounded-3 p-3 mt-3 mb-0 small">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-info-circle-fill text-warning-emphasis fs-5 flex-shrink-0 mt-0"></i>
                                        <div>
                                            <strong class="text-dark d-block mb-1">Guest Perlu Login:</strong>
                                            <span class="text-secondary">
                                                Guest dapat melihat detail kamar, tetapi harus login sebelum melakukan proses booking.
                                            </span>
                                            <div class="mt-2">
                                                <a href="<?= url('auth/login'); ?>" class="btn btn-warning btn-sm rounded-pill px-3 py-1 text-dark fw-semibold me-1">
                                                    Login Sekarang
                                                </a>
                                                <a href="<?= url('auth/register'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1">
                                                    Daftar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($isBooking): ?>
                        <!-- Status: BOOKING (Tombol Booking Tidak Dapat Digunakan) -->
                        <button type="button" class="btn btn-secondary w-100 rounded-pill py-2 fw-semibold mb-2" disabled style="cursor: not-allowed;">
                            <i class="bi bi-hourglass-split me-1"></i> Booking Sekarang (Sedang Dibooking)
                        </button>
                        
                        <div class="alert alert-warning border-0 rounded-3 p-3 mb-0 small">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-exclamation-circle-fill text-warning fs-5 flex-shrink-0"></i>
                                <div>
                                    <strong class="text-dark d-block">Kamar Sedang Dibooking</strong>
                                    <span class="text-secondary">
                                        Kamar ini saat ini sedang dalam proses reservasi/booking oleh calon penghuni lain, sehingga tombol booking tidak dapat digunakan.
                                    </span>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($isTerisi): ?>
                        <!-- Status: TERISI (Tombol Booking Tidak Dapat Digunakan) -->
                        <button type="button" class="btn btn-secondary w-100 rounded-pill py-2 fw-semibold mb-2" disabled style="cursor: not-allowed;">
                            <i class="bi bi-slash-circle me-1"></i> Booking Sekarang (Kamar Terisi)
                        </button>

                        <div class="alert alert-secondary border-0 rounded-3 p-3 mb-0 small">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-dash-circle-fill text-secondary fs-5 flex-shrink-0"></i>
                                <div>
                                    <strong class="text-dark d-block">Kamar Sudah Terisi</strong>
                                    <span class="text-secondary">
                                        Kamar ini saat ini sudah dihuni penuh. Silakan cek kamar lain yang berstatus tersedia.
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <hr class="my-3">

                <!-- Fasilitas Umum Kos -->
                <div class="small text-secondary">
                    <span class="fw-semibold text-dark d-block mb-2">Fasilitas Bersama Gedung:</span>
                    <div class="d-flex flex-column gap-1">
                        <div><i class="bi bi-wifi text-primary me-2"></i>WiFi High-Speed 24 Jam</div>
                        <div><i class="bi bi-shield-check text-primary me-2"></i>Keamanan CCTV &amp; Akses Kunci</div>
                        <div><i class="bi bi-droplet-half text-primary me-2"></i>Air Bersih Lancar &amp; Dapur Umum</div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-center">
                    <a href="<?= url('kamar'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 w-100 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kamar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= REKOMENDASI KAMAR LAINNYA ================= -->
    <?php if (!empty($relatedRooms)): ?>
        <div class="mt-5 pt-4 border-top">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Pilihan Kamar Kos Lainnya</h4>
                    <p class="text-secondary small mb-0">Lihat alternatif kamar kos yang tersedia untuk perbandingan.</p>
                </div>
                <a href="<?= url('kamar'); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-semibold">
                    Semua Kamar <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-4">
                <?php foreach ($relatedRooms as $kamarItem): ?>
                    <?php 
                        $kamar = $kamarItem; 
                        require __DIR__ . '/../components/room_card.php'; 
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!$isLoggedIn): ?>
<!-- ================= MODAL 1: PERINGATAN LOGIN UNTUK GUEST ================= -->
<div class="modal fade" id="modalLoginRequired" tabindex="-1" aria-labelledby="modalLoginRequiredLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4 pt-0">
                <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-warning bg-opacity-10 text-warning mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-person-lock fs-1 text-warning"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2" id="modalLoginRequiredLabel">Login Terlebih Dahulu</h5>
                <p class="text-secondary small mb-4">
                    Sebagai <strong>Guest</strong>, Anda dapat melihat seluruh detail dan fasilitas kamar kos ini. Namun untuk melakukan booking dan reservasi, silakan masuk ke akun Anda terlebih dahulu.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?= url('auth/login'); ?>" class="btn btn-primary rounded-pill py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login ke Akun Saya
                    </a>
                    <a href="<?= url('auth/register'); ?>" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold">
                        <i class="bi bi-person-plus me-1"></i> Belum Punya Akun? Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- ================= MODAL 2: NOTIFIKASI BOOKING (PROSES BELUM DIIMPLEMENTASIKAN) ================= -->
<div class="modal fade" id="modalBookingNotice" tabindex="-1" aria-labelledby="modalBookingNoticeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4 pt-0">
                <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-info-circle-fill fs-1 text-primary"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2" id="modalBookingNoticeLabel">Fitur Booking Kos</h5>
                <p class="text-secondary small mb-3">
                    Halo, <strong><?= htmlspecialchars($currentUser['nama'] ?? 'Pengguna'); ?></strong>! 
                    Data kamar <strong><?= htmlspecialchars($kamar['nomor_kamar']); ?></strong> berstatus <strong>Tersedia</strong> dan siap dipesan.
                </p>
                <div class="alert alert-info border-0 rounded-3 small text-start p-3 mb-4">
                    <i class="bi bi-clock-history me-1"></i> <strong>Informasi Tahap Pengembangan:</strong><br>
                    Sesuai petunjuk sistem, proses booking dan pembayaran saat ini belum diaktifkan pada tahap ini. Ketersediaan dan detail kamar telah diverifikasi dan siap untuk diintegrasikan pada tahap berikutnya.
                </div>
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold w-100" data-bs-dismiss="modal">
                    Mengerti &amp; Lanjutkan Eksplorasi
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
