<?php
// Widget chat mengambang customer (Revisi #7)
// Variabel wajib: $chat_token, $chat_user_id. $chat_conv_id opsional.
// Konteks produk ala Shopee: halaman detail memanggil window.setChatProduct({...}).
if (empty($chat_user_id)) { return; } // tamu tidak melihat widget
?>
<button type="button" id="chatFab" class="btn btn-dark rounded-circle shadow-lg"
        style="position: fixed; right: 24px; bottom: 24px; width: 56px; height: 56px; z-index: 1050; display: flex; align-items: center; justify-content: center;">
    <i class="fas fa-comment-dots fs-4"></i>
</button>

<div id="chatPanel" class="bg-white shadow-lg border" style="position: fixed; right: 24px; bottom: 92px; width: 340px; max-width: calc(100vw - 48px); height: 480px; max-height: 75vh; z-index: 1050; border-radius: 16px; display: none; flex-direction: column; overflow: hidden;">
    <div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-bold ls-1 small">CHAT ADMIN JIDOOR</div>
            <div class="text-white-50" style="font-size: 0.7rem;" id="chatStatus">Menghubungkan...</div>
        </div>
        <button type="button" class="btn btn-link text-white p-0" onclick="toggleChat(false)"><i class="fas fa-times"></i></button>
    </div>

    <div id="chatMessages" class="flex-grow-1 p-3 overflow-auto bg-light" style="font-size: 0.85rem;"></div>

    <!-- Chip pertanyaan cepat ala Shopee (muncul saat konteks produk aktif) -->
    <div id="chatChips" class="d-none px-2 pt-2 bg-white d-flex flex-wrap gap-1" style="border-top: 1px solid rgba(0,0,0,.06);">
        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1" style="font-size:.72rem" data-q="Ready stok?">Ready?</button>
        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1" style="font-size:.72rem" data-q="Bisa custom?">Bisa custom?</button>
        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1" style="font-size:.72rem" data-q="Estimasi kirim berapa hari?">Estimasi kirim?</button>
    </div>

    <!-- Bar konteks produk ala Shopee -->
    <div id="chatContext" class="d-none align-items-center gap-2 px-2 py-2 bg-white" style="border-top: 1px solid rgba(0,0,0,.08);">
        <img id="ctxImg" src="" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:8px;background:#f1f3f5;">
        <div class="flex-grow-1 overflow-hidden">
            <div id="ctxName" class="text-truncate fw-bold" style="font-size:.75rem;"></div>
            <div id="ctxPrice" class="fw-bold" style="font-size:.72rem;color:#ee4d2d;"></div>
        </div>
        <button type="button" id="ctxClose" class="btn btn-link text-muted p-1" title="Lepas konteks produk"><i class="fas fa-times small"></i></button>
    </div>

    <form id="chatForm" class="p-2 border-top d-flex gap-2 bg-white">
        <input type="text" id="chatInput" class="form-control form-control-sm border-0 bg-light rounded-pill px-3" placeholder="Tulis pesan..." maxlength="1000" autocomplete="off">
        <button type="submit" class="btn btn-dark btn-sm rounded-circle px-3"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>

