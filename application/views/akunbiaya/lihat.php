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
                <div class="container-xl">

                    <?php
                    $level    = $this->session->userdata('login')['user_level'] ?? '';
                    $can_edit = in_array($level, ['superadmin', 'admin_document']);
                    ?>

                    <!-- HEADER -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="mb-0"><?= $title ?></h2>
                        <?php if ($can_edit): ?>
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('akunbiaya/export') ?>" class="btn btn-danger btn-sm">
                                    <i class="fa fa-file-pdf me-1"></i>Export PDF
                                </a>
                                <a href="<?= base_url('akunbiaya/tambah') ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus me-1"></i>Tambah Akun
                                </a>
                            </div>
                        <?php endif ?>
                    </div>

                    <!-- FLASH MESSAGES -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- CARD -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="mb-0 fw-semibold text-primary">Daftar Akun Biaya</h6>
                                    <span class="badge bg-info" id="badgeCount">
                                        <?= count($akunbiaya ?? []) ?> Akun
                                    </span>
                                </div>
                                <form id="searchForm" class="d-flex gap-1" style="min-width: 250px">
                                    <input type="text" id="searchInput" class="form-control form-control-sm"
                                        placeholder="Cari tipe, kode, nama..." autocomplete="off">
                                    <button class="btn btn-primary btn-sm" type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm mb-0" id="dataTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th>Tipe Akun</th>
                                            <th>Kode Perkiraan</th>
                                            <th>Nama Akun</th>
                                            <th>Akun Induk</th>
                                            <?php if ($can_edit): ?>
                                                <th width="10%" class="text-center">Aksi</th>
                                            <?php endif ?>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <?php foreach ($akunbiaya as $index => $akun): ?>
                                            <tr>
                                                <td class="text-center"><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($akun->tipe_akun ?? '') ?></td>
                                                <td><strong><?= htmlspecialchars($akun->kode_perkiraan ?? '') ?></strong></td>
                                                <td><?= htmlspecialchars($akun->nama ?? '') ?></td>
                                                <td>
                                                    <?php if (!empty($akun->akun_induk)): ?>
                                                        <span class="badge bg-secondary"><?= htmlspecialchars($akun->akun_induk) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif ?>
                                                </td>
                                                <?php if ($can_edit): ?>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('akunbiaya/ubah/' . $akun->id) ?>"
                                                            class="btn btn-success btn-sm" title="Edit">
                                                            <i class="fa fa-pen"></i>
                                                        </a>
                                                        <a href="<?= base_url('akunbiaya/hapus/' . $akun->id) ?>"
                                                            class="btn btn-danger btn-sm" title="Hapus"
                                                            onclick="return confirm('Yakin hapus akun <?= htmlspecialchars($akun->nama ?? '') ?>?')">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </td>
                                                <?php endif ?>
                                            </tr>
                                        <?php endforeach ?>
                                        <?php if (empty($akunbiaya)): ?>
                                            <tr>
                                                <td colspan="<?= $can_edit ? 6 : 5 ?>" class="text-center text-muted py-4">
                                                    <i class="fa fa-inbox fa-2x d-block mb-2 opacity-25"></i>
                                                    Tidak ada data akun biaya.
                                                </td>
                                            </tr>
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

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {
            function doSearch() {
                const keyword = $('#searchInput').val().trim();
                $.post('<?= base_url('akunbiaya/search') ?>', { keyword }, function (response) {
                    $('#tableBody').html(response);
                    const isEmpty = $('#tableBody tr td[colspan]').length > 0;
                    const count = isEmpty ? 0 : $('#tableBody tr').length;
                    $('#badgeCount').text(count + ' Akun');
                });
            }

            $('#searchForm').on('submit', function (e) {
                e.preventDefault();
                doSearch();
            });

            let timeout;
            $('#searchInput').on('keyup', function () {
                clearTimeout(timeout);
                timeout = setTimeout(doSearch, 500);
            });

            $(document).on('keyup', function (e) {
                if (e.key === 'Escape') {
                    $('#searchInput').val('');
                    doSearch();
                }
            });
        });
    </script>
</body>

</html>