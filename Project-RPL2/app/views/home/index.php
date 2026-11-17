<!-- Hero Section -->
<section class="hero-section text-white position-relative overflow-hidden py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #1e40af 100%);">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <!-- Teks Hero -->
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-20 px-3 py-1 rounded-pill mb-3 text-light small fw-medium">
                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Sederhana &amp; Modern</span>
                    <span>Sistem Reservasi Kamar Kos Online</span>
                </div>
                <h1 class="display-5 fw-bold lh-sm mb-3">
                    <?= htmlspecialchars($hero['headline']); ?>
                </h1>
                <p class="lead text-white-50 mb-4 fs-6">
                    <?= htmlspecialchars($hero['subheadline']); ?>
                </p>

                <!-- Search & Filter Teaser Bar -->
                <div class="card border-0 shadow-lg p-3 rounded-4 bg-white text-dark mb-4">
                    <form class="row g-2 align-items-center" action="<?= url('kamar'); ?>" method="GET">
                        <input type="hidden" name="url" value="kamar">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="bi bi-door-open text-primary me-1"></i> Status Kamar
                            </label>
                            <select class="form-select rounded-3 border-light-subtle bg-light small" name="status">
                                <option value="semua">Semua Status</option>
                                <option value="tersedia" selected>Kamar Tersedia</option>
                                <option value="booking">Sedang Dibooking</option>
                                <option value="terisi">Sudah Terisi</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="bi bi-tag text-success me-1"></i> Estimasi Budget
                            </label>
                            <select class="form-select rounded-3 border-light-subtle bg-light small" name="budget">
                                <option value="">Semua Tarif</option>
                                <option value="1">&lt; Rp 1 Juta / bln</option>
                                <option value="2">Rp 1 - 1.5 Juta / bln</option>
                                <option value="3">&gt; Rp 1.5 Juta / bln</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-none d-md-block small fw-semibold text-transparent mb-1">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Nilai Tambah -->
                <div class="d-flex flex-wrap gap-4 text-white-50 small">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-warning"></i>
                        <span>Tanpa Biaya Admin</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-warning"></i>
                        <span>Konfirmasi Cepat</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-warning"></i>
                        <span>Status Kamar Transparan</span>
                    </div>
                </div>
            </div>

            <!-- Preview Card Samping Hero -->
            <div class="col-lg-5 d-none d-lg-block">
                <div class="p-4 rounded-4 shadow-lg bg-white text-dark position-relative border">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill">
                            <i class="bi bi-shield-check me-1"></i> Sistem Terverifikasi
                        </span>
                        <span class="text-secondary small">Real-time</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3">
                        <div class="avatar-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fs-4 flex-shrink-0" style="width: 50px; height: 50px;">
                            <i class="bi bi-door-open"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Kamar A-101</div>
                            <div class="small text-success fw-semibold">Status: Siap Huni (Tersedia)</div>
                        </div>
                    </div>
                    <div class="p-3 border rounded-3 bg-light-subtle">
                        <div class="d-flex justify-content-between small text-secondary mb-1">
                            <span>Tingkat Ketersediaan Kamar</span>
                            <span class="fw-bold text-primary">50% Tersedia</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: 50%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistik Strip -->
<section class="bg-white border-bottom py-4 shadow-sm">
    <div class="container">
        <div class="row g-4 text-center">
            <?php foreach ($statistics as $stat): ?>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <div class="rounded-3 text-primary bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi <?= htmlspecialchars($stat['icon']); ?> fs-3"></i>
                        </div>
                        <div class="text-start">
                            <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($stat['number']); ?></h4>
                            <span class="text-secondary small"><?= htmlspecialchars($stat['label']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section Rekomendasi Kamar Kos Pilihan -->
<section class="py-5" id="daftar-kamar">
    <div class="container py-3">
        <div class="row align-items-end mb-4">
            <div class="col-md-8">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill mb-2">
                    Kamar Pilihan
                </span>
                <h2 class="fw-bold text-dark mb-1">Daftar Kamar Kos Unggulan</h2>
                <p class="text-secondary small mb-0">Lihat beberapa kamar favorit yang siap dipesan langsung secara online.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= url('kamar'); ?>" class="btn btn-outline-primary rounded-pill px-4 fw-medium btn-sm">
                    Lihat Semua Kamar <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- Grid Menggunakan Reusable Component: room_card.php -->
        <div class="row g-4">
            <?php foreach ($featuredRooms as $kamar): ?>
                <?php require __DIR__ . '/../components/room_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section Alur Pemesanan (Cara Kerja) -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container py-3">
        <div class="text-center mx-auto mb-5" style="max-width: 650px;">
            <span class="badge bg-warning bg-opacity-20 text-dark fw-semibold px-3 py-2 rounded-pill mb-2">
                Panduan Mudah
            </span>
            <h2 class="fw-bold text-dark">Alur Booking Kamar di KosBook</h2>
            <p class="text-secondary small">Empat langkah praktis untuk mendapatkan kamar kos yang Anda inginkan.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm fs-5" style="width: 50px; height: 50px;">
                        1
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Pilih Kamar</h6>
                    <p class="text-secondary small mb-0">Cek daftar kamar, foto, fasilitas, dan harga sewa.</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm fs-5" style="width: 50px; height: 50px;">
                        2
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Login / Daftar</h6>
                    <p class="text-secondary small mb-0">Masuk menggunakan akun calon penghuni.</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm fs-5" style="width: 50px; height: 50px;">
                        3
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Ajukan Booking</h6>
                    <p class="text-secondary small mb-0">Tentukan tanggal masuk dan durasi sewa.</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm fs-5" style="width: 50px; height: 50px;">
                        4
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Konfirmasi Admin</h6>
                    <p class="text-secondary small mb-0">Pemilik kos memverifikasi pesanan Anda.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 bg-light">
    <div class="container py-2">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-primary text-white" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;">
            <div class="row align-items-center justify-content-between g-4">
                <div class="col-lg-8 text-center text-lg-start">
                    <h3 class="fw-bold mb-2">Ingin Memesan Kamar Kos Sekarang?</h3>
                    <p class="text-white-50 mb-0">Temukan kamar kos yang nyaman dan strategis dengan proses reservasi online yang mudah.</p>
                </div>
                <div class="col-lg-4 text-center text-lg-end">
                    <a href="<?= url('kamar'); ?>" class="btn btn-warning text-dark btn-lg px-4 py-2 rounded-pill fw-semibold shadow">
                        <i class="bi bi-grid me-2"></i>Eksplorasi Kamar
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
