<div class="container py-4">
    <!-- Breadcrumb Navigasi -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard User</li>
        </ol>
    </nav>

    <!-- Notifikasi Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <!-- ========================================================================= -->
    <!-- 1. INFORMASI USER                                                         -->
    <!-- ========================================================================= -->
    <div class="card border-0 shadow-sm rounded-4 text-white p-4 p-md-5 mb-4 position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-white text-primary fw-bold shadow-sm" 
                         style="width: 58px; height: 58px; font-size: 1.5rem;">
                        <?= strtoupper(substr($user['nama'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div>
                        <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1 small fw-semibold">
                            <i class="bi bi-person-badge-fill me-1"></i> Akun Penghuni
                        </span>
                        <h2 class="fw-bold mb-0 text-white">Selamat Datang, <?= htmlspecialchars($user['nama'] ?? ''); ?>!</h2>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 text-white-50 small">
                    <div>
                        <i class="bi bi-envelope-fill me-1 text-warning"></i>
                        <span><?= htmlspecialchars($user['email'] ?? ''); ?></span>
                    </div>
                    <div>•</div>
                    <div>
                        <i class="bi bi-person-check me-1 text-white"></i>
                        <span>ID User: #<?= (int)($user['id'] ?? 0); ?></span>
                    </div>
                    <?php if (!empty($user['created_at'])): ?>
                        <div>•</div>
                        <div>
                            <i class="bi bi-calendar-check me-1 text-white"></i>
                            <span>Terdaftar: <?= date('d M Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= url('kamar'); ?>" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-search me-1"></i> Cari Kamar Kos
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STATISTIK SEDERHANA (4 KARTU METRIK)                                      -->
    <!-- Total Booking | Booking Menunggu | Booking Dikonfirmasi | Booking Ditolak -->
    <!-- ========================================================================= -->
    <div class="row g-3 mb-4">
        <!-- Total Booking -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-primary bg-opacity-10 text-primary" 
                         style="width: 48px; height: 48px;">
                        <i class="bi bi-journal-bookmark-fill fs-4"></i>
                    </div>
                    <div>
                        <span class="text-secondary small d-block">Total Booking</span>
                        <h3 class="fw-bold text-dark mb-0"><?= (int)($stats['total'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Menunggu -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-warning border-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-warning bg-opacity-20 text-warning" 
                         style="width: 48px; height: 48px;">
                        <i class="bi bi-hourglass-split fs-4 text-dark"></i>
                    </div>
                    <div>
                        <span class="text-secondary small d-block">Booking Menunggu</span>
                        <h3 class="fw-bold text-warning mb-0"><?= (int)($stats['menunggu'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Dikonfirmasi -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-success border-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-success bg-opacity-10 text-success" 
                         style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <span class="text-secondary small d-block">Booking Dikonfirmasi</span>
                        <h3 class="fw-bold text-success mb-0"><?= (int)($stats['dikonfirmasi'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Ditolak -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-danger border-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-danger bg-opacity-10 text-danger" 
                         style="width: 48px; height: 48px;">
                        <i class="bi bi-x-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <span class="text-secondary small d-block">Booking Ditolak</span>
                        <h3 class="fw-bold text-danger mb-0"><?= (int)($stats['ditolak'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. BOOKING AKTIF                                                          -->
    <!-- Menampilkan sewa kamar yang telah dikonfirmasi oleh Admin                 -->
    <!-- ========================================================================= -->
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-house-check-fill text-success me-2"></i>Booking Aktif
            </h5>
            <?php if (!empty($activeBookings)): ?>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small fw-semibold">
                    <?= count($activeBookings); ?> Kamar Aktif Disewa
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($activeBookings)): ?>
            <div class="row g-3">
                <?php foreach ($activeBookings as $active): 
                    $photoSrc = !empty($active['kamar_foto']) && file_exists(__DIR__ . '/../../public/images/kamar/' . $active['kamar_foto'])
                        ? BASE_URL . '/images/kamar/' . htmlspecialchars($active['kamar_foto'])
                        : (file_exists(__DIR__ . '/../../public/images/' . ($active['kamar_foto'] ?? ''))
                            ? BASE_URL . '/images/' . htmlspecialchars($active['kamar_foto'])
                            : BASE_URL . '/images/default_kamar.jpg');
                ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                            <div class="row g-0 align-items-center">
                                <div class="col-md-3 bg-light" style="min-height: 180px;">
                                    <img src="<?= $photoSrc; ?>" 
                                         alt="Kamar <?= htmlspecialchars($active['nomor_kamar']); ?>" 
                                         class="w-100 h-100 object-fit-cover" 
                                         style="max-height: 220px;"
                                         onerror="this.src='<?= BASE_URL; ?>/images/default_kamar.jpg'">
                                </div>
                                <div class="col-md-9 p-4">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <span class="badge bg-success text-white rounded-pill px-3 py-1 small fw-semibold mb-1">
                                                <i class="bi bi-check-circle-fill me-1"></i> Dikonfirmasi
                                            </span>
                                            <h4 class="fw-bold text-dark mb-1">Kamar <?= htmlspecialchars($active['nomor_kamar']); ?></h4>
                                            <span class="text-muted small">No. Pesanan: #BK-00<?= $active['id']; ?></span>
                                        </div>
                                        <div class="text-md-end">
                                            <span class="text-secondary small d-block">Total Biaya Sewa</span>
                                            <h4 class="fw-bold text-primary mb-0"><?= formatRupiah($active['total_harga']); ?></h4>
                                        </div>
                                    </div>

                                    <div class="row g-2 py-3 border-top border-bottom my-2 bg-light bg-opacity-50 rounded-3 px-2">
                                        <div class="col-sm-4">
                                            <small class="text-muted d-block">Mulai Sewa:</small>
                                            <strong class="text-dark"><i class="bi bi-calendar-event me-1 text-primary"></i><?= date('d M Y', strtotime($active['tanggal_mulai'])); ?></strong>
                                        </div>
                                        <div class="col-sm-4">
                                            <small class="text-muted d-block">Lama Sewa:</small>
                                            <strong class="text-dark"><i class="bi bi-clock-history me-1 text-primary"></i><?= (int)$active['lama_sewa']; ?> Bulan</strong>
                                        </div>
                                        <div class="col-sm-4">
                                            <small class="text-muted d-block">Tanggal Booking:</small>
                                            <strong class="text-dark"><i class="bi bi-calendar-check me-1 text-primary"></i><?= date('d M Y', strtotime($active['tanggal_booking'])); ?></strong>
                                        </div>
                                    </div>

                                    <?php if (!empty($active['kamar_fasilitas'])): ?>
                                        <div class="mb-3 mt-2">
                                            <small class="text-muted me-2">Fasilitas:</small>
                                            <?php 
                                            $fas = is_array($active['kamar_fasilitas']) 
                                                ? $active['kamar_fasilitas'] 
                                                : array_map('trim', explode(',', $active['kamar_fasilitas']));
                                            foreach (array_slice($fas, 0, 4) as $item): 
                                                if (empty($item)) continue;
                                            ?>
                                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small me-1">
                                                    <i class="bi bi-check2 text-success me-1"></i><?= htmlspecialchars($item); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                                        <div class="text-success small d-flex align-items-center">
                                            <i class="bi bi-info-circle-fill me-2"></i>
                                            <span>Kamar kos ini aktif dihuni. Hubungi pemilik kos jika membutuhkan bantuan check-in/kunci.</span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="<?= url('booking/konfirmasi/' . $active['id']); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                <i class="bi bi-receipt me-1"></i> Bukti Booking
                                            </a>
                                            <a href="<?= url('kamar/detail/' . $active['kamar_id']); ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                                <i class="bi bi-door-open me-1"></i> Detail Kamar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="py-3">
                    <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-light text-muted mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-house-dash fs-2"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Belum Ada Booking Aktif</h6>
                    <p class="text-muted small mb-3">Anda belum memiliki kamar kos yang berstatus aktif atau dikonfirmasi oleh pengelola.</p>
                    <a href="<?= url('kamar'); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                        <i class="bi bi-search me-1"></i> Jelajahi Kamar Tersedia
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. BOOKING YANG SEDANG MENUNGGU                                           -->
    <!-- Pesanan dengan status 'menunggu' yang menantikan persetujuan Admin        -->
    <!-- ========================================================================= -->
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-hourglass-split text-warning me-2"></i>Booking yang Sedang Menunggu
            </h5>
            <?php if (!empty($pendingBookings)): ?>
                <span class="badge bg-warning text-dark rounded-pill px-3 py-1 small fw-semibold">
                    <?= count($pendingBookings); ?> Menunggu Konfirmasi Admin
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($pendingBookings)): ?>
            <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-3 d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-circle-fill fs-3 text-warning"></i>
                <div class="small">
                    <strong>Pemberitahuan:</strong> Pengajuan booking Anda sedang ditinjau oleh Admin Kos. Kamar telah diamankan sementara untuk Anda selama masa peninjauan.
                </div>
            </div>

            <div class="row g-3">
                <?php foreach ($pendingBookings as $pending): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 border-top border-warning border-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1 small fw-semibold mb-2">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">Kamar <?= htmlspecialchars($pending['nomor_kamar']); ?></h5>
                                    <small class="text-muted">ID Booking: #BK-00<?= $pending['id']; ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="text-secondary small d-block">Total Pembayaran</span>
                                    <h5 class="fw-bold text-primary mb-0"><?= formatRupiah($pending['total_harga']); ?></h5>
                                </div>
                            </div>

                            <ul class="list-group list-group-flush small mb-3">
                                <li class="list-group-item px-0 d-flex justify-content-between text-secondary">
                                    <span>Tanggal Booking:</span>
                                    <strong class="text-dark"><?= date('d M Y, H:i', strtotime($pending['tanggal_booking'])); ?></strong>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between text-secondary">
                                    <span>Mulai Sewa:</span>
                                    <strong class="text-dark"><?= date('d M Y', strtotime($pending['tanggal_mulai'])); ?></strong>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between text-secondary">
                                    <span>Lama Sewa:</span>
                                    <strong class="text-dark"><?= (int)$pending['lama_sewa']; ?> Bulan</strong>
                                </li>
                            </ul>

                            <div class="d-flex justify-content-end gap-2 mt-auto pt-2 border-top">
                                <a href="<?= url('booking/konfirmasi/' . $pending['id']); ?>" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3">
                                    <i class="bi bi-receipt me-1"></i> Lihat Rincian
                                </a>
                                <a href="<?= url('kamar/detail/' . $pending['kamar_id']); ?>" class="btn btn-light btn-sm rounded-pill px-3 border">
                                    <i class="bi bi-eye me-1"></i> Kamar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="py-2 text-muted small">
                    <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                    Tidak ada pesanan booking yang sedang menunggu konfirmasi admin.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. RIWAYAT BOOKING (TABEL LENGKAP BOOKING SAYA)                            -->
    <!-- Kolom: ID | Kamar | Tanggal Booking | Mulai Sewa | Lama Sewa | Total | Status -->
    <!-- Status: Menunggu = warning, Dikonfirmasi = success, Ditolak = danger, Selesai = secondary -->
    <!-- ========================================================================= -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Booking
                </h5>
                <span class="text-secondary small">Seluruh data riwayat reservasi kamar kos milik Anda</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                    Total: <?= count($bookings); ?> Riwayat
                </span>
                <a href="<?= url('kamar'); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Pesan Kamar Baru
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Kamar</th>
                        <th>Tanggal Booking</th>
                        <th>Mulai Sewa</th>
                        <th>Lama Sewa</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): foreach ($bookings as $b): 
                        // Bootstrap Badge Mapping sesuai spesifikasi:
                        // Menunggu = warning
                        // Dikonfirmasi = success
                        // Ditolak = danger
                        // Selesai = secondary
                        $statusBadgeClass = [
                            'menunggu'     => 'bg-warning text-dark',
                            'dikonfirmasi' => 'bg-success text-white',
                            'ditolak'      => 'bg-danger text-white',
                            'selesai'      => 'bg-secondary text-white'
                        ][$b['status']] ?? 'bg-secondary text-white';

                        $statusBadgeIcon = [
                            'menunggu'     => 'bi-hourglass-split',
                            'dikonfirmasi' => 'bi-check-circle-fill',
                            'ditolak'      => 'bi-x-circle-fill',
                            'selesai'      => 'bi-clock-history'
                        ][$b['status']] ?? 'bi-circle';
                    ?>
                        <tr>
                            <!-- 1. ID -->
                            <td class="ps-4 fw-semibold text-secondary">
                                #<?= (int)$b['id']; ?>
                            </td>

                            <!-- 2. Kamar -->
                            <td>
                                <strong class="text-dark d-block">Kamar <?= htmlspecialchars($b['nomor_kamar']); ?></strong>
                                <small class="text-muted">Kamar ID #<?= (int)$b['kamar_id']; ?></small>
                            </td>

                            <!-- 3. Tanggal Booking -->
                            <td>
                                <span class="text-dark">
                                    <?= date('d M Y', strtotime($b['tanggal_booking'])); ?>
                                </span>
                                <small class="text-muted d-block"><?= date('H:i', strtotime($b['tanggal_booking'])); ?> WIB</small>
                            </td>

                            <!-- 4. Mulai Sewa -->
                            <td>
                                <i class="bi bi-calendar-event me-1 text-secondary"></i>
                                <span class="fw-medium text-dark"><?= date('d M Y', strtotime($b['tanggal_mulai'])); ?></span>
                            </td>

                            <!-- 5. Lama Sewa -->
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <?= (int)$b['lama_sewa']; ?> Bulan
                                </span>
                            </td>

                            <!-- 6. Total -->
                            <td>
                                <span class="fw-bold text-primary"><?= formatRupiah($b['total_harga']); ?></span>
                            </td>

                            <!-- 7. Status (Badge Bootstrap) -->
                            <td>
                                <span class="badge <?= $statusBadgeClass; ?> rounded-pill px-3 py-2 small fw-semibold">
                                    <i class="bi <?= $statusBadgeIcon; ?> me-1"></i>
                                    <?= ucfirst($b['status']); ?>
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-pill border px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Pilihan
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                        <li>
                                            <a class="dropdown-item py-2" href="<?= url('booking/konfirmasi/' . $b['id']); ?>">
                                                <i class="bi bi-receipt text-primary me-2"></i>Rincian Reservasi
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="<?= url('kamar/detail/' . $b['kamar_id']); ?>">
                                                <i class="bi bi-door-open text-secondary me-2"></i>Detail Kamar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                    <h6 class="fw-bold text-dark">Belum Ada Riwayat Booking</h6>
                                    <p class="text-muted small mb-3">Anda belum pernah melakukan pemesanan kamar kos sebelumnya.</p>
                                    <a href="<?= url('kamar'); ?>" class="btn btn-primary btn-sm rounded-pill px-4">
                                        <i class="bi bi-search me-1"></i> Cari & Pesan Kamar Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Informasi Kontak Bantuan Pengelola -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <h6 class="fw-bold text-dark mb-1">
                    <i class="bi bi-headset text-primary me-2"></i>Pertanyaan Mengenai Booking Anda?
                </h6>
                <p class="text-secondary small mb-0">
                    Jika ada kendala pembayaran, konfirmasi pesanan, atau serah terima kunci kamar kos, tim pengelola siap membantu Anda setiap hari.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-success btn-sm rounded-pill px-4 py-2 fw-medium">
                    <i class="bi bi-whatsapp me-1"></i> Hubungi Pengelola
                </a>
            </div>
        </div>
    </div>
</div>
