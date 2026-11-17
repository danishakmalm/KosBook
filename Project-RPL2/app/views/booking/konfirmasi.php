<?php
/**
 * Halaman Konfirmasi Booking Kamar Kos
 * Ditampilkan setelah user berhasil melakukan booking
 * Status booking: "menunggu"
 */

$photoUrl = !empty($booking['kamar_foto']) && file_exists(__DIR__ . '/../../public/images/kamar/' . $booking['kamar_foto'])
    ? BASE_URL . '/images/kamar/' . htmlspecialchars($booking['kamar_foto'])
    : (file_exists(__DIR__ . '/../../public/images/' . $booking['kamar_foto'])
        ? BASE_URL . '/images/' . htmlspecialchars($booking['kamar_foto'])
        : BASE_URL . '/images/default_kamar.jpg');
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('dashboard/user'); ?>" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Konfirmasi Booking</li>
        </ol>
    </nav>

    <!-- Reusable Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header Konfirmasi Sukses -->
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white mb-4 position-relative overflow-hidden">
                <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-success bg-opacity-10 text-success mb-3 mx-auto" style="width: 75px; height: 75px;">
                    <i class="bi bi-check-circle-fill fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Pengajuan Booking Berhasil!</h3>
                <p class="text-secondary small mb-3">
                    Pesanan sewa kamar kos Anda telah berhasil tersimpan di sistem dengan status <strong>Menunggu Konfirmasi</strong>.
                </p>
                <div class="d-inline-flex justify-content-center">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill small fw-semibold shadow-sm">
                        <i class="bi bi-hourglass-split me-1"></i> Status Booking: Menunggu Konfirmasi
                    </span>
                </div>
            </div>

            <!-- Kartu Invoice Rincian Booking -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-light border-bottom p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="text-secondary small text-uppercase fw-semibold d-block">Nomor Registrasi Booking</span>
                        <h4 class="fw-bold text-primary mb-0">#BK-<?= str_pad((string)$booking['id'], 5, '0', STR_PAD_LEFT); ?></h4>
                    </div>
                    <div class="text-sm-end">
                        <span class="text-secondary small d-block">Waktu Pengajuan:</span>
                        <span class="small fw-semibold text-dark">
                            <?= date('d M Y, H:i', strtotime($booking['tanggal_booking'])); ?> WIB
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Rincian Kamar -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-4">
                        <img src="<?= $photoUrl; ?>" 
                             alt="<?= htmlspecialchars($booking['nomor_kamar']); ?>" 
                             class="rounded-3 object-fit-cover" 
                             style="width: 80px; height: 80px;"
                             onerror="this.src='<?= BASE_URL; ?>/images/default_kamar.jpg'">
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary border small mb-1">ID Kamar #00<?= htmlspecialchars($booking['kamar_id']); ?></span>
                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($booking['nomor_kamar']); ?></h5>
                            <span class="text-primary fw-semibold small"><?= formatRupiah($booking['harga_kamar']); ?> / bulan</span>
                        </div>
                    </div>

                    <!-- Tabel Detail Informasi Pemesanan -->
                    <div class="table-responsive mb-4">
                        <table class="table table-borderless align-middle mb-0 small">
                            <tbody>
                                <tr class="border-bottom">
                                    <td class="text-secondary ps-0 py-2" style="width: 40%;">Nama Calon Penghuni</td>
                                    <td class="fw-semibold text-dark text-end py-2"><?= htmlspecialchars($booking['user_nama']); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-secondary ps-0 py-2">Email Pemesan</td>
                                    <td class="fw-semibold text-dark text-end py-2"><?= htmlspecialchars($booking['user_email']); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-secondary ps-0 py-2">Tanggal Mulai Sewa</td>
                                    <td class="fw-semibold text-dark text-end py-2">
                                        <i class="bi bi-calendar-check text-success me-1"></i>
                                        <?= date('d F Y', strtotime($booking['tanggal_mulai'])); ?>
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-secondary ps-0 py-2">Lama Sewa</td>
                                    <td class="fw-semibold text-dark text-end py-2"><?= $booking['lama_sewa']; ?> Bulan</td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-secondary ps-0 py-2">Tarif Sewa Bulanan</td>
                                    <td class="fw-semibold text-dark text-end py-2"><?= formatRupiah($booking['harga_kamar']); ?> / bln</td>
                                </tr>
                                <tr class="bg-light-subtle">
                                    <td class="ps-0 py-3 fw-bold text-dark fs-6">Total Biaya Sewa</td>
                                    <td class="text-end py-3">
                                        <span class="fs-4 fw-bold text-primary"><?= formatRupiah($booking['total_harga']); ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Informasi Langkah Selanjutnya (Tanpa Pembayaran Online) -->
                    <div class="alert alert-info border-0 rounded-3 p-3 mb-0 small">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle-fill text-primary fs-5 flex-shrink-0 mt-0"></i>
                            <div>
                                <strong class="text-dark d-block mb-1">Langkah Selanjutnya:</strong>
                                <ul class="ps-3 mb-0 text-secondary">
                                    <li>Kamar ini telah dialihkan statusnya menjadi <strong>"Booking"</strong> sehingga tidak dapat dipesan oleh pengguna lain.</li>
                                    <li>Pengelola kos akan memverifikasi permohonan booking Anda dan memberikan konfirmasi penerimaan.</li>
                                    <li>Pembayaran diselesaikan secara langsung / manual saat penyerahan kunci dan perjanjian sewa (tanpa pembayaran online).</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Aksi -->
                <div class="card-footer bg-white border-top p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <a href="<?= url('dashboard/user'); ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-journal-bookmark-fill"></i> Lihat di Dashboard Saya
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" onclick="window.print();">
                            <i class="bi bi-printer me-1"></i> Cetak Bukti
                        </button>
                        <a href="<?= url('kamar'); ?>" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold">
                            <i class="bi bi-grid me-1"></i> Katalog Kamar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
