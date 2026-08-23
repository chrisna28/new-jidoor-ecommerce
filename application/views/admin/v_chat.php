<div class="page-head">
    <h2 class="page-title">Chat Pelanggan</h2>
    <p class="page-sub">Balas pertanyaan pelanggan secara real-time.</p>
</div>

<?php
$unread_total = 0;
foreach ($inbox as $c) { $unread_total += (int) $c->unread_admin; }
$has_thread = !empty($conv);
?>

<div class="chat-split <?= $has_thread ? 'has-thread' : '' ?>">
    <!-- ===== Panel kiri: daftar percakapan ===== -->
    <aside class="chat-list-pane">
        <div class="chat-list-head">
            <span class="fw-bold" style="font-size:.92rem;">Percakapan</span>
            <?php if ($unread_total > 0): ?>
                <span class="unread-pill"><?= $unread_total ?></span>
            <?php endif ?>
        </div>
        <div class="chat-list-scroll">
            <?php if (empty($inbox)): ?>
                <div class="empty-state py-5 px-3">
                    <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                    <h6>Belum ada percakapan</h6>
                    <p>Pesan dari tombol "Chat Penjual" akan muncul di sini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($inbox as $c):
                    $isActive = $has_thread && $conv->id == $c->id;
                    $isToday  = date('Y-m-d', strtotime($c->last_message_at)) === date('Y-m-d');
                ?>
                <a href="<?= base_url('admin/chat/' . $c->id) ?>"
                   class="conv-item <?= $isActive ? 'active' : '' ?> <?= $c->unread_admin > 0 ? 'unread' : '' ?>">
                    <span class="avatar-circle avatar-customer avatar-sm"><?= strtoupper(substr($c->username ?: 'U', 0, 1)) ?></span>
                    <span class="conv-body">
                        <span class="conv-name"><?= htmlspecialchars($c->username ?: 'Pengguna #' . $c->user_id) ?></span>
                        <span class="conv-snippet">
                            <?php if ($c->last_product_id): ?><i class="fas fa-box-open conv-prod-icon"></i><?php endif ?>
                            <?= $c->last_text !== null ? htmlspecialchars(mb_strimwidth($c->last_text, 0, 42, '…')) : 'Belum ada pesan' ?>
                        </span>
                    </span>
                    <span class="conv-meta">
                        <time class="conv-time"><?= $isToday ? date('H:i', strtotime($c->last_message_at)) : date('d M', strtotime($c->last_message_at)) ?></time>
                        <?php if ($c->unread_admin > 0): ?>
                            <span class="unread-pill"><?= $c->unread_admin ?></span>
                        <?php endif ?>
                    </span>
                </a>
                <?php endforeach ?>
            <?php endif ?>
        </div>
    </aside>

    <!-- ===== Panel kanan: thread / empty state ===== -->
    <section class="chat-thread-pane">
        <?php if ($has_thread): ?>

        <div class="chat-thread-head">
            <a href="<?= base_url('admin/chat') ?>" class="btn btn-sm btn-admin-outline d-lg-none" title="Kembali ke daftar"><i class="fas fa-arrow-left"></i></a>
            <span class="avatar-circle avatar-customer avatar-sm"><?= strtoupper(substr($conv->username ?: 'U', 0, 1)) ?></span>
            <div class="min-w-0">
                <div class="fw-bold text-truncate" style="font-size:.95rem;"><?= htmlspecialchars($conv->username ?: 'Pengguna #' . $conv->user_id) ?></div>
                <small class="text-success"><span class="ai-pulse">●</span> Percakapan aktif</small>
            </div>
        </div>

        <!-- Riwayat -->
        <div id="adminMessages" class="chat-log chat-log-fill">
            <?php if (empty($history)): ?>
                <div class="text-center text-muted py-5">Belum ada pesan.</div>
            <?php endif ?>
            <?php foreach ($history as $m): ?>
                <div class="d-flex mb-2 <?= $m->sender_role === 'admin' ? 'justify-content-end' : 'justify-content-start' ?>">
                    <div class="chat-bubble <?= $m->sender_role === 'admin' ? 'admin' : 'customer' ?>">
                        <?php if (!empty($m->product_id)): ?>
                            <!-- Kartu konteks produk ala Shopee -->
                            <a href="<?= base_url('produk/' . $m->product_slug) ?>" target="_blank" rel="noopener"
                               class="chat-prodcard">
                                <img src="<?= $m->product_image ? base_url('uploads/products/' . $m->product_image) : 'https://placehold.co/72x72/f1f5f9/94a3b8?text=%3F' ?>"
                                     alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">
                                <span class="overflow-hidden">
                                    <span class="d-block text-truncate fw-bold prod-name" style="font-size:.7rem;max-width:160px;"><?= htmlspecialchars($m->product_name) ?></span>
                                    <span class="d-block fw-bold prod-price" style="font-size:.7rem;">Rp <?= number_format($m->product_price, 0, ',', '.') ?></span>
                                </span>
                            </a>
                        <?php endif ?>
                        <div><?= nl2br(htmlspecialchars($m->message)) ?></div>
                        <div class="chat-time"><?= date('H:i', strtotime($m->created_at)) ?></div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <div class="chat-composer">
            <!-- Chip balasan cepat ala Shopee -->
            <div class="d-flex flex-wrap gap-1 mb-2">
                <button type="button" class="btn btn-sm btn-admin-outline rounded-pill px-3 py-1" style="font-size:.72rem" data-q="Stok ready?">Stok ready?</button>
                <button type="button" class="btn btn-sm btn-admin-outline rounded-pill px-3 py-1" style="font-size:.72rem" data-q="Bisa custom">Bisa custom</button>
                <button type="button" class="btn btn-sm btn-admin-outline rounded-pill px-3 py-1" style="font-size:.72rem" data-q="Estimasi kirim 2-3 hari">Estimasi kirim 2-3 hari</button>
                <button type="button" class="btn btn-sm btn-admin-outline rounded-pill px-3 py-1" style="font-size:.72rem" data-q="Terima kasih sudah bertanya!">Terima kasih</button>
            </div>

            <!-- Preview produk yang akan dilampirkan -->
            <div id="adminPending" class="d-none align-items-center gap-2 p-2 mb-2 info-panel">
                <img id="apImg" src="" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;background:var(--surface-2);">
                <div class="flex-grow-1 overflow-hidden">
                    <div id="apName" class="text-truncate fw-bold small"></div>
                    <div id="apPrice" class="fw-bold" style="font-size:.72rem;color:var(--accent);"></div>
                </div>
                <button type="button" id="apRemove" class="btn btn-link text-muted p-1" title="Lepas produk"><i class="fas fa-times"></i></button>
            </div>

            <!-- Form kirim -->
            <form id="adminChatForm" class="d-flex gap-2">
                <input type="hidden" name="conversation_id" value="<?= $conv->id ?>">
                <input type="text" id="adminChatInput" class="form-control-admin flex-grow-1" placeholder="Tulis balasan..." maxlength="1000" autocomplete="off">
                <div class="position-relative">
                    <button type="button" id="prodBtn" class="btn btn-admin-outline px-3 h-100" title="Lampirkan rekomendasi produk"><i class="fas fa-box-open me-1"></i> Produk</button>
                    <div id="prodMenu" class="d-none position-absolute bottom-100 start-0 mb-1 bg-white border rounded-3 shadow-lg p-1 overflow-auto" style="width:280px; max-height:260px; z-index:10; border-color: var(--border) !important;">
                        <?php foreach ($chat_products as $p): ?>
                        <button type="button"
                                class="d-flex align-items-center gap-2 w-100 text-start border-0 bg-transparent rounded-2 p-1 prod-item"
                                data-id="<?= (int)$p['id'] ?>"
                                data-name="<?= htmlspecialchars($p['name']) ?>"
                                data-price="<?= (float)$p['price'] ?>"
                                data-image="<?= $p['image'] ?: '' ?>">
                            <img src="<?= $p['image'] ? base_url('uploads/products/' . $p['image']) : 'https://placehold.co/64x64/f1f5f9/94a3b8?text=%3F' ?>" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:6px;">
                            <span class="overflow-hidden">
                                <span class="d-block text-truncate fw-bold" style="font-size:.72rem;max-width:170px;color:var(--text-1);"><?= htmlspecialchars($p['name']) ?></span>
                                <span class="d-block fw-bold" style="font-size:.7rem;color:var(--accent);">Rp <?= number_format($p['price'], 0, ',', '.') ?></span>
                            </span>
                        </button>
                        <?php endforeach ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-admin-primary px-4"><i class="fas fa-paper-plane me-1"></i> Kirim</button>
            </form>
            <div class="small text-muted mt-2" id="adminChatStatus"></div>
        </div>

        <script>
        (function () {
            const CONV_ID = <?= (int)$conv->id ?>;
            const ADMIN_ID = <?= (int)$this->session->userdata('user_id') ?>;
            const CHAT_TOKEN = <?= json_encode(Chat_Token::make((int)$this->session->userdata('user_id'), 'admin')) ?>;

            const msgBox   = document.getElementById('adminMessages');
            const form     = document.getElementById('adminChatForm');
            const input    = document.getElementById('adminChatInput');
            const statusEl = document.getElementById('adminChatStatus');

            msgBox.scrollTop = msgBox.scrollHeight;

            const fmtPrice = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
            const escHtml = s => String(s).replace(/[<>&"]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c]));

            function productCardHtml(p) {
                const img = p.image ? '<?= base_url("uploads/products/") ?>' + escHtml(p.image)
                                    : 'https://placehold.co/72x72/f1f5f9/94a3b8?text=%3F';
                return '<a href="<?= base_url("produk/") ?>' + escHtml(p.slug) + '" target="_blank" rel="noopener" class="chat-prodcard">' +
                       '<img src="' + img + '" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">' +
                       '<span class="overflow-hidden">' +
                       '<span class="d-block text-truncate fw-bold prod-name" style="font-size:.7rem;max-width:160px;">' + escHtml(p.name) + '</span>' +
                       '<span class="d-block fw-bold prod-price" style="font-size:.7rem;">' + fmtPrice(p.price) + '</span>' +
                       '</span></a>';
            }

            function bubble(role, text, time, product) {
                const mine = role === 'admin';
                const wrap = document.createElement('div');
                wrap.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');
                wrap.innerHTML =
                    '<div class="chat-bubble ' + (mine ? 'admin' : 'customer') + '">' +
                    (product ? productCardHtml(product) : '') +
                    '<div>' + text.replace(/[<>&]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;'}[c])) + '</div>' +
                    '<div class="chat-time">' + time + '</div>' +
                    '</div>';
                msgBox.appendChild(wrap);
                msgBox.scrollTop = msgBox.scrollHeight;
            }

            let ws = null;
            function connect() {
                try { ws = new WebSocket('ws://localhost:8080/chat'); } catch (e) { offline(); return; }

                ws.onopen = () => ws.send(JSON.stringify({
                    type: 'auth', token: CHAT_TOKEN, user_id: ADMIN_ID, role: 'admin'
                }));

                ws.onmessage = (e) => {
                    const d = JSON.parse(e.data);
                    // Hanya tampilkan pesan percakapan yang sedang dibuka
                    if (d.type === 'message' && d.conversation_id === CONV_ID) {
                        bubble(d.role, d.text, d.sent_at, d.product);
                    }
                };

                ws.onclose = () => { offline(); setTimeout(connect, 3000); };
                ws.onerror = () => { try { ws.close(); } catch (err) {} };

                const origOnOpen = ws.onopen;
                ws.onopen = function () {
                    origOnOpen();
                    statusEl.innerHTML = '<span class="text-success">● Terhubung real-time</span>';
                    ws.send(JSON.stringify({type:'auth', token: CHAT_TOKEN, user_id: ADMIN_ID, role: 'admin'}));
                };
            }

            function offline() {
                statusEl.textContent = 'Server chat tidak aktif — jalankan: php chat-server.php';
            }

            // ===== Chip balasan cepat: isi input, admin bisa edit dulu =====
            document.querySelectorAll('[data-q]').forEach(b =>
                b.addEventListener('click', () => { input.value = b.dataset.q; input.focus(); })
            );

            // ===== Picker rekomendasi produk ala Shopee =====
            let pendingProduct = null;
            const prodBtn  = document.getElementById('prodBtn');
            const prodMenu = document.getElementById('prodMenu');
            const pendingBar = document.getElementById('adminPending');

            prodBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                prodMenu.classList.toggle('d-none');
            });
            document.addEventListener('click', (e) => {
                if (!prodMenu.contains(e.target)) { prodMenu.classList.add('d-none'); }
            });

            function clearPending() {
                pendingProduct = null;
                pendingBar.classList.add('d-none');
                pendingBar.classList.remove('d-flex');
            }

            prodMenu.addEventListener('click', (e) => {
                const item = e.target.closest('.prod-item');
                if (!item) { return; }
                pendingProduct = {
                    id: +item.dataset.id,
                    name: item.dataset.name,
                    price: +item.dataset.price,
                    image: item.dataset.image || null
                };
                document.getElementById('apImg').src = pendingProduct.image
                    ? '<?= base_url("uploads/products/") ?>' + escHtml(pendingProduct.image)
                    : 'https://placehold.co/72x72/f1f5f9/94a3b8?text=%3F';
                document.getElementById('apName').textContent = pendingProduct.name;
                document.getElementById('apPrice').textContent = fmtPrice(pendingProduct.price);
                pendingBar.classList.remove('d-none');
                pendingBar.classList.add('d-flex');
                prodMenu.classList.add('d-none');
                input.focus();
            });

            document.getElementById('apRemove').addEventListener('click', clearPending);

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const text = input.value.trim();
                if (!text) { return; }
                input.value = '';

                const now = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                if (ws && ws.readyState === WebSocket.OPEN) {
                    const payload = { type: 'message', conversation_id: CONV_ID, text: text };
                    if (pendingProduct) { payload.product_id = pendingProduct.id; }
                    ws.send(JSON.stringify(payload));
                    bubble('admin', text, now, pendingProduct);
                    clearPending();
                } else {
                    showToast('Server chat belum berjalan. Jalankan "php chat-server.php" lalu coba lagi.', 'error');
                }
            });

            connect();
        })();
        </script>

        <?php else: ?>

        <div class="empty-state h-100 justify-content-center my-auto">
            <div class="empty-icon"><i class="far fa-comments"></i></div>
            <h6>Pilih percakapan</h6>
            <p>Pilih salah satu pelanggan di panel sebelah kiri untuk mulai membalas pesan secara real-time.</p>
        </div>

        <?php endif ?>
    </section>
</div>
