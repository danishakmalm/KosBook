<?php
// Fallback aman jika view dimuat tanpa melalui DashboardController
if (!isset($aiConfig) || !is_array($aiConfig)) {
    require_once __DIR__ . '/../../services/AiConfigService.php';
    $aiConfig = (new AiConfigService())->getCurrentConfig();
}
?>
<div class="container py-4">
    <!-- Breadcrumb & Header Title -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="<?= url(''); ?>" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard Admin</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Admin
            </h2>
            <p class="text-secondary small mb-0">Pantau statistik operasional, kelola reservasi, dan ketersediaan kamar kos.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill small fw-semibold border border-danger border-opacity-25">
                <i class="bi bi-shield-lock-fill me-1"></i> Mode Administrator Aktif (<?= htmlspecialchars($currentAdmin['nama'] ?? 'Admin'); ?>)
            </span>
            <a href="#konfigurasi-ai" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-2 fw-semibold">
                <i class="bi bi-cpu-fill me-1"></i> Konfigurasi AI
            </a>
            <a href="<?= url('kamar/tambah'); ?>" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kamar Baru
            </a>
            <a href="<?= url('booking/admin'); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-semibold">
                <i class="bi bi-journal-bookmark me-1"></i> Kelola Booking
            </a>
        </div>
    </div>

    <!-- Reusable Alert Flash Message -->
    <?php require __DIR__ . '/../components/alert.php'; ?>

    <!-- ========================================================================= -->
    <!-- STATISTIK DASHBOARD (7 METRIK)                                            -->
    <!-- 1. Total Kamar     2. Kamar Tersedia  3. Kamar Booking  4. Kamar Terisi   -->
    <!-- 5. Total User      6. Total Booking   7. Booking Menunggu                 -->
    <!-- ========================================================================= -->

    <!-- Baris 1: Statistik Status Kamar (4 Kartu) -->
    <div class="row g-3 mb-3">
        <!-- 1. Total Kamar -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Total Kamar</span>
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width: 38px; height: 38px;">
                        <i class="bi bi-door-closed-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?= (int)($stats['total_kamar'] ?? 0); ?></h3>
                <small class="text-muted">Unit kamar terdaftar</small>
            </div>
        </div>

        <!-- 2. Kamar Tersedia -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Kamar Tersedia</span>
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 38px; height: 38px;">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-success mb-0"><?= (int)($stats['kamar_tersedia'] ?? 0); ?></h3>
                <small class="text-muted">Siap ditempati</small>
            </div>
        </div>

        <!-- 3. Kamar Booking -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Kamar Booking</span>
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-warning bg-opacity-20 text-warning" style="width: 38px; height: 38px;">
                        <i class="bi bi-hourglass-split fs-5 text-dark"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-warning mb-0"><?= (int)($stats['kamar_booking'] ?? 0); ?></h3>
                <small class="text-muted">Sedang dipesan/tinjau</small>
            </div>
        </div>

        <!-- 4. Kamar Terisi -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-secondary border-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Kamar Terisi</span>
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-secondary bg-opacity-10 text-secondary" style="width: 38px; height: 38px;">
                        <i class="bi bi-house-check-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-secondary mb-0"><?= (int)($stats['kamar_terisi'] ?? 0); ?></h3>
                <small class="text-muted">Sedang dihuni aktif</small>
            </div>
        </div>
    </div>

    <!-- Baris 2: Statistik Pengguna & Reservasi (3 Kartu) -->
    <div class="row g-3 mb-4">
        <!-- 5. Total User -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-info border-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Total User</span>
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-info bg-opacity-10 text-info" style="width: 38px; height: 38px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?= (int)($stats['total_user'] ?? 0); ?></h3>
                <small class="text-muted">Akun calon penghuni kos</small>
            </div>
        </div>

        <!-- 6. Total Booking -->
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-dark border-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Total Booking</span>
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-dark bg-opacity-10 text-dark" style="width: 38px; height: 38px;">
                        <i class="bi bi-journal-bookmark-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?= (int)($stats['total_booking'] ?? 0); ?></h3>
                <small class="text-muted">Seluruh transaksi booking</small>
            </div>
        </div>

        <!-- 7. Booking Menunggu -->
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-warning border-4 <?= ($stats['booking_menunggu'] > 0) ? 'bg-warning bg-opacity-10' : ''; ?>">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Booking Menunggu</span>
                    <div class="d-inline-flex justify-content-center align-items-center rounded-3 bg-warning text-dark" style="width: 38px; height: 38px;">
                        <i class="bi bi-bell-fill fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="fw-bold text-warning mb-0"><?= (int)($stats['booking_menunggu'] ?? 0); ?></h3>
                    <?php if ($stats['booking_menunggu'] > 0): ?>
                        <span class="badge bg-warning text-dark rounded-pill small">Perlu Tindakan</span>
                    <?php endif; ?>
                </div>
                <small class="text-muted">Menunggu persetujuan admin</small>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- FITUR KONFIGURASI AI CHATBOT (GOOGLE GEMINI API & MODEL PICKER)           -->
    <!-- ========================================================================= -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4" id="konfigurasi-ai">
        <div class="card-header bg-white border-bottom p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-cpu-fill text-primary me-2"></i>Konfigurasi AI Chatbot (Google Gemini API)
                </h5>
                <span class="text-secondary small">Edit API Key dan pilih model AI yang aktif untuk asisten Fairy & Jarvis</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($aiConfig['is_configured'])): ?>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill small" id="badgeAiStatus">
                        <i class="bi bi-check-circle-fill me-1"></i> Aktif: <strong id="lblActiveModel"><?= htmlspecialchars($aiConfig['model']); ?></strong>
                    </span>
                <?php else: ?>
                    <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50 px-3 py-2 rounded-pill small" id="badgeAiStatus">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Belum Dikonfigurasi
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Alert Hasil Aksi AJAX -->
            <div id="aiConfigAlert" class="alert d-none rounded-3 py-2 px-3 small mb-3" role="alert"></div>

            <form action="<?= url('dashboard/simpan_ai_config'); ?>" method="POST" id="formAiConfig">
                <?= AuthHelper::csrfField(); ?>
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <!-- 1. Input API Key -->
                        <div class="mb-3">
                            <label for="gemini_api_key" class="form-label small fw-bold text-secondary mb-1">
                                <i class="bi bi-key-fill text-primary me-1"></i> Google Gemini API Key
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control rounded-start-3 font-monospace" 
                                       id="gemini_api_key" 
                                       name="gemini_api_key" 
                                       placeholder="Masukkan Google Gemini API Key..." 
                                       value="<?= htmlspecialchars($aiConfig['api_key']); ?>" 
                                       required 
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="btnToggleApiKey" title="Tampilkan / Sembunyikan API Key">
                                    <i class="bi bi-eye" id="iconToggleApiKey"></i>
                                </button>
                                <button class="btn btn-outline-primary fw-semibold px-3" type="button" id="btnFetchModels">
                                    <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerFetchModels" role="status"></span>
                                    <i class="bi bi-arrow-repeat me-1" id="iconFetchModels"></i> Cek & Muat Model
                                </button>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-1">
                                <small class="text-muted">
                                    <i class="bi bi-shield-check text-success me-1"></i> Kunci disimpan aman di file <code>.env</code> server.
                                </small>
                                <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" class="small text-decoration-none fw-semibold">
                                    Dapatkan API Key di Google AI Studio <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                        <!-- 2. Pilihan Model (Primary & Fallback) -->
                        <div class="row g-3 mb-3">
                            <!-- Model Utama -->
                            <div class="col-md-6">
                                <label for="gemini_model" class="form-label small fw-bold text-secondary mb-1">
                                    <i class="bi bi-stars text-primary me-1"></i> Model Utama (Primary Model)
                                </label>
                                <select class="form-select rounded-3 font-monospace" id="gemini_model" name="gemini_model" required>
                                    <?php 
                                    $commonModels = [
                                        'gemini-2.5-flash' => 'gemini-2.5-flash (Cepat & Stabil - Rekomendasi)',
                                        'gemini-2.5-flash-lite' => 'gemini-2.5-flash-lite (Hemat Token)',
                                        'gemini-2.5-pro' => 'gemini-2.5-pro (Penalaran Kompleks)',
                                        'gemini-3.5-flash' => 'gemini-3.5-flash (Model Standar)',
                                        'gemini-3.5-flash-lite' => 'gemini-3.5-flash-lite (Ringan & Cepat)',
                                        'gemini-3.1-flash-lite' => 'gemini-3.1-flash-lite',
                                        'gemini-flash-latest' => 'gemini-flash-latest'
                                    ];
                                    if (!array_key_exists($aiConfig['model'], $commonModels)) {
                                        $commonModels[$aiConfig['model']] = $aiConfig['model'];
                                    }
                                    foreach ($commonModels as $mKey => $mLabel): 
                                    ?>
                                        <option value="<?= htmlspecialchars($mKey); ?>" <?= ($mKey === $aiConfig['model']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($mLabel); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small" id="modelHelpText">
                                    Model yang aktif digunakan untuk melayani chat Fairy & Jarvis.
                                </div>
                            </div>

                            <!-- Model Cadangan (Fallback) -->
                            <div class="col-md-6">
                                <label for="gemini_fallback_model" class="form-label small fw-bold text-secondary mb-1">
                                    <i class="bi bi-arrow-left-right text-secondary me-1"></i> Model Cadangan (Fallback)
                                </label>
                                <select class="form-select rounded-3 font-monospace" id="gemini_fallback_model" name="gemini_fallback_model">
                                    <?php 
                                    $fallbackList = $commonModels;
                                    if (!array_key_exists($aiConfig['fallback_model'], $fallbackList)) {
                                        $fallbackList[$aiConfig['fallback_model']] = $aiConfig['fallback_model'];
                                    }
                                    foreach ($fallbackList as $fKey => $fLabel): 
                                    ?>
                                        <option value="<?= htmlspecialchars($fKey); ?>" <?= ($fKey === $aiConfig['fallback_model']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($fLabel); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small">
                                    Otomatis dipakai jika model utama mengalami batas kuota (429) atau lonjakan beban (503).
                                </div>
                            </div>
                        </div>

                        <!-- Info Box Model Tersedia -->
                        <div id="modelsInfoBox" class="p-2 px-3 bg-light rounded-3 border small mb-3 d-none">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            <span class="text-primary fw-bold" id="modelsCountText">0</span> model yang didukung berhasil dimuat dari Google Gemini API. Silakan pilih model yang diinginkan di atas.
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button type="button" class="btn btn-outline-info rounded-pill px-4 py-2 fw-semibold" id="btnTestAi">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerTestAi" role="status"></span>
                                <i class="bi bi-lightning-charge-fill me-1" id="iconTestAi"></i> Uji Koneksi AI
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" id="btnSaveAi">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerSaveAi" role="status"></span>
                                <i class="bi bi-save-fill me-1" id="iconSaveAi"></i> Simpan Konfigurasi
                            </button>
                        </div>
                    </div>

                    <!-- Kolom Informasi Persona AI -->
                    <div class="col-lg-4">
                        <div class="bg-light p-3 p-md-4 rounded-4 border h-100">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>Informasi Persona Chatbot
                            </h6>
                            
                            <div class="p-3 bg-white rounded-3 border mb-3 shadow-sm">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fs-4">🧚</span>
                                    <div>
                                        <strong class="text-dark d-block">Fairy</strong>
                                        <small class="text-muted">Untuk Tamu & Calon Penghuni</small>
                                    </div>
                                </div>
                                <p class="small text-secondary mb-0">
                                    Menjawab ketersediaan kamar, tarif sewa, dan status booking pribadi secara ringkas & to-the-point tanpa kata tambahan.
                                </p>
                            </div>

                            <div class="p-3 bg-white rounded-3 border mb-3 shadow-sm">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fs-4">🤖</span>
                                    <div>
                                        <strong class="text-dark d-block">Jarvis</strong>
                                        <small class="text-muted">Untuk Administrator</small>
                                    </div>
                                </div>
                                <p class="small text-secondary mb-0">
                                    Menyajikan analitik kamar, pesanan menunggu konfirmasi, dan tren operasional secara lugas dan tajam.
                                </p>
                            </div>

                            <div class="small text-muted border-top pt-2">
                                <i class="bi bi-lightbulb-fill text-warning me-1"></i>
                                <strong>Tips:</strong> Klik tombol <em>"Cek & Muat Model"</em> untuk menarik daftar model resmi yang aktif pada API key Anda.
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TABEL 1: "Booking Terbaru"                                                -->
    <!-- Kolom: User | Kamar | Tanggal Booking | Total | Status | Action           -->
    <!-- ========================================================================= -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>Booking Terbaru
                </h5>
                <span class="text-secondary small">Daftar reservasi sewa kamar terbaru yang masuk ke sistem</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                    <?= count($recentBookings); ?> Pesanan Terbaru
                </span>
                <a href="<?= url('booking/admin'); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    Lihat Semua Booking <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Kamar</th>
                        <th>Tanggal Booking</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentBookings)): ?>
                        <?php foreach ($recentBookings as $b): 
                            $bStatus = $b['status'];
                            $bBadge = [
                                'menunggu'     => 'bg-warning text-dark',
                                'dikonfirmasi' => 'bg-success text-white',
                                'ditolak'      => 'bg-danger text-white',
                                'selesai'      => 'bg-secondary text-white'
                            ][$bStatus] ?? 'bg-secondary text-white';

                            $bIcon = [
                                'menunggu'     => 'bi-hourglass-split',
                                'dikonfirmasi' => 'bi-check-circle-fill',
                                'ditolak'      => 'bi-x-circle-fill',
                                'selesai'      => 'bi-clock-history'
                            ][$bStatus] ?? 'bi-circle';
                        ?>
                            <tr>
                                <!-- 1. User -->
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-primary bg-opacity-10 text-primary fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                            <?= strtoupper(substr($b['user_nama'] ?? 'U', 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong class="text-dark d-block"><?= htmlspecialchars($b['user_nama'] ?? ''); ?></strong>
                                            <small class="text-muted"><?= htmlspecialchars($b['user_email'] ?? ''); ?></small>
                                        </div>
                                    </div>
                                </td>

                                <!-- 2. Kamar -->
                                <td>
                                    <strong class="text-dark">Kamar <?= htmlspecialchars($b['nomor_kamar']); ?></strong>
                                    <small class="text-muted d-block">ID #BK-00<?= $b['id']; ?></small>
                                </td>

                                <!-- 3. Tanggal Booking -->
                                <td>
                                    <span class="text-dark">
                                        <?= !empty($b['tanggal_booking']) ? date('d M Y, H:i', strtotime($b['tanggal_booking'])) : '-'; ?> WIB
                                    </span>
                                    <small class="text-muted d-block">Mulai: <?= date('d M Y', strtotime($b['tanggal_mulai'])); ?> (<?= (int)$b['lama_sewa']; ?> bln)</small>
                                </td>

                                <!-- 4. Total -->
                                <td>
                                    <strong class="text-primary"><?= formatRupiah($b['total_harga']); ?></strong>
                                </td>

                                <!-- 5. Status -->
                                <td>
                                    <span class="badge <?= $bBadge; ?> rounded-pill px-3 py-1 small fw-semibold d-inline-flex align-items-center gap-1">
                                        <i class="bi <?= $bIcon; ?> small"></i> <?= ucfirst($bStatus); ?>
                                    </span>
                                </td>

                                <!-- 6. Action -->
                                <td class="text-end pe-4 text-nowrap">
                                    <a href="<?= url('booking/detail/' . $b['id']); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 me-1" title="Lihat Detail Booking">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>

                                    <?php if ($bStatus === 'menunggu'): ?>
                                        <!-- Tombol Konfirmasi dengan Confirmation Dialog Modal -->
                                        <button type="button" 
                                                class="btn btn-success btn-sm rounded-pill px-3 py-1 me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDashConfirm<?= $b['id']; ?>">
                                            <i class="bi bi-check-lg me-1"></i> Konfirmasi
                                        </button>

                                        <!-- Tombol Tolak dengan Confirmation Dialog Modal -->
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDashReject<?= $b['id']; ?>">
                                            <i class="bi bi-x-lg me-1"></i> Tolak
                                        </button>

                                        <!-- Modal Dialog Konfirmasi -->
                                        <div class="modal fade" id="modalDashConfirm<?= $b['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content border-0 shadow rounded-4">
                                                    <div class="modal-header bg-success bg-opacity-10 border-0">
                                                        <h5 class="modal-title fw-bold text-success">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi Booking #BK-00<?= $b['id']; ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="text-dark mb-2">Konfirmasi pesanan sewa kamar <strong><?= htmlspecialchars($b['nomor_kamar']); ?></strong> untuk <strong><?= htmlspecialchars($b['user_nama']); ?></strong>?</p>
                                                        <div class="alert alert-info border-0 rounded-3 small mb-0">
                                                            <strong>Perubahan Status Otomatis:</strong><br>
                                                            &bull; Status booking &rarr; <strong>Dikonfirmasi</strong><br>
                                                            &bull; Status kamar &rarr; <strong>Terisi</strong>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-3 pt-0">
                                                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                        <a href="<?= url('booking/konfirmasi_admin/' . $b['id']); ?>" class="btn btn-success rounded-pill px-4 fw-semibold">
                                                            Ya, Konfirmasi
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Dialog Tolak -->
                                        <div class="modal fade" id="modalDashReject<?= $b['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content border-0 shadow rounded-4">
                                                    <div class="modal-header bg-danger bg-opacity-10 border-0">
                                                        <h5 class="modal-title fw-bold text-danger">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Tolak Booking #BK-00<?= $b['id']; ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="text-dark mb-2">Apakah Anda yakin ingin menolak pesanan booking <strong>Kamar <?= htmlspecialchars($b['nomor_kamar']); ?></strong> atas nama <strong><?= htmlspecialchars($b['user_nama']); ?></strong>?</p>
                                                        <div class="alert alert-warning border-0 rounded-3 small mb-0">
                                                            <strong>Perubahan Status Otomatis:</strong><br>
                                                            &bull; Status booking &rarr; <strong>Ditolak</strong><br>
                                                            &bull; Status kamar &rarr; <strong>Tersedia</strong> (dapat dipesan kembali)
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-3 pt-0">
                                                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                        <a href="<?= url('booking/tolak_admin/' . $b['id']); ?>" class="btn btn-danger rounded-pill px-4 fw-semibold">
                                                            Ya, Tolak
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border px-2 py-1 small">Selesai Ditindak</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                Belum ada data reservasi booking kamar saat ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TABEL 2: "Kamar Terbaru"                                                  -->
    <!-- Kolom: Kamar | Harga / Bulan | Fasilitas | Status | Action                -->
    <!-- ========================================================================= -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-door-open-fill text-primary me-2"></i>Kamar Terbaru
                </h5>
                <span class="text-secondary small">Daftar unit kamar kos yang baru didaftarkan atau diperbarui</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                    <?= count($recentRooms); ?> Kamar Terbaru
                </span>
                <a href="<?= url('kamar/tambah'); ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Kamar
                </a>
                <a href="<?= url('kamar/admin'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    Lihat Semua Kamar <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">Kamar</th>
                        <th>Harga / Bulan</th>
                        <th>Fasilitas</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentRooms)): ?>
                        <?php foreach ($recentRooms as $r): 
                            $kBadge = [
                                'tersedia' => 'bg-success text-white',
                                'booking'  => 'bg-warning text-dark',
                                'terisi'   => 'bg-secondary text-white'
                            ][$r['status']] ?? 'bg-primary';

                            $photoSrc = !empty($r['foto']) && file_exists(__DIR__ . '/../../public/images/kamar/' . $r['foto'])
                                ? BASE_URL . '/images/kamar/' . htmlspecialchars($r['foto'])
                                : (file_exists(__DIR__ . '/../../public/images/' . ($r['foto'] ?? ''))
                                    ? BASE_URL . '/images/' . htmlspecialchars($r['foto'])
                                    : BASE_URL . '/images/default_kamar.jpg');
                        ?>
                            <tr>
                                <!-- 1. Kamar (Foto & Nomor) -->
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= $photoSrc; ?>" 
                                             alt="<?= htmlspecialchars($r['nomor_kamar']); ?>" 
                                             class="rounded-3 object-fit-cover shadow-sm" 
                                             style="width: 52px; height: 52px;"
                                             onerror="this.src='<?= BASE_URL; ?>/images/default_kamar.jpg'">
                                        <div>
                                            <strong class="text-dark d-block">Kamar <?= htmlspecialchars($r['nomor_kamar']); ?></strong>
                                            <small class="text-muted">Unit ID #<?= (int)$r['id']; ?></small>
                                        </div>
                                    </div>
                                </td>

                                <!-- 2. Harga / Bulan -->
                                <td>
                                    <span class="text-primary fw-bold"><?= formatRupiah($r['harga']); ?></span>
                                    <small class="text-muted d-block">per bulan</small>
                                </td>

                                <!-- 3. Fasilitas -->
                                <td>
                                    <?php 
                                    $fasArr = is_array($r['fasilitas']) 
                                        ? $r['fasilitas'] 
                                        : array_map('trim', explode(',', $r['fasilitas'] ?? ''));
                                    $fasClean = array_filter($fasArr);
                                    if (!empty($fasClean)):
                                        foreach (array_slice($fasClean, 0, 3) as $fasItem):
                                    ?>
                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small me-1">
                                            <?= htmlspecialchars($fasItem); ?>
                                        </span>
                                    <?php 
                                        endforeach;
                                        if (count($fasClean) > 3): 
                                    ?>
                                        <span class="text-muted small">+<?= count($fasClean) - 3; ?> lagi</span>
                                    <?php 
                                        endif;
                                    else: 
                                    ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- 4. Status -->
                                <td>
                                    <span class="badge <?= $kBadge; ?> rounded-pill px-3 py-1 small fw-semibold">
                                        <?= ucfirst($r['status']); ?>
                                    </span>
                                </td>

                                <!-- 5. Action -->
                                <td class="text-end pe-4 text-nowrap">
                                    <a href="<?= url('kamar/detail/' . $r['id']); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1 me-1" title="Lihat Detail">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <a href="<?= url('kamar/edit/' . $r['id']); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1 me-1" title="Edit Kamar">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="<?= url('kamar/hapus/' . $r['id']); ?>" 
                                       class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus kamar <?= htmlspecialchars(addslashes($r['nomor_kamar'])); ?>?');" 
                                       title="Hapus Kamar">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-secondary">
                                <i class="bi bi-door-closed fs-2 d-block mb-2 text-muted"></i>
                                Belum ada unit kamar yang didaftarkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TABEL 3: "Data User"                                                      -->
    <!-- Menampilkan daftar pengguna terdaftar untuk pengawasan Admin              -->
    <!-- ========================================================================= -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-people-fill text-primary me-2"></i>Data User
                </h5>
                <span class="text-secondary small">Daftar seluruh akun pengguna yang terdaftar di sistem KosBook</span>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                Total: <?= count($users); ?> Akun
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">ID User</th>
                        <th>Nama Lengkap</th>
                        <th>Alamat Email</th>
                        <th>Role / Peran</th>
                        <th>Tanggal Terdaftar</th>
                        <th class="text-end pe-4">Status Akun</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-secondary">#U-00<?= (int)$u['id']; ?></td>
                                <td>
                                    <strong class="text-dark d-block">
                                        <i class="bi <?= $u['role'] === 'admin' ? 'bi-shield-check text-danger' : 'bi-person text-primary'; ?> me-1"></i>
                                        <?= htmlspecialchars($u['nama']); ?>
                                    </strong>
                                </td>
                                <td><span class="text-secondary"><?= htmlspecialchars($u['email']); ?></span></td>
                                <td>
                                    <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?> rounded-pill px-3 py-1 small">
                                        <?= ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td><span class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])); ?></span></td>
                                <td class="text-end pe-4">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 small">
                                        <i class="bi bi-check-circle me-1"></i> Aktif
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">Belum ada akun user terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const apiKeyInput = document.getElementById('gemini_api_key');
    const btnToggleApiKey = document.getElementById('btnToggleApiKey');
    const iconToggleApiKey = document.getElementById('iconToggleApiKey');
    const btnFetchModels = document.getElementById('btnFetchModels');
    const iconFetchModels = document.getElementById('iconFetchModels');
    const spinnerFetchModels = document.getElementById('spinnerFetchModels');
    const btnTestAi = document.getElementById('btnTestAi');
    const iconTestAi = document.getElementById('iconTestAi');
    const spinnerTestAi = document.getElementById('spinnerTestAi');
    const modelSelect = document.getElementById('gemini_model');
    const fallbackSelect = document.getElementById('gemini_fallback_model');
    const modelsInfoBox = document.getElementById('modelsInfoBox');
    const modelsCountText = document.getElementById('modelsCountText');
    const alertBox = document.getElementById('aiConfigAlert');

    function showAlert(type, message) {
        if (!alertBox) return;
        alertBox.className = `alert alert-${type} rounded-3 py-2 px-3 small mb-3`;
        alertBox.innerHTML = message;
        alertBox.classList.remove('d-none');
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideAlert() {
        if (!alertBox) return;
        alertBox.classList.add('d-none');
        alertBox.innerHTML = '';
    }

    // 1. Toggle Show/Hide API Key
    if (btnToggleApiKey && apiKeyInput) {
        btnToggleApiKey.addEventListener('click', function () {
            if (apiKeyInput.type === 'password') {
                apiKeyInput.type = 'text';
                iconToggleApiKey.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                apiKeyInput.type = 'password';
                iconToggleApiKey.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    }

    // 2. Fetch Available Models from Gemini API
    if (btnFetchModels) {
        btnFetchModels.addEventListener('click', function () {
            const keyVal = (apiKeyInput ? apiKeyInput.value.trim() : '');
            if (!keyVal) {
                showAlert('warning', '<i class="bi bi-exclamation-triangle-fill me-1"></i> Silakan masukkan API Key terlebih dahulu sebelum memuat model.');
                if (apiKeyInput) apiKeyInput.focus();
                return;
            }

            hideAlert();
            btnFetchModels.disabled = true;
            spinnerFetchModels.classList.remove('d-none');
            iconFetchModels.classList.add('d-none');

            const formData = new FormData();
            formData.append('api_key', keyVal);

            fetch('<?= url("dashboard/get_ai_models"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnFetchModels.disabled = false;
                spinnerFetchModels.classList.add('d-none');
                iconFetchModels.classList.remove('d-none');

                if (data.success && Array.isArray(data.models) && data.models.length > 0) {
                    const currentSelected = modelSelect.value;
                    const currentFallback = fallbackSelect.value;

                    // Bersihkan dan bangun kembali dropdown model
                    modelSelect.innerHTML = '';
                    fallbackSelect.innerHTML = '';

                    data.models.forEach(m => {
                        const recBadge = m.is_recommended ? ' ⭐ [Rekomendasi]' : '';
                        const optText = `${m.id}${recBadge} - ${m.displayName}`;

                        const opt1 = new Option(optText, m.id);
                        if (m.id === currentSelected) opt1.selected = true;
                        modelSelect.add(opt1);

                        const opt2 = new Option(optText, m.id);
                        if (m.id === currentFallback) opt2.selected = true;
                        fallbackSelect.add(opt2);
                    });

                    // Tampilkan info box
                    if (modelsInfoBox && modelsCountText) {
                        modelsCountText.innerText = data.count;
                        modelsInfoBox.classList.remove('d-none');
                    }

                    showAlert('success', `<i class="bi bi-check-circle-fill me-1"></i> Berhasil memuat <strong>${data.count} model</strong> yang kompatibel dari Google Gemini API! Silakan pilih model yang diinginkan lalu klik <em>Simpan Konfigurasi</em>.`);
                } else {
                    showAlert('danger', `<i class="bi bi-x-circle-fill me-1"></i> Gagal memuat model: ${data.error || 'Respons tidak valid dari Google API.'}`);
                }
            })
            .catch(err => {
                btnFetchModels.disabled = false;
                spinnerFetchModels.classList.add('d-none');
                iconFetchModels.classList.remove('d-none');
                showAlert('danger', `<i class="bi bi-x-circle-fill me-1"></i> Terjadi kesalahan jaringan saat memuat model: ${err.message}`);
            });
        });
    }

    // 3. Test AI Connection
    if (btnTestAi) {
        btnTestAi.addEventListener('click', function () {
            const keyVal = (apiKeyInput ? apiKeyInput.value.trim() : '');
            const modelVal = (modelSelect ? modelSelect.value.trim() : '');
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            const csrfToken = csrfInput ? csrfInput.value : '';

            if (!keyVal) {
                showAlert('warning', '<i class="bi bi-exclamation-triangle-fill me-1"></i> Masukkan API Key terlebih dahulu untuk pengujian.');
                return;
            }
            if (!modelVal) {
                showAlert('warning', '<i class="bi bi-exclamation-triangle-fill me-1"></i> Pilih model yang akan diuji.');
                return;
            }

            hideAlert();
            btnTestAi.disabled = true;
            spinnerTestAi.classList.remove('d-none');
            iconTestAi.classList.add('d-none');

            const formData = new FormData();
            formData.append('api_key', keyVal);
            formData.append('model', modelVal);
            formData.append('csrf_token', csrfToken);

            fetch('<?= url("dashboard/test_ai_connection"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnTestAi.disabled = false;
                spinnerTestAi.classList.add('d-none');
                iconTestAi.classList.remove('d-none');

                if (data.success) {
                    showAlert('success', `<i class="bi bi-check-circle-fill me-1"></i> <strong>Uji Koneksi Berhasil!</strong> Model <code>${data.model}</code> aktif dan merespons normal: <em>"${data.reply}"</em>.`);
                } else {
                    showAlert('danger', `<i class="bi bi-x-circle-fill me-1"></i> <strong>Uji Koneksi Gagal:</strong> ${data.error || 'Tidak ada respons dari model.'}`);
                }
            })
            .catch(err => {
                btnTestAi.disabled = false;
                spinnerTestAi.classList.add('d-none');
                iconTestAi.classList.remove('d-none');
                showAlert('danger', `<i class="bi bi-x-circle-fill me-1"></i> Terjadi kendala teknis saat uji koneksi: ${err.message}`);
            });
        });
    }
});
</script>
