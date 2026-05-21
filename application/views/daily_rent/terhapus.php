<!-- ============================================================
     FILE: terhapus.php
     PATH: application/views/daily_rent/terhapus.php
     ============================================================ -->
<!DOCTYPE html>
<html lang="en">

<head><?php $this->load->view('partials/head') ?></head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0"><i class="fas fa-trash-restore text-warning"></i> <?= $title ?></h1>
                        <a href="<?= base_url('daily_rent') ?>" class="btn btn-secondary btn-sm"><i
                                class="fas fa-arrow-left"></i> Kembali</a>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $this->session->flashdata('success') ?><button type="button" class="btn-close"
                                data-bs-dismiss="alert"></button></div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $this->session->flashdata('error') ?><button type="button" class="btn-close"
                                data-bs-dismiss="alert"></button></div>
                    <?php endif ?>

                    <div class="card shadow mb-4">
                        <div class="card-header bg-warning py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-trash"></i> Data Terhapus
                                (<?= count($rents ?? []) ?>)</h6>
                            <?php if (!empty($rents)): ?>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-light btn-sm" id="btnBulkRestore"><i
                                            class="fas fa-trash-restore"></i> Restore Terpilih</button>
                                    <button class="btn btn-danger btn-sm" id="btnBulkHapus"><i class="fas fa-trash"></i>
                                        Hapus Permanen Terpilih</button>
                                </div>
                            <?php endif ?>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($rents)): ?>
                                <div class="text-center py-5 text-muted"><i
                                        class="fas fa-inbox fa-3x mb-3 d-block"></i><em>Tidak ada data terhapus.</em></div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0" style="font-size:0.82rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="3%" class="text-center"><input type="checkbox" id="checkAll">
                                                </th>
                                                <th>No Rent</th>
                                                <th>Customer</th>
                                                <th>Periode</th>
                                                <th>Status</th>
                                                <th>Dihapus</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rents ?? [] as $r): ?>
                                                <tr>
                                                    <td class="text-center"><input type="checkbox" class="check-item"
                                                            value="<?= $r->id ?>"></td>
                                                    <td><strong
                                                            style="font-family:monospace;color:#4e73df;"><?= htmlspecialchars($r->no_rent) ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($r->nama_customer ?? '-') ?></td>
                                                    <td>
                                                        <small><?= !empty($r->rent_start_date) ? date('d/m/Y', strtotime($r->rent_start_date)) : '-' ?></small>
                                                        <small class="text-muted"> – </small>
                                                        <small><?= !empty($r->rent_end_date) ? date('d/m/Y', strtotime($r->rent_end_date)) : '-' ?></small>
                                                    </td>
                                                    <td><span
                                                            class="badge badge-secondary"><?= htmlspecialchars($r->status_rent ?? '-') ?></span>
                                                    </td>
                                                    <td><small
                                                            class="text-muted"><?= !empty($r->deleted_at) ? date('d/m/Y H:i', strtotime($r->deleted_at)) : '-' ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('daily_rent/restore/' . $r->id) ?>"
                                                            class="btn btn-warning btn-sm"
                                                            onclick="return confirm('Restore order <?= htmlspecialchars($r->no_rent) ?>?')">
                                                            <i class="fas fa-trash-restore"></i> Restore
                                                        </a>
                                                        <a href="<?= base_url('daily_rent/hapus_permanen/' . $r->id) ?>"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Hapus PERMANEN order <?= htmlspecialchars($r->no_rent) ?>? Tidak bisa dipulihkan!')">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>
    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            const BASE = '<?= base_url() ?>';
            const CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
            let CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';

            $('#checkAll').on('change', function () { $('.check-item').prop('checked', $(this).is(':checked')); });

            function getChecked() { return $('.check-item:checked').map(function () { return $(this).val(); }).get(); }

            $('#btnBulkRestore').on('click', function () {
                let ids = getChecked();
                if (!ids.length) { alert('Pilih minimal 1 data!'); return; }
                if (!confirm('Restore ' + ids.length + ' order?')) return;
                $.ajax({
                    url: BASE + 'daily_rent/bulk_restore', method: 'POST', data: { ids, [CSRF_NAME]: CSRF_HASH }, dataType: 'json',
                    success: function (res) { if (res.success) location.reload(); else alert(res.message); }
                });
            });

            $('#btnBulkHapus').on('click', function () {
                let ids = getChecked();
                if (!ids.length) { alert('Pilih minimal 1 data!'); return; }
                if (!confirm('Hapus PERMANEN ' + ids.length + ' order? Tidak bisa dipulihkan!')) return;
                $.ajax({
                    url: BASE + 'daily_rent/bulk_hapus_permanen', method: 'POST', data: { ids, [CSRF_NAME]: CSRF_HASH }, dataType: 'json',
                    success: function (res) { if (res.success) location.reload(); else alert(res.message); }
                });
            });
        });
    </script>
</body>

</html>