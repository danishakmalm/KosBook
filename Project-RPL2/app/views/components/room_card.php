<?php
/**
 * Komponen Reusable: Card Kamar (Bootstrap Card)
 * Menerima variabel array $kamar:
 * ['id', 'nomor_kamar', 'harga', 'fasilitas', 'deskripsi', 'foto', 'status']
 */
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
    'tersedia' => 'Tersedia',
    'booking'  => 'Dibooking',
    'terisi'   => 'Terisi'
][$kamar['status']] ?? ucfirst($kamar['status']);

// Ekstrak fasilitas menjadi array jika dalam bentuk string
$fasilitasList = is_array($kamar['fasilitas']) 
    ? $kamar['fasilitas'] 
    : array_map('trim', explode(',', $kamar['fasilitas'] ?? ''));
$fasilitasPreview = array_slice($fasilitasList, 0, 3);

$photoUrl = !empty($kamar['foto']) && file_exists(__DIR__ . '/../../public/images/kamar/' . $kamar['foto'])
    ? BASE_URL . '/images/kamar/' . htmlspecialchars($kamar['foto'])
    : (file_exists(__DIR__ . '/../../public/images/' . $kamar['foto'])
        ? BASE_URL . '/images/' . htmlspecialchars($kamar['foto'])
        : BASE_URL . '/images/default_kamar.jpg');
?>
<div class="col-md-6 col-lg-4">
    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden room-card transition-all position-relative">
        <!-- Room Thumbnail & Status Badge -->
        <div class="position-relative overflow-hidden bg-dark" style="height: 210px;">
            <img src="<?= $photoUrl; ?>" 
                 alt="<?= htmlspecialchars($kamar['nomor_kamar']); ?>" 
                 class="w-100 h-100 object-fit-cover transition-all"
                 onerror="this.src='<?= BASE_URL; ?>/images/default_kamar.jpg'">
            
            <!-- Status Badge -->
            <span class="badge <?= $statusClass; ?> position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill fw-semibold shadow-sm d-inline-flex align-items-center gap-1">
                <i class="bi <?= $statusIcon; ?> small"></i> <?= $statusLabel; ?>
            </span>
        </div>

        <!-- Card Body -->
        <div class="card-body p-4 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title fw-bold text-dark mb-0">
                    <?= htmlspecialchars($kamar['nomor_kamar']); ?>
                </h5>
                <span class="badge bg-light text-secondary border small px-2 py-1">
                    #00<?= htmlspecialchars($kamar['id']); ?>
                </span>
            </div>

            <p class="card-text text-secondary small mb-3 flex-grow-1">
                <?= htmlspecialchars(mb_strimwidth($kamar['deskripsi'] ?? 'Kamar kos nyaman dan strategis.', 0, 95, '...')); ?>
            </p>

            <!-- Fasilitas Singkat Badges -->
            <div class="d-flex flex-wrap gap-1 mb-4">
                <?php foreach ($fasilitasPreview as $f): if (!empty($f)): ?>
                    <span class="badge bg-light text-secondary border px-2 py-1 small fw-normal">
                        <i class="bi bi-check2 text-success me-1"></i><?= htmlspecialchars($f); ?>
                    </span>
                <?php endif; endforeach; ?>
                <?php if (count($fasilitasList) > 3): ?>
                    <span class="badge bg-light text-muted border px-2 py-1 small fw-normal">
                        +<?= count($fasilitasList) - 3; ?> lainnya
                    </span>
                <?php endif; ?>
            </div>

            <!-- Price & Action Button (Tombol Lihat Detail) -->
            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                <div>
                    <span class="text-secondary small d-block" style="font-size: 0.75rem;">Mulai dari</span>
                    <span class="fw-bold text-primary fs-5"><?= formatRupiah($kamar['harga']); ?></span>
                    <small class="text-muted">/bln</small>
                </div>
                <a href="<?= url('kamar/detail/' . $kamar['id']); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1">
                    Lihat Detail <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
