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
                            <i class="fas fa-exclamation-triangle text-warning"></i> Driver Violations
                        </h1>
                        <?php if ($user_level == 'superadmin' || $user_level == 'operational_staff'): ?>
                            <a href="<?= base_url('driver_violations/tambah') ?>" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50">
                                    <i class="fas fa-plus"></i>
                                </span>
                                <span class="text">Add Violation</span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <!-- Total Violations -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Violations</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $summary['total'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Pending</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $summary['by_status']['pending']['count'] ?? 0 ?>
                                                <small class="text-muted">(Rp <?= number_format($summary['by_status']['pending']['total_penalty'] ?? 0, 0, ',', '.') ?>)</small>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paid -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Paid</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $summary['by_status']['paid']['count'] ?? 0 ?>
                                                <small class="text-muted">(Rp <?= number_format($summary['by_status']['paid']['total_penalty'] ?? 0, 0, ',', '.') ?>)</small>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Waived -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Waived</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $summary['by_status']['waived']['count'] ?? 0 ?>
                                                <small class="text-muted">(Rp <?= number_format($summary['by_status']['waived']['total_penalty'] ?? 0, 0, ',', '.') ?>)</small>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-filter"></i> Filters
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?= base_url('driver_violations') ?>" id="filterForm">
                                <div class="row">
                                    <!-- Driver -->
                                    <div class="col-md-3 mb-3">
                                        <label>Driver</label>
                                        <select name="driver_id" class="form-control">
                                            <option value="">-- All Drivers --</option>
                                            <?php if (!empty($drivers)): foreach ($drivers as $driver): ?>
                                                    <option value="<?= $driver->id ?>" <?= ($filters['driver_id'] ?? '') == $driver->id ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($driver->nama_driver ?? '') ?>
                                                    </option>
                                            <?php endforeach;
                                            endif; ?>
                                        </select>
                                    </div>

                                    <!-- Violation Type -->
                                    <div class="col-md-3 mb-3">
                                        <label>Violation Type</label>
                                        <select name="violation_type" class="form-control">
                                            <option value="">-- All Types --</option>
                                            <option value="speeding" <?= ($filters['violation_type'] ?? '') == 'speeding' ? 'selected' : '' ?>>Speeding</option>
                                            <option value="accident" <?= ($filters['violation_type'] ?? '') == 'accident' ? 'selected' : '' ?>>Accident</option>
                                            <option value="complaint" <?= ($filters['violation_type'] ?? '') == 'complaint' ? 'selected' : '' ?>>Complaint</option>
                                            <option value="damage" <?= ($filters['violation_type'] ?? '') == 'damage' ? 'selected' : '' ?>>Vehicle Damage</option>
                                            <option value="late_delivery" <?= ($filters['violation_type'] ?? '') == 'late_delivery' ? 'selected' : '' ?>>Late Delivery</option>
                                            <option value="other" <?= ($filters['violation_type'] ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-2 mb-3">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="">-- All Status --</option>
                                            <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="paid" <?= ($filters['status'] ?? '') == 'paid' ? 'selected' : '' ?>>Paid</option>
                                            <option value="waived" <?= ($filters['status'] ?? '') == 'waived' ? 'selected' : '' ?>>Waived</option>
                                        </select>
                                    </div>

                                    <!-- Date From -->
                                    <div class="col-md-2 mb-3">
                                        <label>Date From</label>
                                        <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? '' ?>">
                                    </div>

                                    <!-- Date To -->
                                    <div class="col-md-2 mb-3">
                                        <label>Date To</label>
                                        <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? '' ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Keyword -->
                                    <div class="col-md-6 mb-3">
                                        <label>Search</label>
                                        <input type="text" name="keyword" class="form-control" placeholder="Search driver name or description..." value="<?= $filters['keyword'] ?? '' ?>">
                                    </div>

                                    <!-- Buttons -->
                                    <div class="col-md-6 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="<?= base_url('driver_violations') ?>" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list"></i> Violations List (<?= count($violations ?? []) ?> records)
                            </h6>
                            <div>
                                <a href="<?= base_url('driver_violations/export_pdf?' . http_build_query($filters ?? [])) ?>" class="btn btn-sm btn-danger" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">Driver</th>
                                            <th width="12%">Type</th>
                                            <th width="10%">Date</th>
                                            <th width="25%">Description</th>
                                            <th width="12%">Penalty</th>
                                            <th width="10%">Status</th>
                                            <th width="11%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($violations) && count($violations) > 0): ?>
                                            <?php $no = 1;
                                            foreach ($violations as $v): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($v->nama_driver ?? '-') ?></strong><br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone"></i> <?= htmlspecialchars($v->no_hp ?? '-') ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $type_badges = [
                                                            'speeding' => '<span class="badge badge-danger"><i class="fas fa-tachometer-alt"></i> Speeding</span>',
                                                            'accident' => '<span class="badge badge-dark"><i class="fas fa-car-crash"></i> Accident</span>',
                                                            'complaint' => '<span class="badge badge-warning"><i class="fas fa-comment-slash"></i> Complaint</span>',
                                                            'damage' => '<span class="badge badge-danger"><i class="fas fa-wrench"></i> Damage</span>',
                                                            'late_delivery' => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Late Delivery</span>',
                                                            'other' => '<span class="badge badge-secondary"><i class="fas fa-ellipsis-h"></i> Other</span>'
                                                        ];
                                                        echo $type_badges[$v->violation_type ?? 'other'] ?? ($v->violation_type ?? 'other');
                                                        ?>
                                                    </td>
                                                    <td><?= date('d M Y', strtotime($v->violation_date ?? 'now')) ?></td>
                                                    <td>
                                                        <?= isset($v->description) && $v->description ? (strlen($v->description) > 100 ? substr($v->description, 0, 100) . '...' : $v->description) : '-' ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong>Rp <?= number_format($v->penalty_amount ?? 0, 0, ',', '.') ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_badges = [
                                                            'pending' => '<span class="badge badge-danger">Pending</span>',
                                                            'paid' => '<span class="badge badge-success">Paid</span>',
                                                            'waived' => '<span class="badge badge-info">Waived</span>'
                                                        ];
                                                        echo $status_badges[$v->status ?? 'pending'] ?? ($v->status ?? 'pending');
                                                        ?>
                                                        <?php if (isset($v->resolved_date) && $v->resolved_date): ?>
                                                            <br><small class="text-muted"><?= date('d M Y', strtotime($v->resolved_date)) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($user_level == 'superadmin' || $user_level == 'operational_staff'): ?>
                                                            <a href="<?= base_url('driver_violations/ubah/' . ($v->id ?? 0)) ?>" class="btn btn-sm btn-warning mb-1" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>

                                                        <?php if ($user_level == 'superadmin' && ($v->status ?? '') == 'pending'): ?>
                                                            <a href="<?= base_url('driver_violations/hapus/' . ($v->id ?? 0)) ?>" class="btn btn-sm btn-danger mb-1"
                                                                onclick="return confirm('Are you sure you want to delete this violation?')" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php endif; ?>

                                                        <?php if (($v->status ?? '') == 'pending' && ($user_level == 'superadmin' || $user_level == 'operational_staff')): ?>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <form method="POST" action="<?= base_url('driver_violations/update_status/' . ($v->id ?? 0)) ?>">
                                                                        <input type="hidden" name="status" value="paid">
                                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Mark as PAID?')">
                                                                            <i class="fas fa-check-circle text-success"></i> Mark as Paid
                                                                        </button>
                                                                    </form>
                                                                    <form method="POST" action="<?= base_url('driver_violations/update_status/' . ($v->id ?? 0)) ?>">
                                                                        <input type="hidden" name="status" value="waived">
                                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Mark as WAIVED?')">
                                                                            <i class="fas fa-times-circle text-info"></i> Mark as Waived
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                                    No violations found
                                                </td>
                                            </tr>
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

    <!-- DataTables -->
    <script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "pageLength": 25,
                "order": [
                    [3, "desc"]
                ], // Sort by date
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>

</html>