<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            border-radius: 12px;
            border: none;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, .15) !important;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-label {
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #858796;
        }

        .fleet-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 12px;
            padding: 1.2rem 1.8rem;
            margin-bottom: 1.2rem;
            color: #fff;
        }

        .section-title {
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #4e73df;
            border-left: 4px solid #4e73df;
            padding-left: 10px;
            margin-bottom: 0;
        }

        .chart-container {
            position: relative;
            height: 250px;
        }

        .alert-table th {
            font-size: .73rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            background: #f8f9fc;
            font-weight: 700;
        }

        .alert-table td {
            vertical-align: middle;
            font-size: .84rem;
        }

        .nopol-badge {
            background: #e8f0fe;
            color: #1a56db;
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: .82rem;
            font-family: monospace;
        }

        .bd-expired {
            background: #e74a3b;
            color: #fff;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
        }

        .bd-soon {
            background: #f6c23e;
            color: #333;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
        }

        .bd-ok {
            background: #1cc88a;
            color: #fff;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
        }

        .bd-proses {
            background: #4e73df;
            color: #fff;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
        }

        .progress-km {
            height: 6px;
            border-radius: 3px;
        }

        .tab-nav .nav-link {
            border-radius: 8px;
            font-weight: 600;
            font-size: .85rem;
            color: #858796;
        }

        .tab-nav .nav-link.active {
            background: #4e73df;
            color: #fff;
        }

        .summary-stat {
            text-align: center;
            padding: 10px;
        }

        .summary-stat .val {
            font-size: 1.4rem;
            font-weight: 800;
        }

        .summary-stat .lbl {
            font-size: .7rem;
            color: #858796;
            text-transform: uppercase;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .activity-meta {
            font-size: .75rem;
            color: #858796;
        }

        .filter-pill {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid #e3e6f0;
            color: #6c757d;
            transition: all .15s;
        }

        .filter-pill.active,
        .filter-pill:hover {
            background: #4e73df;
            border-color: #4e73df;
            color: #fff;
        }

        .driver-rank {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .driver-rank:last-child {
            border-bottom: none;
        }

        .rank-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #4e73df;
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .rank-num.gold {
            background: #f6c23e;
            color: #333;
        }

        .rank-num.silver {
            background: #858796;
        }

        .rank-num.bronze {
            background: #e8a87c;
        }

        .status-toggle {
            cursor: pointer;
            transition: opacity .2s;
        }

        .status-toggle:hover {
            opacity: .8;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <!-- FLASH -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-1"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- FLEET HEADER -->
                    <div class="fleet-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h4 class="mb-1 fw-bold"><i class="fas fa-tachometer-alt me-2"></i>Fleet Dashboard</h4>
                            <p class="mb-0" style="opacity:.8;font-size:.88rem">
                                Monitor kondisi armada secara real-time — <?= date('d F Y') ?>
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?= base_url('unit/tambah') ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Tambah Unit
                            </a>
                            <a href="<?= base_url('unit') ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-truck me-1"></i> Kelola Unit
                            </a>
                            <button class="btn btn-outline-light btn-sm" id="btnExcelUnit">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </button>
                            <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalLaporanBBM">
                                <i class="fas fa-print me-1"></i> Laporan BBM
                            </button>
                        </div>
                    </div>

                    <!-- FILTER PERIODE -->
                    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                        <small class="text-muted fw-bold me-1">Periode:</small>
                        <span class="filter-pill <?= $range == 7 ? 'active' : '' ?>" onclick="setRange(7)">7 Hari</span>
                        <span class="filter-pill <?= $range == 30 ? 'active' : '' ?>" onclick="setRange(30)">30 Hari</span>
                        <span class="filter-pill <?= $range == 90 ? 'active' : '' ?>" onclick="setRange(90)">90 Hari</span>
                        <small class="text-muted ms-1">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <?= date('d M Y', strtotime("-{$range} days")) ?> &mdash; <?= date('d M Y') ?>
                        </small>
                    </div>

                    <!-- STAT CARDS -->
                    <div class="row g-3 mb-3">
                        <?php
                        $cards = [
                            ['val' => $total, 'label' => 'Total Unit', 'color' => 'primary', 'icon' => '🚛'],
                            ['val' => $aktif, 'label' => 'Aktif', 'color' => 'success', 'icon' => '✅'],
                            ['val' => $maintenance, 'label' => 'Maintenance', 'color' => 'warning', 'icon' => '🔧'],
                            ['val' => $rusak, 'label' => 'Rusak', 'color' => 'danger', 'icon' => '⚠️'],
                            ['val' => $doc_expired, 'label' => 'Dok Expired', 'color' => 'danger', 'icon' => '📋'],
                            ['val' => $doc_soon, 'label' => 'Dok 30 Hari', 'color' => 'warning', 'icon' => '📅'],
                            ['val' => $service_due, 'label' => 'Perlu Service', 'color' => 'warning', 'icon' => '🛠️'],
                        ];
                        foreach ($cards as $c): ?>
                            <div class="col-xl col-md-4 col-6">
                                <div class="card stat-card shadow-sm p-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-<?= $c['color'] ?> bg-opacity-10 text-<?= $c['color'] ?>">
                                            <?= $c['icon'] ?>
                                        </div>
                                        <div>
                                            <div class="stat-value text-<?= $c['color'] ?>"><?= $c['val'] ?></div>
                                            <div class="stat-label"><?= $c['label'] ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <!-- CHARTS ROW -->
                    <div class="row g-3 mb-3">

                        <!-- Donut status -->
                        <div class="col-xl-3 col-lg-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white py-2">
                                    <p class="section-title">Status Armada</p>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center">
                                    <div class="chart-container w-100">
                                        <canvas id="chartStatus"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bar tipe -->
                        <div class="col-xl-4 col-lg-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white py-2">
                                    <p class="section-title">Distribusi Tipe Unit</p>
                                </div>
                                <div class="card-body d-flex align-items-center">
                                    <div class="chart-container w-100">
                                        <canvas id="chartTipe"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary operasional -->
                        <div class="col-xl-5 col-lg-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white py-2">
                                    <p class="section-title">Operasional <?= $range ?> Hari Terakhir</p>
                                </div>
                                <div class="card-body py-2">
                                    <?php
                                    $pct_aktif = $total > 0 ? round($aktif / $total * 100) : 0;
                                    $pct_maint = $total > 0 ? round($maintenance / $total * 100) : 0;
                                    ?>
                                    <div class="row g-0">
                                        <div class="col-6">
                                            <div class="summary-stat border-end">
                                                <div class="val text-success">
                                                    <?= number_format($bbm_period->total_liter ?? 0, 0) ?>L</div>
                                                <div class="lbl">Total BBM</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="summary-stat">
                                                <div class="val text-info"><?= $bbm_period->total_isi ?? 0 ?>x</div>
                                                <div class="lbl">Pengisian BBM</div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row g-0">
                                        <div class="col-6">
                                            <div class="summary-stat border-end">
                                                <div class="val text-warning">Rp
                                                    <?= number_format(($bbm_period->total_biaya ?? 0) / 1000000, 1) ?>jt
                                                </div>
                                                <div class="lbl">Biaya BBM</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="summary-stat">
                                                <div class="val text-danger">Rp
                                                    <?= number_format(($service_period->total_biaya ?? 0) / 1000000, 1) ?>jt
                                                </div>
                                                <div class="lbl">Biaya Service</div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row g-0">
                                        <div class="col-6">
                                            <div class="summary-stat border-end">
                                                <div class="val text-primary">
                                                    <?= $service_period->total_service ?? 0 ?>x</div>
                                                <div class="lbl">Total Service</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="summary-stat">
                                                <div class="val text-danger"><?= $doc_expired ?></div>
                                                <div class="lbl">Dok Expired</div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="fw-bold">Unit Aktif</small>
                                            <small class="text-success fw-bold"><?= $pct_aktif ?>%</small>
                                        </div>
                                        <div class="progress progress-km">
                                            <div class="progress-bar bg-success" style="width:<?= $pct_aktif ?>%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="fw-bold">Maintenance</small>
                                            <small class="text-warning fw-bold"><?= $pct_maint ?>%</small>
                                        </div>
                                        <div class="progress progress-km">
                                            <div class="progress-bar bg-warning" style="width:<?= $pct_maint ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BBM TREN + TOP DRIVER -->
                    <div class="row g-3 mb-3">
                        <div class="col-xl-8">
                            <div class="card shadow-sm h-100">
                                <div
                                    class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                    <p class="section-title">📈 Tren Pengisian BBM</p>
                                    <small class="text-muted"><?= $range ?> hari terakhir</small>
                                </div>
                                <div class="card-body">
                                    <div style="position:relative;height:210px">
                                        <canvas id="chartBBMTren"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white py-2">
                                    <p class="section-title">🏆 Top Driver BBM</p>
                                    <small class="text-muted"><?= $range ?> hari terakhir</small>
                                </div>
                                <div class="card-body py-2">
                                    <?php if (empty($top_drivers)): ?>
                                        <p class="text-muted text-center py-3 mb-0"><small>Belum ada data driver</small></p>
                                    <?php else:
                                        $rank_colors = ['gold', 'silver', 'bronze'];
                                        foreach ($top_drivers as $i => $d): ?>
                                            <div class="driver-rank">
                                                <div class="rank-num <?= $rank_colors[$i] ?? '' ?>"><?= $i + 1 ?></div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold" style="font-size:.85rem">
                                                        <?= htmlspecialchars($d->driver_nama ?? '-') ?></div>
                                                    <div class="activity-meta"><?= $d->total_isi ?>x isi &middot;
                                                        <?= number_format($d->total_liter, 0) ?>L</div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-success fw-bold d-block">Rp
                                                        <?= number_format($d->total_biaya / 1000, 0) ?>rb</small>
                                                    <?php if ($d->avg_konsumsi > 0): ?>
                                                        <small
                                                            class="<?= $d->avg_konsumsi >= 8 ? 'text-success' : ($d->avg_konsumsi >= 5 ? 'text-warning' : 'text-danger') ?>">
                                                            <?= number_format($d->avg_konsumsi, 1) ?> km/L
                                                        </small>
                                                    <?php endif ?>
                                                </div>
                                            </div>
                                        <?php endforeach; endif ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ALERT TABS + AKTIVITAS -->
                    <div class="row g-3">
                        <div class="col-xl-8">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white py-2">
                                    <ul class="nav tab-nav" id="alertTab">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#tabDokumen">
                                                <i class="fas fa-file-alt me-1"></i> Alert Dokumen
                                                <?php if ($doc_expired > 0): ?>
                                                    <span class="badge bg-danger ms-1"><?= $doc_expired ?></span>
                                                <?php elseif ($doc_soon > 0): ?>
                                                    <span class="badge bg-warning ms-1"><?= $doc_soon ?></span>
                                                <?php endif ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#tabService">
                                                <i class="fas fa-wrench me-1"></i> Alert Service
                                                <?php if ($service_due > 0): ?>
                                                    <span class="badge bg-warning ms-1"><?= $service_due ?></span>
                                                <?php endif ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#tabSemua">
                                                <i class="fas fa-list me-1"></i> Semua Unit
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body p-0">
                                    <div class="tab-content">

                                        <!-- TAB DOKUMEN -->
                                        <div class="tab-pane fade show active" id="tabDokumen">
                                            <div class="table-responsive">
                                                <table class="table table-hover alert-table align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="ps-3">No Polisi</th>
                                                            <th>Jenis Dok</th>
                                                            <th>No Dokumen</th>
                                                            <th>Expired</th>
                                                            <th>Sisa Hari</th>
                                                            <th>Status</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($doc_alerts)): ?>
                                                            <tr>
                                                                <td colspan="7" class="text-center py-4 text-muted">
                                                                    <i
                                                                        class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                                                    Semua dokumen dalam kondisi baik! 🎉
                                                                </td>
                                                            </tr>
                                                        <?php else:
                                                            foreach ($doc_alerts as $d):
                                                                $diff = !empty($d->tanggal_expired) ? (strtotime($d->tanggal_expired) - time()) / 86400 : 999;
                                                                $row_bg = $diff <= 0 ? 'table-danger' : ($diff <= 7 ? 'table-warning' : '');
                                                                ?>
                                                                <tr class="<?= $row_bg ?>">
                                                                    <td class="ps-3">
                                                                        <a href="<?= base_url('unit/detail/' . $d->unit_id) ?>">
                                                                            <span
                                                                                class="nopol-badge"><?= strtoupper($d->no_polisi ?? '') ?></span>
                                                                        </a>
                                                                    </td>
                                                                    <td><span
                                                                            class="badge bg-secondary text-uppercase"><?= $d->jenis_dokumen ?></span>
                                                                    </td>
                                                                    <td><small><?= htmlspecialchars($d->nomor_dokumen ?? '-') ?></small>
                                                                    </td>
                                                                    <td class="fw-bold">
                                                                        <?= !empty($d->tanggal_expired) ? date('d/m/Y', strtotime($d->tanggal_expired)) : '-' ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if ($diff <= 0): ?>
                                                                            <span class="bd-expired">❌ Expired</span>
                                                                        <?php elseif ($diff <= 7): ?>
                                                                            <span class="bd-soon">🔥 <?= ceil($diff) ?> hari</span>
                                                                        <?php else: ?>
                                                                            <span class="bd-soon">⚠️ <?= ceil($diff) ?> hari</span>
                                                                        <?php endif ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if ($d->status == 'expired'): ?>
                                                                            <span class="bd-expired">EXPIRED</span>
                                                                        <?php elseif ($d->status == 'diproses'): ?>
                                                                            <span class="bd-proses">DIPROSES</span>
                                                                        <?php else: ?>
                                                                            <span class="bd-ok">AKTIF</span>
                                                                        <?php endif ?>
                                                                    </td>
                                                                    <td>
                                                                        <a href="<?= base_url('unit/detail/' . $d->unit_id) ?>#tabDokumen"
                                                                            class="btn btn-warning btn-sm">
                                                                            <i class="fas fa-edit"></i> Update
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; endif ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- TAB SERVICE -->
                                        <div class="tab-pane fade" id="tabService">
                                            <div class="table-responsive">
                                                <table class="table table-hover alert-table align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="ps-3">No Polisi</th>
                                                            <th>Tipe</th>
                                                            <th>KM Saat Ini</th>
                                                            <th>Target Service</th>
                                                            <th>Sisa KM</th>
                                                            <th>Status</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($service_alerts)): ?>
                                                            <tr>
                                                                <td colspan="7" class="text-center py-4 text-muted">
                                                                    <i
                                                                        class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                                                    Semua unit tidak memerlukan service segera! 🎉
                                                                </td>
                                                            </tr>
                                                        <?php else:
                                                            foreach ($service_alerts as $u):
                                                                $current = $u->current_km ?? 0;
                                                                $next = $u->next_service_km ?? 0;
                                                                $sisa = $next - $current;
                                                                $pct = min(100, max(0, ($current / max($next, 1)) * 100));
                                                                $sc = ['aktif' => 'success', 'maintenance' => 'warning', 'rusak' => 'danger', 'dijual' => 'secondary', 'nonaktif' => 'secondary'];
                                                                $s = $u->status_unit ?? 'aktif';
                                                                ?>
                                                                <tr>
                                                                    <td class="ps-3">
                                                                        <a href="<?= base_url('unit/detail/' . $u->id) ?>">
                                                                            <span
                                                                                class="nopol-badge"><?= strtoupper($u->no_polisi ?? '') ?></span>
                                                                        </a>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($u->tipe_unit ?? '-') ?></td>
                                                                    <td>
                                                                        <strong><?= number_format($current) ?> km</strong>
                                                                        <div class="progress progress-km mt-1"
                                                                            style="width:90px">
                                                                            <div class="progress-bar <?= $sisa <= 0 ? 'bg-danger' : ($sisa <= 2000 ? 'bg-warning' : 'bg-info') ?>"
                                                                                style="width:<?= $pct ?>%"></div>
                                                                        </div>
                                                                    </td>
                                                                    <td><?= number_format($next) ?> km</td>
                                                                    <td>
                                                                        <?php if ($sisa <= 0): ?>
                                                                            <span class="bd-expired">❌ LEWAT
                                                                                <?= number_format(abs($sisa)) ?> km</span>
                                                                        <?php else: ?>
                                                                            <span class="bd-soon">⚠️ <?= number_format($sisa) ?> km
                                                                                lagi</span>
                                                                        <?php endif ?>
                                                                    </td>
                                                                    <td><span
                                                                            class="badge bg-<?= $sc[$s] ?? 'secondary' ?>"><?= strtoupper($s) ?></span>
                                                                    </td>
                                                                    <td>
                                                                        <a href="<?= base_url('unit/detail/' . $u->id) ?>#tabService"
                                                                            class="btn btn-warning btn-sm">
                                                                            <i class="fas fa-wrench"></i> Catat
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; endif ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- TAB SEMUA UNIT -->
                                        <div class="tab-pane fade" id="tabSemua">
                                            <div class="table-responsive">
                                                <table class="table table-hover alert-table align-middle mb-0"
                                                    id="tblSemua">
                                                    <thead>
                                                        <tr>
                                                            <th class="ps-3">No Polisi</th>
                                                            <th>Tipe Unit</th>
                                                            <th>Tipe Box</th>
                                                            <th>Tahun</th>
                                                            <th>Tonase</th>
                                                            <th>KM</th>
                                                            <th>Status</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($units as $u):
                                                            $sc = ['aktif' => 'success', 'maintenance' => 'warning', 'rusak' => 'danger', 'dijual' => 'secondary', 'nonaktif' => 'secondary'];
                                                            $s = $u->status_unit ?? 'aktif';
                                                            ?>
                                                            <tr>
                                                                <td class="ps-3">
                                                                    <a href="<?= base_url('unit/detail/' . $u->id) ?>">
                                                                        <span
                                                                            class="nopol-badge"><?= strtoupper($u->no_polisi ?? '') ?></span>
                                                                    </a>
                                                                </td>
                                                                <td><?= htmlspecialchars($u->tipe_unit ?? '-') ?></td>
                                                                <td><?= htmlspecialchars($u->tipe_box ?? '-') ?></td>
                                                                <td><?= $u->tahun_unit ?? '-' ?></td>
                                                                <td><?= $u->tonase ?? '-' ?> Ton</td>
                                                                <td><?= !empty($u->current_km) ? number_format($u->current_km) . ' km' : '-' ?>
                                                                </td>
                                                                <td>
                                                                    <div class="dropdown">
                                                                        <span
                                                                            class="badge bg-<?= $sc[$s] ?? 'secondary' ?> status-toggle dropdown-toggle"
                                                                            data-bs-toggle="dropdown"
                                                                            style="cursor:pointer;font-size:.78rem;padding:5px 10px">
                                                                            <?= strtoupper($s) ?> <i
                                                                                class="fas fa-chevron-down fa-xs"></i>
                                                                        </span>
                                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                                                                            style="min-width:140px">
                                                                            <li>
                                                                                <h6 class="dropdown-header"
                                                                                    style="font-size:.7rem">Ubah Status</h6>
                                                                            </li>
                                                                            <?php foreach (['aktif' => ['success', '✅'], 'maintenance' => ['warning', '🔧'], 'rusak' => ['danger', '⚠️'], 'nonaktif' => ['secondary', '🚫']] as $val => [$color, $icon]): ?>
                                                                                <?php if ($val != $s): ?>
                                                                                    <li>
                                                                                        <a class="dropdown-item btn-ubah-status"
                                                                                            href="#" data-id="<?= $u->id ?>"
                                                                                            data-status="<?= $val ?>"
                                                                                            data-nopol="<?= strtoupper($u->no_polisi) ?>"
                                                                                            style="font-size:.82rem">
                                                                                            <?= $icon ?>             <?= ucfirst($val) ?>
                                                                                        </a>
                                                                                    </li>
                                                                                <?php endif ?>
                                                                            <?php endforeach ?>
                                                                        </ul>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <a href="<?= base_url('unit/detail/' . $u->id) ?>"
                                                                        class="btn btn-info btn-sm"><i
                                                                            class="fas fa-eye"></i></a>
                                                                    <a href="<?= base_url('unit/ubah/' . $u->id) ?>"
                                                                        class="btn btn-success btn-sm"><i
                                                                            class="fas fa-edit"></i></a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AKTIVITAS TERBARU -->
                        <div class="col-xl-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-white py-2">
                                    <p class="section-title"><i class="fas fa-wrench text-warning me-1"></i> Service
                                        Terbaru</p>
                                </div>
                                <div class="card-body py-2">
                                    <?php if (empty($recent_services)): ?>
                                        <p class="text-muted text-center py-3 mb-0"><small>Belum ada histori service</small>
                                        </p>
                                    <?php else:
                                        $jenis_icons = ['service_rutin' => '🔧', 'perbaikan' => '🛠️', 'ganti_oli' => '🛢️', 'ganti_ban' => '🔄', 'ganti_aki' => '⚡', 'tune_up' => '⚙️', 'lainnya' => '📋'];
                                        foreach ($recent_services as $m): ?>
                                            <div class="activity-item">
                                                <div class="activity-icon bg-warning text-white">
                                                    <?= $jenis_icons[$m->jenis_service] ?? '🔧' ?></div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold" style="font-size:.85rem">
                                                        <span class="nopol-badge"><?= strtoupper($m->no_polisi) ?></span>
                                                        <small class="ms-1 text-muted"><?= $m->tipe_unit ?></small>
                                                    </div>
                                                    <div class="activity-meta">
                                                        <?= ucfirst(str_replace('_', ' ', $m->jenis_service)) ?>        <?= $m->bengkel ? ' &middot; ' . $m->bengkel : '' ?>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <small
                                                        class="text-muted d-block"><?= date('d/m/Y', strtotime($m->tanggal_service)) ?></small>
                                                    <?php if ($m->biaya > 0): ?>
                                                        <small class="text-danger fw-bold">Rp
                                                            <?= number_format($m->biaya / 1000, 0) ?>rb</small>
                                                    <?php endif ?>
                                                </div>
                                            </div>
                                        <?php endforeach; endif ?>
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-header bg-white py-2">
                                    <p class="section-title"><i class="fas fa-gas-pump text-success me-1"></i> Pengisian
                                        BBM Terbaru</p>
                                </div>
                                <div class="card-body py-2">
                                    <?php if (empty($recent_fuels)): ?>
                                        <p class="text-muted text-center py-3 mb-0"><small>Belum ada histori BBM</small></p>
                                    <?php else:
                                        foreach ($recent_fuels as $f): ?>
                                            <div class="activity-item">
                                                <div class="activity-icon bg-success text-white">⛽</div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold" style="font-size:.85rem">
                                                        <span class="nopol-badge"><?= strtoupper($f->no_polisi) ?></span>
                                                        <small class="ms-1 text-muted"><?= $f->tipe_unit ?></small>
                                                    </div>
                                                    <div class="activity-meta">
                                                        <?= strtoupper($f->jenis_bbm) ?> &middot;
                                                        <?= number_format($f->liter, 1) ?>L<?= $f->spbu ? ' &middot; ' . $f->spbu : '' ?>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <small
                                                        class="text-muted d-block"><?= date('d/m/Y', strtotime($f->tanggal_isi)) ?></small>
                                                    <small class="text-success fw-bold">Rp
                                                        <?= number_format($f->total_biaya / 1000, 0) ?>rb</small>
                                                </div>
                                            </div>
                                        <?php endforeach; endif ?>
                                </div>
                            </div>
                        </div>

                    </div><!-- end row -->

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- MODAL LAPORAN BBM -->
    <div class="modal fade" id="modalLaporanBBM" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);color:#fff">
                    <h5 class="modal-title"><i class="fas fa-print me-2"></i>Cetak Laporan BBM</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= base_url('fleet/laporan_bbm') ?>" method="GET" target="_blank">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Dari Tanggal</label>
                                <input type="date" name="dari" class="form-control form-control-sm"
                                    value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Sampai Tanggal</label>
                                <input type="date" name="sampai" class="form-control form-control-sm"
                                    value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Unit (Opsional)</label>
                                <select name="unit_id" class="form-select form-select-sm">
                                    <option value="">-- Semua Unit --</option>
                                    <?php foreach ($units as $u): ?>
                                        <option value="<?= $u->id ?>"><?= strtoupper($u->no_polisi) ?> &mdash;
                                            <?= $u->tipe_unit ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Format Output</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" value="print" checked
                                            id="fmtPrint">
                                        <label class="form-check-label small" for="fmtPrint"><i
                                                class="fas fa-print me-1"></i> Cetak / PDF</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" value="excel"
                                            id="fmtExcel">
                                        <label class="form-check-label small" for="fmtExcel"><i
                                                class="fas fa-file-excel me-1"></i> Excel</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-print me-1"></i> Cetak Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL UBAH STATUS -->
    <div class="modal fade" id="modalUbahStatus" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exchange-alt me-1"></i> Ubah Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-1">Ubah status <strong id="status_nopol"></strong> menjadi</p>
                    <h5 class="fw-bold" id="status_new_label"></h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning btn-sm" id="btnKonfirmasiStatus">
                        <i class="fas fa-check me-1"></i> Ya, Ubah
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {

            function setRange(r) { window.location = '<?= base_url('fleet') ?>?range=' + r; }
            window.setRange = setRange;

            new Chart(document.getElementById('chartStatus'), {
                type: 'doughnut',
                data: {
                    labels: ['Aktif', 'Maintenance', 'Rusak', 'Dijual', 'Non-Aktif'],
                    datasets: [{
                        data: [<?= $aktif ?>, <?= $maintenance ?>, <?= $rusak ?>, <?= $dijual ?? 0 ?>, <?= $nonaktif ?? 0 ?>],
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'],
                        borderWidth: 2, borderColor: '#fff'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } } }, cutout: '65%' }
            });

            const tipeData = <?= json_encode($tipe_chart ?? []) ?>;
            new Chart(document.getElementById('chartTipe'), {
                type: 'bar',
                data: {
                    labels: Object.keys(tipeData),
                    datasets: [{ label: 'Jumlah Unit', data: Object.values(tipeData), backgroundColor: '#4e73df', borderRadius: 6, borderSkipped: false }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f0f0f0' } }, x: { grid: { display: false } } } }
            });

            const bbmTren = <?= json_encode($bbm_tren ?? []) ?>;
            new Chart(document.getElementById('chartBBMTren'), {
                type: 'line',
                data: {
                    labels: bbmTren.map(d => d.label),
                    datasets: [
                        { label: 'Liter', data: bbmTren.map(d => d.liter), borderColor: '#1cc88a', backgroundColor: 'rgba(28,200,138,.08)', fill: true, tension: .4, pointRadius: 4, pointBackgroundColor: '#1cc88a', yAxisID: 'y' },
                        { label: 'Biaya (rb)', data: bbmTren.map(d => Math.round(d.biaya / 1000)), borderColor: '#4e73df', backgroundColor: 'rgba(78,115,223,.05)', fill: false, tension: .4, pointRadius: 4, pointBackgroundColor: '#4e73df', yAxisID: 'y1', borderDash: [4, 4] }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { font: { size: 11 }, padding: 10 } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f0f0f0' }, title: { display: true, text: 'Liter' } },
                        y1: { beginAtZero: true, position: 'right', grid: { display: false }, title: { display: true, text: 'Biaya (Rp rb)' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            $('#tblSemua').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                pageLength: 10, order: [[0, 'asc']], responsive: true, destroy: true
            });

            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });

            $('#btnExcelUnit').on('click', function () {
                window.location = '<?= base_url('fleet/export_excel') ?>';
            });

            let _statusId = null, _statusVal = null;
            $(document).on('click', '.btn-ubah-status', function (e) {
                e.preventDefault();
                _statusId = $(this).data('id');
                _statusVal = $(this).data('status');
                const labels = { aktif: '✅ AKTIF', maintenance: '🔧 MAINTENANCE', rusak: '⚠️ RUSAK', nonaktif: '🚫 NON-AKTIF' };
                $('#status_nopol').text($(this).data('nopol'));
                $('#status_new_label').text(labels[_statusVal] || _statusVal.toUpperCase());
                new bootstrap.Modal(document.getElementById('modalUbahStatus')).show();
            });

            $('#btnKonfirmasiStatus').on('click', function () {
                if (!_statusId || !_statusVal) return;
                $.post('<?= base_url('fleet/ubah_status') ?>', {
                    id: _statusId, status: _statusVal,
                    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                }, function (res) {
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalUbahStatus')).hide();
                        location.reload();
                    } else {
                        alert('Gagal: ' + (res.message || 'Unknown error'));
                    }
                }, 'json').fail(() => alert('Terjadi kesalahan, coba lagi.'));
            });

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });
    </script>
</body>

</html>