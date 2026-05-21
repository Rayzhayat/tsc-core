<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>
        <?php $this->load->view('partials/sidebar'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-edit text-warning"></i> Edit Violation
                        </h1>
                        <a href="<?= base_url('driver_violations') ?>" class="btn btn-secondary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span class="text">Back to List</span>
                        </a>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-edit"></i> Violation Information
                            </h6>
                            <span class="badge badge-<?= $violation->status == 'pending' ? 'danger' : ($violation->status == 'paid' ? 'success' : 'info') ?> badge-lg">
                                <?= strtoupper($violation->status) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= base_url('driver_violations/proses_ubah') ?>" id="violationForm">
                                <input type="hidden" name="id" value="<?= $violation->id ?>">

                                <div class="row">
                                    <!-- Driver -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Driver <span class="text-danger">*</span></label>
                                        <select name="driver_id" class="form-control" required>
                                            <option value="">-- Select Driver --</option>
                                            <?php foreach ($drivers as $driver): ?>
                                                <option value="<?= $driver->id ?>" <?= $violation->driver_id == $driver->id ? 'selected' : '' ?>>
                                                    <?= $driver->nama_driver ?> - <?= $driver->no_hp ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Violation Date -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Violation Date <span class="text-danger">*</span></label>
                                        <input type="date" name="violation_date" class="form-control" value="<?= $violation->violation_date ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Violation Type -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Violation Type <span class="text-danger">*</span></label>
                                        <select name="violation_type" class="form-control" required>
                                            <option value="">-- Select Type --</option>
                                            <option value="speeding" <?= $violation->violation_type == 'speeding' ? 'selected' : '' ?>>🚗 Speeding</option>
                                            <option value="accident" <?= $violation->violation_type == 'accident' ? 'selected' : '' ?>>💥 Accident</option>
                                            <option value="complaint" <?= $violation->violation_type == 'complaint' ? 'selected' : '' ?>>😡 Customer Complaint</option>
                                            <option value="damage" <?= $violation->violation_type == 'damage' ? 'selected' : '' ?>>🔧 Vehicle Damage</option>
                                            <option value="late_delivery" <?= $violation->violation_type == 'late_delivery' ? 'selected' : '' ?>>⏰ Late Delivery</option>
                                            <option value="other" <?= $violation->violation_type == 'other' ? 'selected' : '' ?>>📝 Other</option>
                                        </select>
                                    </div>

                                    <!-- Penalty Amount -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Penalty Amount (Rp) <span class="text-danger">*</span></label>
                                        <input type="number" name="penalty_amount" class="form-control" value="<?= $violation->penalty_amount ?>" required min="0" step="1000">
                                        <small class="text-muted">Enter amount in Rupiah</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Status -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control" required>
                                            <option value="pending" <?= $violation->status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="paid" <?= $violation->status == 'paid' ? 'selected' : '' ?>>Paid</option>
                                            <option value="waived" <?= $violation->status == 'waived' ? 'selected' : '' ?>>Waived</option>
                                        </select>
                                        <small class="text-muted">
                                            <?php if ($violation->resolved_date): ?>
                                                Resolved on: <?= date('d M Y', strtotime($violation->resolved_date)) ?>
                                                by <?= $violation->resolved_by ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Description -->
                                    <div class="col-md-12 mb-3">
                                        <label class="font-weight-bold">Description</label>
                                        <textarea name="description" class="form-control" rows="4" placeholder="Enter detailed description of the violation..."><?= $violation->description ?></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Notes -->
                                    <div class="col-md-12 mb-3">
                                        <label class="font-weight-bold">Internal Notes</label>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="Internal notes (not visible to driver)..."><?= $violation->notes ?></textarea>
                                    </div>
                                </div>

                                <hr>

                                <!-- Metadata -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Created by: <strong><?= $violation->created_by ?></strong> on
                                            <strong><?= date('d M Y H:i', strtotime($violation->created_at)) ?></strong>
                                        </small>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-warning btn-icon-split">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-save"></i>
                                            </span>
                                            <span class="text">Update Violation</span>
                                        </button>
                                        <a href="<?= base_url('driver_violations') ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>

                                        <?php if ($this->session->userdata('login')['user_level'] == 'superadmin' && $violation->status == 'pending'): ?>
                                            <a href="<?= base_url('driver_violations/hapus/' . $violation->id) ?>"
                                                class="btn btn-danger float-right"
                                                onclick="return confirm('Are you sure you want to DELETE this violation? This action cannot be undone!')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>

                    <!-- Violation History -->
                    <?php if ($violation->resolved_date): ?>
                        <div class="card border-left-success shadow mb-4">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-success">
                                    <i class="fas fa-history"></i> Resolution History
                                </h6>
                                <p class="mb-0">
                                    This violation was marked as <strong><?= strtoupper($violation->status) ?></strong>
                                    on <strong><?= date('d M Y', strtotime($violation->resolved_date)) ?></strong>
                                    by <strong><?= $violation->resolved_by ?></strong>.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Driver Info -->
                    <div class="card border-left-info shadow mb-4">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-info">
                                <i class="fas fa-user"></i> Driver Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Name:</strong> <?= $violation->nama_driver ?></p>
                                    <p class="mb-1"><strong>Phone:</strong> <?= $violation->no_hp ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>SIM:</strong> <?= $violation->no_sim ?></p>
                                    <p class="mb-1"><strong>Rating:</strong>
                                        <span class="badge badge-<?= $violation->rating >= 4 ? 'success' : ($violation->rating >= 3 ? 'warning' : 'danger') ?>">
                                            ⭐ <?= number_format($violation->rating, 1) ?> / 5.0
                                        </span>
                                    </p>
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

        <script>
            $(document).ready(function() {
                // Form validation
                $('#violationForm').on('submit', function(e) {
                    let driver = $('select[name="driver_id"]').val();
                    let type = $('select[name="violation_type"]').val();
                    let date = $('input[name="violation_date"]').val();
                    let penalty = $('input[name="penalty_amount"]').val();
                    let status = $('select[name="status"]').val();

                    if (!driver || !type || !date || !penalty || !status) {
                        e.preventDefault();
                        alert('Please fill in all required fields!');
                        return false;
                    }

                    if (parseInt(penalty) < 0) {
                        e.preventDefault();
                        alert('Penalty amount cannot be negative!');
                        return false;
                    }

                    return confirm('Are you sure you want to update this violation?');
                });

                // Auto format currency
                $('input[name="penalty_amount"]').on('blur', function() {
                    let val = $(this).val();
                    if (val) {
                        $(this).val(Math.round(val / 1000) * 1000);
                    }
                });

                // Status change warning
                $('select[name="status"]').on('change', function() {
                    let oldStatus = '<?= $violation->status ?>';
                    let newStatus = $(this).val();

                    if (oldStatus == 'pending' && newStatus != 'pending') {
                        alert('⚠️ Changing status to ' + newStatus.toUpperCase() + ' will mark this violation as resolved and improve driver\'s rating.');
                    }

                    if (oldStatus != 'pending' && newStatus == 'pending') {
                        alert('⚠️ Changing status back to PENDING will reduce driver\'s rating again.');
                    }
                });
            });
        </script>