<?php
/**
 * Komponen Reusable: Chatbot AI Floating Widget (Fairy & Jarvis)
 * Terintegrasi secara kondisional berdasarkan role autentikasi server.
 */

$currentRole = AuthHelper::role();
$isAdmin = ($currentRole === 'admin');
$csrfToken = AuthHelper::csrfToken();

if ($isAdmin) {
    $botName = 'Jarvis';
    $botAvatar = '🤖';
    $botSubtitle = 'AI Operasional Admin';
    $launcherClass = 'kb-launcher-jarvis';
    $headerClass = 'kb-header-jarvis';
    $sendClass = 'kb-send-jarvis';
    $launcherLabel = 'Tanya Jarvis 🤖';
    $initialGreeting = 'Selamat datang, Admin. Saya Jarvis 🤖. Saya siap membantu menganalisis data dan operasional KosBook.';
    $quickPrompts = [
        'Berapa jumlah kamar yang tersedia sekarang?',
        'Tampilkan booking yang menunggu konfirmasi.',
        'Berapa booking yang dikonfirmasi bulan ini?',
        'Buat ringkasan aktivitas booking minggu ini.',
        'Berapa total nilai booking yang tercatat bulan ini?',
        'Bandingkan jumlah booking bulan ini dengan bulan sebelumnya.'
    ];
} else {
    $botName = 'Fairy';
    $botAvatar = '🧚';
    $botSubtitle = 'Asisten Virtual KosBook';
    $launcherClass = 'kb-launcher-fairy';
    $headerClass = 'kb-header-fairy';
    $sendClass = 'kb-send-fairy';
    $launcherLabel = 'Tanya Fairy 🧚';
    $initialGreeting = 'Hai! Aku Fairy 🧚 Ada yang bisa aku bantu tentang kamar kos atau booking kamu?';
    $quickPrompts = [
        'Kamar apa yang masih tersedia?',
        'Bagaimana cara booking kamar?',
        'Bagaimana cara mengecek status booking saya?',
        'Berapa tarif sewa kamar termurah?',
        'Fasilitas apa saja yang ada di Kamar VIP?'
    ];
}

// Ambil riwayat chat sesi aktif jika ada
require_once __DIR__ . '/../../services/ChatbotService.php';
$chatHistory = ChatbotService::getSessionHistory($botName);
?>

<!-- 1. Floating Action Launcher Button -->
<div class="kb-chatbot-launcher <?= $launcherClass; ?>" id="kbChatbotLauncher" title="Buka Percakapan dengan <?= $botName; ?>">
    <div class="kb-launcher-label shadow-sm">
        <span><?= htmlspecialchars($launcherLabel); ?></span>
    </div>
    <button type="button" class="kb-launcher-btn" aria-label="Buka Chat AI <?= $botName; ?>">
        <div class="kb-launcher-pulse"></div>
        <span><?= $botAvatar; ?></span>
    </button>
</div>

<!-- 2. Chatbot Pop-up Window -->
<div class="kb-chatbot-popup" 
     id="kbChatbotPopup" 
     role="dialog" 
     aria-modal="true" 
     aria-hidden="true"
     data-endpoint-message="<?= url('chatbot/message'); ?>"
     data-endpoint-history="<?= url('chatbot/history'); ?>"
     data-endpoint-clear="<?= url('chatbot/clear'); ?>"
     data-csrf="<?= htmlspecialchars($csrfToken); ?>"
     data-bot="<?= $botName; ?>"
     data-avatar="<?= $botAvatar; ?>">

    <!-- Header -->
    <div class="kb-chat-header <?= $headerClass; ?>">
        <div class="kb-bot-info">
            <div class="kb-bot-avatar">
                <?= $botAvatar; ?>
            </div>
            <div>
                <h6 class="kb-bot-title"><?= $botName; ?> AI</h6>
                <div class="kb-bot-subtitle">
                    <span class="kb-status-dot"></span>
                    <span><?= htmlspecialchars($botSubtitle); ?></span>
                </div>
            </div>
        </div>
        <div class="kb-header-actions">
            <button type="button" class="kb-btn-header" id="kbChatClear" title="Bersihkan Percakapan" aria-label="Bersihkan Chat">
                <i class="bi bi-trash3"></i>
            </button>
            <button type="button" class="kb-btn-header" id="kbChatClose" title="Tutup Chat" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Messages Body -->
    <div class="kb-chat-body" id="kbChatBody">
        <!-- Pesan Salam Pembuka -->
        <div class="kb-msg-row kb-msg-bot">
            <div class="kb-msg-avatar"><?= $botAvatar; ?></div>
            <div class="kb-msg-bubble">
                <?= htmlspecialchars($initialGreeting); ?>
                
                <!-- Quick Prompt Chips -->
                <div class="kb-prompt-suggestions">
                    <?php foreach ($quickPrompts as $prompt): ?>
                        <button type="button" class="kb-prompt-chip" data-prompt="<?= htmlspecialchars($prompt); ?>">
                            <i class="bi bi-chat-quote-fill opacity-75"></i>
                            <?= htmlspecialchars($prompt); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="kb-msg-time"><?= date('H:i'); ?></div>
            </div>
        </div>

        <!-- Typing Indicator (Tersembunyi secara default) -->
        <div class="kb-typing-indicator d-none" id="kbTypingIndicator" aria-live="polite">
            <div class="kb-typing-dot"></div>
            <div class="kb-typing-dot"></div>
            <div class="kb-typing-dot"></div>
        </div>
    </div>

    <!-- Input Footer -->
    <div class="kb-chat-footer">
        <div class="kb-input-group">
            <textarea class="kb-chat-input" 
                      id="kbChatInput" 
                      rows="1" 
                      placeholder="Ketik pesan untuk <?= $botName; ?>..." 
                      aria-label="Pesan untuk Chatbot"></textarea>
            <button type="button" class="kb-chat-send <?= $sendClass; ?>" id="kbChatSend" title="Kirim Pesan" disabled>
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
        <p class="kb-input-hint">
            <i class="bi bi-info-circle me-1"></i>Tekan Enter untuk kirim, Shift+Enter untuk baris baru.
        </p>
    </div>
</div>
