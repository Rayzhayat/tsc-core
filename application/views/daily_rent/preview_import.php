<!-- ============================================================
     FILE: preview_import.php
     PATH: application/views/daily_rent/preview_import.php
     ============================================================ -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .row-error {
            background: #fde8e8 !important;
        }

        .row-warning {
            background: #fff8e1 !important;
        }

        .row-ok {
            background: #e8f5e9 !important;
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
                        <h1 class="h3 mb-0"><i class="fas fa-eye text-primary"></i> Preview Import Daily Rent</h1>
                        <a href="<?= base_url('daily_rent/import') ?>" class="btn btn-secondary btn-sm"><i
                                class="fas fa-arrow-left"></i> Upload Ulang</a>
                    </div>

                    <?php
                    $import_data = $import_data ?? [];
                    $validated = $import_data['validated'] ?? [];
                    $errors = $import_data['errors'] ?? [];
                    $warnings = $import_data['warnings'] ?? [];
                    $total_rows = $import_data['total_rows'] ?? 0;
                    ?>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-left-primary shadow py-2">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Baris
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $total_rows ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-success shadow py-2">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">✅ Siap Import
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-success"><?= count($validated) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-warning shadow py-2">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">⚠️ Peringatan
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-warning"><?= count($warnings) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-danger shadow py-2">
                                <div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">❌ Error</div>
                                    <div class="h5 mb-0 font-weight-bold text-danger"><?= count($errors) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Errors -->
                    <?php if (!empty($errors)): ?>
                        <div class="card shadow mb-4 border-left-danger">
                            <div class="card-header bg-danger text-white py-2">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-times-circle"></i> Baris Error (Tidak akan
                                    diimport)</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="80">Baris</th>
                                            <th>Error</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($errors as $err): ?>
                                            <tr class="row-error">
                                                <td class="text-center font-weight-bold text-danger"><?= $err['row'] ?></td>
                                                <td><?php foreach ($err['errors'] as $e): ?>
                                                        <small class="d-block text-danger"><i class="fas fa-times"></i>
                                                            <?= htmlspecialchars($e) ?></small>
                                                    <?php endforeach ?>
                                                </td>
                                                <td><small
                                                        class="text-muted"><?= htmlspecialchars(implode(' | ', array_filter(is_array($err['data']) ? $err['data'] : []))) ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif ?>

                    <!-- Warnings -->
                    <?php if (!empty($warnings)): ?>
                        <div class="card shadow mb-4 border-left-warning">
                            <div class="card-header bg-warning text-white py-2">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-exclamation-triangle"></i> Baris
                                    Peringatan (Tetap diimport)</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="80">Baris</th>
                                            <th>Peringatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($warnings as $warn): ?>
                                            <tr class="row-warning">
                                                <td class="text-center font-weight-bold text-warning"><?= $warn['row'] ?></td>
                                                <td><?php foreach ($warn['warnings'] as $w): ?>
                                                        <small class="d-block text-warning"><i
                                                                class="fas fa-exclamation-triangle"></i>
                                                            <?= htmlspecialchars($w) ?></small>
                                                    <?php endforeach ?>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif ?>

                    <!-- Preview Table -->
                    <?php if (!empty($validated)): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header bg-success text-white py-2">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-check-circle"></i> Preview Data yang Akan
                                    Diimport (<?= count($validated) ?> baris)</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Customer</th>
                                                <th>Lokasi</th>
                                                <th>PIC</th>
                                                <th>Tanggal Mulai</th>
                                                <th>Tanggal Selesai</th>
                                                <th>Durasi</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($validated as $i => $row):
                                                $dur = (!empty($row['rent_start_date']) && !empty($row['rent_end_date']))
                                                    ? round((strtotime($row['rent_end_date']) - strtotime($row['rent_start_date'])) / 86400) . ' hari' : '-';
                                                ?>
                                                <tr class="row-ok">
                                                    <td class="text-center"><?= $i + 1 ?></td>
                                                    <td><?= htmlspecialchars($row['customer_name'] ?? $row['customer_raw'] ?? '-') ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['location'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($row['pic_customer'] ?? '-') ?></td>
                                                    <td><small>
                                                            <?= !empty($row['rent_start_date']) ? date('d/m/Y', strtotime($row['rent_start_date'])) : '-' ?>
                                                            <?= !empty($row['rent_start_time']) ? $row['rent_start_time'] : '' ?>
                                                        </small></td>
                                                    <td><small>
                                                            <?= !empty($row['rent_end_date']) ? date('d/m/Y', strtotime($row['rent_end_date'])) : '-' ?>
                                                            <?= !empty($row['rent_end_time']) ? $row['rent_end_time'] : '' ?>
                                                        </small></td>
                                                    <td class="text-center"><span class="badge badge-info"><?= $dur ?></span>
                                                    </td>
                                                    <td><small
                                                            class="text-muted"><?= htmlspecialchars($row['notes'] ?? '-') ?></small>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-body">
                                <form action="<?= base_url('daily_rent/execute_import') ?>" method="post">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <p class="mb-1">
                                                <i class="fas fa-info-circle text-primary"></i>
                                                <strong><?= count($validated) ?> order</strong> siap diimport dengan status
                                                awal <strong>Sourcing Vendor</strong>.
                                                Unit kendaraan ditambahkan di halaman detail masing-masing order.
                                            </p>
                                            <?php if (count($errors) > 0): ?>
                                                <small class="text-danger"><i class="fas fa-times-circle"></i>
                                                    <?= count($errors) ?> baris tidak diimport karena error.</small>
                                            <?php endif ?>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <a href="<?= base_url('daily_rent/import') ?>" class="btn btn-secondary mr-2"><i
                                                    class="fas fa-times"></i> Batal</a>
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Lanjutkan import <?= count($validated) ?> order Daily Rent?')">
                                                <i class="fas fa-check"></i> Eksekusi Import
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Tidak ada data valid. Silakan perbaiki file dan
                            upload ulang.
                        </div>
                        <a href="<?= base_url('daily_rent/import') ?>" class="btn btn-primary"><i class="fas fa-redo"></i>
                            Upload Ulang</a>
                    <?php endif ?>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>
    <?php $this->load->view('partials/js') ?>
</body>

</html>