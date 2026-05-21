<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ── Status badges ── */
        .badge-pending  { background:#f6c23e !important; color:#212529; }
        .badge-approved { background:#1cc88a !important; color:#fff; }
        .badge-rejected { background:#e74a3b !important; color:#fff; }

        /* ── Card karyawan di modal ── */
        .req-avatar {
            width: 60px; height: 60px; border-radius: 50%;
            border: 3px solid #dee2e6; object-fit: cover;
        }

        /* ── KTP preview ── */
        .ktp-thumb {
            max-width: 100%; border-radius: 10px;
            border: 2px solid #dee2e6; cursor: zoom-in;
        }

        /* ── Filter tabs ── */
        .tab-filter .nav-link {
            font-size: 0.78rem; font-weight: 600;
            letter-spacing: 0.05em; text-transform: uppercase;
            color: #858796; border-radius: 6px; padding: 6px 14px;
        }
        .tab-filter .nav-link.active { background: #4e73df; color: #fff; }
        .tab-filter .nav-link:hover:not(.active) { background: #eaecf4; color: #333; }

        /* ── Table ── */
        #tblPending th, #tblPending td { vertical-align: middle; font-size: 0.82rem; }
        #tblPending thead th { font-size: 0.73rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #4e73df; }
        .avatar-sm { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #dee2e6; object-fit: cover; }

        /* ── Empty state ── */
        .empty-state { padding: 60px 20px; text-align: center; color: #b7b9cc; }
        .empty-state i { font-size: 3rem; margin-bottom: 14px; opacity: 0.4; }

        /* ── Badge counter on tab ── */
        .tab-counter { display: inline-block; background: #e74a3b; color: #fff; border-radius: 20px; padding: 1px 7px; font-size: 0.68rem; font-weight: 700; margin-left: 5px; vertical-align: middle; }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <!-- Header -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="mb-0"><?= $title ?></h2>
                            <small class="text-muted">Review dan setujui pendaftaran karyawan baru</small>
                        </div>
                        <a href="<?= base_url('pengguna') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Master Karyawan
                        </a>
                    </div>

                    <!-- Flash -->
                    <div id="flashArea"></div>

                    <!-- Stats cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card border-left-warning shadow-sm h-100 py-2">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu</div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="statPending">—</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-left-success shadow-sm h-100 py-2">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Disetujui</div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="statApproved">—</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-left-danger shadow-sm h-100 py-2">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Ditolak</div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="statRejected">—</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-left-info shadow-sm h-100 py-2">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total</div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="statTotal">—</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main card -->
                    <div class="card shadow-sm">
                        <div class="card-header py-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-auto me-md-auto">
                                    <!-- Tabs -->
                                    <ul class="nav tab-filter" id="statusTabs">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="#" data-status="pending">
                                                Pending <span class="tab-counter" id="tabCountPending">0</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" data-status="approved">Disetujui</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" data-status="rejected">Ditolak</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" data-status="all">Semua</a>
                                        </li>
                                    </ul>
                                </div>
                                <!-- Search -->
                                <div class="col-12 col-sm-5 col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" id="searchInput" class="form-control" placeholder="Cari nama, NIK...">
                                    </div>
                                </div>
                                <!-- Limit -->
                                <div class="col-auto d-flex align-items-center gap-2">
                                    <span class="text-muted small">Show</span>
                                    <select id="limitSel" class="form-select form-select-sm" style="width:70px">
                                        <option value="10">10</option>
                                        <option value="25" selected>25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle mb-0" id="tblPending">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width:45px">No</th>
                                            <th style="min-width:180px">Nama / NIK</th>
                                            <th style="min-width:110px">Tgl Daftar</th>
                                            <th style="min-width:130px">Posisi</th>
                                            <th style="min-width:120px">Group</th>
                                            <th style="min-width:90px">Status</th>
                                            <th style="min-width:100px">IP Address</th>
                                            <th class="text-center" style="min-width:160px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer py-2">
                            <small class="text-muted" id="paginInfo">—</small>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- ── MODAL DETAIL ── -->
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-check me-2"></i>Detail Pendaftaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody"><!-- Diisi JS --></div>
                <div class="modal-footer" id="modalFooter"><!-- Diisi JS --></div>
            </div>
        </div>
    </div>

    <!-- ── MODAL CATATAN (approve/reject) ── -->
    <div class="modal fade" id="modalCatatan" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" id="catatanHeader">
                    <h5 class="modal-title" id="catatanTitle">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="catatanDesc" class="text-muted small mb-3"></p>
                    <label class="form-label fw-bold">Catatan Admin <small class="text-muted">(opsional)</small></label>
                    <textarea id="catatanInput" class="form-control" rows="3" placeholder="Tulis catatan untuk pendaftar..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn" id="catatanConfirmBtn">Konfirmasi</button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function () {

        let currentStatus = 'pending';
        let currentPage   = 1;
        let pendingAction = null; // { action: 'approve'|'reject', id, nama }
        let searchTimeout;

        const BASE = '<?= base_url() ?>';
        const KTP_BASE = '<?= base_url('uploads/ktp/') ?>';
        const PROFIL_BASE = '<?= base_url('uploads/profil/') ?>';

        // ── Load data ──
        function loadData() {
            const keyword = $('#searchInput').val().trim();
            const limit   = parseInt($('#limitSel').val());
            const offset  = (currentPage - 1) * limit;

            $.ajax({
                url: BASE + 'register/list_ajax',
                method: 'POST',
                data: { status: currentStatus, keyword, limit, offset },
                success: function(res) {
                    const d = JSON.parse(res);
                    renderTable(d);
                    updateStats();
                },
                error: function() {
                    $('#tableBody').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data.</td></tr>');
                }
            });
        }

        // ── Render table ──
        function renderTable(d) {
            if (!d.list || !d.list.length) {
                $('#tableBody').html(`
                    <tr><td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            <strong>Tidak ada pendaftaran</strong><br>
                            <small>Belum ada data untuk filter ini.</small>
                        </div>
                    </td></tr>
                `);
                $('#paginInfo').text('Menampilkan 0 entri');
                return;
            }

            let html = '', no = d.start;
            d.list.forEach(function(r) {
                const statusMap = { pending: 'warning', approved: 'success', rejected: 'danger' };
                const statusLabel = { pending: 'Pending', approved: 'Disetujui', rejected: 'Ditolak' };
                const sBadge = `<span class="badge badge-${r.status} px-2 py-1" style="font-size:0.73rem">${statusLabel[r.status]||r.status}</span>`;
                const levelLabel = ucwords((r.user_level||'').replace(/_/g,' '));
                const tgl = formatTgl(r.created_at);

                const actionBtns = r.status === 'pending' ? `
                    <button class="btn btn-success btn-sm btn-approve" data-id="${r.id}" data-nama="${esc(r.nama)}" title="Setujui">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-danger btn-sm btn-reject" data-id="${r.id}" data-nama="${esc(r.nama)}" title="Tolak">
                        <i class="fas fa-times"></i>
                    </button>
                ` : '';

                html += `<tr>
                    <td class="text-center">${no++}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="${PROFIL_BASE}${esc(r.foto_profil||'default-1.png')}" class="avatar-sm" alt="">
                            <div>
                                <div class="fw-bold">${esc(r.nama)}</div>
                                <small class="text-muted">${esc(r.nik)}</small>
                            </div>
                        </div>
                    </td>
                    <td class="small text-muted">${tgl}</td>
                    <td><span class="badge bg-secondary px-2 py-1" style="font-size:0.73rem">${levelLabel}</span></td>
                    <td class="small">${esc(r.group_karyawan||'—')}</td>
                    <td>${sBadge}</td>
                    <td class="small text-muted">${esc(r.ip_address||'—')}</td>
                    <td class="text-center">
                        <button class="btn btn-info btn-sm btn-detail" data-id="${r.id}" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${actionBtns}
                        <button class="btn btn-secondary btn-sm btn-hapus" data-id="${r.id}" data-nama="${esc(r.nama)}" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });

            $('#tableBody').html(html);
            $('#paginInfo').text(`Menampilkan ${d.start}–${d.end} dari ${d.total} entri`);
        }

        // ── Update stats ──
        function updateStats() {
            ['pending','approved','rejected'].forEach(function(s) {
                $.ajax({
                    url: BASE + 'register/list_ajax',
                    method: 'POST',
                    data: { status: s, keyword: '', limit: 1, offset: 0 },
                    success: function(res) {
                        const d = JSON.parse(res);
                        if (s === 'pending') {
                            $('#statPending, #tabCountPending').text(d.total);
                            const map = { pending: 'warning', approved: 'success', rejected: 'danger' };
                        }
                        if (s === 'approved') $('#statApproved').text(d.total);
                        if (s === 'rejected') $('#statRejected').text(d.total);
                    }
                });
            });
            $.ajax({
                url: BASE + 'register/list_ajax',
                method: 'POST',
                data: { status: 'all', keyword: '', limit: 1, offset: 0 },
                success: function(res) { $('#statTotal').text(JSON.parse(res).total); }
            });
        }

        // ── Tab click ──
        $(document).on('click', '#statusTabs .nav-link', function(e) {
            e.preventDefault();
            $('#statusTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            currentStatus = $(this).data('status');
            currentPage = 1;
            loadData();
        });

        // ── Search ──
        $('#searchInput').on('keyup', function() {
            currentPage = 1;
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadData, 400);
        });

        $('#limitSel').on('change', function() { currentPage = 1; loadData(); });

        // ── Detail modal ──
        $(document).on('click', '.btn-detail', function() {
            const id = $(this).data('id');
            $.ajax({
                url: BASE + 'register/list_ajax',
                method: 'POST',
                data: { status: 'all', keyword: '', limit: 1000, offset: 0 },
                success: function(res) {
                    const list = JSON.parse(res).list;
                    const r = list.find(x => x.id == id);
                    if (!r) return;

                    const statusColor = { pending: 'warning', approved: 'success', rejected: 'danger' };
                    const statusLabel = { pending: 'Menunggu Persetujuan', approved: 'Disetujui', rejected: 'Ditolak' };

                    $('#modalBody').html(`
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img src="${PROFIL_BASE}${esc(r.foto_profil||'default-1.png')}" class="req-avatar">
                            <div>
                                <h5 class="mb-1 fw-bold">${esc(r.nama)}</h5>
                                <div class="text-muted small">NIK: ${esc(r.nik)}</div>
                                <span class="badge badge-${r.status} mt-1">${statusLabel[r.status]||r.status}</span>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr><th class="bg-light" style="width:40%">Tanggal Lahir</th><td>${esc(r.tanggal_lahir)}</td></tr>
                                    <tr><th class="bg-light">Posisi</th><td>${ucwords((r.user_level||'').replace(/_/g,' '))}</td></tr>
                                    <tr><th class="bg-light">Group</th><td>${esc(r.group_karyawan||'—')}</td></tr>
                                    <tr><th class="bg-light">Status</th><td>${esc(r.status_kepegawaian||'—')}</td></tr>
                                    <tr><th class="bg-light">Golongan</th><td>${esc(r.golongan||'—')}</td></tr>
                                    <tr><th class="bg-light">Tgl Bergabung</th><td>${esc(r.tanggal_join||'—')}</td></tr>
                                    <tr><th class="bg-light">IP Address</th><td>${esc(r.ip_address||'—')}</td></tr>
                                    <tr><th class="bg-light">Tgl Daftar</th><td>${formatTgl(r.created_at)}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <p class="fw-bold mb-2 small text-muted text-uppercase" style="letter-spacing:0.08em">Foto KTP</p>
                                ${r.foto_ktp
                                    ? `<a href="${KTP_BASE}${esc(r.foto_ktp)}" target="_blank">
                                        <img src="${KTP_BASE}${esc(r.foto_ktp)}" class="ktp-thumb" alt="KTP">
                                       </a>
                                       <small class="text-muted d-block mt-1"><i class="fas fa-external-link-alt"></i> Klik untuk buka full</small>`
                                    : '<em class="text-muted small">Tidak ada foto KTP</em>'
                                }
                                ${r.catatan_admin ? `
                                    <div class="alert alert-info mt-3 small mb-0">
                                        <strong>Catatan Admin:</strong> ${esc(r.catatan_admin)}
                                    </div>` : ''
                                }
                            </div>
                        </div>
                    `);

                    const footerBtns = r.status === 'pending' ? `
                        <button type="button" class="btn btn-success btn-approve" data-id="${r.id}" data-nama="${esc(r.nama)}">
                            <i class="fas fa-check me-1"></i> Setujui
                        </button>
                        <button type="button" class="btn btn-danger btn-reject" data-id="${r.id}" data-nama="${esc(r.nama)}">
                            <i class="fas fa-times me-1"></i> Tolak
                        </button>
                    ` : '';

                    $('#modalFooter').html(`
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        ${footerBtns}
                    `);

                    new bootstrap.Modal(document.getElementById('modalDetail')).show();
                }
            });
        });

        // ── Approve click ──
        $(document).on('click', '.btn-approve', function() {
            pendingAction = { action: 'approve', id: $(this).data('id'), nama: $(this).data('nama') };
            $('#catatanHeader').removeClass('bg-danger').addClass('bg-success text-white');
            $('#catatanTitle').html('<i class="fas fa-check-circle me-2"></i>Setujui Pendaftaran');
            $('#catatanDesc').html(`Anda akan menyetujui pendaftaran <strong>${esc(pendingAction.nama)}</strong>.<br>Akun akan langsung aktif dan bisa digunakan untuk login.`);
            $('#catatanInput').val('');
            $('#catatanConfirmBtn').removeClass('btn-danger').addClass('btn-success').html('<i class="fas fa-check me-1"></i>Ya, Setujui');
            bootstrap.Modal.getInstance(document.getElementById('modalDetail'))?.hide();
            new bootstrap.Modal(document.getElementById('modalCatatan')).show();
        });

        // ── Reject click ──
        $(document).on('click', '.btn-reject', function() {
            pendingAction = { action: 'reject', id: $(this).data('id'), nama: $(this).data('nama') };
            $('#catatanHeader').removeClass('bg-success').addClass('bg-danger text-white');
            $('#catatanTitle').html('<i class="fas fa-times-circle me-2"></i>Tolak Pendaftaran');
            $('#catatanDesc').html(`Anda akan menolak pendaftaran <strong>${esc(pendingAction.nama)}</strong>.<br>Akun tidak akan dibuat.`);
            $('#catatanInput').val('');
            $('#catatanConfirmBtn').removeClass('btn-success').addClass('btn-danger').html('<i class="fas fa-times me-1"></i>Ya, Tolak');
            bootstrap.Modal.getInstance(document.getElementById('modalDetail'))?.hide();
            new bootstrap.Modal(document.getElementById('modalCatatan')).show();
        });

        // ── Confirm action ──
        $('#catatanConfirmBtn').on('click', function() {
            if (!pendingAction) return;
            const catatan = $('#catatanInput').val().trim();
            const url = BASE + 'register/' + pendingAction.action;

            $.ajax({
                url: url,
                method: 'POST',
                data: { id: pendingAction.action === 'approve' ? pendingAction.id : undefined, id: pendingAction.id, catatan },
                success: function(res) {
                    const d = JSON.parse(res);
                    bootstrap.Modal.getInstance(document.getElementById('modalCatatan')).hide();
                    if (d.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', html: d.message, timer: 2500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: d.message });
                    }
                    pendingAction = null;
                    setTimeout(loadData, 1500);
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan. Coba lagi.' });
                }
            });
        });

        // ── Hapus ──
        $(document).on('click', '.btn-hapus', function() {
            const id   = $(this).data('id');
            const nama = $(this).data('nama');
            Swal.fire({
                title: 'Hapus Data?',
                html: `Data pendaftaran <strong>${esc(nama)}</strong> akan dihapus permanen.`,
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal', reverseButtons: true
            }).then(function(r) {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: BASE + 'register/hapus',
                    method: 'POST', data: { id },
                    success: function(res) {
                        const d = JSON.parse(res);
                        if (d.status === 'success') {
                            Swal.fire({ icon: 'success', title: 'Dihapus!', text: d.message, timer: 2000, showConfirmButton: false });
                            setTimeout(loadData, 1500);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: d.message });
                        }
                    }
                });
            });
        });

        // ── Helpers ──
        function esc(t) {
            if (!t) return '';
            const d = document.createElement('div'); d.textContent = t; return d.innerHTML;
        }
        function ucwords(s) { return s ? s.replace(/\b\w/g, l => l.toUpperCase()) : ''; }
        function formatTgl(s) {
            if (!s) return '—';
            const d = new Date(s); if (isNaN(d)) return s;
            return d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) +
                   ' ' + d.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
        }

        // ── Init ──
        loadData();
        updateStats();
    });
    </script>
</body>
</html>