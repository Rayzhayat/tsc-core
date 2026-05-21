<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .import-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .status-selector {
            background: #f8f9fc;
            border: 2px solid #4e73df;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .status-option {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
        }

        .status-option:hover {
            border-color: #4e73df;
            background: #f8f9fc;
        }

        .status-option.selected {
            border-color: #1cc88a;
            background: #d4edda;
        }

        .status-option input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
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
                            <i class="fas fa-clipboard-check text-primary"></i> Preview Import Pengeluaran
                        </h1>
                    </div>

                    <!-- Import Summary -->
                    <div class="import-summary shadow">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <i class="fas fa-file-excel fa-3x mb-2"></i>
                                <h5>Total Baris</h5>
                                <h2><?= $import_data['total_rows'] ?></h2>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-check-circle fa-3x mb-2"></i>
                                <h5>Valid</h5>
                                <h2 class="text-success"><?= count($import_data['validated']) ?></h2>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-exclamation-circle fa-3x mb-2"></i>
                                <h5>Error</h5>
                                <h2 class="text-danger"><?= count($import_data['errors']) ?></h2>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($import_data['errors'])): ?>
                        <div class="card shadow mb-4 border-left-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="m-0 font-weight-bold">
                                    <i class="fas fa-times-circle"></i> Baris dengan Error
                                    (<?= count($import_data['errors']) ?>)
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($import_data['errors'] as $error): ?>
                                    <div class="alert alert-danger">
                                        <strong>Baris <?= $error['row'] ?>:</strong>
                                        <ul class="mb-0">
                                            <?php foreach ($error['errors'] as $err): ?>
                                                <li><?= $err ?></li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    <?php endif ?>

                    <?php if (!empty($import_data['warnings'])): ?>
                        <div class="card shadow mb-4 border-left-warning">
                            <div class="card-header bg-warning">
                                <h6 class="m-0 font-weight-bold">
                                    <i class="fas fa-exclamation-triangle"></i> Peringatan
                                    (<?= count($import_data['warnings']) ?>)
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($import_data['warnings'] as $warning): ?>
                                    <div class="alert alert-warning">
                                        <strong>Baris <?= $warning['row'] ?>:</strong>
                                        <ul class="mb-0">
                                            <?php foreach ($warning['warnings'] as $warn): ?>
                                                <li><?= $warn ?></li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    <?php endif ?>

                    <?php if (!empty($import_data['validated'])): ?>
                        <!-- ✅ NEW: Status Selector -->
                        <div class="card shadow mb-4 border-left-primary">
                            <div class="card-header bg-gradient-primary text-white">
                                <h6 class="m-0 font-weight-bold">
                                    <i class="fas fa-tasks"></i> Pilih Status untuk Data Import
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="status-selector">
                                    <p class="text-primary font-weight-bold mb-3">
                                        <i class="fas fa-info-circle"></i>
                                        Pilih status yang akan diterapkan untuk semua data yang diimport:
                                    </p>

                                    <form action="<?= base_url('pengeluaran/execute_import') ?>" method="post"
                                        id="importForm">

                                        <!-- Option 1: Approved (Direct Expense) -->
                                        <div class="status-option" onclick="selectStatus('approved')">
                                            <label class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                                <input type="radio" name="import_status" value="approved"
                                                    id="status_approved" checked>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-check-circle fa-2x text-success mr-3"></i>
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold">✅ Approved - Direct Expense
                                                            </h6>
                                                            <small class="text-muted">
                                                                Pengeluaran langsung yang sudah disetujui (tidak dari
                                                                tagihan vendor)
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <!-- Option 2: Paid (From Invoice) -->
                                        <div class="status-option" onclick="selectStatus('paid')">
                                            <label class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                                <input type="radio" name="import_status" value="paid" id="status_paid">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-money-bill-wave fa-2x text-primary mr-3"></i>
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold">💰 Paid - Invoice Payment</h6>
                                                            <small class="text-muted">
                                                                Pembayaran yang sudah dilunasi (seolah-olah dari tagihan
                                                                vendor yang sudah dibayar)
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <!-- Option 3: Pending Approval -->
                                        <div class="status-option" onclick="selectStatus('pending')">
                                            <label class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                                <input type="radio" name="import_status" value="pending"
                                                    id="status_pending">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-clock fa-2x text-warning mr-3"></i>
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold">⏳ Pending - Waiting Approval
                                                            </h6>
                                                            <small class="text-muted">
                                                                Menunggu persetujuan dari atasan (perlu di-approve manual)
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <hr>

                                        <div class="alert alert-info">
                                            <i class="fas fa-lightbulb"></i>
                                            <strong>Rekomendasi:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Pilih <strong>"Approved"</strong> jika ini pengeluaran langsung tanpa
                                                    invoice</li>
                                                <li>Pilih <strong>"Paid"</strong> jika data Excel ini sebenarnya invoice
                                                    yang sudah dibayar</li>
                                                <li>Pilih <strong>"Pending"</strong> jika perlu review/approval dulu sebelum
                                                    final</li>
                                            </ul>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                                <i class="fas fa-check"></i> Import Sekarang
                                            </button>
                                            <a href="<?= base_url('pengeluaran/import') ?>"
                                                class="btn btn-secondary btn-lg px-5">
                                                <i class="fas fa-times"></i> Batal
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Data Table -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    Preview Data yang Akan Diimport (<?= count($import_data['validated']) ?> baris)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Tipe</th>
                                                <th>Tanggal</th>
                                                <th>Kode Biaya</th>
                                                <th>Vendor</th>
                                                <th>Nominal</th>
                                                <th>PPN</th>
                                                <th>PPH</th>
                                                <th>Total Bayar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = 1;
                                            $grand_total = 0;
                                            foreach ($import_data['validated'] as $item):
                                                $total_bayar = ($item['nominal'] + $item['ppn']) - $item['pph'];
                                                $grand_total += $total_bayar;
                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td>
                                                        <span
                                                            class="badge badge-<?= $item['tipe'] == 'V' ? 'primary' : 'info' ?>">
                                                            <?= $item['tipe'] == 'V' ? 'VENDOR' : 'MANUAL' ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($item['tanggal'])) ?></td>
                                                    <td><?= $item['kode_biaya'] ?></td>
                                                    <td><?= $item['vendor_kode'] ?: '-' ?></td>
                                                    <td class="text-right">Rp
                                                        <?= number_format($item['nominal'], 0, ',', '.') ?></td>
                                                    <td class="text-right">Rp <?= number_format($item['ppn'], 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-right">Rp <?= number_format($item['pph'], 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong>Rp <?= number_format($total_bayar, 0, ',', '.') ?></strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <th colspan="8" class="text-right">GRAND TOTAL:</th>
                                                <th class="text-right">
                                                    <strong class="text-primary">
                                                        Rp <?= number_format($grand_total, 0, ',', '.') ?>
                                                    </strong>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Tidak ada data valid yang dapat diimport. Silakan perbaiki error di atas.
                        </div>
                        <div class="text-center">
                            <a href="<?= base_url('pengeluaran/import') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali ke Import
                            </a>
                        </div>
                    <?php endif ?>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        function selectStatus(status) {
            // Remove all selected classes
            document.querySelectorAll('.status-option').forEach(el => {
                el.classList.remove('selected');
            });

            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');

            // Check the radio button
            document.getElementById('status_' + status).checked = true;
        }

        // Auto-select on page load
        document.addEventListener('DOMContentLoaded', function () {
            const checkedRadio = document.querySelector('input[name="import_status"]:checked');
            if (checkedRadio) {
                checkedRadio.closest('.status-option').classList.add('selected');
            }
        });

        // Prevent accidental navigation
        document.getElementById('importForm').addEventListener('submit', function (e) {
            const confirmed = confirm('Yakin ingin mengimport ' + <?= count($import_data['validated']) ?> + ' data pengeluaran?');
            if (!confirmed) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>