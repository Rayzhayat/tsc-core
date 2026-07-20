<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$h = $health;
$health_cfg = [
    'sehat' => ['color' => '#1cc88a', 'badge' => 'bg-success', 'icon' => 'check-circle', 'label' => 'Sehat'],
    'perlu_perhatian' => ['color' => '#f6c23e', 'badge' => 'bg-warning text-dark', 'icon' => 'exclamation-circle', 'label' => 'Perlu Perhatian'],
    'kritis' => ['color' => '#e74a3b', 'badge' => 'bg-danger', 'icon' => 'times-circle', 'label' => 'Kritis'],
];
$hc = $health_cfg[$h->health_status] ?? $health_cfg['kritis'];
?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Breadcrumb + Header -->
                <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-1 small">
                                <li class="breadcrumb-item"><a href="<?= base_url('analytics') ?>">Analytics</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('customer_health') ?>">Customer
                                        Health</a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($customer) ?></li>
                            </ol>
                        </nav>
                        <h1 class="page-title mb-0 d-flex align-items-center gap-2">
                            <span class="badge <?= $hc['badge'] ?> fs-6">
                                <i class="fas fa-<?= $hc['icon'] ?> me-1"></i> <?= $hc['label'] ?>
                            </span>
                            <?= htmlspecialchars($customer) ?>
                        </h1>
                        <?php if ($h->first_shipment && $h->last_shipment): ?>
                            <small class="text-muted">
                                Data: <?= date('d M Y', strtotime($h->first_shipment)) ?> –
                                <?= date('d M Y', strtotime($h->last_shipment)) ?>
                            </small>
                        <?php endif ?>
                    </div>

                    <!-- Sheet Type Filter -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php foreach ($sheet_types as $st): ?>
                            <a href="<?= base_url('customer_health/detail/' . rawurlencode($customer) . '?sheet_type=' . urlencode($st->sheet_type)) ?>"
                                class="btn btn-sm <?= $sheet_type == $st->sheet_type ? 'btn-primary' : 'btn-outline-secondary' ?>">
                                <?= $st->sheet_type ?>
                            </a>
                        <?php endforeach ?>
                        <?php if (!empty($sheet_type)): ?>
                            <a href="<?= base_url('customer_health/detail/' . rawurlencode($customer)) ?>"
                                class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times"></i> Semua Sheet
                            </a>
                        <?php endif ?>
                    </div>
                </div>

                <!-- Scorecard Cards -->
                <div class="row g-3 mb-4">
                    <!-- Revenue -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100" style="border-top:3px solid #4e73df !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                    style="font-size:.65rem;letter-spacing:.05em">Total Revenue</div>
                                <div class="fw-bold" style="font-size:.95rem">
                                    Rp <?= number_format($h->total_revenue, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Margin -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100"
                            style="border-top:3px solid <?= $h->total_margin >= 0 ? '#1cc88a' : '#e74a3b' ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                    style="font-size:.65rem;letter-spacing:.05em">Total Margin</div>
                                <div class="fw-bold <?= $h->total_margin >= 0 ? 'text-success' : 'text-danger' ?>"
                                    style="font-size:.95rem">
                                    Rp <?= number_format($h->total_margin, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Margin % -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100"
                            style="border-top:3px solid <?= $h->margin_pct > 10 ? '#1cc88a' : ($h->margin_pct > 0 ? '#f6c23e' : '#e74a3b') ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                    style="font-size:.65rem;letter-spacing:.05em">Margin %</div>
                                <div
                                    class="fw-bold h4 mb-0 <?= $h->margin_pct > 10 ? 'text-success' : ($h->margin_pct > 0 ? 'text-warning' : 'text-danger') ?>">
                                    <?= $h->margin_pct ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Shipment -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100" style="border-top:3px solid #36b9cc !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                    style="font-size:.65rem;letter-spacing:.05em">Total Shipment</div>
                                <div class="fw-bold h4 mb-0"><?= number_format($h->total_shipment) ?></div>
                                <div class="text-muted" style="font-size:.7rem"><?= $h->total_rute ?> rute ·
                                    <?= $h->total_vendor_used ?> vendor</div>
                            </div>
                        </div>
                    </div>
                    <!-- Unfulfill -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100"
                            style="border-top:3px solid <?= $h->unfulfill_pct >= 10 ? '#e74a3b' : '#adb5bd' ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                    style="font-size:.65rem;letter-spacing:.05em">Unfulfill Rate</div>
                                <div class="fw-bold h4 mb-0 <?= $h->unfulfill_pct >= 10 ? 'text-danger' : '' ?>">
                                    <?= $h->unfulfill_pct ?>%
                                </div>
                                <div class="text-muted" style="font-size:.7rem"><?= $h->total_unfulfill ?> trip</div>
                            </div>
                        </div>
                    </div>
                    <!-- Pending Payment (Real) -->
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card shadow-sm border-0 h-100"
                            style="border-top:3px solid <?= $h->pending_real_pct >= 20 ? '#e74a3b' : '#adb5bd' ?> !important">
                            <div class="card-body py-2 px-3">
                                <div class="text-muted fw-semibold text-uppercase mb-1"
                                    style="font-size:.65rem;letter-spacing:.05em">Pending (Real)</div>
                                <div class="fw-bold h4 mb-0 <?= $h->pending_real_pct >= 20 ? 'text-danger' : '' ?>">
                                    <?= $h->pending_real_pct ?>%
                                </div>
                                <div class="text-muted" style="font-size:.7rem">
                                    <?= $h->pending_real ?> invoice
                                    <?php if ($h->belum_diisi_pct > 0): ?>
                                        · <span class="text-warning"><?= $h->belum_diisi_pct ?>% belum diisi</span>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Chart Trend + Payment Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-8">
                        <div class="card shadow h-100">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">
                                    <i class="fas fa-chart-line me-2"></i> Trend Margin per Bulan
                                </h6>
                                <div class="btn-group btn-group-sm">
                                    <input type="radio" class="btn-check" name="trendMode" id="trendMargin" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="trendMargin">Margin</label>
                                    <input type="radio" class="btn-check" name="trendMode" id="trendRevenue">
                                    <label class="btn btn-outline-secondary btn-sm" for="trendRevenue">Revenue</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTrend" height="110"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold" style="color:#6f42c1">
                                    <i class="fas fa-money-bill-wave me-2"></i> Status Payment
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartPayment" height="180"></canvas>
                                <div class="mt-3">
                                    <?php foreach ($payment_stats as $ps): ?>
                                        <?php
                                        $status_lc = strtolower($ps->status_payment_user ?? '');
                                        $color = (strpos($status_lc, 'paid') !== false || strpos($status_lc, 'lunas') !== false)
                                            ? 'success' : ((strpos($status_lc, 'waiting') !== false || strpos($status_lc, 'pending') !== false)
                                                ? 'danger' : ($status_lc === '' ? 'secondary' : 'warning'));
                                        ?>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-truncate" style="max-width:150px">
                                                <span class="badge bg-<?= $color ?> me-1"
                                                    style="font-size:.6rem">&nbsp;</span>
                                                <?= htmlspecialchars($ps->status_payment_user ?: '(belum diisi)') ?>
                                            </span>
                                            <span class="fw-bold"><?= $ps->total ?></span>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Rute Breakdown -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-success">
                                    <i class="fas fa-route me-2"></i> Breakdown per Rute
                                    <span class="badge bg-secondary ms-2"><?= count($rute_breakdown) ?></span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-success sticky-top">
                                            <tr>
                                                <th>Origin</th>
                                                <th>Dest</th>
                                                <th>Sheet</th>
                                                <th class="text-center">Trip</th>
                                                <th class="text-end">Revenue</th>
                                                <th class="text-end">Margin</th>
                                                <th class="text-center">Margin%</th>
                                                <th class="text-center">Unfulfill</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rute_breakdown as $r): ?>
                                                <tr>
                                                    <td class="small"><?= htmlspecialchars($r->origin) ?></td>
                                                    <td class="small"><?= htmlspecialchars($r->dest_1) ?></td>
                                                    <td><span class="badge bg-secondary"
                                                            style="font-size:.6rem"><?= $r->sheet_type ?></span></td>
                                                    <td class="text-center fw-bold"><?= $r->total_trip ?></td>
                                                    <td class="text-end small">Rp
                                                        <?= number_format($r->total_revenue, 0, ',', '.') ?></td>
                                                    <td
                                                        class="text-end small fw-semibold <?= $r->total_margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        Rp <?= number_format($r->total_margin, 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge <?= $r->margin_pct > 10 ? 'bg-success' : ($r->margin_pct > 0 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                                            <?= $r->margin_pct ?>%
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($r->unfulfill_count > 0): ?>
                                                            <span class="badge bg-danger"><?= $r->unfulfill_count ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (empty($rute_breakdown)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center py-3 text-muted">Belum ada data rute
                                                    </td>
                                                </tr>
                                            <?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Shipment History -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="m-0 fw-bold">
                            <i class="fas fa-list me-2 text-info"></i> Riwayat Shipment
                        </h6>
                        <!-- Filter tanggal inline -->
                        <form method="GET" action="<?= base_url('customer_health/detail/' . rawurlencode($customer)) ?>"
                            class="d-flex gap-2 align-items-center flex-wrap">
                            <?php if (!empty($sheet_type)): ?>
                                <input type="hidden" name="sheet_type" value="<?= htmlspecialchars($sheet_type) ?>">
                            <?php endif ?>
                            <input type="date" name="date_from" class="form-control form-control-sm" style="width:140px"
                                value="<?= $date_from ?>">
                            <span class="text-muted small">s/d</span>
                            <input type="date" name="date_to" class="form-control form-control-sm" style="width:140px"
                                value="<?= $date_to ?>">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <?php if ($date_from || $date_to): ?>
                                <a href="<?= base_url('customer_health/detail/' . rawurlencode($customer) . (!empty($sheet_type) ? '?sheet_type=' . urlencode($sheet_type) : '')) ?>"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif ?>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Sheet</th>
                                        <th>Origin → Dest</th>
                                        <th>Vendor</th>
                                        <th>Status</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Margin</th>
                                        <th>Status Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shipments as $s): ?>
                                        <?php
                                        $status_lc = strtolower($s->status ?? '');
                                        $is_unfulfill = strpos($status_lc, 'unfulfill') !== false
                                            || strpos($status_lc, 'cancel') !== false
                                            || strpos($status_lc, 'not support') !== false
                                            || strpos($status_lc, 'standby') !== false
                                            || $status_lc === 'belum jalan'
                                            || $status_lc === 'off';
                                        $payment_lc = strtolower($s->status_payment_user ?? '');
                                        $is_paid = strpos($payment_lc, 'paid') !== false || strpos($payment_lc, 'lunas') !== false;
                                        $is_real_pending = !$is_paid && $payment_lc !== ''
                                            && (strpos($payment_lc, 'waiting') !== false || strpos($payment_lc, 'pending') !== false);
                                        $is_belum_diisi = $payment_lc === '';
                                        ?>
                                        <tr class="<?= $is_unfulfill ? 'table-warning' : '' ?>">
                                            <td class="small">
                                                <?= $s->start_date ? date('d/m/Y', strtotime($s->start_date)) : '—' ?></td>
                                            <td><span class="badge bg-secondary"
                                                    style="font-size:.6rem"><?= $s->sheet_type ?></span></td>
                                            <td class="small">
                                                <?= htmlspecialchars($s->origin) ?>
                                                <?php if ($s->dest_1): ?>
                                                    <i class="fas fa-arrow-right text-muted mx-1" style="font-size:.6rem"></i>
                                                    <?= htmlspecialchars($s->dest_1) ?>
                                                <?php endif ?>
                                            </td>
                                            <td class="small text-muted"><?= htmlspecialchars($s->vendor ?: '—') ?></td>
                                            <td>
                                                <span
                                                    class="badge <?= $is_unfulfill ? 'bg-warning text-dark' : 'bg-light text-dark' ?>"
                                                    style="font-size:.65rem">
                                                    <?= htmlspecialchars($s->status ?: '—') ?>
                                                </span>
                                            </td>
                                            <td class="text-end small">
                                                Rp <?= number_format($s->trip_cost_from_user, 0, ',', '.') ?>
                                            </td>
                                            <td
                                                class="text-end small fw-semibold <?= $s->margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($s->margin, 0, ',', '.') ?>
                                            </td>
                                            <td>
                                                <?php if ($is_paid): ?>
                                                    <span class="badge bg-success" style="font-size:.65rem">
                                                        <?= htmlspecialchars($s->status_payment_user) ?>
                                                    </span>
                                                <?php elseif ($is_real_pending): ?>
                                                    <span class="badge bg-danger" style="font-size:.65rem">
                                                        <?= htmlspecialchars($s->status_payment_user) ?>
                                                    </span>
                                                <?php elseif ($is_belum_diisi): ?>
                                                    <span class="badge bg-secondary" style="font-size:.65rem">
                                                        Belum diisi
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark" style="font-size:.65rem">
                                                        <?= htmlspecialchars($s->status_payment_user) ?>
                                                    </span>
                                                <?php endif ?>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                    <?php if (empty($shipments)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada shipment di
                                                periode ini</td>
                                        </tr>
                                    <?php endif ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ── Data trend dari PHP ──
    const trendData = <?= json_encode(array_map(fn($r) => [
        'periode' => $r->periode,
        'sheet' => $r->sheet_type,
        'margin' => (float) $r->total_margin,
        'revenue' => (float) $r->total_revenue,
        'shipment' => (int) $r->total_shipment,
        'margin_pct' => (float) $r->margin_pct,
    ], $trend)) ?>;

    const paymentData = <?= json_encode(array_map(fn($r) => [
        'label' => $r->status_payment_user ?: '(belum diisi)',
        'total' => (int) $r->total,
        'revenue' => (float) $r->total_revenue,
    ], $payment_stats)) ?>;

    const MONTH_ORDER = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
    const SHEET_COLORS = {
        'FTL_Non_SPX': '#4e73df',
        'Dailyrent': '#1cc88a',
        'FTL_A1_SPX': '#f6c23e',
        'FTL_Dedicated': '#e74a3b',
        'FTL_COC_SPX': '#36b9cc',
        'FTL_Reguler_SPX': '#6f42c1',
    };
    const FALLBACK = ['#fd7e14', '#20c997', '#d63384', '#0dcaf0', '#adb5bd'];

    function monthIdx(p) {
        const i = MONTH_ORDER.indexOf(p.toLowerCase().trim());
        return i !== -1 ? i : 99;
    }

    // ── Chart Trend ──
    let trendChart = null;

    function buildTrendChart(mode = 'margin') {
        const canvas = document.getElementById('chartTrend');
        if (!trendData.length) {
            canvas.parentElement.innerHTML = '<div class="text-center py-4 text-muted">Belum ada data trend</div>';
            return;
        }

        const periodes = [...new Set(trendData.map(r => r.periode))].sort((a, b) => monthIdx(a) - monthIdx(b));
        const sheets = [...new Set(trendData.map(r => r.sheet))];
        let fallbackIdx = 0;

        const datasets = sheets.map(sheet => {
            const color = SHEET_COLORS[sheet] || FALLBACK[fallbackIdx++ % FALLBACK.length];
            return {
                label: sheet,
                data: periodes.map(p => {
                    const found = trendData.find(r => r.sheet === sheet && r.periode.toLowerCase().trim() === p.toLowerCase().trim());
                    return found ? (mode === 'margin' ? found.margin : found.revenue) : null;
                }),
                borderColor: color,
                backgroundColor: color + '22',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                spanGaps: false,
                fill: false,
            };
        });

        if (trendChart) { trendChart.destroy(); trendChart = null; }

        trendChart = new Chart(canvas, {
            type: 'line',
            data: { labels: periodes, datasets },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': Rp ' + (ctx.parsed.y ?? 0).toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: { callback: v => 'Rp ' + (v / 1e6).toFixed(0) + 'jt' },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        });
    }

    buildTrendChart('margin');
    document.getElementById('trendMargin').addEventListener('change', () => buildTrendChart('margin'));
    document.getElementById('trendRevenue').addEventListener('change', () => buildTrendChart('revenue'));

    // ── Chart Payment (donut) ──
    (function () {
        if (!paymentData.length) return;
        const colors = paymentData.map(d => {
            const l = d.label.toLowerCase();
            if (l.includes('paid') || l.includes('lunas')) return '#1cc88a';
            if (l.includes('waiting') || l.includes('pending')) return '#e74a3b';
            if (l === '(belum diisi)') return '#adb5bd';
            return '#f6c23e';
        });
        new Chart(document.getElementById('chartPayment'), {
            type: 'doughnut',
            data: {
                labels: paymentData.map(d => d.label),
                datasets: [{ data: paymentData.map(d => d.total), backgroundColor: colors, borderWidth: 2 }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } },
                    tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed + ' invoice' } }
                }
            }
        });
    })();
</script>