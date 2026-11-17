<?php
/**
 * Halaman Detail Booking Kamar Kos
 * Menampilkan 9 data wajib:
 * - ID Booking, Nama User, Email, Nomor Kamar, Tanggal Booking, Tanggal Mulai, Lama Sewa, Total Harga, Status
 * Aksi Admin:
 * - Konfirmasi Booking (menunggu -> dikonfirmasi, kamar: booking -> terisi)
 * - Tolak Booking (menunggu -> ditolak, kamar: booking -> tersedia)
 * Dilengkapi Bootstrap Modal Confirmation Dialog.
 */

$photoUrl = !empty($booking['kamar_foto']) && file_exists(__DIR__ . '/../../public/images/kamar/' . $booking['kamar_foto'])
    ? BASE_URL . '/images/kamar/' . htmlspecialchars($booking['kamar_foto'])
    : (file_exists(__DIR__ . '/../../public/images/' . $booking['kamar_foto'])
        ? BASE_URL . '/images/' . htmlspecialchars($booking['kamar_foto'])
        : BASE_URL . '/images/default_kamar.jpg');

$statusBadge = [
    'menunggu'     => 'bg-warning text-dark',
    'dikonfirmasi' => 'bg-success text-white',
    'ditolak'      => 'bg-danger text-white',
    'selesai'      => 'bg-secondary text-white'
][$booking['status']] ?? 'bg-primary text-white';

