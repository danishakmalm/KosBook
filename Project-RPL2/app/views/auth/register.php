<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <!-- Reusable Alert Flash Message -->
            <?php require __DIR__ . '/../components/alert.php'; ?>

            <!-- Card Form Register -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <span class="d-inline-flex justify-content-center align-items-center rounded-circle bg-warning bg-opacity-20 text-warning mb-3" style="width: 58px; height: 58px;">
                        <i class="bi bi-person-plus fs-3 text-dark"></i>
                    </span>
                    <h3 class="fw-bold text-dark mb-1">Daftar Akun Baru</h3>
                    <p class="text-secondary small mb-0">Daftarkan diri Anda untuk mulai mencari dan memesan kamar kos KosBook.</p>
                </div>

                <!-- Form Register -->
                <form action="<?= url('auth/register'); ?>" method="POST" id="registerForm">
                    <?= AuthHelper::csrfField(); ?>
                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-semibold text-secondary">
                            <i class="bi bi-person me-1"></i> Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control rounded-3 py-2" 
                               id="nama" 
                               name="nama" 
                               placeholder="Contoh: Muhammad Reza" 
                               required 
                               value="<?= htmlspecialchars($old['nama'] ?? ''); ?>"
                               autocomplete="name">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold text-secondary">
                            <i class="bi bi-envelope me-1"></i> Alamat Email Aktif <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control rounded-3 py-2" 
                               id="email" 
                               name="email" 
                               placeholder="nama@email.com" 
                               required 
                               value="<?= htmlspecialchars($old['email'] ?? ''); ?>"
                               autocomplete="email">
                        <div class="form-text small text-muted">Pastikan email belum terdaftar di sistem.</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label small fw-semibold text-secondary">
                                <i class="bi bi-lock me-1"></i> Kata Sandi <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control rounded-3 py-2" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimal 6 karakter" 
                                   required 
                                   minlength="6"
                                   autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label small fw-semibold text-secondary">
                                <i class="bi bi-shield-check me-1"></i> Ulangi Sandi <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control rounded-3 py-2" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   placeholder="Ulangi kata sandi" 
                                   required 
                                   minlength="6"
                                   autocomplete="new-password">
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required checked>
                        <label class="form-check-label small text-secondary" for="terms">
                            Saya menyetujui <a href="#" class="text-primary text-decoration-none">Syarat &amp; Ketentuan</a> layanan KosBook
                        </label>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 rounded-pill py-2 fw-semibold shadow-sm mb-3 text-dark">
                        <i class="bi bi-person-check me-1"></i> Buat Akun Sekarang
                    </button>
                </form>

                <div class="text-center small text-secondary">
                    Sudah memiliki akun? <a href="<?= url('auth/login'); ?>" class="fw-semibold text-primary text-decoration-none">Masuk di sini</a>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="<?= url(''); ?>" class="text-secondary small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Utama
                </a>
            </div>

        </div>
    </div>
</div>
