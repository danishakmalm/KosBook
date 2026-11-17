<?php
/**
 * Halaman Daftar Kamar Kos (User & Guest)
 * Dilengkapi pencarian (nomor kamar & fasilitas) dan filter (status, harga min, harga max, sorting)
 */
?>
<div class="container py-4">
    <!-- Breadcrumb Navigasi -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Daftar Kamar</li>
        </ol>
    </nav>

    <!-- Reusable Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <!-- Header & Ringkasan Status -->
    <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-7">
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-grid-fill text-primary me-2"></i>Daftar Kamar Kos
            </h2>
            <p class="text-secondary small mb-0">
                Pilih kamar kos yang sesuai dengan preferensi, fasilitas, dan anggaran bulanan Anda.
            </p>
        </div>
        <div class="col-lg-5 text-lg-end">
            <div class="d-inline-flex flex-wrap gap-2 justify-content-lg-end">
                <span class="badge bg-white text-secondary border px-3 py-2 rounded-pill small shadow-sm">
                    <i class="bi bi-door-closed text-primary me-1"></i> Total: <strong><?= $totalRooms; ?></strong> Kamar
                </span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill small">
                    <i class="bi bi-check-circle-fill me-1"></i> <strong><?= $stats['kamar_tersedia'] ?? 0; ?></strong> Tersedia
                </span>
                <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 px-3 py-2 rounded-pill small">
                    <i class="bi bi-hourglass-split me-1"></i> <strong><?= $stats['kamar_booking'] ?? 0; ?></strong> Booking
                </span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill small">
                    <i class="bi bi-slash-circle me-1"></i> <strong><?= $stats['kamar_terisi'] ?? 0; ?></strong> Terisi
                </span>
            </div>
        </div>
    </div>

    <!-- Card Filter & Pencarian Kamar -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <form action="<?= BASE_URL; ?>/index.php" method="GET" id="formFilterKamar">
            <input type="hidden" name="url" value="kamar">

            <div class="row g-3">
                <!-- 1. Pencarian: Nomor Kamar atau Fasilitas -->
                <div class="col-lg-5 col-md-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">
                        <i class="bi bi-search text-primary me-1"></i> Cari Nomor Kamar atau Fasilitas
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="q" 
                               id="inputKeyword"
                               class="form-control bg-light border-start-0 rounded-end-3 py-2 small" 
                               placeholder="Contoh: A-101, AC, WiFi, Kamar Mandi..." 
                               value="<?= htmlspecialchars($filters['q'] ?? ''); ?>">
                    </div>
                </div>

                <!-- 2. Filter: Status Kamar -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">
                        <i class="bi bi-door-open text-primary me-1"></i> Status Kamar
                    </label>
                    <select name="status" class="form-select bg-light rounded-3 py-2 small">
                        <option value="semua" <?= ($filters['status'] === 'semua' || empty($filters['status'])) ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="tersedia" <?= ($filters['status'] === 'tersedia') ? 'selected' : ''; ?>>Tersedia (Bisa Dipesan)</option>
                        <option value="booking" <?= ($filters['status'] === 'booking') ? 'selected' : ''; ?>>Dibooking (Proses Reservasi)</option>
                        <option value="terisi" <?= ($filters['status'] === 'terisi') ? 'selected' : ''; ?>>Terisi (Penuh)</option>
                    </select>
                </div>

                <!-- 3. Pengurutan (Sort) -->
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">
                        <i class="bi bi-sort-down text-primary me-1"></i> Urutkan Berdasarkan
                    </label>
                    <select name="sort" class="form-select bg-light rounded-3 py-2 small">
                        <option value="terbaru" <?= ($filters['sort'] === 'terbaru') ? 'selected' : ''; ?>>Kamar Terbaru</option>
                        <option value="harga_asc" <?= ($filters['sort'] === 'harga_asc' || $filters['sort'] === 'termurah') ? 'selected' : ''; ?>>Harga: Terendah ke Tertinggi</option>
                        <option value="harga_desc" <?= ($filters['sort'] === 'harga_desc' || $filters['sort'] === 'termahal') ? 'selected' : ''; ?>>Harga: Tertinggi ke Terendah</option>
                        <option value="nomor_asc" <?= ($filters['sort'] === 'nomor_asc') ? 'selected' : ''; ?>>Nomor Kamar (A - Z)</option>
                    </select>
                </div>

                <!-- 4. Filter: Harga Minimum -->
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">
                        <i class="bi bi-cash-coin text-success me-1"></i> Harga Minimum (Rp / bulan)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary small">Rp</span>
                        <input type="number" 
                               name="min_harga" 
                               class="form-control bg-light rounded-end-3 py-2 small" 
                               placeholder="Contoh: 800000" 
                               min="0" 
                               step="50000"
                               value="<?= isset($filters['min_harga']) && $filters['min_harga'] !== null ? htmlspecialchars((string)$filters['min_harga']) : ''; ?>">
                    </div>
                </div>

                <!-- 5. Filter: Harga Maksimum -->
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">
                        <i class="bi bi-cash-stack text-success me-1"></i> Harga Maksimum (Rp / bulan)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary small">Rp</span>
                        <input type="number" 
                               name="max_harga" 
                               class="form-control bg-light rounded-end-3 py-2 small" 
                               placeholder="Contoh: 1500000" 
                               min="0" 
                               step="50000"
                               value="<?= isset($filters['max_harga']) && $filters['max_harga'] !== null ? htmlspecialchars((string)$filters['max_harga']) : ''; ?>">
                    </div>
                </div>

                <!-- 6. Tombol Aksi: Terapkan Filter & Reset -->
                <div class="col-lg-4 col-md-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill py-2 px-4 fw-semibold flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-funnel-fill"></i> Terapkan Filter
                    </button>
                    <?php if ($isFiltered): ?>
                        <a href="<?= url('kamar'); ?>" class="btn btn-outline-secondary rounded-pill py-2 px-3 fw-semibold d-flex align-items-center gap-1" title="Bersihkan semua filter">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Cepat Filter Status (Pills Sederhana) -->
            <div class="d-flex flex-wrap align-items-center gap-2 pt-3 mt-3 border-top">
                <span class="small text-secondary fw-semibold me-1">Filter Cepat Status:</span>
                <a href="<?= url('kamar'); ?>" 
                   class="btn btn-sm rounded-pill px-3 <?= ($filters['status'] === 'semua' && empty($filters['q']) && $filters['min_harga'] === null && $filters['max_harga'] === null) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    Semua (<?= $totalRooms; ?>)
                </a>
                <a href="<?= url('kamar') . '&status=tersedia' . (!empty($filters['q']) ? '&q=' . urlencode($filters['q']) : ''); ?>" 
                   class="btn btn-sm rounded-pill px-3 <?= ($filters['status'] === 'tersedia') ? 'btn-success' : 'btn-outline-success'; ?>">
                    <i class="bi bi-check-circle me-1"></i> Tersedia (<?= $stats['kamar_tersedia'] ?? 0; ?>)
                </a>
                <a href="<?= url('kamar') . '&status=booking' . (!empty($filters['q']) ? '&q=' . urlencode($filters['q']) : ''); ?>" 
                   class="btn btn-sm rounded-pill px-3 <?= ($filters['status'] === 'booking') ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'; ?>">
                    <i class="bi bi-hourglass-split me-1"></i> Dibooking (<?= $stats['kamar_booking'] ?? 0; ?>)
                </a>
                <a href="<?= url('kamar') . '&status=terisi' . (!empty($filters['q']) ? '&q=' . urlencode($filters['q']) : ''); ?>" 
                   class="btn btn-sm rounded-pill px-3 <?= ($filters['status'] === 'terisi') ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                    <i class="bi bi-slash-circle me-1"></i> Terisi (<?= $stats['kamar_terisi'] ?? 0; ?>)
                </a>
            </div>
        </form>
    </div>

    <!-- Active Filter Tags & Hasil Pencarian -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="text-secondary small">
                Menampilkan <strong><?= count($rooms); ?></strong> dari <strong><?= $totalRooms; ?></strong> kamar kos
            </span>
        </div>

        <?php if ($isFiltered): ?>
            <!-- Filter Chips / Badges yang Sedang Aktif -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-secondary small fw-semibold">Filter Aktif:</span>
                
                <?php if (!empty($filters['q'])): ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill small">
                        Pencarian: "<?= htmlspecialchars($filters['q']); ?>"
                    </span>
                <?php endif; ?>

                <?php if ($filters['status'] !== 'semua' && !empty($filters['status'])): ?>
                    <span class="badge bg-info bg-opacity-10 text-info-emphasis border border-info border-opacity-25 px-2 py-1 rounded-pill small">
                        Status: <?= ucfirst(htmlspecialchars($filters['status'])); ?>
                    </span>
                <?php endif; ?>

                <?php if ($filters['min_harga'] !== null): ?>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill small">
                        Min: <?= formatRupiah($filters['min_harga']); ?>
                    </span>
                <?php endif; ?>

                <?php if ($filters['max_harga'] !== null): ?>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill small">
                        Maks: <?= formatRupiah($filters['max_harga']); ?>
                    </span>
                <?php endif; ?>

                <a href="<?= url('kamar'); ?>" class="text-danger small text-decoration-none fw-semibold ms-1">
                    <i class="bi bi-x-circle me-1"></i>Hapus Semua Filter
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Grid Kartu Kamar (Bootstrap Card Component) -->
    <div class="row g-4">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $kamar): ?>
                <?php require __DIR__ . '/../components/room_card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State Jika Tidak Ditemukan -->
            <div class="col-12 py-5 text-center">
                <div class="card border-0 shadow-sm rounded-4 p-5 bg-white max-w-700 mx-auto">
                    <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-light text-secondary mb-3 mx-auto" style="width: 80px; height: 80px;">
                        <i class="bi bi-search fs-1 text-primary"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Tidak Ada Kamar yang Sesuai</h4>
                    <p class="text-secondary small mb-4">
                        Maaf, tidak ditemukan kamar kos yang memenuhi kriteria pencarian nomor kamar, fasilitas, status, atau rentang harga yang Anda pilih.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?= url('kamar'); ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                            <i class="bi bi-arrow-repeat me-1"></i> Tampilkan Semua Kamar
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
