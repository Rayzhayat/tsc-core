<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .threat-card {
            transition: all 0.3s;
            margin-bottom: 15px;
            border-left: 4px solid #e74a3b;
        }

        .threat-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .threat-card.critical {
            border-left-color: #e74a3b;
            background: #fff5f5;
        }

        .threat-card.high {
            border-left-color: #f6c23e;
            background: #fffbf0;
        }

        .threat-card.medium {
            border-left-color: #36b9cc;
            background: #f0f9fb;
        }

        .threat-card.low {
            border-left-color: #1cc88a;
            background: #f0fdf7;
        }

        .severity-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .severity-critical {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
            color: white;
        }

        .severity-high {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
            color: white;
        }

        .severity-medium {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
            color: white;
        }

        .severity-low {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
        }

        .threat-type-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
        }

        .ip-badge {
            background: #5a5c69;
            color: white;
            padding: 6px 14px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .code-block {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }

        .stats-card {
            transition: transform 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .filter-btn {
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 600;
            margin: 3px;
            transition: all 0.3s;
        }

        .filter-btn.active {
            transform: scale(1.05);
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
            padding-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e3e6f0;
        }

        .timeline-dot {
            position: absolute;
            left: 0;
            top: 5px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid #e74a3b;
            background: white;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-exclamation-triangle text-danger"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('security_monitor') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <a href="<?= base_url('security_monitor/access_logs') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-list"></i> View Logs
                            </a>
                            <button onclick="location.reload()" class="btn btn-info btn-sm">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Row -->
                    <div class="row mb-4">
                        <?php
                        $total = count($threats);
                        $critical = count(array_filter($threats, function ($t) {
                            return $t->severity === 'critical'; }));
                        $high = count(array_filter($threats, function ($t) {
                            return $t->severity === 'high'; }));
                        $medium = count(array_filter($threats, function ($t) {
                            return $t->severity === 'medium'; }));
                        $low = count(array_filter($threats, function ($t) {
                            return $t->severity === 'low'; }));
                        ?>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Critical Threats
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($critical) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-skull-crossbones fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                High Threats
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($high) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Medium Threats
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($medium) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Detected
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($total) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="card shadow mb-4">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-filter"></i> Filter by Severity
                            </h6>
                            <button class="btn btn-secondary filter-btn active" data-filter="all">
                                <i class="fas fa-list"></i> All (<?= $total ?>)
                            </button>
                            <button class="btn btn-danger filter-btn" data-filter="critical">
                                <i class="fas fa-skull-crossbones"></i> Critical (<?= $critical ?>)
                            </button>
                            <button class="btn btn-warning filter-btn" data-filter="high">
                                <i class="fas fa-exclamation-circle"></i> High (<?= $high ?>)
                            </button>
                            <button class="btn btn-info filter-btn" data-filter="medium">
                                <i class="fas fa-exclamation"></i> Medium (<?= $medium ?>)
                            </button>
                            <button class="btn btn-success filter-btn" data-filter="low">
                                <i class="fas fa-info-circle"></i> Low (<?= $low ?>)
                            </button>
                        </div>
                    </div>

                    <!-- Threats List -->
                    <?php if (empty($threats)): ?>
                        <div class="card shadow">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-shield-alt fa-5x text-success mb-3"></i>
                                <h4 class="text-success">No Threats Detected</h4>
                                <p class="text-muted">Your system is secure! No security threats have been detected.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($threats as $threat): ?>
                                <div class="col-lg-12 threat-item" data-severity="<?= $threat->severity ?>">
                                    <div class="card threat-card <?= $threat->severity ?> shadow-sm">
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Left Column: Main Info -->
                                                <div class="col-md-8">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h5 class="mb-2">
                                                                <span class="threat-type-badge">
                                                                    <i class="fas fa-bug"></i>
                                                                    <?= strtoupper(htmlspecialchars($threat->threat_type)) ?>
                                                                </span>
                                                                <span class="severity-badge severity-<?= $threat->severity ?>">
                                                                    <?= strtoupper($threat->severity) ?>
                                                                </span>
                                                            </h5>
                                                            <p class="mb-2">
                                                                <span class="ip-badge">
                                                                    <i class="fas fa-network-wired"></i>
                                                                    <?= htmlspecialchars($threat->ip_address) ?>
                                                                </span>
                                                                <?php if ($threat->country): ?>
                                                                    <small class="text-muted ml-2">
                                                                        <i class="fas fa-globe"></i>
                                                                        <?= htmlspecialchars($threat->country) ?>
                                                                    </small>
                                                                <?php endif; ?>
                                                            </p>
                                                            <small class="text-muted">
                                                                <i class="far fa-clock"></i>
                                                                <?= date('d M Y, H:i:s', strtotime($threat->detected_at)) ?>
                                                                <span class="ml-2 text-danger">
                                                                    (<?= time_elapsed_string($threat->detected_at) ?>)
                                                                </span>
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <!-- Request Details -->
                                                    <div class="mb-3">
                                                        <h6 class="text-dark font-weight-bold mb-2">
                                                            <i class="fas fa-link"></i> Request URI:
                                                        </h6>
                                                        <code class="d-block p-2 bg-light border rounded">
                                                                    <?= htmlspecialchars($threat->request_uri) ?>
                                                                </code>
                                                    </div>

                                                    <!-- Payload -->
                                                    <?php if ($threat->payload): ?>
                                                        <div class="mb-2">
                                                            <h6 class="text-dark font-weight-bold mb-2">
                                                                <i class="fas fa-code"></i> Payload Detected:
                                                            </h6>
                                                            <div class="code-block">
                                                                <?= htmlspecialchars(substr($threat->payload, 0, 500)) ?>
                                                                <?= strlen($threat->payload) > 500 ? '...' : '' ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Right Column: Actions & Details -->
                                                <div class="col-md-4">
                                                    <div class="text-center mb-3">
                                                        <button
                                                            onclick="blockIP('<?= htmlspecialchars($threat->ip_address) ?>')"
                                                            class="btn btn-danger btn-block mb-2">
                                                            <i class="fas fa-ban"></i> Block This IP
                                                        </button>
                                                        <button
                                                            onclick="viewIPInfo('<?= htmlspecialchars($threat->ip_address) ?>')"
                                                            class="btn btn-info btn-block mb-2">
                                                            <i class="fas fa-info-circle"></i> View IP Info
                                                        </button>
                                                        <button onclick="showDetails(<?= $threat->id ?>)"
                                                            class="btn btn-secondary btn-block">
                                                            <i class="fas fa-eye"></i> Full Details
                                                        </button>
                                                    </div>

                                                    <!-- Additional Info -->
                                                    <div
                                                        class="alert alert-<?= $threat->severity === 'critical' ? 'danger' : ($threat->severity === 'high' ? 'warning' : 'info') ?> p-2">
                                                        <small>
                                                            <strong>Method:</strong>
                                                            <?= strtoupper($threat->method ?? 'GET') ?><br>
                                                            <strong>User Agent:</strong><br>
                                                            <span class="text-muted" style="font-size: 10px;">
                                                                <?= htmlspecialchars(substr($threat->user_agent ?? 'Unknown', 0, 50)) ?>...
                                                            </span>
                                                        </small>
                                                    </div>

                                                    <!-- Risk Score -->
                                                    <div class="text-center">
                                                        <div class="progress" style="height: 25px;">
                                                            <?php
                                                            $risk_score = 0;
                                                            switch ($threat->severity) {
                                                                case 'critical':
                                                                    $risk_score = 95;
                                                                    break;
                                                                case 'high':
                                                                    $risk_score = 75;
                                                                    break;
                                                                case 'medium':
                                                                    $risk_score = 50;
                                                                    break;
                                                                case 'low':
                                                                    $risk_score = 25;
                                                                    break;
                                                            }
                                                            $bar_class = $risk_score > 70 ? 'danger' : ($risk_score > 40 ? 'warning' : 'info');
                                                            ?>
                                                            <div class="progress-bar bg-<?= $bar_class ?>"
                                                                style="width: <?= $risk_score ?>%">
                                                                <strong>Risk: <?= $risk_score ?>%</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Legend Card -->
                    <div class="card shadow mt-4 border-left-primary">
                        <div class="card-body">
                            <h6 class="text-primary font-weight-bold mb-3">
                                <i class="fas fa-info-circle"></i> Threat Severity Levels
                            </h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <span class="severity-badge severity-critical">CRITICAL</span>
                                    <p class="small text-muted mt-2">Immediate action required. Active exploitation
                                        attempt.</p>
                                </div>
                                <div class="col-md-3">
                                    <span class="severity-badge severity-high">HIGH</span>
                                    <p class="small text-muted mt-2">Serious threat. Should be addressed soon.</p>
                                </div>
                                <div class="col-md-3">
                                    <span class="severity-badge severity-medium">MEDIUM</span>
                                    <p class="small text-muted mt-2">Potential threat. Monitor activity.</p>
                                </div>
                                <div class="col-md-3">
                                    <span class="severity-badge severity-low">LOW</span>
                                    <p class="small text-muted mt-2">Minor issue. Log for reference.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        $(document).ready(function () {
            // Filter functionality
            $('.filter-btn').on('click', function () {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');

                const filter = $(this).data('filter');

                if (filter === 'all') {
                    $('.threat-item').slideDown();
                } else {
                    $('.threat-item').hide();
                    $('.threat-item[data-severity="' + filter + '"]').slideDown();
                }
            });
        });

        function blockIP(ip) {
            if (confirm('Block IP: ' + ip + '?\n\nThis will prevent all access from this IP address.')) {
                $.post('<?= base_url('security_monitor/block_ip') ?>', {
                    ip: ip,
                    reason: 'Blocked from threat detection - Security threat detected'
                }, function () {
                    alert('IP blocked successfully!');
                    location.reload();
                });
            }
        }

        function viewIPInfo(ip) {
            alert('IP Info for: ' + ip + '\n\nThis feature will show detailed information about the IP address.');
            // TODO: Implement IP info modal
        }

        function showDetails(threatId) {
            alert('Threat ID: ' + threatId + '\n\nFull details will be displayed here.');
            // TODO: Implement details modal
        }

        // Helper function for time elapsed
        <?php if (!function_exists('time_elapsed_string')): ?>
            function time_elapsed_string(datetime) {
                // Fallback if helper not loaded
                return datetime;
            }
        <?php endif; ?>
    </script>
</body>

</html>