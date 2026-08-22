    </div><!-- /.admin-page -->
</main>

<!-- Toast stack -->
<div class="toast-stack" id="toastStack"></div>

<!-- Modal konfirmasi global -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm-custom">
        <div class="modal-content" style="max-width: 400px; margin: 0 auto;">
            <div class="modal-body text-center pt-4">
                <div class="empty-icon mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:56px;height:56px;background:var(--danger-bg);color:var(--danger);font-size:1.25rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h6 class="fw-bold mb-1" id="confirmTitle">Konfirmasi</h6>
                <p class="text-muted mb-0" style="font-size: 0.87rem;" id="confirmMessage">Lanjutkan tindakan ini?</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-admin-outline" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger rounded-3 fw-semibold px-4" id="confirmOk">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    /* ── Sidebar drawer (mobile) ── */
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.add('open');
            overlay.classList.add('show');
        });
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    /* ── Toast ── */
    function showToast(message, type) {
        type = type || 'success';
        var stack = document.getElementById('toastStack');
        var icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
        var el = document.createElement('div');
        el.className = 'toast-item t-' + type;
        el.innerHTML =
            '<i class="fa-solid ' + (icons[type] || icons.info) + ' toast-icon"></i>' +
            '<span class="toast-msg">' + message + '</span>' +
            '<button class="toast-close" aria-label="tutup">&times;</button>';
        stack.appendChild(el);
        var remove = function () {
            el.classList.add('leaving');
            setTimeout(function () { el.remove(); }, 250);
        };
        el.querySelector('.toast-close').addEventListener('click', remove);
        setTimeout(remove, 3800);
    }

    /* Flashdata dari controller → toast otomatis */
    document.querySelectorAll('.js-flash').forEach(function (n) {
        showToast(n.dataset.msg, n.dataset.type);
    });

    /* ── Konfirmasi global ──
       - <a>/<button> ber-data-confirm  : dicegat saat diklik
       - <form> ber-data-confirm        : dicegat saat SUBMIT saja,
         sehingga klik select/input di dalam form tidak memunculkan popup  */
    function openConfirm(title, msg, onOk) {
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = msg;
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmModal'));
        document.getElementById('confirmOk').onclick = function () {
            modal.hide();
            onOk();
        };
        modal.show();
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('a[data-confirm], button[data-confirm]');
        if (!trigger) return;
        e.preventDefault();
        e.stopPropagation();
        openConfirm(
            trigger.getAttribute('data-confirm-title') || 'Konfirmasi',
            trigger.getAttribute('data-confirm') || 'Lanjutkan tindakan ini?',
            function () {
                if (trigger.tagName === 'A') { window.location.href = trigger.getAttribute('href'); }
            }
        );
    });

    document.addEventListener('submit', function (e) {
        var form = e.target.closest('form[data-confirm]');
        if (!form) return;
        e.preventDefault();
        openConfirm(
            form.getAttribute('data-confirm-title') || 'Konfirmasi',
            form.getAttribute('data-confirm') || 'Lanjutkan tindakan ini?',
            function () { form.submit(); }
        );
    });

    /* Tooltip initialization */
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
</body>
</html>
