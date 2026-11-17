<?php
/**
 * Halaman Form Booking Kamar Kos
 * Input: Kamar (info), Tanggal Mulai Sewa, Lama Sewa
 * Sistem menghitung: total_harga = harga_kamar * lama_sewa secara real-time
 */

$photoUrl = !empty($kamar['foto']) && file_exists(__DIR__ . '/../../public/images/kamar/' . $kamar['foto'])
    ? BASE_URL . '/images/kamar/' . htmlspecialchars($kamar['foto'])
    : (file_exists(__DIR__ . '/../../public/images/' . $kamar['foto'])
        ? BASE_URL . '/images/' . htmlspecialchars($kamar['foto'])
        : BASE_URL . '/images/default_kamar.jpg');

$hargaPerBulan = (float)$kamar['harga'];
$initialLamaSewa = max(1, (int)($old['lama_sewa'] ?? 1));
$initialTotalHarga = $hargaPerBulan * $initialLamaSewa;
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('kamar'); ?>" class="text-decoration-none">Daftar Kamar</a></li>
            <li class="breadcrumb-item"><a href="<?= url('kamar/detail/' . $kamar['id']); ?>" class="text-decoration-none"><?= htmlspecialchars($kamar['nomor_kamar']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Formulir Booking</li>
        </ol>
    </nav>

    <!-- Reusable Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Halaman -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">
                        <i class="bi bi-bookmark-plus-fill text-primary me-2"></i>Formulir Reservasi Booking
                    </h2>
                    <p class="text-secondary small mb-0">
                        Lengkapi informasi sewa untuk mengajukan booking kamar kos ini.
                    </p>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill small fw-semibold">
                    <i class="bi bi-check-circle-fill me-1"></i> Status: Tersedia
                </span>
            </div>

            <div class="row g-4">
                <!-- Kolom Kiri: Ringkasan Kamar Terpilih -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                        <div class="position-relative" style="height: 220px;">
                            <img src="<?= $photoUrl; ?>" 
                                 alt="<?= htmlspecialchars($kamar['nomor_kamar']); ?>" 
                                 class="w-100 h-100 object-fit-cover"
                                 onerror="this.src='<?= BASE_URL; ?>/images/default_kamar.jpg'">
                            <span class="badge bg-success position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill fw-semibold shadow-sm">
                                <i class="bi bi-door-open-fill me-1"></i> Kamar Tersedia
                            </span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($kamar['nomor_kamar']); ?></h4>
                                <span class="badge bg-light text-secondary border">ID #00<?= htmlspecialchars($kamar['id']); ?></span>
                            </div>

                            <p class="text-secondary small mb-3">
                                <?= htmlspecialchars(mb_strimwidth($kamar['deskripsi'] ?? 'Kamar kos nyaman dan siap huni.', 0, 100, '...')); ?>
                            </p>

                            <hr class="my-2">

                            <div class="my-3">
                                <span class="text-secondary small d-block">Harga Sewa Kamar:</span>
                                <h4 class="fw-bold text-primary mb-0"><?= formatRupiah($kamar['harga']); ?> <span class="fs-6 text-muted fw-normal">/ bulan</span></h4>
                            </div>

                            <div class="mt-auto pt-3 border-top">
                                <div class="d-flex align-items-center gap-2 text-secondary small">
                                    <i class="bi bi-person-circle text-primary"></i>
                                    <span>Pemesan: <strong><?= htmlspecialchars($currentUser['nama']); ?></strong></span>
                                </div>
                                <div class="d-flex align-items-center gap-2 text-secondary small mt-1">
                                    <i class="bi bi-envelope text-primary"></i>
                                    <span><?= htmlspecialchars($currentUser['email']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Form Input Booking -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h5 class="fw-bold text-dark mb-3">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Detail Pengajuan Sewa
                        </h5>

                        <form action="<?= url('booking/tambah/' . $kamar['id']); ?>" method="POST" id="formBooking">
                            <?= AuthHelper::csrfField(); ?>
                            <!-- 1. Kamar yang Dipilih (Read-only Display) -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">
                                    <i class="bi bi-door-closed me-1 text-primary"></i> Kamar Terpilih
                                </label>
                                <input type="text" 
                                       class="form-control bg-light rounded-3 py-2 fw-medium text-dark" 
                                       value="<?= htmlspecialchars($kamar['nomor_kamar']); ?> (<?= formatRupiah($kamar['harga']); ?> / bulan)" 
                                       readonly>
                            </div>

                            <!-- 2. Tanggal Mulai Sewa -->
                            <div class="mb-3">
                                <label for="tanggal_mulai" class="form-label small fw-semibold text-secondary">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i> Tanggal Mulai Sewa <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="tanggal_mulai" 
                                       id="tanggal_mulai" 
                                       class="form-control rounded-3 py-2" 
                                       min="<?= date('Y-m-d'); ?>" 
                                       value="<?= htmlspecialchars($old['tanggal_mulai'] ?? date('Y-m-d', strtotime('+1 day'))); ?>" 
                                       required>
                                <div class="form-text small">
                                    Pilih tanggal mulai menempati kamar (minimal tanggal hari ini).
                                </div>
                            </div>

                            <!-- 3. Lama Sewa (Bulan) -->
                            <div class="mb-3">
                                <label for="lama_sewa" class="form-label small fw-semibold text-secondary">
                                    <i class="bi bi-clock-history me-1 text-primary"></i> Lama Sewa (Bulan) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           name="lama_sewa" 
                                           id="inputLamaSewa" 
                                           class="form-control rounded-start-3 py-2 fw-bold text-primary" 
                                           min="1" 
                                           max="36" 
                                           value="<?= htmlspecialchars((string)$initialLamaSewa); ?>" 
                                           required>
                                    <span class="input-group-text bg-light text-secondary rounded-end-3">Bulan</span>
                                </div>

                                <!-- Tombol Cepat Pilihan Durasi -->
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-preset-durasi" data-bulan="1">1 Bulan</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-preset-durasi" data-bulan="3">3 Bulan</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-preset-durasi" data-bulan="6">6 Bulan</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-preset-durasi" data-bulan="12">1 Tahun</button>
                                </div>
                            </div>

                            <!-- Perhitungan Otomatis: total_harga = harga_kamar * lama_sewa -->
                            <div class="card border border-primary border-opacity-25 rounded-4 p-3 bg-primary bg-opacity-10 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1 text-secondary small">
                                    <span>Tarif Kamar:</span>
                                    <span><?= formatRupiah($hargaPerBulan); ?> / bln</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 text-secondary small">
                                    <span>Durasi Sewa:</span>
                                    <span><span id="labelLamaSewa"><?= $initialLamaSewa; ?></span> Bulan</span>
                                </div>
                                <hr class="my-2 border-primary border-opacity-25">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="small fw-semibold text-dark d-block">Total Estimasi Biaya:</span>
                                        <small class="text-muted" style="font-size: 0.75rem;">(harga_kamar &times; lama_sewa)</small>
                                    </div>
                                    <div class="text-end">
                                        <h3 class="fw-bold text-primary mb-0" id="labelTotalHarga">
                                            <?= formatRupiah($initialTotalHarga); ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Catatan Prosedur Tanpa Pembayaran Online -->
                            <div class="alert alert-info border-0 rounded-3 small p-3 mb-4">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-info-circle-fill text-primary fs-5 flex-shrink-0 mt-0"></i>
                                    <div>
                                        <strong>Informasi Prosedur Booking:</strong><br>
                                        Setelah menekan konfirmasi, data Anda akan disimpan dengan status <strong>"Menunggu"</strong> dan kamar akan otomatis ditandai <strong>"Booking"</strong>. Pembayaran dilakukan secara langsung/manual setelah diverifikasi oleh pemilik kos (tanpa pembayaran online).
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Aksi Submit Form -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill py-2 px-4 fw-semibold flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-check-circle-fill"></i> Konfirmasi &amp; Simpan Booking
                                </button>
                                <a href="<?= url('kamar/detail/' . $kamar['id']); ?>" class="btn btn-outline-secondary rounded-pill py-2 px-4 fw-semibold">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script Hitung Total Harga Otomatis -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hargaKamar = <?= (float)$hargaPerBulan; ?>;
    const inputLamaSewa = document.getElementById('inputLamaSewa');
    const labelLamaSewa = document.getElementById('labelLamaSewa');
    const labelTotalHarga = document.getElementById('labelTotalHarga');
    const presetButtons = document.querySelectorAll('.btn-preset-durasi');

    function formatRupiahJs(number) {
        return 'Rp ' + Math.round(number).toLocaleString('id-ID');
    }

    function updateTotal() {
        let durasi = parseInt(inputLamaSewa.value);
        if (isNaN(durasi) || durasi < 1) {
            durasi = 1;
        }
        labelLamaSewa.innerText = durasi;
        const total = hargaKamar * durasi;
        labelTotalHarga.innerText = formatRupiahJs(total);
    }

    inputLamaSewa.addEventListener('input', updateTotal);
    inputLamaSewa.addEventListener('change', updateTotal);

    presetButtons.forEach(button => {
        button.addEventListener('click', function () {
            inputLamaSewa.value = this.dataset.bulan;
            updateTotal();
        });
    });
});
</script>
