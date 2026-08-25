<div class="container">
    <div class="row justify-content-center pb-5">
        <div class="col-lg-7">
            <div class="page-head text-center" style="padding-bottom:32px;">
                <span class="eyebrow">Bantuan</span>
                <h1 class="page-title mt-2">Chat admin</h1>
                <p style="color:var(--muted); font-size:.9rem;" class="mb-0">Tanya sebelum beli, kami siap membantu.</p>
            </div>

            <div class="card-soft overflow-hidden">
                <div class="p-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--line); background:var(--paper);">
                    <span class="fw-semibold small">CHAT ADMIN JIDOOR</span>
                    <span style="font-size: 0.72rem; color:var(--muted);" id="pageChatStatus">Menghubungkan...</span>
                </div>
                <div id="pageMessages" class="p-4 overflow-auto" style="height: 420px; font-size: 0.9rem; background:#f4f1ec;"></div>
                <form id="pageChatForm" class="p-3 d-flex gap-2" style="border-top:1px solid var(--line);">
                    <input type="text" id="pageChatInput" class="fx-input" style="border-radius:999px; padding:10px 18px;" placeholder="Tulis pesan..." maxlength="1000" autocomplete="off">
                    <button type="submit" class="btn-ink px-3 flex-shrink-0" style="border-radius:50%; width:46px; height:46px; padding:0;"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const CHAT_TOKEN   = <?= json_encode(Chat_Token::make((int)$this->session->userdata('user_id'), 'user')) ?>;
    const CHAT_USER_ID = <?= (int)$this->session->userdata('user_id') ?>;

    let ws = null;
    const msgBox   = document.getElementById('pageMessages');
    const statusEl = document.getElementById('pageChatStatus');
    const form     = document.getElementById('pageChatForm');
    const input    = document.getElementById('pageChatInput');
    let lastDate   = null;

    const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    function todayKey() {
        const n = new Date();
        return n.getFullYear() + '-' + String(n.getMonth() + 1).padStart(2, '0') + '-' + String(n.getDate()).padStart(2, '0');
    }

    function dateLabel(d) {
        const today = todayKey();
        const y = new Date(); y.setDate(y.getDate() - 1);
        const yesterday = y.getFullYear() + '-' + String(y.getMonth() + 1).padStart(2, '0') + '-' + String(y.getDate()).padStart(2, '0');
        if (d === today) return 'Hari Ini';
        if (d === yesterday) return 'Kemarin';
        const p = String(d).split('-');
        return parseInt(p[2], 10) + ' ' + MONTHS[parseInt(p[1], 10) - 1] + ' ' + p[0];
    }

    function divider(label) {
        const el = document.createElement('div');
        el.className = 'd-flex justify-content-center my-3';
        el.innerHTML = '<span class="chat-date-divider">' + label + '</span>';
        msgBox.appendChild(el);
    }

    function bubble(role, text, time, date) {
        if (date && date !== lastDate) { divider(dateLabel(date)); lastDate = date; }
        const mine = role === 'user';
        const wrap = document.createElement('div');
        wrap.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');
        wrap.innerHTML =
            '<div class="px-3 py-2 ' + (mine ? 'bg-dark text-white' : 'bg-white border') + '" style="max-width:75%; border-radius:14px;">' +
            '<div>' + text.replace(/[<>&]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;'}[c])) + '</div>' +
            '<div class="' + (mine ? 'text-white-50' : 'text-muted') + '" style="font-size:0.65rem; text-align:right;">' + time + '</div>' +
            '</div>';
        msgBox.appendChild(wrap);
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    // Riwayat via HTTP saat halaman dibuka
    fetch('<?= site_url("chat/history") ?>')
        .then(r => r.json())
        .then(res => {
            (res.messages || []).forEach(m => bubble(m.role, m.text, m.sent_at, m.date));
            if (!(res.messages || []).length) {
                msgBox.innerHTML = '<div class="text-center text-muted py-5">Belum ada pesan.<br>Sapa admin kami!</div>';
            }
        });

    function connect() {
        try { ws = new WebSocket('ws://localhost:8080/chat'); } catch (e) { return; }

        ws.onopen = () => {
            statusEl.innerHTML = '<span class="text-success">● Admin sedang online</span>';
            ws.send(JSON.stringify({ type: 'auth', token: CHAT_TOKEN, user_id: CHAT_USER_ID, role: 'user' }));
        };
        ws.onmessage = (e) => {
            const d = JSON.parse(e.data);
            if (d.type === 'message') { bubble(d.role, d.text, d.sent_at, d.date); }
        };
        ws.onclose = () => {
            statusEl.textContent = 'Admin tidak sedang online — tinggalkan pesan';
            setTimeout(connect, 3000);
        };
        ws.onerror = () => { try { ws.close(); } catch (err) {} };
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) { return; }
        input.value = '';

        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ type: 'message', text: text }));
            bubble('user', text, new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}), todayKey());
        } else {
            const fd = new FormData();
            fd.append('message', text);
            fetch('<?= site_url("chat/offline_message") ?>', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(() => bubble('user', text, new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}), todayKey()));
        }
    });

    connect();
})();
</script>
