<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .ip-card {
            transition: all 0.3s;
            border-left: 4px solid #e74a3b;
        }

        .ip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .ip-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
        }

        .reason-text {
            font-size: 0.9rem;
            color: #5a5c69;
        }

        .block-form-card {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
            color: white;
        }

        .stats-badge {
            background: white;
            color: #e74a3b;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
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
                            <i class="fas fa-ban text-danger"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('security_monitor') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <a href="<?= base_url('security_monitor/access_logs') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-list"></i> View Logs
                            </a>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Block New IP Card -->
                    <div class="card shadow mb-4 block-form-card">
                        <div class="card-body">
                            <h5 class="font-weight-bold mb-3">
                                <i class="fas fa-plus-circle"></i> Block New IP Address
                            </h5>
                            <form action="<?= base_url('security_monitor/block_ip') ?>" method="post" id="blockForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" name="ip" class="form-control"
                                            placeholder="Enter IP Address (e.g., 192.168.1.100)" required
                                            pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$">
                                        <small class="text-white-50">
                                            <i class="fas fa-info-circle"></i> Format: xxx.xxx.xxx.xxx
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="reason" class="form-control"
                                            placeholder="Reason for blocking (optional)" maxlength="200">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-light btn-block font-weight-bold">
                                            <i class="fas fa-ban"></i> Block IP
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-danger shadow">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <h4 class="mb-0">
                                            <span class="stats-badge"><?= count($blocked_ips) ?></span>
                                        </h4>
                                        <small class="font-weight-bold">Total Blocked IPs</small>
                                    </div>
                                    <div class="col-md-4">
                                        <h4 class="mb-0">
                                            <span class="stats-badge">
                                                <?php
                                                $today_blocks = array_filter($blocked_ips, function ($ip) {
                                                    return date('Y-m-d', strtotime($ip->blocked_at)) === date('Y-m-d');
                                                });
                                                echo count($today_blocks);
                                                ?>
                                            </span>
                                        </h4>
                                        <small class="font-weight-bold">Blocked Today</small>
                                    </div>
                                    <div class="col-md-4">
                                        <h4 class="mb-0">
                                            <span class="stats-badge">
                                                <?php
                                                $week_blocks = array_filter($blocked_ips, function ($ip) {
                                                    return strtotime($ip->blocked_at) >= strtotime('-7 days');
                                                });
                                                echo count($week_blocks);
                                                ?>
                                            </span>
                                        </h4>
                                        <small class="font-weight-bold">This Week</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Blocked IPs List -->
                    <?php if (empty($blocked_ips)): ?>
                        <div class="card shadow">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-shield-alt fa-5x text-success mb-3"></i>
                                <h4 class="text-success">No Blocked IPs</h4>
                                <p class="text-muted">All clear! No IP addresses are currently blocked.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($blocked_ips as $ip): ?>
                                <div class="col-lg-6 mb-4">
                                    <div class="card ip-card shadow">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h5 class="mb-2">
                                                        <span class="ip-badge">
                                                            <i class="fas fa-ban"></i>
                                                            <?= htmlspecialchars($ip->ip_address) ?>
                                                        </span>
                                                    </h5>
                                                    <p class="reason-text mb-2">
                                                        <i class="fas fa-info-circle text-danger"></i>
                                                        <strong>Reason:</strong>
                                                        <?= htmlspecialchars($ip->reason ?: 'No reason provided') ?>
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="far fa-clock"></i>
                                                        Blocked: <?= date('d M Y, H:i', strtotime($ip->blocked_at)) ?>
                                                    </small>
                                                    <?php if ($ip->updated_at): ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-sync-alt"></i>
                                                            Updated: <?= date('d M Y, H:i', strtotime($ip->updated_at)) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-right">
                                                    <button onclick="unblockIP('<?= htmlspecialchars($ip->ip_address) ?>')"
                                                        class="btn btn-success btn-sm mb-2" title="Unblock this IP">
                                                        <i class="fas fa-unlock"></i> Unblock
                                                    </button>
                                                    <br>
                                                    <button onclick="checkIPInfo('<?= htmlspecialchars($ip->ip_address) ?>')"
                                                        class="btn btn-info btn-sm" title="View IP Info">
                                                        <i class="fas fa-search"></i> Info
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Additional Info -->
                                            <?php if ($ip->country): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="text-muted">
                                                        <i class="fas fa-globe"></i>
                                                        <strong>Location:</strong>
                                                        <?= htmlspecialchars($ip->country) ?>
                                                        <?php if ($ip->city): ?>
                                                            , <?= htmlspecialchars($ip->city) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Info Card -->
                    <div class="card shadow mb-4 border-left-warning">
                        <div class="card-body">
                            <h6 class="text-warning font-weight-bold mb-3">
                                <i class="fas fa-exclamation-triangle"></i> Important Information
                            </h6>
                            <ul class="mb-0">
                                <li>Blocked IPs cannot access any part of the system</li>
                                <li>Blocking is immediate and affects all endpoints</li>
                                <li>You can unblock an IP at any time</li>
                                <li>Be careful not to block your own IP address</li>
                                <li>Consider checking IP info before blocking</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- IP Info Modal -->
    <div class="modal fade" id="ipInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i> IP Address Information
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="ipInfoContent">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-3x text-info"></i>
                            <p class="mt-3">Loading IP information...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        // Form validation
        $('#blockForm').on('submit', function (e) {
            const ip = $('input[name="ip"]').val();
            const ipPattern = /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/;

            if (!ipPattern.test(ip)) {
                e.preventDefault();
                alert('Invalid IP address format!\n\nPlease use format: xxx.xxx.xxx.xxx\nExample: 192.168.1.100');
                return false;
            }

            // Check if blocking own IP
            const currentIP = '<?= $this->input->ip_address() ?>';
            if (ip === currentIP) {
                e.preventDefault();
                alert('⚠️ WARNING!\n\nYou are about to block YOUR OWN IP address!\n\nThis will log you out and prevent you from accessing the system.\n\nAre you sure?');
                if (!confirm('Really block your own IP? This is not recommended!')) {
                    return false;
                }
            }

            return confirm('Block IP: ' + ip + '?\n\nThis IP will immediately lose all access to the system.');
        });

        function unblockIP(ip) {
            if (confirm('Unblock IP: ' + ip + '?\n\nThis IP will regain access to the system.')) {
                window.location.href = '<?= base_url('security_monitor/unblock_ip/') ?>' + ip;
            }
        }

        function checkIPInfo(ip) {
            $('#ipInfoModal').modal('show');
            $('#ipInfoContent').html(`
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-3x text-info"></i>
                    <p class="mt-3">Loading IP information...</p>
                </div>
            `);

            $.post('<?= base_url('security_monitor/check_ip') ?>', {
                ip: ip
            }, function (response) {
                try {
                    const data = JSON.parse(response);

                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary font-weight-bold mb-3">
                                    <i class="fas fa-network-wired"></i> Network Information
                                </h6>
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th width="40%">IP Address</th>
                                        <td><span class="ip-badge">${ip}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Type</th>
                                        <td>${data.type || 'Unknown'}</td>
                                    </tr>
                                    <tr>
                                        <th>ISP</th>
                                        <td>${data.isp || 'Unknown'}</td>
                                    </tr>
                                    <tr>
                                        <th>Organization</th>
                                        <td>${data.org || 'Unknown'}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-success font-weight-bold mb-3">
                                    <i class="fas fa-globe"></i> Geographic Information
                                </h6>
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th width="40%">Country</th>
                                        <td>${data.country || 'Unknown'}</td>
                                    </tr>
                                    <tr>
                                        <th>Region</th>
                                        <td>${data.region || 'Unknown'}</td>
                                    </tr>
                                    <tr>
                                        <th>City</th>
                                        <td>${data.city || 'Unknown'}</td>
                                    </tr>
                                    <tr>
                                        <th>Timezone</th>
                                        <td>${data.timezone || 'Unknown'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    `;

                    if (data.is_vpn || data.is_proxy || data.is_tor) {
                        html += `
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Security Alert:</strong> This IP may be using VPN, Proxy, or Tor network.
                            </div>
                        `;
                    }

                    $('#ipInfoContent').html(html);

                } catch (e) {
                    $('#ipInfoContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            Failed to load IP information. Please try again.
                        </div>
                    `);
                }
            }).fail(function () {
                $('#ipInfoContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i>
                        Failed to load IP information. Please try again.
                    </div>
                `);
            });
        }

        // Auto-hide alerts
        setTimeout(function () {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
</body>

</html>