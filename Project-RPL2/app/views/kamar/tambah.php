<div class="container py-4">
    <!-- Breadcrumb & Judul Halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('dashboard/admin'); ?>" class="text-decoration-none">Dashboard Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('kamar/admin'); ?>" class="text-decoration-none">Kelola Kamar</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Kamar</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">Tambah Kamar Kos Baru</h2>
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
                <div class="mb-4 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-door-closed text-primary me-2"></i> Formulir Tambah Unit Kamar
                    </h5>
                    <p class="text-muted small mb-0">Lengkapi informasi unit kamar kos di bawah ini secara akurat.</p>
                </div>

                <form action="<?= url('kamar/tambah'); ?>" method="POST" enctype="multipart/form-data">
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
                                   placeholder="Contoh: Kamar C-301 atau VIP-03" 
                                   value="<?= htmlspecialchars($old['nomor_kamar'] ?? ''); ?>" 
                                   required>
                            <div class="form-text small text-muted">Nomor kamar harus unik dan belum digunakan.</div>
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
                                       placeholder="Contoh: 1250000" 
                                       min="0" 
                                       step="1000"
                                       value="<?= htmlspecialchars($old['harga'] ?? ''); ?>" 
                                       required>
                            </div>
                            <div class="form-text small text-muted">Harga harus berupa angka dan tidak boleh bernilai negatif.</div>
                        </div>
                    </div>

                    <!-- Status Kamar -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label small fw-semibold text-secondary">
                                Status Awal Kamar <span class="text-danger">*</span>
                            </label>
                            <select class="form-select rounded-3 py-2" id="status" name="status" required>
                                <option value="tersedia" <?= (($old['status'] ?? 'tersedia') === 'tersedia') ? 'selected' : ''; ?>>Tersedia (Siap Huni)</option>
                                <option value="booking" <?= (($old['status'] ?? '') === 'booking') ? 'selected' : ''; ?>>Booking (Dalam Proses Reservasi)</option>
                                <option value="terisi" <?= (($old['status'] ?? '') === 'terisi') ? 'selected' : ''; ?>>Terisi (Sedang Dihuni)</option>
                            </select>
                        </div>

                        <!-- Upload Foto Kamar -->
                        <div class="col-md-6">
                            <label for="foto" class="form-label small fw-semibold text-secondary">
                                Foto Kamar Kos
                            </label>
                            <input type="file" 
                                   class="form-control rounded-3 py-2" 
                                   id="foto" 
                                   name="foto" 
                                   accept="image/jpeg,image/png,image/webp,image/jpg"
                                   onchange="previewImage(this)">
                            <div class="form-text small text-muted">Format: JPG, JPEG, PNG, WEBP (Maksimal 2 MB). Kosongkan jika memakai foto default.</div>
                        </div>
                    </div>

                    <!-- Preview Foto -->
                    <div class="mb-3 d-none" id="previewContainer">
                        <label class="form-label small fw-semibold text-secondary d-block">Preview Foto Baru:</label>
                        <img id="imagePreview" src="#" alt="Preview Foto" class="rounded-3 border shadow-sm object-fit-cover" style="max-height: 180px; max-width: 280px;">
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
                               placeholder="Contoh: AC Dingin, Kamar Mandi Dalam, Kasur Springbed, Meja Belajar, WiFi 50 Mbps" 
                               value="<?= htmlspecialchars($old['fasilitas'] ?? ''); ?>">
                        <div class="form-text small text-muted">Pisahkan setiap fasilitas dengan tanda koma (,).</div>
                    </div>

                    <!-- Deskripsi Kamar -->
                    <div class="mb-4">
                        <label for="deskripsi" class="form-label small fw-semibold text-secondary">
                            Deskripsi Lengkap Kamar
                        </label>
                        <textarea class="form-control rounded-3 py-2" 
                                  id="deskripsi" 
                                  name="deskripsi" 
                                  rows="4" 
                                  placeholder="Tuliskan keunggulan kamar, pencahayaan, ventilasi, dan informasi penting lainnya..."><?= htmlspecialchars($old['deskripsi'] ?? ''); ?></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url('kamar/admin'); ?>" class="btn btn-light rounded-pill px-4">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Kamar Baru
                        </button>
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
