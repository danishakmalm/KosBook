<div class="container py-4">
    <!-- Breadcrumb & Judul Halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('dashboard/admin'); ?>" class="text-decoration-none">Dashboard Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Kelola Data Kamar</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">Manajemen Data Kamar Kos</h2>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="<?= url('dashboard/admin'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Dashboard Admin
            </a>
            <a href="<?= url('kamar/tambah'); ?>" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kamar Baru
            </a>
        </div>
    </div>

    <!-- Reusable Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <!-- Stat Cards Ringkasan Kamar -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary small d-block">Total Seluruh Kamar</span>
                        <h3 class="fw-bold text-dark mb-0"><?= $stats['total_kamar']; ?></h3>
                    </div>
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3">
                        <i class="bi bi-door-open fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary small d-block">Kamar Tersedia</span>
                        <h3 class="fw-bold text-success mb-0"><?= $stats['kamar_tersedia']; ?></h3>
                    </div>
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3">
                        <i class="bi bi-check-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary small d-block">Sedang Dibooking</span>
                        <h3 class="fw-bold text-warning mb-0"><?= $stats['kamar_booking']; ?></h3>
                    </div>
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3">
                        <i class="bi bi-hourglass-split fs-3 text-dark"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary small d-block">Kamar Terisi</span>
                        <h3 class="fw-bold text-secondary mb-0"><?= $stats['kamar_terisi']; ?></h3>
                    </div>
                    <div class="rounded-3 bg-secondary bg-opacity-10 text-secondary p-3">
                        <i class="bi bi-person-fill-lock fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Tabel Data Kamar -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <!-- Filter Status Pill -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?= url('kamar/admin'); ?>" class="btn <?= ($filterStatus === 'semua') ? 'btn-primary' : 'btn-outline-secondary'; ?> rounded-start-pill px-3">
                        Semua (<?= $stats['total_kamar']; ?>)
                    </a>
                    <a href="<?= url('kamar/admin?status=tersedia'); ?>" class="btn <?= ($filterStatus === 'tersedia') ? 'btn-success' : 'btn-outline-success'; ?> px-3">
                        Tersedia (<?= $stats['kamar_tersedia']; ?>)
                    </a>
                    <a href="<?= url('kamar/admin?status=booking'); ?>" class="btn <?= ($filterStatus === 'booking') ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'; ?> px-3">
                        Booking (<?= $stats['kamar_booking']; ?>)
                    </a>
                    <a href="<?= url('kamar/admin?status=terisi'); ?>" class="btn <?= ($filterStatus === 'terisi') ? 'btn-secondary' : 'btn-outline-secondary'; ?> rounded-end-pill px-3">
                        Terisi (<?= $stats['kamar_terisi']; ?>)
                    </a>
                </div>

                <div class="small text-muted">
                    Menampilkan <strong><?= count($rooms); ?></strong> kamar
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">No. Kamar</th>
                        <th>Foto</th>
                        <th>Tarif Sewa</th>
                        <th>Fasilitas</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data kamar pada kategori ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $r): 
                            $badgeClass = [
                                'tersedia' => 'bg-success',
                                'booking'  => 'bg-warning text-dark',
                                'terisi'   => 'bg-secondary'
                            ][$r['status']] ?? 'bg-primary';

                            $photoUrl = !empty($r['foto']) && file_exists(__DIR__ . '/../../public/images/kamar/' . $r['foto'])
                                ? BASE_URL . '/images/kamar/' . htmlspecialchars($r['foto'])
                                : (file_exists(__DIR__ . '/../../public/images/' . $r['foto'])
                                    ? BASE_URL . '/images/' . htmlspecialchars($r['foto'])
                                    : BASE_URL . '/images/default_kamar.jpg');
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark fs-6">
                                        <a href="<?= url('kamar/detail/' . $r['id']); ?>" class="text-decoration-none text-dark hover-primary">
                                            <?= htmlspecialchars($r['nomor_kamar']); ?>
                                        </a>
                                    </div>
                                    <small class="text-muted">ID: #00<?= $r['id']; ?></small>
                                </td>
                                <td>
                                    <img src="<?= $photoUrl; ?>" 
                                         alt="<?= htmlspecialchars($r['nomor_kamar']); ?>" 
                                         class="rounded-3 object-fit-cover shadow-sm border" 
                                         style="width: 64px; height: 50px;"
                                         onerror="this.src='<?= BASE_URL; ?>/images/default_kamar.jpg'">
                                </td>
                                <td>
                                    <span class="fw-bold text-primary"><?= formatRupiah($r['harga']); ?></span>
                                    <small class="text-muted d-block">/ bulan</small>
                                </td>
                                <td style="max-width: 250px;">
                                    <div class="small text-truncate" title="<?= htmlspecialchars($r['fasilitas'] ?? '-'); ?>">
                                        <?= htmlspecialchars($r['fasilitas'] ?? '-'); ?>
                                    </div>
                                    <small class="text-muted d-block text-truncate" title="<?= htmlspecialchars($r['deskripsi'] ?? ''); ?>">
                                        <?= htmlspecialchars($r['deskripsi'] ?? ''); ?>
                                    </small>
                                </td>
                                <td>
                                    <!-- Dropdown Ubah Status Cepat -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm <?= $badgeClass; ?> rounded-pill dropdown-toggle px-3 py-1 fw-semibold border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <?= ucfirst($r['status']); ?>
                                        </button>
                                        <ul class="dropdown-menu shadow-sm border-0 small">
                                            <li class="dropdown-header text-muted">Ubah Status Kamar:</li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2 <?= $r['status'] === 'tersedia' ? 'active' : ''; ?>" 
                                                   href="<?= url('kamar/ubah_status/' . $r['id'] . '?status=tersedia'); ?>">
                                                    <span class="badge bg-success rounded-circle p-1"> </span> Tersedia
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2 <?= $r['status'] === 'booking' ? 'active' : ''; ?>" 
                                                   href="<?= url('kamar/ubah_status/' . $r['id'] . '?status=booking'); ?>">
                                                    <span class="badge bg-warning rounded-circle p-1"> </span> Booking
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2 <?= $r['status'] === 'terisi' ? 'active' : ''; ?>" 
                                                   href="<?= url('kamar/ubah_status/' . $r['id'] . '?status=terisi'); ?>">
                                                    <span class="badge bg-secondary rounded-circle p-1"> </span> Terisi
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('kamar/detail/' . $r['id']); ?>" class="btn btn-outline-info rounded-pill px-2 py-1 me-1" title="Lihat Detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <a href="<?= url('kamar/edit/' . $r['id']); ?>" class="btn btn-outline-primary rounded-pill px-2 py-1 me-1" title="Ubah Data">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-outline-danger rounded-pill px-2 py-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDelete<?= $r['id']; ?>" 
                                                title="Hapus Kamar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal Konfirmasi Hapus Bootstrap 5 -->
                                    <div class="modal fade" id="modalDelete<?= $r['id']; ?>" tabindex="-1" aria-labelledby="modalLabel<?= $r['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content border-0 rounded-4 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-danger" id="modalLabel<?= $r['id']; ?>">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Konfirmasi Hapus Kamar
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <p class="mb-1">Apakah Anda yakin ingin menghapus data kamar <strong><?= htmlspecialchars($r['nomor_kamar']); ?></strong>?</p>
                                                    <small class="text-muted">Tindakan ini tidak dapat dibatalkan dan file foto terkait akan dihapus secara permanen.</small>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <a href="<?= url('kamar/hapus/' . $r['id']); ?>" class="btn btn-danger rounded-pill px-4 fw-semibold">
                                                        <i class="bi bi-trash-fill me-1"></i> Ya, Hapus Kamar
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Modal -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
