<?php
/**
 * Halaman Manajemen Data Booking untuk Admin
 * Menampilkan 9 kolom wajib:
 * - ID Booking, Nama User, Email, Nomor Kamar, Tanggal Booking, Tanggal Mulai, Lama Sewa, Total Harga, Status
 * Aksi:
 * 1. Melihat detail booking
 * 2. Mengkonfirmasi booking (menunggu -> dikonfirmasi, kamar: booking -> terisi)
 * 3. Menolak booking (menunggu -> ditolak, kamar: booking -> tersedia)
 * Dilengkapi dialog konfirmasi Bootstrap modal sebelum tindakan dieksekusi.
 */
?>
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('dashboard/admin'); ?>" class="text-decoration-none">Dashboard Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Kelola Booking</li>
        </ol>
    </nav>

    <!-- Reusable Alert Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <!-- Header & Info -->
    <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-7">
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Manajemen Pemesanan Kamar
            </h2>
            <p class="text-secondary small mb-0">
                Kelola dan verifikasi permintaan reservasi kamar kos dari calon penghuni secara real-time.
            </p>
        </div>
        <div class="col-lg-5 text-lg-end">
            <a href="<?= url('dashboard/admin'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard Admin
            </a>
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill small fw-semibold">
                <i class="bi bi-shield-lock-fill me-1"></i> Panel Administrator
            </span>
        </div>
    </div>

    <!-- Ringkasan Status Booking (KPI Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-secondary small">Total Pesanan</span>
                    <i class="bi bi-journal-text fs-4 text-primary"></i>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?= $stats['total']; ?></h3>
                <small class="text-muted">Semua status</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-secondary small">Menunggu Respon</span>
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <h3 class="fw-bold text-warning mb-0"><?= $stats['menunggu']; ?></h3>
                <small class="text-muted">Perlu konfirmasi</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-secondary small">Dikonfirmasi</span>
                    <i class="bi bi-check-circle fs-4 text-success"></i>
                </div>
                <h3 class="fw-bold text-success mb-0"><?= $stats['dikonfirmasi']; ?></h3>
                <small class="text-muted">Kamar terisi</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-secondary small">Ditolak</span>
                    <i class="bi bi-x-circle fs-4 text-danger"></i>
                </div>
                <h3 class="fw-bold text-danger mb-0"><?= $stats['ditolak']; ?></h3>
                <small class="text-muted">Kamar tersedia kembali</small>
            </div>
        </div>
    </div>

    <!-- Filter Tabs Status -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= url('booking/admin'); ?>" 
                   class="btn btn-sm rounded-pill px-3 <?= ($filterStatus === 'semua') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    Semua (<?= $stats['total']; ?>)
                </a>
                <a href="<?= url('booking/admin') . '&status=menunggu'; ?>" 
                   class="btn btn-sm rounded-pill px-3 <?= ($filterStatus === 'menunggu') ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'; ?>">
                    <i class="bi bi-hourglass-split me-1"></i> Menunggu (<?= $stats['menunggu']; ?>)
                </a>
                <a href="<?= url('booking/admin') . '&status=dikonfirmasi'; ?>" 
                   class="btn btn-sm rounded-pill px-3 <?= ($filterStatus === 'dikonfirmasi') ? 'btn-success' : 'btn-outline-success'; ?>">
                    <i class="bi bi-check-circle me-1"></i> Dikonfirmasi (<?= $stats['dikonfirmasi']; ?>)
                </a>
                <a href="<?= url('booking/admin') . '&status=ditolak'; ?>" 
                   class="btn btn-sm rounded-pill px-3 <?= ($filterStatus === 'ditolak') ? 'btn-danger' : 'btn-outline-danger'; ?>">
                    <i class="bi bi-x-circle me-1"></i> Ditolak (<?= $stats['ditolak']; ?>)
                </a>
            </div>
            <span class="text-secondary small">
                Menampilkan <strong><?= count($bookings); ?></strong> dari <strong><?= $stats['total']; ?></strong> booking
            </span>
        </div>
    </div>

    <!-- Tabel Data Booking (9 Kolom Lengkap + Aksi) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">ID Booking</th>
                        <th>Nama User</th>
                        <th>Email</th>
                        <th>Nomor Kamar</th>
                        <th>Tanggal Booking</th>
                        <th>Tanggal Mulai</th>
                        <th>Lama Sewa</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $b): 
                            $bStatus = $b['status'];
                            $badgeClass = [
                                'menunggu'     => 'bg-warning text-dark',
                                'dikonfirmasi' => 'bg-success text-white',
                                'ditolak'      => 'bg-danger text-white',
                                'selesai'      => 'bg-secondary text-white'
                            ][$bStatus] ?? 'bg-primary text-white';

                            $statusIcon = [
                                'menunggu'     => 'bi-hourglass-split',
                                'dikonfirmasi' => 'bi-check-circle-fill',
                                'ditolak'      => 'bi-x-circle-fill',
                                'selesai'      => 'bi-flag-fill'
                            ][$bStatus] ?? 'bi-circle-fill';
                        ?>
                            <tr>
                                <!-- 1. ID Booking -->
                                <td class="ps-4 fw-bold text-primary">
                                    #BK-00<?= htmlspecialchars((string)$b['id']); ?>
                                </td>

                                <!-- 2. Nama User -->
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($b['user_nama']); ?></strong>
                                </td>

                                <!-- 3. Email -->
                                <td>
                                    <span class="text-secondary small"><?= htmlspecialchars($b['user_email']); ?></span>
                                </td>

                                <!-- 4. Nomor Kamar -->
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <?= htmlspecialchars($b['nomor_kamar']); ?>
                                    </span>
                                </td>

                                <!-- 5. Tanggal Booking -->
                                <td>
                                    <span class="small text-secondary">
                                        <?= date('d M Y, H:i', strtotime($b['tanggal_booking'])); ?>
                                    </span>
                                </td>

                                <!-- 6. Tanggal Mulai -->
                                <td>
                                    <span class="small fw-semibold text-dark">
                                        <?= date('d M Y', strtotime($b['tanggal_mulai'])); ?>
                                    </span>
                                </td>

                                <!-- 7. Lama Sewa -->
                                <td>
                                    <span class="small"><?= (int)$b['lama_sewa']; ?> Bulan</span>
                                </td>

                                <!-- 8. Total Harga -->
                                <td>
                                    <strong class="text-primary"><?= formatRupiah($b['total_harga']); ?></strong>
                                </td>

                                <!-- 9. Status -->
                                <td>
                                    <span class="badge <?= $badgeClass; ?> rounded-pill px-3 py-1 small d-inline-flex align-items-center gap-1">
                                        <i class="bi <?= $statusIcon; ?> small"></i> <?= ucfirst($bStatus); ?>
                                    </span>
                                </td>

                                <!-- Aksi Admin -->
                                <td class="text-end pe-4 text-nowrap">
                                    <!-- 1. Tombol Lihat Detail -->
                                    <a href="<?= url('booking/detail/' . $b['id']); ?>" 
                                       class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 me-1" 
                                       title="Lihat Detail Booking">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>

                                    <?php if ($bStatus === 'menunggu'): ?>
                                        <!-- 2. Tombol Konfirmasi (Memicu Confirmation Dialog Modal) -->
                                        <button type="button" 
                                                class="btn btn-success btn-sm rounded-pill px-3 py-1 me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalConfirmBooking<?= $b['id']; ?>">
                                            <i class="bi bi-check-lg me-1"></i> Konfirmasi
                                        </button>

                                        <!-- 3. Tombol Tolak (Memicu Confirmation Dialog Modal) -->
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalRejectBooking<?= $b['id']; ?>">
                                            <i class="bi bi-x-lg me-1"></i> Tolak
                                        </button>

                                        <!-- MODAL CONFIRMATION DIALOG: KONFIRMASI BOOKING -->
                                        <div class="modal fade" id="modalConfirmBooking<?= $b['id']; ?>" tabindex="-1" aria-labelledby="modalConfirmLabel<?= $b['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content border-0 shadow rounded-4">
                                                    <div class="modal-header bg-success bg-opacity-10 border-0">
                                                        <h5 class="modal-title fw-bold text-success d-flex align-items-center gap-2" id="modalConfirmLabel<?= $b['id']; ?>">
                                                            <i class="bi bi-check-circle-fill"></i> Konfirmasi Pesanan Booking
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="text-dark mb-3">
                                                            Apakah Anda yakin ingin <strong>mengkonfirmasi</strong> pesanan berikut?
                                                        </p>
                                                        <div class="p-3 bg-light rounded-3 small mb-3">
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span class="text-secondary">No. Booking:</span>
                                                                <strong>#BK-00<?= $b['id']; ?></strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span class="text-secondary">Nama Pemesan:</span>
                                                                <strong><?= htmlspecialchars($b['user_nama']); ?></strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span class="text-secondary">Kamar:</span>
                                                                <strong><?= htmlspecialchars($b['nomor_kamar']); ?></strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span class="text-secondary">Total Biaya:</span>
                                                                <strong class="text-primary"><?= formatRupiah($b['total_harga']); ?></strong>
                                                            </div>
                                                        </div>
                                                        <div class="alert alert-info border-0 rounded-3 small mb-0">
                                                            <i class="bi bi-info-circle-fill me-1"></i>
                                                            <strong>Konsekuensi Perubahan Status:</strong>
                                                            <ul class="mb-0 ps-3 mt-1">
                                                                <li>Status booking berubah: <strong>Menunggu &rarr; Dikonfirmasi</strong></li>
                                                                <li>Status kamar berubah: <strong>Booking &rarr; Terisi</strong></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-3 pt-0">
                                                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                        <a href="<?= url('booking/konfirmasi_admin/' . $b['id']); ?>" class="btn btn-success rounded-pill px-4 fw-semibold">
                                                            <i class="bi bi-check-lg me-1"></i> Ya, Konfirmasi Booking
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- MODAL CONFIRMATION DIALOG: TOLAK BOOKING -->
                                        <div class="modal fade" id="modalRejectBooking<?= $b['id']; ?>" tabindex="-1" aria-labelledby="modalRejectLabel<?= $b['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content border-0 shadow rounded-4">
                                                    <div class="modal-header bg-danger bg-opacity-10 border-0">
                                                        <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2" id="modalRejectLabel<?= $b['id']; ?>">
                                                            <i class="bi bi-exclamation-triangle-fill"></i> Tolak Pesanan Booking
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="text-dark mb-3">
                                                            Apakah Anda yakin ingin <strong>menolak</strong> pesanan booking kamar ini?
                                                        </p>
                                                        <div class="p-3 bg-light rounded-3 small mb-3">
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span class="text-secondary">No. Booking:</span>
                                                                <strong>#BK-00<?= $b['id']; ?></strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span class="text-secondary">Nama Pemesan:</span>
                                                                <strong><?= htmlspecialchars($b['user_nama']); ?></strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span class="text-secondary">Kamar:</span>
                                                                <strong><?= htmlspecialchars($b['nomor_kamar']); ?></strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span class="text-secondary">Total Biaya:</span>
                                                                <strong class="text-primary"><?= formatRupiah($b['total_harga']); ?></strong>
                                                            </div>
                                                        </div>
                                                        <div class="alert alert-warning border-0 rounded-3 small mb-0">
                                                            <i class="bi bi-arrow-repeat me-1"></i>
                                                            <strong>Konsekuensi Perubahan Status:</strong>
                                                            <ul class="mb-0 ps-3 mt-1">
                                                                <li>Status booking berubah: <strong>Menunggu &rarr; Ditolak</strong></li>
                                                                <li>Status kamar berubah: <strong>Booking &rarr; Tersedia</strong> (siap dipesan kembali)</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-3 pt-0">
                                                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                        <a href="<?= url('booking/tolak_admin/' . $b['id']); ?>" class="btn btn-danger rounded-pill px-4 fw-semibold">
                                                            <i class="bi bi-x-lg me-1"></i> Ya, Tolak Booking
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php else: ?>
                                        <!-- Jika sudah dikonfirmasi atau ditolak, tombol tidak aktif / status tuntas -->
                                        <span class="badge bg-light text-muted border small px-2 py-1">
                                            Selesai Diproses
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-secondary">
                                <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-light mb-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-inbox fs-3 text-muted"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Belum Ada Data Booking</h6>
                                <p class="small text-muted mb-0">Tidak ada data pemesanan kamar dengan filter yang Anda pilih.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
