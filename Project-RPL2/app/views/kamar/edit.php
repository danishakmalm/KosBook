<div class="container py-4">
    <!-- Breadcrumb & Judul Halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('dashboard/admin'); ?>" class="text-decoration-none">Dashboard Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('kamar/admin'); ?>" class="text-decoration-none">Kelola Kamar</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit <?= htmlspecialchars($kamar['nomor_kamar']); ?></li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">Ubah Data Kamar Kos</h2>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= url('kamar/admin'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kamar
            </a>
        </div>
    </div>

    <!-- Reusable Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-pencil-square text-primary me-2"></i> Edit Data <?= htmlspecialchars($kamar['nomor_kamar']); ?>
                        </h5>
                        <p class="text-muted small mb-0">Perbarui informasi kamar, tarif sewa bulanan, dan status ketersediaan.</p>
                    </div>
                    <span class="badge bg-light text-secondary border px-3 py-2">ID Kamar: #00<?= $kamar['id']; ?></span>
                </div>

                <form action="<?= url('kamar/edit/' . $kamar['id']); ?>" method="POST" enctype="multipart/form-data">
                    <?= AuthHelper::csrfField(); ?>
                    <div class="row g-3 mb-3">
                        <!-- Nomor Kamar -->
                        <div class="col-md-6">
                            <label for="nomor_kamar" class="form-label small fw-semibold text-secondary">
                                Nomor / Kode Kamar <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control rounded-3 py-2" 
                                   id="nomor_kamar" 
                                   name="nomor_kamar" 
                                   value="<?= htmlspecialchars($kamar['nomor_kamar']); ?>" 
                                   required>
                            <div class="form-text small text-muted">Pastikan nomor kamar unik dan mudah dikenali calon penghuni.</div>
                        </div>

                        <!-- Harga per Bulan -->
                        <div class="col-md-6">
                            <label for="harga" class="form-label small fw-semibold text-secondary">
                                Harga Sewa / Bulan (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">Rp</span>
                                <input type="number" 
                                       class="form-control rounded-end-3 py-2" 
                                       id="harga" 
                                       name="harga" 
                                       min="0" 
                                       step="1000"
                                       value="<?= htmlspecialchars((int)$kamar['harga']); ?>" 
                                       required>
                            </div>
                            <div class="form-text small text-muted">Harga harus berupa angka dan tidak boleh bernilai negatif.</div>
                        </div>
                    </div>

                    <!-- Status Kamar & Foto -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label small fw-semibold text-secondary">
                                Status Ketersediaan Kamar <span class="text-danger">*</span>
                            </label>
                            <select class="form-select rounded-3 py-2" id="status" name="status" required>
                                <option value="tersedia" <?= ($kamar['status'] === 'tersedia') ? 'selected' : ''; ?>>Tersedia (Siap Huni)</option>
                                <option value="booking" <?= ($kamar['status'] === 'booking') ? 'selected' : ''; ?>>Booking (Dalam Proses Reservasi)</option>
                                <option value="terisi" <?= ($kamar['status'] === 'terisi') ? 'selected' : ''; ?>>Terisi (Sedang Dihuni)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="foto" class="form-label small fw-semibold text-secondary">
                                Ganti Foto Kamar
                            </label>
                            <input type="file" 
                                   class="form-control rounded-3 py-2" 
                                   id="foto" 
                                   name="foto" 
                                   accept="image/jpeg,image/png,image/webp,image/jpg"
                                   onchange="previewImage(this)">
                            <div class="form-text small text-muted">Pilih file baru jika ingin mengganti foto saat ini (Maksimal 2 MB).</div>
                        </div>
                    </div>

                    <!-- Tampilan Foto Saat Ini & Preview -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary d-block">Foto Kamar Saat Ini:</label>
                            <?php 
                                $currentPhotoUrl = !empty($kamar['foto']) && file_exists(__DIR__ . '/../../public/images/kamar/' . $kamar['foto'])
                                    ? BASE_URL . '/images/kamar/' . htmlspecialchars($kamar['foto'])
                                    : (file_exists(__DIR__ . '/../../public/images/' . $kamar['foto'])
                                        ? BASE_URL . '/images/' . htmlspecialchars($kamar['foto'])
                                        : BASE_URL . '/images/default_kamar.jpg');
                            ?>
                            <div class="d-flex align-items-center gap-3 p-2 bg-light rounded-3 border">
                                <img src="<?= $currentPhotoUrl; ?>" 
                                     alt="Foto Saat Ini" 
                                     class="rounded-3 border shadow-sm object-fit-cover" 
                                     style="width: 100px; height: 75px;"
                                     onerror="this.src='<?= BASE_URL; ?>/images/default_kamar.jpg'">
                                <div class="small text-muted">
                                    <div>Nama File:</div>
                                    <strong class="text-dark"><?= htmlspecialchars($kamar['foto'] ?? 'default_kamar.jpg'); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 d-none" id="previewContainer">
                            <label class="form-label small fw-semibold text-secondary d-block">Preview Foto Baru:</label>
                            <div class="p-2 bg-light rounded-3 border">
                                <img id="imagePreview" src="#" alt="Preview Foto Baru" class="rounded-3 border shadow-sm object-fit-cover" style="width: 100px; height: 75px;">
                            </div>
                        </div>
                    </div>

                    <!-- Fasilitas Kamar -->
                    <div class="mb-3">
                        <label for="fasilitas" class="form-label small fw-semibold text-secondary">
                            Fasilitas Kamar
                        </label>
                        <input type="text" 
                               class="form-control rounded-3 py-2" 
                               id="fasilitas" 
                               name="fasilitas" 
                               value="<?= htmlspecialchars($kamar['fasilitas'] ?? ''); ?>">
                        <div class="form-text small text-muted">Pisahkan setiap fasilitas dengan tanda koma (,). Contoh: AC, KM Dalam, Springbed, WiFi.</div>
                    </div>

                    <!-- Deskripsi Kamar -->
                    <div class="mb-4">
                        <label for="deskripsi" class="form-label small fw-semibold text-secondary">
                            Deskripsi Lengkap Kamar
                        </label>
                        <textarea class="form-control rounded-3 py-2" 
                                  id="deskripsi" 
                                  name="deskripsi" 
                                  rows="4"><?= htmlspecialchars($kamar['deskripsi'] ?? ''); ?></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="<?= url('kamar/detail/' . $kamar['id']); ?>" class="btn btn-outline-info rounded-pill px-3">
                            <i class="bi bi-eye me-1"></i> Lihat Tampilan Publik
                        </a>
                        <div class="d-flex gap-2">
                            <a href="<?= url('kamar/admin'); ?>" class="btn btn-light rounded-pill px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            previewContainer.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.classList.add('d-none');
    }
}
</script>
