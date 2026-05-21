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
                            <i class="fas fa-plus-circle text-primary"></i> Add Violation
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
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-edit"></i> Violation Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= base_url('driver_violations/proses_tambah') ?>" id="violationForm">

                                <div class="row">
                                    <!-- Driver -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Driver <span class="text-danger">*</span></label>
                                        <select name="driver_id" class="form-control" required>
                                            <option value="">-- Select Driver --</option>
                                            <?php foreach ($drivers as $driver): ?>
                                                <option value="<?= $driver->id ?>">
                                                    <?= $driver->nama_driver ?> - <?= $driver->no_hp ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Violation Date -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Violation Date <span class="text-danger">*</span></label>
                                        <input type="date" name="violation_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Violation Type -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Violation Type <span class="text-danger">*</span></label>
                                        <select name="violation_type" class="form-control" required>
                                            <option value="">-- Select Type --</option>
                                            <option value="speeding">🚗 Speeding</option>
                                            <option value="accident">💥 Accident</option>
                                            <option value="complaint">😡 Customer Complaint</option>
                                            <option value="damage">🔧 Vehicle Damage</option>
                                            <option value="late_delivery">⏰ Late Delivery</option>
                                            <option value="other">📝 Other</option>
                                        </select>
                                    </div>

                                    <!-- Penalty Amount -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Penalty Amount (Rp) <span class="text-danger">*</span></label>
                                        <input type="number" name="penalty_amount" class="form-control" placeholder="0" required min="0" step="1000">
                                        <small class="text-muted">Enter amount in Rupiah</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Status -->
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Status</label>
                                        <select name="status" class="form-control">
                                            <option value="pending" selected>Pending</option>
                                            <option value="paid">Paid</option>
                                            <option value="waived">Waived</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Description -->
                                    <div class="col-md-12 mb-3">
                                        <label class="font-weight-bold">Description</label>
                                        <textarea name="description" class="form-control" rows="4" placeholder="Enter detailed description of the violation..."></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Notes -->
                                    <div class="col-md-12 mb-3">
                                        <label class="font-weight-bold">Internal Notes</label>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="Internal notes (not visible to driver)..."></textarea>
                                    </div>
                                </div>

                                <hr>

                                <!-- Buttons -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary btn-icon-split">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-save"></i>
                                            </span>
                                            <span class="text">Save Violation</span>
                                        </button>
                                        <a href="<?= base_url('driver_violations') ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>

                    <!-- Quick Guide -->
                    <div class="card border-left-info shadow mb-4">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-info">
                                <i class="fas fa-info-circle"></i> Quick Guide
                            </h6>
                            <ul class="mb-0">
                                <li><strong>Penalty Amount:</strong> Will be deducted from driver's monthly incentive</li>
                                <li><strong>Status:</strong>
                                    <span class="badge badge-danger">Pending</span> = Active penalty, affects rating |
                                    <span class="badge badge-success">Paid</span> = Resolved |
                                    <span class="badge badge-info">Waived</span> = Forgiven
                                </li>
                                <li><strong>Driver Rating:</strong> Automatically reduced by 0.5 for each pending violation</li>
                                <li><strong>Performance Score:</strong> Formula: 5.0 - (pending_violations × 0.5)</li>
                            </ul>
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

                    if (!driver || !type || !date || !penalty) {
                        e.preventDefault();
                        alert('Please fill in all required fields!');
                        return false;
                    }

                    if (parseInt(penalty) < 0) {
                        e.preventDefault();
                        alert('Penalty amount cannot be negative!');
                        return false;
                    }

                    return confirm('Are you sure you want to add this violation?');
                });

                // Auto format currency
                $('input[name="penalty_amount"]').on('blur', function() {
                    let val = $(this).val();
                    if (val) {
                        $(this).val(Math.round(val / 1000) * 1000);
                    }
                });
            });
        </script>