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

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-chart-line text-info"></i> Driver Performance
                        </h1>
                        <div>
                            <a href="<?= base_url('driver_violations') ?>" class="btn btn-secondary mr-2">
                                <i class="fas fa-list"></i> View Violations
                            </a>
                            <a href="<?= base_url('driver_violations/incentive') ?>" class="btn btn-success">
                                <i class="fas fa-calculator"></i> Incentive Calculator
                            </a>
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="<?= base_url('driver_violations/performance') ?>" class="form-inline">
                                <label class="mr-2">Period:</label>
                                <input type="date" name="date_from" class="form-control mr-2" value="<?= $date_from ?>">
                                <span class="mr-2">to</span>
                                <input type="date" name="date_to" class="form-control mr-2" value="<?= $date_to ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="<?= base_url('driver_violations/performance') ?>" class="btn btn-secondary ml-2">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <!-- Total Drivers -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Drivers</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= is_array($drivers) || is_object($drivers) ? count($drivers) : 0 ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Violations -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Violations</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $summary['total'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Violations -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $summary['by_status']['pending']['count'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Penalties -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Penalties</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($summary['by_status']['pending']['total_penalty'], 0, ',', '.') ?>
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

                    <!-- Top Violators -->
                    <?php if (!empty($top_violators)): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-danger">
                                    <i class="fas fa-trophy"></i> Top Violators (Period: <?= date('d M Y', strtotime($date_from)) ?> - <?= date('d M Y', strtotime($date_to)) ?>)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="30%">Driver</th>
                                                <th width="15%">Rating</th>
                                                <th width="20%">Violations</th>
                                                <th width="20%">Total Penalty</th>
                                                <th width="10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($top_violators as $v): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <strong><?= $v->nama_driver ?></strong><br>
                                                        <small class="text-muted"><?= $v->no_hp ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= $v->rating >= 4 ? 'success' : ($v->rating >= 3 ? 'warning' : 'danger') ?> badge-lg">
                                                            ⭐ <?= number_format($v->rating, 1) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <h4 class="mb-0 text-danger"><?= $v->violation_count ?></h4>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-danger">Rp <?= number_format($v->total_penalty, 0, ',', '.') ?></strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('driver_violations/performance/' . $v->id . '?date_from=' . $date_from . '&date_to=' . $date_to) ?>"
                                                            class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- All Drivers Performance -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-users"></i> All Drivers Performance
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="25%">Driver</th>
                                            <th width="12%">Rating</th>
                                            <th width="12%">Total Trips</th>
                                            <th width="15%">Violations</th>
                                            <th width="10%">Status</th>
                                            <th width="11%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($drivers) && (is_array($drivers) || is_object($drivers)) && count($drivers) > 0): ?>
                                            <?php $no = 1;
                                            foreach ($drivers as $d): ?>
                                                <?php
                                                // Skip if $d is not an object
                                                if (!is_object($d)) continue;

                                                // Get violation count for this driver
                                                $violation_count = 0;
                                                if (!empty($top_violators) && is_array($top_violators)) {
                                                    foreach ($top_violators as $v) {
                                                        if (is_object($v) && isset($v->id) && $v->id == $d->id) {
                                                            $violation_count = $v->violation_count ?? 0;
                                                            break;
                                                        }
                                                    }
                                                }
                                                ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <strong><?= isset($d->nama_driver) ? htmlspecialchars($d->nama_driver) : '-' ?></strong><br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone"></i> <?= isset($d->no_hp) ? htmlspecialchars($d->no_hp) : '-' ?><br>
                                                            <i class="fas fa-id-card"></i> <?= isset($d->sim) ? htmlspecialchars($d->sim) : '-' ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?php $rating = isset($d->rating) ? $d->rating : 5.0; ?>
                                                        <span class="badge badge-<?= $rating >= 4 ? 'success' : ($rating >= 3 ? 'warning' : 'danger') ?> badge-lg">
                                                            ⭐ <?= number_format($rating, 1) ?> / 5.0
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <h5 class="mb-0"><?= isset($d->total_trip) ? $d->total_trip : 0 ?></h5>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($violation_count > 0): ?>
                                                            <span class="badge badge-danger badge-lg"><?= $violation_count ?> violations</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success">Clean</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php $status = isset($d->status_driver) ? $d->status_driver : 'aktif'; ?>
                                                        <span class="badge badge-<?= $status == 'aktif' ? 'success' : 'secondary' ?>">
                                                            <?= ucfirst($status) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php $driver_id = isset($d->id) ? $d->id : 0; ?>
                                                        <a href="<?= base_url('driver_violations/performance/' . $driver_id . '?date_from=' . $date_from . '&date_to=' . $date_to) ?>"
                                                            class="btn btn-sm btn-info mb-1">
                                                            <i class="fas fa-chart-bar"></i> Detail
                                                        </a>
                                                        <a href="<?= base_url('driver_violations/incentive/' . $driver_id) ?>"
                                                            class="btn btn-sm btn-success mb-1">
                                                            <i class="fas fa-calculator"></i> Incentive
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
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

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <!-- DataTables -->
    <script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "pageLength": 25,
                "order": [
                    [2, "desc"]
                ], // Sort by rating
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
        });
    </script>
</body>

</html>