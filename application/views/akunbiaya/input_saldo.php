<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <link href="<?= base_url('sb-admin/vendor/datatables/dataTables.bootstrap4.min.css') ?>" rel="stylesheet">
    <style>
        .input-saldo {
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }

        .input-saldo:focus {
            background-color: #fff3cd;
            border-color: #ffc107;
        }

        .card-stats {
            border-left: 4px solid;
        }

        .has-change {
            background-color: #d4edda !important;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-coins text-warning"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('akunbiaya') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <hr>

                    <!-- ALERT -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Info Card -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Petunjuk:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Input saldo awal untuk setiap akun biaya yang digunakan</li>
                            <li>Gunakan format angka tanpa titik/koma (misal: 1000000)</li>
                            <li>Klik tombol "Simpan Semua" untuk menyimpan perubahan</li>
                            <li>Atau klik ikon <i class="fas fa-save text-success"></i> untuk menyimpan per akun</li>
                        </ul>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <?php
                        $total_asset = 0;
                        $total_liab = 0;
                        $total_equity = 0;
                        $count_with_saldo = 0;

                        foreach ($akun_biaya as $akun) {
                            $saldo = $akun->saldo_awal ?? 0;
                            $tipe = strtoupper($akun->tipe_akun ?? '');

                            if ($saldo > 0)
                                $count_with_saldo++;

                            if (in_array($tipe, ['ASSET', 'BANK'])) {
                                $total_asset += $saldo;
                            }
                            if ($tipe == 'LIAB' || $tipe == 'LIABILITY') {
                                $total_liab += $saldo;
                            }
                            if ($tipe == 'EQUITY') {
                                $total_equity += $saldo;
                            }
                        }
                        ?>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card card-stats border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Asset
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_asset, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card card-stats border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Total Liabilitas
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_liab, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card card-stats border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Equity
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_equity, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card card-stats border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Akun dengan Saldo
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $count_with_saldo ?> / <?= count($akun_biaya) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Card -->
                    <form action="<?= base_url('akunbiaya/proses_input_saldo') ?>" method="post" id="formSaldo">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-gradient-warning">
                                <h6 class="m-0 font-weight-bold text-white">
                                    <i class="fas fa-table"></i> Input Saldo Awal Akun Biaya
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="dataTable" width="100%"
                                        cellspacing="0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="40">No</th>
                                                <th>Kode Perkiraan</th>
                                                <th>Nama Akun</th>
                                                <th width="120">Tipe</th>
                                                <th width="200">Saldo Awal</th>
                                                <th width="80">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            <?php foreach ($akun_biaya as $akun): ?>
                                                <tr data-id="<?= $akun->id ?>">
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <span class="badge badge-secondary">
                                                            <?= $akun->kode_perkiraan ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($akun->nama) ?></td>
                                                    <td>
                                                        <?php
                                                        $tipe = $akun->tipe_akun ?? '-';

                                                        $badge_color = 'secondary';
                                                        switch (strtoupper($tipe)) {
                                                            case 'ASSET':
                                                            case 'BANK':
                                                                $badge_color = 'success';
                                                                break;  // BANK juga asset
                                                            case 'LIAB':
                                                            case 'LIABILITY':
                                                                $badge_color = 'danger';
                                                                break;
                                                            case 'EQUITY':
                                                                $badge_color = 'primary';
                                                                break;
                                                            case 'REVENUE':
                                                            case 'REVE':
                                                                $badge_color = 'info';
                                                                break;
                                                            case 'COGS':
                                                                $badge_color = 'warning';
                                                                break;
                                                            case 'EXPENSE':
                                                            case 'EXPS':
                                                                $badge_color = 'dark';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge badge-<?= $badge_color ?>">
                                                            <?= htmlspecialchars($tipe) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="saldo[<?= $akun->id ?>]"
                                                            class="form-control input-saldo"
                                                            value="<?= number_format($akun->saldo_awal, 0, ',', '.') ?>"
                                                            data-original="<?= $akun->saldo_awal ?>" placeholder="0">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-success btn-sm btn-save-single"
                                                            data-id="<?= $akun->id ?>" title="Simpan akun ini">
                                                            <i class="fas fa-save"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-warning btn-lg px-5 shadow-sm">
                                        <i class="fas fa-save"></i> Simpan Semua Saldo
                                    </button>
                                    <a href="<?= base_url('akunbiaya') ?>" class="btn btn-secondary btn-lg px-5">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="<?= base_url('sb-admin/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('sb-admin/vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>

    <script>
        $(document).ready(function () {
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                },
                "pageLength": 50,
                "order": [[1, 'asc']],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 5]
                }]
            });

            // Format number on input
            $('.input-saldo').on('keyup', function () {
                var value = $(this).val().replace(/\./g, '');
                if (value !== '') {
                    $(this).val(formatNumber(value));
                }

                // Highlight row if changed
                var original = $(this).data('original');
                var current = parseNumber($(this).val());

                if (current != original) {
                    $(this).closest('tr').addClass('has-change');
                } else {
                    $(this).closest('tr').removeClass('has-change');
                }
            });

            // Save single
            $('.btn-save-single').on('click', function () {
                var btn = $(this);
                var id = btn.data('id');
                var input = $('input[name="saldo[' + id + ']"]');
                var saldo = input.val();

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '<?= base_url("akunbiaya/ajax_update_saldo") ?>',
                    method: 'POST',
                    data: { id: id, saldo: saldo },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            alert('✅ ' + response.message);
                            input.data('original', parseNumber(saldo));
                            input.closest('tr').removeClass('has-change');
                            location.reload(); // Reload untuk update statistics
                        } else {
                            alert('❌ ' + response.message);
                        }
                    },
                    error: function () {
                        alert('❌ Terjadi kesalahan!');
                    },
                    complete: function () {
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i>');
                    }
                });
            });

            // Form submit validation
            $('#formSaldo').on('submit', function (e) {
                var hasChange = $('.has-change').length;

                if (hasChange === 0) {
                    e.preventDefault();
                    alert('⚠️ Tidak ada perubahan data!');
                    return false;
                }

                if (!confirm('Simpan ' + hasChange + ' perubahan saldo?')) {
                    e.preventDefault();
                    return false;
                }

                return true;
            });

            function formatNumber(num) {
                var n = parseInt(num) || 0;
                return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function parseNumber(str) {
                return parseFloat(str.replace(/\./g, '')) || 0;
            }

            // Auto hide alert
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>

</html>