$statusIcon = [
    'menunggu'     => 'bi-hourglass-split',
    'dikonfirmasi' => 'bi-check-circle-fill',
    'ditolak'      => 'bi-x-circle-fill',
    'selesai'      => 'bi-flag-fill'
][$booking['status']] ?? 'bi-circle-fill';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
            <?php if ($isAdmin): ?>
                <li class="breadcrumb-item"><a href="<?= url('booking/admin'); ?>" class="text-decoration-none">Kelola Booking</a></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?= url('dashboard/user'); ?>" class="text-decoration-none">Dashboard Saya</a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page">Detail Booking #BK-00<?= $booking['id']; ?></li>
        </ol>
    </nav>

    <!-- Reusable Alert Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Header Kartu Detail -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-primary text-white p-4 d-flex flex-wrap justify-content-between align-items-center gap-2" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;">
                    <div>
                        <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 small mb-2">
                            Informasi Detail Reservasi
                        </span>
                        <h3 class="fw-bold mb-0">Booking #BK-00<?= htmlspecialchars((string)$booking['id']); ?></h3>
                    </div>
                    <div class="text-sm-end">
                        <span class="text-white-50 small d-block">Status Saat Ini:</span>
                        <span class="badge <?= $statusBadge; ?> px-3 py-2 rounded-pill small fw-semibold shadow-sm d-inline-flex align-items-center gap-1">
                            <i class="bi <?= $statusIcon; ?> small"></i> <?= ucfirst($booking['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Kartu Ringkasan Kamar -->
                    <div class="card border-0 bg-light rounded-4 p-3 mb-4">
                        <div class="row align-items-center g-3">
                            <div class="col-sm-4 col-md-3">
                                <img src="<?= $photoUrl; ?>" 
                                     alt="<?= htmlspecialchars($booking['nomor_kamar']); ?>" 
                                     class="w-100 rounded-3 object-fit-cover shadow-sm" 
                                     style="height: 120px;"
                                     onerror="this.src='<?= BASE_URL; ?>/images/default_kamar.jpg'">
                            </div>
                            <div class="col-sm-8 col-md-9">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 small mb-1">
                                    ID Kamar: #00<?= htmlspecialchars((string)$booking['kamar_id']); ?>
                                </span>
                                <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($booking['nomor_kamar']); ?></h4>
                                <span class="text-primary fw-bold fs-5"><?= formatRupiah($booking['harga_kamar']); ?></span>
                                <small class="text-muted">/ bulan</small>
                                <div class="mt-2">
                                    <a href="<?= url('kamar/detail/' . $booking['kamar_id']); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3" target="_blank">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Kamar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 9 Informasi Wajib Booking -->
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-list-check text-primary me-2"></i>Rincian Lengkap Pemesanan
                    </h5>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0 small">
                            <tbody>
                                <tr>
                                    <th class="bg-light text-secondary" style="width: 35%;">1. ID Booking</th>
                                    <td class="fw-bold text-primary">#BK-00<?= htmlspecialchars((string)$booking['id']); ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-secondary">2. Nama User (Pemesan)</th>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($booking['user_nama']); ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-secondary">3. Email Pemesan</th>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($booking['user_email']); ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($booking['user_email']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-secondary">4. Nomor Kamar</th>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <?= htmlspecialchars($booking['nomor_kamar']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-secondary">5. Tanggal Booking</th>
                                    <td><?= date('d F Y, H:i', strtotime($booking['tanggal_booking'])); ?> WIB</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-secondary">6. Tanggal Mulai Sewa</th>
                                    <td class="fw-semibold text-success">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= date('d F Y', strtotime($booking['tanggal_mulai'])); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-secondary">7. Lama Sewa</th>
                                    <td class="fw-semibold"><?= (int)$booking['lama_sewa']; ?> Bulan</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-secondary">8. Total Harga</th>
                                    <td>
                                        <span class="fs-5 fw-bold text-primary"><?= formatRupiah($booking['total_harga']); ?></span>
                                        <small class="text-muted d-block">(<?= formatRupiah($booking['harga_kamar']); ?> &times; <?= $booking['lama_sewa']; ?> bulan)</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-secondary">9. Status Booking</th>
                                    <td>
                                        <span class="badge <?= $statusBadge; ?> rounded-pill px-3 py-1 small fw-semibold">
                                            <i class="bi <?= $statusIcon; ?> small me-1"></i> <?= ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Panel Aksi Khusus Admin -->
                    <?php if ($isAdmin): ?>
                        <div class="card border border-2 border-primary border-opacity-25 rounded-4 p-4 bg-light mb-4">
                            <h5 class="fw-bold text-dark mb-2">
                                <i class="bi bi-shield-check text-primary me-2"></i>Tindakan Administrator
                            </h5>
                            
                            <?php if ($booking['status'] === 'menunggu'): ?>
                                <p class="text-secondary small mb-3">
                                    Permintaan booking ini sedang berstatus <strong>Menunggu</strong>. Anda dapat mengkonfirmasi untuk menerima penyewa atau menolak permohonan booking.
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <!-- Tombol Konfirmasi dengan Confirmation Dialog Modal -->
                                    <button type="button" 
                                            class="btn btn-success rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetailConfirm">
                                        <i class="bi bi-check-circle-fill"></i> Konfirmasi Booking
                                    </button>

                                    <!-- Tombol Tolak dengan Confirmation Dialog Modal -->
                                    <button type="button" 
                                            class="btn btn-outline-danger rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetailReject">
                                        <i class="bi bi-x-circle-fill"></i> Tolak Booking
                                    </button>
                                </div>

                                <!-- MODAL 1: KONFIRMASI BOOKING DETAIL -->
                                <div class="modal fade" id="modalDetailConfirm" tabindex="-1" aria-labelledby="modalDetailConfirmLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-success bg-opacity-10 border-0">
                                                <h5 class="modal-title fw-bold text-success" id="modalDetailConfirmLabel">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi Booking Kamar
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 text-start">
                                                <p class="text-dark mb-3">
                                                    Apakah Anda yakin ingin menyetujui dan <strong>mengkonfirmasi</strong> pesanan booking <strong>#BK-00<?= $booking['id']; ?></strong> untuk <strong><?= htmlspecialchars($booking['user_nama']); ?></strong>?
                                                </p>
                                                <div class="alert alert-info border-0 rounded-3 small mb-0">
                                                    <i class="bi bi-info-circle-fill me-1"></i>
                                                    <strong>Sistem akan memperbarui:</strong>
                                                    <ul class="mb-0 ps-3 mt-1">
                                                        <li>Status booking: <strong>menunggu &rarr; dikonfirmasi</strong></li>
                                                        <li>Status kamar: <strong>booking &rarr; terisi</strong></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 p-3 pt-0">
                                                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                <a href="<?= url('booking/konfirmasi_admin/' . $booking['id']); ?>" class="btn btn-success rounded-pill px-4 fw-semibold">
                                                    <i class="bi bi-check-lg me-1"></i> Ya, Konfirmasi Sekarang
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODAL 2: TOLAK BOOKING DETAIL -->
                                <div class="modal fade" id="modalDetailReject" tabindex="-1" aria-labelledby="modalDetailRejectLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-danger bg-opacity-10 border-0">
                                                <h5 class="modal-title fw-bold text-danger" id="modalDetailRejectLabel">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Tolak Booking Kamar
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 text-start">
                                                <p class="text-dark mb-3">
                                                    Apakah Anda yakin ingin <strong>menolak</strong> pesanan booking <strong>#BK-00<?= $booking['id']; ?></strong> untuk <strong><?= htmlspecialchars($booking['user_nama']); ?></strong>?
                                                </p>
                                                <div class="alert alert-warning border-0 rounded-3 small mb-0">
                                                    <i class="bi bi-arrow-repeat me-1"></i>
                                                    <strong>Sistem akan memperbarui:</strong>
                                                    <ul class="mb-0 ps-3 mt-1">
                                                        <li>Status booking: <strong>menunggu &rarr; ditolak</strong></li>
                                                        <li>Status kamar: <strong>booking &rarr; tersedia</strong> (siap dipesan kembali)</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 p-3 pt-0">
                                                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                <a href="<?= url('booking/tolak_admin/' . $booking['id']); ?>" class="btn btn-danger rounded-pill px-4 fw-semibold">
                                                    <i class="bi bi-x-lg me-1"></i> Ya, Tolak Sekarang
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php else: ?>
                                <!-- Jika status sudah dikonfirmasi atau ditolak -->
                                <div class="alert alert-secondary border-0 rounded-3 small mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-lock-fill fs-5 text-secondary"></i>
                                    <div>
                                        <strong>Status Selesai:</strong> Pesanan booking ini telah berstatus <strong>"<?= ucfirst($booking['status']); ?>"</strong> dan tidak dapat dikonfirmasi atau ditolak ulang.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Informasi Catatan Pembayaran (Tanpa Pembayaran Online) -->
                    <div class="alert alert-info border-0 rounded-3 p-3 small mb-0">
                        <i class="bi bi-cash-coin me-1"></i> <strong>Prosedur Pembayaran:</strong> Sesuai kebijakan KosBook, transaksi pembayaran dilakukan secara langsung atau transfer manual langsung ke rekening pemilik kos saat check-in dan tanda tangan perjanjian sewa (tanpa pembayaran online).
                    </div>
                </div>

                <!-- Footer Navigasi -->
                <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                    <?php if ($isAdmin): ?>
                        <a href="<?= url('booking/admin'); ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Kelola Booking
                        </a>
                    <?php else: ?>
                        <a href="<?= url('dashboard/user'); ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                        </a>
                    <?php endif; ?>

                    <button type="button" class="btn btn-outline-primary rounded-pill px-3 fw-semibold" onclick="window.print();">
                        <i class="bi bi-printer me-1"></i> Cetak Detail
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
