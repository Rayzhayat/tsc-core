<!-- performa.php — REPLACE FULL FILE -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .dash-hero {
            background: linear-gradient(135deg, #1a1f3a 0%, #2d3561 60%, #1e3a5f 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .dash-hero::after {
            content: '';
            position: absolute;
            right: -40px;
            top: -40px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .dash-hero h1 {
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0;
        }

        .dash-hero p {
            opacity: 0.6;
            font-size: 0.85rem;
            margin: 4px 0 0;
        }

        .sum-card {
            border-radius: 14px;
            padding: 20px 22px;
            color: #fff;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .sum-card::before {
            content: '';
            position: absolute;
            right: -20px;
            bottom: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .sum-card .sum-val {
            font-size: 2rem;
            font-weight: 900;
            line-height: 1;
        }

        .sum-card .sum-lbl {
            font-size: 0.72rem;
            opacity: 0.75;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .sum-card .sum-icon {
            font-size: 2rem;
            opacity: 0.18;
            position: absolute;
            right: 18px;
            top: 18px;
        }

        .sc-blue {
            background: linear-gradient(135deg, #4e73df, #3a5fc7);
        }

        .sc-green {
            background: linear-gradient(135deg, #1cc88a, #17a673);
        }

        .sc-yellow {
            background: linear-gradient(135deg, #f6c23e, #e0a800);
        }

        .sc-red {
            background: linear-gradient(135deg, #e74a3b, #c0392b);
        }

        .sc-teal {
            background: linear-gradient(135deg, #36b9cc, #2a9ab0);
        }

        /* ── Group filter tabs ── */
        .group-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .group-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
            background: #f8f9fc;
            color: #6c757d;
            border-color: #e3e6f0;
        }

        .group-tab.active,
        .group-tab:hover {
            border-color: currentColor;
        }

        .group-tab .gdot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .gt-all {
            color: #4e73df;
        }

        .gt-all.active {
            background: #eef0fd;
            border-color: #4e73df;
        }

        .gt-yamazaki {
            color: #c2185b;
        }

        .gt-yamazaki.active {
            background: #fff0f6;
            border-color: #c2185b;
        }

        .gt-admin {
            color: #1565c0;
        }

        .gt-admin.active {
            background: #e8f4fd;
            border-color: #1565c0;
        }

        .gt-ops {
            color: #2e7d32;
        }

        .gt-ops.active {
            background: #e8f5e9;
            border-color: #2e7d32;
        }

        .gt-tsf {
            color: #f57f17;
        }

        .gt-tsf.active {
            background: #fff8e1;
            border-color: #f57f17;
        }

        .gt-sinar {
            color: #6a1b9a;
        }

        .gt-sinar.active {
            background: #f3e5f5;
            border-color: #6a1b9a;
        }

        .gt-rorotan {
            color: #bf360c;
        }

        .gt-rorotan.active {
            background: #fbe9e7;
            border-color: #bf360c;
        }

        .rank-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .rank-table thead th {
            background: #f8f9fc;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #b7b9cc;
            padding: 10px 16px;
            border-bottom: 2px solid #e3e6f0;
        }

        .rank-table tbody tr {
            transition: background 0.15s;
        }

        .rank-table tbody tr:hover {
            background: #f8f9fc;
        }

        .rank-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f2f8;
            vertical-align: middle;
            font-size: 0.88rem;
        }

        .rank-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 0.8rem;
        }

        .rank-1 {
            background: #fff3cd;
            color: #b8860b;
        }

        .rank-2 {
            background: #e8e8e8;
            color: #555;
        }

        .rank-3 {
            background: #fde8d8;
            color: #a04000;
        }

        .rank-n {
            background: #f0f2f8;
            color: #858796;
        }

        .skor-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 0.82rem;
        }

        .mini-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e3e6f0;
        }

        .sp-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 2px;
        }

        .sp-dot.sp1 {
            background: #f59e0b;
        }

        .sp-dot.sp2 {
            background: #ea580c;
        }

        .sp-dot.sp3 {
            background: #dc2626;
        }

        .mini-bar {
            height: 6px;
            border-radius: 4px;
            background: #f0f2f8;
            overflow: hidden;
            margin-top: 3px;
            width: 80px;
        }

        .mini-bar-fill {
            height: 100%;
            border-radius: 4px;
        }

        .pending-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f2f8;
        }

        .pending-item:last-child {
            border-bottom: none;
        }

        .chart-card {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .chart-card h6 {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #b7b9cc;
            margin-bottom: 16px;
        }

        .gbadge-sm {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            border-radius: 5px;
            font-size: 0.65rem;
            font-weight: 600;
            border: 1px solid;
        }

        /* ══════════════════════════════════════════
           MODAL REKAP KALENDER ABSENSI — Styles
        ══════════════════════════════════════════ */
        .kal-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
            margin-bottom: 4px;
        }

        .kal-header span {
            text-align: center;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #b7b9cc;
            padding: 4px 0;
        }

        .kal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
        }

        .kal-cell {
            aspect-ratio: 1;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: default;
            position: relative;
            transition: transform 0.1s;
            min-height: 36px;
        }

        .kal-cell:hover {
            transform: scale(1.08);
        }

        .kal-cell.empty {
            background: transparent;
            cursor: default;
            pointer-events: none;
        }

        .kal-cell.hadir {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #6ee7b7;
        }

        .kal-cell.tidak_hadir {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #fca5a5;
        }

        .kal-cell.libur {
            background: #f3f4f6;
            color: #9ca3af;
            border: 2px solid #e5e7eb;
        }

        .kal-cell.off_khusus {
            background: #fef9c3;
            color: #92400e;
            border: 2px solid #fde68a;
        }

        .kal-cell.belum {
            background: #f8f9fc;
            color: #d1d5db;
            border: 2px dashed #e5e7eb;
        }

        .kal-cell .kal-icon {
            font-size: 0.6rem;
            margin-top: 1px;
            opacity: 0.8;
        }

        .kal-summary {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .kal-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .kal-pill .kal-pill-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .kal-legend {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e3e6f0;
        }

        .kal-legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.72rem;
            color: #6c757d;
        }

        .kal-legend-box {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid;
        }

        .kal-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kal-nav select {
            border: 1px solid #e3e6f0;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #3a3b45;
            background: #f8f9fc;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <?php
                    $total_karyawan = count($performa_list);
                    $cuti_pending_ct = count($cuti_pending);
                    $dok_expired_ct = count($dok_hampir_expired);

                    $avg_kehadiran = 0;
                    if ($total_karyawan > 0) {
                        $avg_kehadiran = round(array_sum(array_column($performa_list, 'persen_kehadiran')) / $total_karyawan, 1);
                    }

                    $performa_scored = [];
                    foreach ($performa_list as $p) {
                        $jatah = (int) ($p->jatah_cuti ?? 12);
                        $sisa = (int) ($p->sisa_cuti ?? $jatah);
                        $sp = (int) ($p->jumlah_sp ?? 0);
                        $pct = (float) ($p->persen_kehadiran ?? 0);
                        $skor = min(100, round($pct * 0.6 + ($jatah > 0 ? ($sisa / $jatah) * 100 * 0.2 : 20) + max(0, 20 - ($sp * 10))));
                        $performa_scored[] = array_merge((array) $p, ['skor' => $skor]);
                    }
                    usort($performa_scored, fn($a, $b) => $b['skor'] - $a['skor']);

                    $excellent = count(array_filter($performa_scored, fn($p) => $p['skor'] >= 80));
                    $good = count(array_filter($performa_scored, fn($p) => $p['skor'] >= 65 && $p['skor'] < 80));
                    $average = count(array_filter($performa_scored, fn($p) => $p['skor'] >= 50 && $p['skor'] < 65));
                    $poor = count(array_filter($performa_scored, fn($p) => $p['skor'] < 50));

                    $group_defs = [
                        '' => ['all', '#4e73df', 'Semua Group'],
                        'Yamazaki Staff' => ['yamazaki', '#c2185b', 'Yamazaki Staff'],
                        'Admin TSC' => ['admin', '#1565c0', 'Admin TSC'],
                        'Operasional TSC' => ['ops', '#2e7d32', 'Operasional TSC'],
                        'TSF Staff' => ['tsf', '#f57f17', 'TSF Staff'],
                        'Sinar Boga Staff' => ['sinar', '#6a1b9a', 'Sinar Boga Staff'],
                        'Rorotan Staff' => ['rorotan', '#bf360c', 'Rorotan Staff'],
                    ];

                    $group_counts = [];
                    foreach ($performa_scored as $p) {
                        $g = $p['group_karyawan'] ?? '';
                        $group_counts[$g] = ($group_counts[$g] ?? 0) + 1;
                    }
                    ?>

                    <!-- Hero -->
                    <div class="dash-hero">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h1><i class="fas fa-chart-line me-2" style="opacity:0.7;"></i><?= $title ?></h1>
                                <p>Data kehadiran, cuti, dan skor performa karyawan · <?= date('F Y') ?>
                                    <?php if (!empty($active_group)): ?>
                                        · <strong><?= $active_group ?></strong>
                                    <?php endif ?>
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('absensi/laporan') ?>" class="btn btn-sm"
                                    style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);">
                                    <i class="fas fa-clipboard-list"></i> Laporan Absensi
                                </a>
                                <a href="<?= base_url('pengguna') ?>" class="btn btn-sm"
                                    style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);">
                                    <i class="fas fa-users"></i> Master Karyawan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ── GROUP FILTER TABS ── -->
                    <div class="group-tabs">
                        <?php foreach ($group_defs as $gval => [$gcls, $gclr, $glbl]): ?>
                            <a href="<?= base_url('pengguna/performa') . ($gval ? '?group=' . urlencode($gval) : '') ?>"
                                class="group-tab gt-<?= $gcls ?> <?= ($active_group ?? '') === $gval ? 'active' : '' ?>">
                                <?php if ($gval): ?>
                                    <span class="gdot" style="background:<?= $gclr ?>;"></span>
                                <?php else: ?>
                                    <i class="fas fa-th-large" style="font-size:0.75rem;"></i>
                                <?php endif ?>
                                <?= $glbl ?>
                                <span style="opacity:0.6; font-size:0.7rem;">
                                    (<?= $gval === '' ? $total_karyawan : ($group_counts[$gval] ?? 0) ?>)
                                </span>
                            </a>
                        <?php endforeach ?>
                    </div>

                    <!-- Summary cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-2">
                            <div class="sum-card sc-blue">
                                <div class="sum-val"><?= $total_karyawan ?></div>
                                <div class="sum-lbl">Total Karyawan</div><i class="fas fa-users sum-icon"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="sum-card sc-green">
                                <div class="sum-val"><?= $avg_kehadiran ?>%</div>
                                <div class="sum-lbl">Avg Kehadiran</div><i class="fas fa-calendar-check sum-icon"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="sum-card sc-yellow">
                                <div class="sum-val"><?= $cuti_pending_ct ?></div>
                                <div class="sum-lbl">Cuti Pending</div><i class="fas fa-clock sum-icon"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="sum-card sc-red">
                                <div class="sum-val"><?= $dok_expired_ct ?></div>
                                <div class="sum-lbl">Dok Hampir Exp</div><i class="fas fa-file-contract sum-icon"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="sum-card sc-green">
                                <div class="sum-val"><?= $excellent ?></div>
                                <div class="sum-lbl">Excellent</div><i class="fas fa-star sum-icon"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="sum-card sc-red">
                                <div class="sum-val"><?= $poor ?></div>
                                <div class="sum-lbl">Perlu Perhatian</div><i class="fas fa-exclamation sum-icon"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <div class="chart-card">
                                <h6><i class="fas fa-chart-bar me-1"></i> Kehadiran Bulan Ini per Karyawan</h6>
                                <div style="height:280px;"><canvas id="chartKehadiran"></canvas></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chart-card">
                                <h6><i class="fas fa-chart-pie me-1"></i> Distribusi Skor Performa</h6>
                                <div style="height:200px;"><canvas id="chartDistribusi"></canvas></div>
                                <div class="mt-3">
                                    <?php foreach ([['Excellent (≥80)', $excellent, '#1cc88a'], ['Good (65–79)', $good, '#36b9cc'], ['Average (50–64)', $average, '#f6c23e'], ['Poor (<50)', $poor, '#e74a3b']] as [$lbl, $cnt, $clr]): ?>
                                        <div class="d-flex align-items-center justify-content-between small mb-1">
                                            <span class="d-flex align-items-center gap-2">
                                                <span
                                                    style="width:10px;height:10px;border-radius:3px;background:<?= $clr ?>;display:inline-block;"></span><?= $lbl ?>
                                            </span>
                                            <span class="fw-bold"><?= $cnt ?></span>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ranking + Sidebar -->
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom py-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="fw-bold mb-0"><i class="fas fa-trophy text-warning"></i> Ranking
                                            Performa Karyawan</h6>
                                        <div class="d-flex gap-2">
                                            <select id="filterStatus" class="form-select form-select-sm"
                                                style="width:130px;">
                                                <option value="">Semua Status</option>
                                                <option value="Tetap">Tetap</option>
                                                <option value="Kontrak">Kontrak</option>
                                                <option value="Magang">Magang</option>
                                            </select>
                                            <input type="text" id="searchName" class="form-control form-control-sm"
                                                placeholder="Cari nama..." style="width:140px;">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="rank-table">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Karyawan</th>
                                                    <th>Group</th>
                                                    <th>Kehadiran</th>
                                                    <th>Cuti</th>
                                                    <th>SP</th>
                                                    <th>Skor</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="rankBody">
                                                <?php foreach ($performa_scored as $i => $p):
                                                    $rank = $i + 1;
                                                    $skor = $p['skor'];
                                                    $pct = (float) ($p['persen_kehadiran'] ?? 0);
                                                    $sisa = (int) ($p['sisa_cuti'] ?? 0);
                                                    $jatah = (int) ($p['jatah_cuti'] ?? 12);
                                                    $sp = (int) ($p['jumlah_sp'] ?? 0);
                                                    $grp = $p['group_karyawan'] ?? '';

                                                    if ($skor >= 80) {
                                                        $skor_color = '#1cc88a';
                                                        $skor_bg = '#f0fdf4';
                                                    } elseif ($skor >= 65) {
                                                        $skor_color = '#36b9cc';
                                                        $skor_bg = '#f0fdff';
                                                    } elseif ($skor >= 50) {
                                                        $skor_color = '#f6c23e';
                                                        $skor_bg = '#fffbeb';
                                                    } else {
                                                        $skor_color = '#e74a3b';
                                                        $skor_bg = '#fef2f2';
                                                    }

                                                    $bar_color = $pct >= 90 ? '#1cc88a' : ($pct >= 75 ? '#36b9cc' : ($pct >= 50 ? '#f6c23e' : '#e74a3b'));

                                                    $grp_styles = [
                                                        'Yamazaki Staff' => ['#fff0f6', '#c2185b', '#f48fb1'],
                                                        'Admin TSC' => ['#e8f4fd', '#1565c0', '#90caf9'],
                                                        'Operasional TSC' => ['#e8f5e9', '#2e7d32', '#a5d6a7'],
                                                        'TSF Staff' => ['#fff8e1', '#f57f17', '#ffe082'],
                                                        'Sinar Boga Staff' => ['#f3e5f5', '#6a1b9a', '#ce93d8'],
                                                        'Rorotan Staff' => ['#fbe9e7', '#bf360c', '#ffab91'],
                                                    ];
                                                    [$gbg, $gtxt, $gbrd] = $grp_styles[$grp] ?? ['#f5f5f5', '#616161', '#e0e0e0'];
                                                    ?>
                                                    <tr data-status="<?= $p['status_kepegawaian'] ?>"
                                                        data-nama="<?= strtolower($p['nama']) ?>">
                                                        <td>
                                                            <span
                                                                class="rank-num <?= $rank <= 3 ? 'rank-' . $rank : 'rank-n' ?>"><?= $rank ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <img src="<?= base_url('uploads/profil/' . ($p['foto_profil'] ?? 'default-1.png')) ?>"
                                                                    class="mini-avatar" alt="">
                                                                <div>
                                                                    <div class="fw-bold" style="font-size:0.88rem;">
                                                                        <?= $p['nama'] ?></div>
                                                                    <div style="font-size:0.72rem;color:#b7b9cc;">
                                                                        <?= $p['nik'] ?></div>
                                                                    <?php if ($p['status_kepegawaian']): ?>
                                                                        <span class="badge"
                                                                            style="font-size:0.6rem;background:<?= $p['status_kepegawaian'] === 'Tetap' ? '#1cc88a' : ($p['status_kepegawaian'] === 'Kontrak' ? '#f6c23e' : '#36b9cc') ?>;"><?= $p['status_kepegawaian'] ?></span>
                                                                    <?php endif ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if ($grp): ?>
                                                                <span class="gbadge-sm"
                                                                    style="background:<?= $gbg ?>;color:<?= $gtxt ?>;border-color:<?= $gbrd ?>;">
                                                                    <span
                                                                        style="width:6px;height:6px;border-radius:50%;background:<?= $gtxt ?>;display:inline-block;"></span>
                                                                    <?= $grp ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted small">—</span>
                                                            <?php endif ?>
                                                        </td>
                                                        <td>
                                                            <div style="font-size:0.88rem;font-weight:700;"><?= $pct ?>%
                                                            </div>
                                                            <div style="font-size:0.7rem;color:#b7b9cc;">
                                                                <?= $p['hadir_bulan_ini'] ?> hari</div>
                                                            <div class="mini-bar">
                                                                <div class="mini-bar-fill"
                                                                    style="width:<?= min($pct, 100) ?>%;background:<?= $bar_color ?>;">
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="fw-bold <?= $sisa <= 3 ? 'text-danger' : 'text-success' ?>"
                                                                style="font-size:0.88rem;"><?= $sisa ?></span>
                                                            <span class="text-muted small">/<?= $jatah ?></span>
                                                        </td>
                                                        <td>
                                                            <?php if ($sp == 0): ?>
                                                                <i class="fas fa-check text-success"></i>
                                                            <?php else: ?>
                                                                <?php for ($s = 1; $s <= min($sp, 3); $s++): ?>
                                                                    <span class="sp-dot sp<?= $s ?>"></span>
                                                                <?php endfor ?>
                                                                <span class="text-danger small fw-bold"><?= $sp ?></span>
                                                            <?php endif ?>
                                                        </td>
                                                        <td>
                                                            <span class="skor-badge"
                                                                style="background:<?= $skor_bg ?>;color:<?= $skor_color ?>;"><?= $skor ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-1">
                                                                <a href="<?= base_url('pengguna/detail/' . $p['user_id']) ?>"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    style="font-size:0.72rem;padding:3px 10px;">Detail</a>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-secondary"
                                                                    style="font-size:0.72rem;padding:3px 8px;"
                                                                    onclick="openRekapKalender(<?= $p['user_id'] ?>, '<?= addslashes($p['nama']) ?>')"
                                                                    title="Rekap Kalender Absensi">
                                                                    <i class="fas fa-calendar-alt"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-md-4">
                            <!-- Cuti pending -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom py-2">
                                    <h6 class="fw-bold mb-0 small"><i class="fas fa-clock text-warning"></i> Cuti
                                        Menunggu Persetujuan
                                        <?php if ($cuti_pending_ct): ?>
                                            <span class="badge bg-warning text-dark ms-1"><?= $cuti_pending_ct ?></span>
                                        <?php endif ?>
                                    </h6>
                                </div>
                                <div class="card-body py-2 px-3">
                                    <?php if (empty($cuti_pending)): ?>
                                        <div class="text-center py-3 text-muted small">
                                            <i class="fas fa-check-circle text-success"></i> Tidak ada yang pending
                                        </div>
                                    <?php else: ?>
                                        <?php foreach (array_slice($cuti_pending, 0, 5) as $c): ?>
                                            <div class="pending-item">
                                                <img src="<?= base_url('uploads/profil/' . ($c->foto_profil ?? 'default-1.png')) ?>"
                                                    class="mini-avatar" alt="">
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="fw-bold small text-truncate"><?= $c->karyawan_nama ?></div>
                                                    <div class="text-muted" style="font-size:0.72rem;">
                                                        <?= date('d M', strtotime($c->tanggal_mulai)) ?> –
                                                        <?= date('d M', strtotime($c->tanggal_selesai)) ?> ·
                                                        <?= $c->jumlah_hari ?> hari
                                                    </div>
                                                </div>
                                                <a href="<?= base_url('pengguna/detail/' . $c->user_id . '#cuti') ?>"
                                                    class="btn btn-xs btn-warning"
                                                    style="font-size:0.65rem;padding:3px 8px;white-space:nowrap;">Review</a>
                                            </div>
                                        <?php endforeach ?>
                                        <?php if ($cuti_pending_ct > 5): ?>
                                            <div class="text-center pt-2">
                                                <small class="text-muted">+<?= $cuti_pending_ct - 5 ?> lainnya</small>
                                            </div>
                                        <?php endif ?>
                                    <?php endif ?>
                                </div>
                            </div>

                            <!-- Dokumen hampir expired -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom py-2">
                                    <h6 class="fw-bold mb-0 small"><i class="fas fa-file-contract text-danger"></i>
                                        Dokumen Hampir Kadaluarsa
                                        <?php if ($dok_expired_ct): ?>
                                            <span class="badge bg-danger ms-1"><?= $dok_expired_ct ?></span>
                                        <?php endif ?>
                                    </h6>
                                </div>
                                <div class="card-body py-2 px-3">
                                    <?php if (empty($dok_hampir_expired)): ?>
                                        <div class="text-center py-3 text-muted small">
                                            <i class="fas fa-check-circle text-success"></i> Semua dokumen aman
                                        </div>
                                    <?php else: ?>
                                        <?php foreach (array_slice($dok_hampir_expired, 0, 5) as $d): ?>
                                            <div class="pending-item">
                                                <div
                                                    style="width:34px;height:34px;border-radius:8px;background:#fef2f2;color:#e74a3b;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    <i class="fas fa-file-contract" style="font-size:0.85rem;"></i>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="fw-bold small text-truncate"><?= $d->karyawan_nama ?></div>
                                                    <div class="text-muted" style="font-size:0.72rem;"><?= $d->jenis_dokumen ?>
                                                    </div>
                                                    <div class="text-danger" style="font-size:0.68rem;font-weight:600;">
                                                        Exp: <?= date('d M Y', strtotime($d->tanggal_expired)) ?>
                                                    </div>
                                                </div>
                                                <a href="<?= base_url('pengguna/detail/' . $d->user_id . '#dokumen') ?>"
                                                    class="btn btn-xs btn-danger"
                                                    style="font-size:0.65rem;padding:3px 8px;">Lihat</a>
                                            </div>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </div>
                            </div>

                            <!-- Perlu perhatian -->
                            <?php $poor_list = array_filter($performa_scored, fn($p) => $p['skor'] < 50); ?>
                            <?php if (!empty($poor_list)): ?>
                                <div class="card border-0 shadow-sm" style="border-left:4px solid #e74a3b!important;">
                                    <div class="card-header bg-white border-bottom py-2">
                                        <h6 class="fw-bold mb-0 small text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Perlu Perhatian
                                        </h6>
                                    </div>
                                    <div class="card-body py-2 px-3">
                                        <?php foreach (array_slice($poor_list, 0, 4) as $p): ?>
                                            <div class="pending-item">
                                                <img src="<?= base_url('uploads/profil/' . ($p['foto_profil'] ?? 'default-1.png')) ?>"
                                                    class="mini-avatar" alt="">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold small"><?= $p['nama'] ?></div>
                                                    <div class="text-danger" style="font-size:0.72rem;">
                                                        Skor <?= $p['skor'] ?> · <?= $p['persen_kehadiran'] ?>% hadir
                                                    </div>
                                                </div>
                                                <a href="<?= base_url('pengguna/detail/' . $p['user_id']) ?>"
                                                    class="btn btn-xs btn-danger"
                                                    style="font-size:0.65rem;padding:3px 8px;">Detail</a>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         MODAL REKAP KALENDER ABSENSI
    ══════════════════════════════════════════ -->
    <div class="modal fade" id="modalRekapKalender" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"
                    style="background:linear-gradient(135deg,#1a1f3a,#2d3561);color:#fff;border-radius:12px 12px 0 0;">
                    <div>
                        <h5 class="modal-title mb-0 text-white">
                            <i class="fas fa-calendar-alt me-2"></i> Rekap Kalender Absensi
                        </h5>
                        <small id="kal-subtitle" style="opacity:0.6;font-size:0.78rem;"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Loading -->
                    <div id="kal-loading" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <div class="text-muted mt-2 small">Memuat data...</div>
                    </div>

                    <!-- Content -->
                    <div id="kal-content" style="display:none;">

                        <!-- Navigator -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="kal-nav">
                                <button class="btn btn-sm btn-outline-secondary" id="kal-prev">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <select id="kal-select-bulan">
                                    <?php
                                    $bulan_names_kal = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>"><?= $bulan_names_kal[$m] ?></option>
                                    <?php endfor ?>
                                </select>
                                <select id="kal-select-tahun">
                                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                                        <option value="<?= $y ?>"><?= $y ?></option>
                                    <?php endfor ?>
                                </select>
                                <button class="btn btn-sm btn-outline-secondary" id="kal-next">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            <div id="kal-jadwal-badge" class="badge bg-light text-dark border"
                                style="font-size:0.72rem;"></div>
                        </div>

                        <!-- Summary -->
                        <div class="kal-summary" id="kal-summary"></div>

                        <!-- Kalender -->
                        <div class="kal-header">
                            <span>Sen</span><span>Sel</span><span>Rab</span>
                            <span>Kam</span><span>Jum</span><span>Sab</span>
                            <span style="color:#e74a3b;">Min</span>
                        </div>
                        <div class="kal-grid" id="kal-grid"></div>

                        <!-- Legend -->
                        <div class="kal-legend">
                            <div class="kal-legend-item">
                                <div class="kal-legend-box" style="background:#d1fae5;border-color:#6ee7b7;"></div>
                                Hadir
                            </div>
                            <div class="kal-legend-item">
                                <div class="kal-legend-box" style="background:#fee2e2;border-color:#fca5a5;"></div>
                                Tidak Hadir
                            </div>
                            <div class="kal-legend-item">
                                <div class="kal-legend-box" style="background:#fef9c3;border-color:#fde68a;"></div>
                                Hari Off Khusus
                            </div>
                            <div class="kal-legend-item">
                                <div class="kal-legend-box" style="background:#f3f4f6;border-color:#e5e7eb;"></div>
                                Libur Reguler
                            </div>
                            <div class="kal-legend-item">
                                <div class="kal-legend-box"
                                    style="background:#f8f9fc;border-color:#e5e7eb;border-style:dashed;"></div>
                                Belum
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END MODAL REKAP KALENDER -->

    <?php $this->load->view('partials/js') ?>
    <script>
        /* ── Charts & filter table ── */
        $(document).ready(function () {
            const kehadiranData = <?php
            $labels = array_map(fn($p) => $p['nama'], array_slice($performa_scored, 0, 15));
            $values = array_map(fn($p) => (float) $p['persen_kehadiran'], array_slice($performa_scored, 0, 15));
            $colors = array_map(fn($v) => $v >= 90 ? 'rgba(28,200,138,0.8)' : ($v >= 75 ? 'rgba(54,185,204,0.8)' : ($v >= 50 ? 'rgba(246,194,62,0.8)' : 'rgba(231,74,59,0.8)')), $values);
            echo json_encode(['labels' => $labels, 'values' => $values, 'colors' => $colors]);
            ?>;

            new Chart(document.getElementById('chartKehadiran'), {
                type: 'bar',
                data: {
                    labels: kehadiranData.labels,
                    datasets: [{ data: kehadiranData.values, backgroundColor: kehadiranData.colors, borderRadius: 6, borderSkipped: false }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.parsed.y + '% kehadiran' } } },
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        x: { ticks: { maxRotation: 35, font: { size: 10 }, callback: function (val) { return this.getLabelForValue(val).split(' ')[0]; } }, grid: { display: false } }
                    }
                }
            });

            new Chart(document.getElementById('chartDistribusi'), {
                type: 'doughnut',
                data: {
                    labels: ['Excellent', 'Good', 'Average', 'Poor'],
                    datasets: [{ data: [<?= $excellent ?>, <?= $good ?>, <?= $average ?>, <?= $poor ?>], backgroundColor: ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'], borderWidth: 0, hoverOffset: 4 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false } } }
            });

            function filterTable() {
                const status = $('#filterStatus').val().toLowerCase();
                const name = $('#searchName').val().toLowerCase();
                $('#rankBody tr').each(function () {
                    const show = (!status || ($(this).data('status') || '').toLowerCase() === status)
                        && (!name || ($(this).data('nama') || '').includes(name));
                    $(this).toggle(show);
                });
            }
            $('#filterStatus, #searchName').on('change keyup', filterTable);
            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });

        /* ══════════════════════════════════════════
           REKAP KALENDER — Script
        ══════════════════════════════════════════ */
        (function () {
            let currentUserId = null;
            let currentBulan = <?= date('m') ?>;
            let currentTahun = <?= date('Y') ?>;

            const bulanNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            window.openRekapKalender = function (userId, nama) {
                currentUserId = userId;
                currentBulan = <?= date('m') ?>;
                currentTahun = <?= date('Y') ?>;

                $('#kal-subtitle').text(nama);
                $('#kal-select-bulan').val(currentBulan);
                $('#kal-select-tahun').val(currentTahun);
                $('#kal-loading').html('<i class="fas fa-spinner fa-spin fa-2x text-muted"></i><div class="text-muted mt-2 small">Memuat data...</div>').show();
                $('#kal-content').hide();

                $('#modalRekapKalender').modal('show');
                loadKalender();
            };

            function loadKalender() {
                $('#kal-loading').html('<i class="fas fa-spinner fa-spin fa-2x text-muted"></i><div class="text-muted mt-2 small">Memuat data...</div>').show();
                $('#kal-content').hide();

                $.get('<?= base_url('pengguna/rekap_kalender') ?>', {
                    user_id: currentUserId,
                    bulan: currentBulan,
                    tahun: currentTahun
                }, function (res) {
                    if (!res.success) {
                        $('#kal-loading').html('<div class="text-danger py-3"><i class="fas fa-exclamation-circle me-1"></i>' + res.message + '</div>');
                        return;
                    }
                    renderKalender(res);
                    $('#kal-loading').hide();
                    $('#kal-content').show();
                }, 'json').fail(function () {
                    $('#kal-loading').html('<div class="text-danger small py-3">Gagal memuat data. Coba lagi.</div>');
                });
            }

            function renderKalender(data) {
                $('#kal-select-bulan').val(data.bulan);
                $('#kal-select-tahun').val(data.tahun);
                $('#kal-subtitle').text(data.nama + ' · ' + bulanNames[data.bulan] + ' ' + data.tahun);

                // Badge jadwal
                const hariNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                const hariAktif = data.jadwal.split(',').map(h => hariNames[parseInt(h) - 1]).join(', ');
                $('#kal-jadwal-badge').text('Jadwal: ' + hariAktif);

                // Summary pills
                const pct = data.persen;
                const pctColor = pct >= 90 ? '#1cc88a' : (pct >= 75 ? '#36b9cc' : (pct >= 50 ? '#f6c23e' : '#e74a3b'));
                const tidakHadirCount = data.days.filter(d => d.status === 'tidak_hadir').length;
                const offCount = data.days.filter(d => d.status === 'off_khusus').length;

                let summaryHtml = `
                    <div class="kal-pill" style="background:${pctColor}20;border:1px solid ${pctColor}40;">
                        <span class="kal-pill-dot" style="background:${pctColor};"></span>
                        <span style="color:${pctColor};">${data.hadir_count}/${data.wajib_count} hari · ${pct}%</span>
                    </div>`;

                if (tidakHadirCount > 0) {
                    summaryHtml += `
                    <div class="kal-pill" style="background:#fee2e2;border:1px solid #fca5a5;">
                        <span class="kal-pill-dot" style="background:#e74a3b;"></span>
                        <span style="color:#991b1b;">${tidakHadirCount} tidak hadir</span>
                    </div>`;
                }
                if (offCount > 0) {
                    summaryHtml += `
                    <div class="kal-pill" style="background:#fef9c3;border:1px solid #fde68a;">
                        <span class="kal-pill-dot" style="background:#f59e0b;"></span>
                        <span style="color:#92400e;">${offCount} hari off khusus</span>
                    </div>`;
                }

                $('#kal-summary').html(summaryHtml);

                // Build grid
                let html = '';
                for (let i = 1; i < data.first_dow; i++) {
                    html += '<div class="kal-cell empty"></div>';
                }

                data.days.forEach(function (d) {
                    const icons = {
                        hadir: '<span class="kal-icon">✓</span>',
                        tidak_hadir: '<span class="kal-icon">✗</span>',
                        libur: '',
                        off_khusus: '<span class="kal-icon">★</span>',
                        belum: '',
                    };

                    let titleAttr = '';
                    if (d.status === 'hadir') titleAttr = 'title="Hadir"';
                    if (d.status === 'tidak_hadir') titleAttr = 'title="Tidak Hadir"';
                    if (d.status === 'libur') titleAttr = 'title="Libur reguler"';
                    if (d.status === 'off_khusus') titleAttr = `title="Hari Off: ${d.off_ket || ''}"`;
                    if (d.status === 'belum') titleAttr = 'title="Belum"';

                    html += `<div class="kal-cell ${d.status}" ${titleAttr}>
                        ${d.day}
                        ${icons[d.status] || ''}
                    </div>`;
                });

                $('#kal-grid').html(html);
            }

            // Navigator buttons
            $('#kal-prev').on('click', function () {
                currentBulan--;
                if (currentBulan < 1) { currentBulan = 12; currentTahun--; }
                loadKalender();
            });

            $('#kal-next').on('click', function () {
                currentBulan++;
                if (currentBulan > 12) { currentBulan = 1; currentTahun++; }
                loadKalender();
            });

            $('#kal-select-bulan, #kal-select-tahun').on('change', function () {
                currentBulan = parseInt($('#kal-select-bulan').val());
                currentTahun = parseInt($('#kal-select-tahun').val());
                loadKalender();
            });
        })();
    </script>
</body>

</html>