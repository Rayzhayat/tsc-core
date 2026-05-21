<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .alert-card {
            border-left: 4px solid;
            transition: all 0.2s;
        }
        .alert-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateX(2px);
        }
        .alert-card.border-left-critical { border-left-color: #e74a3b; }
        .alert-card.border-left-high { border-left-color: #f6c23e; }
        .alert-card.border-left-medium { border-left-color: #36b9cc; }
        .alert-card.border-left-low { border-left-color: #858796; }
        
        .priority-badge {
            font-size: 0.7rem;
            padding: 4px 8px;
            font-weight: bold;
        }
        
        .filter-btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .bulk-actions {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
            padding: 15px;
            border-bottom: 2px solid #e3e6f0;
            margin-bottom: 20px;
        }
    </style>
</head>
<body id="page-top" class="fixed-nav">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">
                    <!-- PAGE HEADING -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-bell text-danger"></i> TMS Alert Manager
                        </h1>
                        <div>
                            <a href="<?= base_url('tms_dashboard') ?>" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <a href="<?= base_url('tms_dashboard/generate_alerts') ?>" class="btn btn-warning btn-sm shadow-sm">
                                <i class="fas fa-sync"></i> Generate Alerts
                            </a>
                        </div>
                    </div>

                    <!-- ALERTS -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('info')): ?>
                        <div class="alert alert-info alert-dismissible fade show">
                            <i class="fas fa-info-circle"></i> <?= $this->session->flashdata('info') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif; ?>

                    <!-- STATISTICS CARDS -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Critical
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $alert_counts['critical'] ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                High Priority
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $alert_counts['high'] ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Medium
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $alert_counts['medium'] ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-info-circle fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-secondary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                                Low Priority
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $alert_counts['low'] ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-flag fa-2x text-secondary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FILTERS & BULK ACTIONS -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-filter"></i> Filter Alerts
                                    </h6>
                                </div>
                                <div class="col-md-6 text-right">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('tms_alerts') ?>" 
                                           class="btn <?= empty($current_status) && empty($current_priority) ? 'btn-primary' : 'btn-outline-primary' ?>">
                                            All
                                        </a>
                                        <a href="<?= base_url('tms_alerts?status=pending') ?>" 
                                           class="btn <?= $current_status == 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
                                            Pending
                                        </a>
                                        <a href="<?= base_url('tms_alerts?status=acknowledged') ?>" 
                                           class="btn <?= $current_status == 'acknowledged' ? 'btn-info' : 'btn-outline-info' ?>">
                                            Acknowledged
                                        </a>
                                        <a href="<?= base_url('tms_alerts?status=resolved') ?>" 
                                           class="btn <?= $current_status == 'resolved' ? 'btn-success' : 'btn-outline-success' ?>">
                                            Resolved
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <strong class="text-muted">Priority:</strong>
                                    <a href="<?= base_url('tms_alerts?priority=critical') ?>" 
                                       class="btn btn-sm <?= $current_priority == 'critical' ? 'btn-danger' : 'btn-outline-danger' ?> filter-btn">
                                        Critical
                                    </a>
                                    <a href="<?= base_url('tms_alerts?priority=high') ?>" 
                                       class="btn btn-sm <?= $current_priority == 'high' ? 'btn-warning' : 'btn-outline-warning' ?> filter-btn">
                                        High
                                    </a>
                                    <a href="<?= base_url('tms_alerts?priority=medium') ?>" 
                                       class="btn btn-sm <?= $current_priority == 'medium' ? 'btn-info' : 'btn-outline-info' ?> filter-btn">
                                        Medium
                                    </a>
                                    <a href="<?= base_url('tms_alerts?priority=low') ?>" 
                                       class="btn btn-sm <?= $current_priority == 'low' ? 'btn-secondary' : 'btn-outline-secondary' ?> filter-btn">
                                        Low
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BULK ACTIONS FORM -->
                    <?php if (!empty($alerts)): ?>
                    <form action="<?= base_url('tms_alerts/bulk_action') ?>" method="POST" id="bulkForm">
                        <div class="bulk-actions card shadow">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="selectAll">
                                        <label class="custom-control-label" for="selectAll">
                                            <strong>Select All</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-9 text-right">
                                    <span class="text-muted mr-3">
                                        <span id="selectedCount">0</span> selected
                                    </span>
                                    <button type="submit" name="action" value="acknowledge" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Acknowledge
                                    </button>
                                    <button type="submit" name="action" value="resolve" class="btn btn-primary btn-sm">
                                        <i class="fas fa-check-double"></i> Resolve
                                    </button>
                                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Are you sure you want to delete selected alerts?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ALERTS LIST -->
                        <div class="row">
                            <?php if (empty($alerts)): ?>
                                <div class="col-12">
                                    <div class="card shadow">
                                        <div class="card-body text-center py-5">
                                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">No alerts found</h5>
                                            <p class="text-muted">Try changing your filter settings</p>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($alerts as $alert): ?>
                                    <?php
                                    $priority_class = 'border-left-' . $alert->priority;
                                    $badge_class = '';
                                    switch($alert->priority) {
                                        case 'critical': $badge_class = 'badge-danger'; break;
                                        case 'high': $badge_class = 'badge-warning'; break;
                                        case 'medium': $badge_class = 'badge-info'; break;
                                        case 'low': $badge_class = 'badge-secondary'; break;
                                    }
                                    
                                    $status_badge = '';
                                    switch($alert->status) {
                                        case 'pending': $status_badge = 'badge-warning'; break;
                                        case 'acknowledged': $status_badge = 'badge-info'; break;
                                        case 'resolved': $status_badge = 'badge-success'; break;
                                    }
                                    ?>
                                    
                                    <div class="col-12 mb-3">
                                        <div class="card alert-card <?= $priority_class ?> shadow-sm">
                                            <div class="card-body">
                                                <div class="row align-items-start">
                                                    <div class="col-auto">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input alert-checkbox" 
                                                                   id="alert_<?= $alert->id ?>" name="alert_ids[]" value="<?= $alert->id ?>">
                                                            <label class="custom-control-label" for="alert_<?= $alert->id ?>"></label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <span class="badge <?= $badge_class ?> priority-badge mr-2">
                                                                    <?= strtoupper($alert->priority) ?>
                                                                </span>
                                                                <span class="badge <?= $status_badge ?> priority-badge">
                                                                    <?= strtoupper($alert->status) ?>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <?php if ($alert->status == 'pending'): ?>
                                                                    <a href="<?= base_url('tms_alerts/acknowledge/' . $alert->id) ?>" 
                                                                       class="btn btn-success btn-sm" title="Acknowledge">
                                                                        <i class="fas fa-check"></i>
                                                                    </a>
                                                                    <a href="<?= base_url('tms_alerts/resolve/' . $alert->id) ?>" 
                                                                       class="btn btn-primary btn-sm" title="Resolve">
                                                                        <i class="fas fa-check-double"></i>
                                                                    </a>
                                                                <?php elseif ($alert->status == 'acknowledged'): ?>
                                                                    <a href="<?= base_url('tms_alerts/resolve/' . $alert->id) ?>" 
                                                                       class="btn btn-primary btn-sm" title="Resolve">
                                                                        <i class="fas fa-check-double"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                                
                                                                <a href="<?= base_url('tms_alerts/delete/' . $alert->id) ?>" 
                                                                   class="btn btn-danger btn-sm" 
                                                                   onclick="return confirm('Delete this alert?')"
                                                                   title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        
                                                        <h5 class="mb-2">
                                                            <i class="fas fa-<?= $alert->reference_type == 'unit' ? 'truck' : 'user-tie' ?>"></i>
                                                            <?= htmlspecialchars($alert->title) ?>
                                                        </h5>
                                                        
                                                        <p class="text-gray-700 mb-2">
                                                            <?= htmlspecialchars($alert->message) ?>
                                                        </p>
                                                        
                                                        <div class="row text-sm text-muted">
                                                            <div class="col-md-6">
                                                                <i class="fas fa-calendar"></i> Alert Date: 
                                                                <strong><?= date('d M Y', strtotime($alert->alert_date)) ?></strong>
                                                                
                                                                <?php if ($alert->expired_date): ?>
                                                                    <br>
                                                                    <i class="fas fa-clock"></i> Expired Date: 
                                                                    <strong class="text-danger">
                                                                        <?= date('d M Y', strtotime($alert->expired_date)) ?>
                                                                    </strong>
                                                                <?php endif; ?>
                                                            </div>
                                                            
                                                            <div class="col-md-6">
                                                                <?php if ($alert->acknowledged_by): ?>
                                                                    <i class="fas fa-user-check"></i> Acknowledged by: 
                                                                    <strong><?= htmlspecialchars($alert->acknowledged_by) ?></strong>
                                                                    <br>
                                                                    <i class="fas fa-clock"></i> 
                                                                    <?= date('d M Y H:i', strtotime($alert->acknowledged_at)) ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </form>
                    <?php endif; ?>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    
    <script>
    $(document).ready(function() {
        // ========================================
        // SELECT ALL CHECKBOX
        // ========================================
        $('#selectAll').on('change', function() {
            $('.alert-checkbox').prop('checked', this.checked);
            updateSelectedCount();
        });
        
        // ========================================
        // INDIVIDUAL CHECKBOX
        // ========================================
        $('.alert-checkbox').on('change', function() {
            updateSelectedCount();
            
            // Update select all checkbox
            const total = $('.alert-checkbox').length;
            const checked = $('.alert-checkbox:checked').length;
            $('#selectAll').prop('checked', total === checked);
        });
        
        // ========================================
        // UPDATE SELECTED COUNT
        // ========================================
        function updateSelectedCount() {
            const count = $('.alert-checkbox:checked').length;
            $('#selectedCount').text(count);
        }
        
        // ========================================
        // BULK FORM VALIDATION
        // ========================================
        $('#bulkForm').on('submit', function(e) {
            const count = $('.alert-checkbox:checked').length;
            if (count === 0) {
                e.preventDefault();
                alert('Please select at least one alert!');
                return false;
            }
        });
        
        // ========================================
        // AUTO HIDE ALERTS
        // ========================================
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        console.log('✅ TMS Alert Manager Loaded!');
    });
    </script>
</body>
</html>