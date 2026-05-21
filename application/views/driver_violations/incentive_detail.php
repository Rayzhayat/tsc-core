<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-calculator text-success"></i> Incentive Detail - <?= htmlspecialchars($driver->nama_driver ?? 'Driver') ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('driver_violations/incentive') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                    <!-- Driver Info Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-user-tie"></i> Driver Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Name:</strong></td>
                                            <td><?= htmlspecialchars($driver->nama_driver ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>NIK:</strong></td>
                                            <td><?= htmlspecialchars($driver->nik ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>SIM:</strong></td>
                                            <td><?= htmlspecialchars($driver->sim ?? '-') ?> (<?= htmlspecialchars($driver->tipe_sim ?? '-') ?>)</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Phone:</strong></td>
                                            <td><?= htmlspecialchars($driver->no_hp ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge badge-<?= ($driver->status_driver ?? 'aktif') == 'aktif' ? 'success' : 'secondary' ?>">
                                                    <?= strtoupper($driver->status_driver ?? 'aktif') ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Rating:</strong></td>
                                            <td>
                                                <span class="badge badge-warning badge-lg">
                                                    ⭐ <?= number_format($driver->rating ?? 5.0, 1) ?> / 5.0
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Incentive Calculation -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-success">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-money-bill-wave"></i> Incentive Calculation (Period: <?= date('F Y', strtotime($month ?? date('Y-m') . '-01')) ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="50%">Component</th>
                                            <th width="20%" class="text-center">Details</th>
                                            <th width="30%" class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Base Salary -->
                                        <tr>
                                            <td><strong>Base Salary</strong></td>
                                            <td class="text-center">-</td>
                                            <td class="text-right">
                                                <strong>Rp <?= number_format($incentive['base_salary'] ?? 0, 0, ',', '.') ?></strong>
                                            </td>
                                        </tr>

                                        <!-- Trip Bonus -->
                                        <tr class="table-success">
                                            <td>
                                                <i class="fas fa-plus-circle text-success"></i> Trip Bonus
                                                <small class="text-muted d-block">Rp 50,000 per trip</small>
                                            </td>
                                            <td class="text-center">
                                                <strong><?= $incentive['trip_count'] ?? 0 ?> trips</strong>
                                            </td>
                                            <td class="text-right text-success">
                                                <strong>+Rp <?= number_format($incentive['trip_bonus'] ?? 0, 0, ',', '.') ?></strong>
                                            </td>
                                        </tr>

                                        <!-- Performance Bonus -->
                                        <tr class="table-success">
                                            <td>
                                                <i class="fas fa-plus-circle text-success"></i> Performance Bonus
                                                <small class="text-muted d-block">Up to 20% based on rating</small>
                                            </td>
                                            <td class="text-center">
                                                Rating: <?= number_format($incentive['performance_rating'] ?? 0, 1) ?>
                                            </td>
                                            <td class="text-right text-success">
                                                <strong>+Rp <?= number_format($incentive['performance_bonus'] ?? 0, 0, ',', '.') ?></strong>
                                            </td>
                                        </tr>

                                        <!-- Fuel Efficiency Bonus -->
                                        <tr class="table-success">
                                            <td>
                                                <i class="fas fa-plus-circle text-success"></i> Fuel Efficiency Bonus
                                                <small class="text-muted d-block">If ≥ 8 km/L</small>
                                            </td>
                                            <td class="text-center">
                                                <?= number_format($incentive['fuel_efficiency'] ?? 0, 2) ?> km/L
                                            </td>
                                            <td class="text-right text-success">
                                                <strong>+Rp <?= number_format($incentive['fuel_bonus'] ?? 0, 0, ',', '.') ?></strong>
                                            </td>
                                        </tr>

                                        <!-- Safety Bonus -->
                                        <tr class="table-success">
                                            <td>
                                                <i class="fas fa-plus-circle text-success"></i> Safety Bonus
                                                <small class="text-muted d-block">No pending violations</small>
                                            </td>
                                            <td class="text-center">
                                                <?php if (($performance['pending_violations'] ?? 0) == 0): ?>
                                                    <span class="badge badge-success">Clean</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger"><?= $performance['pending_violations'] ?? 0 ?> violations</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right text-success">
                                                <strong>+Rp <?= number_format($incentive['safety_bonus'] ?? 0, 0, ',', '.') ?></strong>
                                            </td>
                                        </tr>

                                        <!-- Total Bonus -->
                                        <tr class="bg-light">
                                            <td colspan="2" class="text-right"><strong>Total Bonus:</strong></td>
                                            <td class="text-right text-success">
                                                <strong>+Rp <?= number_format($incentive['total_bonus'] ?? 0, 0, ',', '.') ?></strong>
                                            </td>
                                        </tr>

                                        <!-- Penalties -->
                                        <?php if (($incentive['total_penalties'] ?? 0) > 0): ?>
                                            <tr class="table-danger">
                                                <td>
                                                    <i class="fas fa-minus-circle text-danger"></i> Violation Penalties
                                                </td>
                                                <td class="text-center">
                                                    <?= $performance['violation_count'] ?? 0 ?> pending
                                                </td>
                                                <td class="text-right text-danger">
                                                    <strong>-Rp <?= number_format($incentive['total_penalties'] ?? 0, 0, ',', '.') ?></strong>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <!-- Net Salary -->
                                        <tr class="table-primary">
                                            <td colspan="2" class="text-right">
                                                <h5 class="mb-0"><strong>NET SALARY:</strong></h5>
                                            </td>
                                            <td class="text-right">
                                                <h5 class="mb-0 text-primary">
                                                    <strong>Rp <?= number_format($incentive['net_salary'] ?? 0, 0, ',', '.') ?></strong>
                                                </h5>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Details -->
                    <?php if (!empty($performance['violations'])): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-warning">
                                <h6 class="m-0 font-weight-bold text-white">
                                    <i class="fas fa-exclamation-triangle"></i> Violations in Period
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Penalty</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($performance['violations'] as $v): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= date('d M Y', strtotime($v->violation_date ?? 'now')) ?></td>
                                                    <td><?= ucwords(str_replace('_', ' ', $v->violation_type ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars(substr($v->description ?? '-', 0, 50)) ?></td>
                                                    <td class="text-right">Rp <?= number_format($v->penalty_amount ?? 0, 0, ',', '.') ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= ($v->status ?? 'pending') == 'pending' ? 'danger' : 'success' ?>">
                                                            <?= ucfirst($v->status ?? 'pending') ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
    // Print styling
    window.onbeforeprint = function() {
        document.querySelector('.btn').style.display = 'none';
    };
    window.onafterprint = function() {
        document.querySelector('.btn').style.display = 'inline-block';
    };
    </script>
</body>
</html>