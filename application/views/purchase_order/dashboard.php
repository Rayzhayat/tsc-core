<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .stat-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
        }

        .stat-card.draft {
            border-left-color: #858796;
        }

        .stat-card.pending {
            border-left-color: #36b9cc;
        }

        .stat-card.approved {
            border-left-color: #1cc88a;
        }

        .stat-card.rejected {
            border-left-color: #e74a3b;
        }

        .stat-card.completed {
            border-left-color: #4e73df;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #5a5c69;
        }

        .stat-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            color: #858796;
            font-weight: bold;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }

        .table-recent th {
            background: #4e73df;
            color: white;
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 4px 10px;
        }

        .filter-card {
            background: #f8f9fc;
            border-left: 4px solid #4e73df;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">

                    <!-- Dashboard Header -->
                    <div class="dashboard-header shadow">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">
                                    <i class="fas fa-chart-line"></i> Purchase Order Dashboard
                                </h2>
                                <p class="mb-0">
                                    <i class="fas fa-calendar"></i>
                                    <?= date('l, d F Y') ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?= base_url('purchase_order/tambah') ?>"
                                    class="btn btn-light btn-lg">
                                    <i class="fas fa-plus-circle"></i> Buat PO Baru
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="filter-card">
                        <form method="GET" action="<?= base_url('purchase_order/dashboard') ?>" class="form-inline">
                            <label class="mr-2 font-weight-bold">Filter Periode:</label>
                            <select name="periode" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                <option value="today" <?= ($periode ?? 'today') == 'today' ? 'selected' : '' ?>>Hari Ini</option>
                                <option value="week" <?= ($periode ?? '') == 'week' ? 'selected' : '' ?>>Minggu Ini</option>
                                <option value="month" <?= ($periode ?? '') == 'month' ? 'selected' : '' ?>>Bulan Ini</option>
                                <option value="year" <?= ($periode ?? '') == 'year' ? 'selected' : '' ?>>Tahun Ini</option>
                                <option value="all" <?= ($periode ?? '') == 'all' ? 'selected' : '' ?>>Semua</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </form>
                    </div>

                    <!-- Statistics Cards Row 1 -->
                    <div class="row">

                        <!-- Total PO -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label mb-1">Total PO</div>
                                            <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Draft -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card draft shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label mb-1">Draft</div>
                                            <div class="stat-number text-secondary"><?= $stats['draft'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Approval -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card pending shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label mb-1">Pending</div>
                                            <div class="stat-number text-info"><?= $stats['pending'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approved -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card approved shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label mb-1">Approved</div>
                                            <div class="stat-number text-success"><?= $stats['approved'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Statistics Cards Row 2 -->
                    <div class="row">

                        <!-- Rejected -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card rejected shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label mb-1">Rejected</div>
                                            <div class="stat-number text-danger"><?= $stats['rejected'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-times-circle fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Completed -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card completed shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label mb-1">Completed</div>
                                            <div class="stat-number text-primary"><?= $stats['completed'] ?? 0 ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-double fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Value -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card shadow h-100 py-2" style="border-left-color: #f6c23e;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label mb-1">Total Value</div>
                                            <div class="h6 mb-0 font-weight-bold text-warning">
                                                Rp <?= number_format($stats['total_value'] ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Outstanding Payment -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card shadow h-100 py-2" style="border-left-color: #e74a3b;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label mb-1">Outstanding</div>
                                            <div class="h6 mb-0 font-weight-bold text-danger">
                                                Rp <?= number_format($stats['outstanding'] ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-3x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Charts Row -->
                    <div class="row">

                        <!-- Status Chart -->
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-pie"></i> Status Distribution
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Trend Chart -->
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-line"></i> Monthly Trend
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="trendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Top Vendors -->
                    <div class="row">
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-building"></i> Top 5 Vendors
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="vendorChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Chart -->
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-tags"></i> Category Distribution
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="categoryChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent PO Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-list"></i> Recent Purchase Orders
                                    </h6>
                                    <a href="<?= base_url('purchase_order') ?>" class="btn btn-sm btn-primary">
                                        View All <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-recent">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>No. PO</th>
                                                    <th>Tanggal</th>
                                                    <th>Vendor</th>
                                                    <th>Kategori</th>
                                                    <th class="text-right">Total</th>
                                                    <th width="10%">Status</th>
                                                    <th width="10%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($recent_po)): ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted">
                                                            <em>Belum ada purchase order</em>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php
                                                    $no = 1;
                                                    foreach ($recent_po as $po):
                                                        $badge_color = [
                                                            'draft' => 'secondary',
                                                            'pending' => 'info',
                                                            'approved' => 'success',
                                                            'rejected' => 'danger',
                                                            'partial_received' => 'warning',
                                                            'received' => 'primary',
                                                            'completed' => 'success',
                                                            'cancelled' => 'dark'
                                                        ];
                                                        $color = $badge_color[$po->status] ?? 'secondary';
                                                    ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td>
                                                                <strong><?= $po->no_po ?></strong>
                                                            </td>
                                                            <td><?= date('d/m/Y', strtotime($po->tanggal_po)) ?></td>
                                                            <td><?= htmlspecialchars($po->vendor_nama) ?></td>
                                                            <td>
                                                                <span class="badge badge-light">
                                                                    <?= ucfirst($po->kategori) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-right">
                                                                Rp <?= number_format($po->total_po, 0, ',', '.') ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-<?= $color ?> badge-status">
                                                                    <?= strtoupper(str_replace('_', ' ', $po->status)) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="<?= base_url('purchase_order/detail/' . $po->id) ?>"
                                                                    class="btn btn-sm btn-info" title="Detail">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
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
        $(document).ready(function() {

            // ============================================
            // CHART 1: STATUS DISTRIBUTION (Pie Chart)
            // ============================================
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Draft', 'Pending', 'Approved', 'Rejected', 'Completed', 'Others'],
                    datasets: [{
                        data: [
                            <?= $stats['draft'] ?? 0 ?>,
                            <?= $stats['pending'] ?? 0 ?>,
                            <?= $stats['approved'] ?? 0 ?>,
                            <?= $stats['rejected'] ?? 0 ?>,
                            <?= $stats['completed'] ?? 0 ?>,
                            <?= $stats['others'] ?? 0 ?>
                        ],
                        backgroundColor: [
                            '#858796', // Draft - Gray
                            '#36b9cc', // Pending - Info
                            '#1cc88a', // Approved - Success
                            '#e74a3b', // Rejected - Danger
                            '#4e73df', // Completed - Primary
                            '#f6c23e' // Others - Warning
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // ============================================
            // CHART 2: MONTHLY TREND (Line Chart)
            // ============================================
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            const trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($monthly_labels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) ?>,
                    datasets: [{
                        label: 'Total PO',
                        data: <?= json_encode($monthly_data ?? [0, 0, 0, 0, 0, 0]) ?>,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#4e73df',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7
                    }, {
                        label: 'Total Value (Juta)',
                        data: <?= json_encode($monthly_value ?? [0, 0, 0, 0, 0, 0]) ?>,
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#1cc88a',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.datasetIndex === 0) {
                                        label += context.parsed.y + ' PO';
                                    } else {
                                        label += 'Rp ' + context.parsed.y.toLocaleString('id-ID') + ' Juta';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // ============================================
            // CHART 3: TOP 5 VENDORS (Horizontal Bar)
            // ============================================
            const vendorCtx = document.getElementById('vendorChart').getContext('2d');
            const vendorChart = new Chart(vendorCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($top_vendors_labels ?? ['Vendor A', 'Vendor B', 'Vendor C', 'Vendor D', 'Vendor E']) ?>,
                    datasets: [{
                        label: 'Total PO',
                        data: <?= json_encode($top_vendors_data ?? [0, 0, 0, 0, 0]) ?>,
                        backgroundColor: [
                            '#4e73df',
                            '#1cc88a',
                            '#36b9cc',
                            '#f6c23e',
                            '#e74a3b'
                        ],
                        borderWidth: 0,
                        barThickness: 40
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.x + ' PO';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // ============================================
            // CHART 4: CATEGORY DISTRIBUTION (Doughnut)
            // ============================================
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Barang', 'Jasa', 'Aset'],
                    datasets: [{
                        data: [
                            <?= $stats['kategori_barang'] ?? 0 ?>,
                            <?= $stats['kategori_jasa'] ?? 0 ?>,
                            <?= $stats['kategori_aset'] ?? 0 ?>
                        ],
                        backgroundColor: [
                            '#4e73df', // Barang - Primary
                            '#1cc88a', // Jasa - Success
                            '#f6c23e' // Aset - Warning
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // ============================================
            // AUTO REFRESH DASHBOARD (Optional)
            // ============================================
            let autoRefresh = false; // Set to true if you want auto refresh

            if (autoRefresh) {
                setInterval(function() {
                    location.reload();
                }, 300000); // Refresh every 5 minutes (300000 ms)
            }

            // ============================================
            // CONSOLE LOGGING
            // ============================================
            console.log('📊 Purchase Order Dashboard Ready!');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Statistics:');
            console.log('- Total PO: <?= $stats['total'] ?? 0 ?>');
            console.log('- Draft: <?= $stats['draft'] ?? 0 ?>');
            console.log('- Pending: <?= $stats['pending'] ?? 0 ?>');
            console.log('- Approved: <?= $stats['approved'] ?? 0 ?>');
            console.log('- Rejected: <?= $stats['rejected'] ?? 0 ?>');
            console.log('- Completed: <?= $stats['completed'] ?? 0 ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Total Value: Rp <?= number_format($stats['total_value'] ?? 0, 0, ',', '.') ?>');
            console.log('Outstanding: Rp <?= number_format($stats['outstanding'] ?? 0, 0, ',', '.') ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Charts:');
            console.log('✅ Status Distribution (Doughnut)');
            console.log('✅ Monthly Trend (Line)');
            console.log('✅ Top Vendors (Horizontal Bar)');
            console.log('✅ Category Distribution (Doughnut)');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Period Filter: <?= $periode ?? 'today' ?>');
            console.log('Recent PO Count: <?= count($recent_po ?? []) ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            // ============================================
            // SMOOTH SCROLL TO CHARTS
            // ============================================
            $('a[href^="#"]').on('click', function(event) {
                var target = $(this.getAttribute('href'));
                if (target.length) {
                    event.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                }
            });

            // ============================================
            // TOOLTIPS
            // ============================================
            $('[data-toggle="tooltip"]').tooltip();

        });
    </script>
</body>

</html>