<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .dashboard-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .dashboard-card.card-primary {
            border-left-color: #4e73df;
        }

        .dashboard-card.card-success {
            border-left-color: #1cc88a;
        }

        .dashboard-card.card-warning {
            border-left-color: #f6c23e;
        }

        .dashboard-card.card-danger {
            border-left-color: #e74a3b;
        }

        .dashboard-card.card-info {
            border-left-color: #36b9cc;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.3;
        }

        .overdue-item {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            animation: pulse-warning 2s infinite;
        }

        @keyframes pulse-warning {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .upcoming-item {
            border-left: 4px solid #4e73df;
            transition: all 0.3s;
        }

        .upcoming-item:hover {
            background: #f8f9fc;
            transform: translateX(5px);
        }

        .progress-slim {
            height: 8px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .performance-table {
            font-size: 0.9rem;
        }

        .performance-table .rank-1 {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            font-weight: bold;
        }

        .performance-table .rank-2 {
            background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
        }

        .performance-table .rank-3 {
            background: linear-gradient(135deg, #cd7f32 0%, #e8b482 100%);
        }

        .sla-gauge {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto;
        }

        .filter-badge {
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-badge:hover {
            transform: scale(1.1);
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .quick-action-btn {
            margin: 5px;
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
                                <h2 class="mb-2 font-weight-bold">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard TMS - Surat Jalan
                                </h2>
                                <p class="mb-0">
                                    <i class="fas fa-calendar"></i> <?= date('l, d F Y') ?>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-clock"></i> <span id="currentTime"></span>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?= base_url('surat_jalan/tambah') ?>" class="btn btn-light btn-lg shadow quick-action-btn">
                                    <i class="fas fa-plus"></i> Buat Surat Jalan
                                </a>
                                <a href="<?= base_url('surat_jalan') ?>" class="btn btn-outline-light btn-lg quick-action-btn">
                                    <i class="fas fa-list"></i> Lihat Semua
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards Row 1 -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card dashboard-card card-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Surat Jalan
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($summary->total_surat_jalan ?? 0) ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> Bulan ini
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-alt stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card dashboard-card card-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                On Trip
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($summary->total_on_trip ?? 0) ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-truck"></i> Sedang berjalan
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shipping-fast stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card dashboard-card card-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Completed
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($summary->total_completed ?? 0) ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-check-circle"></i> Selesai bulan ini
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card dashboard-card card-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Total Revenue
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($summary->total_revenue ?? 0, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-dollar-sign"></i> Bulan ini
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards Row 2 (SLA & Cost) -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card dashboard-card card-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                SLA On Time
                                            </div>
                                            <?php
                                            $sla_percentage = 0;
                                            if (isset($summary->total_completed) && $summary->total_completed > 0) {
                                                $sla_percentage = ($summary->total_on_time ?? 0) / $summary->total_completed * 100;
                                            }
                                            ?>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($sla_percentage, 1) ?>%
                                            </div>
                                            <div class="progress progress-slim mt-2">
                                                <div class="progress-bar bg-success"
                                                    role="progressbar"
                                                    style="width: <?= $sla_percentage ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card dashboard-card card-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Total Cost
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($summary->total_cost ?? 0, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-money-bill-wave"></i> Biaya operasional
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card dashboard-card card-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Profit
                                            </div>
                                            <?php
                                            $profit = ($summary->total_revenue ?? 0) - ($summary->total_cost ?? 0);
                                            ?>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($profit, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-chart-line"></i> Margin bulan ini
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card dashboard-card card-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Avg Delay
                                            </div>
                                            <?php
                                            $avg_delay = 0;
                                            if (isset($summary->total_completed) && $summary->total_completed > 0 && isset($summary->total_delay_minutes)) {
                                                $avg_delay = $summary->total_delay_minutes / $summary->total_completed;
                                            }
                                            ?>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= number_format($avg_delay, 0) ?> min
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-hourglass-half"></i> Rata-rata keterlambatan
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hourglass-half stat-icon text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-lg-8">

                            <!-- Overdue Deliveries Alert -->
                            <?php if (isset($overdue_deliveries) && !empty($overdue_deliveries)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-gradient-danger text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Trip Overdue (<?= count($overdue_deliveries) ?>)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($overdue_deliveries as $overdue): ?>
                                            <div class="card overdue-item mb-3">
                                                <div class="card-body py-2">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-3">
                                                            <strong><?= htmlspecialchars($overdue->no_surat_jalan) ?></strong>
                                                            <br><small class="text-muted"><?= htmlspecialchars($overdue->customer) ?></small>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <small class="text-muted">Driver:</small><br>
                                                            <?= htmlspecialchars($overdue->nama_driver) ?>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <small class="text-muted">Unit:</small><br>
                                                            <?= htmlspecialchars($overdue->no_polisi) ?>
                                                        </div>
                                                        <div class="col-md-2 text-center">
                                                            <span class="badge badge-danger">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                <?= round($overdue->minutes_overdue) ?> min
                                                            </span>
                                                        </div>
                                                        <div class="col-md-2 text-right">
                                                            <a href="<?= base_url('surat_jalan/detail/' . $overdue->id) ?>"
                                                                class="btn btn-sm btn-danger">
                                                                <i class="fas fa-eye"></i> Detail
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Upcoming Trips (Next 7 Days) -->
                            <?php if (isset($upcoming_trips) && !empty($upcoming_trips)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-calendar-alt"></i>
                                            Jadwal Trip (7 Hari Ke Depan)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($upcoming_trips as $trip): ?>
                                            <div class="card upcoming-item mb-3">
                                                <div class="card-body py-2">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-2 text-center">
                                                            <div class="bg-primary text-white rounded p-2">
                                                                <div class="h5 mb-0"><?= date('d', strtotime($trip->tanggal)) ?></div>
                                                                <small><?= date('M', strtotime($trip->tanggal)) ?></small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong><?= htmlspecialchars($trip->no_surat_jalan) ?></strong>
                                                            <br><small class="text-muted"><?= htmlspecialchars($trip->customer) ?></small>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <small><?= htmlspecialchars($trip->origin) ?></small>
                                                            <br><i class="fas fa-arrow-down text-muted"></i>
                                                            <br><small><?= htmlspecialchars($trip->dest1) ?></small>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <small class="text-muted">Driver:</small><br>
                                                            <?= htmlspecialchars($trip->nama_driver) ?>
                                                        </div>
                                                        <div class="col-md-2 text-right">
                                                            <a href="<?= base_url('surat_jalan/detail/' . $trip->id) ?>"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Monthly Chart -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-line"></i>
                                        Grafik Bulanan (<?= date('Y') ?>)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="monthlyChart"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Column -->
                        <div class="col-lg-4">
                            <!-- SLA Compliance Gauge -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-gradient-success text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-tachometer-alt"></i> SLA Compliance
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="sla-gauge">
                                        <canvas id="slaGauge"></canvas>
                                    </div>
                                    <h3 class="mt-3 mb-2 font-weight-bold text-success">
                                        <?= number_format($sla_percentage, 1) ?>%
                                    </h3>
                                    <p class="text-muted mb-0">
                                        <?= $summary->total_on_time ?? 0 ?> dari <?= $summary->total_completed ?? 0 ?> trip selesai tepat waktu
                                    </p>
                                </div>
                            </div>

                            <!-- Driver Performance (Top 5) -->
                            <?php if (isset($driver_performance) && !empty($driver_performance)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-trophy"></i> Top 5 Driver Performance
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm performance-table mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="45%">Driver</th>
                                                    <th width="25%" class="text-center">Trips</th>
                                                    <th width="25%" class="text-center">On Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $rank = 1;
                                                foreach (array_slice($driver_performance, 0, 5) as $driver):
                                                    $rank_class = '';
                                                    if ($rank == 1) $rank_class = 'rank-1';
                                                    elseif ($rank == 2) $rank_class = 'rank-2';
                                                    elseif ($rank == 3) $rank_class = 'rank-3';
                                                ?>
                                                    <tr class="<?= $rank_class ?>">
                                                        <td class="text-center">
                                                            <?php if ($rank <= 3): ?>
                                                                <i class="fas fa-medal"></i>
                                                            <?php else: ?>
                                                                <?= $rank ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <strong><?= htmlspecialchars($driver->nama_driver) ?></strong>
                                                            <br><small class="text-muted">NIK: <?= htmlspecialchars($driver->driver_nik) ?></small>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary"><?= $driver->total_trips ?></span>
                                                        </td>
                                                        <td class="text-center">
                                                            <strong class="text-success"><?= number_format($driver->on_time_percentage, 1) ?>%</strong>
                                                            <br><small class="text-muted"><?= $driver->on_time_trips ?>/<?= $driver->total_trips ?></small>
                                                        </td>
                                                    </tr>
                                                <?php
                                                    $rank++;
                                                endforeach;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Unit Utilization -->
                            <?php if (isset($unit_performance) && !empty($unit_performance)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-truck"></i> Top 5 Unit Utilization
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm performance-table mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="50%">Unit</th>
                                                    <th width="25%" class="text-center">Trips</th>
                                                    <th width="25%" class="text-right">Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($unit_performance, 0, 5) as $unit): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= htmlspecialchars($unit->no_polisi) ?></strong>
                                                            <br><small class="text-muted"><?= htmlspecialchars($unit->tipe_unit) ?></small>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-info"><?= $unit->total_trips ?></span>
                                                        </td>
                                                        <td class="text-right">
                                                            <small>Rp <?= number_format($unit->total_revenue, 0, ',', '.') ?></small>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Customer Performance -->
                            <?php if (isset($customer_performance) && !empty($customer_performance)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-building"></i> Top Customers
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach (array_slice($customer_performance, 0, 5) as $customer): ?>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <strong><?= htmlspecialchars($customer->customer) ?></strong>
                                                    <span class="text-muted"><?= $customer->total_trips ?> trips</span>
                                                </div>
                                                <div class="progress progress-slim">
                                                    <?php
                                                    $max_revenue = $customer_performance[0]->total_revenue ?? 1;
                                                    $percentage = ($customer->total_revenue / $max_revenue * 100);
                                                    ?>
                                                    <div class="progress-bar bg-primary"
                                                        style="width: <?= $percentage ?>%"
                                                        title="Rp <?= number_format($customer->total_revenue, 0, ',', '.') ?>">
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    Rp <?= number_format($customer->total_revenue, 0, ',', '.') ?> |
                                                    SLA: <?= number_format($customer->on_time_percentage, 1) ?>%
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Status Distribution Pie Chart -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-pie"></i> Status Distribution
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 250px;">
                                        <canvas id="statusPieChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-gradient-info text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-info-circle"></i> Quick Stats
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <h3 class="text-primary mb-0"><?= $summary->total_draft ?? 0 ?></h3>
                                            <small class="text-muted">Draft</small>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <h3 class="text-info mb-0"><?= $summary->total_scheduled ?? 0 ?></h3>
                                            <small class="text-muted">Scheduled</small>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <h3 class="text-danger mb-0"><?= $summary->total_cancelled ?? 0 ?></h3>
                                            <small class="text-muted">Cancelled</small>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <h3 class="text-warning mb-0"><?= $summary->total_late ?? 0 ?></h3>
                                            <small class="text-muted">Late Deliveries</small>
                                        </div>
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
            // Real-time clock
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                $('#currentTime').text(timeString);
            }

            updateClock();
            setInterval(updateClock, 1000);

            // Monthly Chart (Line Chart)
            <?php if (isset($monthly_stats) && !empty($monthly_stats)): ?>
                const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
                const monthlyChart = new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: [
                            <?php foreach ($monthly_stats as $stat): ?> '<?= date('M Y', strtotime($stat->year . '-' . $stat->month . '-01')) ?>',
                            <?php endforeach; ?>
                        ],
                        datasets: [{
                                label: 'Total Trips',
                                data: [
                                    <?php foreach ($monthly_stats as $stat): ?>
                                        <?= $stat->total_trips ?? 0 ?>,
                                    <?php endforeach; ?>
                                ],
                                borderColor: '#4e73df',
                                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Completed',
                                data: [
                                    <?php foreach ($monthly_stats as $stat): ?>
                                        <?= $stat->completed_trips ?? 0 ?>,
                                    <?php endforeach; ?>
                                ],
                                borderColor: '#1cc88a',
                                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                                tension: 0.4,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
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
            <?php endif; ?>

            // SLA Gauge (Doughnut Chart)
            const slaCtx = document.getElementById('slaGauge').getContext('2d');
            const slaPercentage = <?= $sla_percentage ?>;
            const slaChart = new Chart(slaCtx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [slaPercentage, 100 - slaPercentage],
                        backgroundColor: ['#1cc88a', '#e3e6f0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    }
                }
            });

            // Status Distribution Pie Chart
            const statusCtx = document.getElementById('statusPieChart').getContext('2d');
            const statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Draft', 'Scheduled', 'On Trip', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: [
                            <?= $summary->total_draft ?? 0 ?>,
                            <?= $summary->total_scheduled ?? 0 ?>,
                            <?= $summary->total_on_trip ?? 0 ?>,
                            <?= $summary->total_completed ?? 0 ?>,
                            <?= $summary->total_cancelled ?? 0 ?>
                        ],
                        backgroundColor: [
                            '#858796',
                            '#4e73df',
                            '#f6c23e',
                            '#1cc88a',
                            '#e74a3b'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });

            // Auto-refresh dashboard every 5 minutes
            setTimeout(function() {
                location.reload();
            }, 300000); // 5 minutes

            // Console logging
            console.log('📊 Dashboard TMS - Surat Jalan');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Summary:');
            console.log('- Total SJ: <?= $summary->total_surat_jalan ?? 0 ?>');
            console.log('- On Trip: <?= $summary->total_on_trip ?? 0 ?>');
            console.log('- Completed: <?= $summary->total_completed ?? 0 ?>');
            console.log('- SLA On Time: <?= number_format($sla_percentage, 1) ?>%');
            console.log('- Total Revenue: Rp <?= number_format($summary->total_revenue ?? 0, 0, ',', '.') ?>');
            console.log('- Total Cost: Rp <?= number_format($summary->total_cost ?? 0, 0, ',', '.') ?>');
            console.log('- Profit: Rp <?= number_format($profit, 0, ',', '.') ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            <?php if (isset($overdue_deliveries) && !empty($overdue_deliveries)): ?>
                console.warn('⚠️ OVERDUE ALERT: <?= count($overdue_deliveries) ?> trip(s) melewati target!');
            <?php endif; ?>

            console.log('🔄 Auto-refresh: Setiap 5 menit');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            // Keyboard shortcuts
            $(document).keydown(function(e) {
                // N = New Surat Jalan
                if (e.keyCode === 78 && !e.ctrlKey) {
                    window.location.href = '<?= base_url('surat_jalan/tambah') ?>';
                }

                // L = List View
                if (e.keyCode === 76 && !e.ctrlKey) {
                    window.location.href = '<?= base_url('surat_jalan') ?>';
                }

                // R = Refresh
                if (e.keyCode === 82 && e.ctrlKey) {
                    e.preventDefault();
                    location.reload();
                }
            });

            console.log('💡 Keyboard Shortcuts:');
            console.log('   N : New Surat Jalan');
            console.log('   L : List View');
            console.log('   Ctrl + R : Refresh');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            // Animate numbers on load
            $('.h5, .h3, .h4, .h6').each(function() {
                const $this = $(this);
                const text = $this.text();

                // Check if contains number
                if (/\d/.test(text)) {
                    $this.css('opacity', '0').animate({
                        opacity: 1
                    }, 600);
                }
            });

            // Pulse animation for overdue items
            setInterval(function() {
                $('.overdue-item').animate({
                    opacity: 0.7
                }, 500).animate({
                    opacity: 1
                }, 500);
            }, 2000);
        });

        // Format number helper
        function formatNumber(num) {
            return Math.round(num).toLocaleString('id-ID');
        }

        function formatRupiah(angka) {
            return 'Rp ' + formatNumber(angka);
        }
    </script>
</body>

</html>