<script>
(function () {
    const CHAT_TOKEN   = <?= json_encode($chat_token) ?>;
    const CHAT_USER_ID = <?= json_encode((int)$chat_user_id) ?>;
    const CHAT_ROLE    = 'user';

    let ws = null;
    let wsOnline = false;
    let historyLoaded = false;
    let chatProduct = null; // konteks produk aktif (ala Shopee)

    const panel    = document.getElementById('chatPanel');
    const fab      = document.getElementById('chatFab');
    const msgBox   = document.getElementById('chatMessages');
    const statusEl = document.getElementById('chatStatus');
    const form     = document.getElementById('chatForm');
    const input    = document.getElementById('chatInput');
    const ctxBar   = document.getElementById('chatContext');
    const chipsRow = document.getElementById('chatChips');

    const fmt = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
    const esc = s => String(s).replace(/[<>&"]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c]));
    const imgUrl = img => img ? '<?= base_url("uploads/products/") ?>' + img
                              : 'https://placehold.co/80x80/f1f3f5/adb5bd?text=%3F';

    function toggleChat(open) {
        const willOpen = (open === undefined) ? panel.style.display !== 'flex' : open;
        panel.style.display = willOpen ? 'flex' : 'none';
        if (willOpen && !historyLoaded) { loadHistory(); }
        if (willOpen) { input.focus(); }
    }
    window.toggleChat = toggleChat;
    fab.addEventListener('click', () => toggleChat());

    // ===== Konteks produk ala Shopee =====
    function renderContext() {
        if (!chatProduct) {
            ctxBar.classList.add('d-none');
            ctxBar.classList.remove('d-flex');
            chipsRow.classList.add('d-none');
            return;
        }
        document.getElementById('ctxImg').src = imgUrl(chatProduct.image);
        document.getElementById('ctxName').textContent = chatProduct.name;
        document.getElementById('ctxPrice').textContent = fmt(chatProduct.price);
        ctxBar.classList.remove('d-none');
        ctxBar.classList.add('d-flex');
        chipsRow.classList.remove('d-none');
    }

    // Dipanggil tombol "Chat Penjual" di halaman detail produk
    window.setChatProduct = function (p) {
        if (!p || !p.id) { return; }
        chatProduct = p;
        renderContext();
        toggleChat(true);
    };

    document.getElementById('ctxClose').addEventListener('click', () => {
        chatProduct = null;
        renderContext();
    });

    chipsRow.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-q]');
        if (btn) { send(btn.dataset.q); }
    });

    // ===== Bubble + kartu produk inline =====
    function productCardHtml(p) {
        return '<a href="<?= base_url("produk/") ?>' + esc(p.slug) + '" target="_blank" rel="noopener" ' +
               'class="d-flex align-items-center gap-2 text-decoration-none p-1 mb-1 rounded-3 bg-light" style="min-width:170px;">' +
               '<img src="' + imgUrl(p.image) + '" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">' +
               '<span class="overflow-hidden">' +
               '<span class="d-block text-truncate fw-bold text-dark" style="font-size:.68rem;max-width:150px;">' + esc(p.name) + '</span>' +
               '<span class="d-block fw-bold" style="font-size:.68rem;color:#ee4d2d;">' + fmt(p.price) + '</span>' +
               '</span></a>';
    }

    function bubble(role, text, time, product) {
        const wrap = document.createElement('div');
        const mine = role === 'user';
        wrap.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');
        wrap.innerHTML =
            '<div class="px-3 py-2 ' + (mine ? 'bg-dark text-white' : 'bg-white border') + '" style="max-width:78%; border-radius:14px;">' +
            (product ? productCardHtml(product) : '') +
            '<div>' + esc(text) + '</div>' +
            '<div class="' + (mine ? 'text-white-50' : 'text-muted') + '" style="font-size:0.65rem; text-align:right;">' + time + '</div>' +
            '</div>';
        msgBox.appendChild(wrap);
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    function loadHistory() {
        fetch('<?= site_url("chat/history") ?>')
            .then(r => r.json())
            .then(res => {
                msgBox.innerHTML = '';
                (res.messages || []).forEach(m => bubble(m.role, m.text, m.sent_at, m.product));
                if (!(res.messages || []).length) {
                    msgBox.innerHTML = '<div class="text-center text-muted small py-5">Belum ada pesan.<br>Sapa admin kami!</div>';
                }
                historyLoaded = true;
            });
    }

    function connect() {
        try { ws = new WebSocket('ws://localhost:8080/chat'); } catch (e) { onOffline(); return; }

        ws.onopen = () => {
            ws.send(JSON.stringify({ type: 'auth', token: CHAT_TOKEN, user_id: CHAT_USER_ID, role: CHAT_ROLE }));
            wsOnline = true;
            statusEl.innerHTML = '<span class="text-success">● Admin online</span>';
        };

        ws.onmessage = (e) => {
            const d = JSON.parse(e.data);
            if (d.type === 'message') { bubble(d.role, d.text, d.sent_at, d.product); }
        };

        ws.onclose = () => { wsOnline = false; onOffline(); setTimeout(connect, 3000); };
        ws.onerror = () => { try { ws.close(); } catch (e) {} };
    }

    function onOffline() {
        statusEl.textContent = 'Admin tidak sedang online — tinggalkan pesan';
    }

    function send(text) {
        if (!text) { return; }
        const payload = { type: 'message', text: text };
        if (chatProduct) { payload.product_id = chatProduct.id; }
        const now = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});

        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(payload));
            bubble('user', text, now, chatProduct);
        } else {
            // Fallback HTTP saat daemon mati
            const fd = new FormData();
            fd.append('message', text);
            if (chatProduct) { fd.append('product_id', chatProduct.id); }
            fetch('<?= site_url("chat/offline_message") ?>', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(() => bubble('user', text, now, chatProduct));
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim();
        input.value = '';
        send(text);
    });

    connect();
})();
</script>
