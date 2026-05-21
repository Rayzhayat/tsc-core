<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .log-row {
            font-size: 0.85rem;
        }

        .log-row.success {
            background-color: #d4edda;
        }

        .log-row.blocked {
            background-color: #f8d7da;
        }

        .log-row.suspicious {
            background-color: #fff3cd;
        }

        .ip-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-200 {
            background: #d4edda;
            color: #155724;
        }

        .status-404 {
            background: #fff3cd;
            color: #856404;
        }

        .status-500 {
            background: #f8d7da;
            color: #721c24;
        }

        .method-badge {
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: bold;
        }

        .method-GET {
            background: #cfe2ff;
            color: #084298;
        }

        .method-POST {
            background: #d1e7dd;
            color: #0f5132;
        }

        .method-DELETE {
            background: #f8d7da;
            color: #842029;
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
                            <i class="fas fa-list text-info"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('security_monitor') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <a href="<?= base_url('security_monitor/export_logs') ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Export CSV
                            </a>
                            <button
                                onclick="if(confirm('Delete logs older than 30 days?')) location.href='<?= base_url('security_monitor/clear_old_logs') ?>'"
                                class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Clear Old Logs
                            </button>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-filter"></i> Filter Logs
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('security_monitor/access_logs') ?>">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">Date From</label>
                                        <input type="date" name="date_from" class="form-control"
                                            value="<?= $filters['date_from'] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">Date To</label>
                                        <input type="date" name="date_to" class="form-control"
                                            value="<?= $filters['date_to'] ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold">IP Address</label>
                                        <input type="text" name="ip" class="form-control" placeholder="Search IP..."
                                            value="<?= $filters['ip'] ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small font-weight-bold">Status</label>
                                        <select name="status" class="form-control">
                                            <option value="">All Status</option>
                                            <option value="200" <?= $filters['status'] == '200' ? 'selected' : '' ?>>200 -
                                                Success</option>
                                            <option value="404" <?= $filters['status'] == '404' ? 'selected' : '' ?>>404 -
                                                Not Found</option>
                                            <option value="500" <?= $filters['status'] == '500' ? 'selected' : '' ?>>500 -
                                                Error</option>
                                            <option value="blocked" <?= $filters['status'] == 'blocked' ? 'selected' : '' ?>>Blocked</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="small">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Logs Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-white">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-table"></i> Access Logs
                                <span class="badge badge-info ml-2"><?= count($logs) ?> records</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm" width="100%">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="12%">Timestamp</th>
                                            <th width="10%">IP Address</th>
                                            <th width="8%">User</th>
                                            <th width="25%">Page URL</th>
                                            <th width="6%">Method</th>
                                            <th width="6%">Status</th>
                                            <th width="8%">Country</th>
                                            <th width="20%">User Agent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($logs)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                    No logs found
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $no = 1;
                                            foreach ($logs as $log):
                                                $row_class = '';
                                                if ($log->status == 'blocked') {
                                                    $row_class = 'blocked';
                                                } elseif ($log->status >= 500) {
                                                    $row_class = 'suspicious';
                                                } elseif ($log->status == 200) {
                                                    $row_class = 'success';
                                                }

                                                $status_class = 'status-200';
                                                if ($log->status == 404)
                                                    $status_class = 'status-404';
                                                if ($log->status >= 500)
                                                    $status_class = 'status-500';
                                                if ($log->status == 'blocked')
                                                    $status_class = 'status-500';

                                                $method_class = 'method-' . strtoupper($log->method);
                                                ?>
                                                <tr class="log-row <?= $row_class ?>">
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <small><?= date('d/m/Y H:i:s', strtotime($log->timestamp)) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="ip-badge"><?= htmlspecialchars($log->ip_address) ?></span>
                                                    </td>
                                                    <td>
                                                        <small><?= htmlspecialchars($log->username ?: 'Guest') ?></small>
                                                    </td>
                                                    <td>
                                                        <small class="text-primary">
                                                            <?= htmlspecialchars(substr($log->page_url, 0, 50)) ?>
                                                            <?= strlen($log->page_url) > 50 ? '...' : '' ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="method-badge <?= $method_class ?>">
                                                            <?= strtoupper($log->method) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge <?= $status_class ?>">
                                                            <?= $log->status ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?php if ($log->country): ?>
                                                                <span
                                                                    class="flag-icon flag-icon-<?= strtolower($log->country_code ?? '') ?>"></span>
                                                                <?= htmlspecialchars($log->country) ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"
                                                            title="<?= htmlspecialchars($log->user_agent) ?>">
                                                            <?= htmlspecialchars(substr($log->user_agent, 0, 30)) ?>...
                                                        </small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card shadow mb-4 border-left-info">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <h6 class="text-info font-weight-bold">
                                        <i class="fas fa-check-circle"></i> Success (200)
                                    </h6>
                                    <p class="mb-0">Normal requests</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-warning font-weight-bold">
                                        <i class="fas fa-exclamation-triangle"></i> Not Found (404)
                                    </h6>
                                    <p class="mb-0">Missing pages</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-danger font-weight-bold">
                                        <i class="fas fa-times-circle"></i> Error (500)
                                    </h6>
                                    <p class="mb-0">Server errors</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-dark font-weight-bold">
                                        <i class="fas fa-ban"></i> Blocked
                                    </h6>
                                    <p class="mb-0">Blocked by security</p>
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
            // Auto-hide alerts
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>

</html>