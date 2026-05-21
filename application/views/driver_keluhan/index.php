<!DOCTYPE html>
<html lang="id">

<head><?php $this->load->view('partials/head') ?></head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="mb-0"><i class="fas fa-truck text-primary me-2"></i>Keluhan Driver</h2>
                            <small class="text-muted">Laporan masuk dari driver lapangan</small>
                        </div>
                        <a href="<?= base_url('driver_keluhan') ?>" target="_blank" class="btn btn-success">
                            <i class="fas fa-external-link-alt me-1"></i> Buka Form Driver
                        </a>
                    </div>

                    <!-- STAT CARDS -->
                    <?php
                    $total = count($keluhans);
                    $baru = count(array_filter($keluhans, fn($k) => $k->status === 'baru'));
                    $proses = count(array_filter($keluhans, fn($k) => $k->status === 'diproses'));
                    $selesai = count(array_filter($keluhans, fn($k) => $k->status === 'selesai'));
                    ?>
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="card text-center shadow-sm">
                                <div class="card-body py-3">
                                    <div class="fs-2 fw-bold text-primary"><?= $total ?></div>
                                    <div class="text-muted small">Total Laporan</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="?status=baru" class="text-decoration-none">
                                <div class="card text-center shadow-sm border-danger">
                                    <div class="card-body py-3">
                                        <div class="fs-2 fw-bold text-danger"><?= $baru ?></div>
                                        <div class="text-muted small">Baru</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="?status=diproses" class="text-decoration-none">
                                <div class="card text-center shadow-sm border-warning">
                                    <div class="card-body py-3">
                                        <div class="fs-2 fw-bold text-warning"><?= $proses ?></div>
                                        <div class="text-muted small">Diproses</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="?status=selesai" class="text-decoration-none">
                                <div class="card text-center shadow-sm border-success">
                                    <div class="card-body py-3">
                                        <div class="fs-2 fw-bold text-success"><?= $selesai ?></div>
                                        <div class="text-muted small">Selesai</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- TABLE CARD -->
                    <div class="card shadow">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-list me-1"></i> Daftar Laporan
                                <?php if ($filter = $this->input->get('status')): ?>
                                    &mdash; <span
                                        class="badge bg-<?= $filter === 'baru' ? 'danger' : ($filter === 'diproses' ? 'warning' : 'success') ?>"><?= strtoupper($filter) ?></span>
                                <?php endif ?>
                            </h6>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="date" id="tglDari" class="form-control form-control-sm"
                                    style="width:130px">
                                <input type="date" id="tglSampai" class="form-control form-control-sm"
                                    style="width:130px">
                                <select id="filterStatus" class="form-select form-select-sm" style="width:110px">
                                    <option value="">Semua</option>
                                    <option value="baru" <?= $this->input->get('status') == 'baru' ? 'selected' : '' ?>>
                                        Baru</option>
                                    <option value="diproses" <?= $this->input->get('status') == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                    <option value="selesai" <?= $this->input->get('status') == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                                <button class="btn btn-sm btn-success" onclick="doExport()">
                                    <i class="fas fa-file-excel me-1"></i> Export Excel
                                </button>
                                <a href="<?= base_url('driver_keluhan/admin') ?>"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sync-alt"></i> Reset
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle mb-0" id="tabelKeluhan"
                                    style="font-size:.84rem">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30">#</th>
                                            <th>Driver</th>
                                            <th>No Polisi</th>
                                            <th>Vendor</th>
                                            <th>No LT</th>
                                            <th>Rute</th>
                                            <th>Keluhan</th>
                                            <th class="text-center" width="50">Foto</th>
                                            <th class="text-center" width="100">Status</th>
                                            <th class="text-center" width="110">Waktu</th>
                                            <th class="text-center" width="120">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($keluhans)): ?>
                                            <tr>
                                                <td colspan="11" class="text-center py-4 text-muted">Belum ada laporan
                                                    masuk.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $badge = ['baru' => 'danger', 'diproses' => 'warning', 'selesai' => 'success'];
                                            $icon = ['baru' => 'circle', 'diproses' => 'spinner fa-spin', 'selesai' => 'check-circle'];
                                            foreach ($keluhans as $i => $k):
                                                ?>
                                                <tr id="row-<?= $k->id ?>">
                                                    <td><?= $i + 1 ?></td>
                                                    <td><strong><?= htmlspecialchars($k->nama_driver) ?></strong></td>
                                                    <td><?= htmlspecialchars($k->no_polisi) ?></td>
                                                    <td><?= htmlspecialchars($k->vendor ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($k->no_lt ?: '-') ?></td>
                                                    <td>
                                                        <?php if ($k->origin || $k->destinasi): ?>
                                                            <span
                                                                class="badge bg-light text-dark border"><?= htmlspecialchars($k->origin) ?></span>
                                                            <i class="fas fa-arrow-right text-muted mx-1"
                                                                style="font-size:.65rem"></i>
                                                            <span
                                                                class="badge bg-light text-dark border"><?= htmlspecialchars($k->destinasi) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td style="max-width:200px">
                                                        <div class="text-truncate" title="<?= htmlspecialchars($k->keluhan) ?>">
                                                            <?= htmlspecialchars($k->keluhan) ?>
                                                        </div>
                                                        <?php if ($k->catatan_admin): ?>
                                                            <div class="text-truncate text-info small mt-1"
                                                                title="Catatan: <?= htmlspecialchars($k->catatan_admin) ?>">
                                                                <i
                                                                    class="fas fa-sticky-note me-1"></i><?= htmlspecialchars($k->catatan_admin) ?>
                                                            </div>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($k->foto): ?>
                                                            <a href="<?= base_url($k->foto) ?>" target="_blank"
                                                                class="btn btn-sm btn-outline-info py-0 px-2">
                                                                <i class="fas fa-image"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-center" id="status-cell-<?= $k->id ?>">
                                                        <span class="badge bg-<?= $badge[$k->status] ?>">
                                                            <i
                                                                class="fas fa-<?= $icon[$k->status] ?> me-1"></i><?= strtoupper($k->status) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center" style="white-space:nowrap;font-size:.78rem">
                                                        <?= date('d/m/Y H:i', strtotime($k->created_at)) ?>
                                                    </td>
                                                    <td class="text-center" style="white-space:nowrap">
                                                        <!-- Detail -->
                                                        <button class="btn btn-primary btn-sm py-0 px-2 me-1"
                                                            onclick="bukaDetail(<?= $k->id ?>)" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <!-- Update Status -->
                                                        <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                                            onclick="bukaUpdateStatus(<?= $k->id ?>, '<?= $k->status ?>', '<?= htmlspecialchars($k->catatan_admin ?? '', ENT_QUOTES) ?>')"
                                                            title="Update Status">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <!-- Hapus -->
                                                        <button class="btn btn-danger btn-sm py-0 px-2"
                                                            onclick="hapusKeluhan(<?= $k->id ?>, '<?= htmlspecialchars($k->nama_driver, ENT_QUOTES) ?>')"
                                                            title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </tbody>
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
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-alt me-2"></i>Detail Laporan Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL UPDATE STATUS -->
    <div class="modal fade" id="modalUpdateStatus" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Update Status</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-2">
                    <input type="hidden" id="updateId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">Status</label>
                        <select id="updateStatus" class="form-select form-select-sm">
                            <option value="baru">🔴 Baru</option>
                            <option value="diproses">🟡 Diproses</option>
                            <option value="selesai">🟢 Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-semibold small mb-1">Catatan Admin <span
                                class="text-muted fw-normal">(opsional)</span></label>
                        <textarea id="updateCatatan" class="form-control form-control-sm" rows="3"
                            placeholder="Tulis catatan tindak lanjut..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm btn-primary" id="btnSimpanStatus" onclick="simpanStatus()">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            var $tbody = $('#tabelKeluhan tbody');
            if ($tbody.find('tr td[colspan]').length > 0) $tbody.empty();

            $('#tabelKeluhan').DataTable({
                order: [[9, 'desc']],
                columnDefs: [{ orderable: false, targets: [5, 6, 7, 8, 10] }],
                dom: "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6 d-flex justify-content-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2 align-items-center'<'col-sm-5'i><'col-sm-7 d-flex justify-content-end'p>>",
                language: {
                    search: '',
                    searchPlaceholder: '🔍 Cari driver, vendor, no polisi...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ laporan',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Tidak ada laporan yang cocok',
                    paginate: { previous: '&lsaquo;', next: '&rsaquo;' },
                    emptyTable: 'Belum ada laporan masuk.'
                },
                initComplete: function () {
                    // Style search input biar match sama Bootstrap 5
                    $('#tabelKeluhan_filter input')
                        .addClass('form-control form-control-sm')
                        .css('width', '260px');
                }
            });
        });

        // ── DETAIL MODAL ─────────────────────────────────────────────────────────
        function bukaDetail(id) {
            document.getElementById('modalBody').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>';
            var modal = new bootstrap.Modal(document.getElementById('modalDetail'));
            modal.show();
            fetch('<?= base_url('driver_keluhan/detail/') ?>' + id)
                .then(r => r.text())
                .then(html => { document.getElementById('modalBody').innerHTML = html; });
        }

        // ── UPDATE STATUS ────────────────────────────────────────────────────
        var _modalStatus = null;

        function bukaUpdateStatus(id, status, catatan) {
            document.getElementById('updateId').value = id;
            document.getElementById('updateStatus').value = status;
            document.getElementById('updateCatatan').value = catatan;
            if (!_modalStatus) _modalStatus = new bootstrap.Modal(document.getElementById('modalUpdateStatus'));
            _modalStatus.show();
        }

        function simpanStatus() {
            var id = document.getElementById('updateId').value;
            var status = document.getElementById('updateStatus').value;
            var catatan = document.getElementById('updateCatatan').value;
            var btn = document.getElementById('btnSimpanStatus');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

            fetch('<?= base_url("driver_keluhan/update_status/") ?>' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'status=' + encodeURIComponent(status) + '&catatan_admin=' + encodeURIComponent(catatan)
            })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';

                    if (res.success) {
                        _modalStatus.hide();

                        // Update badge status di baris tanpa reload
                        var badgeMap = { baru: 'danger', diproses: 'warning', selesai: 'success' };
                        var iconMap = { baru: 'circle', diproses: 'spinner fa-spin', selesai: 'check-circle' };
                        document.getElementById('status-cell-' + id).innerHTML =
                            '<span class="badge bg-' + badgeMap[status] + '">' +
                            '<i class="fas fa-' + iconMap[status] + ' me-1"></i>' +
                            status.toUpperCase() + '</span>';

                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Status diperbarui!', showConfirmButton: false, timer: 1800, timerProgressBar: true });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menyimpan status.' });
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi gagal.' });
                });
        }

        // ── HAPUS ────────────────────────────────────────────────────────────
        function hapusKeluhan(id, nama) {
            Swal.fire({
                title: 'Hapus Laporan?',
                html: 'Laporan dari <strong>' + nama + '</strong> akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch('<?= base_url("driver_keluhan/hapus") ?>/' + id, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    })
                    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi gagal.' }));
            });
        }

        // ── EXPORT ───────────────────────────────────────────────────────────
        function doExport() {
            var status = document.getElementById('filterStatus').value;
            var tglDari = document.getElementById('tglDari').value;
            var tglSampai = document.getElementById('tglSampai').value;
            var url = '<?= base_url("driver_keluhan/export") ?>?';
            if (status) url += 'status=' + status + '&';
            if (tglDari) url += 'tgl_dari=' + tglDari + '&';
            if (tglSampai) url += 'tgl_sampai=' + tglSampai + '&';
            window.location.href = url;
        }
    </script>
</body>

</html>