/**
 * KosBook AI Chatbot Client (Fairy & Jarvis)
 * Vanilla JavaScript untuk interaksi tanpa reload halaman.
 * Keamanan:
 * - Tidak menyimpan atau mengirim API key
 * - Sanitasi output teks untuk mencegah XSS
 * - Proteksi pengiriman ganda (disable button)
 */

(function () {
    'use strict';

    // 1. Elemen DOM
    const launcher = document.getElementById('kbChatbotLauncher');
    const popup = document.getElementById('kbChatbotPopup');
    const closeBtn = document.getElementById('kbChatClose');
    const clearBtn = document.getElementById('kbChatClear');
    const chatBody = document.getElementById('kbChatBody');
    const chatInput = document.getElementById('kbChatInput');
    const sendBtn = document.getElementById('kbChatSend');
    const typingIndicator = document.getElementById('kbTypingIndicator');

    if (!launcher || !popup || !chatInput || !sendBtn) {
        return; // Halaman tidak memuat komponen chatbot
    }

    // 2. Konfigurasi Endpoint dari Data Attribute
    const messageEndpoint = popup.getAttribute('data-endpoint-message') || 'index.php?url=chatbot/message';
    const historyEndpoint = popup.getAttribute('data-endpoint-history') || 'index.php?url=chatbot/history';
    const clearEndpoint = popup.getAttribute('data-endpoint-clear') || 'index.php?url=chatbot/clear';
    const csrfToken = popup.getAttribute('data-csrf') || '';
    const botName = popup.getAttribute('data-bot') || 'Fairy';
    const botAvatar = popup.getAttribute('data-avatar') || '🧚';

    let isSending = false;
    let historyLoaded = false;

    async function loadHistory() {
        if (historyLoaded) return;
        historyLoaded = true;
        try {
            const res = await fetch(historyEndpoint);
            if (res.ok) {
                const data = await res.json();
                if (data.success && Array.isArray(data.history)) {
                    data.history.forEach(item => {
                        appendMessage(item.role === 'user' ? 'user' : 'bot', item.text);
                    });
                }
            }
        } catch (e) {
            console.error('Gagal memuat riwayat chat:', e);
        }
    }

    // 3. Toggle Pop-up Buka / Tutup
    function togglePopup(open) {
        const shouldOpen = (open !== undefined) ? open : !popup.classList.contains('is-open');
        if (shouldOpen) {
            popup.classList.add('is-open');
            popup.setAttribute('aria-hidden', 'false');
            loadHistory();
            chatInput.focus();
            scrollToBottom();
        } else {
            popup.classList.remove('is-open');
            popup.setAttribute('aria-hidden', 'true');
        }
    }

    launcher.addEventListener('click', () => togglePopup());
    closeBtn.addEventListener('click', () => togglePopup(false));

    // Tutup dengan tombol Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && popup.classList.contains('is-open')) {
            togglePopup(false);
        }
    });

    // 4. Auto-scroll ke pesan terbawah
    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    // 5. Auto-resize input textarea
    function autoResizeInput() {
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, 100) + 'px';
        validateSendButton();
    }

    function validateSendButton() {
        const text = chatInput.value.trim();
        sendBtn.disabled = (text === '' || isSending);
    }

    chatInput.addEventListener('input', autoResizeInput);

    // 6. Dukungan Enter untuk kirim, Shift+Enter untuk baris baru
    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!sendBtn.disabled) {
                sendMessage();
            }
        }
    });

    sendBtn.addEventListener('click', () => {
        if (!sendBtn.disabled) {
            sendMessage();
        }
    });

    // 7. Sanitasi & Markdown Rendering Aman (Mencegah XSS)
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatMarkdown(text) {
        if (!text) return '';
        // 1. Escape HTML terlebih dahulu
        let safe = escapeHtml(text);

        // 2. Bold: **teks**
        safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // 3. Italic: *teks*
        safe = safe.replace(/\*(.*?)\*/g, '<em>$1</em>');

        // 4. Inline code: `teks`
        safe = safe.replace(/`([^`]+)`/g, '<code class="bg-light px-1 py-0.5 rounded border">$1</code>');

        // 5. Link format: [text](url) -> sanitasi hanya http, https, atau internal
        safe = safe.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+|\/?index\.php[^\s)]*)\)/g, function(match, label, href) {
            return `<a href="${href}" target="_blank" rel="noopener noreferrer">${label}</a>`;
        });

        // 6. Unordered lists: - item atau * item
        safe = safe.replace(/(?:^|\n)[-*]\s+(.+)/g, '<li>$1</li>');
        safe = safe.replace(/(<li>.*?<\/li>)+/gs, '<ul class="mb-2">$0</ul>');

        // 7. Line breaks
        safe = safe.replace(/\n/g, '<br>');

        return safe;
    }

    function getCurrentTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    // 8. Menambahkan Bubble Pesan ke Chat Body
    function appendMessage(sender, text, isError = false) {
        const row = document.createElement('div');
        row.className = `kb-msg-row ${sender === 'user' ? 'kb-msg-user' : 'kb-msg-bot'}`;

        const avatar = document.createElement('div');
        avatar.className = 'kb-msg-avatar';
        avatar.innerHTML = sender === 'user' ? '<i class="bi bi-person-fill"></i>' : botAvatar;

        const bubble = document.createElement('div');
        bubble.className = 'kb-msg-bubble';

        if (isError) {
            bubble.classList.add('kb-chat-error');
            bubble.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i> ${escapeHtml(text)}`;
        } else if (sender === 'user') {
            bubble.textContent = text;
        } else {
            bubble.innerHTML = formatMarkdown(text);
        }

        const time = document.createElement('div');
        time.className = 'kb-msg-time';
        time.textContent = getCurrentTime();
        bubble.appendChild(time);

        row.appendChild(avatar);
        row.appendChild(bubble);

        // Sisipkan sebelum typing indicator
        chatBody.insertBefore(row, typingIndicator);
        scrollToBottom();
        return row;
    }

    // 9. Kirim Pesan ke Backend PHP
    async function sendMessage(customText) {
        const text = (customText !== undefined) ? customText.trim() : chatInput.value.trim();
        if (text === '' || isSending) return;

        // Reset input
        chatInput.value = '';
        chatInput.style.height = 'auto';
        isSending = true;
        validateSendButton();

        // Tampilkan pesan pengguna
        appendMessage('user', text);

        // Tampilkan typing indicator
        typingIndicator.classList.remove('d-none');
        scrollToBottom();

        try {
            const response = await fetch(messageEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    message: text,
                    csrf_token: csrfToken
                })
            });

            const data = await response.json();

            // Sembunyikan typing indicator
            typingIndicator.classList.add('d-none');

            if (response.ok && data.success) {
                appendMessage('bot', data.reply);
            } else {
                const errorMsg = data.error || 'Maaf, terjadi kesalahan pada koneksi asisten AI. Silakan coba kembali.';
                appendMessage('bot', errorMsg, true);
            }
        } catch (err) {
            typingIndicator.classList.add('d-none');
            appendMessage('bot', 'Gagal menghubungi server. Mohon periksa koneksi internet Anda.', true);
        } finally {
            isSending = false;
            validateSendButton();
            chatInput.focus();
        }
    }

    // 10. Handler Quick Prompt Chips
    document.addEventListener('click', (e) => {
        const chip = e.target.closest('.kb-prompt-chip');
        if (chip) {
            const promptText = chip.getAttribute('data-prompt');
            if (promptText && !isSending) {
                sendMessage(promptText);
            }
        }
    });

    // 11. Handler Bersihkan Chat (Clear History)
    if (clearBtn) {
        clearBtn.addEventListener('click', async () => {
            if (!confirm(`Bersihkan riwayat percakapan dengan ${botName}?`)) {
                return;
            }

            try {
                const response = await fetch(clearEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: `csrf_token=${encodeURIComponent(csrfToken)}`
                });

                if (response.ok) {
                    // Hapus semua pesan kecuali salam awal (pesan pertama) dan typing indicator
                    const rows = chatBody.querySelectorAll('.kb-msg-row:not(:first-child)');
                    rows.forEach(r => r.remove());
                }
            } catch (err) {
                console.error('Gagal membersihkan riwayat chat:', err);
            }
        });
    }

    // Initial validation
    validateSendButton();
})();
