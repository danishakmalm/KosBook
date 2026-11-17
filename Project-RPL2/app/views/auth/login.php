<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <!-- Reusable Alert Flash Message -->
            <?php require __DIR__ . '/../components/alert.php'; ?>

            <!-- Card Form Login -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <span class="d-inline-flex justify-content-center align-items-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 58px; height: 58px;">
                        <i class="bi bi-box-arrow-in-right fs-3"></i>
                    </span>
                    <h3 class="fw-bold text-dark mb-1">Masuk Akun</h3>
                    <p class="text-secondary small mb-0">Selamat datang kembali! Silakan masuk ke akun KosBook Anda.</p>
                </div>

                <!-- Form Login -->
                <form action="<?= url('auth/login'); ?>" method="POST" id="loginForm">
                    <?= AuthHelper::csrfField(); ?>
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold text-secondary">
                            <i class="bi bi-envelope me-1"></i> Alamat Email
                        </label>
                        <input type="email" 
                               class="form-control rounded-3 py-2" 
                               id="email" 
                               name="email" 
                               placeholder="nama@email.com" 
                               required 
                               value="<?= htmlspecialchars($old['email'] ?? ''); ?>" 
                               autocomplete="email">
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label small fw-semibold text-secondary mb-0">
                                <i class="bi bi-lock me-1"></i> Kata Sandi
                            </label>
                        </div>
                        <input type="password" 
                               class="form-control rounded-3 py-2" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••" 
                               required 
                               autocomplete="current-password">
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                        <label class="form-check-label small text-secondary" for="remember">Ingat saya di perangkat ini</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold shadow-sm mb-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                    </button>
                </form>


                <div class="text-center small text-secondary">
                    Belum punya akun? <a href="<?= url('auth/register'); ?>" class="fw-semibold text-primary text-decoration-none">Daftar sekarang</a>
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
