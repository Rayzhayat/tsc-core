<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FTL Non SPX — Live Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg: #0d1117;
            --card-bg: #161b22;
            --border: #30363d;
            --text: #e6edf3;
            --muted: #8b949e;
            --scheduled: #8b949e;
            --sourcing: #5a5c69;
            --loading: #0dcaf0;
            --on_trip: #4e73df;
            --tiba_muat: #f6c23e;
            --tiba_bongkar: #6f42c1;
            --completed: #1cc88a;
            --cancelled: #e74a3b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', Arial, sans-serif;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ===== HEADER ===== */
        .tv-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .tv-header .brand {
            font-size: 20px;
            font-weight: 700;
            color: #4e73df;
            letter-spacing: 1px;
        }

        .tv-header .brand span {
            color: var(--text);
        }

        .tv-header .clock {
            font-size: 26px;
            font-weight: 700;
            color: var(--text);
            font-variant-numeric: tabular-nums;
        }

        .tv-header .date-label {
            font-size: 12px;
            color: var(--muted);
            text-align: right;
        }

        .refresh-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--muted);
        }

        .refresh-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #1cc88a;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.4;
                transform: scale(0.8);
            }
        }

        /* ===== STAT CARDS ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            padding: 16px 20px 8px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 10px;
            text-align: center;
            border-top: 4px solid;
            transition: transform 0.2s;
            cursor: default;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card .num {
            font-size: 36px;
            font-weight: 800;
            line-height: 1;
        }

        .stat-card .lbl {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-top: 6px;
        }

        .sc-total {
            border-top-color: #4e73df;
        }

        .sc-total .num {
            color: #4e73df;
        }

        .sc-scheduled {
            border-top-color: var(--scheduled);
        }

        .sc-scheduled .num {
            color: var(--scheduled);
        }

        .sc-sourcing {
            border-top-color: var(--sourcing);
        }

        .sc-sourcing .num {
            color: var(--sourcing);
        }

        .sc-loading {
            border-top-color: var(--loading);
        }

        .sc-loading .num {
            color: var(--loading);
        }

        .sc-on_trip {
            border-top-color: var(--on_trip);
        }

        .sc-on_trip .num {
            color: var(--on_trip);
        }

        .sc-tiba_muat {
            border-top-color: var(--tiba_muat);
        }

        .sc-tiba_muat .num {
            color: var(--tiba_muat);
        }

        .sc-tiba_bongkar {
            border-top-color: var(--tiba_bongkar);
        }

        .sc-tiba_bongkar .num {
            color: var(--tiba_bongkar);
        }

        .sc-completed {
            border-top-color: var(--completed);
        }

        .sc-completed .num {
            color: var(--completed);
        }

        /* ===== SLA BAR ===== */
        .sla-bar-row {
            display: flex;
            gap: 12px;
            padding: 4px 20px 10px;
        }

        .sla-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 18px;
        }

        .sla-card-otd {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .sla-card-mini {
            min-width: 155px;
            text-align: center;
        }

        .sla-lbl {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .sla-num {
            font-size: 32px;
            font-weight: 800;
            line-height: 1;
        }

        .sla-detail {
            font-size: 10px;
            color: var(--muted);
            margin-top: 5px;
        }

        .sla-progress-wrap {
            flex: 1;
        }

        .sla-progress-track {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            height: 10px;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .sla-progress-fill {
            height: 100%;
            border-radius: 6px;
            transition: width 0.8s ease, background 0.4s;
        }

        .overdue-card {
            border-color: var(--cancelled) !important;
            background: rgba(231, 74, 59, 0.1) !important;
        }

        .overdue-standby-card {
            border-color: rgba(246, 194, 62, 0.6) !important;
            background: rgba(246, 194, 62, 0.08) !important;
        }

        /* ===== MAIN GRID ===== */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            padding: 8px 20px 0;
        }

        /* ===== SECTION PANEL ===== */
        .section-panel {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .section-header {
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-header .title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-header .count-badge {
            font-size: 18px;
            font-weight: 800;
            padding: 2px 12px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
        }

        .sh-on_trip {
            background: rgba(78, 115, 223, 0.25);
            border-bottom: 2px solid var(--on_trip);
        }

        .sh-loading {
            background: rgba(13, 202, 240, 0.2);
            border-bottom: 2px solid var(--loading);
        }

        .sh-tiba_bongkar {
            background: rgba(111, 66, 193, 0.25);
            border-bottom: 2px solid var(--tiba_bongkar);
        }

        .sh-tiba_muat {
            background: rgba(246, 194, 62, 0.2);
            border-bottom: 2px solid var(--tiba_muat);
        }

        .sh-scheduled {
            background: rgba(139, 148, 158, 0.15);
            border-bottom: 2px solid var(--scheduled);
        }

        .sh-sourcing {
            background: rgba(90, 92, 105, 0.25);
            border-bottom: 2px solid var(--sourcing);
        }

        .sh-overdue {
            background: rgba(231, 74, 59, 0.2);
            border-bottom: 2px solid var(--cancelled);
        }

        .sh-overdue-standby {
            background: rgba(246, 194, 62, 0.15);
            border-bottom: 2px solid #f6c23e;
        }

        /* ===== SHIPMENT LIST ===== */
        .shipment-list {
            flex: 1;
            overflow: hidden;
            max-height: 270px;
            padding: 8px;
        }

        .shipment-row {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 7px;
            transition: background 0.2s;
        }

        .shipment-row:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .shipment-row:last-child {
            margin-bottom: 0;
        }

        .row-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .no-shipment {
            font-size: 13px;
            font-weight: 800;
            color: #e6edf3;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }

        .customer-name {
            font-size: 12px;
            font-weight: 600;
            color: #e6edf3;
            flex: 1;
            margin-left: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .truck-badge {
            font-size: 9px;
            padding: 2px 7px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            color: var(--muted);
        }

        .row-mid {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #e6edf3;
            margin-bottom: 4px;
        }

        .route-arrow {
            color: var(--muted);
            font-size: 10px;
        }

        .row-bot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10px;
            color: var(--muted);
        }

        .driver-info {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nopol-tag {
            font-size: 10px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid var(--border);
            padding: 1px 7px;
            border-radius: 4px;
            color: #e6edf3;
            font-family: 'Courier New', monospace;
        }

        .target-time {
            font-size: 10px;
            color: var(--muted);
        }

        .target-time.overdue {
            color: var(--cancelled);
            font-weight: 600;
        }

        .shipment-row.overdue-row {
            border-left: 3px solid var(--cancelled);
            border-color: rgba(231, 74, 59, 0.5);
        }

        .shipment-row.overdue-standby-row {
            border-left: 3px solid #f6c23e;
            border-color: rgba(246, 194, 62, 0.4);
        }

        .overdue-days {
            font-size: 10px;
            font-weight: 700;
            color: var(--cancelled);
            white-space: nowrap;
        }

        .overdue-standby-days {
            font-size: 10px;
            font-weight: 700;
            color: #f6c23e;
            white-space: nowrap;
        }

        .empty-state {
            text-align: center;
            padding: 30px 0;
            color: var(--muted);
            font-size: 12px;
        }

        .empty-state i {
            font-size: 28px;
            margin-bottom: 8px;
            display: block;
            opacity: 0.4;
        }

        /* ===== OVERDUE PANEL ===== */
        .overdue-wrap {
            padding: 10px 20px 0;
        }

        /* Horizontal scroll wrapper untuk overdue */
        .overdue-scroll-track {
            overflow: hidden;
            padding: 10px 10px 8px;
            position: relative;
        }

        .overdue-grid {
            display: flex;
            flex-direction: row;
            gap: 8px;
            /* lebar akan disesuaikan JS */
            transition: transform 0.05s linear;
            will-change: transform;
        }

        .overdue-grid .shipment-row {
            flex: 0 0 270px;
            /* lebar tetap tiap card */
            min-width: 270px;
            max-width: 270px;
            margin-bottom: 0;
        }

        /* ===== FOOTER ===== */
        .tv-footer {
            background: var(--card-bg);
            border-top: 1px solid var(--border);
            padding: 8px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            color: var(--muted);
            margin-top: 14px;
        }

        .tv-footer a {
            color: #4e73df;
            text-decoration: none;
        }

        #last-updated {
            color: #1cc88a;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <?php
    function render_row($item, $extra_class = '')
    {
        $today = date('Y-m-d');
        $arrival_overdue = (!empty($item->target_arrival_date) && $item->target_arrival_date < $today) ? ' overdue' : '';
        $vendor = !empty($item->nama_vendor) ? htmlspecialchars($item->nama_vendor) : '';
        $nopol = !empty($item->nopol) ? htmlspecialchars($item->nopol) : '-';
        $driver = !empty($item->driver) ? htmlspecialchars($item->driver) : '<span style="color:#e74a3b">Belum diisi</span>';

        $standby = '';
        if (!empty($item->target_standby_date)) {
            $standby = date('d/m', strtotime($item->target_standby_date));
            if (!empty($item->target_standby_time))
                $standby .= ' ' . substr($item->target_standby_time, 0, 5);
        }
        $arrival = '';
        if (!empty($item->target_arrival_date)) {
            $arrival = date('d/m', strtotime($item->target_arrival_date));
            if (!empty($item->target_arrival_time))
                $arrival .= ' ' . substr($item->target_arrival_time, 0, 5);
        }
        $dest2 = !empty($item->dest2) ? '<span class="route-arrow">›</span> ' . htmlspecialchars($item->dest2) : '';

        ob_start(); ?>
        <div class="shipment-row <?= $extra_class ?>">
            <div class="row-top">
                <span class="no-shipment"><?= htmlspecialchars($item->no_shipment) ?></span>
                <span class="customer-name"><?= htmlspecialchars($item->nama_customer ?? '-') ?></span>
                <span class="truck-badge"><?= htmlspecialchars($item->truck_type ?? '-') ?></span>
            </div>
            <div class="row-mid">
                <i class="fas fa-map-marker-alt" style="color:#e74a3b;font-size:9px;"></i>
                <span><?= htmlspecialchars($item->origin ?? '-') ?></span>
                <span class="route-arrow">→</span>
                <span style="color:#1cc88a;"><?= htmlspecialchars($item->dest1 ?? '-') ?></span>
                <?= $dest2 ?>
            </div>
            <div class="row-bot">
                <div class="driver-info">
                    <span class="nopol-tag"><?= $nopol ?></span>
                    <i class="fas fa-user" style="font-size:9px;"></i>
                    <span><?= $driver ?></span>
                    <?php if ($vendor): ?>
                        <span style="font-size:9px;color:var(--muted);">— <?= $vendor ?></span>
                    <?php endif; ?>
                </div>
                <div class="target-time<?= $arrival_overdue ?>">
                    <?php if ($standby): ?><i class="fas fa-flag" style="font-size:9px;"></i> <?= $standby ?><?php endif; ?>
                    <?php if ($arrival): ?>&nbsp;<i class="fas fa-flag-checkered" style="font-size:9px;"></i>
                        <?= $arrival ?>    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    $sla = $sla ?? null;
    $sla_total = (int) ($sla->total_completed ?? 0);
    $sla_ontime = (int) ($sla->ontime ?? 0);
    $sla_late = (int) ($sla->late ?? 0);
    $sla_pct = $sla_total > 0 ? round(($sla_ontime / $sla_total) * 100, 1) : null;
    $avg_transit = isset($sla->avg_transit_minutes) && $sla->avg_transit_minutes !== null ? round($sla->avg_transit_minutes / 60, 1) : null;
    $overdue_list = $overdue ?? [];
    $overdue_count = count($overdue_list);
    $overdue_standby_list = $overdue_standby ?? [];
    $overdue_standby_count = count($overdue_standby_list);

    function sla_color($pct)
    {
        if ($pct === null)
            return '#8b949e';
        if ($pct >= 85)
            return '#1cc88a';
        if ($pct >= 70)
            return '#f6c23e';
        return '#e74a3b';
    }
    $sc = sla_color($sla_pct);
    $s = $stats;
    ?>

    <!-- HEADER -->
    <div class="tv-header">
        <div>
            <div class="brand">FTL NON SPX <span>— Live Dashboard</span></div>
            <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                <i class="fas fa-map-marker-alt"></i> Transportation Management System
            </div>
        </div>
        <div class="refresh-badge">
            <div class="refresh-dot"></div>
            <span>Auto-refresh 30s &nbsp;|&nbsp; Last update: <span id="last-updated"><?= date('H:i:s') ?></span></span>
        </div>
        <div style="text-align:right;">
            <div class="clock" id="clock">--:--:--</div>
            <div class="date-label" id="date-label"><?= date('l, d F Y') ?></div>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="stats-row">
        <div class="stat-card sc-total">
            <div class="num" id="sc-total"><?= $s['total'] ?></div>
            <div class="lbl">Total</div>
        </div>
        <div class="stat-card sc-sourcing">
            <div class="num" id="sc-sourcing"><?= $s['sourcing'] ?></div>
            <div class="lbl">Sourcing</div>
        </div>
        <div class="stat-card sc-scheduled">
            <div class="num" id="sc-scheduled"><?= $s['scheduled'] ?></div>
            <div class="lbl">Scheduled</div>
        </div>
        <div class="stat-card sc-tiba_muat">
            <div class="num" id="sc-tiba_muat"><?= $s['tiba_muat'] ?></div>
            <div class="lbl">Tiba Muat</div>
        </div>
        <div class="stat-card sc-loading">
            <div class="num" id="sc-loading"><?= $s['loading'] ?></div>
            <div class="lbl">Loading</div>
        </div>
        <div class="stat-card sc-on_trip">
            <div class="num" id="sc-on_trip"><?= $s['on_trip'] ?></div>
            <div class="lbl">On Trip</div>
        </div>
        <div class="stat-card sc-tiba_bongkar">
            <div class="num" id="sc-tiba_bongkar"><?= $s['tiba_bongkar'] ?></div>
            <div class="lbl">Tiba Bongkar</div>
        </div>
        <div class="stat-card sc-completed">
            <div class="num" id="sc-completed"><?= $s['completed'] ?></div>
            <div class="lbl">Completed</div>
        </div>
    </div>

    <!-- SLA BAR -->
    <div class="sla-bar-row">
        <div class="sla-card sla-card-otd">
            <div>
                <div class="sla-lbl">SLA On-Time Delivery</div>
                <div class="sla-num" id="sla-pct" style="color:<?= $sc ?>;">
                    <?= $sla_pct !== null ? $sla_pct . '%' : 'N/A' ?>
                </div>
            </div>
            <div class="sla-progress-wrap">
                <div class="sla-progress-track">
                    <div class="sla-progress-fill" id="sla-bar"
                        style="width:<?= $sla_pct ?? 0 ?>%; background:<?= $sc ?>;"></div>
                </div>
                <div class="sla-detail">
                    ✅ <span id="sla-ontime"><?= $sla_ontime ?></span> On-Time &nbsp;|&nbsp;
                    ❌ <span id="sla-late"><?= $sla_late ?></span> Late &nbsp;|&nbsp;
                    Total: <span id="sla-total"><?= $sla_total ?></span> delivery
                    <?php if ($sla_total === 0): ?>&nbsp;<em style="opacity:.5;">(belum ada data
                            delivery)</em><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="sla-card sla-card-mini">
            <div class="sla-lbl">Avg Transit Time</div>
            <div class="sla-num" id="avg-transit" style="color:#4e73df;">
                <?= $avg_transit !== null ? $avg_transit . 'h' : 'N/A' ?>
            </div>
            <div class="sla-detail">rata-rata perjalanan</div>
        </div>
        <div class="sla-card sla-card-mini <?= $overdue_count > 0 ? 'overdue-card' : '' ?>">
            <div class="sla-lbl">⚠️ Overdue Arrival</div>
            <div class="sla-num" id="overdue-count" style="color:<?= $overdue_count > 0 ? '#e74a3b' : '#8b949e' ?>;">
                <?= $overdue_count ?>
            </div>
            <div class="sla-detail">target arrival terlewat</div>
        </div>
        <div class="sla-card sla-card-mini <?= $overdue_standby_count > 0 ? 'overdue-standby-card' : '' ?>">
            <div class="sla-lbl">🕐 Overdue Standby</div>
            <div class="sla-num" id="overdue-standby-count"
                style="color:<?= $overdue_standby_count > 0 ? '#f6c23e' : '#8b949e' ?>;">
                <?= $overdue_standby_count ?>
            </div>
            <div class="sla-detail">belum berangkat, standby terlewat</div>
        </div>
    </div>

    <!-- OVERDUE ARRIVAL PANEL -->
    <div id="overdue-wrap" <?= $overdue_count === 0 ? 'style="display:none;"' : '' ?>>
        <div class="overdue-wrap">
            <div class="section-panel" style="border-color:rgba(231,74,59,0.6);">
                <div class="section-header sh-overdue">
                    <div class="title" style="color:#e74a3b;">
                        <i class="fas fa-exclamation-triangle"></i> OVERDUE ARRIVAL — Target Arrival Sudah Terlewat
                    </div>
                    <div class="count-badge" style="color:#e74a3b;" id="badge-overdue"><?= $overdue_count ?></div>
                </div>
                <div class="overdue-scroll-track" id="track-overdue">
                    <div class="overdue-grid" id="list-overdue">
                        <?php foreach ($overdue_list as $item):
                            $days_late = max(0, (int) floor((time() - strtotime($item->target_arrival_date)) / 86400)); ?>
                            <div class="shipment-row overdue-row">
                                <div class="row-top">
                                    <span class="no-shipment"><?= htmlspecialchars($item->no_shipment) ?></span>
                                    <span class="customer-name"><?= htmlspecialchars($item->nama_customer ?? '-') ?></span>
                                    <span class="overdue-days">+<?= $days_late ?>h</span>
                                </div>
                                <div class="row-mid">
                                    <i class="fas fa-map-marker-alt" style="color:#e74a3b;font-size:9px;"></i>
                                    <span><?= htmlspecialchars($item->origin ?? '-') ?></span>
                                    <span class="route-arrow">→</span>
                                    <span style="color:#1cc88a;"><?= htmlspecialchars($item->dest1 ?? '-') ?></span>
                                </div>
                                <div class="row-bot">
                                    <div class="driver-info">
                                        <span class="nopol-tag"><?= htmlspecialchars($item->nopol ?? '-') ?></span>
                                        <span><?= htmlspecialchars($item->driver ?? 'Belum diisi') ?></span>
                                    </div>
                                    <span style="color:#e74a3b;font-weight:600;font-size:10px;">
                                        Target: <?= date('d/m', strtotime($item->target_arrival_date)) ?>
                                        <?= !empty($item->target_arrival_time) ? substr($item->target_arrival_time, 0, 5) : '' ?>
                                        &nbsp;|&nbsp; Status: <?= htmlspecialchars($item->status_shipment) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div><!-- /.overdue-grid -->
                </div><!-- /.overdue-scroll-track -->
            </div>
        </div>
    </div>

    <!-- OVERDUE STANDBY PANEL -->
    <div id="overdue-standby-wrap" <?= $overdue_standby_count === 0 ? 'style="display:none;"' : '' ?>>
        <div class="overdue-wrap">
            <div class="section-panel" style="border-color:rgba(246,194,62,0.6);">
                <div class="section-header sh-overdue-standby">
                    <div class="title" style="color:#f6c23e;">
                        <i class="fas fa-clock"></i> OVERDUE STANDBY — Belum Berangkat, Target Standby Sudah Terlewat
                    </div>
                    <div class="count-badge" style="color:#f6c23e;" id="badge-overdue-standby">
                        <?= $overdue_standby_count ?>
                    </div>
                </div>
                <div class="overdue-scroll-track" id="track-overdue-standby">
                    <div class="overdue-grid" id="list-overdue-standby">
                        <?php foreach ($overdue_standby_list as $item):
                            $days_late = max(0, (int) floor((time() - strtotime($item->target_standby_date)) / 86400)); ?>
                            <div class="shipment-row overdue-standby-row">
                                <div class="row-top">
                                    <span class="no-shipment"><?= htmlspecialchars($item->no_shipment) ?></span>
                                    <span class="customer-name"><?= htmlspecialchars($item->nama_customer ?? '-') ?></span>
                                    <span class="overdue-standby-days">+<?= $days_late ?>h</span>
                                </div>
                                <div class="row-mid">
                                    <i class="fas fa-map-marker-alt" style="color:#e74a3b;font-size:9px;"></i>
                                    <span><?= htmlspecialchars($item->origin ?? '-') ?></span>
                                    <span class="route-arrow">→</span>
                                    <span style="color:#1cc88a;"><?= htmlspecialchars($item->dest1 ?? '-') ?></span>
                                </div>
                                <div class="row-bot">
                                    <div class="driver-info">
                                        <span class="nopol-tag"><?= htmlspecialchars($item->nopol ?? '-') ?></span>
                                        <span><?= htmlspecialchars($item->driver ?? 'Belum diisi') ?></span>
                                        <?php if (!empty($item->nama_vendor)): ?>
                                            <span style="font-size:9px;color:var(--muted);">—
                                                <?= htmlspecialchars($item->nama_vendor) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span style="color:#f6c23e;font-weight:600;font-size:10px;">
                                        Standby: <?= date('d/m', strtotime($item->target_standby_date)) ?>
                                        <?= !empty($item->target_standby_time) ? substr($item->target_standby_time, 0, 5) : '' ?>
                                        &nbsp;|&nbsp; <?= htmlspecialchars($item->status_shipment) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div><!-- /.overdue-grid -->
                </div><!-- /.overdue-scroll-track -->
            </div>
        </div>
    </div>

    <!-- MAIN GRID ROW 1 -->
    <div class="main-grid" style="padding-top:12px;">
        <div class="section-panel">
            <div class="section-header sh-sourcing">
                <div class="title" style="color:#9da5b1;"><i class="fas fa-search"></i> Sourcing Vendor</div>
                <div class="count-badge" style="color:#9da5b1;" id="badge-sourcing"><?= count($sourcing) ?></div>
            </div>
            <div class="shipment-list" id="list-sourcing">
                <?php if (empty($sourcing)): ?>
                    <div class="empty-state"><i class="fas fa-search"></i>Tidak ada</div>
                <?php else:
                    foreach ($sourcing as $item):
                        echo render_row($item); endforeach; endif; ?>
            </div>
        </div>
        <div class="section-panel">
            <div class="section-header sh-scheduled">
                <div class="title" style="color:#8b949e;"><i class="fas fa-calendar-check"></i> Scheduled</div>
                <div class="count-badge" style="color:#8b949e;" id="badge-scheduled"><?= count($scheduled) ?></div>
            </div>
            <div class="shipment-list" id="list-scheduled">
                <?php if (empty($scheduled)): ?>
                    <div class="empty-state"><i class="fas fa-calendar"></i>Tidak ada</div>
                <?php else:
                    foreach ($scheduled as $item):
                        echo render_row($item); endforeach; endif; ?>
            </div>
        </div>
        <div class="section-panel">
            <div class="section-header sh-tiba_muat">
                <div class="title" style="color:#f6c23e;"><i class="fas fa-map-pin"></i> Tiba Lokasi Muat</div>
                <div class="count-badge" style="color:#f6c23e;" id="badge-tiba_muat"><?= count($tiba_muat) ?></div>
            </div>
            <div class="shipment-list" id="list-tiba_muat">
                <?php if (empty($tiba_muat)): ?>
                    <div class="empty-state"><i class="fas fa-map-pin"></i>Tidak ada</div>
                <?php else:
                    foreach ($tiba_muat as $item):
                        echo render_row($item); endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- MAIN GRID ROW 2 -->
    <div class="main-grid" style="padding-top:12px; padding-bottom:0;">
        <div class="section-panel">
            <div class="section-header sh-loading">
                <div class="title" style="color:#0dcaf0;"><i class="fas fa-boxes"></i> Loading</div>
                <div class="count-badge" style="color:#0dcaf0;" id="badge-loading"><?= count($loading) ?></div>
            </div>
            <div class="shipment-list" id="list-loading">
                <?php if (empty($loading)): ?>
                    <div class="empty-state"><i class="fas fa-boxes"></i>Tidak ada shipment Loading</div>
                <?php else:
                    foreach ($loading as $item):
                        echo render_row($item); endforeach; endif; ?>
            </div>
        </div>
        <div class="section-panel">
            <div class="section-header sh-on_trip">
                <div class="title" style="color:#7b9cf5;"><i class="fas fa-truck-moving"></i> On Trip</div>
                <div class="count-badge" style="color:#7b9cf5;" id="badge-on_trip"><?= count($on_trip) ?></div>
            </div>
            <div class="shipment-list" id="list-on_trip">
                <?php if (empty($on_trip)): ?>
                    <div class="empty-state"><i class="fas fa-truck"></i>Tidak ada shipment On Trip</div>
                <?php else:
                    foreach ($on_trip as $item):
                        echo render_row($item); endforeach; endif; ?>
            </div>
        </div>
        <div class="section-panel">
            <div class="section-header sh-tiba_bongkar">
                <div class="title" style="color:#9d6fdb;"><i class="fas fa-warehouse"></i> Tiba Lokasi Bongkar</div>
                <div class="count-badge" style="color:#9d6fdb;" id="badge-tiba_bongkar"><?= count($tiba_bongkar) ?>
                </div>
            </div>
            <div class="shipment-list" id="list-tiba_bongkar">
                <?php if (empty($tiba_bongkar)): ?>
                    <div class="empty-state"><i class="fas fa-warehouse"></i>Tidak ada</div>
                <?php else:
                    foreach ($tiba_bongkar as $item):
                        echo render_row($item); endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="tv-footer">
        <div>TSC — Transportation Management System &nbsp;|&nbsp; <a href="<?= base_url('ftl_non_spx') ?>">Kelola
                Shipment</a></div>
        <div>Refresh otomatis setiap <strong>30 detik</strong></div>
        <div>
            <a href="<?= base_url('home') ?>" style="color:var(--muted);margin-right:12px;"><i class="fas fa-home"></i>
                Dashboard</a>
            <a href="<?= base_url('ftl_non_spx/tambah') ?>" style="color:#1cc88a;"><i class="fas fa-plus-circle"></i>
                Tambah Shipment</a>
        </div>
    </div>

    <script>
        // ════════════════════════════════════════════════════════
        // LIVE CLOCK
        // ════════════════════════════════════════════════════════
        function updateClock() {
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            document.getElementById('clock').textContent =
                `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('date-label').textContent =
                `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
        }
        updateClock();
        setInterval(updateClock, 1000);

        // ════════════════════════════════════════════════════════
        // VERTICAL AUTO SCROLL (panel list atas-bawah)
        // Pakai setInterval bukan rAF supaya tidak dimatikan
        // browser pas tab tidak fokus
        // ════════════════════════════════════════════════════════
        const SCROLL_SPEED = 1;      // px per tick
        const TICK_MS = 30;     // interval ms (~33fps)
        const PAUSE_TOP_MS = 2500;
        const PAUSE_BOT_MS = 1500;

        const SCROLL_IDS = [
            'list-sourcing', 'list-scheduled', 'list-tiba_muat',
            'list-loading', 'list-on_trip', 'list-tiba_bongkar',
        ];

        const vScrollState = {};

        function vInit(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.scrollTop = 0;
            vScrollState[id] = { goingDown: true, pauseUntil: Date.now() + PAUSE_TOP_MS };
        }

        function vTick(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const st = vScrollState[id];
            const maxSc = el.scrollHeight - el.clientHeight;
            if (maxSc <= 2) { el.scrollTop = 0; return; }
            if (Date.now() < st.pauseUntil) return;

            if (st.goingDown) {
                el.scrollTop += SCROLL_SPEED;
                if (el.scrollTop >= maxSc - 1) {
                    st.goingDown = false;
                    st.pauseUntil = Date.now() + PAUSE_BOT_MS;
                }
            } else {
                el.scrollTop = 0;
                st.goingDown = true;
                st.pauseUntil = Date.now() + PAUSE_TOP_MS;
            }
        }

        SCROLL_IDS.forEach(vInit);
        setInterval(() => SCROLL_IDS.forEach(vTick), TICK_MS);

        // ════════════════════════════════════════════════════════
        // HORIZONTAL AUTO SCROLL (overdue panels kiri-kanan)
        // Pakai CSS transform translate bukan scrollLeft
        // supaya lebih smooth dan tidak terpengaruh overflow hidden
        // ════════════════════════════════════════════════════════
        const H_SPEED = 1;      // px per tick
        const H_PAUSE_MS = 2000;   // pause di ujung kiri/kanan

        const HORIZ_IDS = [
            { track: 'track-overdue', list: 'list-overdue' },
            { track: 'track-overdue-standby', list: 'list-overdue-standby' },
        ];

        const hScrollState = {};

        function hInit(ids) {
            hScrollState[ids.track] = {
                offset: 0,
                goingRight: true,
                pauseUntil: Date.now() + H_PAUSE_MS,
            };
            const grid = document.getElementById(ids.list);
            if (grid) grid.style.transform = 'translateX(0px)';
        }

        function hTick(ids) {
            const track = document.getElementById(ids.track);
            const grid = document.getElementById(ids.list);
            if (!track || !grid) return;

            const st = hScrollState[ids.track];
            const trackW = track.clientWidth;
            const gridW = grid.scrollWidth;
            const maxOffset = Math.max(0, gridW - trackW);

            if (maxOffset < 10) {
                // tidak perlu scroll — konten muat semua
                grid.style.transform = 'translateX(0px)';
                return;
            }

            if (Date.now() < st.pauseUntil) return;

            if (st.goingRight) {
                st.offset += H_SPEED;
                if (st.offset >= maxOffset) {
                    st.offset = maxOffset;
                    st.goingRight = false;
                    st.pauseUntil = Date.now() + H_PAUSE_MS;
                }
            } else {
                st.offset -= H_SPEED;
                if (st.offset <= 0) {
                    st.offset = 0;
                    st.goingRight = true;
                    st.pauseUntil = Date.now() + H_PAUSE_MS;
                }
            }

            grid.style.transform = `translateX(-${st.offset}px)`;
        }

        HORIZ_IDS.forEach(hInit);
        setInterval(() => HORIZ_IDS.forEach(hTick), TICK_MS);

        // ════════════════════════════════════════════════════════
        // HELPERS
        // ════════════════════════════════════════════════════════
        function slaColor(pct) {
            if (pct === null || pct === undefined) return '#8b949e';
            if (pct >= 85) return '#1cc88a';
            if (pct >= 70) return '#f6c23e';
            return '#e74a3b';
        }

        function fmtDate(dateStr, timeStr) {
            if (!dateStr) return '';
            const d = dateStr.substring(5, 10).split('-').reverse().join('/');
            return d + (timeStr ? ' ' + timeStr.substring(0, 5) : '');
        }

        function esc(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        // ════════════════════════════════════════════════════════
        // RENDER ROW (JS)
        // ════════════════════════════════════════════════════════
        function renderRow(item, extraClass = '') {
            const today = new Date().toISOString().split('T')[0];
            const isOverdue = item.target_arrival_date && item.target_arrival_date < today;
            const vendor = esc(item.nama_vendor) || '';
            const nopol = esc(item.nopol) || '-';
            const driver = item.driver ? esc(item.driver) : '<span style="color:#e74a3b">Belum diisi</span>';
            const standby = fmtDate(item.target_standby_date, item.target_standby_time);
            const arrival = fmtDate(item.target_arrival_date, item.target_arrival_time);
            const dest2 = item.dest2 ? `<span class="route-arrow">›</span> ${esc(item.dest2)}` : '';
            const overdueClass = isOverdue ? ' overdue' : '';

            return `<div class="shipment-row ${extraClass}">
                <div class="row-top">
                    <span class="no-shipment">${esc(item.no_shipment)}</span>
                    <span class="customer-name">${esc(item.nama_customer) || '-'}</span>
                    <span class="truck-badge">${esc(item.truck_type) || '-'}</span>
                </div>
                <div class="row-mid">
                    <i class="fas fa-map-marker-alt" style="color:#e74a3b;font-size:9px;"></i>
                    <span>${esc(item.origin) || '-'}</span>
                    <span class="route-arrow">→</span>
                    <span style="color:#1cc88a;">${esc(item.dest1) || '-'}</span>
                    ${dest2}
                </div>
                <div class="row-bot">
                    <div class="driver-info">
                        <span class="nopol-tag">${nopol}</span>
                        <i class="fas fa-user" style="font-size:9px;"></i>
                        <span>${driver}</span>
                        ${vendor ? `<span style="font-size:9px;color:var(--muted);">— ${vendor}</span>` : ''}
                    </div>
                    <div class="target-time${overdueClass}">
                        ${standby ? `<i class="fas fa-flag" style="font-size:9px;"></i> ${standby}` : ''}
                        ${arrival ? `&nbsp;<i class="fas fa-flag-checkered" style="font-size:9px;"></i> ${arrival}` : ''}
                    </div>
                </div>
            </div>`;
        }

        function renderOverdueRow(item) {
            const daysLate = Math.max(0, Math.floor((Date.now() - new Date(item.target_arrival_date).getTime()) / 86400000));
            const arrival = fmtDate(item.target_arrival_date, item.target_arrival_time);
            return `<div class="shipment-row overdue-row">
                <div class="row-top">
                    <span class="no-shipment">${esc(item.no_shipment)}</span>
                    <span class="customer-name">${esc(item.nama_customer) || '-'}</span>
                    <span class="overdue-days">+${daysLate}h</span>
                </div>
                <div class="row-mid">
                    <i class="fas fa-map-marker-alt" style="color:#e74a3b;font-size:9px;"></i>
                    <span>${esc(item.origin) || '-'}</span>
                    <span class="route-arrow">→</span>
                    <span style="color:#1cc88a;">${esc(item.dest1) || '-'}</span>
                </div>
                <div class="row-bot">
                    <div class="driver-info">
                        <span class="nopol-tag">${esc(item.nopol) || '-'}</span>
                        <span>${esc(item.driver) || 'Belum diisi'}</span>
                    </div>
                    <span style="color:#e74a3b;font-weight:600;font-size:10px;">
                        Target: ${arrival} &nbsp;|&nbsp; ${esc(item.status_shipment)}
                    </span>
                </div>
            </div>`;
        }

        function renderOverdueStandbyRow(item) {
            const daysLate = Math.max(0, Math.floor((Date.now() - new Date(item.target_standby_date).getTime()) / 86400000));
            const standby = fmtDate(item.target_standby_date, item.target_standby_time);
            const vendor = esc(item.nama_vendor) || '';
            return `<div class="shipment-row overdue-standby-row">
                <div class="row-top">
                    <span class="no-shipment">${esc(item.no_shipment)}</span>
                    <span class="customer-name">${esc(item.nama_customer) || '-'}</span>
                    <span class="overdue-standby-days">+${daysLate}h</span>
                </div>
                <div class="row-mid">
                    <i class="fas fa-map-marker-alt" style="color:#e74a3b;font-size:9px;"></i>
                    <span>${esc(item.origin) || '-'}</span>
                    <span class="route-arrow">→</span>
                    <span style="color:#1cc88a;">${esc(item.dest1) || '-'}</span>
                </div>
                <div class="row-bot">
                    <div class="driver-info">
                        <span class="nopol-tag">${esc(item.nopol) || '-'}</span>
                        <span>${esc(item.driver) || 'Belum diisi'}</span>
                        ${vendor ? `<span style="font-size:9px;color:var(--muted);">— ${vendor}</span>` : ''}
                    </div>
                    <span style="color:#f6c23e;font-weight:600;font-size:10px;">
                        Standby: ${standby} &nbsp;|&nbsp; ${esc(item.status_shipment)}
                    </span>
                </div>
            </div>`;
        }

        function emptyState(icon, text) {
            return `<div class="empty-state"><i class="fas ${icon}"></i>${text}</div>`;
        }

        // ════════════════════════════════════════════════════════
        // SMART RENDER LIST
        // Vertical: hanya reset scroll kalau jumlah item berubah
        // Horizontal: reset offset ke 0 kalau konten berubah
        // ════════════════════════════════════════════════════════
        const prevItemCounts = {};

        function renderList(id, items, emptyIcon, emptyText, renderFn) {
            const el = document.getElementById(id);
            if (!el) return;

            const newCount = (items || []).length;
            const newHtml = (!items || items.length === 0)
                ? emptyState(emptyIcon, emptyText)
                : items.map(i => (renderFn || renderRow)(i)).join('');

            el.innerHTML = newHtml;

            if (prevItemCounts[id] !== newCount) {
                prevItemCounts[id] = newCount;

                // Vertical list reset
                if (SCROLL_IDS.includes(id)) {
                    vInit(id);
                }

                // Horizontal overdue reset
                const horizEntry = HORIZ_IDS.find(h => h.list === id);
                if (horizEntry) {
                    hInit(horizEntry);
                }
            }
        }

        function flashNum(id, newVal) {
            const el = document.getElementById(id);
            if (!el) return;
            if (String(el.textContent).trim() !== String(newVal)) {
                el.textContent = newVal;
                el.style.transition = 'color 0.3s';
                el.style.color = '#f6c23e';
                setTimeout(() => { el.style.color = ''; }, 900);
            }
        }

        // ════════════════════════════════════════════════════════
        // AUTO REFRESH — cache-busting + no-cache headers
        // ════════════════════════════════════════════════════════
        function refreshDashboard() {
            const url = '<?= base_url('tms_dashboard/get_stats') ?>?_=' + Date.now();

            fetch(url, {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    const s = data.stats || {};
                    flashNum('sc-total', s.total ?? 0);
                    flashNum('sc-scheduled', s.scheduled ?? 0);
                    flashNum('sc-sourcing', s.sourcing ?? 0);
                    flashNum('sc-loading', s.loading ?? 0);
                    flashNum('sc-on_trip', s.on_trip ?? 0);
                    flashNum('sc-tiba_muat', s.tiba_muat ?? 0);
                    flashNum('sc-tiba_bongkar', s.tiba_bongkar ?? 0);
                    flashNum('sc-completed', s.completed ?? 0);

                    const badge = (id, arr) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = (arr || []).length;
                    };
                    badge('badge-on_trip', data.on_trip);
                    badge('badge-loading', data.loading);
                    badge('badge-tiba_bongkar', data.tiba_bongkar);
                    badge('badge-tiba_muat', data.tiba_muat);
                    badge('badge-scheduled', data.scheduled);
                    badge('badge-sourcing', data.sourcing);

                    renderList('list-on_trip', data.on_trip, 'fa-truck', 'Tidak ada shipment On Trip');
                    renderList('list-loading', data.loading, 'fa-boxes', 'Tidak ada shipment Loading');
                    renderList('list-tiba_bongkar', data.tiba_bongkar, 'fa-warehouse', 'Tidak ada');
                    renderList('list-tiba_muat', data.tiba_muat, 'fa-map-pin', 'Tidak ada');
                    renderList('list-scheduled', data.scheduled, 'fa-calendar', 'Tidak ada');
                    renderList('list-sourcing', data.sourcing, 'fa-search', 'Tidak ada');

                    // SLA
                    if (data.sla) {
                        const sl = data.sla;
                        const pct = sl.pct;
                        const color = slaColor(pct);
                        const pctEl = document.getElementById('sla-pct');
                        if (pctEl) { pctEl.textContent = pct !== null ? pct + '%' : 'N/A'; pctEl.style.color = color; }
                        const barEl = document.getElementById('sla-bar');
                        if (barEl) { barEl.style.width = (pct ?? 0) + '%'; barEl.style.background = color; }
                        const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
                        setText('sla-ontime', sl.ontime ?? 0);
                        setText('sla-late', sl.late ?? 0);
                        setText('sla-total', sl.total_completed ?? 0);
                        const atEl = document.getElementById('avg-transit');
                        if (atEl) atEl.textContent = sl.avg_transit_hours !== null ? sl.avg_transit_hours + 'h' : 'N/A';
                    }

                    // Overdue Arrival
                    const overdueArr = data.overdue || [];
                    const overdueCount = overdueArr.length;
                    const ocEl = document.getElementById('overdue-count');
                    if (ocEl) { ocEl.textContent = overdueCount; ocEl.style.color = overdueCount > 0 ? '#e74a3b' : '#8b949e'; }
                    const wrapEl = document.getElementById('overdue-wrap');
                    if (wrapEl) wrapEl.style.display = overdueCount > 0 ? '' : 'none';
                    const badgeOD = document.getElementById('badge-overdue');
                    if (badgeOD) badgeOD.textContent = overdueCount;
                    renderList('list-overdue', overdueArr, 'fa-check-circle', 'Semua shipment on-time!', renderOverdueRow);

                    // Overdue Standby
                    const overdueStandbyArr = data.overdue_standby || [];
                    const overdueStandbyCount = overdueStandbyArr.length;
                    const oscEl = document.getElementById('overdue-standby-count');
                    if (oscEl) { oscEl.textContent = overdueStandbyCount; oscEl.style.color = overdueStandbyCount > 0 ? '#f6c23e' : '#8b949e'; }
                    const wrapStandby = document.getElementById('overdue-standby-wrap');
                    if (wrapStandby) wrapStandby.style.display = overdueStandbyCount > 0 ? '' : 'none';
                    const badgeOS = document.getElementById('badge-overdue-standby');
                    if (badgeOS) badgeOS.textContent = overdueStandbyCount;
                    renderList('list-overdue-standby', overdueStandbyArr, 'fa-clock', 'Semua standby on-time!', renderOverdueStandbyRow);

                    const luEl = document.getElementById('last-updated');
                    if (luEl) luEl.textContent = data.last_updated || '';
                })
                .catch(err => console.warn('Refresh error:', err));
        }

        setInterval(refreshDashboard, 30000);
        console.log('✅ FTL Non SPX Live Dashboard — Auto-refresh 30s | V-Scroll + H-Scroll ON | Cache-bust AKTIF');
    </script>
</body>

</html>