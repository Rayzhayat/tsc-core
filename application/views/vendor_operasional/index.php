<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .table-vendor thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .table-vendor tbody td {
            font-size: 0.88rem;
            vertical-align: middle;
        }

        .vendor-name {
            font-weight: 600;
            color: #3a3b45;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- HEADER -->
                    <?php
                    $level = $this->session->userdata('login')['user_level'] ?? '';
                    $can_manage = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);
                    ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-building text-primary"></i> <?= $title ?>
                        </h1>
                        <?php if ($can_manage): ?>
                            <a href="<?= base_url('vendor_operasional/tambah') ?>" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Tambah Vendor
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- ALERTS -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif; ?>

                    <!-- INFO -->
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fas fa-info-circle"></i>
                        Master vendor ini khusus untuk kebutuhan <strong>operasional</strong> (FTL Non SPX, dll).
                        Terpisah dari master vendor finance.
                    </div>

                    <!-- TABLE -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-gradient-primary">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-list"></i> Daftar Vendor Operasional
                                    </h6>
                                </div>
                                <div class="col-auto">
                                    <span class="badge badge-light">
                                        <i class="fas fa-building"></i> <?= count($vendors) ?> Vendor
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Search bar -->
                            <div class="px-3 pt-3 pb-2">
                                <div class="input-group input-group-sm" style="max-width:300px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" id="searchVendor" class="form-control"
                                        placeholder="Cari nama vendor...">
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-vendor mb-0" id="vendorTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th>Nama Vendor</th>
                                            <th width="12%" class="text-center">Ditambahkan</th>
                                            <?php if ($can_manage): ?>
                                                <th width="12%" class="text-center">Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($vendors)): ?>
                                            <tr>
                                                <td colspan="<?= $can_manage ? 4 : 3 ?>"
                                                    class="text-center text-muted py-5">
                                                    <i class="fas fa-building fa-3x mb-3 d-block text-gray-300"></i>
                                                    <em>Belum ada vendor operasional. Silakan tambahkan.</em>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1;
                                            foreach ($vendors as $v): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <i class="fas fa-building text-primary mr-2"
                                                            style="font-size:11px;"></i>
                                                        <span
                                                            class="vendor-name"><?= htmlspecialchars($v->nama_vendor) ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <small class="text-muted">
                                                            <?= !empty($v->created_at) ? date('d/m/Y', strtotime($v->created_at)) : '-' ?>
                                                        </small>
                                                    </td>
                                                    <?php if ($can_manage): ?>
                                                        <td class="text-center">
                                                            <a href="<?= base_url('vendor_operasional/ubah/' . $v->id) ?>"
                                                                class="btn btn-outline-warning btn-sm" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button class="btn btn-outline-danger btn-sm btn-hapus"
                                                                data-id="<?= $v->id ?>"
                                                                data-nama="<?= htmlspecialchars($v->nama_vendor) ?>" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
            const BASE = '<?= base_url() ?>';

            // LIVE SEARCH
            $('#searchVendor').on('input', function () {
                let q = $(this).val().toLowerCase();
                $('#vendorTable tbody tr').each(function () {
                    let nama = $(this).find('.vendor-name').text().toLowerCase();
                    $(this).toggle(q === '' || nama.includes(q));
                });
            });

            // HAPUS — AJAX
            $(document).on('click', '.btn-hapus', function () {
                let nama = $(this).data('nama');
                let id = $(this).data('id');
                if (!confirm('Hapus vendor "' + nama + '"?\nData tidak bisa dipulihkan.')) return;

                let btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.ajax({
                    url: BASE + 'vendor_operasional/hapus',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            location.reload();
                        } else {
                            alert(res.message || 'Gagal!');
                            btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                        }
                    },
                    error: function () {
                        alert('Gagal koneksi ke server!');
                        btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                    }
                });
            });

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });
    </script>
</body>

</html>