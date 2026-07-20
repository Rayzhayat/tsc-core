<!-- index.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>

        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <!-- FLASH -->
                    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls): ?>
                        <?php if ($this->session->flashdata($key)): ?>
                            <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                                <?= $this->session->flashdata($key) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>
                    <?php endforeach ?>

                    <!-- HEADER -->
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <h1 class="page-title mb-0">
                                <i class="fas fa-history text-primary me-2"></i> Audit Log
                            </h1>
                            <small class="text-muted">Jejak aktivitas pengguna di seluruh sistem</small>
                        </div>
                    </div>

                    <!-- SUMMARY CARDS -->
                    <?php $s = $summary; ?>
                    <div class="row g-3 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #4e73df !important;">
                                <div class="card-body d-flex align-items-center gap-3 py-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:46px;height:46px;background:rgba(78,115,223,.15)">
                                        <i class="fas fa-list text-primary fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;letter-spacing:.05em">
                                            Total Log
                                        </div>
                                        <div class="h4 mb-0 fw-bold"><?= number_format($s->total) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #1cc88a !important;">
                                <div class="card-body d-flex align-items-center gap-3 py-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:46px;height:46px;background:rgba(28,200,138,.15)">
                                        <i class="fas fa-calendar-day text-success fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;letter-spacing:.05em">
                                            Hari Ini
                                        </div>
                                        <div class="h4 mb-0 fw-bold text-success"><?= number_format($s->today) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #e74a3b !important;">
                                <div class="card-body d-flex align-items-center gap-3 py-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:46px;height:46px;background:rgba(231,74,59,.15)">
                                        <i class="fas fa-trash text-danger fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;letter-spacing:.05em">
                                            Total Delete
                                        </div>
                                        <div class="h4 mb-0 fw-bold text-danger"><?= number_format($s->delete_count) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f6c23e !important;">
                                <div class="card-body d-flex align-items-center gap-3 py-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:46px;height:46px;background:rgba(246,194,62,.15)">
                                        <i class="fas fa-users text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;letter-spacing:.05em">
                                            Active User (7 hari)
                                        </div>
                                        <div class="h4 mb-0 fw-bold text-warning"><?= number_format($s->active_users) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FILTER BAR -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-body py-2">
                            <form id="filterForm" class="row g-2 align-items-end">
                                <div class="col-md-2 col-6">
                                    <label class="form-label small mb-1 fw-semibold">Modul</label>
                                    <select id="f_modul" class="form-select form-select-sm">
                                        <option value="">Semua Modul</option>
                                        <?php foreach ($modul_list as $m): ?>
                                            <option value="<?= $m->modul ?>" <?= $filters['modul'] == $m->modul ? 'selected' : '' ?>>
                                                <?= $m->modul ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <div class="col-md-2 col-6">
                                    <label class="form-label small mb-1 fw-semibold">Aksi</label>
                                    <select id="f_aksi" class="form-select form-select-sm">
                                        <option value="">Semua Aksi</option>
                                        <?php foreach (['create', 'update', 'delete', 'approve', 'reject', 'login', 'other'] as $a): ?>
                                            <option value="<?= $a ?>" <?= $filters['aksi'] == $a ? 'selected' : '' ?>>
                                                <?= strtoupper($a) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <div class="col-md-2 col-6">
                                    <label class="form-label small mb-1 fw-semibold">User</label>
                                    <select id="f_user_id" class="form-select form-select-sm">
                                        <option value="">Semua User</option>
                                        <?php foreach ($user_list as $u): ?>
                                            <option value="<?= $u->user_id ?>" <?= $filters['user_id'] == $u->user_id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($u->user_nama) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <div class="col-md-2 col-6">
                                    <label class="form-label small mb-1 fw-semibold">Dari</label>
                                    <input type="date" id="f_date_from" class="form-control form-control-sm"
                                           value="<?= $filters['date_from'] ?>">
                                </div>

                                <div class="col-md-2 col-6">
                                    <label class="form-label small mb-1 fw-semibold">Sampai</label>
                                    <input type="date" id="f_date_to" class="form-control form-control-sm"
                                           value="<?= $filters['date_to'] ?>">
                                </div>

                                <div class="col-md-2 col-6">
                                    <button type="button" id="btnFilter" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 fw-bold">
                                <i class="fas fa-table me-2 text-primary"></i> Daftar Aktivitas
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0" id="auditTable" style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Waktu</th>
                                            <th>User</th>
                                            <th>Level</th>
                                            <th>Modul</th>
                                            <th>Aksi</th>
                                            <th>Keterangan</th>
                                            <th>IP</th>
                                            <th class="text-center">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- MODAL DETAIL -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i> Detail Perubahan
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="detailKeterangan" class="fw-semibold mb-3"></p>
                    <div class="row">
                        <div class="col-6">
                            <h6 class="text-danger">Data Sebelum</h6>
                            <pre id="detailLama" class="bg-light p-2 rounded small" style="white-space:pre-wrap"></pre>
                        </div>
                        <div class="col-6">
                            <h6 class="text-success">Data Sesudah</h6>
                            <pre id="detailBaru" class="bg-light p-2 rounded small" style="white-space:pre-wrap"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        (function initAuditTable() {
            if (typeof jQuery === 'undefined' || !$.fn.DataTable) {
                setTimeout(initAuditTable, 100);
                return;
            }

            var table = $('#auditTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                ajax: {
                    url: '<?= base_url('audit_log/ajax_list') ?>',
                    type: 'POST',
                    data: function (d) {
                        d.f_modul = $('#f_modul').val();
                        d.f_aksi = $('#f_aksi').val();
                        d.f_user_id = $('#f_user_id').val();
                        d.f_date_from = $('#f_date_from').val();
                        d.f_date_to = $('#f_date_to').val();
                    }
                },
                columns: [
                    { data: 'created_at' },
                    { data: 'user_nama' },
                    { data: 'user_level' },
                    { data: 'modul' },
                    { data: 'aksi', orderable: false },
                    { data: 'keterangan' },
                    { data: 'ip_address' },
                    { data: 'aksi_btn', orderable: false, className: 'text-center' },
                ],
                pageLength: 25,
                language: {
                    processing: 'Memuat...',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    paginate: { first: 'Awal', last: 'Akhir', next: 'Next', previous: 'Prev' }
                }
            });

            $('#btnFilter').on('click', function () {
                table.ajax.reload();
            });
        })();

        function showDetail(id) {
            fetch('<?= base_url('audit_log/ajax_detail/') ?>' + id)
                .then(r => r.json())
                .then(d => {
                    document.getElementById('detailKeterangan').textContent = d.keterangan || '';
                    document.getElementById('detailLama').textContent = d.data_lama
                        ? JSON.stringify(d.data_lama, null, 2)
                        : '(tidak ada data)';
                    document.getElementById('detailBaru').textContent = d.data_baru
                        ? JSON.stringify(d.data_baru, null, 2)
                        : '(tidak ada data)';
                    new bootstrap.Modal(document.getElementById('detailModal')).show();
                })
                .catch(() => alert('Gagal memuat detail.'));
        }
    </script>
</body>

</html>