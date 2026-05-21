<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-calculator text-success"></i> Incentive Calculator
                        </h1>
                        <div>
                            <a href="<?= base_url('driver_violations') ?>" class="btn btn-secondary mr-2">
                                <i class="fas fa-list"></i> View Violations
                            </a>
                            <a href="<?= base_url('driver_violations/performance') ?>" class="btn btn-info">
                                <i class="fas fa-chart-line"></i> Performance
                            </a>
                        </div>
                    </div>

                    <!-- Month Filter -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="<?= base_url('driver_violations/incentive') ?>" class="form-inline">
                                <label class="mr-2">Month:</label>
                                <input type="month" name="month" class="form-control mr-2" value="<?= $month ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Calculate
                                </button>
                                <a href="<?= base_url('driver_violations/incentive') ?>" class="btn btn-secondary ml-2">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Incentive Formula Card -->
                    <div class="card border-left-success shadow mb-4">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-success">
                                <i class="fas fa-info-circle"></i> Incentive Calculation Formula
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Income Components:</strong>
                                    <ul class="mb-0">
                                        <li>Base Salary: Rp 3,000,000</li>
                                        <li>Trip Bonus: Rp 50,000 per trip</li>
                                        <li>Performance Bonus: Up to 20% (rating-based)</li>
                                        <li>Fuel Efficiency Bonus: Rp 200,000 (if ≥ 8 km/L)</li>
                                        <li>Safety Bonus: Rp 300,000 (no pending violations)</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <strong>Deductions:</strong>
                                    <ul class="mb-0">
                                        <li>Pending Violation Penalties</li>
                                    </ul>
                                    <br>
                                    <strong>Net Salary:</strong>
                                    <p class="mb-0">= Base + Total Bonus - Total Penalties</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- All Drivers Incentive Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-users"></i> All Drivers Incentive (Period: <?= date('F Y', strtotime($month . '-01')) ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable">
                                    <thead class="bg-gradient-success text-white">
                                        <tr>
                                            <th width="3%">No</th>
                                            <th width="15%">Driver</th>
                                            <th width="8%">Rating</th>
                                            <th width="8%">Trips</th>
                                            <th width="11%">Base Salary</th>
                                            <th width="10%">Trip Bonus</th>
                                            <th width="10%">Perf. Bonus</th>
                                            <th width="9%">Other Bonus</th>
                                            <th width="10%">Penalties</th>
                                            <th width="12%">Net Salary</th>
                                            <th width="4%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($incentive_list) > 0): ?>
                                            <?php
                                            $no = 1;
                                            $total_net_salary = 0;
                                            $total_base = 0;
                                            $total_bonus = 0;
                                            $total_penalties = 0;

                                            foreach ($incentive_list as $item):
                                                $driver = $item['driver'];
                                                $incentive = $item['incentive'];
                                                $other_bonus = $incentive['fuel_bonus'] + $incentive['safety_bonus'];

                                                $total_net_salary += $incentive['net_salary'];
                                                $total_base += $incentive['base_salary'];
                                                $total_bonus += $incentive['total_bonus'];
                                                $total_penalties += $incentive['total_penalties'];
                                            ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <strong><?= $driver->nama_driver ?></strong><br>
                                                        <small class="text-muted"><?= $driver->no_hp ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-<?= $incentive['performance_rating'] >= 4 ? 'success' : ($incentive['performance_rating'] >= 3 ? 'warning' : 'danger') ?>">
                                                            ⭐ <?= number_format($incentive['performance_rating'], 1) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <strong><?= $incentive['trip_count'] ?></strong>
                                                    </td>
                                                    <td class="text-right">
                                                        Rp <?= number_format($incentive['base_salary'], 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-right text-success">
                                                        +Rp <?= number_format($incentive['trip_bonus'], 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-right text-success">
                                                        +Rp <?= number_format($incentive['performance_bonus'], 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-right text-success">
                                                        +Rp <?= number_format($other_bonus, 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-right text-danger">
                                                        <?php if ($incentive['total_penalties'] > 0): ?>
                                                            -Rp <?= number_format($incentive['total_penalties'], 0, ',', '.') ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-primary">Rp <?= number_format($incentive['net_salary'], 0, ',', '.') ?></strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('driver_violations/incentive/' . $driver->id . '?month=' . $month) ?>"
                                                            class="btn btn-sm btn-info" title="View Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>

                                            <!-- Total Row -->
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="4" class="text-right">TOTAL:</td>
                                                <td class="text-right">Rp <?= number_format($total_base, 0, ',', '.') ?></td>
                                                <td colspan="3" class="text-right text-success">+Rp <?= number_format($total_bonus, 0, ',', '.') ?></td>
                                                <td class="text-right text-danger">-Rp <?= number_format($total_penalties, 0, ',', '.') ?></td>
                                                <td class="text-right text-primary"><strong>Rp <?= number_format($total_net_salary, 0, ',', '.') ?></strong></td>
                                                <td></td>
                                            </tr>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                                    No drivers found
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row">
                        <!-- Total Base Salary -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Base Salary</div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_base ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Bonus -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Bonus</div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_bonus ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-gift fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Penalties -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Penalties</div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_penalties ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-minus-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Net Salary -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Net Salary</div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_net_salary ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <?php $this->load->view('partials/footer'); ?>
        </div>
        <!-- End of Content Wrapper -->

        <!-- DataTables -->
        <script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
        <script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>

        <script>
            $(document).ready(function() {
                $('#dataTable').DataTable({
                    "pageLength": 25,
                    "order": [
                        [9, "desc"]
                    ], // Sort by net salary
                    "language": {
                        "search": "Search:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries"
                    }
                });
            });
        </script>
        -e
        <?php $this->load->view('partials/js') ?>
</body>

</html>